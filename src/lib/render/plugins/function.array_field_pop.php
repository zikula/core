<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2.1 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Smarty function return and unset an array field if set.
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
 * @param array  $params         All attributes passed to this function from the template.
 * @param Smarty &$smarty        Reference to the {@link Renderer} object.
 * @param string $params[assign] The template variable to assign the result to (required).
 *
 * @return null The value of the specified array element is return
 *              in the specified template variable if it is set,
 *              otherwise the template variable is set to false; no output to the template.
 */
function smarty_function_array_field_pop($params, &$smarty)
{
    $array       = isset($smarty->_tpl_vars[$params['array']]);
    $field       = isset($params['field'])   ? $params['field']   : null;
    $unset       = isset($params['unset'])   ? $params['unset']   : false;
    $assign      = isset($params['assign'])  ? $params['assign']  : null;

    if (!$array) {
        $smarty->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('array_field_pop', 'array')));
        return false;
    }

    if (!is_array($smarty->_tpl_vars[$params['array']])) {
        $smarty->trigger_error(__f('Non-array passed to %s.', 'array_field_pop'));
        return false;
    }

    if ($field === null) {
        $smarty->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('array_field_pop', 'field')));
        return false;
    }

    $result = false;
    if (isset($smarty->_tpl_vars[$params['array']][$field])) {
        $result = $smarty->_tpl_vars[$params['array']][$field];
        if ($unset) {
            unset($smarty->_tpl_vars[$params['array']][$field]);
        }
    }

    if ($assign) {
        $smarty->assign($assign, $result);
    } else {
        $smarty->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified to get the required field.', array('array_field_pop', 'assign')));
        return false;
    }
}