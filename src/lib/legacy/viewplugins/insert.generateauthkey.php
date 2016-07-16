<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Zikula_View insert function to dynamically generated an authorisation key
 *
 * Available parameters:
 *   - module:   The well-known name of a module to execute a function from (required)
 *   - assign:   If set, the results are assigned to the corresponding variable instead of printed out
 *
 * Example
 * <input type="hidden" name="authid" value="{insert name='generateauthkey' module='ZikulaUsersModule'}" />
 *
 * @param array       $params All attributes passed to this function from the template
 * @param Zikula_View $view   Reference to the Zikula_View object
 *
 * @return string
 */
function smarty_insert_generateauthkey($params, $view)
{
    LogUtil::log(__f('Warning! Template plugin {%1$s} is deprecated, please use {%2$s} instead.', ["insert name='secgenauthkey' ...", "insert name='csrftoken' ..."]), E_USER_DEPRECATED);
    $module = isset($params['module']) ? $params['module'] : null;

    if (!$module) {
        $module = ModUtil::getName();
    }

    $result = SecurityUtil::generateAuthKey($module);

    // NOTE: assign parameter is handled by the smarty_core_run_insert_handler(...) function in lib/vendor/Smarty/internals/core.run_insert_handler.php

    return $result;
}
