<?php
/**
 * Copyright 2014 Zikula Foundation
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage Zikula_EventManager
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Core\Exception;

use Symfony\Component\Debug\Exception\FatalErrorException as SymfonyFatalErrorException;

/**
 * Class FatalErrorException
 *
 * @package Zikula\Core\Exception
 */
class FatalErrorException extends SymfonyFatalErrorException
{
    /**
     * Constructor.
     *
     * @param string $message
     * @param int $code
     * @param int $severity
     * @param string $filename
     * @param int $lineno
     */
    public function __construct($message = 'Fatal Error!', $code = 0, $severity = 1, $filename = __FILE__, $lineno = __LINE__)
    {
        parent::__construct($message, $code, $severity, $filename, $lineno);
    }
}
