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
 * Class Banners
 * @package Comely\CLI\ASCII
 */
class Banners
{
    /**
     * @param string $caption
     * @return ASCII_Banner
     */
    public static function Digital(string $caption): ASCII_Banner
    {
        $words = explode(" ", $caption);
        $caption = "|" . implode("|", str_split($caption)) . "|";
        foreach ($words as $word) {
            $padding[] = str_repeat("+-", strlen($word)) . "+";
        }

        $padding = implode(" ", $padding ?? []);
        return new ASCII_Banner("digital", [$padding, $caption, $padding]);
    }
}