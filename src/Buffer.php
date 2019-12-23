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

use Comely\CLI\Buffer\BufferedData;
use Comely\CLI\Exception\CLI_BufferException;

/**
 * Class Buffer
 * @package Comely\CLI
 */
class Buffer
{
    /** @var null|BufferedData */
    private $bufferData;
    /** @var bool */
    private $ansiEscapeSeq;

    /**
     * Buffer constructor.
     */
    public function __construct()
    {
        $this->ansiEscapeSeq = false;
    }

    /**
     * @param bool $keep
     */
    public function ansiEscapeSeq(bool $keep): void
    {
        $this->ansiEscapeSeq = $keep;
    }

    /**
     * @return bool
     */
    public function isBuffering(): bool
    {
        return $this->bufferData ? true : false;
    }

    /**
     * @return void
     */
    public function startClean(): void
    {
        $this->bufferData = new BufferedData();
    }

    /**
     * @param string $data
     */
    public function appendIfBuffering(string $data): void
    {
        if ($this->bufferData) {
            $this->appendToBuffer($data);
        }
    }

    /**
     * @param string $data
     * @throws CLI_BufferException
     */
    public function append(string $data): void
    {
        if (!$this->bufferData) {
            throw new CLI_BufferException('CLI buffer is inactive');
        }

        $this->appendToBuffer($data);
    }

    /**
     * @param string $data
     */
    private function appendToBuffer(string $data): void
    {
        if (!$this->ansiEscapeSeq) {
            $data = preg_replace('/{([a-z]+|\/)}/i', '', $data);
        }

        $this->bufferData->append($data);
    }

    /**
     * @return BufferedData
     * @throws CLI_BufferException
     */
    public function getBufferedData(): BufferedData
    {
        if (!$this->bufferData) {
            throw new CLI_BufferException('CLI buffer is inactive');
        }

        return $this->bufferData;
    }

    /**
     * @return BufferedData
     * @throws CLI_BufferException
     */
    public function endClean(): BufferedData
    {
        $bufferedData = $this->getBufferedData();
        $this->bufferData = null;
        return $bufferedData;
    }
}