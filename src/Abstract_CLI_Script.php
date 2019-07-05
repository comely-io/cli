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

/**
 * Class Abstract_CLI_Script
 * @package Comely\CLI
 */
abstract class Abstract_CLI_Script
{
    /** @var CLI */
    protected $cli;

    /**
     * Abstract_CLI_Script constructor.
     * @param CLI $cli
     */
    public function __construct(CLI $cli)
    {
        $this->cli = $cli;
    }

    abstract public function exec(): void;

    /**
     * @param string $line
     * @param int $sleep
     */
    final protected function print(string $line, int $sleep = 0): void
    {
        $this->cli->print($line, $sleep);
    }

    /**
     * @param string $line
     * @param int $sleep
     */
    final protected function inline(string $line, int $sleep = 0): void
    {
        $this->cli->inline($line, $sleep);
    }

    /**
     * @param int $milliseconds
     */
    final protected function microSleep(int $milliseconds = 0): void
    {
        $this->cli->microSleep($milliseconds);
    }

    /**
     * @param string $line
     * @param int $interval
     * @param bool $eol
     */
    final protected function typewrite(string $line, int $interval = 100, bool $eol = false): void
    {
        $this->cli->typewrite($line, $interval, $eol);
    }

    /**
     * @param string $char
     * @param int $count
     * @param int $interval
     * @param bool $eol
     */
    final protected function repeat(string $char = ".", int $count = 10, int $interval = 100, bool $eol = false): void
    {
        $this->cli->repeat($char, $count, $interval, $eol);
    }

    /**
     * @return Args
     */
    final protected function args(): Args
    {
        return $this->cli->args();
    }

    /**
     * @return Flags
     */
    final protected function flags(): Flags
    {
        return $this->cli->flags();
    }
}