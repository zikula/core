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
 * Zikula_View function to load Zikula_tree.
 *
 * Example:
 * {tree $menuArray=$your_content imagesDir='yout/path/to/images/'}
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the Zikula_View object.
 *
 * @return string The results of the module function
 */
function smarty_function_tree($params, Zikula_View $view)
{
    $menuString = isset($params['menustring']) ? $params['menustring'] : null;
    $menuArray  = isset($params['menuarray'])  ? $params['menuarray']  : null;
    $treeArray  = isset($params['treearray'])  ? $params['treearray']  : null;
    $config     = isset($params['config'])     ? $params['config']     : array();

    if (!isset($menuString) && !isset($menuArray) && !isset($treeArray)) {
        $view->trigger_error(__f('Error! in %1$s: %2$s, %3$s or %4$s parameter must be specified.', array('smarty_function_tree', 'menustring', 'menuarray','treearray')));

        return false;
    }
    unset($params['menustring']);
    unset($params['menuarray']);
    unset($params['treearray']);
    unset($params['config']);
    $config = array_merge($config,(array)$params);

    $tree = new Zikula_Tree($config);
    if (isset($treeArray)) {
        $tree->setTreeData($treeArray);
    } elseif (isset($menuArray)) {
        $tree->loadArrayData($menuArray);
    } else {
        $tree->loadStringData($menuString);
    }
    if (isset($params['assign'])) {
        $view->assign($params['assign'],$tree->getHTML());
    } else {
        return $tree->getHTML();
    }
}

