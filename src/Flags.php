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
 * Class Flags
 * @package Comely\CLI
 */
class Flags implements \Iterator, \Countable
{
    /** @var array */
    private $flags;
    /** @var int */
    private $count;
    /** @var int */
    private $pos;

    /** @var bool */
    private $_force;
    /** @var bool */
    private $_quickExec;

    /**
     * Flags constructor.
     */
    public function __construct()
    {
        $this->flags = [];
        $this->pos = 0;
        $this->count = 0;
    }

    /**
     * @return bool
     */
    public function force(): bool
    {
        if (!is_bool($this->_force)) {
            $this->_force = $this->has("force") || $this->has("f");
        }

        return $this->_force;
    }

    /**
     * @return bool
     */
    public function quickExec(): bool
    {
        if (!is_bool($this->_quickExec)) {
            $this->_quickExec = $this->has("quick") || $this->has("q");
        }

        return $this->_quickExec;
    }

    /**
     * @param string $name
     * @param string|null $value
     * @return Flags
     */
    public function set(string $name, ?string $value): self
    {
        $this->flags[strtolower(ltrim($name, "-"))] = $value;
        $this->count++;
        return $this;
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return $this->count;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool
    {
        return array_key_exists(strtolower($name), $this->flags);
    }

    /**
     * @param string $name
     * @return string|null
     */
    public function get(string $name): ?string
    {
        return $this->flags[$name] ?? null;
    }

    /**
     * @return void
     */
    public function rewind(): void
    {
        reset($this->flags);
    }

    /**
     * @return string|null
     */
    public function current(): ?string
    {
        return current($this->flags);
    }

    /**
     * @return string
     */
    public function key(): string
    {
        return key($this->flags);
    }

    /**
     * @void
     */
    public function next(): void
    {
        next($this->flags);
    }

    /**
     * @return bool
     */
    public function valid(): bool
    {
        return key($this->flags) !== null;
    }
}