<?php
/**
 * Zikula Application Framework
 *
 * @link http://www.zikula.org
 * @version $Id: function.pagerabc.php 20883 2006-12-22 10:24:16Z markwest $
 * @package Zikula_Template_Plugins
 * @subpackage Functions
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
 * -------------------------------------------------------------
 */
function smarty_modifier_google_highlight($text, $search, $contextSize)
{
  return StringUtil::highlightWords($text, $search, $contextSize);
}
