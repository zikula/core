<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Render
 * @subpackage Template_Plugins
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Smarty function to get a colour definition from the theme
 *
 * This function returns the corresponding color define from the theme
 *
 * Available parameters:
 *   - name:    Name of the colour definition
 *   - default: If set, the default value to return if the variable is not set
 *   - assign:  If set, the results are assigned to the corresponding variable instead of printed out
 *
 * Example
 * {themegetvar name='bgcolor'}
 *
 * @param array  $params  All attributes passed to this function from the template.
 * @param Smarty &$smarty Reference to the Smarty object.
 *
 * @return string The colour definition.
 */
function smarty_function_themegetvar($params, &$smarty)
{
    $assign  = isset($params['assign'])  ? $params['assign']  : null;
    $default = isset($params['default']) ? $params['default'] : null;
    $name    = isset($params['name'])    ? $params['name']    : null;

    if (!$name) {
        $smarty->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('pnthemegetvar', 'name')));
        return false;
    }

    $result = ThemeUtil::getVar($name, $default);

    if ($assign) {
        $smarty->assign($assign, $result);
    } else {
        return $result;
    }
}
