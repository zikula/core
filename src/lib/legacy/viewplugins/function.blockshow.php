<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Renders and displays a single Zikula block by blockinfo array or block id.
 *
 * Available attributes:
 *  - module    (string)    The internal name of the module that defines the block.
 *  - blockname (string)    The internal name of the block.
 *  - block     (int|array) Either the integer block id (bid) of the block, or
 *                          an array containing the blockinfo for the block.
 *  - position  (string)    The position of the block.
 *  - assign    (string)    If set, the results are assigned to the corresponding
 *                          template variable instead of being returned to the template (optional)
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the {@link Zikula_View} object.
 *
 * @return string The rendered output of the specified block.
 */
function smarty_function_blockshow($params, Zikula_View $view)
{
    $module    = isset($params['module'])    ? $params['module']    : null;
    $blockname = isset($params['blockname']) ? $params['blockname'] : null;
    $block     = isset($params['block'])     ? $params['block']     : null;
    $position  = isset($params['position'])  ? $params['position']  : null;
    $assign    = isset($params['assign'])    ? $params['assign']    : null;

    if (!$module) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('blockshow', 'module')));

        return;
    }

    if (!$blockname) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('blockshow', 'blockname')));

        return;
    }

    if (!$block) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('blockshow', 'id/info')));

        return;
    }

    if (!$position) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('blockshow', 'position')));

        return;
    }

    if (!is_array($block)) {
        $block = BlockUtil::getBlockInfo($block);
    }

    $block['position'] = $position;

    $output = BlockUtil::show($module, $blockname, $block);

    if ($assign) {
        $view->assign($assign, $output);
    } else {
        return $output;
    }
}
