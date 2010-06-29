<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2001, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id: modifier.published.php 22138 2007-06-01 10:19:14Z markwest $
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 *
 * @package Zikula_Themes
 * @subpackage rss
 */

/**
 * Smarty function sets correct http header for RSS feeds.
 *
 * @return void
 */

function smarty_function_xmlHeader()
{
    header("Content-type: application/rss+xml");
}
