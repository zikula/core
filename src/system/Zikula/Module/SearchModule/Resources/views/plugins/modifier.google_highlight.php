<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     modifier
 * Name:     google_highlight
 * Version:  2.0
 * Date:     May 7, 2007
 * Author:   Jorn Wildt
 * Purpose:  html safe case insensitive google highlight
 * Comments: based on work by Jeroen de Jong <jeroen@telartis.nl>
 *           based on work by Tom Anderson <toma@etree.org>
 *
 * Example smarty code:
 *
 * {assign var=text value="This is a <a href=this>string</a> I want to search through"}
 * {assign var=search value="this \"to search\" through"}
 * {$text|google_highlight:$search}
 *
 * @param string  $text        The string to operate on.
 * @param string  $search      The search phrase.
 * @param integer $contextSize The number of chars shown as context around the search phrase.
 */
function smarty_modifier_google_highlight($text, $search, $contextSize)
{
    return StringUtil::highlightWords($text, $search, $contextSize);
}
