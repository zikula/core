<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Smarty function return and unset an array field if set
 *
 * @param        array       $params      All attributes passed to this function from the template
 * @param        object      &$smarty     Reference to the Smarty object
 * @param        array       string       The name of the array template variable
 * @param        field       string       The string name of the array field that we wish to pop and unset
 * @param        unset       boolean      The flag to control if unset the field after extraction or not
 * @param        assign      string       The variable to assign the result to (optional)
 * @return       bool                     Wheather or not the array subscript is set
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
