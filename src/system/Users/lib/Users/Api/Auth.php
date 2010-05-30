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

class Users_Api_Auth extends AbstractApi
{
    public function login($args)
    {
        $login = (string)strtolower($args['uname']);
        $pass = (string)$args['pass'];

        // password check doesn't apply to HTTP(S) based login
        if ($checkPassword) {
            return ModUtil::apiFunc('Users', 'auth', 'checkPassword', array('login' => $login, 'pass' => $pass));
        }
    }

    public function logout()
    {
        return true;
    }

    public function checkPassword($args)
    {
        $login = strtolower($args['login']);
        $raw_password = $args['pass'];

        $uservars = ModUtil::getVar('Users');
        if (!System::varValidate($login, (($uservars['loginviaoption'] == 1) ? 'email' : 'uname'))) {
            return false;
        }

        if (!isset($uservars['loginviaoption']) || $uservars['loginviaoption'] == 0) {
            $user = DBUtil::selectObjectByID('users', $login, 'uname', null, null, null, false, 'lower');
        } else {
            $user = DBUtil::selectObjectByID('users', $login, 'email', null, null, null, false, 'lower');
        }

        if (!$user) {
            return false;
        }

        $uid = $user['uid'];

        $hash_number = $user['hash_method'];
        $stored_hash = $user['pass'];
        $hashmethodsarray = ModUtil::apiFunc('Users', 'user', 'getHashMethods', array('reverse' => true));

        $hashed_password = hash($hashmethodsarray[$hash_number], $raw_password);

        if ($hashed_password != $stored_hash) {
            return false;
        }

        // Check stored hash matches the current system type, if not convert it.
        $system_hash_method = $uservars['hash_method'];
        if ($system_hash_method != $hashmethodsarray[$hash_number]) {
            $newhash = hash($system_hash_method, $raw_password);
            $hashtonumberarray = ModUtil::apiFunc('Users', 'user', 'getHashMethods');

            $obj = array('uid' => $uid, 'pass' => $newhash, 'hash_method' => $hashtonumberarray[$system_hash_method]);
            $result = DBUtil::updateObject($obj, 'users', '', 'uid');

            if (!$result) {
                return false;
            }
        }

        return true;
    }
}
