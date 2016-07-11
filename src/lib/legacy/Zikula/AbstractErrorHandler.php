<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Monolog\Logger as Log;

/**
 * Zikula ErrorHandler base class.
 *
 * @deprecated
 */
abstract class Zikula_AbstractErrorHandler
{
    const EMERG = Log::EMERGENCY; //0; // Emergency: system is unusable
    const EMERGENCY = Log::EMERGENCY; //0; // Emergency: system is unusable
    const ALERT = Log::ALERT; //-1; // Alert: action must be taken immediately
    const CRIT = Log::CRITICAL; //-2; // Critical: critical conditions
    const ERR = Log::ERROR; //-3; // Error: error conditions
    const ERROR = Log::ERROR; //-3; // Error: error conditions
    const WARN = Log::WARNING; //-4; // Warning: warning conditions
    const WARNING = Log::WARNING; //-4; // Warning: warning conditions
    const NOTICE = Log::NOTICE; //-5; // Notice: normal but significant condition
    const INFO = Log::INFO; //-6; // Informational: informational messages
    const DEBUG = Log::DEBUG; //-7; // Debug: debug messages
}
