<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Url class.
 *
 * @deprecated since 1.4.0
 * @see Zikula\Core\ModUrl
 */
class Zikula_ModUrl extends Zikula\Core\ModUrl
{
    public function __construct($application, $controller, $action, $language, array $args = [], $fragment = null)
    {
        @trigger_error('ModUrl is deprecated, please use Symfony routing instead.', E_USER_DEPRECATED);

        parent::__construct($application, $controller, $action, $language, $args, $fragment);
    }
}
