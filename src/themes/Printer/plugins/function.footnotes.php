<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2001, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 *
 * Xanthia plugin
 *
 * This file is a plugin for Xanthia, the Zikula implementation of Smarty
 *
 * @package      Xanthia_Templating_Environment
 * @subpackage   Xanthia
 */

/**
 * Smarty function to display footnotes caculated by earlier modifier
 *
 * Example
 *   <!--[footnotes]-->
 *
 * @author      Jochen Roemling
 * @author      Mark West
 * @since       23/02/2004
 * @param       array       $params      All attributes passed to this function from the template
 * @param       object      &$smarty     Reference to the Smarty object
 */
function smarty_function_footnotes($params, &$smarty)
{
    // globalise the links array
    global $link_arr;

    $text = '';

    if (is_array($link_arr) && !empty($link_arr)) {
        $text .= '<ol>';
        foreach ($link_arr as $key => $link) {
            // check for an e-mail address
            if (preg_match("/^([a-z0-9_]|\\-|\\.)+@(([a-z0-9_]|\\-)+\\.)+[a-z]{2,4}$/i", $link)) {
                $linktext = $link;
                $link = 'mailto:' . $link;
            // append base URL for local links (not web links)
            } elseif (!preg_match("/^http:\/\//i",$link))    {
                $link = pnGetBaseURL().$link;
                $linktext = $link;
            } else {
                $linktext = $link;
            }
            $linktext = DataUtil::formatForDisplay($linktext);
            $link = DataUtil::formatForDisplay($link);
            // output link
            $text .= '<li><a class="print-normal" href="'.$link.'">'.$linktext.'</a></li>'."\n";
        }
        $text .= '</ol>';
    }
    return $text;
}
