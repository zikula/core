<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Smarty function to get the user id for a given user.
 *
 * This function will return the user ID for a given username.
 *
 * available parameters:
 *  - uname       the username return the id for
 *  - assign      if set, the language will be assigned to this variable
 *
 * @param array  $params  All attributes passed to this function from the template.
 * @param object &$smarty Reference to the Smarty object.
 *
 * @return   string   the user ID
 */
function smarty_function_usergetidfromname($params, &$smarty)
{
    $assign  = isset($params['assign'])  ? $params['assign']  : null;
    $uname   = isset($params['uname'])   ? $params['uname']    : null;

    if (!$uname) {
        $smarty->trigger_error(__f('Error! in %1$s: the %2$s parameter must be specified.', array('pnusergetidfromname', 'uname')));
        return false;
    }

    $return = pnUserGetIDFromName($uname);

    if ($assign) {
        $smarty->assign($assign, $return);
    } else {
        return $return;
    }
}
