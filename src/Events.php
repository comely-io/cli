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

use Comely\Utils\Events\Event;
use Comely\Utils\Events\EventsRegister;

/**
 * Class Events
 * @package Comely\CLI
 */
class Events
{
    /** @var CLI */
    private $cli;
    /** @var EventsRegister */
    private $register;

    /**
     * Events constructor.
     * @param CLI $cli
     */
    public function __construct(CLI $cli)
    {
        $this->cli = $cli;
        $this->register = new EventsRegister();
    }

    /**
     * Callback first argument is instance of CLI obj
     * @return Event
     */
    public function beforeExec(): Event
    {
        return $this->register->on("before_exec");
    }

    /**
     * Callback first argument is instance of CLI obj
     * Callback second argument is boolean, if script exec method finishes without any thrown exceptions, its value is TRUE otherwise FALSE
     * @return Event
     */
    public function afterExec(): Event
    {
        return $this->register->on("after_exec");
    }

    /**
     * Callback first argument is instance of CLI obj
     * Callback second argument is string class name
     * @return Event
     */
    public function scriptNotFound(): Event
    {
        return $this->register->on("script_not_found");
    }

    /**
     * Callback first argument is instance of CLI obj
     * Callback second argument is instance of Abstract_CLI_Script
     * @return Event
     */
    public function scriptLoaded(): Event
    {
        return $this->register->on("script_loaded");
    }

    /**
     * Callback first argument is instance of CLI obj
     * Callback second argument will be instance of \Throwable
     * @return Event
     */
    public function scriptExecException(): Event
    {
        return $this->register->on("script_exec_exception");
    }
}