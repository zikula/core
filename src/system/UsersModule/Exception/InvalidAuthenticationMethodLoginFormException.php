<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Exception;

class InvalidAuthenticationMethodLoginFormException extends \Exception
{
    /**
     * InvalidAuthenticationMethodFormException constructor.
     */
    public function __construct()
    {
        $this->message = 'Zikula Authentication Method login forms are required to contain a rememberme field.';
    }
}
