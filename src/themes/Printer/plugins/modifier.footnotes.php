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
 * Smarty modifier to convert urls into footnote references for printable page
 *
 * File:         modifier.footnotes.php
 * Type:         modifier
 * Name:         footnotes
 * Purpose:      Generate footnotes for printable page
 * @param         string
 * @param         Smarty
 */
function smarty_modifier_footnotes($string)
{
    // globalise the links array
    global $link_arr;

    $link_arr = array();
    // replace the links
    $text = preg_replace_callback('/<a [^>]*href\s*=\s*\"?([^>\"]*)\"?[^>]*>(.*?)<\/a.*?>/i','_smarty_modifier_footnotes_callback',$string);

    return $text;
}


function _smarty_modifier_footnotes_callback($arr)
{
    // globalise the links array
    global $link_arr;

    // remember the link
    // TODO - work out why some links need decoding twice (&amp;amp;....)
    $link_arr[] = html_entity_decode(html_entity_decode($arr[1]));

    // return the replaced link
    return '<strong><em>'.$arr[2].'</em></strong> <small>['.count($link_arr).']</small>';
}
