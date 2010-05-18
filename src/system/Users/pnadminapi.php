<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_System_Modules
 * @subpackage Users
 */

/**
 * Find whether the user exists.
 *
 * @param array $args All parameters passed to the function.
 *                    $args['chng_user'] (string|numeric) Either a user name or a user id for which to search.
 *
 * @return bool True if the specified user exists, otherwise false.
 */
function users_adminapi_userexists($args)
{
    // Do not check for is_numeric() here to determine if the chng_user is a user name or a user id.
    // Some sites might have all numeric user names (e.g., a membership number). Since there is no way to tell if the
    // parameter should be treated as a user name or a user id, check both defaulting to user name first.
    $user = DBUtil::selectObjectByID('users', $args['chng_user'], 'uname');
    if (!$user) {
        $user = DBUtil::selectObjectByID('users', $args['chng_user'], 'uid');
    }

    return (boolean)$user;
}

/**
 * Get a list of user groups. DO NOT confuse this function with users_user_getusergroups.
 *
 * @see    users_user_getusergroups()
 *
 * @return array|bool An array of user groups ordered by name; false on error.
 */
function users_adminapi_getusergroups()
{
    // Need read access to call this function
    if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_READ)) {
        return false;
    }

    // Get and display current groups
    $objArray = DBUtil::selectObjectArray('groups', '', 'name');

    if ($objArray === false) {
        LogUtil::registerError(__('Error! Could not load data.'));
    }

    return $objArray;
}

/**
 * Find users.
 *
 * @param array $args All parameters passed to this function.
 *                    $args['uname']         (string) A fragment of a user name on which to search using an SQL LIKE clause. The user name will be surrounded by wildcards.
 *                    $args['ugroup']        (int)    A group id in which to search (only users who are members of the specified group are returned).
 *                    $args['email']         (string) A fragment of an e-mail address on which to search using an SQL LIKE clause. The e-mail address will be surrounded by
 *                                                      wildcards.
 *                    $args['regdateafter']  (string) An SQL date-time (in the form '1970-01-01 00:00:00'); only user accounts with a registration date after the date specified
 *                                                      will be returned.
 *                    $args['regdatebefore'] (string) An SQL date-time (in the form '1970-01-01 00:00:00'); only user accounts with a registration date before the date specified
 *                                                      will be returned.
 *                    $args['dynadata']      (array)  An array of search values to be passed to the designated profile module. Only those user records also satisfying the profile
 *                                                      module's search of its data are returned.
 *                    $args['condition']     (string) An SQL condition for finding users; overrides all other parameters.
 *
 * @return mixed array of items if succcessful, false otherwise
 */
function users_adminapi_findusers($args)
{
    // Need read access to call this function
    if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_READ)) {
        return false;
    }

    $profileModule = System::getVar('profilemodule', '');
    $useProfileMod = (!empty($profileModule) && ModUtil::available($profileModule));

    $pntable     = System::dbGetTables();
    $userstable  = $pntable['users'];
    $userscolumn = $pntable['users_column'];

    // Set query conditions (unless some one else sends a hardcoded one)
    if (!isset($args['condition']) || !$args['condition']) {
        // process all of these in one loop
        $args['condition'] = $userscolumn['uname'] . " != 'Anonymous'";
        $vars = array('uname', 'email');
        foreach ($vars as $var) {
            if (isset($args[$var]) && !empty($args[$var])) {
                $args['condition'] .= ' AND '.$userscolumn[$var].' LIKE \'%'.DataUtil::formatForStore($args[$var]).'%\'';
            }
        }

        // do the rest manually
        if (isset($args['ugroup']) && $args['ugroup']) {
            Loader::loadClass('UserUtil');
            $guids = UserUtil::getUsersForGroup($args['ugroup']);
            if (!empty($guids)) {
                $args['condition'] .= " AND $userscolumn[uid] IN (";
                foreach ($guids as $uid) {
                    $args['condition'] .= DataUtil::formatForStore($uid) . ',';
                }
                $args['condition'] .= '0)';
            }
        }
        if (isset($args['regdateafter']) && $args['regdateafter']) {
            $args['condition'] .= " AND $userscolumn[user_regdate] > '".DataUtil::formatForStore($args['regdateafter'])."'";
        }
        if (isset($args['regdatebefore']) && $args['regdatebefore']) {
            $args['condition'] .= " AND $userscolumn[user_regdate] < '".DataUtil::formatForStore($args['regdatebefore'])."'";
        }

        if ($useProfileMod) {
            // Check for attributes
            if (isset($args['dynadata']) && is_array($args['dynadata'])) {
                $uids = ModUtil::apiFunc($profileModule, 'user', 'searchdynadata', array('dynadata' => $args['dynadata']));
                if (is_array($uids) && !empty($uids)) {
                    $args['condition'] .= " AND $userscolumn[uid] IN (";
                    foreach ($uids as $uid) {
                        $args['condition'] .= DataUtil::formatForStore($uid) . ',';
                    }
                    $args['condition'] .= '0)';
                }
            }
        }
    }

    $where = 'WHERE ' . $args['condition'];

    $permFilter = array();
    $permFilter[] = array('realm' => 0,
                      'component_left'   => 'Users',
                      'component_middle' => '',
                      'component_right'  => '',
                      'instance_left'    => 'uname',
                      'instance_middle'  => '',
                      'instance_right'   => 'uid',
                      'level'            => ACCESS_READ);
    $objArray = DBUtil::selectObjectArray('users', $where, 'uname', null, null, null, $permFilter);

    return $objArray;
}

/**
 * Save a new user record.
 *
 * @param array $args All parameters passed to this function.
 *                    $args['uname']              (string) The user name to store on the new user record.
 *                    $args['email']              (string) The e-mail address to store on the new user record.
 *                    $args['pass']               (string) The new password to store on the new user record.
 *                    $args['vpass']              (string) A verification of the new password to store on the new user record.
 *                    $args['dynadata']           (array)  An array of additional information to be stored by the designated profile module, and linked to the newly created
 *                                                           user account.
 *                    $args['access_permissions'] (array)  Used only for 'edit' operations; an array of group ids to which the user should belong.
 *
 * @return bool true if successful, false otherwise.
 */
function users_adminapi_saveuser($args)
{
    if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_EDIT)) {
        return false;
    }

    // Checking for necessary basics
    if (!isset($args['uid']) || empty($args['uid']) || !isset($args['uname']) || empty($args['uname']) ||
        !isset($args['email'])  || empty($args['email'])) {
        return LogUtil::registerError(__('Error! One or more required fields were left blank or incomplete.'));
    }

    $checkpass = false;
    if (isset($args['pass']) && !empty($args['pass'])) {
        $checkpass = true;
    }

    if ($checkpass) {
        if (isset($args['pass']) && isset($args['vpass']) && $args['pass'] !== $args['vpass']) {
            return LogUtil::registerError(__('Error! You did not enter the same password in each password field. '
                . 'Please enter the same password once in each password field (this is required for verification).'));
        }

        $pass  = $args['pass'];
        $vpass = $args['vpass'];

        $minpass = ModUtil::getVar('Users', 'minpass');
        if (empty($pass) || strlen($pass) < $minpass) {
            return LogUtil::registerError(_fn('Your password must be at least %s character long', 'Your password must be at least %s characters long', $minpass, $minpass));
        }
        if (!empty($pass) && $pass) {
            $method = ModUtil::getVar('Users', 'hash_method', 'sha1');
            $hashmethodsarray = ModUtil::apiFunc('Users', 'user', 'gethashmethods');
            $args['pass'] = hash($method, $pass);
            $args['hash_method'] = $hashmethodsarray[$method];
        }
    } else {
        unset($args['pass']);
    }

    // process the dynamic data
    $dynadata = isset($args['dynadata']) ? $args['dynadata'] : array();

    $profileModule = System::getVar('profilemodule', '');
    $useProfileMod = (!empty($profileModule) && ModUtil::available($profileModule));
    if ($useProfileMod && $dynadata) {

        $checkrequired = ModUtil::apiFunc($profileModule, 'user', 'checkrequired',
                                      array('dynadata' => $dynadata));

        if ($checkrequired['result'] == true) {
            return LogUtil::registerError(__f('Error! A required item is missing from your profile information (%s).', $checkrequired['translatedFieldsStr']));
        }
    }

    if (isset($dynadata['publicemail']) && !empty($dynadata['publicemail'])) {
        $dynadata['publicemail'] = preg_replace('/[^a-zA-Z0-9_@.-]/', '', $dynadata['publicemail']);
    }

    if (isset($dynadata['url']) && !empty($dynadata['url'])) {
        $dynadata['url'] = preg_replace('/[^a-zA-Z0-9_@.&#?;:\/-]/', '', $dynadata['url']);
        if (!preg_match('/^http:\/\/[0-9a-z]+/i', $dynadata['url'])) {
            $dynadata['url'] = "http://" . $dynadata['url'];
        }
    }

    $args['dynadata'] = $dynadata;

    // call the profile manager to handle dynadata if needed
    if ($useProfileMod) {
        $adddata = ModUtil::apiFunc($profileModule, 'user', 'insertdyndata', $args);
        if (is_array($adddata)) {
            $args = array_merge($adddata, $args);
        }
    }

    DBUtil::updateObject($args, 'users', '', 'uid');

    // Fixing a high numitems to be sure to get all groups
    $groups = ModUtil::apiFunc('Groups', 'user', 'getall', array('numitems' => 1000));

    foreach ($groups as $group) {
        if (in_array($group['gid'], $args['access_permissions'])) {
            // Check if the user is already in the group
            $useringroup = false;
            $usergroups  = ModUtil::apiFunc('Groups', 'user', 'getusergroups', array('uid' => $args['uid']));
            if ($usergroups) {
                foreach ($usergroups as $usergroup) {
                    if ($group['gid'] == $usergroup['gid']) {
                        $useringroup = true;
                        break;
                    }
                }
            }
            // User is not in this group
            if ($useringroup == false) {
                ModUtil::apiFunc('Groups', 'admin', 'adduser', array('gid' => $group['gid'], 'uid' => $args['uid']));
            }
        } else {
            // We don't need to do a complex check, if the user is not in the group, the SQL will not return
            // an error anyway.
            if (SecurityUtil::checkPermission('Groups::', "$group[gid]::", ACCESS_EDIT)) {
                ModUtil::apiFunc('Groups', 'admin', 'removeuser', array('gid' => $group['gid'], 'uid' => $args['uid']));
            }
        }
    }

    // Let other modules know we have updated an item
    ModUtil::callHooks('item', 'update', $args['uid'], array('module' => 'Users'));

    return true;
}

/**
 * Delete one or more user account records.
 *
 * @param array $args All parameters passed to this function.
 *                    $args['uid'] (numeric|array) A single (int) user id, or an array of user ids to delete.
 *
 * @return bool True if successful, false otherwise.
 */
function users_adminapi_deleteuser($args)
{
    if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_DELETE)) {
        return false;
    }

    if (!isset($args['uid']) || !(is_numeric($args['uid']) || is_array($args['uid']))) {
        return LogUtil::registerError("Error! Illegal argument were passed to 'deleteuser'");
    }

    // ensure we always have an array
    if (!is_array($args['uid'])) {
        $args['uid'] = array(0 => $args['uid']);
    }

    foreach ($args['uid'] as $id) {
        if (!DBUtil::deleteObjectByID('group_membership', $id, 'uid')) {
            return false;
        }

        if (!DBUtil::deleteObjectByID('users', $id, 'uid')) {
            return false;
        }

        // Let other modules know we have deleted an item
        ModUtil::callHooks('item', 'delete', $id, array('module' => 'Users'));
    }

    return $args['uid'];
}

/**
 * Removes a registration request from the database, either because of a denial or because of an approval.
 * Internal use only. Not intended to be used through an API call. Security check done in the API function that
 * calls this.
 *
 * @param array $args All parameters passed to this function.
 *                    $args['userid'] (numeric) The tid of the temporary user record (registration request) to delete.
 *
 * @return bool True if successful, false otherwise.
 */
function _adminapi_removeRegistration($args)
{
    // Don't do a security check here. Do it in the function that calls this one.
    // This is an internal-only function.

    if (!isset($args['userid']) || !$args['userid']) {
        return false;
    }

    $res = DBUtil::deleteObjectByID('users_temp', $args['userid'], 'tid');

    return $res;
}

/**
 * Deny an application for a new user account (deny a registration request).
 *
 * @param array $args All parameters passed to this function.
 *                    $args['userid'] (int) The tid of the temporary user record (registration request) to deny.
 *
 * @return bool True if successful, false otherwise.
 */
function users_adminapi_deny($args)
{
    if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_DELETE)) {
        return false;
    }

    return _adminapi_removeRegistration($args);
}

/**
 * Approve an application for a new user account (approve a registration request).
 *
 * @param array $args All parameters passed to this function.
 *                    $args['userid'] (numeric) The tid of the temporary user record (registration request) to approve.
 *
 * @return true if successful, false otherwise
 */
function users_adminapi_approve($args)
{
    if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_ADD)) {
        return false;
    }

    if (!isset($args['userid']) || !$args['userid']) {
        return false;
    }

    $user = DBUtil::selectObjectByID('users_temp', $args['userid'], 'tid');

    if (!$user) {
        return $user;
    }

    $user['vpass']     = $user['pass'];
    $user['dynadata']  = unserialize($user['dynamics']);
    $user['moderated'] = true;

    $insert = ModUtil::apiFunc('Users', 'user', 'finishnewuser', $user);

    if ($insert) {
        // $insert has uid, we remove it from the temp
        $result = _adminapi_removeRegistration(array('userid' => $args['userid']));
    } else {
        $result = false;
    }

    return $result;
}

/**
 * Retrieve all pending applications for a new user account (all registration requests).
 *
 * @param array $args All parameters passed to this function.
 *                    $args['starnum']  (int) The ordinal number of the first item to return.
 *                    $args['numitems'] (int) The number (count) of items to return.
 *
 * @return array|bool Array of registration requests, or false on failure.
 */
function users_adminapi_getallpendings($args)
{
    if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_MODERATE)) {
        return false;
    }

    // Optional arguments.
    $startnum = (isset($args['startnum']) && is_numeric($args['startnum'])) ? $args['startnum'] : 1;
    $numitems = (isset($args['numitems']) && is_numeric($args['numitems'])) ? $args['numitems'] : -1;
    unset($args);

    $items = array();

    // Security check
    if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_OVERVIEW)) {
        return $items;
    }

    $pntable = System::dbGetTables();
    $userscolumn = $pntable['users_temp_column'];
    $where = "$userscolumn[type] = 1";
    $orderby = "ORDER BY $userscolumn[tid]";

    $result = DBUtil::selectObjectArray('users_temp', $where, $orderby, $startnum-1, $numitems, '');

    if ($result === false) {
        LogUtil::registerError(__('Error! Could not load data.'));
    }

    return $result;
}

/**
 * Retrieve one application for a new user account (one registration request).
 *
 * @param array $args All parameters passed to this function.
 *                    $args['userid'] (numeric) The tid of the temporary user record (registration request) to return.
 *
 * @return array|bool An array containing the record, or false on error.
 */
function users_adminapi_getapplication($args)
{
    if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_MODERATE)) {
        return false;
    }

    if (!isset($args['userid']) || !$args['userid']) {
        return false;
    }

    $item = DBUtil::selectObjectByID('users_temp', $args['userid'], 'tid');

    if ($item === false) {
        LogUtil::registerError(__('Error! Could not load data.'));
    }

    return $item;
}

/**
 * Returns the number of pending applications for new user accounts (registration requests).
 *
 * @return int|bool Numer of pending applications, false on error.
 */
function users_adminapi_countpending()
{
    if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_MODERATE)) {
        return false;
    }

    $pntable = System::dbGetTables();
    $userscolumn = $pntable['users_temp_column'];
    $where = "$userscolumn[type] = 1";
    return DBUtil::selectObjectCount('users_temp', $where);
}

/**
 * Get available admin panel links.
 *
 * @return array Array of admin links.
 */
function Users_adminapi_getlinks()
{
    $links = array();

    if (SecurityUtil::checkPermission('Users::', '::', ACCESS_MODERATE)) {
        $links[] = array('url' => ModUtil::url('Users', 'admin', 'view'), 'text' => __('Users list'), 'class' => 'z-icon-es-list');
    }
    if (SecurityUtil::checkPermission('Users::', '::', ACCESS_MODERATE)) {
        $pending = ModUtil::apiFunc('Users', 'admin', 'countpending');
        if ($pending) {
            $links[] = array('url' => ModUtil::url('Users', 'admin', 'viewapplications'), 'text' => __('Pending registrations') . ' ( '.DataUtil::formatForDisplay($pending).' )');
        }
    }
    if (SecurityUtil::checkPermission('Users::', '::', ACCESS_ADD)) {
        $links[] = array('url' => ModUtil::url('Users', 'admin', 'new'), 'text' => __('Create new user'), 'class' => 'z-icon-es-new');
        $links[] = array('url' => ModUtil::url('Users', 'admin', 'import'), 'text' => __('Import users'), 'class' => 'z-icon-es-import');
    }
    if (SecurityUtil::checkPermission('Users::', '::', ACCESS_ADMIN)) {
        $links[] = array('url' => ModUtil::url('Users', 'admin', 'export'), 'text' => __('Export users'), 'class' => 'z-icon-es-export');
    }
    if (SecurityUtil::checkPermission('Users::MailUsers', '::', ACCESS_MODERATE)) {
        $links[] = array('url' => ModUtil::url('Users', 'admin', 'search'), 'text' => __('Find and e-mail users'), 'class' => 'z-icon-es-mail');
    } else if (SecurityUtil::checkPermission('Users::', '::', ACCESS_MODERATE)) {
        $links[] = array('url' => ModUtil::url('Users', 'admin', 'search'), 'text' => __('Find users'), 'class' => 'z-icon-es-search');
    }
    if (SecurityUtil::checkPermission('Users::', '::', ACCESS_ADMIN)) {
        $links[] = array('url' => ModUtil::url('Users', 'admin', 'modifyconfig'), 'text' => __('Settings'), 'class' => 'z-icon-es-config');
    }

    $profileModule = System::getVar('profilemodule', '');
    $useProfileMod = (!empty($profileModule) && ModUtil::available($profileModule));
    if ($useProfileMod) {
        // Make sure there are links for the user to see in the submenu. Don't try
        // to guess at what permission level the profule module might have for its
        // links in its getlinks function. Just try to get the links and see if
        // it is not empty. If it is not empty, then the user has permissions for
        // at least one function in there (maybe more).
        $profileAdminLinks = ModUtil::apiFunc($profileModule, 'admin', 'getlinks');
        if (!empty($profileAdminLinks)) {
            if (ModUtil::getName() == 'Users') {
                $links[] = array('url' => 'javascript:showdynamicsmenu()', 'text' => __('Account panel manager'), 'class' => 'z-icon-es-profile');
            } else {
                $links[] = array('url' => ModUtil::url($profileModule, 'admin', 'main'), 'text' => __('Account panel manager'), 'class' => 'z-icon-es-profile');
            }
        }
    }

    return $links;
}

/**
 * Retrieve an array of user records whose field specified by the key parameter match one of the values specified in the valuesArray parameter.
 *
 * @param array $args All parameters passed to this function.
 *                    $args['key']      (string) The field to be searched, typically 'uname' or 'email'.
 *                    $args['keyValue'] (array)  An array containing the values to be matched.
 *
 * @return array|bool An array of user records indexed by user name, each whose key field matches one value in the valueArray; false on error.
 */
function Users_adminapi_checkMultipleExistence($args)
{
    // Need read access to call this function
    if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_READ)) {
        return false;
    }

    $pntable = System::dbGetTables();
    $userscolumn = $pntable['users_column'];

    $valuesArray = $args['valuesArray'];
    $key = $args['key'];

    $where = '';
    foreach ($valuesArray as $value) {
        $where .=  $userscolumn[$key] . "='" . $value . "' OR ";
    }

    $where = substr($where, 0, -3);

    $items = DBUtil::selectObjectArray ('users', $where, '', '-1', '-1', 'uname');

    if ($items === false) {
        return false;
    }

    return $items;
}

/**
 * Add new user accounts from the import process.
 *
 * @param array $args All parameters passed to this function.
 *                    $args['importValues'] (array) An array of information used to create new user records.
 *
 * @return bool True on success; false otherwise.
 */
function Users_adminapi_createImport($args)
{
    // Need add access to call this function
    if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_ADD)) {
        return false;
    }

    $importValues = $args['importValues'];

    if (empty($importValues)) {
        return false;
    }

    $pntable = System::dbGetTables();
    $userstable = $pntable['users'];
    $userscolumn = $pntable['users_column'];

    // get encrypt method for passwords
    $method = ModUtil::getVar('Users', 'hash_method');
    $methodNumberArray = ModUtil::apiFunc('Users','user','gethashmethods', array('reverse' => false));
    $methodNumber = $methodNumberArray[$method];

    $createUsersSQL = "INSERT INTO " . $userstable . "($userscolumn[uname],$userscolumn[email],$userscolumn[activated],$userscolumn[pass],$userscolumn[hash_method]) VALUES ";

    // construct a sql statement with all the inserts to avoid to much database connections
    foreach ($importValues as $value) {
        $value = DataUtil::formatForStore($value);
        $password = DataUtil::hash(trim($value['pass']), $method);
        $createUsersSQL .= "('" . trim($value['uname']) . "','" . trim($value['email']) . "'," . $value['activated'] . ",'" . $password . "', $methodNumber),";
        $usersArray[] = $value['uname'];
    }

    $createUsersSQL = substr($createUsersSQL, 0 , -1) . ';';

    // execute sql to create users
    $result = DBUtil::executeSQL($createUsersSQL);
    if (!$result) {
        return false;
    }

    // get users. We need the users identities set them into their groups
    $usersInDB = ModUtil::apiFunc('Users', 'admin', 'checkMultipleExistence',
                                  array('valuesArray' => $usersArray,
                                        'key' => 'uname'));
    if (!$usersInDB) {
        return LogUtil::registerError(__('Error! The users have been created but something has failed trying to get them from the database. '
            . 'Now all these users do not have group.'));
    }

    // get available groups
    $allGroups = ModUtil::apiFunc('Groups','user','getall');

    // create an array with the groups identities where the user can add other users
    $allGroupsArray = array();
    foreach ($allGroups as $group) {
        if (SecurityUtil::checkPermission('Groups::', $group['name'] . '::' . $group['gid'], ACCESS_EDIT)) {
            $allGroupsArray[] = $group['gid'];
        }
    }

    $groupstable = $pntable['group_membership'];
    $groupscolumn = $pntable['group_membership_column'];

    $addUsersToGroupsSQL = "INSERT INTO " . $groupstable . "({$groupscolumn['uid']},{$groupscolumn['gid']}) VALUES ";

    // construct a sql statement with all the inserts to avoid to much database connections
    foreach ($importValues as $value) {
        $groupsArray = explode('|', $value['groups']);
        foreach ($groupsArray as $group) {
            if (in_array(trim($group), $allGroupsArray)) {
                $addUsersToGroupsSQL .= "(" . $usersInDB[$value['uname']]['uid'] . "," . $group . "),";
            }
        }
    }

    $addUsersToGroupsSQL = substr($addUsersToGroupsSQL, 0 , -1) . ';';

    // execute sql to create users
    $result = DBUtil::executeSQL($addUsersToGroupsSQL);
    if (!$result) {
        return LogUtil::registerError(__('Error! The users have been created but something has failed while trying to add the users to their groups. '
            . 'Now all these users do not have group.'));
    }

    // check if module Mailer is active
    $modinfo = ModUtil::getInfo(ModUtil::getIdFromName('Mailer'));
    if ($modinfo['state'] == 3) {
        $sitename  = System::getVar('sitename');
        $siteurl   = System::getBaseUrl();
        $pnRender = Renderer::getInstance('Users', false);
        $pnRender->assign('sitename', $sitename);
        $pnRender->assign('siteurl', $siteurl);
        foreach ($importValues as $value) {
            if ($value['activated'] == 1 && $value['sendMail'] == 1) {
                $pnRender->assign('email', $value['email']);
                $pnRender->assign('uname', $value['uname']);
                $pnRender->assign('pass', $value['pass']);
                $message = $pnRender->fetch('users_adminapi_notifyemail.htm');
                $subject = __f('Password for %1$s from %2$s', array($value['uname'], $sitename));
                if (!ModUtil::apiFunc('Mailer', 'user', 'sendmessage',
                                    array('toaddress' => $value['email'],
                                          'subject' => $subject,
                                          'body' => $message,
                                          'html' => true)))
                {
                    LogUtil::registerError(__f('Error! A problem has occurred while sending e-mail messages. The error happened trying to send a message to the user %s. '
                        . 'After this error, no more messages were sent.', $value['uname']));
                    break;
                }
            }
        }
    }

    return true;
}
