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
 * Class Args
 * @package Comely\CLI
 */
class Args implements \Iterator, \Countable
{
    /** @var array */
    private $args;
    /** @var int */
    private $count;
    /** @var int */
    private $pos;

    /**
     * Args constructor.
     */
    public function __construct()
    {
        $this->args = [];
        $this->count = 0;
        $this->pos = 0;
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return $this->count;
    }

    /**
     * @param string $arg
     * @return Args
     */
    public function append(string $arg): self
    {
        $this->args[] = strtolower($arg);
        $this->count++;
        return $this;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool
    {
        return in_array(strtolower($name), $this->args);
    }

    /**
     * @param int $num
     * @return string|null
     */
    public function get(int $num): ?string
    {
        return $this->args[$num] ?? null;
    }

    /**
     * @return void
     */
    public function rewind(): void
    {
        $this->pos = 0;
    }

    /**
     * @return void
     */
    public function next(): void
    {
        ++$this->pos;
    }

    /**
     * @return int
     */
    public function key(): int
    {
        return $this->pos;
    }

    /**
     * @return string
     */
    public function current(): string
    {
        return $this->args[$this->pos];
    }

    /**
     * @return bool
     */
    public function valid(): bool
    {
        return isset($this->args[$this->pos]);
    }
}