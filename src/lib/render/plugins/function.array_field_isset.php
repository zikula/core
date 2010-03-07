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
 * Smarty function check if an array subscript is set
 *
 * @param        array       $params      All attributes passed to this function from the template
 * @param        object      &$smarty     Reference to the Smarty object
 * @param        array       $array       The array we wish to check
 * @param        field       string       The string name of the array subscript we wish to check
 * @param        assign      string       The variable to assign the result to (optional)
 * @return       bool                     Wheather or not the array subscript is set
 */
function smarty_function_array_field_isset($params, &$smarty)
{
    $array       = isset($params['array'])       ? $params['array']        : null;
    $field       = isset($params['field'])       ? $params['field']        : null;
    $returnValue = isset($params['returnValue']) ? $params['returnValue']  : null;
    $assign      = isset($params['assign'])      ? $params['assign']       : null;

    if ($array === null) {
        $smarty->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('array_field_isset', 'array')));
        return false;
    }

    if ($field === null) {
        $smarty->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('array_field_isset', 'field')));
        return false;
    }

    $result = isset($array[$field]);
    if ($result && $returnValue) {
        $result = $array[$field];
    }

    if ($assign) {
        $smarty->assign($assign, $result);
    } else {
        return $result;
    }
}
