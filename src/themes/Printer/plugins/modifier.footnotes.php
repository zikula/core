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
 * Smarty modifier to convert urls into footnote references for printable page
 *
 * File:     	modifier.footnotes.php
 * Type:     	modifier
 * Name:     	footnotes
 * Date:     	Feb 23, 2005
 * Purpose:  	Generate footnotes for printable page
 * @author		Jochen Roemling
 * @author      Mark West
 * @version  	1.3
 * @param 		string
 * @param 		Smarty
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
