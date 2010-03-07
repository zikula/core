<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2001, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id: modifier.updated.php 22138 2007-06-01 10:19:14Z markwest $
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 *
 * @package Zikula_Themes
 * @subpackage rss
 */

/**
 * Smarty modifier format an issue date for an atom news feed
 *
 * Example
 *
 *   <!--[$MyVar|updated]-->
 *
 * @param       array    $string     the contents to transform
 * @return       string   the updated output
 */
function smarty_modifier_updated($string)
{
    global $rss_feed_lastupdated;

    $timestamp = strtotime($string);

    if (!isset($rss_feed_lastupdated) || $timestamp > $rss_feed_lastupdated) {
        $rss_feed_lastupdated = $timestamp;
    }

    return $string;
}
