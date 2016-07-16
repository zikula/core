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
 * Counts all elements in the specified array.
 *
 * Returns the value of the PHP count function, if the specified variable is an array.
 *
 * Available attributes:
 *  - array     (array)     the array to be counted
 *  - assign    (string)    (optional) the name of a template variable to assign the
 *                          count to, instead of returning the value.
 *
 * Examples:
 *
 *  Returns the value 3, if the template variable $myArray is an array containing
 *  three elements:
 *
 *  <samp>{array_size array=$myArray}</samp>
 *
 *  Assigns the value 3 to the template variable $myCount, if the template
 *  variable $myArray is an array containing three elements:
 *
 *  <samp>{array_size array=$myArray assign='myCount'}</samp>
 *
 *  Returns the value 0, if the template variable $myVar is not an array, or
 *  if $myVar is an empty array:
 *
 *  <samp>{array_size array=$myVar}</samp>
 *
 *  Assigns the value 0 to the template variable $myCount, if the template
 *  variable $myVar is not an array or is an empty array:
 *
 *  <samp>{array_size array=$myVar assign='myCount'}</samp>
 *
 * @param array       $params All attributes passed to this function from the template
 * @param Zikula_View $view   Reference to the {@link Zikula_View} object
 *
 * @return mixed The number of elements in the specified array, or 0 (zero)
 *               if the array is empty, or 0 (zero) if the specified
 *               template variable is not an array; returns null if the
 *               assign parameter is specified
 */
function smarty_function_array_size($params, Zikula_View $view)
{
    $val = 0;
    if (is_array($params['array'])) {
        $val = count($params['array']);
    }

    if ($params['assign']) {
        $view->assign($params['assign'], $val);
    } else {
        return $val;
    }
}
