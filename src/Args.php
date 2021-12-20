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

/**
 * Class Args
 * @package Comely\CLI
 */
class Args implements \Iterator, \Countable
{
    /** @var array */
    private array $args = [];
    /** @var int */
    private int $count = 0;

    /**
     * @return int
     */
    public function count(): int
    {
        return $this->count;
    }

    /**
     * @param string $name
     * @param string|null $value
     * @return $this
     */
    public function set(string $name, ?string $value = null): self
    {
        $this->args[strtolower($name)] = $value;
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
     * @param string $name
     * @return string|bool|null
     */
    public function get(string $name): string|null|bool
    {
        $key = strtolower($name);
        if (array_key_exists($key, $this->args)) {
            return $this->args[$key];
        }

        return false;
    }

    /**
     * @return void
     */
    public function rewind(): void
    {
        reset($this->args);
    }

    /**
     * @return string|null
     */
    public function current(): ?string
    {
        return current($this->args);
    }

    /**
     * @return string
     */
    public function key(): string
    {
        return key($this->args);
    }

    /**
     * @void
     */
    public function next(): void
    {
        next($this->args);
    }

    /**
     * @return bool
     */
    public function valid(): bool
    {
        return key($this->args) !== null;
    }
}
