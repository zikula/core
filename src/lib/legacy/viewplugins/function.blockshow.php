<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
 * @param array       $params All attributes passed to this function from the template
 * @param Zikula_View $view   Reference to the {@link Zikula_View} object
 *
 * @return string The rendered output of the specified block
 */
function smarty_function_blockshow($params, Zikula_View $view)
{
    $module    = isset($params['module'])    ? $params['module']    : null;
    $blockname = isset($params['blockname']) ? $params['blockname'] : null;
    $block     = isset($params['block'])     ? $params['block']     : null;
    $position  = isset($params['position'])  ? $params['position']  : null;
    $assign    = isset($params['assign'])    ? $params['assign']    : null;

    if (!$module) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', ['blockshow', 'module']));

        return;
    }

    if (!$blockname) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', ['blockshow', 'blockname']));

        return;
    }

    if (!$block) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', ['blockshow', 'block']));

        return;
    }

    if (!$position) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', ['blockshow', 'position']));

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
