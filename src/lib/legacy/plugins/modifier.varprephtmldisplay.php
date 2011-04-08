<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula_View
 * @subpackage Template_Plugins
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Smarty modifier to prepare variable for display, preserving some HTML tags
 *
 * This modifier carries out suitable escaping of characters such that when output
 * as part of an HTML page the exact string is displayed, except for a number of
 * admin-defined HTML tags which are left as-is for display purposes.
 *
 * This modifier should be used with great care, as it does allow certain
 * HTML tags to be displayed.
 *
 * The HTML tags that will be displayed are those defined in the configuration
 * variable AllowableHTML , which is set on a per-instance basis by the site administrator.
 *
 * Running this modifier multiple times is cumulative and is not reversible.
 * It recommended that variables that have been returned from this modifier
 * are only used to display the results, and then discarded.
 *
 * Example
 *
 *   {$MyVar|varprephtmldisplay}
 *
 * @see          modifier.safehtml.php::smarty_modifier_safehtml
 * @param        array    $string     the contents to transform
 * @return       string   the modified output
 */
function smarty_modifier_varprephtmldisplay($string)
{
    LogUtil::log(__f('Warning! Template modifier {$var|%1$s} is deprecated, please use {$var|%2$s} instead.', array('varprephtmldisplay', 'safehtml')), E_USER_DEPRECATED);

    return DataUtil::formatForDisplayHTML($string);
}
