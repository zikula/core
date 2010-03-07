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
 * assign an array key to the specified value
 *
 * @param   array           the array we wish to get an element from
 * @param   key         the array key we wish to retrieve
 * @param   assign          the smarty variable to assign the result to
 */
function smarty_function_assign_arrayval ($params, &$smarty)
{
    $array  = isset($params['array'])  ? $params['array']  : array();
    $key    = isset($params['key'])    ? $params['key']    : '';
    $assign = isset($params['assign']) ? $params['assign'] : $key;

    $val = isset($array[$key]) ? $array[$key] : null;
    $smarty->assign($assign, $val);
}