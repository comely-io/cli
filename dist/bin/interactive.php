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

namespace bin;

use Comely\CLI\Abstract_CLI_Script;

/**
 * Class interactive
 * @package bin
 */
class interactive extends Abstract_CLI_Script
{
    /**
     * @return void
     */
    public function exec(): void
    {
        $name = $this->inline("{red}Your complete name?{/} {yellow}")->waitForInput();
        $email = $this->inline("{red}Your e-mail address:{/} {yellow}")->waitForInput();
        $subscribe = strtolower(strval($this->inline("{grey}Subscribe to our newsletter?{/} (Y/n) {cyan}")->waitForInput())) === "y";

        $this->print("");
        $this->print(sprintf(
            "Greetings, {yellow}{b}%s{/} e-mail ID {yellow}{b}%s{/}. You have chosen to {%s}%s.",
            $name,
            $email,
            $subscribe ? "green" : "red",
            $subscribe ? "subscribe" : "not subscribe"
        ));
    }
}
