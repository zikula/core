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
 * array_size
 *
 * @param    name        the variable name we wish to assign
 * @param    value        the value we wish to assign to the named variable
 * @param    html        wether or not to pnVarPrepHTMLDisplay the value
 */
function smarty_function_array_size($params, &$smarty)
{
    $val = 0;
    if (is_array($params['array'])) {
        $val = count($params['array']);
    }

    if ($params['assign']) {
        $smarty->assign($params['assign'], $val);
    } else {
        return $val;
    }
    return;
}