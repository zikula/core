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
 * Smarty function to obtain the a block variable
 *
 * Note: If the name parameter is not set then the assign parameter must be set since there is an array of
 * block variables available.
 *
 * Available parameters:
 *   - bid: the block id
 *   - name: If set the name of the parameter to get otherwise the entire block array is assigned to the template
 *   - assign: If set, the results are assigned to the corresponding variable instead of printed out
 *
 * @param        array       $params      All attributes passed to this function from the template
 * @param        object      &$smarty     Reference to the Smarty object
 * @return       string      the variables content
 */
function smarty_function_blockgetinfo($params, &$smarty)
{
    $bid    = isset($params['bid'])    ? (int)$params['bid'] : 0;
    $name   = isset($params['name'])   ? $params['name']     : null;
    $assign = isset($params['assign']) ? $params['assign']   : null;

    if (!$bid) {
        $smarty->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('pnblockgetinfo', 'bid')));
    }

    // get the block info array
    $blockinfo = pnBlockGetInfo($bid);

    if ($name) {
        if ($assign) {
            $smarty->assign($assign, $blockinfo[$name]);
        } else {
            return $blockinfo[$name];
        }
    } else {
        // handle the full blockinfo array
        if ($assign) {
            $smarty->assign($assign, $blockinfo);
        } else {
            $smarty->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified to get the full block information.', array('pnblockgetinfo', 'assign')));
        }
    }

    return;
}
