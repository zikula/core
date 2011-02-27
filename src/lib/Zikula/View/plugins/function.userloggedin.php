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
 * Zikula_View function to determine whether the current user is logged in.
 *
 * This function will return true if that is true and false otherwise
 *
 * available parameters:
 *  - assign      if set, the loggedin status will be assigned to this variable
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the Zikula_View object.
 *
 * @return   bool   the logged in status
 */
function smarty_function_userloggedin($params, Zikula_View $view)
{
    $assign = isset($params['assign']) ? $params['assign'] : null;

    $return = UserUtil::isLoggedIn();

    if ($assign) {
        $view->assign($assign, $return);
    } else {
        return $return;
    }
}
