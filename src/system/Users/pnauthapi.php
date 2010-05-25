<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv2.1 (or at your option any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

function Users_authapi_login($args)
{
    $uname = (string)$args['uname'];
    $pass = (string)$args['pass'];
    $rememberme = (bool)$args['rememberme'];
    $checkPassword = (bool)$args['checkPassword'];

    // password check doesn't apply to HTTP(S) based login
    if ($checkPassword) {
        $result = ModUtil::apiFunc('Users', 'user', 'checkpassword', array('user' => $user));
        if (!$result) {
            return false;
        }
    }

    // Storing Last Login date
    if (!UserUtil::setVar('lastlogin', date("Y-m-d H:i:s", time()), $uid)) {
        // show messages but continue
        LogUtil::registerError(__('Error! Could not save the log-in date.'));
    }

    if (!System::isInstalling()) {
        SessionUtil::requireSession();
    }

    // Set session variables
    SessionUtil::setVar('uid', (int) $uid);
    if (!empty($rememberme)) {
        SessionUtil::setVar('rememberme', 1);
    }

    if (isset($confirmtou) && $confirmtou == 1) {
        // if we get here, the user did accept the terms of use
        // now update the status
        self::setVar('activated', 1, (int) $uid);
        SessionUtil::delVar('confirmtou');
    }

    // now we've logged in the permissions previously calculated are invalid
    $GLOBALS['authinfogathered'][$uid] = 0;

    $event = new Event('user.login', null, array('user' => UserUtil::getVar('uid')));
    EventManagerUtil::notify($event);

    return true;
}

function Users_authapi_logout()
{
    return true;
}

function Users_authapi_checkpassword($args)
{
    $user = $args['user'];// user array
    $upass = $user['pass'];
    $hash_number = $user['hash_method'];
    $hashmethodsarray = ModUtil::apiFunc('Users', 'user', 'gethashmethods', array('reverse' => true));

    $hpass = hash($hashmethodsarray[$hash_number], $pass);
    if ($hpass != $upass) {
        return false;
    }

    // Check stored hash matches the current system type, if not convert it.
    $system_hash_method = $uservars['hash_method'];
    if ($system_hash_method != $hashmethodsarray[$hash_number]) {
        $newhash = hash($system_hash_method, $pass);
        $hashtonumberarray = ModUtil::apiFunc('Users', 'user', 'gethashmethods');

        $obj = array('uid' => $uid, 'pass' => $newhash, 'hash_method' => $hashtonumberarray[$system_hash_method]);
        $result = DBUtil::updateObject($obj, 'users', '', 'uid');

        if (!$result) {
            return false;
        }
    }

    return true;
}