<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv2.1 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Smarty function to display the theme info
 *
 * Example
 * <!--[themeinfo]-->
 *
 * @see          function.themeinfo.php::smarty_function_themeinfo()
 * @param        array       $params      All attributes passed to this function from the template
 * @param        object      &$smarty     Reference to the Smarty object
 * @return       string      the themeinfo
 */
function smarty_function_themeinfo($params, &$smarty)
{
    $thistheme = pnUserGetTheme();
    $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($thistheme));

    $themecredits = '<!-- ' . __f('Theme: %1$s by %2$s - %3$s', array(DataUtil::formatForDisplay($themeinfo['display']), DataUtil::formatForDisplay($themeinfo['author']), DataUtil::formatForDisplay($themeinfo['contact']))).' -->';

    return $themecredits;
}
