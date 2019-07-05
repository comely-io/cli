<?php /** @noinspection PhpUnhandledExceptionInspection */
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

require "../../vendor/autoload.php";

// Prepare Arguments
$args = $argv[1] ?? "";
$args = explode(";", substr($args, 1, -1));

// Instantiate CLI
$bin = new \Comely\Filesystem\Directory(__DIR__);
$cli = new \Comely\CLI\CLI($bin, $args);

// Listen to events, etc...
$cli->events()->beforeExec();

// Execute
$cli->exec();