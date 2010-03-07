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
 * @subpackage rss
 */

/**
 * Smarty function to generate a valid atom ID for the feed
 *
 * Example
 *
 *   <updated><!--[updated]--></updated>
 *
 * @return       string the atom ID
 */
function smarty_function_updated($params, &$smarty)
{
    return date('D, d M Y H:i:s O', $GLOBALS['rss_feed_lastupdated']);
}
