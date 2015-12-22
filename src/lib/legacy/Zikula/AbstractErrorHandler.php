<?php
/**
 * Copyright 2010 Zikula Foundation.
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage Zikula_Core
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
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
