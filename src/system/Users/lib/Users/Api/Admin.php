<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage Users
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * The administrative system-level and database-level functions for the Users module.
 */
class Users_Api_Admin extends Zikula_AbstractApi
{
    /**
     * Find users.
     *
     * @param array $args All parameters passed to this function.
     *                      string $args['uname']         A fragment of a user name on which to search using an SQL
     *                                                      LIKE clause. The user name will be surrounded by wildcards.
     *                      int    $args['ugroup']        A group id in which to search (only users who are members of
     *                                                      the specified group are returned).
     *                      string $args['email']         A fragment of an e-mail address on which to search using an
     *                                                      SQL LIKE clause. The e-mail address will be surrounded by
     *                                                      wildcards.
     *                      string $args['regdateafter']  An SQL date-time (in the form '1970-01-01 00:00:00'); only
     *                                                      user accounts with a registration date after the date
     *                                                      specified will be returned.
     *                      string $args['regdatebefore'] An SQL date-time (in the form '1970-01-01 00:00:00'); only
     *                                                      user accounts with a registration date before the date
     *                                                      specified will be returned.
     *                      array  $args['dynadata']      An array of search values to be passed to the designated
     *                                                      profile module. Only those user records also satisfying the
     *                                                      profile module's search of its data are returned.
     *                      string $args['condition']     An SQL condition for finding users; overrides all other
     *                                                      parameters.
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
        $where[] = "({$userscolumn['activated']} != " . Users_Constant::ACTIVATED_PENDING_REG . ")";
        $where = 'WHERE ' . implode(' AND ', $where);

        $permFilter = array();
        $permFilter[] = array(
            'realm'             => 0,
            'component_left'    => $this->name,
            'component_middle'  => '',
            'component_right'   => '',
            'instance_left'     => 'uname',
            'instance_middle'   => '',
            'instance_right'    => 'uid',
            'level'             => ACCESS_READ,
        );
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
     * 
     * @throws Zikula_Exception_Forbidden If the user does not have edit access to any user account records.
     */
    public function updateUser($args)
    {
        // check permission to edit any generic user
        if (!SecurityUtil::checkPermission('Users::', 'ANY', ACCESS_EDIT)) {
            throw new Zikula_Exception_Forbidden();
        }

        // Checking for necessary basics
        if (!isset($args['userinfo']) || !is_array($args['userinfo']) || empty($args['userinfo'])) {
            $this->registerError(LogUtil::getErrorMsgArgs());
            return false;
        }

        $updatedUser = $args['userinfo'];
        if (!isset($updatedUser['uid']) || empty($updatedUser['uid']) || !isset($updatedUser['uname'])
                || empty($updatedUser['uname']) || !isset($updatedUser['email'])  || empty($updatedUser['email'])) {

            $this->registerError(LogUtil::getErrorMsgArgs());
            return false;
        }

        $isRegistration = UserUtil::isRegistration($updatedUser['uid']);
        $originalUser = UserUtil::getVars($updatedUser['uid'], true, 'uid', $isRegistration);
        
        if (!$originalUser) {
            $this->registerError($this->__('Error! Could not find the user record in order to update it.'));
            return false;
        } elseif (!SecurityUtil::checkPermission('Users::', "{$originalUser['uname']}::{$originalUser['uid']}", ACCESS_EDIT)) {
            // above elseif checks permission to edit the specific user
            throw new Zikula_Exception_Forbidden();
        }

        if (isset($updatedUser['pass']) && !empty($updatedUser['pass'])) {
            $setpass = true;
        } else {
            $setpass = false;
        }

        $registrationErrors = ModUtil::apiFunc($this->name, 'registration', 'getRegistrationErrors', array(
            'checkmode'  => 'modify',
            'setpass'    => $setpass,
            'reginfo'    => $updatedUser,
            'passagain'  => isset($args['passagain']) ? $args['passagain'] : '',
            'emailagain' => $args['emailagain'],
        ));
        if ($registrationErrors) {
            foreach ($registrationErrors as $message) {
                $this->registerError($message);
            }
            return false;
        }

        if ($setpass) {
            $updatedUser['pass'] = UserUtil::getHashedPassword($updatedUser['pass']);
        } else {
            unset($updatedUser['pass']);
        }

        DBUtil::updateObject($updatedUser, 'users', '', 'uid');

        if ($args['access_permissions'] !== false) {
            // Fixing a high numitems to be sure to get all groups
            $groups = ModUtil::apiFunc('Groups', 'user', 'getAll', array('numitems' => 10000));
            $curUserGroupMembership = ModUtil::apiFunc('Groups', 'user', 'getUserGroups', array('uid' => $updatedUser['uid']));

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
                            'uid' => $updatedUser['uid']
                        ));
                        $curUserGroupMembership[] = $group;
                    }
                } else {
                    // We don't need to do a complex check, if the user is not in the group, the SQL will not return
                    // an error anyway.
                    ModUtil::apiFunc('Groups', 'admin', 'removeUser', array(
                        'gid' => $group['gid'],
                        'uid' => $updatedUser['uid']
                    ));
                }
            }
        }

        // Let other modules know we have updated an item
        if ($isRegistration) {
            $updateEvent = new Zikula_Event('registration.update', $updatedUser);
        } else {
            $updateEvent = new Zikula_Event('user.update', $updatedUser);
        }
        $this->eventManager->notify($updateEvent);

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
            $this->registerError("Error! Illegal argument were passed to 'deleteuser'");
            return false;
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
                UserUtil::setVar('activated', Users_Constant::ACTIVATED_PENDING_DELETE, $userObj['uid']);
            } else {
                // TODO - This should be in the Groups module, and happen as a result of an event.
                if (!DBUtil::deleteObjectByID('group_membership', $userObj['uid'], 'uid')) {
                    return false;
                }

                ModUtil::apiFunc($this->name, 'user', 'resetVerifyChgFor', array('uid' => $userObj['uid']));
                DBUtil::deleteObjectByID('session_info', $userObj['uid'], 'uid');

                if (!DBUtil::deleteObject($userObj, 'users', '', 'uid')) {
                    return false;
                }

                // Let other modules know we have deleted an item
                $deleteEvent = new Zikula_Event('user.delete', $userObj);
                $this->eventManager->notify($deleteEvent);
            }
        }

        return $args['uid'];
    }

    /**
     * Send an e-mail message to one or more users.
     *
     * @param array $args All arguments passed to this function.
     *
     * @return bool True on success; otherwise false
     * 
     * @throws Zikula_Exception_Forbidden if the current user does not have sufficient access to send mail.
     */
    public function sendmail($args)
    {
        if (!SecurityUtil::checkPermission('Users::MailUsers', '::', ACCESS_COMMENT)) {
            throw new Zikula_Exception_Forbidden();
        }

        if (isset($args['uid']) && !empty($args['uid'])) {
            if (is_array($args['uid'])) {
                $recipientUidList = $args['uid'];
            } else {
                $recipientUidList = array($args['uid']);
            }
        } else {
            $this->registerError(__('Error! No users selected for removal, or invalid uid list.'));
            return false;
        }

        if (isset($args['sendmail']) && !empty($args['sendmail']) && is_array($args['sendmail'])) {
            $sendmail = $args['sendmail'];
        } else {
            $this->registerError(__('Error! E-mail message to be sent not specified or invalid.'));
            return false;
        }

        $missingFields = array();
        if (empty($sendmail['from'])) {
            $missingFields[] = 'from';
        }
        if (empty($sendmail['rpemail'])) {
            $missingFields[] = 'reply-to e-mail address';
        }
        if (empty($sendmail['subject'])) {
            $missingFields[] = 'subject';
        }
        if (empty($sendmail['message'])) {
            $missingFields[] = 'message';
        }
        if (!empty($missingFields)) {
            $count = count($missingFields);
            $msg = _fn('Error! The required field \'%2$s\' was blank or missing',
                    'Error! %1$d required fields were blank or missing: \'%2$s\'.',
                    $count, array($count, implode("', '", $missingFields)));
            $this->registerError($msg);
            return false;
        }
        unset($missingFields);

        $bcclist = array();
        $recipientlist = array();
        $recipientscount = 0;
        foreach ($sendmail['recipientsemail'] as $uid => $recipient) {
            if (in_array($uid, $recipientUidList)) {
                $bcclist[] = array('name'    => $sendmail['recipientsname'][$uid],
                                   'address' => $recipient);
                $recipientlist[] = $recipient;
            }
            if (count($bcclist) == $sendmail['batchsize']) {
                if (ModUtil::apiFunc('Mailer', 'user', 'sendmessage',
                                 array('fromname'       => $sendmail['from'],
                                       'fromaddress'    => $sendmail['rpemail'],
                                       'toname'         => UserUtil::getVar('uname'),
                                       'toaddress'      => UserUtil::getVar('email'),
                                       'replytoname'    => UserUtil::getVar('uname'),
                                       'replytoaddress' => $sendmail['rpemail'],
                                       'subject'        => $sendmail['subject'],
                                       'body'           => $sendmail['message'],
                                       'bcc'            => $bcclist)) == true) {
                    $recipientscount += count($bcclist);
                    $bcclist = array();
                } else {
                    $this->registerError($this->__('Error! Could not send the e-mail message.'));
                    return false;
                }
            }
        }
        if (count($bcclist) <> 0) {
            $sendMessageArgs = array(
                'fromname'      => $sendmail['from'],
                'fromaddress'   => $sendmail['rpemail'],
                'toname'        => UserUtil::getVar('uname'),
                'toaddress'     => UserUtil::getVar('email'),
                'replytoname'   => UserUtil::getVar('uname'),
                'replytoaddress'=> $sendmail['rpemail'],
                'subject'       => $sendmail['subject'],
                'body'          => $sendmail['message'],
                'bcc'           => $bcclist,
            );
            if (ModUtil::apiFunc('Mailer', 'user', 'sendMessage', $sendMessageArgs)) {
                $recipientscount += count($bcclist);
            } else {
                $this->registerError($this->__('Error! Could not send the e-mail message.'));
                return false;
            }
        }
        if ($recipientscount > 0) {
            $this->registerStatus($this->_fn(
                'Done! E-mail message has been sent to %1$d user. ',
                'Done! E-mail message has been sent to %1$d users. ',
                $recipientscount,
                array($recipientscount)
            ));
        }
        return true;
    }

    /**
     * Get available admin panel links.
     *
     * @return array Array of admin links.
     */
    public function getLinks()
    {
        $links = array();
        $submenulinks = array();

        if (SecurityUtil::checkPermission('Users::', '::', ACCESS_MODERATE)) {
            $links[] = array('url' => ModUtil::url($this->name, 'admin', 'view'), 'text' => $this->__('Users list'), 'class' => 'z-icon-es-view');
        }
        if (SecurityUtil::checkPermission('Users::', '::', ACCESS_MODERATE)) {
            $pending = ModUtil::apiFunc($this->name, 'registration', 'countAll');
            if ($pending) {
                $links[] = array('url' => ModUtil::url($this->name, 'admin', 'viewRegistrations'), 'text' => $this->__('Pending registrations') . ' ('.DataUtil::formatForDisplay($pending).')', 'class' => 'user-icon-adduser');
            }
        }
        if (SecurityUtil::checkPermission('Users::', '::', ACCESS_ADD)) {
            $submenulinks[] = array('url' => ModUtil::url($this->name, 'admin', 'newUser'), 'text' => $this->__('Create new user'));
            $submenulinks[] = array('url' => ModUtil::url($this->name, 'admin', 'import'), 'text' => $this->__('Import users'));
            if (SecurityUtil::checkPermission('Users::', '::', ACCESS_ADMIN)) {
                 $submenulinks[] = array('url' => ModUtil::url($this->name, 'admin', 'exporter'), 'text' => $this->__('Export users'));
            }
            $links[] = array('url' => ModUtil::url($this->name, 'admin', 'newUser'), 'text' => $this->__('Create new user'), 'class' => 'z-icon-es-new', 'links' => $submenulinks);
        }
        if (SecurityUtil::checkPermission('Users::', '::', ACCESS_MODERATE)) {
            $links[] = array('url' => ModUtil::url($this->name, 'admin', 'search'), 'text' => $this->__('Find users'), 'class' => 'z-icon-es-search');
        }
        if (SecurityUtil::checkPermission('Users::MailUsers', '::', ACCESS_MODERATE)) {
            $links[] = array('url' => ModUtil::url($this->name, 'admin', 'mailUsers'), 'text' => $this->__('E-mail users'), 'class' => 'z-icon-es-mail');
        }
        if (SecurityUtil::checkPermission('Users::', '::', ACCESS_ADMIN)) {
            $links[] = array('url' => ModUtil::url($this->name, 'admin', 'config'), 'text' => $this->__('Settings'), 'class' => 'z-icon-es-config');
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

        $valuesArray = $args['valuesarray'];
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

        $importValues = $args['importvalues'];

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
        $usersInDB = ModUtil::apiFunc($this->name, 'admin', 'checkMultipleExistence',
                                      array('valuesarray' => $usersArray,
                                            'key' => 'uname'));
        if (!$usersInDB) {
            $this->registerError($this->__(
                'Error! The users have been created but something has failed trying to get them from the database. '
                . 'Now all these users do not have group.'));
            return false;
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
            $this->registerError($this->__('Error! The users have been created but something has failed while trying to add the users to their groups. These users are not assigned to a group.'));
            return false;
        }

        // check if module Mailer is active
        $modinfo = ModUtil::getInfoFromName('Mailer');
        if ($modinfo['state'] == ModUtil::TYPE_SYSTEM) {
            $sitename  = System::getVar('sitename');
            $siteurl   = System::getBaseUrl();

            $renderer = Zikula_View::getInstance($this->name, false);
            $renderer->assign('sitename', $sitename);
            $renderer->assign('siteurl', $siteurl);

            foreach ($importValues as $value) {
                if ($value['activated'] != Users_Constant::ACTIVATED_PENDING_REG) {
                    $createEvent = new Zikula_Event('user.create', $value);
                    $this->eventManager->notify($createEvent);
                } else {
                    $createEvent = new Zikula_Event('registration.create', $value);
                    $this->eventManager->notify($createEvent);
                }
                if (($value['activated'] != Users_Constant::ACTIVATED_PENDING_REG) && ($value['activated'] != Users_Constant::ACTIVATED_INACTIVE)
                        && ($value['sendmail'] == 1)) {

                    $renderer->assign('email', $value['email']);
                    $renderer->assign('uname', $value['uname']);
                    $renderer->assign('pass', $value['pass']);
                    $message = $renderer->fetch('users_email_importnotify_html.tpl');
                    $subject = $this->__f('Password for %1$s from %2$s', array($value['uname'], $sitename));
                    $sendMessageArgs = array(
                        'toaddress' => $value['email'],
                        'subject'   => $subject,
                        'body'      => $message,
                        'html'      => true,
                    );
                    if (!ModUtil::apiFunc('Mailer', 'user', 'sendMessage', $sendMessageArgs)) {
                        $this->registerError($this->__f('Error! A problem has occurred while sending e-mail messages. The error happened trying to send a message to the user %s. After this error, no more messages were sent.', $value['uname']));
                        break;
                    }
                }
            }
        }

        return true;
    }
}
