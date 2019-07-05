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

namespace bin;

use Comely\CLI\Abstract_CLI_Script;
use Comely\CLI\ASCII\Banners;

/**
 * Class console
 * @package bin
 */
class console extends Abstract_CLI_Script
{
    public function exec(): void
    {
        $this->repeat("~", 5, 0);
        foreach (Banners::Digital("COMELY CLI")->lines() as $line) {
            $this->print("{magenta}{invert}" . $line . "{/}");
        }

        $this->repeat("~", 5, 0);
        $this->print("");
        $this->typewrite("This is a sample script that runs in CLI", 100, true);
    }
}