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

namespace Comely\CLI\Buffer;

/**
 * Class BufferedData
 * @package Comely\CLI\Buffer
 */
class BufferedData
{
    /** @var string */
    private $data;
    /** @var int */
    private $size;

    /**
     * BufferedData constructor.
     */
    public function __construct()
    {
        $this->data = "";
        $this->size = 0;
    }

    /**
     * @param string $data
     */
    public function append(string $data): void
    {
        $this->data .= $data;
        $this->size += strlen($data);
    }

    /**
     * @return int
     */
    public function size(): int
    {
        return $this->size;
    }

    /**
     * @return string
     */
    public function data(): string
    {
        return $this->data;
    }
}
