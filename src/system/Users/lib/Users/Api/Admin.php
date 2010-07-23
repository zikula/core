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
 * The Admin API provides administrative system-level and database-level functions for modules;
 * this class provides those functions for the Users module.
 *
 * @package Zikula
 * @subpackage Users
 */
class Users_Api_Admin extends Zikula_Api
{
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
    public function findUsers($args)
    {
        // Need read access to call this function
        if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_READ)) {
            return false;
        }

        $profileModule = System::getVar('profilemodule', '');
        $useProfileMod = (!empty($profileModule) && ModUtil::available($profileModule));

        $dbtable     = DBUtil::getTables();
        $userstable  = $dbtable['users'];
        $userscolumn = $dbtable['users_column'];

        // Set query conditions (unless some one else sends a hardcoded one)
        $where = array();
        if (!isset($args['condition']) || !$args['condition']) {
            // Do not include anonymous user
            $where[] = "({$userscolumn['uid']} != 1)";

            foreach ($args as $arg => $value) {
                if ($value) {
                    switch($arg) {
                        case 'uname':
                            // Fall through to next on purpose--no break
                        case 'email':
                            $where[] = "({$userscolumn[$arg]} LIKE '%".DataUtil::formatForStore($value)."%')";
                            break;
                        case 'ugroup':
                            $uidList = UserUtil::getUsersForGroup($value);
                            if (is_array($uidList) && !empty($uidList)) {
                                $where[] = "({$userscolumn['uid']} IN (" . implode(', ', $uidList) . "))";
                            }
                            break;
                        case 'regdateafter':
                            $where[] = "({$userscolumn['user_regdate']} > '"
                                . DataUtil::formatForStore($value) . "')";
                            break;
                        case 'regdatebefore':
                            $where[] = "({$userscolumn['user_regdate']} < '"
                                . DataUtil::formatForStore($value) . "')";
                            break;
                        case 'dynadata':
                            if ($useProfileMod) {
                                $uidList = ModUtil::apiFunc($profileModule, 'user', 'searchDynadata', array(
                                    'dynadata' => $value
                                ));
                                if (is_array($uidList) && !empty($uidList)) {
                                    $where[] = "({$userscolumn['uid']} IN (" . implode(', ', $uidList) . "))";
                                }
                            }
                            break;
                        default:
                            // Skip unknown values--do nothing, and no error--might be other legitimate arguments.
                    }
                }
            }
        }
        // TODO - Should this exclude pending delete too?
        $where[] = "({$userscolumn['activated']} != " . UserUtil::ACTIVATED_PENDING_REG . ")";
        $where = 'WHERE ' . implode(' AND ', $where);

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
     * Save an updated user record.
     *
     * @param array $args All parameters passed to this function.
     *                    array  $args['userinfo']          The updated user information.
     *                    string $args['emailagain']        A verification of the new e-mail address to store on the
     *                                                          user record, required.
     *                    string $args['passagain']         A verification of the new password to store on the user
     *                                                          record, required if $args['userinfo']['pass'] is set.
     *                    array $args['access_permissions'] An array of group ids to which the user should belong.
     *
     * @return bool true if successful, false otherwise.
     */
    public function updateUser($args)
    {
        if (!SecurityUtil::checkPermission('Users::', 'ANY', ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }

        // Checking for necessary basics
        if (!isset($args['userinfo']) || !is_array($args['userinfo']) || empty($args['userinfo'])) {
            return LogUtil::registerArgsError();
        }

        $userInfo = $args['userinfo'];
        if (!isset($userInfo['uid']) || empty($userInfo['uid']) || !isset($userInfo['uname'])
            || empty($userInfo['uname']) || !isset($userInfo['email'])  || empty($userInfo['email']))
        {
            return LogUtil::registerArgsError();
        }

        $oldUserObj = UserUtil::getVars($userInfo['uid']);
        if (!$oldUserObj) {
            return LogUtil::registerError($this->__('Error! Could not find the user record in order to update it.'));
        } elseif (!SecurityUtil::checkPermission('Users::', "{$oldUserObj['uname']}::{$oldUserObj['uid']}", ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }

        if (isset($userInfo['pass']) && !empty($userInfo['pass'])) {
            $setpass = true;
        } else {
            $setpass = false;
        }

        $registrationErrors = ModUtil::apiFunc('Users', 'registration', 'getRegistrationErrors', array(
            'checkmode'  => 'modify',
            'setpass'    => $setpass,
            'reginfo'    => $userInfo,
            'passagain'  => $args['passagain'],
            'emailagain' => $args['emailagain'],
        ));
        if ($registrationErrors) {
            foreach ($registrationErrors as $fieldGroup) {
                foreach ($fieldGroup as $message) {
                    LogUtil::registerError($message);
                }
            }
            return false;
        }

        if ($setpass) {
            $userInfo['pass'] = UserUtil::getHashedPassword($userInfo['pass']);
        } else {
            unset($userInfo['pass']);
        }

        // process the dynamic data
        $dynadata = isset($userInfo['dynadata']) ? $userInfo['dynadata'] : array();
        unset($userInfo['dynadata']);

        // call the profile manager to handle dynadata if needed
        $profileModule = System::getVar('profilemodule', '');
        $useProfileMod = (!empty($profileModule) && ModUtil::available($profileModule));
        if ($useProfileMod) {
            $adddata = ModUtil::apiFunc($profileModule, 'user', 'insertDyndata', $userInfo);
            if (is_array($adddata)) {
                $userInfo = array_merge($adddata, $userInfo);
            }
        }

        DBUtil::updateObject($userInfo, 'users', '', 'uid');

        // Fixing a high numitems to be sure to get all groups
        $groups = ModUtil::apiFunc('Groups', 'user', 'getAll', array('numitems' => 10000));
        $curUserGroupMembership = ModUtil::apiFunc('Groups', 'user', 'getUserGroups', array('uid' => $userInfo['uid']));

        foreach ($groups as $group) {
            if (in_array($group['gid'], $args['access_permissions'])) {
                // Check if the user is already in the group
                $userIsMember = false;
                if ($curUserGroupMembership) {
                    foreach ($curUserGroupMembership as $alreadyMemberOf) {
                        if ($group['gid'] == $alreadyMemberOf['gid']) {
                            $userIsMember = true;
                            break;
                        }
                    }
                }
                if ($userIsMember == false) {
                    // User is not in this group
                    ModUtil::apiFunc('Groups', 'admin', 'addUser', array(
                        'gid' => $group['gid'],
                        'uid' => $userInfo['uid']
                    ));
                    $curUserGroupMembership[] = $group;
                }
            } else {
                // We don't need to do a complex check, if the user is not in the group, the SQL will not return
                // an error anyway.
                ModUtil::apiFunc('Groups', 'admin', 'removeUser', array(
                    'gid' => $group['gid'],
                    'uid' => $userInfo['uid']
                ));
            }
        }

        // Let other modules know we have updated an item
        $updateEvent = new Zikula_Event('user.update', $userInfo);
        $this->eventManager->notify($updateEvent);

        $this->callHooks('item', 'update', $userInfo['uid'], array('module' => 'Users'));

        return true;
    }

    /**
     * Delete one or more user account records, or mark one or more account records for deletion.
     *
     * If records are marked for deletion, they remain in the system and accessible by the system, but are given an
     * 'activated' status that prevents the user from logging in. Records marked for deletion will not appear on the
     * regular users list. The delete hook and delete events are not triggered if the records are only marked for
     * deletion.
     *
     * @param array $args All parameters passed to this function.
     *                    $args['uid']  numeric|array A single (int) user id, or an array of user ids to delete.
     *                    $args['mark'] bool          If true, then mark for deletion, but do not actually delete.
     *                                                  defaults to false.
     *
     * @return bool True if successful, false otherwise.
     */
    public function deleteUser($args)
    {
        if (!SecurityUtil::checkPermission('Users::', 'ANY', ACCESS_DELETE)) {
            return false;
        }

        if (!isset($args['uid']) || (!is_numeric($args['uid']) && !is_array($args['uid']))) {
            return LogUtil::registerError("Error! Illegal argument were passed to 'deleteuser'");
        }

        if (isset($args['mark']) && is_bool($args['mark'])) {
            $markOnly = $args['mark'];
        } else {
            $markOnly = false;
        }

        // ensure we always have an array
        if (!is_array($args['uid'])) {
            $args['uid'] = array($args['uid']);
        }

        $curUserUid = UserUtil::getVar('uid');
        $userList = array();
        foreach ($args['uid'] as $uid) {
            if (!is_numeric($uid) || ((int)$uid != $uid) || ($uid == $curUserUid)) {
                return false;
            }
            $userObj = UserUtil::getVars($uid);
            if (!$userObj) {
                return false;
            } elseif (!SecurityUtil::checkPermission('Users::', "{$userObj['uname']}::{$userObj['uid']}", ACCESS_DELETE)) {
                return false;
            }

            $userList[] = $userObj;
        }


        foreach ($userList as $userObj) {
            if ($markOnly) {
                UserUtil::setVar('activated', UserUtil::ACTIVATED_PENDING_DELETE, $userObj['uid']);
            } else {
                // TODO - This should be in the Groups module, and happen as a result of an event.
                if (!DBUtil::deleteObjectByID('group_membership', $userObj['uid'], 'uid')) {
                    return false;
                }

                ModUtil::apiFunc('Users', 'user', 'resetVerifyChgFor', array('uid' => $userObj['uid']));
                DBUtil::deleteObjectByID('session_info', $userObj['uid'], 'uid');

                if (!DBUtil::deleteObject($userObj, 'users', '', 'uid')) {
                    return false;
                }

                // Let other modules know we have deleted an item
                $deleteEvent = new Zikula_Event('user.delete', $userObj);
                $this->eventManager->notify($deleteEvent);

                $this->callHooks('item', 'delete', $userObj['uid'], array('module' => 'Users'));
            }
        }

        return $args['uid'];
    }

    /**
     * Get available admin panel links.
     *
     * @return array Array of admin links.
     */
    public function getLinks()
    {
        $links = array();

        if (SecurityUtil::checkPermission('Users::', '::', ACCESS_MODERATE)) {
            $links[] = array('url' => ModUtil::url('Users', 'admin', 'view'), 'text' => $this->__('Users list'), 'class' => 'z-icon-es-list');
        }
        if (SecurityUtil::checkPermission('Users::', '::', ACCESS_MODERATE)) {
            $pending = ModUtil::apiFunc('Users', 'registration', 'countAll');
            if ($pending) {
                $links[] = array('url' => ModUtil::url('Users', 'admin', 'viewRegistrations'), 'text' => $this->__('Pending registrations') . ' ('.DataUtil::formatForDisplay($pending).')', 'class' => 'z-icon-es-adduser');
            }
        }
        if (SecurityUtil::checkPermission('Users::', '::', ACCESS_ADD)) {
            $links[] = array('url' => ModUtil::url('Users', 'admin', 'newUser'), 'text' => $this->__('Create new user'), 'class' => 'z-icon-es-new');
            $links[] = array('url' => ModUtil::url('Users', 'admin', 'import'), 'text' => $this->__('Import users'), 'class' => 'z-icon-es-import');
        }
        if (SecurityUtil::checkPermission('Users::', '::', ACCESS_ADMIN)) {
            $links[] = array('url' => ModUtil::url('Users', 'admin', 'exporter'), 'text' => $this->__('Export users'), 'class' => 'z-icon-es-export');
        }
        if (SecurityUtil::checkPermission('Users::MailUsers', '::', ACCESS_MODERATE)) {
            $links[] = array('url' => ModUtil::url('Users', 'admin', 'search'), 'text' => $this->__('Find and e-mail users'), 'class' => 'z-icon-es-mail');
        } else if (SecurityUtil::checkPermission('Users::', '::', ACCESS_MODERATE)) {
            $links[] = array('url' => ModUtil::url('Users', 'admin', 'search'), 'text' => $this->__('Find users'), 'class' => 'z-icon-es-search');
        }
        if (SecurityUtil::checkPermission('Users::', '::', ACCESS_ADMIN)) {
            $links[] = array('url' => ModUtil::url('Users', 'admin', 'modifyConfig'), 'text' => $this->__('Settings'), 'class' => 'z-icon-es-config');
        }

        $profileModule = System::getVar('profilemodule', '');
        $useProfileMod = (!empty($profileModule) && ModUtil::available($profileModule));
        if ($useProfileMod) {
            // Make sure there are links for the user to see in the submenu. Don't try
            // to guess at what permission level the profule module might have for its
            // links in its getlinks function. Just try to get the links and see if
            // it is not empty. If it is not empty, then the user has permissions for
            // at least one function in there (maybe more).
            $profileAdminLinks = ModUtil::apiFunc($profileModule, 'admin', 'getLinks');
            if (!empty($profileAdminLinks)) {
                if (ModUtil::getName() == 'Users') {
                    $links[] = array('url' => 'javascript:showdynamicsmenu()', 'text' => $this->__('Account panel manager'), 'class' => 'z-icon-es-profile');
                } else {
                    $links[] = array('url' => ModUtil::url($profileModule, 'admin', 'main'), 'text' => $this->__('Account panel manager'), 'class' => 'z-icon-es-profile');
                }
            }
        }

        return $links;
    }

    /**
     * Retrieve a list of users whose field specified by the key match one of the values specified in the keyValue.
     *
     * @param array $args All parameters passed to this function.
     *                    $args['key']      (string) The field to be searched, typically 'uname' or 'email'.
     *                    $args['keyValue'] (array)  An array containing the values to be matched.
     *
     * @return array|bool An array of user records indexed by user name, each whose key field matches one value in the
     *                      valueArray; false on error.
     */
    public function checkMultipleExistence($args)
    {
        // Need read access to call this function
        if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_READ)) {
            return false;
        }

        $dbtable = DBUtil::getTables();
        $userscolumn = $dbtable['users_column'];

        $valuesArray = $args['valuesArray'];
        $key = $args['key'];

        $where = "WHERE ({$userscolumn[$key]} IN ('" . implode("', '", $valuesArray) . "'))";
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
    public function createImport($args)
    {
        // Need add access to call this function
        if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_ADD)) {
            return false;
        }

        $importValues = $args['importValues'];

        if (empty($importValues)) {
            return false;
        }

        // Prepare arrays.
        foreach ($importValues as $key => $value) {
            $usersArray[] = $value['uname'];
            $importValues[$key]['pass'] = UserUtil::getHashedPassword($importValues[$key]['pass']);
        }

        // execute sql to create users
        $result = DBUtil::insertObjectArray($importValues, 'users', 'uid');
        if (!$result) {
            return false;
        }

        // get users. We need the users identities set them into their groups
        $usersInDB = ModUtil::apiFunc('Users', 'admin', 'checkMultipleExistence',
                                      array('valuesArray' => $usersArray,
                                            'key' => 'uname'));
        if (!$usersInDB) {
            return LogUtil::registerError($this->__(
                'Error! The users have been created but something has failed trying to get them from the database. '
                . 'Now all these users do not have group.'));
        }

        // get available groups
        $allGroups = ModUtil::apiFunc('Groups', 'user', 'getAll');

        // create an array with the groups identities where the user can add other users
        $allGroupsArray = array();
        foreach ($allGroups as $group) {
            if (SecurityUtil::checkPermission('Groups::', $group['name'] . '::' . $group['gid'], ACCESS_EDIT)) {
                $allGroupsArray[] = $group['gid'];
            }
        }

        $groups = array();
        // construct a sql statement with all the inserts to avoid to much database connections
        foreach ($importValues as $value) {
            $groupsArray = explode('|', $value['groups']);
            foreach ($groupsArray as $group) {
                $groups[] = array('uid' => $usersInDB[$value['uname']]['uid'], 'gid' => $group);
            }
        }

        // execute sql to create users
        $result = DBUtil::insertObjectArray($groups, 'group_membership', 'gid', true);
        if (!$result) {
            return LogUtil::registerError($this->__('Error! The users have been created but something has failed while trying to add the users to their groups. These users are not assigned to a group.'));
        }

        // check if module Mailer is active
        $modinfo = ModUtil::getInfoFromName('Mailer');
        if ($modinfo['state'] == ModUtil::TYPE_SYSTEM) {
            $sitename  = System::getVar('sitename');
            $siteurl   = System::getBaseUrl();

            $renderer = Zikula_View::getInstance('Users', false);
            $renderer->assign('sitename', $sitename);
            $renderer->assign('siteurl', $siteurl);

            foreach ($importValues as $value) {
                if ($value['activated'] == UserUtil::ACTIVATED_ACTIVE && $value['sendMail'] == 1) {
                    $renderer->assign('email', $value['email']);
                    $renderer->assign('uname', $value['uname']);
                    $renderer->assign('pass', $value['pass']);
                    $message = $renderer->fetch('users_email_importnotify_html.tpl');
                    $subject = $this->__f('Password for %1$s from %2$s', array($value['uname'], $sitename));
                    if (!ModUtil::apiFunc('Mailer', 'user', 'sendMessage',
                                        array('toaddress' => $value['email'],
                                              'subject' => $subject,
                                              'body' => $message,
                                              'html' => true)))
                    {
                        LogUtil::registerError($this->__f('Error! A problem has occurred while sending e-mail messages. The error happened trying to send a message to the user %s. After this error, no more messages were sent.', $value['uname']));
                        break;
                    }
                }
            }
        }

        return true;
    }
}
