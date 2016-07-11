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
 * ParameterBag is a container for key/value pairs.
 * @deprecated as of Core 1.4.0
 */
class Zikula_Bag_ParameterBag extends \Symfony\Component\HttpFoundation\ParameterBag
{
    /**
     * Filter key.
     * @deprecated as of Core 1.4.0
     * @see \Symfony\Component\HttpFoundation\ParameterBag::filter
     *
     * @param string $key     Key.
     * @param mixed  $default Default = null.
     * @param bool   $deep    Default = false.
     * @param int    $filter  FILTER_* constant.
     * @param mixed  $options Filter options.
     *
     * @see http://php.net/manual/en/function.filter-var.php
     *
     * @return mixed
     */
    public function filter($key, $default = null, $deep = false, $filter = FILTER_DEFAULT, $options = [])
    {
        if (func_num_args() > 2) {
            if (is_bool(func_get_arg(2))) {
                // usage is compatible with normal ParameterBag
                $deep = func_get_arg(2);
                $filter = (func_num_args() >= 4) && (func_get_arg(3) !== false) ? func_get_arg(3) : FILTER_DEFAULT;
                $options = (func_num_args() == 5) && (func_get_arg(4) !== false) ? func_get_arg(4) : [];
            } else {
                // using old signature - third param exists and is a constant, not a bool
                LogUtil::log('The method signature for filter() has changed. See \Symfony\Component\HttpFoundation\ParameterBag::filter().', E_USER_DEPRECATED);
                $filter = (func_num_args() >= 3) && (func_get_arg(2) !== false) ? func_get_arg(2) : FILTER_DEFAULT;
                $options = (func_num_args() >= 4) && (func_get_arg(3) !== false) ? func_get_arg(3) : [];
            }
        }

        return parent::filter($key, $default, $filter, $options, $deep);
    }
}
