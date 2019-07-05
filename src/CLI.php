<?php
/**
 * This file is a part of "comely-io/cli" package.
 * https://github.com/comely-io/cli
 *
 * Copyright (c) Furqan A. Siddiqui <hello@furqansiddiqui.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit following link:
 * https://github.com/comely-io/cli/blob/master/LICENSE
 */

declare(strict_types=1);

namespace Comely\CLI;

use Comely\CLI\Exception\BadArgumentException;
use Comely\Filesystem\Directory;
use Comely\Utils\OOP\OOP;

/**
 * Class CLI
 * @package Comely\CLI
 */
class CLI
{
    /** string Version (Major.Minor.Release-Suffix) */
    public const VERSION = "1.0.10";
    /** int Version (Major * 10000 + Minor * 100 + Release) */
    public const VERSION_ID = 10010;

    /** @var Directory */
    private $dir;
    /** @var Events */
    private $events;
    /** @var string */
    private $eolChar;
    /** @var Args */
    private $args;
    /** @var Flags */
    private $flags;
    /** @var float */
    private $execStartStamp;

    /**
     * CLI constructor.
     * @param Directory $dir
     * @param array $args
     * @throws BadArgumentException
     */
    public function __construct(Directory $dir, array $args)
    {
        $this->dir = $dir;
        $this->events = new Events($this);
        $this->eolChar = PHP_EOL;
        $this->args = new Args();
        $this->flags = new Flags();
        $this->execStartStamp = microtime(true);

        foreach ($args as $arg) {
            if (!$arg) {
                continue;
            }

            // Is an argument?
            if (preg_match('/\w+/', $arg)) {
                $this->args->append($arg);
                continue;
            }

            // If a flag?
            if (preg_match('/\-{1,2}\w+(\=[\w\@\.\-]+)?/', $arg)) {
                $arg = explode("=", $arg);
                $this->flags->set($arg[0], $arg[1] ?? null);
                continue;
            }

            // Bad argument type
            throw new BadArgumentException(
                sprintf('Unacceptable passed argument format near "%s..."', substr($arg, 0, 8))
            );
        }
    }

    /**
     * @param string $char
     * @return CLI
     */
    final public function eol(string $char): self
    {
        if (!in_array($char, ["\n", "\r\n"])) {
            throw new \InvalidArgumentException('Invalid EOL character');
        }

        $this->eolChar = $char;
        return $this;
    }

    /**
     * @return void
     */
    final public function exec(): void
    {
        try {
            // Before execution starts
            $this->events->beforeExec()->trigger([$this]);

            // Bin namespace autoloader
            $binDirectoryPath = $this->dir->path();
            spl_autoload_register(function (string $class) use ($binDirectoryPath) {
                if (preg_match('/^bin\\\\\w+$/', $class)) {
                    $className = OOP::baseClassName($class);
                    $classFilename = OOP::snake_case($className);
                    $classFilepath = $binDirectoryPath . DIRECTORY_SEPARATOR . $classFilename . ".php";
                    if (@is_file($classFilepath)) {
                        /** @noinspection PhpIncludeInspection */
                        @include_once($classFilepath);
                    }
                }
            });

            // Load script
            try {
                $scriptName = $this->args()->get(0) ?? "console";
                if (!is_string($scriptName) || !preg_match('/\w+/', $scriptName)) {
                    throw new \InvalidArgumentException('Invalid CLI script name');
                }

                $scriptClassname = "bin\\" . OOP::snake_case($scriptName);
                if (!class_exists($scriptClassname)) {
                    throw new \RuntimeException(sprintf('Script class "%s" does not exist', $scriptClassname));
                } elseif (!is_a($scriptClassname, 'Comely\CLI\Abstract_CLI_Script', true)) {
                    throw new \RuntimeException(
                        sprintf('Script class "%s" must extend "Abstract_CLI_Script" class', $scriptClassname)
                    );
                }

                /** @var Abstract_CLI_Script $scriptObject */
                $scriptObject = new $scriptClassname($this);
            } catch (\RuntimeException $e) {
                $this->events->scriptNotFound()->trigger([$this, $scriptClassname ?? ""]);
                throw $e;
            }

            // Script is loaded trigger
            $this->events->scriptLoaded()->trigger([$this, $scriptObject]);

            // Execute script
            $execSuccess = false;
            try {
                $scriptObject->exec();
                $execSuccess = true;
            } catch (\Throwable $t) {
                $this->events->scriptExecException()->trigger([$this, $t]);
                throw $t;
            }

            // After script exec event
            $this->events->afterExec()->trigger([$this, $execSuccess]);
        } catch (\Throwable $t) {
            $this->exception2Str($t);
        }

        // Execution
        $this->print("");
        if (isset($execSuccess) && $execSuccess) {
            $this->print("{green}Execution finished!{/}");
        } else {
            $this->print("{red}Execution finish with an error!{/}");
        }

        $this->finish();
    }

    /**
     * @param bool $exit
     */
    final public function finish(bool $exit = true): void
    {
        // Finish execution
        $this->print("");
        $this->print(sprintf("Execution time: {grey}%s{/}", number_format(microtime(true) - $this->execStartStamp, 4)));
        $this->printMemoryConsumption();

        if ($exit) {
            exit();
        }
    }

    /**
     * @return void
     */
    final public function printMemoryConsumption(): void
    {
        $memoryUsage = number_format((memory_get_usage(false) / 1024) / 1024, 2);
        $memoryUsageReal = number_format((memory_get_usage(true) / 1024) / 1024, 2);
        $this->print(sprintf("Memory usage: {grey}%sMB{/} / {grey}%sMB{/}", $memoryUsage, $memoryUsageReal));

        $peakMemoryUsage = number_format((memory_get_peak_usage(false) / 1024) / 1024, 2);
        $peakMemoryUsageReal = number_format((memory_get_peak_usage(true) / 1024) / 1024, 2);
        $this->print(sprintf("Peak Memory usage: {grey}%sMB{/} / {grey}%sMB{/}", $peakMemoryUsage, $peakMemoryUsageReal));
    }

    /**
     * @return Events
     */
    final public function events(): Events
    {
        return $this->events;
    }

    /**
     * @return Args
     */
    final public function args(): Args
    {
        return $this->args;
    }

    /**
     * @return Flags
     */
    final public function flags(): Flags
    {
        return $this->flags;
    }

    /**
     * @param string $char
     * @param int $count
     * @param int $interval
     * @param bool $eol
     */
    final public function repeat(string $char = ".", int $count = 10, int $interval = 100, bool $eol = false): void
    {
        if ($interval <= 0) {
            throw new \InvalidArgumentException('Repeat method requires positive interval');
        }

        $quickExec = $this->flags->quickExec();
        for ($i = 0; $i < $count; $i++) {
            print $char;
            if (!$quickExec) {
                $this->microSleep($interval);
            }
        }

        if ($eol) {
            print "\e[0m";
            print $this->eolChar;
        }
    }

    /**
     * @param string $line
     * @param int $interval
     * @param bool $eol
     */
    final public function typewrite(string $line, int $interval = 100, bool $eol = false): void
    {
        if ($interval <= 0) {
            throw new \InvalidArgumentException('Typewrite method requires positive interval');
        }

        $quickExec = $this->flags->quickExec();
        $chars = str_split($line);
        foreach ($chars as $char) {
            print $char;
            if (!$quickExec) {
                $this->microSleep($interval);
            }
        }

        if ($eol) {
            print "\e[0m";
            print $this->eolChar;
        }
    }

    /**
     * @param string $line
     * @param int $sleep
     */
    final public function print(string $line, int $sleep = 0): void
    {
        print $this->ansiEscapeSeq($line) . $this->eolChar;
        if (!$this->flags->quickExec()) {
            $this->microSleep($sleep);
        }
    }

    /**
     * @param string $line
     * @param int $sleep
     */
    final public function inline(string $line, int $sleep = 0): void
    {
        print $this->ansiEscapeSeq($line, false);
        if (!$this->flags->quickExec()) {
            $this->microSleep($sleep);
        }
    }

    /**
     * @param int $milliseconds
     */
    final public function microSleep(int $milliseconds = 0): void
    {
        if ($milliseconds > 0) {
            usleep(intval(($milliseconds / 1000) * pow(10, 6)));
        }
    }

    /**
     * @param \Throwable $t
     * @param int $tabIndex
     */
    final public function exception2Str(\Throwable $t, int $tabIndex = 0): void
    {
        $tabs = str_repeat("\t", $tabIndex);
        $this->print("");
        $this->repeat(".", 10);
        $this->print("");
        $this->print($tabs . sprintf('{yellow}Caught:{/} {red}{b}%s{/}', get_class($t)));
        $this->print("");
        $this->print($tabs . "\t{cyan}" . $t->getMessage() . "{/}");
        $this->print("");
        $this->print($tabs . sprintf("{yellow}File:{/} %s", $t->getFile()));
        $this->print($tabs . sprintf("{yellow}Line:{/} {cyan}%d{/}", $t->getLine()));
        $this->print("");
        $this->print($tabs . "Debug Backtrace:");

        foreach ($t->getTrace() as $trace) {
            $function = $trace["function"] ?? null;
            $class = $trace["class"] ?? null;
            $type = $trace["type"] ?? null;
            $file = $trace["file"] ?? null;
            $line = $trace["line"] ?? null;

            if ($file && is_string($file) && $line) {
                $method = $function;
                if ($class && $type) {
                    $method = $class . $type . $function;
                }

                $traceString = sprintf('“<u>%s</u>” on line # <u>%d</u>', $file, $line);
                if ($method) {
                    $traceString = sprintf('Method <u>%s()</u> in file', $method) . $traceString;
                }

                $this->print($tabs . "├─ " . $traceString);
            }
        }
        unset($trace, $traceString, $function, $class, $type, $file, $line);
    }

    /**
     * @param string $prepare
     * @param bool $reset
     * @return string
     */
    private function ansiEscapeSeq(string $prepare, bool $reset = true): string
    {
        $prepared = preg_replace_callback(
            '/\{([a-z]+|\/)\}/i',
            function ($modifier) {
                switch (strtolower($modifier[1] ?? "")) {
                    // Colors
                    case "red":
                        return "\e[31m";
                    case "green":
                        return "\e[32m";
                    case "yellow":
                        return "\e[33m";
                    case "blue":
                        return "\e[34m";
                    case "magenta":
                        return "\e[35m";
                    case "gray":
                    case "grey":
                        return "\e[90m";
                    case "cyan":
                        return "\e[36m";
                    // Formats
                    case "b":
                    case "bold":
                        return "\e[1m";
                    case "u":
                    case "underline":
                        return "\e[4m";
                    // Special
                    case "blink":
                        return "\e[5m";
                    case "invert":
                        return "\e[7m";
                    // Reset
                    case "reset":
                    case "/":
                        return "\e[0m";
                    // Default
                    default:
                        return $modifier[0] ?? "";
                }
            },
            $prepare
        );

        if ($reset) {
            $prepared .= "\e[0m";
        }

        return $prepared;
    }
}