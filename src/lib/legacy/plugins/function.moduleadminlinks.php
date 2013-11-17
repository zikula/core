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
 * Zikula_View function to display admin links for a module.
 *
 * Example:
 * {moduleadminlinks modname=Example start="[" end="]" seperator="|" class="z-menuitem-title"}
 *
 * Available parameters:
 *   - modname   Module name to display links for.
 *   - start     Start string (optional).
 *   - end       End string (optional).
 *   - seperator Link seperator (optional).
 *   - class     CSS class (optional).
 *
 * @param array       $params  All attributes passed to this function from the template.
 * @param Zikula_View $view    Reference to the Zikula_View object.
 *
 * @return string A formatted string containing navigation for the module admin panel.
 */
function smarty_function_moduleadminlinks($params, $view)
{
    LogUtil::log(__f('Warning! Template plugin {%1$s} is deprecated, please use {%2$s} instead.', array('moduleadminlinks', 'modulelinks')), E_USER_DEPRECATED);

    // set some defaults
    $start     = isset($params['start'])    ? $params['start']    : '[';
    $end       = isset($params['end'])      ? $params['end']      : ']';
    $seperator = isset($params['seperator'])? $params['seperator']: '|';
    $class     = isset($params['class'])    ? $params['class']    : 'z-menuitem-title';

    $modname = $params['modname'];
    unset ($params['modname']);

    if (!isset($modname) || !ModUtil::available($modname)) {
        $modname = ModUtil::getName();
    }

    // check our module name
    if (!ModUtil::available($modname)) {
        $view->trigger_error('moduleadminlinks: '.__f("Error! The '%s' module is not available.", DataUtil::formatForDisplay($modname)));

        return false;
    }

    // get the links from the module API
    $links = ModUtil::apiFunc($modname, 'admin', 'getlinks', $params);

    // establish some useful count vars
    $linkcount = count($links);

    $adminlinks = "<span class=\"$class\">$start ";
    foreach ($links as $key => $link) {
        $id = '';
        if (isset($link['id'])) {
            $id = 'id="' . $link['id'] . '"';
        }
        if (!isset($link['title'])) {
            $link['title'] = $link['text'];
        }
        if (isset($link['disabled']) && $link['disabled'] == true) {
            $adminlinks .= "<span $id>" . '<a class="z-disabledadminlink" title="' . DataUtil::formatForDisplay($link['title']) . '">' . DataUtil::formatForDisplay($link['text']) . '</a> ';
        } else {
            $adminlinks .= "<span $id><a href=\"" . DataUtil::formatForDisplay($link['url']) . '" title="' . DataUtil::formatForDisplay($link['title']) . '">' . DataUtil::formatForDisplay($link['text']) . '</a> ';
        }
        if ($key == $linkcount-1) {
            $adminlinks .= '</span>';
            continue;
        }
        // linebreak
        if (isset($link['linebreak']) && $link['linebreak'] == true) {
            $adminlinks .= "</span>\n ";
            $adminlinks .= "$end</span><br /><span class=\"$class\">$start ";
        } else {
            $adminlinks .= "$seperator</span>\n ";
        }
    }
    $adminlinks .= "$end</span>\n";

    return $adminlinks;
}
