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
 *   {$var|gt:$zikula_view}
 *
 * @param string      $string The contents to transform.
 * @param Zikula_View $view   This Zikula_View object (available as $renderObject in templates).
 *
 * @return string The modified output.
 */
function smarty_modifier_gt($string, $view)
{
    if (!$view instanceof Zikula_View) {
        return __('Error! With modifier_gt, you must use the following form for the gettext modifier (\'gt\'): $var|gt:$zikula_view');
    }

    return __($string, $view->getDomain());
}
