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
     * Parameters passed in the $args array:
     * -------------------------------------
     * string $args['uname']         A fragment of a user name on which to search using an SQL
     *                                      LIKE clause. The user name will be surrounded by wildcards.
     * int    $args['ugroup']        A group id in which to search (only users who are members of
     *                                      the specified group are returned).
     * string $args['email']         A fragment of an e-mail address on which to search using an
     *                                      SQL LIKE clause. The e-mail address will be surrounded by
     *                                      wildcards.
     * string $args['regdateafter']  An SQL date-time (in the form '1970-01-01 00:00:00'); only
     *                                      user accounts with a registration date after the date
     *                                      specified will be returned.
     * string $args['regdatebefore'] An SQL date-time (in the form '1970-01-01 00:00:00'); only
     *                                      user accounts with a registration date before the date
     *                                      specified will be returned.
     * array  $args['dynadata']      An array of search values to be passed to the designated
     *                                      profile module. Only those user records also satisfying the
     *                                      profile module's search of its data are returned.
     * string $args['condition']     An SQL condition for finding users; overrides all other
     *                                      parameters.
     *
     * @param array $args All parameters passed to this function.
     *
     * @return mixed array of items if succcessful, false otherwise
     */
    public function findUsers($args)
    {
        // Need read access to call this function
        if (!SecurityUtil::checkPermission("{$this->name}::", '::', ACCESS_READ)) {
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
                    switch ($arg) {
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
     * Delete one or more user account records, or mark one or more account records for deletion.
     *
     * If records are marked for deletion, they remain in the system and accessible by the system, but are given an
     * 'activated' status that prevents the user from logging in. Records marked for deletion will not appear on the
     * regular users list. The delete hook and delete events are not triggered if the records are only marked for
     * deletion.
     *
     * Parameters passed in the $args array:
     * -------------------------------------
     * numeric|array $args['uid']  A single (numeric integer) user id, or an array of user ids to delete.
     * boolean       $args['mark'] If true, then mark for deletion, but do not actually delete.
     *                                  defaults to false.
     *
     * @param array $args All parameters passed to this function.
     *
     * @return bool True if successful, false otherwise.
     */
    public function deleteUser($args)
    {
        if (!SecurityUtil::checkPermission("{$this->name}::", 'ANY', ACCESS_DELETE)) {
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
            } elseif (!SecurityUtil::checkPermission("{$this->name}::", "{$userObj['uname']}::{$userObj['uid']}", ACCESS_DELETE)) {
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
                $deleteEvent = new Zikula_Event('user.account.delete', $userObj);
                $this->eventManager->notify($deleteEvent);
            }
        }

        return $args['uid'];
    }

    /**
     * Send an e-mail message to one or more users.
     *
     * Parameters passed in the $args array:
     * -------------------------------------
     * numeric|array $args['uid']                         A single (numeric integer) uid or an array of uids to which the e-mail should be sent.
     * array         $args['sendmail']                    An array containing the information necessary to send an e-mail.
     * string        $args['sendmail']['from']            The name of the e-mail message's sender.
     * string        $args['sendmail']['rpemail']         The e-mail address of the e-mail message's sender.
     * string        $args['sendmail']['subject']         The e-mail message's subject.
     * string        $args['sendmail']['message']         The e-mail message's body (the message itself).
     * array         $args['sendmail']['recipientsname']  An array indexed by uid of each recipient's name.
     * array         $args['sendmail']['recipientsemail'] An array indexed by uid of each recipient's e-mail address.
     *
     * @param array $args All arguments passed to this function.
     *
     * @return bool True on success; otherwise false
     *
     * @throws Zikula_Exception_Forbidden Thrown if the current user does not have sufficient access to send mail.
     */
    public function sendmail($args)
    {
        if (!SecurityUtil::checkPermission("{$this->name}::MailUsers", '::', ACCESS_COMMENT)) {
            throw new Zikula_Exception_Forbidden();
        }

        if (isset($args['uid']) && !empty($args['uid'])) {
            if (is_array($args['uid'])) {
                $recipientUidList = $args['uid'];
            } else {
                $recipientUidList = array($args['uid']);
            }
        } else {
            $this->registerError(__('Error! No users selected to receive e-mail, or invalid uid list.'));

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

        if (SecurityUtil::checkPermission("{$this->name}::", '::', ACCESS_MODERATE)) {
            $links[] = array('url' => ModUtil::url($this->name, 'admin', 'view'), 'text' => $this->__('Users list'), 'class' => 'z-icon-es-view');
        }
        if (SecurityUtil::checkPermission("{$this->name}::", '::', ACCESS_MODERATE)) {
            $pending = ModUtil::apiFunc($this->name, 'registration', 'countAll');
            if ($pending) {
                $links[] = array('url' => ModUtil::url($this->name, 'admin', 'viewRegistrations'), 'text' => $this->__('Pending registrations') . ' ('.DataUtil::formatForDisplay($pending).')', 'class' => 'user-icon-adduser');
            }
        }

        // To create a new user (or import users) when registration is enabled, ADD access is required.
        // If registration is disabled, then ADMIN access required.
        // ADMIN access is always required for exporting the users.
        if ($this->getVar(Users_Constant::MODVAR_REGISTRATION_ENABLED, false)) {
            $createUserAccessLevel = ACCESS_ADD;
        } else {
            $createUserAccessLevel = ACCESS_ADMIN;
        }
        if (SecurityUtil::checkPermission("{$this->name}::", '::', $createUserAccessLevel)) {
            $submenulinks = array();
            $submenulinks[] = array('url' => ModUtil::url($this->name, 'admin', 'newUser'), 'text' => $this->__('Create new user'));
            $submenulinks[] = array('url' => ModUtil::url($this->name, 'admin', 'import'), 'text' => $this->__('Import users'));
            if (SecurityUtil::checkPermission("{$this->name}::", '::', ACCESS_ADMIN)) {
                 $submenulinks[] = array('url' => ModUtil::url($this->name, 'admin', 'exporter'), 'text' => $this->__('Export users'));
            }
            $links[] = array('url' => ModUtil::url($this->name, 'admin', 'newUser'), 'text' => $this->__('Create new user'), 'class' => 'z-icon-es-new', 'links' => $submenulinks);
        }
        if (SecurityUtil::checkPermission("{$this->name}::", '::', ACCESS_MODERATE)) {
            $links[] = array('url' => ModUtil::url($this->name, 'admin', 'search'), 'text' => $this->__('Find users'), 'class' => 'z-icon-es-search');
        }
        if (SecurityUtil::checkPermission('Users::MailUsers', '::', ACCESS_MODERATE)) {
            $links[] = array('url' => ModUtil::url($this->name, 'admin', 'mailUsers'), 'text' => $this->__('E-mail users'), 'class' => 'z-icon-es-mail');
        }
        if (SecurityUtil::checkPermission("{$this->name}::", '::', ACCESS_ADMIN)) {
            $links[] = array('url' => ModUtil::url($this->name, 'admin', 'config'), 'text' => $this->__('Settings'), 'class' => 'z-icon-es-config');
        }

        return $links;
    }

    /**
     * Retrieve a list of users whose field specified by the key match one of the values specified in the keyValue.
     *
     * Parameters passed in the $args array:
     * -------------------------------------
     * string $args['key']         The field to be searched, typically 'uname' or 'email'.
     * array  $args['valuesarray'] An array containing the values to be matched.
     *
     * @param array $args All parameters passed to this function.
     *
     * @return array|bool An array of user records indexed by user name, each whose key field matches one value in the
     *                      valueArray; false on error.
     */
    public function checkMultipleExistence($args)
    {
        // Need read access to call this function
        if (!SecurityUtil::checkPermission("{$this->name}::", '::', ACCESS_READ)) {
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
     * Parameters passed in the $args array:
     * -------------------------------------
     * array $args['importvalues'] An array of information used to create new user records. Each element of the
     *                                  array should represent the minimum information to create a user record, including
     *                                  'uname', 'email', 'pass', etc.
     *
     * @param array $args All parameters passed to this function.
     *
     * @return bool True on success; false otherwise.
     */
    public function createImport($args)
    {
        // Need add access to call this function
        if (!SecurityUtil::checkPermission("{$this->name}::", '::', ACCESS_ADD)) {
            return false;
        }

        $importValues = $args['importvalues'];

        if (empty($importValues)) {
            return false;
        }

        // Prepare arrays.
        $usersArray = array();
        foreach ($importValues as $key => $value) {
            $usersArray[] = $value['uname'];
            if (!$value['activated']) {
                $importValues[$key]['activated'] = Users_Constant::ACTIVATED_PENDING_REG;
            }
        }

        $importValuesDB = $importValues;
        foreach ($importValuesDB as $key => $value) {
            $importValuesDB[$key]['pass'] = UserUtil::getHashedPassword($importValuesDB[$key]['pass']);
        }

        // execute sql to create users
        $result = DBUtil::insertObjectArray($importValuesDB, 'users', 'uid');
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
        // construct a sql statement with all the inserts to reduce SQL queries
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

            $view = Zikula_View::getInstance($this->name, false);
            $view->assign('sitename', $sitename);
            $view->assign('siteurl', $siteurl);

            foreach ($importValues as $value) {
                if ($value['activated'] != Users_Constant::ACTIVATED_PENDING_REG) {
                    $createEvent = new Zikula_Event('user.account.create', $value);
                    $this->eventManager->notify($createEvent);
                } else {
                    $createEvent = new Zikula_Event('user.registration.create', $value);
                    $this->eventManager->notify($createEvent);
                }
                if ($value['activated'] && $value['sendmail']) {
                    $view->assign('email', $value['email']);
                    $view->assign('uname', $value['uname']);
                    $view->assign('pass', $value['pass']);
                    $message = $view->fetch('users_email_importnotify_html.tpl');
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
