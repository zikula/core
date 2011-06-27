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
 * Smarty function to display footnotes caculated by earlier modifier
 *
 * Example
 *   {footnotes}
 *
 * @param       array       $params      All attributes passed to this function from the template
 * @param       object      $smarty     Reference to the Smarty object
 */
function smarty_function_footnotes($params, $smarty)
{
    // globalise the links array
    global $link_arr;

    $text = '';

    if (is_array($link_arr) && !empty($link_arr)) {
        $text .= '<ol>';
        $link_arr = array_unique($link_arr);
        foreach ($link_arr as $key => $link) {
            // check for an e-mail address
            if (preg_match("/^([a-z0-9_]|\\-|\\.)+@(([a-z0-9_]|\\-)+\\.)+[a-z]{2,4}$/i", $link)) {
                $linktext = $link;
                $link = 'mailto:' . $link;
            // append base URL for local links (not web links)
            } elseif (!preg_match("/^http:\/\//i",$link))    {
                $link = System::getBaseUrl().$link;
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

    if (isset($params['assign'])) {
        $smarty->assign($params['assign'], $text);
    } else {
        return $text;
    }
}
