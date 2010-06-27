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
 * Smarty insert function to dynamically generated an authorisation key
 *
 * Available parameters:
 *   - module:   The well-known name of a module to execute a function from (required)
 *   - assign:   If set, the results are assigned to the corresponding variable instead of printed out
 *
 * Example
 * <input type="hidden" name="authid" value="{insert name="generateauthkey" module="Users" }" />
 *
 * @param array  $params  All attributes passed to this function from the template.
 * @param Smarty &$smarty Reference to the Smarty object.
 *
 * @return string
 */
function smarty_insert_generateauthkey($params, &$smarty)
{
    $assign = isset($params['assign']) ? $params['assign'] : null;
    $module = isset($params['module']) ? $params['module'] : null;

    if (!$module) {
        $module = ModUtil::getName();
    }

    $result = SecurityUtil::generateAuthKey($module);

    if ($assign) {
        $smarty->assign($assign, $result);
    } else {
        return $result;
    }
}
