<?php
/*
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
    public const VERSION = "2.0.2";
    /** int Version (Major * 10000 + Minor * 100 + Release) */
    public const VERSION_ID = 20002;

    /** @var Events */
    public readonly Events $events;
    /** @var Args */
    public readonly Args $args;
    /** @var Flags */
    public readonly Flags $flags;
    /** @var float */
    public readonly float $execStartStamp;
    /** @var Buffer */
    public readonly Buffer $buffer;
    /** @var null|string */
    protected ?string $execClassName = null;
    /** @var string|null */
    protected ?string $scriptName = null;
    /** @var string */
    private string $eolChar = PHP_EOL;

    /**
     * CLI constructor.
     * @param Directory $dir
     * @param array $args
     * @throws BadArgumentException
     */
    public function __construct(private Directory $dir, array $args)
    {
        $this->events = new Events();
        $this->args = new Args();
        $this->flags = new Flags();
        $this->execStartStamp = microtime(true);
        $this->buffer = new Buffer();

        $aC = -1;
        foreach ($args as $arg) {
            if (!$arg) {
                continue;
            }

            $aC++;
            if ($aC === 0) {
                if (preg_match('/^\w+$/', $arg)) {
                    $this->scriptName = $arg;
                    continue;
                }
            }

            // Set if a flag
            if (preg_match('/^-{1,2}\w$/', $arg)) {
                switch ($arg) {
                    case "q":
                    case "quick":
                        $this->flags->set(Flags::QUICK);
                        break;
                    case "f":
                    case "force":
                        $this->flags->set(Flags::FORCE);
                        break;
                    case "debug":
                        $this->flags->set(Flags::DEBUG);
                        break;
                    case "v":
                    case "verbose":
                        $this->flags->set(Flags::VERBOSE);
                        break;
                }
            }

            // Append to arguments
            if (preg_match('/^-{0,2}\w+(=[\w@.\-]+)?$/', $arg)) {
                $arg = explode("=", $arg);
                $this->args->set(ltrim($arg[0], "-"), $arg[1] ?? null);
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
    final public function eolChar(string $char): self
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
        // Exec success signal
        $execSuccess = false;

        try {
            // Bin namespace autoloader
            $binDirectoryPath = $this->dir->path();
            spl_autoload_register(function (string $class) use ($binDirectoryPath) {
                if (preg_match('/^bin\\\\\w+$/', $class)) {
                    $className = OOP::baseClassName($class);
                    $classFilename = OOP::snake_case($className);
                    $classFilepath = $binDirectoryPath . DIRECTORY_SEPARATOR . $classFilename . ".php";
                    if (@is_file($classFilepath)) {
                        @include_once($classFilepath);
                    }
                }
            });

            // Before execution starts
            $this->events->beforeExec()->trigger([$this]);

            // Load script
            try {
                $scriptName = $this->scriptName ?? "console";
                $scriptClassname = "bin\\" . OOP::snake_case($scriptName);
                if (!class_exists($scriptClassname)) {
                    throw new \RuntimeException(sprintf('Script class "%s" does not exist', $scriptClassname));
                } elseif (!is_a($scriptClassname, 'Comely\CLI\Abstract_CLI_Script', true)) {
                    throw new \RuntimeException(
                        sprintf('Script class "%s" must extend "Abstract_CLI_Script" class', $scriptClassname)
                    );
                }

                $this->execClassName = $scriptClassname;
                /** @var Abstract_CLI_Script $scriptObject */
                $scriptObject = new $scriptClassname($this);
            } catch (\RuntimeException $e) {
                $this->events->scriptNotFound()->trigger([$this, $scriptClassname ?? ""]);
                throw $e;
            }

            // Script is loaded trigger
            $this->events->scriptLoaded()->trigger([$this, $scriptObject]);

            // Execute script
            try {
                $scriptObject->exec();
                $execSuccess = true;
            } catch (\Throwable $t) {
                $this->events->scriptExecException()->trigger([$this, $t]);
                throw $t;
            }
        } catch (\Throwable $t) {
            $this->exception2Str($t);
        }

        // Execution
        $this->print("");
        if ($execSuccess) {
            $this->print("{green}Execution finished!{/}");
        } else {
            $this->print("{red}Execution finished with an exception!{/}");
        }

        // After script exec event
        $this->events->afterExec()->trigger([$this, $execSuccess]);
        $this->finish();
    }

    /**
     * @param bool $exit
     */
    final public function finish(bool $exit = true): void
    {
        // Finish execution
        $this->print("");
        $this->print(sprintf("Execution time: {grey}%ss{/}", number_format(microtime(true) - $this->execStartStamp, 4)));
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

        $quickExec = $this->flags->isQuick();
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

        $quickExec = $this->flags->isQuick();
        $chars = str_split($line);
        foreach ($chars as $char) {
            print $char;
            if (!$quickExec) {
                $this->microSleep($interval);
            }
        }

        $this->buffer->appendIfBuffering($line);

        if ($eol) {
            print "\e[0m";
            print $this->eolChar;
            $this->buffer->appendIfBuffering($this->eolChar);
        }
    }

    /**
     * @return void
     */
    final public function eol(): void
    {
        $this->print("");
    }

    /**
     * @param string $line
     * @param int $sleep
     */
    final public function print(string $line, int $sleep = 0): void
    {
        $this->buffer->appendIfBuffering($line . $this->eolChar);
        print $this->ansiEscapeSeq($line) . $this->eolChar;
        if (!$this->flags->isQuick()) {
            $this->microSleep($sleep);
        }
    }

    /**
     * @param string $line
     * @param int $sleep
     */
    final public function inline(string $line, int $sleep = 0): void
    {
        $this->buffer->appendIfBuffering($line);
        print $this->ansiEscapeSeq($line, false);
        if (!$this->flags->isQuick()) {
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
        $this->repeat(".", 10, 50, true);
        $this->print("");
        $this->print($tabs . sprintf('{yellow}Caught:{/} {red}{b}%s{/}', get_class($t)));
        $this->print($tabs . sprintf("{yellow}Message:{/} {cyan}%s{/}", $t->getMessage()));
        $this->print($tabs . sprintf("{yellow}File:{/} %s", $t->getFile()));
        $this->print($tabs . sprintf("{yellow}Line:{/} {cyan}%d{/}", $t->getLine()));
        $this->print($tabs . "{yellow}Debug Backtrace:");
        $this->print($tabs . "┬");

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

                $traceString = sprintf('"{u}{cyan}%s{/}" on line # {u}{yellow}%d{/}', $file, $line);
                if ($method) {
                    $traceString = sprintf('Method {u}{magenta}%s(){/} in file ', $method) . $traceString;
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
            '/{([a-z]+|\/)}/i',
            function ($modifier) {
                return match (strtolower($modifier[1] ?? "")) {
                    "red" => "\e[31m",
                    "green" => "\e[32m",
                    "yellow" => "\e[33m",
                    "blue" => "\e[34m",
                    "magenta" => "\e[35m",
                    "gray", "grey" => "\e[90m",
                    "cyan" => "\e[36m",
                    "b", "bold" => "\e[1m",
                    "u", "underline" => "\e[4m",
                    "blink" => "\e[5m",
                    "invert" => "\e[7m",
                    "reset", "/" => "\e[0m",
                    default => $modifier[0] ?? "",
                };
            },
            $prepare
        );

        if ($reset) {
            $prepared .= "\e[0m";
        }

        return $prepared;
    }
}
