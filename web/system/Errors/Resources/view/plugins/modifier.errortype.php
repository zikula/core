<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2002, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id: modifier.errortype.php 18167 2006-03-16 01:49:56Z drak $
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package      Zikula_System_Modules
 * @subpackage   Errors
 */

/**
 * Smarty modifier to convert lanugage define into textual string
 *
 * Example
 *
 *   {$var|errortype}
 *
 * @see          modifier.errortype.php::smarty_modifier_errortype()
 * @param        array    $string     the contents to transform
 * @return       string   the modified output
 */
function smarty_modifier_errortype($string)
{
    switch ($string) {
        case E_ERROR:
        case E_USER_ERROR:
            $msg = __('An unidentified problem occurred (classified as an error). The following message was returned:');
            break;
        case E_WARNING:
        case E_USER_WARNING:
            $msg = __('A non-critical problem occurred (classified as a warning). The following message was returned:');
            break;
        case E_NOTICE:
        case E_USER_NOTICE:
            $msg = __('A non-critical problem occurred (classified as a notice). The following message was returned:');
            break;
        default:
            $msg = __('An unidentified problem occurred (unclassified). The following message was returned:');
            break;
    }

    return $msg;
}
