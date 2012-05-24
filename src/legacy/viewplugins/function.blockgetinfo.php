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
 * Obtain the value of one block variable or all block variables for a specified block.
 *
 * Note: If the name of the block variable is not set, then the assign parameter
 * must be set since an array of block variables will be returned.
 *
 * Available attributes:
 *   - bid      (numeric)   The block id
 *   - name     (string)    The name of the block variable to get, otherwise the
 *                          entire block array is assigned is returned.
 *                          (required, if the assign attribute is not specified,
 *                          otherwise, optional)
 *   - assign   (string)    The name of the template variable to which the value
 *                          is assigned, instead of being output to the template.
 *                          (optional if the name attribute is set, otherwise
 *                          required)
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the {@link Zikula_View} object.
 *
 * @return mixed the value of the block variable specified by the name attribute,
 *               or an array containing the full block information.
 */
function smarty_function_blockgetinfo($params, Zikula_View $view)
{
    $bid    = isset($params['bid'])    ? (int)$params['bid'] : 0;
    $name   = isset($params['name'])   ? $params['name']     : null;
    $assign = isset($params['assign']) ? $params['assign']   : null;

    if (!$bid) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('blockgetinfo', 'bid')));
    }

    // get the block info array
    $blockinfo = BlockUtil::getBlockInfo($bid);

    if ($name) {
        if ($assign) {
            $view->assign($assign, $blockinfo[$name]);
        } else {
            return $blockinfo[$name];
        }
    } else {
        // handle the full blockinfo array
        if ($assign) {
            $view->assign($assign, $blockinfo);
        } else {
            $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified to get the full block information.', array('pnblockgetinfo', 'assign')));
        }
    }

    return;
}
