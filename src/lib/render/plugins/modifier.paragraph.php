<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Smarty paragraph modifier plugin
 *
 * Purpose:  convert \n and other newline chars to HTML paragraphs
 * Note:     Uses code from WordPress by Matthew Mullenweg (http://www.photomatt.net; http://www.wordpress.org)
 * Input:
 *         - string: input block of text
 *         - br: change single \n to 'br' or not
 *
 * @param        array    $string     the contents to transform
 * @return       string   the modified output
 */
function smarty_modifier_paragraph($string,$br = 0) {

    if($string != '') {
        $string = $string . "\n"; // just to make things a little easier, pad the end
        $string = preg_replace('|<br />\s*<br />|', "\n\n", $string);
        $string = preg_replace('!(<(?:table|ul|ol|li|pre|form|blockquote|h[1-6])[^>]*>)!', "\n$1", $string); // Space things out a little
        $string = preg_replace('!(</(?:table|ul|ol|li|pre|form|blockquote|h[1-6])>)!', "$1\n", $string); // Space things out a little
        $string = preg_replace("/(\r\n|\r)/", "\n", $string); // cross-platform newlines
        $string = preg_replace("/\n\n+/", "\n\n", $string); // take care of duplicates
        $string = preg_replace('/\n?(.+?)(?:\n\s*\n|\z)/s', "\t<p>$1</p>\n", $string); // make paragraphs, including one at the end
        $string = preg_replace('|<p>\s*?</p>|', '', $string); // under certain strange conditions it could create a P of entirely whitespace
        $string = preg_replace("|<p>(<li.+?)</p>|", "$1", $string); // problem with nested lists
        $string = preg_replace('|<p><blockquote([^>]*)>|i', "<blockquote$1><p>", $string);
        $string = str_replace('</blockquote></p>', '</p></blockquote>', $string);
        $string = preg_replace('!<p>\s*(</?(?:table|tr|td|th|div|ul|ol|li|pre|select|form|blockquote|p|h[1-6])[^>]*>)!', "$1", $string);
        $string = preg_replace('!(</?(?:table|tr|td|th|div|ul|ol|li|pre|select|form|blockquote|p|h[1-6])[^>]*>)\s*</p>!', "$1", $string);
        if ($br) $string = preg_replace('|(?<!<br />)\s*\n|', "<br />\n", $string); // optionally make line breaks
        $string = preg_replace('!(</?(?:table|tr|td|th|div|dl|dd|dt|ul|ol|li|pre|select|form|blockquote|p|h[1-6])[^>]*>)\s*<br />!', "$1", $string);
        $string = preg_replace('!<br />(\s*</?(?:p|li|div|th|pre|td|ul|ol)>)!', '$1', $string);
        $string = preg_replace('/&([^#])(?![a-z]{1,8};)/', '&#038;$1', $string);

        return $string;
    } else {
        return;
    }
}
