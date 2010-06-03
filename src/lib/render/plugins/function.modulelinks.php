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
 * Smarty function to display menulinks in an unordered list
 *
 * Example
 * {modulelinks data=$links id='listid' class='z-menulinks' itemclass='z-ml-item' first='z-ml-first' last='z-ml-last'}
 *
 * @param        array       $params      All attributes passed to this function from the template
 * @param        object      &$smarty     Reference to the Smarty object
 * @param        string      $links       array with menulinks (text, url, title, id, class, disabled) (optional)
 * @param        string      $modname     module name to display links for (optional)
 * @param        string      $type        function type where the getlinks-function is located (optional)
 * @param        string      $menuid      ID for the unordered list (optional)
 * @param        string      $menuclass   class for the unordered list (optional)
 * @param        string      $itemclass   array with menulinks (text, url, title, class, disabled) (optional)
 * @param        string      $first       class for the first element (optional)
 * @param        string      $last        class for the last element (optional)
 * @param        string      $seperator   link seperator (optional)
 * @param        string      $class       CSS class (optional)
 * @return       string      a formatted string containing navigation for the module admin panel
 */

function smarty_function_modulelinks($params, &$smarty)
{
    $menuLinks          = isset($params['links'])       ? $params['links'] : '';
    $menuId             = isset($params['menuid'])      ? $params['menuid'] : '';
    $menuClass          = isset($params['menuclass'])   ? $params['menuclass'] : 'z-menulinks';
    $menuItemClass      = isset($params['itemclass'])   ? $params['itemclass'] : '';
    $menuItemFirst      = isset($params['first'])       ? $params['first'] : '';
    $menuItemLast       = isset($params['last'])        ? $params['last'] : '';

    if (empty($menuLinks)) {
        if (!isset($params['modname']) || !ModUtil::available($params['modname'])) {
            $params['modname'] = ModUtil::getName();
        }

        // check our module name
        if (!ModUtil::available($params['modname'])) {
            $smarty->trigger_error('modulelinks: '.__f("Error! The '%s' module is not available.", DataUtil::formatForDisplay($params['modname'])));
            return false;
        }

        $params['type'] = isset($params['type']) ? $params['type'] : 'admin';

        // get the links from the module API
        $menuLinks = ModUtil::apiFunc($params['modname'], $params['type'], 'getlinks', $params);
    }

    $html = '<ul';
    $html .= !empty($menuId) ? ' id="'.$menuId.'"' : '';
    $html .= !empty($menuClass) ? ' class="'.$menuClass.'"' : '';
    $html .= '>';

    $size = count($menuLinks);
    $i = 1;
    foreach ($menuLinks as $menuitem) {
        $class = array();
        $class[] = $size == 1 ? 'z-ml-single' : '';
        $class[] = ($i == 1 && $size > 1) ? $menuItemFirst : '';
        $class[] = ($i == $size && $size > 1) ? $menuItemLast : '';
        $class[] = !empty($menuItemClass) ? $menuItemClass : '';
        $class[] = (isset($menuitem['disabled']) && $menuitem['disabled'] == true) ? 'z-ml-disabled' : '';
        $class = trim(implode(' ', $class));
        $i++;

        $html .= '<li';
        $html .= !empty($menuitem['id']) ? ' id="'.$menuitem['id'].'"' : '';
        $html .= !empty($class) ? ' class="'.$class.'"' : '';
        $html .= '>';
        $attr  = !empty($menuitem['title']) ? ' title="'.$menuitem['title'].'"' : '';
        $attr .= !empty($menuitem['class']) ? ' class="'.$menuitem['class'].'"' : '';

        if (isset($menuitem['disabled']) && $menuitem['disabled'] == true) {
            $html .= '<a '.$attr.'>'.$menuitem['text'].'</a>';
        } elseif (!empty($menuitem['url'])) {
            $html .= '<a href="'.DataUtil::formatForDisplay($menuitem['url']).'"'.$attr.'>'.$menuitem['text'].'</a>';
        } else {
            $html .= '<span'.$attr.'>'.$menuitem['text'].'</span>';
        }
        $html .= '</li>';

    }

    $html .= '</ul>';

    if (isset($params['assign'])) {
        $smarty->assign($params['assign'], $html);
    } else {
        return $html;
    }

}
