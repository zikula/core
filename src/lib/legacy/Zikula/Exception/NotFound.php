<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Zikula_Exception_NotFound class.
 *
 * @deprecated since 1.4.0
 * @see NotFoundHttpException
 */
class Zikula_Exception_NotFound extends NotFoundHttpException
{
    public function __construct($message = null, \Exception $previous = null, $code = 0)
    {
        parent::__construct($message, $previous, $code);
        @trigger_error('This exception is deprecated, please use NotFoundHttpException instead.', E_USER_DEPRECATED);
    }
}
