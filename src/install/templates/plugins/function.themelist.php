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
 * Smarty function to display a drop down list of themes
 *
 * Available parameters:
 *   - assign:   If set, the results are assigned to the corresponding variable instead of printed out
 *   - selected: Selected value
 *
 * Example
 *   <!--[themelist selected=ExtraLite]-->
 *
 * @param        array       $params      All attributes passed to this function from the template
 * @param        object      &$smarty     Reference to the Smarty object
 * @return       string      the value of the last status message posted, or void if no status message exists
 */
function smarty_function_themelist($params, &$smarty)
{
    $handle = opendir('themes');
    while (false !== ($f = readdir($handle))) {
        if (is_dir("themes/$f") && file_exists("themes/$f/images/preview_medium.png")) {
            $themelist[$f] = "themes/$f/images/preview_medium.png";
        }
    }
    ksort($themelist);
    closedir ($handle);

    $themestring = '<table id="themeselector" width="100%">';
    $i=1;
    foreach ($themelist as $theme => $imagepath) {
        $themestring .= '<tr>';
        $themestring .= '<td class="themename"><label for="theme-' . DataUtil::formatForDisplay($theme). '">' . DataUtil::formatForDisplay($theme) . '</label></td>';
        $themestring .= '<td class="themeselect"><input id="theme-' . DataUtil::formatForDisplay($theme) . '" type="radio" name="defaulttheme" value="' . DataUtil::formatForDisplay($theme) . '"';
        if ($i == 1) $themestring .= ' checked="checked"';
        $themestring .= ' /></td>';
        $themestring .= '<td class="themepreview"><img src="' . DataUtil::formatForDisplay($imagepath) . '" alt="" /></td>';
        $themestring .= '</tr>';
        $i++;
    }
    $themestring .= '</table>';

    if (isset($params['assign'])) {
        $smarty->assign($params['assign'], $themestring);
    } else {
        return $themestring;
    }
}
