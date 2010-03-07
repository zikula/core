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
 * assign_concat
 *
 * @param   1..10           the 1st through 10th value we wish to assign
 * @param   name        the variable name we wish to assign
 * @param   html        wether or not to pnVarPrepHTMLDisplay the value
 */
function smarty_function_assign_concat($params, &$smarty)
{
    if (!$params['name']) {
        $smarty->trigger_error(__f('Invalid %1$s passed to %2$s.', array('name', 'assign_concat')));
        return false;
    }

    $txt = '';
    for ($i=1; $i<10; $i++) {
        $txt .= isset($params[$i]) ? $params[$i] : '';
    }

    if (isset($params['html']) && $params['html']) {
        $smarty->assign($params['name'], DataUtil::formatForDisplayHTML($txt));
    } else {
        $smarty->assign($params['name'], $txt);
    }
    return;
}
