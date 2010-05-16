<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv2.1 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Renderes and displays a single Zikula block by blockinfo array or block id.
 *
 * Available attributes:
 *  - module    (string)    The internal name of the module that defines the block.
 *  - blockname (string)    The internal name of the block.
 *  - block     (int|array) Either the integer block id (bid) of the block, or
 *                          an array containing the blockinfo for the block.
 *  - assign    (string)    If set, the results are assigned to the corresponding
 *                          template variable instead of being returned to the template (optional)
 *
 * @param array  $params  All attributes passed to this function from the template.
 * @param Smarty &$smarty Reference to the {@link Renderer} object.
 *
 * @return string The rendered output of the specified block.
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