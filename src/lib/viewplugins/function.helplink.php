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
 * Zikula_View function to create help link.
 *
 * This function creates a help link from some parameters.
 *
 * Available parameters:
 *   - filename: name of file, defaults to 'help.txt'.
 *   - anchor:   anchor marker.
 *   - popup:    opens the help file in a new window using javascript.
 *   - width:    width of the window if newwindow is set, default 600.
 *   - height:   height of the window if newwindow is set, default 400.
 *   - title:    name of the new window if newwindow is set, default is modulename.
 *   - class:    class for use in the <a> tag.
 *   - assign:   if set, the results (array('url', 'link') are assigned to the corresponding variable instead of printed out.
 *
 * Example
 * {helplink popup='1' width='400' height='300' title='help.txt'}
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the Zikula_View object.
 *
 * @return string|void
 */
function smarty_function_helplink($params, Zikula_View $view)
{
    $userLang = ZLanguage::transformFS(ZLanguage::getLanguageCode());
    $systemLang = System::getVar('language_i18n');

    $title = (isset($params['title'])) ? $params['title'] : 'Help';
    $fileName = (isset($params['filename'])) ? $params['filename'] : 'help.txt';
    $chapter = (isset($params['anchor'])) ? '#' . $params['anchor'] : '';
    $class = (isset($params['class'])) ? $params['class'] : null;
    $width = (isset($params['width'])) ? $params['width'] : 600;
    $height = (isset($params['height'])) ? $params['height'] : 400;
    $popup = (isset($params['popup'])) ? true : false;
    $modname = $view->getModuleName();

    $base = ModUtil::getModuleBaseDir($modname) . "/$modname/docs";
    $paths = array(
            "$base/$userLang/$fileName",
            "$base/$systemLang/$fileName",
            "$base/en/$fileName",
    );

    $found = false;
    foreach ($paths as $path) {
        if (is_readable($path)) {
            $found = true;
            $contents = StringUtil::getMarkdownExtraParser()->transform(file_get_contents($path));
            $url = $path . $chapter;
            break;
        }
    }

    if (!$found) {
        //$view->trigger_error(__f('Helpfile %s not found', $fileName));
        return;
    }

    $linkID = DataUtil::formatForDisplay(strtolower('manuallink_' . $modname));
    $class = !empty($class) ? "class=\"$class\"" : '';

    if ($popup) {
        PageUtil::addVar('javascript', 'zikula.ui');
        $link = array();
        $link[] = "<a id=\"{$linkID}\" {$class} href=\"#{$linkID}_content\" title=\"{$title}\">" . DataUtil::formatForDisplayHTML($title) . "</a>";
        $link[] = "<div id=\"{$linkID}_content\" style=\"display: none;\">{$contents}</div>";
        $link[] = "<script type=\"text/javascript\">var $linkID = new Zikula.UI.Window($('$linkID'),{resizable: true, width: $width, height: $height})</script>";
        $link = implode("\n", $link);
    } else {
        $link = "<a id=\"{$linkID}\" {$class} href=\"" . DataUtil::formatForDisplay($url) . "\" title=\"{$title}\">" . DataUtil::formatForDisplayHTML($title) . "</a>";
    }

    if (isset($params['assign'])) {
        $ret = array('url' => $url, 'link' => $link);
        $view->assign($params['assign'], $ret);
        return;
    } else {
        return $link;
    }
}