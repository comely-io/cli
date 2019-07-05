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

namespace Comely\CLI\ASCII;

/**
 * Class ASCII_Banner
 * @package Comely\CLI\ASCII
 */
class ASCII_Banner
{
    /** @var string */
    private $name;
    /** @var array */
    private $lines;

    /**
     * ASCII_Banner constructor.
     * @param string $name
     * @param array $lines
     */
    public function __construct(string $name, array $lines)
    {
        $this->name = $name;
        $this->lines = $lines;
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function lines(): array
    {
        return $this->lines;
    }
}