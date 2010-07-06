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
 * Zikula_View modifier to parse gettext string.
 *
 * Example
 *
 *   {$var|gt:$renderObject}
 *
 * @param string   $string  The contents to transform.
 * @param Zikula_View &$view This Zikula_View object (available as $renderObject in templates).
 *
 * @return string The modified output.
 */
function smarty_modifier_gt($string, &$view)
{
    if (!is_object($view)) {
        return __('Error! With modifier_gt, you must use the following form for the gettext modifier (\'gt\'): $var|gt:$renderObject.');
    }

    // the check order here is important because:
    // if we are calling from a theme both $view->themeDomain and $view->renderDomain are set.
    // if the call was from a template only $view->renderDomain is set.
    if (isset($view->renderDomain) && !isset($view->themeDomain)) {
        $domain = $view->renderDomain;
    } elseif (isset($view->themeDomain)) {
        $domain = $view->themeDomain;
    } else {
        $domain = 'zikula'; // default domain
    }

    return __($string, $domain);
}
