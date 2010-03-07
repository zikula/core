<?php
/**
 * pnRender plugin
 *
 * This file is a plugin for pnRender, the Zikula implementation of Smarty
 *
 * @package      Xanthia_Templating_Environment
 * @subpackage   pnRender
 * @version      $Id$
 * @author       The Zikula development team
 * @link         http://www.zikula.org  The Zikula Home Page
 * @copyright    Copyright (C) 2002 by the Zikula Development Team
 * @license      http://www.gnu.org/copyleft/gpl.html GNU General Public License
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
 *
 * @author       Mark West
 * @since        25 April 2004
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
