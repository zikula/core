<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
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
     * @see http://php.net/manual/en/function.filter-var.php
     *
     * @return mixed
     */
    public function filter()
    {
        /**
         * Args in order:
         *  key
         *  default = null
         *  deep = false (missing in old signature)
         *  filter = FILTER_DEFAULT
         *  options = array()
         */
        $key = func_get_arg(0);
        $default = func_get_arg(1) === false ? null : func_get_arg(2);
        $deep = false;
        $filter = FILTER_DEFAULT;
        $options = array();

        if (func_num_args() > 2) {
            if (is_bool(func_get_arg(2))) {
                // usage is compatible with normal ParameterBag
                $deep = func_get_arg(2);
                $filter = func_get_arg(3) === false ? FILTER_DEFAULT : func_get_arg(3);
                $options = func_get_arg(4) === false ? array() : func_get_arg(4);
            } else {
                // using old signature - third param exists and is a constant, not a bool
                LogUtil::log('The method signature for filter() has changed. See \Symfony\Component\HttpFoundation\ParameterBag::filter().', E_USER_DEPRECATED);
                $deep = false;
                $filter = func_get_arg(2) === false ? FILTER_DEFAULT : func_get_arg(2);
                $options = func_get_arg(3) === false ? array() : func_get_arg(3);
            }
        }

        return parent::filter($key, $default, $deep, $filter, $options);
    }
}
