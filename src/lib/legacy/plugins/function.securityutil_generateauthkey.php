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
 * Smarty function to generate a unique key to secure forms content as unique.
 *
 * Note that you must not cache the outputs from this function, as its results
 * change aech time it is called. The Zikula developers are looking for ways to
 * automise this.
 *
 *
 * Available parameters:
 *   - module:   The well-known name of a module to execute a function from (required)
 *   - assign:   If set, the results are assigned to the corresponding variable instead of printed out
 *
 * Example
 *   <input type="hidden" name="authid" value="{securityutil_generateauthkey module='MyModule'}">
 *
 * @todo         prevent this function from being cached (Smarty 2.6.0)
 * @param        array       $params      All attributes passed to this function from the template
 * @param        object      $smarty     Reference to the Smarty object
 * @return       string      the authentication key
 * @deprecated
 */
function smarty_function_securityutil_generateauthkey($params, $smarty)
{
    LogUtil::log(__f('Warning! Template plugin {%1$s} is deprecated, please use {%2$s} instead.', array('securityutil_generateauthkey', 'insert.generateauthkey')), E_USER_DEPRECATED);

    if (!isset($params['module'])) {
        $smarty->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('securityutil_generateauthkey', 'module')));

        return false;
    }

    $result = SecurityUtil::generateAuthKey($params['module']);

    if (isset($params['assign'])) {
        $smarty->assign($params['assign'], $result);
    } else {
        return $result;
    }
}
