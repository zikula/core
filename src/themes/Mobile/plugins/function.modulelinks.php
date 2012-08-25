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
 * Zikula_View function to display menulinks in an unordered list
 *
 * Example
 * {modulelinks data=$links id='listid' class='z-menulinks' itemclass='z-ml-item' first='z-ml-first' last='z-ml-last'}
 *
 * Available parameters:
 *  links     Array with menulinks (text, url, title, id, class, disabled) (optional)
 *  modname   Module name to display links for (optional)
 *  type      Function type where the getlinks-function is located (optional)
 *  menuid    ID for the unordered list (optional)
 *  menuclass Class for the unordered list (optional)
 *  itemclass Array with menulinks (text, url, title, class, disabled) (optional)
 *  first     Class for the first element (optional)
 *  last      Class for the last element (optional)
 *  seperator Link seperator (optional)
 *  class     CSS class (optional).
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the Zikula_View object.
 *
 * @return string A formatted string containing navigation for the module admin panel.
 */
function smarty_function_modulelinks($params, Zikula_View $view)
{
    return 'hallo welt';
    
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
            $view->trigger_error('modulelinks: '.__f("Error! The '%s' module is not available.", DataUtil::formatForDisplay($params['modname'])));
            return false;
        }

        $params['type'] = isset($params['type']) ? $params['type'] : 'admin';

        // get the links from the module API
        $menuLinks = ModUtil::apiFunc($params['modname'], $params['type'], 'getlinks', $params);
    }

    // return if there are no links to print
    if (!$menuLinks) {
        if (isset($params['assign'])) {
            $view->assign($params['assign'], $menuLinks);
        } else {
            return '';
        }
    }

    
    $html = '';

    if (!empty($menuLinks)) {
        $html = '<ul';
        $html .= !empty($menuId) ? ' id="'.$menuId.'"' : '';
        $html .= !empty($menuClass) ? ' class="'.$menuClass.'"' : '';
        $html .= '>';

        $i = 1;
        $size = count($menuLinks);
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
            $attr .= !empty($menuitem['class']) ? ' class="z-iconlink '.$menuitem['class'].'"' : '';

            if (isset($menuitem['disabled']) && $menuitem['disabled'] == true) {
                $html .= '<a '.$attr.'>'.$menuitem['text'].'</a>';
            } elseif (!empty($menuitem['url'])) {
                $html .= '<a href="'.DataUtil::formatForDisplay($menuitem['url']).'"'.$attr.'>'.$menuitem['text'].'</a>';
            } else {
                $html .= '<span'.$attr.'>'.$menuitem['text'].'</span>';
            }
            if (isset($menuitem['links'])) {
                $html .= _smarty_function_modulelinks($i, $menuitem['links']);
            }
            $html .= '</li>';
        }

        $html .= '</ul>';
    }

    if (isset($params['assign'])) {
        $view->assign($params['assign'], $html);
    } else {
        return $html;
    }
}

/**
 * Internal function to render a set of links.
 *
 * @param string $id    ID of the context.
 * @param array  $links Array of links to be rendered.
 *
 * @return string HTML output.
 */
function _smarty_function_modulelinks($id, $links)
{
    PageUtil::addVar('javascript', 'zikula.ui');

    $html = '';
    $html .= '<span id="modcontext' .$id .'" class="z-drop">&nbsp;</span>';
    $html .= "<script type='text/javascript'>
            /* <![CDATA[ */
                var context_modcontext{$id} = new Control.ContextMenu('modcontext{$id}',{
                    leftClick: true,
                    animation: false
                });";
    foreach ($links as $link) {
        $html .= "context_modcontext{$id}.addItem({
                    label: '{$link['text']}',
                    callback: function(){window.location =  '{$link['url']}';}
                });";

    }
    $html .= "/* ]]> */
            </script>";

    return $html;
}
