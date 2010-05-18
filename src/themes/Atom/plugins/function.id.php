<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2001, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id: modifier.modified.php 18169 2006-03-16 02:17:22Z drak $
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 *
 * @package Zikula_Themes
 * @subpackage Atom
 */

/**
 * Smarty function to generate a valid atom ID for the feed
 *
 * Example
 *
 *   <id><!--[id]--></id>
 *
 * @return       string the atom ID
 */
function smarty_function_id($params, &$smarty)
{
    $baseurl = System::getBaseUrl();

    $parts = parse_url($baseurl);

    $starttimestamp = strtotime(System::getVar('startdate'));
    $startdate = strftime('%Y-%m-%d', $starttimestamp);

    $sitename = System::getVar('sitename');
    $sitename = preg_replace('/[^a-zA-Z0-9-\s]/', '', $sitename);
    $sitename = DataUtil::formatForURL($sitename);

    return "tag:{$parts['host']},{$startdate}:{$sitename}";
}
