<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Zikula\Core\Exception\FatalErrorException;

/**
 * Zikula_Exception_Fatal class.
 *
 * @deprecated since 1.4.0
 * @see FatalErrorException
 */
class Zikula_Exception_Fatal extends FatalErrorException
{
    public function __construct($message = 'Fatal Error!', $code = 0, $severity = 1, $filename = __FILE__, $lineno = __LINE__)
    {
        parent::__construct($message, $code, $severity, $filename, $lineno);
        @trigger_error('This exception is deprecated, please use FatalErrorException instead.', E_USER_DEPRECATED);
    }
}
