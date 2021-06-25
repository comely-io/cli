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
 * Class Flags
 * @package Comely\CLI
 */
class Flags
{
    /** @var int Quick execution flag */
    public const QUICK = 1 << 0;
    /** @var int Force execution flag */
    public const FORCE = 1 << 1;
    /** @var int Debug mode flag */
    public const DEBUG = 1 << 2;
    /** @var int Verbose mode flag */
    public const VERBOSE = 1 << 3;

    /** @var int */
    private int $flags = 0;

    /**
     * @return bool
     */
    public function isQuick(): bool
    {
        return $this->has(self::QUICK);
    }

    /**
     * @return bool
     */
    public function forceExec(): bool
    {
        return $this->has(self::FORCE);
    }

    /**
     * @return bool
     */
    public function isDebug(): bool
    {
        return $this->has(self::DEBUG);
    }

    /**
     * @return bool
     */
    public function isVerbose(): bool
    {
        return $this->has(self::VERBOSE);
    }

    /**
     * @param int $flag
     * @return $this
     */
    public function set(int $flag): self
    {
        $this->flags = $this->flags | $flag;
        return $this;
    }

    /**
     * @param int $flag
     * @return bool
     */
    public function has(int $flag): bool
    {
        return (bool)(($this->flags & $flag));
    }

    /**
     * @param int $flag
     * @return $this
     */
    public function remove(int $flag): self
    {
        $this->flags &= ~$flag;
        return $this;
    }
}
