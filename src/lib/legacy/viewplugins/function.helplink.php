<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Zikula_View function to create help link.
 *
 * This function creates a help link.
 *
 * To make the link appear as a button, wrap it in a div or span with a class
 * of z-buttons.
 *
 * Available parameters:
 *   - filename:     name of file, defaults to 'help.txt'.
 *   - anchor:       anchor marker.
 *   - popup:        opens the help file in a new window using javascript.
 *   - width:        width of the window if newwindow is set, default 600.
 *   - height:       height of the window if newwindow is set, default 400.
 *   - title:        name of the new window if new window is set, default is 'Help'.
 *   - link_contents the text for the link (between the <a> and </a> tags); optional, if not specified, then the title is used.
 *   - icon_type      an optional icon type to include in the link, separated from the link_contents (or title) by a non-breaking space; equivalent to the type parameter from the {icon} template function
 *   - icon_size      the size of the icon (e.g., extrasmall); optional if link_icon_type is specified, defaults to 'extrasmall', otherwise ignored;
 *                      equivalent to the size parameter of the {icon} template function
 *   - icon_width    the width of the icon in pixels; optional if link_icon_type is specified, if not specified, then obtained from size, otherwise ignored;
 *                      equivalent to the width parameter of the {icon} template function
 *   - icon_height   the height of the icon in pixels; optional if link_icon_type is specified, if not specified, then obtained from size, otherwise ignored;
 *                      equivalent to the height parameter of the {icon} template function
 *   - icon_alt      the alternate text for the icon, used for the alt param of the {icon} template function; optional if link_icon_type is specified,
 *                      defaults to an empty string, otherwise ignored
 *   - icon_title    the title text for the icon, used for the title param of the {icon} template function; optional if link_icon_type is specified,
 *                      defaults to an empty string, otherwise ignored
 *   - icon_optional if true and the icon image is not found then an error will not be returned, used for the optinal param of the {icon} template
 *                      function; optional if link_icon_type is specified, defaults to false, otherwise ignored
 *   - icon_default  the full path to an image file to use if the icon is not found, used for the default param of the {icon} template
 *                      function; optional if link_icon_type is specified, defaults to an empty string, otherwise ignored
 *   - icon_right    if true, then the icon is placed on the right side of the link text (the text from either link_contents or title); optional,
 *                      defaults to false (placing the icon on the left side of the text)
 *   - icon_*        all remaining parameters with a "icon_" prefix are passed to the {icon} function and subsequently to the <img> tag, except for
 *                      'icon_assign' which is completely ignored; optional if link_icon_type is specified, otherwise ignored
 *   - class:        class for use in the <a> tag.
 *   - assign:       if set, the results (array('url', 'link') are assigned to the corresponding variable instead of printed out.
 *
 * Example: A pop-up help window with a width of 400 and a height of 300, containing the contents of help.txt, and a title of 'Help'
 * {helplink popup='1' width='400' height='300' filename='help.txt' title='Help'}
 *
 * Example: The same as above, except displayed as a button with an icon image placed on the left side of the text 'Help' separated by a non-breaking space.
 *          The image does not have either alternate text nor a title.
 * <div class="z-buttons">
 *     {helplink popup='1' width='400' height='300' filename='help.txt' title='Help' icon_type='help' icon_size='extrasmall'}
 * </div>
 *
 * Example: The same as above, except the icon's <img> tag will contain a class attrbute with the value "my_class"
 * <div class="z-buttons">
 *     {helplink popup='1' width='400' height='300' filename='help.txt' title='Help' icon_type='help' icon_size='extrasmall' icon_class='my_class'}
 * </div>
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

    $iconParams = array();
    if (!empty($params) && is_array($params)) {
        foreach ($params as $key => $value) {
            if ((strpos($key, 'icon_') === 0) && (strlen($key) > 5)) {
                $iconParams[substr($key, 5)] = $value;
                unset($params[$key]);
            }
        }
    }

    if (!empty($iconParams) && isset($iconParams['type'])) {
        // We need to make sure the icon template function is available so we can call it.
        require_once $view->_get_plugin_filepath('function', 'icon');

        $iconRightSide = false;
        if (isset($iconParams['right'])) {
            $iconRightSide = (bool)$iconParams['right'];
            unset($iconParams['right']);
        }

        if (isset($iconParams['assign'])) {
            // We cannot use the assign parameter with the icon function in this context.
            unset($iconParams['assign']);
        }
    } else {
        $iconParams = false;
        $iconRightSide = false;
    }

    $title = (isset($params['title'])) ? $params['title'] : 'Help';
    $linkContents = (isset($params['link_contents'])) ? $params['link_contents'] : $title;
    $fileName = (isset($params['filename'])) ? $params['filename'] : 'help.txt';
    $chapter = (isset($params['anchor'])) ? '#' . $params['anchor'] : '';
    $class = (isset($params['class'])) ? $params['class'] : null;
    $width = (isset($params['width'])) ? $params['width'] : 600;
    $height = (isset($params['height'])) ? $params['height'] : 400;
    $popup = (isset($params['popup'])) ? true : false;
    $modname = $view->getModuleName();
    $linkID = (isset($params['linkid'])) ? $params['linkid'] : DataUtil::formatForDisplay(strtolower('manuallink_' . $modname . '_' . hash('md5', serialize($params))));

    $paths = array();
    $module = ModUtil::getModule($modname);
    if ($module) {
        $base = $module->getPath();
        $paths[] = "$base/Resources/docs/$userLang/$fileName";
        $paths[] = "$base/Resources/docs/$systemLang/$fileName";
        $paths[] = "$base/Resources/docs/en/$fileName";
    }
    $base = ModUtil::getModuleBaseDir($modname) . "/$modname/docs";
    $paths[] = "$base/$userLang/$fileName";
    $paths[] = "$base/docs/$systemLang/$fileName";
    $paths[] = "$base/en/$fileName";

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

    $linkContents = DataUtil::formatForDisplayHTML($linkContents);
    if ($iconParams) {
        $iconContents = smarty_function_icon($iconParams, $view);

        if (isset($iconContents) && is_string($iconContents) && !empty($iconContents)) {
            if ($iconRightSide) {
                $linkContents = $linkContents . '&nbsp;' . $iconContents;
            } else {
                $linkContents = $iconContents . '&nbsp;' . $linkContents;
            }
        } else {
            //$view->trigger_error(__f('Icon for type '%s' not found', $iconParams['type']));
            return;
        }
    }

    $class = !empty($class) ? "class=\"$class\"" : '';

    if ($popup) {
        PageUtil::addVar('javascript', 'zikula.ui');
        $link = array();
        $link[] = "<a id=\"{$linkID}\" {$class} data-toggle=\"modal\" data-target=\"#{$linkID}_content\" title=\"{$title}\">" . $linkContents . "</a>";
        $link[] = '<div class="modal fade" id="'.$linkID.'_content" tabindex="-1" role="dialog" aria-labelledby="'.$linkID.'_label" aria-hidden="true">';
        $link[] = '<div class="modal-dialog">';
        $link[] = '<div class="modal-content">';
        $link[] = '<div class="modal-header">';
        $link[] = '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>';
        $link[] = '<h4 class="modal-title" id="'.$linkID.'_label">'.$title.'</h4>';
        $link[] = '</div>';
        $link[] = '<div class="modal-body">';
        $link[] = $contents;
        $link[] = '</div>';
        $link[] = '</div>';
        $link[] = '</div>';
        $link[] = '</div>';
        $link = implode("\n", $link);
    } else {
        $link = "<a id=\"{$linkID}\" {$class} href=\"" . DataUtil::formatForDisplay($url) . "\" title=\"{$title}\">" . $linkContents . "</a>";
    }

    if (isset($params['assign'])) {
        $ret = array('url' => $url, 'link' => $link);
        $view->assign($params['assign'], $ret);

        return;
    } else {
        return $link;
    }
}
