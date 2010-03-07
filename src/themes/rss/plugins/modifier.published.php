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
 * Smarty modifier format an issue date for an atom news feed
 *
 * Example
 *
 *   <!--[$MyVar|published]-->
 *
 * @param        array    $string     the contents to transform
 * @return       string   the modified output
 */
function smarty_modifier_published($string)
{
    return date('D, d M Y H:i:s O', strtotime($string));
}
