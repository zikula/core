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
 * Check if an array element (subscript) is set.
 *
 * Available attributes:
 *  - array         (array)     an array template variable
 *  - field         (string)    the value of a key in the array specified above
 *  - returnValue   (bool|int)  if set, then the contents of the array element
 *                              $array[$field] is returned if it is set, otherwise false is returned
 *  - assign        (string)    (optional) if provided, a template variable with
 *                              the specified name is set with the return value,
 *                              instead of returning the value to the template
 *
 * Examples:
 *
 *  Return true to the template if the template variable $myarray['arraykey']
 *  is set, otherwise return false to the template:
 *
 *  <samp>{array_field_isset array=$myarray field='arraykey'}</samp>
 *
 *  Return the value of the template variable $myarray['arraykey'] to the
 *  template if it is set, otherwise return false to the template:
 *
 *  <samp>{array_field_isset array=$myarray field='arraykey' returnValue=1}</samp>
 *
 *  Assign true to the template variable $myValue if the template variable
 *  $myarray['arraykey'] is set, otherwise set $myValue to false:
 *
 *  <samp>{array_field_isset array=$myarray field='arraykey' assign='myValue'}</samp>
 *
 *  Assign the value of the template variable $myarray['arraykey'] to the
 *  template variable $myValue if it is set, otherwise assign false to $myValue:
 *
 *  <samp>{array_field_isset array=$myarray field='arraykey' returnValue=1 assign='myValue'}</samp>
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the {@link Zikula_View} object.
 *
 * @return boolean|mixed if returnValue is not set, then returns true if the array
 *                       element is set, otherwise false; if returnValue is set,
 *                       then returns the value of the array element if it is set,
 *                       otherwise false.
 */
function smarty_function_array_field_isset($params, Zikula_View $view)
{
    LogUtil::log(__f('Warning! Template plugin {%1$s} is deprecated, please use {%2$s} instead.', array('array_field_isset returnValue=1 ...', 'array_field ...')), E_USER_DEPRECATED);

    $array       = isset($params['array'])       ? $params['array']        : null;
    $field       = isset($params['field'])       ? $params['field']        : null;
    $returnValue = isset($params['returnValue']) ? $params['returnValue']  : null;
    $assign      = isset($params['assign'])      ? $params['assign']       : null;

    if ($array === null) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('array_field_isset', 'array')));

        return false;
    }

    if ($field === null) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('array_field_isset', 'field')));

        return false;
    }

    $result = isset($array[$field]);
    if ($result && $returnValue) {
        $result = $array[$field];
    }

    if ($assign) {
        $view->assign($assign, $result);
    } else {
        return $result;
    }
}
