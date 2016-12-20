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
    public function __construct()
    {
        @trigger_error('This exception is deprecated, please use FatalErrorException instead.', E_USER_DEPRECATED);
    }
}
