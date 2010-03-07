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
 * Smarty function to show a zikula block by blockinfo array or blockid.
 *
 * This function returns a zikula block by blockinfo array or blockid
 *
 * Available parameters:
 *   - module
 *   - blockname
 *   - block
 *   - assign:   If set, the results are assigned to the corresponding variable instead of printed out
 *
 * @param        array       $params      All attributes passed to this function from the template
 * @param        object      &$smarty     Reference to the Smarty object
 * @return       string      the zikula block
 */
function smarty_function_blockshow($params, &$smarty)
{
    $module    = isset($params['module'])    ? $params['module']    : null;
    $blockname = isset($params['blockname']) ? $params['blockname'] : null;
    $block     = isset($params['block'])     ? $params['block']     : null;
    $assign    = isset($params['assign'])    ? $params['assign']    : null;

    if (!$module) {
        $smarty->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('pnblockshow', 'module')));
        return;
    }

    if (!$blockname) {
        $smarty->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('pnblockshow', 'blockname')));
        return;
    }

    if (!$block) {
        $smarty->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('pnblockshow', 'id/info')));
        return;
    }

    if (!is_array($block)) {
        $output = pnBlockShow($module, $blockname, pnBlockGetInfo($block));
    } else {
        $vars   = pnBlockVarsFromContent($block['content']);
        $output = pnBlockShow($module, $blockname, $vars['content']);
    }

    if ($assign) {
        $smarty->assign($assign, $output);
    } else {
        return $output;
    }
}
