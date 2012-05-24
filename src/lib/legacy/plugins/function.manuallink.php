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
 * Zikula_View function to create  manual link.
 *
 * This function creates a manual link from some parameters.
 *
 * Available parameters:
 *   - manual:    name of manual file, manual.html if not set
 *   - chapter:   an anchor in the manual file to jump to
 *   - newwindow: opens the manual in a new window using javascript
 *   - width:     width of the window if newwindow is set, default 600
 *   - height:    height of the window if newwindow is set, default 400
 *   - title:     name of the new window if newwindow is set, default is modulename
 *   - class:     class for use in the <a> tag
 *   - assign:    if set, the results ( array('url', 'link') are assigned to the corresponding variable instead of printed out
 *
 * Example
 * {manuallink newwindow=1 width=400 height=300 title=rtfm }
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the Zikula_View object.
 *
 * @return string|void
 */
function smarty_function_manuallink($params, Zikula_View $view)
{
    LogUtil::log(__f('Warning! Template plugin {%1$s} is deprecated.', array('manuallink')), E_USER_DEPRECATED);

    $userlang = ZLanguage::transformFS(ZLanguage::getLanguageCode());
    $stdlang = System::getVar('language_i18n');

    $title = (isset($params['title'])) ? $params['title'] : 'Manual';
    $manual = (isset($params['manual'])) ? $params['manual'] : 'manual.html';
    $chapter = (isset($params['chapter'])) ? '#' . $params['chapter'] : '';
    $class = (isset($params['class'])) ? 'class="' . $params['class'] . '"' : '';
    $width = (isset($params['width'])) ? $params['width'] : 600;
    $height = (isset($params['height'])) ? $params['height'] : 400;
    $modname = ModUtil::getName();

    $possibleplaces = array("modules/$modname/docs/$userlang/manual/$manual",
            "modules/$modname/docs/$stdlang/manual/$manual",
            "modules/$modname/docs/en/manual/$manual",
            "modules/$modname/docs/$userlang/$manual",
            "modules/$modname/docs/$stdlang/$manual",
            "modules/$modname/docs/lang/en/$manual");
    foreach ($possibleplaces as $possibleplace) {
        if (file_exists($possibleplace)) {
            $url = $possibleplace . $chapter;
            break;
        }
    }
    if (isset($params['newwindow'])) {
        $link = "<a $class href='#' onclick=\"window.open( '" . DataUtil::formatForDisplay($url) . "' , '" . DataUtil::formatForDisplay($modname) . "', 'status=yes,scrollbars=yes,resizable=yes,width=$width,height=$height'); picwin.focus();\">" . DataUtil::formatForDisplayHTML($title) . "</a>";
    } else {
        $link = "<a $class href=\"" . DataUtil::formatForDisplay($url) . "\">" . DataUtil::formatForDisplayHTML($title) . "</a>";
    }

    if (isset($params['assign'])) {
        $ret = array('url' => $url, 'link' => $link);
        $view->assign($params['assign'], $ret);

        return;
    } else {
        return $link;
    }
}
