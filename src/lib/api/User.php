<?php
/**
 * Zikula Application Framework
 * @version $Id$
 * @license GNU/GPLv2 (or at your option any later version).
 * Please see the NOTICE and LICENSE files distributed with this source
 */

/**
 * Log the user in
 *
 * @param uname $ the name of the user logging in
 * @param pass $ the password of the user logging in
 * @param rememberme whether $ or not to remember this login
 * @param checkPassword bool true whether or not to check the password
 * @return bool true if the user successfully logged in, false otherwise
 */
function pnUserLogIn($uname, $pass, $rememberme = false, $checkPassword = true)
{
    if (pnUserLoggedIn()) {
        return true;
    }

    $uservars = ModUtil::getVar('Users');

    if (!pnVarValidate($uname, (($uservars['loginviaoption'] == 1) ? 'email' : 'uname'))) {
        return false;
    }

    // get the database connection
    pnModDBInfoLoad('Users', 'Users');
    pnModAPILoad('Users', 'user', true);

    $uname = strtolower($uname);
    if (!pnModAvailable('AuthPN')) {
        if (!isset($uservars['loginviaoption']) || $uservars['loginviaoption'] == 0) {
            $user = DBUtil::selectObjectByID('users', $uname, 'uname', null, null, null, false, 'lower');
        } else {
            $user = DBUtil::selectObjectByID('users', $uname, 'email', null, null, null, false, 'lower');
        }

        if (!$user) {
            return false;
        }

        // check if the account is active
        if (isset($user['activated']) && $user['activated'] == '0') {
            // account inactive, deny login
            return false;
        } else if ($user['activated'] == '2') {
            // we need a session var here that can have 3 states
            // 0: account needs to be activated, this is the value after
            //    we detected this
            // 1: account needs to activated, user check the accept checkbox
            // 2: everything is ok
            // have we been here before?
            $confirmtou = SessionUtil::getVar('confirmtou', 0);
            switch ($confirmtou)
            {
                case 0 :
                    // continue if legal module is active and and configured to
                    // use the terms of use
                    if (pnModAvailable('legal')) {
                        $tou = ModUtil::getVar('legal', 'termsofuse');
                        if ($tou == 1) {
                            // users must confirm terms of use before before he can continue
                            // we redirect him to the login screen
                            // to ensure that he reads this reminder
                            SessionUtil::setVar('confirmtou', 0);
                            return false;
                        }
                    }
                    break;
                case 1 : // user has accepted the terms of use - continue
                case 2 :
                default :
            }
        }

        $uid = $user['uid'];

        // password check doesn't apply to HTTP(S) based login
        if ($checkPassword) {
            $upass = $user['pass'];
            $pnuser_hash_number = $user['hash_method'];
            $hashmethodsarray = pnModAPIFunc('Users', 'user', 'gethashmethods', array('reverse' => true));

            $hpass = hash($hashmethodsarray[$pnuser_hash_number], $pass);
            if ($hpass != $upass) {
                $event = new Event('user.login.failed', null, array('username' => $uname));
                EventManagerUtil::notify($event);
                return false;
            }

            // Check stored hash matches the current system type, if not convert it.
            $system_hash_method = $uservars['hash_method'];
            if ($system_hash_method != $hashmethodsarray[$pnuser_hash_number]) {
                $newhash = hash($system_hash_method, $pass);
                $hashtonumberarray = pnModAPIFunc('Users', 'user', 'gethashmethods');

                $obj = array('uid' => $uid, 'pass' => $newhash, 'hash_method' => $hashtonumberarray[$system_hash_method]);
                $result = DBUtil::updateObject($obj, 'users', '', 'uid');

                if (!$result) {
                    return false;
                }
            }
        }

        // Storing Last Login date
        if (!pnUserSetVar('lastlogin', date("Y-m-d H:i:s", time()), $uid)) {
            // show messages but continue
            LogUtil::registerError(__('Error! Could not save the log-in date.'));
        }
    } else {
        $authmodules = explode(',', ModUtil::getVar('AuthPN', 'authmodules'));
        foreach ($authmodules as $authmodule) {
            $authmodule = trim($authmodule);
            if (pnModAvailable($authmodule) && pnModAPILoad($authmodule, 'user')) {
                $uid = pnModAPIFunc($authmodule, 'user', 'login', array('uname' => $uname, 'pass' => $pass, 'rememberme' => $rememberme, 'checkPassword' => $checkPassword));
                if ($uid) {
                    break;
                }
            }
        }
        if (!$uid) {
            $event = new Event('user.login.failed', null, array('user' => pnUserGetVar('uid')));
            EventManagerUtil::notify($event);
            return false;
        }
    }

    if (!defined('_ZINSTALLVER')) {
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
        pnUserSetVar('activated', 1, (int) $uid);
        SessionUtil::delVar('confirmtou');
    }

    // now we've logged in the permissions previously calculated are invalid
    $GLOBALS['authinfogathered'][$uid] = 0;

    $event = new Event('user.login', null, array('user' => pnUserGetVar('uid')));
    EventManagerUtil::notify($event);

    return true;
}

/**
 * Log the user in via the REMOTE_USER SERVER property. This routine simply
 * checks if the REMOTE_USER exists in the PN environment: if he does a
 * session is created for him, regardless of the password being used.
 *
 * @return bool true if the user successfully logged in, false otherwise
 */
function pnUserLogInHTTP()
{
    $uname = pnServerGetVar('REMOTE_USER');
    $hSec  = pnConfigGetVar('session_http_login_high_security', true);
    $rc    = pnUserLogIn($uname, null, false, false);
    if ($rc && $hSec) {
        pnConfigSetVar('seclevel', 'High');
    }

    return $rc;
}

/**
 * Log the user out
 *
 * @public
 * @return bool true if the user successfully logged out, false otherwise
 */
function pnUserLogOut()
{
    if (pnUserLoggedIn()) {
        $event = new Event('user.logout', null, array('user' => pnUserGetVar('uid')));
        EventManagerUtil::notify($event);
        if (pnModAvailable('AuthPN')) {
            $authmodules = explode(',', ModUtil::getVar('AuthPN', 'authmodules'));
            foreach ($authmodules as $authmodule)
            {
                $authmodule = trim($authmodule);
                if (pnModAvailable($authmodule) && pnModAPILoad($authmodule, 'user')) {
                    if (!$result = pnModAPIFunc($authmodule, 'user', 'logout')) {
                        return false;
                    }
                }
            }
        }

        // delete logged on user the session
        // SessionUtil::delVar('rememberme');
        // SessionUtil::delVar('uid');
        session_destroy();
    }

    return true;
}

/**
 * is the user logged in?
 *
 * @public
 * @returns bool true if the user is logged in, false if they are not
 */
function pnUserLoggedIn()
{
    return (bool) SessionUtil::getVar('uid');
}

/**
 * Get all user variables, maps new style attributes to old style user data.
 *
 * @param uid $ the user id of the user
 * @return array an associative array with all variables for a user
 */
function pnUserGetVars($id, $force = false, $idfield = '')
{
    if (empty($id)) {
        return false;
    }

    // assign a value for the parameter idfield if it is necessary and prevent from possible typing mistakes
    if ($idfield  == '' || ($idfield != 'uid' && $idfield != 'uname' && $idfield != 'email')) {
        $idfield = 'uid';
        if (!is_numeric($id)) {
            $idfield = 'uname';
            if (strpos($id, '@')) {
                $idfield = 'email';
            }
        }
    }

    static $cache = array(), $unames = array(), $emails = array();

    // caching
    if ($idfield == 'uname' && isset($unames[$id]) && $force == false) {
        if ($unames[$id] !== false) {
            return $cache[$unames[$id]];
        }
        return false;
    }

    if ($idfield == 'email' && isset($emails[$id]) && $force == false) {
        if ($emails[$id] !== false) {
            return $cache[$emails[$id]];
        }
        return false;
    }

    if (isset($cache[$id]) && $force == false) {
        return $cache[$id];
    }

    // load the Users database information
    pnModDBInfoLoad('Users', 'Users');

    // get user info, don't cache as this information must be up-to-date
    $user = DBUtil::selectObjectByID('users', $id, $idfield, null, null, null, false);
    // user can be false (error) or empty array (no such user)
    if ($user === false || empty($user)) {
        switch ($idfield)
        {
            case 'uid':
                $cache[$id] = false;
                break;
            case 'uname':
                $unames[$id] = false;
                break;
            case 'email':
                $emails[$id] = false;
                break;
        }
        if ($user === false) {
            return LogUtil::registerError(__('Error! Could not load data.'));
        }
        return false;
    }

    $cache[$user['uid']] = $user;
    $unames[$user['uname']] = $user['uid'];
    $emails[$user['email']] = $user['uid'];

    return ($user);
}

/**
 * get a user variable
 *
 * @param name $ the name of the variable
 * @param uid $ the user to get the variable for
 * @param default $ the default value to return if the specified variable doesn't exist
 * @return string the value of the user variable if successful, null otherwise
 */
function pnUserGetVar($name, $uid = -1, $default = false)
{
    if (empty($name)) {
        return;
    }

    // bug fix #1311 [landseer]
    if (isset($uid) && !is_numeric($uid)) {
        return;
    }

    if ($uid == -1) {
        $uid = SessionUtil::getVar('uid');
    }
    if (empty($uid)) {
        return;
    }

    // get this user's variables
    $vars = pnUserGetVars($uid);

    // Return the variable
    if (isset($vars[$name])) {
        return $vars[$name];
    }

    // or an attribute
    if (isset($vars['__ATTRIBUTES__'][$name])) {
        return $vars['__ATTRIBUTES__'][$name];
    }

    return $default;
}

/**
 * Set a user variable. This can be
 * - a field in the users table
 * - or an attribute and in this case either a new style attribute or an old style user information.
 *
 * Examples:
 * pnUserSetVar('pass', 'mysecretpassword'); // store a password (should be hashed of course)
 * pnUserSetVar('avatar', 'mypicture.gif');  // stores an users avatar, new style
 * (internally both the new and the old style write the same attribute)
 *
 * If the user variable does not exist it will be created automatically. This means with
 * pnUserSetVar('somename', 'somevalue');
 * you can easily create brand new users variables onthefly.
 *
 * This function does not allow you to set uid or uname.
 *
 * @param name $ the name of the variable
 * @param value $ the value of the variable
 * @param uid $ the user to set the variable for
 * @return bool true if the set was successful, false otherwise
 */
function pnUserSetVar($name, $value, $uid = -1)
{
    $pntable = pnDBGetTables();

    if (empty($name)) {
        return false;
    }
    if (!isset($value)) {
        return false;
    }

    if ($uid == -1) {
        $uid = SessionUtil::getVar('uid');
    }
    if (empty($uid)) {
        return false;
    }

    // this array maps old DUDs to new attributes
    $mappingarray = array(
        '_UREALNAME' => 'realname',
        '_UFAKEMAIL' => 'publicemail',
        '_YOURHOMEPAGE' => 'url',
        '_TIMEZONEOFFSET' => 'tzoffset',
        '_YOURAVATAR' => 'avatar',
        '_YLOCATION' => 'city',
        '_YICQ' => 'icq',
        '_YAIM' => 'aim',
        '_YYIM' => 'yim',
        '_YMSNM' => 'msnm',
        '_YOCCUPATION' => 'occupation',
        '_SIGNATURE' => 'signature',
        '_EXTRAINFO' => 'extrainfo',
        '_YINTERESTS' => 'interests',
        'name' => 'realname',
        'femail' => 'publicemail',
        'timezone_offset' => 'tzoffset',
        'user_avatar' => 'avatar',
        'user_icq' => 'icq',
        'user_aim' => 'aim',
        'user_yim' => 'yim',
        'user_msnm' => 'msnm',
        'user_from' => 'city',
        'user_occ' => 'occupation',
        'user_intrest' => 'interests',
        'user_sig' => 'signature',
        'bio' => 'extrainfo');

    $res = false;
    if (pnUserFieldAlias($name)) {
        // this value comes from the users table
        $obj = array('uid' => $uid, $name => $value);
        $res = (bool) DBUtil::updateObject($obj, 'users', '', 'uid');
    } else if (array_key_exists($name, $mappingarray)) {
        LogUtil::log(__f('Warning! User variable [%1$s] is deprecated. Please use [%2$s] instead.', array($name, $mappingarray[$name])), 'STRICT');
        // $name is a former DUD /old style user information now stored as an attribute
        $obj = array('uid' => $uid, '__ATTRIBUTES__' => array($mappingarray[$name] => $value));
        $res = (bool) ObjectUtil::updateObjectAttributes($obj, 'users', 'uid', true);

    } else if (!in_array($name, array('uid', 'uname'))) {
        // $name not in the users table and also not found in the mapping array and also not one of the
        // forbidden names, let's make an attribute out of it
        $obj = array('uid' => $uid, '__ATTRIBUTES__' => array($name => $value));
        $res = (bool) ObjectUtil::updateObjectAttributes($obj, 'users', 'uid', true);
    }

    // force loading of attributes from db
    pnUserGetVars($uid, true);
    return $res;
}

function pnUserSetPassword($pass)
{
    $method = ModUtil::getVar('Users', 'hash_method');
    $hashmethodsarray = pnModAPIFunc('Users', 'user', 'gethashmethods');
    pnUserSetVar('pass', hash($method, $pass));
    pnUserSetVar('hash_method', $hashmethodsarray[$method]);
}

/**
 * Delete the contents of a user variable. This can either be
 * - a variable stored in the users table or
 * - an attribute to the users table, either a new style sttribute or the old style user information
 *
 * Examples:
 * pnUserDelVar('ublock');  // clears the recent users table entry for 'ublock'
 * pnUserDelVar('_YOURAVATAR', 123), // removes a users avatar, old style (uid = 123)
 * pnUserDelVar('avatar', 123);  // removes a users avatar, new style (uid=123)
 * (internally both the new style and the old style clear the same attribute)
 *
 * It does not allow the deletion of uid, email, uname and pass (word) as these are mandatory
 * fields in the users table.
 *
 * @param name $ the name of the variable
 * @param uid $ the user to delete the variable for
 * @return boolen true on success, false on failure
 */
function pnUserDelVar($name, $uid = -1)
{
    // Prevent deletion of core fields (duh)
    if (empty($name) || ($name == 'uid') || ($name == 'email') || ($name == 'pass') || ($name == 'uname')) {
        return false;
    }

    if ($uid == -1) {
        $uid = SessionUtil::getVar('uid');
    }
    if (empty($uid)) {
        return false;
    }

    // this array maps old DUDs to new attributes
    $mappingarray = array(
        '_UREALNAME' => 'realname',
        '_UFAKEMAIL' => 'publicemail',
        '_YOURHOMEPAGE' => 'url',
        '_TIMEZONEOFFSET' => 'tzoffset',
        '_YOURAVATAR' => 'avatar',
        '_YLOCATION' => 'city',
        '_YICQ' => 'icq',
        '_YAIM' => 'aim',
        '_YYIM' => 'yim',
        '_YMSNM' => 'msnm',
        '_YOCCUPATION' => 'occupation',
        '_SIGNATURE' => 'signature',
        '_EXTRAINFO' => 'extrainfo',
        '_YINTERESTS' => 'interests',
        'name' => 'realname',
        'femail' => 'publicemail',
        'timezone_offset' => 'tzoffset',
        'user_avatar' => 'avatar',
        'user_icq' => 'icq',
        'user_aim' => 'aim',
        'user_yim' => 'yim',
        'user_msnm' => 'msnm',
        'user_from' => 'city',
        'user_occ' => 'occupation',
        'user_intrest' => 'interests',
        'user_sig' => 'signature',
        'bio' => 'extrainfo');

    if (pnUserFieldAlias($name)) {
        // this value comes from the users table
        $obj = array('uid' => $uid, $name => '');
        return (bool) DBUtil::updateObject($obj, 'users', '', 'uid');
    } else if (array_key_exists($name, $mappingarray)) {
        LogUtil::log(__f('Warning! User variable [%1$s] is deprecated. Please use [%2$s] instead.', array($name, $mappingarray[$name])), 'STRICT');
        // $name is a former DUD /old style user information now stored as an attribute
        $res = ObjectUtil::deleteObjectSingleAttribute($uid, 'users', $mappingarray[$name]);

    } else {
        // $name not in the users table and also not found in the mapping array,
        // let's make an attribute out of it
        $res = ObjectUtil::deleteObjectSingleAttribute($uid, 'users', $name);
    }

    // force loading of attributes from db
    pnUserGetVars($uid, true);
    return (bool) $res;
}

/**
 * get the user's theme
 * This function will return the current theme for the user.
 * Order of theme priority:
 *  - page-specific
 *  - category
 *  - user
 *  - system
 *
 * @public
 * @return string the name of the user's theme
 **/
function pnUserGetTheme($force = false)
{
    static $theme;
    if (isset($theme) && !$force) {
        return $theme;
    }

    // Page-specific theme
    $pagetheme = FormUtil::getPassedValue('theme', null, 'GETPOST');
    $type = FormUtil::getPassedValue('type', null, 'GETPOST');
    $qstring = pnServerGetVar('QUERY_STRING');
    if (!empty($pagetheme)) {
        $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($pagetheme));
        if ($themeinfo['state'] == PNTHEME_STATE_ACTIVE && ($themeinfo['user'] || $themeinfo['system'] || ($themeinfo['admin'] && ($type == 'admin' || stristr($qstring, 'admin.php')))) && is_dir('themes/' . DataUtil::formatForOS($themeinfo['directory']))) {
            $theme = _pnUserGetThemeEvent($themeinfo['name']);
            return $theme;
        }
    }

    // check for an admin theme
    if (($type == 'admin' || stristr($qstring, 'admin.php')) && SecurityUtil::checkPermission('::', '::', ACCESS_EDIT)) {
        $admintheme = ModUtil::getVar('Admin', 'admintheme');
        if (!empty($admintheme)) {
            $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($admintheme));
            if ($themeinfo && $themeinfo['state'] == PNTHEME_STATE_ACTIVE && is_dir('themes/' . DataUtil::formatForOS($themeinfo['directory']))) {
                $theme = _pnUserGetThemeEvent($themeinfo['name']);
                return $theme;
            }
        }
    }

    // set a new theme for the user
    $newtheme = FormUtil::getPassedValue('newtheme', null, 'GETPOST');
    if (!empty($newtheme) && pnConfigGetVar('theme_change')) {
        $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($newtheme));
        if ($themeinfo && $themeinfo['state'] == PNTHEME_STATE_ACTIVE && is_dir('themes/' . DataUtil::formatForOS($themeinfo['directory']))) {
            if (pnUserLoggedIn()) {
                pnUserSetVar('theme', $newtheme);
            } else {
                SessionUtil::setVar('theme', $newtheme);
            }
            $theme = _pnUserGetThemeEvent($themeinfo['name']);
            return $theme;
        }
    }

    // User theme
    if (pnConfigGetVar('theme_change')) {
        if ((pnUserLoggedIn())) {
            $usertheme = pnUserGetVar('theme');
        } else {
            $usertheme = SessionUtil::getVar('theme');
        }
        $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($usertheme));
        if ($themeinfo && $themeinfo['state'] == PNTHEME_STATE_ACTIVE && is_dir('themes/' . DataUtil::formatForOS($themeinfo['directory']))) {
            $theme = _pnUserGetThemeEvent($themeinfo['name']);
            return $theme;
        }
    }

    // default site theme
    $defaulttheme = pnConfigGetVar('Default_Theme');
    $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($defaulttheme));
    if ($themeinfo && $themeinfo['state'] == PNTHEME_STATE_ACTIVE && is_dir('themes/' . DataUtil::formatForOS($themeinfo['directory']))) {
        $theme = _pnUserGetThemeEvent($themeinfo['name']);
        return $theme;
    }

    throw new RuntimeException(__('pnUserGetTheme: unable to calculate theme name.'));
}

function _pnUserGetThemeEvent($themeName)
{
    $event = new Event('user.gettheme', null, array('name' => $themeName));
    EventManagerUtil::notifyUntil($event);
    return $event['name'];
}

/**
 * get the user's language
 *
 * @deprecated
 * @see ZLanaguage::getLanguageCode()
 *
 * This function returns the deprecated 3 digit language codes, you need to switch APIs
 *
 * @return string the name of the user's language
 */
function pnUserGetLang()
{
    return ZLanguage::getLanguageCodeLegacy();
}

/**
 * get a list of user information
 *
 * @public
 * @return array array of user arrays
 */
function pnUserGetAll($sortbyfield = 'uname', $sortorder = 'ASC', $limit = -1, $startnum = -1, $activated = '', $regexpfield = '', $regexpression = '', $where = '')
{
    $pntable = pnDBGetTables();
    $userscolumn = $pntable['users_column'];

    if (empty($where)) {
        if (!empty($regexpfield) && (array_key_exists($regexpfield, $userscolumn)) && !empty($regexpression)) {
            $where = 'WHERE ' . $userscolumn[$regexpfield] . ' REGEXP "' . DataUtil::formatForStore($regexpression) . '"';
        }
        if (!empty($activated) && is_numeric($activated) && array_key_exists('activated', $userscolumn)) {
            if (!empty($where)) {
                $where .= ' AND ';
            } else {
                $where = ' WHERE ';
            }
            $where .= "$userscolumn[activated] != '" . DataUtil::formatForStore($activated) . "'";
        }
    }

    $sortby = '';
    if (!empty($sortbyfield)) {
        if (array_key_exists($sortbyfield, $userscolumn)) {
            $sortby = 'ORDER BY ' . $userscolumn[$sortbyfield] . ' ' . DataUtil::formatForStore($sortorder); //sort by .....
        } else {
            $sortby = 'ORDER BY ' . DataUtil::formatForStore($sortbyfield) . ' ' . DataUtil::formatForStore($sortorder); //sorty by dynamic.....
        }
        if ($sortbyfield != 'uname') {
            $sortby .= ', ' . $userscolumn['uname'] . ' ASC ';
        }
    }

    return DBUtil::selectObjectArray('users', $where, $sortby, $startnum, $limit, 'uid');
}

/**
 * Get the uid of a user from the username
 *
 * @param uname $ the username
 * @return mixed userid if found, false if not
 */
function pnUserGetIDFromName($uname)
{
    $result = pnUserGetVars($uname, false, 'uname');
    return ($result && isset($result['uid']) ? $result['uid'] : false);
}

/**
 * Get the uid of a user from the email (case for unique emails)
 *
 * @param email $ the user email
 * @return mixed userid if found, false if not
 */
function pnUserGetIDFromEmail($email)
{
    $result = pnUserGetVars($email);
    return ($result && isset($result['uid']) ? $result['uid'] : false);
}

/**
 * Checks the alias and returns if we save the data in the
 * Profile module's user_data table or the users table.
 * This should be removed if we ever go fully dynamic
 *
 * @param label $ the alias of the field to check
 * @return true if found, false if not, void upon error
 */
function pnUserFieldAlias($label)
{
    if (empty($label)) {
        return false;
    }

    // no change in uid or uname allowed
    if ($label == 'uid' || $label == 'uname') {
        return false;
    }

    $pntables = pnDBGetTables();
    return array_key_exists($label, $pntables['users_column']);
}
