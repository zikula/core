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
 * Smarty modifier to parse gettext string.
 *
 * Example
 *
 *   {$var|gt:$renderObject}
 *
 * @param string   $string  The contents to transform.
 * @param Zikula_View &$smarty This smarty object (available as $renderObject in templates).
 *
 * @return string The modified output.
 */
function smarty_modifier_gt($string, &$smarty)
{
    if (!is_object($smarty)) {
        return __('Error! With modifier_gt, you must use the following form for the gettext modifier (\'gt\'): $var|gt:$renderObject.');
    }

    // the check order here is important because:
    // if we are calling from a theme both $smarty->themeDomain and $smarty->renderDomain are set.
    // if the call was from a template only $smarty->renderDomain is set.
    if (isset($smarty->renderDomain) && !isset($smarty->themeDomain)) {
        $domain = $smarty->renderDomain;
    } elseif (isset($smarty->themeDomain)) {
        $domain = $smarty->themeDomain;
    } else {
        $domain = 'zikula'; // default domain
    }

    return __($string, $domain);
}
