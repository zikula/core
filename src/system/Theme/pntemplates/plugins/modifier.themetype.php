<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2001, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 *
 * pnRender plugin
 *
 * This file is a plugin for pnRender, the Zikula implementation of Smarty
 *
 * @package      Zikula_System_Modules
 * @subpackage   pnRender
 */

/**
 * Smarty modifier to convert theme type into a language string
 *
 * Example
 *
 *   <!--[$mythemetype|themetype]-->
 *
 * @see          modifier.pnvarprepfordisplay.php::smarty_modifier_DataUtil::formatForDisplay()
 * @param        array    $string     the contents to transform
 * @return       string   the modified output
 */
function smarty_modifier_themetype($string)
{
    switch ((int)$string) {
        case 3:
            return __('Theme 3.0');

    }
}
