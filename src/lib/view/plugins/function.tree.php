<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2 (or at your option, any later version).
 * @package Zikula_View
 * @subpackage Template_Plugins
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Smarty function to include the relevant files for the phpLayersMenu and pass a previously generated menu string to phpLayersMenu
 *
 * @param array  $params  All attributes passed to this function from the template.
 * @param Smarty &$smarty Reference to the Smarty object.
 *
 * @return string The results of the module function
 */
function smarty_function_tree ($params, &$smarty)
{
    $menuString = isset($params['menustring']) ? $params['menustring'] : null;
    $menuArray = isset($params['menuarray']) ? $params['menuarray'] : null;
    $config    = isset($params['config'])    ? $params['config']    : array();

    if (!isset($menuString) && !isset($menuArray)) {
        $smarty->trigger_error(__f('Error! in %1$s: %2$s or %3$s parameter must be specified.', array('smarty_function_tree', 'menustring', 'menuarray')));
        return false;
    }
    unset($params['menuString']);
    unset($params['menuArray']);
    unset($params['config']);
    $config = array_merge($config,(array)$params);

    $tree = new Zikula_Tree($config);
    if (isset($menuArray)) {
        $tree->loadArrayData($menuArray);
    } else {
        $tree->loadStringData($menuString);
    }
    if (isset($params['assign'])) {
        $smarty->assign($params['assign'],$tree->getHTML());
    } else {
        return $tree->getHTML();
    }
}

