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
 * Plugin to set a variable on the theme
 *
 * This function set the corresponding value on a theme variable
 *
 * Available parameters:
 *   - name:    Name of the variable
 *   - value:   The value to set on the variable
 *
 * Example
 * {themesetvar name='master' value='1col'} for Andreas08
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the Zikula_View object.
 *
 * @return mixed
 */
function smarty_function_themesetvar($params, Zikula_View $view)
{
    $name   = isset($params['name'])   ? $params['name']    : null;
    $value  = isset($params['value'])  ? $params['value'] : null;

    if (!$name) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('themegetvar', 'name')));

        return false;
    }

    ThemeUtil::setVar($name, $value);
}
