<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula_View
 * @subpackage Template_Plugins
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Assign a template variable with the value found in an array element at the specified key.
 *
 * Available attributes:
 *  - array     (array)         The array template variable in which to retrieve the value
 *  - key       (string|int)    The key into the specified array where the value is to be retrieved from
 *  - assign    (string)        The name of the template variable to assign the value to (required)
 *
 * Examples:
 *
 *  Assign the template variable $myVar with the value found in the template
 *  variable $myArray['myKey']:
 *
 *  <samp>{assign_arrayval array=$myArray key='myKey' assign='myVar'}</samp>
 *
 *  Assign the template variable $myVar with the value found in the template
 *  variable $myArray[3]:
 *
 *  <samp>{assign_arrayval array=$myArray key=3 assign='myVar'}</samp>
 *
 *  In the following example, assume the template variable $myArray[4] is not
 *  set (isset would return false). In this case $myVar is set to null:
 *
 *  <samp>{assign_arrayval array=$myArray key=4 assign='myVar'}</samp>
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the {@link Zikula_View} object.
 *
 * @return void
 */
function smarty_function_assign_arrayval($params, Zikula_View $view)
{
    LogUtil::log(__f('Warning! Template plugin {%1$s} is deprecated, please use {%2$s} instead.', array('assign_arrayval key="X" ...', 'array_field field="X" ...')), E_USER_DEPRECATED);

    $array = isset($params['array']) ? $params['array'] : array();
    $key = isset($params['key']) ? $params['key'] : '';
    $assign = isset($params['assign']) ? $params['assign'] : $key;

    $val = isset($array[$key]) ? $array[$key] : null;
    $view->assign($assign, $val);
}
