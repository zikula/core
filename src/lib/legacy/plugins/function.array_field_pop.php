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
 * Zikula_View function return and unset an array field if set.
 *
 * Available attributes:
 *  - array     (string)    The name of an array template variable
 *  - field     (string)    The name of an array key in the array template variable above
 *  - unset     (bool|int)  If true, the array element will be unset, if false the
 * \                        array element will remain unchanged
 *  - assign    (string)    The name of a template variable that the value of
 *                          $array['field'] will be assigned to
 *
 * Examples:
 *
 *  Assign the value of the template variable $myarray['arraykey'] to the
 *  template variable $myValue if it is set, otherwise assign false to $myValue.
 *  The template variable $myarray['arraykey'] is NOT unset:
 *
 *  <samp>{array_field_pop array='myarray' field='arraykey' assign='myValue'}</samp>
 *
 *  Assign the value of the template variable $myarray['arraykey'] to the
 *  template variable $myValue if it is set, otherwise assign false to $myValue.
 *  The template variable $myarray['arraykey'] IS unset:
 *
 *  <samp>{array_field_pop array='myarray' field='arraykey' unset=1 assign='myValue'}</samp>
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the {@link Zikula_View} object.
 *
 * @return null The value of the specified array element is return
 *              in the specified template variable if it is set,
 *              otherwise the template variable is set to false; no output to the template.
 */
function smarty_function_array_field_pop($params, Zikula_View $view)
{
    LogUtil::log(__f('Warning! Template plugin {%1$s} is deprecated, please use {%2$s} instead.', array('array_field_pop', 'array_pop')), E_USER_DEPRECATED);

    $array       = isset($view->_tpl_vars[$params['array']]);
    $field       = isset($params['field'])   ? $params['field']   : null;
    $unset       = isset($params['unset'])   ? $params['unset']   : false;
    $assign      = isset($params['assign'])  ? $params['assign']  : null;

    if (!$array) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('array_field_pop', 'array')));

        return false;
    }

    if (!is_array($view->_tpl_vars[$params['array']])) {
        $view->trigger_error(__f('Non-array passed to %s.', 'array_field_pop'));

        return false;
    }

    if ($field === null) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('array_field_pop', 'field')));

        return false;
    }

    $result = false;
    if (isset($view->_tpl_vars[$params['array']][$field])) {
        $result = $view->_tpl_vars[$params['array']][$field];
        if ($unset) {
            unset($view->_tpl_vars[$params['array']][$field]);
        }
    }

    if ($assign) {
        $view->assign($assign, $result);
    } else {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified to get the required field.', array('array_field_pop', 'assign')));

        return false;
    }
}
