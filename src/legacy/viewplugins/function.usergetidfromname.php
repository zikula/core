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
 * Zikula_View function to get the user id for a given user.
 *
 * This function will return the user ID for a given username.
 *
 * available parameters:
 *  - uname       the username return the id for
 *  - assign      if set, the language will be assigned to this variable
 *
 * @param array       $params All attributes passed to this function from the template.
 * @param Zikula_View $view   Reference to the Zikula_View object.
 *
 * @return string The user ID.
 */
function smarty_function_usergetidfromname($params, Zikula_View $view)
{
    $assign  = isset($params['assign'])  ? $params['assign']  : null;
    $uname   = isset($params['uname'])   ? $params['uname']    : null;

    if (!$uname) {
        $view->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('usergetidfromname', 'uname')));

        return false;
    }

    $return = UserUtil::getIdFromName($uname);

    if ($assign) {
        $view->assign($assign, $return);
    } else {
        return $return;
    }
}
