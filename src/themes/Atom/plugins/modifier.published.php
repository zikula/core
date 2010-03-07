<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2001, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 *
 * @package Zikula_Themes
 * @subpackage Atom
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
    return strftime('%Y-%m-%dT%H:%M:%SZ', strtotime($string));
}
