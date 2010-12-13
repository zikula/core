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
 * Administrator-initiated actions for the Users module.
 */
class Users_Controller_Admin extends Zikula_Controller
{
    /**
     * Post initialise.
     *
     * Run after construction.
     *
     * @return void
     */
    protected function postInitialize()
    {
        // Set caching to false by default.
        $this->view->setCaching(false);
    }

    /**
     * Determines if the user currently logged in has administrative access for the Users module.
     *
     * @return bool True if the current user is logged in and has administrator access for the Users
     *                  module; otherwise false.
     */
    private function currentUserIsAdmin()
    {
        return UserUtil::isLoggedIn() && SecurityUtil::checkPermission('Users::', '::', ACCESS_ADMIN);
    }

    /**
     * Determines if the user currently logged in has add access for the Users module.
     *
     * @return bool True if the current user is logged in and has administrative permission for the Users
     *                  module; otherwise false.
     */
    private function currentUserIsAdminOrSubAdmin()
    {
        return UserUtil::isLoggedIn() && SecurityUtil::checkPermission('Users::', '::', ACCESS_ADD);
    }

    /**
     * Redirects users to the "view" page.
     *
     * @return string HTML string containing the rendered view template.
     */
    public function main()
    {
        // Security check will be done in view()
        return $this->view();
    }

    /**
     * Display a form to add a new user account.
     *
     * Available Request Parameters:
     * - userinfo (array) An associative array of initial values for the form fields. The elements of the array correspond to the
     *      post parameters expected by $this->createUser().
     *
     * @return string HTML string containing the rendered template.
     */
    public function newUser()
    {
        if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_ADD)) {
            return LogUtil::registerPermissionError();
        }

        if ($this->getVar('reg_allowreg', false) && !SecurityUtil::checkPermission('Users::', '::', ACCESS_ADMIN)) {
            $registrationUnavailableReason = $this->getVar('reg_noregreasons', $this->__('Sorry! New user registration is currently disabled.'));
            return LogUtil::registerError($registrationUnavailableReason, 403, System::getHomepageUrl());
        }

        // If we are returning here from validation errors detected in createUser, then get the data already entered
        // otherwise $rendererArgs should end up to be an empty array. See registerNewUser() below for what is stored on the session
        // variable and returned here (The "if ($registrationErrors)" block).
        $rendererArgs = SessionUtil::getVar('Users_Admin_newUser', array(), '/', false);
        SessionUtil::delVar('Users_Admin_newUser');

        if (!empty($rendererArgs)) {
            $registrationErrors = isset($rendererArgs['regerrors']) ? $rendererArgs['regerrors'] : array();
            // For now do it this way. Later maybe show the messages with the field--and if that's
            // done, then $errorFields and $errorMessages not needed--we'd just pass $registrationErrors directly.
            $errorInfo = ModUtil::apiFunc('Users', 'user', 'processRegistrationErrorsForDisplay', array('registrationErrors' => $registrationErrors));

            $rendererArgs['regerrors'] = $registrationErrors;
            $rendererArgs['errormsgs'] = (isset($errorInfo['errorMessages']) && !empty($errorInfo['errorMessages']))
                ? $errorInfo['errorMessages']
                : array();
            $rendererArgs['errorflds'] = (isset($errorInfo['errorFields']) && !empty($errorInfo['errorFields']))
                ? $errorInfo['errorFields']
                : array();
        } else {
            // It was empty, so set defaults.
            $rendererArgs['reginfo'] = array();
            $rendererArgs['reginfo']['uname'] = '';
            $rendererArgs['reginfo']['email'] = '';
            $rendererArgs['reginfo']['dynadata'] = array();
            $rendererArgs['setpass'] = false;
            $rendererArgs['emailagain'] = '';
            $rendererArgs['sendpass'] = false;
            $rendererArgs['usermustverify'] = ($this->getVar('reg_verifyemail') != UserUtil::VERIFY_NO) ? true : false;
            $rendererArgs['regerrors'] = array();
            $rendererArgs['errormsgs'] = array();
            $rendererArgs['errorflds'] = array();
        }

        $profileModName = System::getVar('profilemodule', '');
        $profileModAvailable = !empty($profileModName) && ModUtil::available($profileModName);

        $legalAvailable = ModUtil::available('Legal');
        $touActive = $legalAvailable && ModUtil::getVar('Legal', 'termsofuse');
        $ppActive = $legalAvailable && ModUtil::getVar('Legal', 'privacypolicy');

        // Set a few other things, no matter if we are coming back or not
        $rendererArgs['usermustaccept'] = $touActive || $ppActive;
        $rendererArgs['profilemodname'] = $profileModName;
        $rendererArgs['showprops'] = $profileModAvailable && $this->getVar('reg_optitems');
        $rendererArgs['passagain'] = '';

        // Return the output that has been generated by this function
        $this->view->add_core_data();
        $this->view->assign($rendererArgs);
        return $this->view->fetch('users_admin_newuser.tpl');
    }

    /**
     * Create a new user.
     *
     * Available Post Parameters:
     *
     * @return bool True if successful, false otherwise.
     */
    public function registerNewUser()
    {
        // check permisisons
        if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_ADD)) {
            return LogUtil::registerPermissionError();
        }

        if (!SecurityUtil::confirmAuthKey('Users')) {
            return LogUtil::registerAuthidError(ModUtil::url('Users', 'admin', 'view'));
        }

        // get arguments
        $reginfo = FormUtil::getPassedValue('reginfo', null, 'POST');
        if (isset($reginfo['uname']) && !empty($reginfo['uname'])) {
            $reginfo['uname'] = mb_strtolower($reginfo['uname']);
        }
        if (isset($reginfo['email']) && !empty($reginfo['email'])) {
            $reginfo['email'] = mb_strtolower($reginfo['email']);
        }
        $reginfo['dynadata'] = FormUtil::getPassedValue('dynadata', array(), 'POST');

        $checkMode = 'new';
        $setPassword = FormUtil::getPassedValue('setpass', true, 'POST');
        $emailAgain = FormUtil::getPassedValue('emailagain', null, 'POST');
        $passwordAgain = !$setPassword ? null : FormUtil::getPassedValue('passagain', null, 'POST');
        $userMustVerify = !$setPassword || FormUtil::getPassedValue('usermustverify', false, 'POST');
        $sendPassword = $setPassword && FormUtil::getPassedValue('sendpass', false, 'POST');

        if ($setPassword) {
            if (!isset($reginfo['pass'])) {
                // Ensure set and empty for validation.
                $reginfo['pass'] = '';
            }
            if (!isset($reginfo['passreminder'])) {
                $reginfo['passreminder'] = $this->__('(Password provided by site administrator)');
            }
        } else {
            // The fields may have had values but were hidden. Ensure they are not set.
            unset($reginfo['pass']);
            unset($reginfo['passreminder']);
        }

        // Set agreetoterms property, so we know to ask the user to agree on activation or login.
        $reginfo['agreetoterms'] = false;

        $registrationErrors = ModUtil::apiFunc('Users', 'registration', 'getRegistrationErrors', array(
            'checkmode'     => $checkMode,
            'reginfo'       => $reginfo,
            'setpass'       => $setPassword,
            'sendpass'      => $sendPassword,
            'passagain'     => $passwordAgain,
            'emailagain'    => $emailAgain,
        ));

        if ($registrationErrors) {
            SessionUtil::setVar('reginfo', $reginfo, 'Users_Admin_newUser', true, true);
            SessionUtil::setVar('setpass', $setPassword, 'Users_Admin_newUser', true, true);
            //SessionUtil::setVar('passagain', $passwordAgain, 'Users_Admin_newUser', true, true);
            SessionUtil::setVar('emailagain', $emailAgain, 'Users_Admin_newUser', true, true);
            SessionUtil::setVar('usermustverify', $userMustVerify, 'Users_Admin_newUser', true, true);
            SessionUtil::setVar('sendpass', $sendPassword, 'Users_Admin_newUser', true, true);
            SessionUtil::setVar('regerrors', $registrationErrors, 'Users_Admin_newUser', true, true);

            return System::redirect(ModUtil::url('Users', 'admin', 'newUser'));
        }

        $currentUserEmail = UserUtil::getVar('email');
        $adminNotifyEmail = $this->getVar('reg_notifyemail', '');
        $adminNotification = (strtolower($currentUserEmail) != strtolower($adminNotifyEmail));

        $registeredObj = ModUtil::apiFunc('Users', 'registration', 'registerNewUser', array(
            'reginfo'           => $reginfo,
            'usermustverify'    => $userMustVerify,
            'sendpass'          => $sendPassword,
            'usernotification'  => true,
            'adminnotification' => true,
        ));

        if ($registeredObj) {
            if ($registeredObj['activated'] == UserUtil::ACTIVATED_PENDING_REG) {
                LogUtil::registerStatus($this->__('Done! Created new registration application.'));
            } elseif (isset($registeredObj['activated'])) {
                LogUtil::registerStatus($this->__('Done! Created new user account.'));
            } else {
                LogUtil::registerError($this->__('Warning! New user information has been saved, however there may have been an issue saving it properly.'));
            }
        } else {
            LogUtil::registerError($this->__('Error! Could not create the new user account or registration application.'));
        }

        return System::redirect(ModUtil::url('Users', 'admin', 'view'));
    }

    /**
     * Shows all items and lists the administration options.
     *
     * Available Get Parameters:
     * - startnum (int)    The ordinal number at which to start displaying user records.
     * - letter   (string) The first letter of the user names to display.
     *
     * @param array $args All parameters passed to the function.
     *                    $args['startnum'] (int) The ordinal number at which to start displaying user records. Used as a default if
     *                      the get parameter is not set. Allows the function to be called internally.
     *                    $args['letter'] (string) The first letter of the user names to display. Used as a default if
     *                      the get parameter is not set. Allows the function to be called internally.
     *
     * @return string HTML string containing the rendered template.
     */
    public function view($args = array())
    {
        // Get parameters from whatever input we need.
        $startnum = FormUtil::getPassedValue('startnum', isset($args['startnum']) ? $args['startnum'] : null, 'GET');
        $letter = FormUtil::getPassedValue('letter', isset($args['letter']) ? $args['letter'] : null, 'GET');
        $sort = FormUtil::getPassedValue('sort', isset($args['sort']) ? $args['sort'] : 'uname', 'GET');
        $sortDirection = FormUtil::getPassedValue('sortdir', isset($args['sortdir']) ? $args['sortdir'] : 'ASC', 'GET');

        if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_MODERATE)) {
            return LogUtil::registerPermissionError();
        }

        // we need this value multiple times, so we keep it
        $itemsperpage = $this->getVar('itemsperpage');

        // Get all users
        $sortArray = array($sort => $sortDirection);
        if ($sort != 'uname') {
            $sortArray['uname'] = $sortDirection;
        }
        $items = ModUtil::apiFunc('Users', 'user', 'getAll', array(
            'startnum'  => $startnum,
            'numitems'  => $itemsperpage,
            'letter'    => $letter,
            'sort'      => $sortArray,
        ));

        // Get all groups
        $groups = ModUtil::apiFunc('Groups', 'user', 'getall');

        // check what groups can access the user
        $userGroupsAccess = array();
        $groupsArray = array();
        $canSeeGroups = (!empty($groups));
        foreach ($groups as $group) {
            $userGroupsAccess[$group['gid']] = array('gid' => $group['gid']);

            // rewrite the groups array with the group id as key and the group name as value
            $groupsArray[$group['gid']] = array('name' => DataUtil::formatForDisplayHTML($group['name']));
        }

        $profileModule = System::getVar('profilemodule', '');
        $useProfileModule = (!empty($profileModule) && ModUtil::available($profileModule));

        // if module Legal is not available show the equivalent states for user activation value
        $adaptStateLegalMod = (ModUtil::available('Legal') && (ModUtil::getVar('Legal', 'termsofuse') || ModUtil::getVar('Legal', 'privacypolicy'))) ? 0 : 1;

        // Get the current user's uid
        $currentUid = UserUtil::getVar('uid');

        // Loop through each returned item adding in the options that the user has over
        // each item based on the permissions the user has.
        foreach ($items as $key => $item) {
            $options = array();
            $authId = SecurityUtil::generateAuthKey('Users');
            if (SecurityUtil::checkPermission('Users::', "{$item['uname']}::{$item['uid']}", ACCESS_READ) && $item['uid'] != 1) {

                // Options for the item.
                if ($useProfileModule) {
                    $options[] = array('url'   => ModUtil::url($profileModule, 'user', 'view', array('uid' => $item['uid'])),
                                       'image' => 'personal.gif',
                                       'title' => $this->__f('View the profile of \'%s\''), $item['uname']);
                }
                if (SecurityUtil::checkPermission('Users::', "{$item['uname']}::{$item['uid']}", ACCESS_MODERATE)) {
                    $options[] = array('url'   => ModUtil::url('Users', 'admin', 'lostUsername', array('uid' => $item['uid'], 'authid' => $authId)),
                                       'image' => 'lostusername.png',
                                       'title' => $this->__f('Send user name to \'%s\'', $item['uname']));

                    $options[] = array('url'   => ModUtil::url('Users', 'admin', 'lostPassword', array('uid' => $item['uid'], 'authid' => $authId)),
                                       'image' => 'lostpassword.png',
                                       'title' => $this->__f('Send password recovery code to \'%s\'', $item['uname']));

                    if (SecurityUtil::checkPermission('Users::', "{$item['uname']}::{$item['uid']}", ACCESS_EDIT)) {
                        $options[] = array('url'   => ModUtil::url('Users', 'admin', 'modify', array('userid' => $item['uid'])),
                                           'image' => 'xedit.gif',
                                           'title' => $this->__f('Edit \'%s\'', $item['uname']));

                        if (($currentUid != $item['uid']) && SecurityUtil::checkPermission('Users::', "{$item['uname']}::{$item['uid']}", ACCESS_DELETE)) {
                            $options[] = array('url'   => ModUtil::url('Users', 'admin', 'deleteUsers', array('userid' => $item['uid'])),
                                               'image' => '14_layer_deletelayer.gif',
                                               'title' => $this->__f('Delete \'%s\'', $item['uname']));
                        }
                    }
                }
                // get user groups
                $userGroups = ModUtil::apiFunc('Groups', 'user', 'getusergroups',
                                            array('uid' => $item['uid'],
                                                  'clean' => 1));
                // we need an associative array by the key to compare with the groups that the user can see
                $userGroupsByKey = array();
                foreach ($userGroups as $userGroup) {
                    $userGroupsByKey[$userGroup['gid']] = array('gid' => $userGroup['gid']);
                }
                $userGroupsView = array_intersect_key($userGroupsAccess, $userGroupsByKey);
            }

            if ($item['uid'] == 1) {
                $userGroupsView = array();
            }
            // format the dates
            if (!empty($item['user_regdate']) && ($item['user_regdate'] != '0000-00-00 00:00:00') && ($item['user_regdate'] != '1970-01-01 00:00:00')) {
                $items[$key]['user_regdate'] = DateUtil::formatDatetime($item['user_regdate'], $this->__('%m-%d-%Y'));
            } else {
                $items[$key]['user_regdate'] = '---';
            }

            if (!empty($item['lastlogin']) && ($item['lastlogin'] != '0000-00-00 00:00:00') && ($item['lastlogin'] != '1970-01-01 00:00:00')) {
                $items[$key]['lastlogin'] = DateUtil::formatDatetime($item['lastlogin'], $this->__('%m-%d-%Y'));
            } else {
                $items[$key]['lastlogin'] = '---';
            }

            // show user's activation state
            $activationImg = '';
            $activationTitle = '';
            // adapt states if it is necessary
            if ($adaptStateLegalMod) {
                if ($items[$key]['activated'] == UserUtil::ACTIVATED_INACTIVE_TOUPP) {
                    $items[$key]['activated'] = UserUtil::ACTIVATED_ACTIVE;
                } else if ($items[$key]['activated'] == UserUtil::ACTIVATED_INACTIVE_PWD_TOUPP) {
                    $items[$key]['activated'] = UserUtil::ACTIVATED_INACTIVE_PWD;
                }
            }
            // show user's activation state
            if ($items[$key]['activated'] == UserUtil::ACTIVATED_ACTIVE) {
                $activationImg = 'greenled.gif';
                $activationTitle = $this->__('Active');
            } elseif ($items[$key]['activated'] == UserUtil::ACTIVATED_INACTIVE_TOUPP) {
                $activationImg = 'yellowled.gif';
                $activationTitle = $this->__('Inactive until Legal terms accepted');
            } elseif ($items[$key]['activated'] == UserUtil::ACTIVATED_INACTIVE_PWD) {
                $activationImg = 'yellowled.gif';
                $activationTitle = $this->__('Inactive until changing password');
            } elseif ($items[$key]['activated'] == UserUtil::ACTIVATED_INACTIVE_PWD_TOUPP) {
                $activationImg = 'yellowled.gif';
                $activationTitle = $this->__('Inactive until change password and accept legal terms');
            } elseif ($items[$key]['activated'] == UserUtil::ACTIVATED_PENDING_DELETE) {
                $activationImg = '14_layer_deletelayer.gif';
                $activationTitle = $this->__('Inactive, pending deletion');
            } elseif ($items[$key]['activated'] == UserUtil::ACTIVATED_INACTIVE) {
                $activationImg = 'yellowled.gif';
                $activationTitle = $this->__('Inactive');
            } else {
                $activationImg = 'status_unknown.gif';
                $activationTitle = $this->__('Status unknown');
            }
            $items[$key]['activation'] = array('image' => $activationImg,
                                               'title' => $activationTitle);

            // Add the calculated menu options to the item array
            $items[$key]['options'] = $options;
            // Add the groups that the user can see to the item array
            $items[$key]['userGroupsView'] = $userGroupsView;
        }

        $pager = array(
            'numitems'     => ModUtil::apiFunc('Users', 'user', 'countItems', array('letter' => $letter)),
            'itemsperpage' => $itemsperpage,
        );

        // Assign the items to the template
        $this->view->assign('usersitems', $items)
                        ->assign('pager', $pager)
                        ->assign('allGroups', $groupsArray)
                        ->assign('canSeeGroups', $canSeeGroups)
                        ->assign('sort', $sort)
                        ->assign('sortdir', $sortDirection);

        // Return the output that has been generated by this function
        return $this->view->fetch('users_admin_view.tpl');
    }

    /**
     * Displays a user account search form.
     *
     * @return string HTML string containing the rendered template.
     */
    public function search()
    {
        // security check
        if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_MODERATE)) {
            return LogUtil::registerPermissionError();
        }

        // get group items
        $groups = ModUtil::apiFunc('Groups', 'user', 'getall');
        $this->view->assign('groups', $groups);

        return $this->view->fetch('users_admin_search.tpl');
    }

    /**
     * List the users as a result of a form post.
     *
     * Available Post Parameters:
     * - uname         (string) A fragment of a user name on which to search using an SQL LIKE clause. The user name will be surrounded by wildcards.
     * - ugroup        (int)    A group id in which to search (only users who are members of the specified group are returned).
     * - email         (string) A fragment of an e-mail address on which to search using an SQL LIKE clause. The e-mail address will be surrounded by wildcards.
     * - regdateafter  (string) An SQL date-time (in the form '1970-01-01 00:00:00'); only user accounts with a registration date after the date specified will be returned.
     * - regdatebefore (string) An SQL date-time (in the form '1970-01-01 00:00:00'); only user accounts with a registration date before the date specified will be returned.
     * - dynadata      (array)  An array of search values to be passed to the designated profile module. Only those user records also satisfying the profile module's search of its data
     *                          are returned.
     *
     * @return string HTML string containing the rendered template.
     */
    public function listUsers()
    {
        $uname         = FormUtil::getPassedValue('uname', null, 'POST');
        $ugroup        = FormUtil::getPassedValue('ugroup', null, 'POST');
        $email         = FormUtil::getPassedValue('email', null, 'POST');
        $regdateafter  = FormUtil::getPassedValue('regdateafter', null, 'POST');
        $regdatebefore = FormUtil::getPassedValue('regdatebefore', null, 'POST');

        $dynadata      = FormUtil::getPassedValue('dynadata', null, 'POST');

        // call the api
        $items = ModUtil::apiFunc('Users', 'admin', 'findUsers', array(
            'uname'         => $uname,
            'email'         => $email,
            'ugroup'        => $ugroup,
            'regdateafter'  => $regdateafter,
            'regdatebefore' => $regdatebefore,
            'dynadata'      => $dynadata
        ));

        if (!$items) {
            LogUtil::registerError($this->__('Sorry! No matching users found.'), 404, ModUtil::url('Users', 'admin', 'search'));
        }

        $currentUid = UserUtil::getVar('uid');

        $actions = array();
        foreach ($items as $key => $userinfo) {
            $actions[$key] = array(
                'modifyUrl'    => false,
                'deleteUrl'    => false,
            );
            if ($userinfo['uid'] != 1) {
                if (SecurityUtil::checkPermission($this->getName().'::', $userinfo['uname'].'::'.$userinfo['uid'], ACCESS_EDIT)) {
                    $actions[$key]['modifyUrl'] = ModUtil::url($this->getName(), 'admin', 'modify', array('userid' => $userinfo['uid']));
                }
                if (($currentUid != $userinfo['uid'])
                        && SecurityUtil::checkPermission($this->getName().'::', $userinfo['uname'].'::'.$userinfo['uid'], ACCESS_DELETE)) {

                    $actions[$key]['deleteUrl'] = ModUtil::url($this->getName(), 'admin', 'deleteusers', array('userid' => $userinfo['uid']));
                }
            }
        }

        // assign the matching results
        $this->view->assign('items', $items)
                   ->assign('actions', $actions)
                   ->assign('mailusers', SecurityUtil::checkPermission('Users::MailUsers', '::', ACCESS_COMMENT))
                   ->assign('deleteusers', SecurityUtil::checkPermission('Users::', '::', ACCESS_ADMIN));

        return $this->view->fetch('users_admin_listusers.tpl');
    }

    /**
     * Perform one of several possible operations on a user as a result of a form post.
     *
     * Available Post Parameters:
     * - op                 (string)  The operation. One of: 'edit', 'delete', 'mail', 'approve', or 'deny'.
     * - do                 (string)  Used only for 'edit' or 'delete' operations; either the value 'yes' or null. Controls whether
     *                                  a confirmation page is displayed for the operation (value of null) or the operation is actually
     *                                  performed (value 'yes').
     * - userid             (numeric) The user id of the user record on which the operation is to be performed.
     * - uname              (string)  Used only for 'edit' operations; the user name to be saved to the user record.
     * - email              (string)  Used only for 'edit' operations; the e-mail address to be saved to the user record.
     * - activated          (bool)    Used only for 'edit' operations; the activation state to be saved to the user record.
     * - pass               (string)  Used only for 'edit' operations; the new password to be saved to the user record.
     * - vpass              (string)  Used only for 'edit' operations; the confirmation of the new password to be saved to the user record.
     * - theme              (string)  Used only for 'edit' operations; the name of the theme to be saved to the user record.
     * - access_permissions (array)   Used only for 'edit' operations; an array of group ids to which the user should belong.
     * - dynadata           (array)   Used only for 'edit' operations; an array of dynamic user data to be stored with the designated profile module for the user account.
     * - sendmail           (array)   Used only for 'mail' operations; an array containing the e-mail to be sent.
     * - tag                (int)     Used only for 'approve' and 'deny' operations; if not 1, then a confirmation page is displayed; if 1 the operation is carried out.
     * - action             (string)  Used only for 'approve' and 'deny' operations; a fragment of the name of the function to call, either 'approve' or 'deny'.
     *
     * @return mixed true successful, false or string otherwise
     */
    public function processUsers()
    {
        // security check
        if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }

        // get the arguments from our input
        $op     = FormUtil::getPassedValue('op', null, 'GETPOST');
        $do     = FormUtil::getPassedValue('do', null, 'POST');
        $userid = FormUtil::getPassedValue('userid', null, 'POST');

        if ($op == 'edit' && !empty($userid)) {
            if ($do != 'yes') {
                return System::redirect(ModUtil::url('Users', 'admin', 'modify', array('userid' => $userid)));
            } else {
                $userinfo             = FormUtil::getPassedValue('userinfo', null, 'POST');
                $passAgain            = FormUtil::getPassedValue('passagain', null, 'POST');
                $emailAgain           = FormUtil::getPassedValue('emailagain', null, 'POST');
                $access_permissions   = FormUtil::getPassedValue('access_permissions', null, 'POST');
                $userinfo['dynadata'] = FormUtil::getPassedValue('dynadata', array(), 'POST');

                $return = ModUtil::apiFunc('Users', 'admin', 'updateUser', array(
                    'userinfo'           => $userinfo,
                    'emailagain'         => $emailAgain,
                    'passagain'          => $passAgain,
                    'access_permissions' => $access_permissions,
                ));

                if ($return) {
                    LogUtil::registerStatus($this->__("Done! Saved user's account information."));
                    return System::redirect(ModUtil::url('Users', 'admin', 'main'));
                } else {
                    return false;
                }
            }

        } elseif ($op == 'delete' && !empty($userid)) {
            $userid = FormUtil::getPassedValue('userid', null, 'POST');
            if ($do != 'yes') {
                return System::redirect(ModUtil::url('Users', 'admin', 'deleteUsers', array('userid' => $userid)));
            } else {
                // Ensure that the current user's uid is not selected for deletion.
                $currentUserId = UserUtil::getVar('uid');
                if (!is_array($userid)) {
                    $userid = array($userid);
                }
                foreach ($userid as $uid) {
                    if ($uid == $currentUserId) {
                        return LogUtil::registerError($this->__("Error! You can't delete the account you are currently logged into."));
                    }
                }

                // Current user is not in the list to be deleted. Proceed.
                $return = ModUtil::apiFunc('Users', 'admin', 'deleteUser', array('uid' => $userid));
                if ($return == true) {
                    return LogUtil::registerStatus($this->__('Done! Deleted user account.'), ModUtil::url('Users', 'admin', 'main'));
                } else {
                    return false;
                }
            }

        } elseif ($op == 'mail' && !empty($userid) && SecurityUtil::checkPermission('Users::MailUsers', '::', ACCESS_COMMENT)) {
            $userid   = FormUtil::getPassedValue('userid', array(), 'POST');
            $sendmail = FormUtil::getPassedValue('sendmail', array(), 'POST');
            if (empty($sendmail['from']) || empty($sendmail['rpemail']) || empty($sendmail['subject']) || empty($sendmail['message'])) {
                return LogUtil::registerError($this->__('Error! One or more information items needed to send an e-mail message are missing.'),
                                              null,
                                              ModUtil::url('Users', 'admin', 'search'));
            }

            $bcclist = array();
            $mailssent = 0;
            $recipientscount = 0;
            foreach ($sendmail['recipientsemail'] as $uid => $recipient) {
                if (in_array($uid, $userid)) {
                    $bcclist[] = array('name'    => $sendmail['recipientsname'][$uid],
                                       'address' => $recipient);
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
                        $mailssent++;
                        $recipientscount += count($bcclist);
                        $bcclist = array();
                    } else {
                        return LogUtil::registerError($this->__('Error! Could not send the e-mail message.'),
                                                      null,
                                                      ModUtil::url('Users', 'admin', 'main'));
                    }
                }
            }
            if (count($bcclist) <> 0) {
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
                    $mailssent++;
                    $recipientscount += count($bcclist);
                } else {
                    return LogUtil::registerError($this->__('Error! Could not send the e-mail message.'),
                                                  null,
                                                  ModUtil::url('Users', 'admin', 'main'));
                }
            }
            if ($mailssent > 0) {
                LogUtil::registerStatus($this->_fn(
                    'Done! %1$c e-mail message has been sent to %2$c user.',
                    'Done! %1$c e-mail messages have been sent to %2$c users.',
                    $mailssent,
                    array($mailssent, $recipientscount)));
            }
            return System::redirect(ModUtil::url('Users', 'admin', 'main'));

        } else {
            return LogUtil::registerError($this->__('Error! No users were selected.'));
        }

        return System::redirect(ModUtil::url('Users', 'admin', 'search'));
    }

    /**
     * Display a form to edit one user account.
     *
     * Available Get Parameters:
     * - userid (numeric) The user id of the user to be modified.
     * - uname  (string)  The user name of the user to be modified.
     *
     * @param array $args All arguments passed to the function.
     *                    $args['userid'] (numeric) the user id of the user to be modified. Used as a default value if the get parameter
     *                      is not set. Allow the function to be called internally.
     *                    $args['uname'] (string) the user name of the user to be modified. Used as a default value if the get parameter
     *                      is not set. Allow the function to be called internally.
     *
     * @return string HTML string containing the rendered template.
     */
    public function modify($args)
    {
        // security check
        if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }

        // get arguments
        $userid = FormUtil::getPassedValue('userid', (isset($args['userid']) ? $args['userid'] : null), 'GET');
        $uname  = FormUtil::getPassedValue('uname', (isset($args['uname']) ? $args['uname'] : null), 'GET');

        // check arguments
        if (is_null($userid) && is_null($uname)) {
            LogUtil::registerError($this->__('Sorry! No such user found.'));
            return System::redirect(ModUtil::url('Users', 'admin', 'main'));
        }

        // retreive userid from uname
        if (is_null($userid) && !empty($uname)) {
            $userid = UserUtil::getIdFromName($uname);
        }

        // warning for guest account
        if ($userid == 1) {
            LogUtil::registerError($this->__("Error! You can't edit the guest account."));
            return System::redirect(ModUtil::url('Users', 'admin', 'main'));
        }

        // get the user vars
        $uservars = UserUtil::getVars($userid);
        if ($uservars == false) {
            LogUtil::registerError($this->__('Sorry! No such user found.'));
            return System::redirect(ModUtil::url('Users', 'admin', 'main'));
        }

        // if module Legal is not available show the equivalent states for user activation value
        if (!ModUtil::available('Legal') || (!ModUtil::getVar('Legal', 'termsofuse') && !ModUtil::getVar('Legal', 'privacypolicy'))) {
            if ($uservars['activated'] == UserUtil::ACTIVATED_INACTIVE_TOUPP) {
                $uservars['activated'] = UserUtil::ACTIVATED_ACTIVE;
            } else if ($uservars['activated'] == UserUtil::ACTIVATED_INACTIVE_PWD_TOUPP) {
                $uservars['activated'] = UserUtil::ACTIVATED_INACTIVE_PWD;
            }
        }

        // urls
        $this->view->assign('urlprocessusers', ModUtil::url('Users', 'admin', 'processUsers', array('op' => 'edit', 'do' => 'yes')))
                   ->assign('op', 'edit')
                   ->assign('userid', $userid)
                   ->assign('userinfo', $uservars);

        // groups
        $groups_infos = array();
        $user_groups_register = array();
        $user_groups = ModUtil::apiFunc('Groups', 'user', 'getusergroups', array('uid' => $userid));
        $all_groups = ModUtil::apiFunc('Groups', 'user', 'getall');

        foreach ($user_groups as $user_group) {
            $user_groups_register[] = $user_group['gid'];
        }

        foreach ($all_groups as $group) {
            if (SecurityUtil::checkPermission('Groups::', "$group[gid]::", ACCESS_EDIT)) {
                $groups_infos[$group['gid']] = array();
                $groups_infos[$group['gid']]['name'] = $group['name'];

                if (in_array($group['gid'], $user_groups_register)) {
                    $groups_infos[$group['gid']]['access'] = true;
                } else {
                    $groups_infos[$group['gid']]['access'] = false;
                }
            }
        }

        $this->view->add_core_data()
            ->assign('defaultgroupid', ModUtil::getVar('Groups', 'defaultgroup', 1))
            ->assign('primaryadmingroupid', ModUtil::getVar('Groups', 'primaryadmingroup', 2))
            ->assign('groups_infos', $groups_infos)
            ->assign('legal', ModUtil::available('Legal'))
            ->assign('tou_active', ModUtil::getVar('Legal', 'termsofuse', true))
            ->assign('pp_active',  ModUtil::getVar('Legal', 'privacypolicy', true));

        return $this->view->fetch('users_admin_modify.tpl');
    }

    /**
     * Allows an administrator to send a user his user name via email.
     *
     * @return bool True on success and redirect; otherwise false.
     */
    public function lostUsername()
    {
        if (!SecurityUtil::confirmAuthKey('Users')) {
            return LogUtil::registerAuthidError(ModUtil::url('Users', 'admin', 'view'));
        }

        $uid = FormUtil::getPassedValue('uid', null, 'GET');

        if (!isset($uid) || !is_numeric($uid) || ((int)$uid != $uid) || ($uid <= 1)) {
            return LogUtil::registerArgsError(ModUtil::url('Users', 'admin', 'view'));
        }

        $user = UserUtil::getVars($uid);
        if (!$user) {
            return LogUtil::registerError($this->__('Sorry! Unable to retrieve information for that user id.'));
        }

        if (!SecurityUtil::checkPermission('Users::', "{$user['uname']}::{$user['uid']}", ACCESS_MODERATE)) {
            return LogUtil::registerPermissionError();
        }

        $userNameSent = ModUtil::apiFunc('Users', 'user', 'mailUname', array(
            'idfield'       => 'uid',
            'id'            => $user['uid'],
            'adminRequest'  => true,
        ));

        if ($userNameSent) {
            LogUtil::registerStatus($this->__f('Done! The user name for %s has been sent via e-mail.', $user['uname']));
        }

        return System::redirect(ModUtil::url('Users', 'admin', 'view'));
    }

    /**
     * Allows an administrator to send a user a password recovery verification code.
     *
     * @return bool True on success and redirect; otherwise false.
     */
    public function lostPassword()
    {
        if (!SecurityUtil::confirmAuthKey('Users')) {
            return LogUtil::registerAuthidError(ModUtil::url('Users', 'admin', 'view'));
        }

        $uid = FormUtil::getPassedValue('uid', null, 'GET');

        if (!isset($uid) || !is_numeric($uid) || ((int)$uid != $uid) || ($uid <= 1)) {
            return LogUtil::registerArgsError(ModUtil::url('Users', 'admin', 'view'));
        }

        $user = UserUtil::getVars($uid);
        if (!$user) {
            return LogUtil::registerError($this->__('Sorry! Unable to retrieve information for that user id.'));
        }

        if (!SecurityUtil::checkPermission('Users::', "{$user['uname']}::{$user['uid']}", ACCESS_MODERATE)) {
            return LogUtil::registerPermissionError();
        }

        $confirmationCodeSent = ModUtil::apiFunc('Users', 'user', 'mailConfirmationCode', array(
            'idfield'       => 'uid',
            'id'            => $user['uid'],
            'adminRequest'  => true,
        ));

        if ($confirmationCodeSent) {
            LogUtil::registerStatus($this->__f('Done! The password recovery verification code for %s has been sent via e-mail.', $user['uname']));
        }

        return System::redirect(ModUtil::url('Users', 'admin', 'view'));
    }

    /**
     * Display a form to confirm the deletion of one user.
     *
     * Available Get Parameters:
     * - userid (numeric) The user id of the user to be deleted.
     * - uname  (string)  The user name of the user to be deleted.
     *
     * @param array $args All arguments passed to the function.
     *                    $args['userid'] (numeric) the user id of the user to be deleted. Used as a default value if the get parameter
     *                      is not set. Allow the function to be called internally.
     *                    $args['uname'] (string) the user name of the user to be deleted. Used as a default value if the get parameter
     *                      is not set. Allow the function to be called internally.
     *
     * @return string HTML string containing the rendered template.
     */
    public function deleteUsers($args)
    {
        // check permissions
        if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_DELETE)) {
            return LogUtil::registerPermissionError();
        }

        // get arguments
        $userid = FormUtil::getPassedValue('userid', (isset($args['userid']) ? $args['userid'] : null), 'GET');
        $uname  = FormUtil::getPassedValue('uname', (isset($args['uname']) ? $args['uname'] : null), 'GET');

        // check arguments
        if (is_null($userid) && is_null($uname)) {
            LogUtil::registerError($this->__('Sorry! No such user found.'));
            return System::redirect(ModUtil::url('Users', 'admin', 'main'));
        }

        // retreive userid from uname
        if (is_null($userid) && !empty($uname)) {
            $userid = UserUtil::getIdFromName($uname);
        }

        // warning for guest account, own account
        if ($userid == 1) {
            LogUtil::registerError($this->__("Error! You can't delete the guest account."));
            return System::redirect(ModUtil::url('Users', 'admin', 'main'));
        } elseif ($userid == UserUtil::getVar('uid')) {
            LogUtil::registerError($this->__("Error! You can't delete the account you are currently logged into."));
            return System::redirect(ModUtil::url('Users', 'admin', 'main'));
        }

        // get the user vars
        $uname = UserUtil::getVar('uname', $userid);
        if ($uname == false) {
            LogUtil::registerError($this->__('Sorry! No such user found.'));
            return System::redirect(ModUtil::url('Users', 'admin', 'main'));
        }

        $this->view->assign('userid', $userid)
                   ->assign('uname', $uname);

        // return output
        return $this->view->fetch('users_admin_deleteusers.tpl');
    }

    /**
     * Constructs a list of various actions for a list of registrations appropriate for the current user.
     *
     * NOTE: Internal function.
     *
     * @param array  $reglist     The list of registration records.
     * @param string $restoreView Indicates where the calling function expects to return to; 'view' indicates
     *                                  that the calling function expects to return to the registration list
     *                                  and 'display' indicates that the calling function expects to return
     *                                  to an individual registration record.
     *
     * @return array An array of valid action URLs for each registration record in the list.
     */
    protected function getActionsForRegistrations(array $reglist, $restoreView='view')
    {
        $actions = array();
        if (!empty($reglist)) {
            $approvalOrder = $this->getVar('moderation_order', UserUtil::APPROVAL_BEFORE);

            // Don't try to put any visual elements here (images, titles, colors, css classes, etc.). Leave that to
            // the template, so that they can be customized without hacking the core code. In fact, all we really need here
            // is what options are enabled. The template could build everything else. We will put the URL for the action
            // in the array for convenience, but that could be done in the template too, really.
            //
            // Make certain that the following goes from most restricted to least (ADMIN...NONE order).  Having the
            // security check as the outer if statement, and similar foreach loops within each saves on repeated checking
            // of permissions, speeding things up a bit.
            if (SecurityUtil::checkPermission('Users::', '::', ACCESS_ADMIN)) {
                $actions['count'] = 6;
                foreach ($reglist as $key => $reginfo) {
                    $enableVerify = !$reginfo['isverified'];
                    $enableApprove = !$reginfo['isapproved'];
                    $enableForced = !$reginfo['isverified'] && isset($reginfo['pass']) && !empty($reginfo['pass']);
                    $actions['list'][$reginfo['uid']] = array(
                        'display'       =>                  ModUtil::url('Users', 'admin', 'displayRegistration',   array('uid' => $reginfo['uid'])),
                        'modify'        =>                  ModUtil::url('Users', 'admin', 'modifyRegistration',    array('uid' => $reginfo['uid'], 'restoreview' => $restoreView)),
                        'verify'        => $enableVerify ?  ModUtil::url('Users', 'admin', 'verifyRegistration',    array('uid' => $reginfo['uid'], 'restoreview' => $restoreView)) : false,
                        'approve'       => $enableApprove ? ModUtil::url('Users', 'admin', 'approveRegistration',   array('uid' => $reginfo['uid'])) : false,
                        'deny'          =>                  ModUtil::url('Users', 'admin', 'denyRegistration',      array('uid' => $reginfo['uid'])),
                        'approveForce'  => $enableForced ?  ModUtil::url('Users', 'admin', 'approveRegistration',   array('uid' => $reginfo['uid'], 'force' => true)) : false,
                    );
                }
            } elseif (SecurityUtil::checkPermission('Users::', '::', ACCESS_DELETE)) {
                $actions['count'] = 5;
                foreach ($reglist as $key => $reginfo) {
                    $enableVerify = !$reginfo['isverified'] && (($approvalOrder != UserUtil::APPROVAL_BEFORE) || $reginfo['isapproved']);
                    $enableApprove = !$reginfo['isapproved'] && (($approvalOrder != UserUtil::APPROVAL_AFTER) || $reginfo['isverified']);
                    $actions['list'][$reginfo['uid']] = array(
                        'display'       =>                  ModUtil::url('Users', 'admin', 'displayRegistration',   array('uid' => $reginfo['uid'])),
                        'modify'        =>                  ModUtil::url('Users', 'admin', 'modifyRegistration',    array('uid' => $reginfo['uid'], 'restoreview' => $restoreView)),
                        'verify'        => $enableVerify ?  ModUtil::url('Users', 'admin', 'verifyRegistration',    array('uid' => $reginfo['uid'], 'restoreview' => $restoreView)) : false,
                        'approve'       => $enableApprove ? ModUtil::url('Users', 'admin', 'approveRegistration',   array('uid' => $reginfo['uid'])) : false,
                        'deny'          =>                  ModUtil::url('Users', 'admin', 'denyRegistration',      array('uid' => $reginfo['uid'])),
                    );
                }
            } elseif (SecurityUtil::checkPermission('Users::', '::', ACCESS_ADD)) {
                $actions['count'] = 4;
                foreach ($reglist as $key => $reginfo) {
                    $actionUrlArgs['uid'] = $reginfo['uid'];
                    $enableVerify = !$reginfo['isverified'] && (($approvalOrder != UserUtil::APPROVAL_BEFORE) || $reginfo['isapproved']);
                    $enableApprove = !$reginfo['isapproved'] && (($approvalOrder != UserUtil::APPROVAL_AFTER) || $reginfo['isverified']);
                    $actions['list'][$reginfo['uid']] = array(
                        'display'       =>                  ModUtil::url('Users', 'admin', 'displayRegistration',   array('uid' => $reginfo['uid'])),
                        'modify'        =>                  ModUtil::url('Users', 'admin', 'modifyRegistration',    array('uid' => $reginfo['uid'], 'restoreview' => $restoreView)),
                        'verify'        => $enableVerify ?  ModUtil::url('Users', 'admin', 'verifyRegistration',    array('uid' => $reginfo['uid'], 'restoreview' => $restoreView)) : false,
                        'approve'       => $enableApprove ? ModUtil::url('Users', 'admin', 'approveRegistration',   array('uid' => $reginfo['uid'])) : false,
                    );
                }
            } elseif (SecurityUtil::checkPermission('Users::', '::', ACCESS_EDIT)) {
                $actions['count'] = 3;
                foreach ($reglist as $key => $reginfo) {
                    $actionUrlArgs['uid'] = $reginfo['uid'];
                    $enableVerify = !$reginfo['isverified'] && (($approvalOrder != UserUtil::APPROVAL_BEFORE) || $reginfo['isapproved']);
                    $actions['list'][$reginfo['uid']] = array(
                        'display'       =>                  ModUtil::url('Users', 'admin', 'displayRegistration',   array('uid' => $reginfo['uid'])),
                        'modify'        =>                  ModUtil::url('Users', 'admin', 'modifyRegistration',    array('uid' => $reginfo['uid'], 'restoreview' => $restoreView)),
                        'verify'        => $enableVerify ?  ModUtil::url('Users', 'admin', 'verifyRegistration',    array('uid' => $reginfo['uid'], 'restoreview' => $restoreView)) : false,
                    );
                }
            } elseif (SecurityUtil::checkPermission('Users::', '::', ACCESS_MODERATE)) {
                $actions['count'] = 2;
                foreach ($reglist as $key => $reginfo) {
                    $actionUrlArgs['uid'] = $reginfo['uid'];
                    $enableVerify = !$reginfo['isverified'] && (($approvalOrder != UserUtil::APPROVAL_BEFORE) || $reginfo['isapproved']);
                    $actions['list'][$reginfo['uid']] = array(
                        'display'       =>                  ModUtil::url('Users', 'admin', 'displayRegistration',   array('uid' => $reginfo['uid'])),
                        'verify'        => $enableVerify ?  ModUtil::url('Users', 'admin', 'verifyRegistration',    array('uid' => $reginfo['uid'], 'restoreview' => $restoreView)) : false,
                    );
                }
            }
        }

        return $actions;
    }

    /**
     * Shows all the registration requests (applications), and the options available to the current user.
     *
     * Available Request Parameters:
     * - startnum (int) The ordinal number of the first record to display, especially if using itemsperpage to limit the number of records on a single page.
     *
     * @return string HTML string containing the rendered template.
     */
    public function viewRegistrations()
    {
        // security check
        if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_MODERATE)) {
            return LogUtil::registerPermissionError();
        }

        $regCount = ModUtil::apiFunc('Users', 'registration', 'countAll');
        $limitNumRows = $this->getVar('itemsperpage', 25);
        if (!is_numeric($limitNumRows) || ((int)$limitNumRows != $limitNumRows) || (($limitNumRows < 1) && ($limitNumRows != -1))) {
            $limitNumRows = 25;
        }

        $backFromAction = FormUtil::getPassedValue('restoreview', false, 'GET');

        if ($backFromAction) {
            $returnArgs = SessionUtil::getVar('Users_admin_viewRegistrations', array('startnum' => 1), '/', false);
            SessionUtil::delVar('Users_admin_viewRegistrations');

            if ($limitNumRows < 1) {
                unset($returnArgs['startnum']);
            } elseif (!isset($returnArgs['startnum']) || !is_numeric($returnArgs['startnum']) || empty($returnArgs['startnum'])
                    || ((int)$returnArgs['startnum'] != $returnArgs['startnum']) || ($returnArgs['startnum'] < 1)) {

                $returnArgs['startnum'] = 1;
            } elseif ($returnArgs['startnum'] > $regCount) {
                // Probably deleted something. Reset to last page.
                $returnArgs['startnum'] = $regCount - ($regCount % $limitNumRows) + 1;
            } elseif (($returnArgs['startnum'] % $limitNumRows) != 1) {
                // Probably deleted something. Reset to last page.
                $returnArgs['startnum'] = $returnArgs['startnum'] - ($returnArgs['startnum'] % $limitNumRows) + 1;
            }

            // Reset the URL and load the proper page.
            return System::redirect(ModUtil::url('Users', 'admin', 'viewRegistrations', $returnArgs));
        } else {
            $reset = false;

            $startNum = FormUtil::getPassedValue('startnum', 1);
            if (!is_numeric($startNum) || empty($startNum)  || ((int)$startNum != $startNum) || ($startNum < 1)) {
                $limitOffset = -1;
                $reset = true;
            } elseif ($limitNumRows < 1) {
                $limitOffset = -1;
            } elseif ($startNum > $regCount) {
                // Probably deleted something. Reset to last page.
                $limitOffset = $regCount - ($regCount % $limitNumRows);
                $reset = (($regCount == 0) && ($startNum != 1));
            } elseif (($startNum % $limitNumRows) != 1) {
                // Reset to page boundary
                $limitOffset = $startNum - ($startNum % $limitOffset);
                $reset = true;
            } else {
                $limitOffset = $startNum - 1;
            }

            if ($reset) {
                $returnArgs = array();
                if ($limitOffset >= 0) {
                    $returnArgs['startnum'] = $limitOffset + 1;
                }
                System::redirect(ModUtil::url('Users', 'admin', 'viewRegistrations', $returnArgs));
            }
        }

        SessionUtil::setVar('startnum', ($limitOffset + 1), 'Users_admin_viewRegistrations');

        $reglist = ModUtil::apiFunc('Users', 'registration', 'getAll', array('limitoffset' => $limitOffset, 'limitnumrows' => $limitNumRows));

        if (($reglist === false) || !is_array($reglist)) {
            if (!LogUtil::hasErrors()) {
                LogUtil::registerError($this->__('An error occurred while trying to retrieve the registration records.'));
            }
            return System::redirect(ModUtil::url('Users', 'admin'), null, 500);
        }

        $actions = $this->getActionsForRegistrations($reglist, 'view');

        $pager = array();
        if ($limitNumRows > 0) {
            $pager = array(
                'rowcount'  => $regCount,
                'limit'     => $limitNumRows,
                'posvar'    => 'startnum',
            );
        }

        $this->view->add_core_data()
                    ->assign('reglist', $reglist)
                    ->assign('actions', $actions)
                    ->assign('pager', $pager);

        return $this->view->fetch('users_admin_viewregistrations.tpl');
    }

    /**
     * Displays the information on a single registration request.
     *
     * Available Get Parameters:
     * - userid (numeric) The id of the registration request (id) to retrieve and display.
     *
     * @return string HTML string containing the rendered template.
     */
    public function displayRegistration()
    {
        if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_MODERATE)) {
            return LogUtil::registerPermissionError();
        }

        // Get parameters from whatever input we need.
        // (Note that the name of the passed parameter is 'userid' but that it
        // is actually a registration application id.)
        $uid = FormUtil::getPassedValue('uid', null, 'GET');

        if (empty($uid) || !is_numeric($uid)) {
            return LogUtil::registerArgsError(ModUtil::url('Users', 'admin', 'viewRegistrations', array('return' => true)));
        }

        $reginfo = ModUtil::apiFunc('Users', 'registration', 'get', array('uid' => $uid));
        if (!$reginfo) {
            // get application could fail (return false) because of a nonexistant
            // record, no permission to read an existing record, or a database error
            return LogUtil::registerError($this->__('Unable to retrieve registration record. '
                . 'The record with the specified id might not exist, or you might not have permission to access that record.'));
        }

        // ...for the Profile module's display of dud items (it assumes a full user).
        // Be sure that this $reginfo is never used to update the database!
        $reginfo['__ATTRIBUTES__'] = array_merge($reginfo['__ATTRIBUTES__'], $reginfo['dynadata']);

        // So expiration can be displayed
        $regExpireDays = $this->getVar('reg_expiredays', 0);
        if (!$reginfo['isverified'] && !empty($reginfo['verificationsent']) && ($regExpireDays > 0)) {
            try {
                $expiresUTC = new DateTime($reginfo['verificationsent'], new DateTimeZone('UTC'));
            } catch (Exception $e) {
                $expiresUTC = new DateTime(UserUtil::EXPIRED, new DateTimeZone('UTC'));
            }
            $expiresUTC->modify("+{$regExpireDays} days");
            $reginfo['validuntil'] = DateUtil::formatDatetime($expiresUTC->format(UserUtil::DATETIME_FORMAT),
                $this->__('%m-%d-%Y %H:%M'));
        }

        if (ModUtil::available('Legal')) {
            $touActive = ModUtil::getVar('Legal', 'termsofuse', true);
            $ppActive = ModUtil::getVar('Legal', 'privacypolicy', true);
        } else {
            $touActive = false;
            $ppActive = false;
        }

        $actions = $this->getActionsForRegistrations(array($reginfo), 'display');

        $this->view->add_core_data()
            ->assign('reginfo', $reginfo)
            ->assign('actions', $actions)
            ->assign('touActive', $touActive)
            ->assign('ppActive', $ppActive);

        return $this->view->fetch('users_admin_displayregistration.tpl');
    }

    /**
     * Display a form to edit one tegistration account.
     *
     * @return string|bool The rendered template; false on error.
     */
    public function modifyRegistration()
    {
        if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }

        $uid = FormUtil::getPassedValue('uid', null, 'GET');

        if (isset($uid)) {
            if (!is_numeric($uid) || ((int)$uid != $uid)) {
                return LogUtil::registerError($this->__('Error! Invalid registration uid.'),
                    ModUtil::url('Users', 'admin', 'viewRegistrations', array('restoreview' => true)));
            }

            $reginfo = ModUtil::apiFunc('Users', 'registration', 'get', array('uid' => $uid));

            if (!$reginfo) {
                return LogUtil::registerError($this->__('Error! Unable to load registration record.'),
                    ModUtil::url('Users', 'admin', 'viewRegistrations', array('restoreview' => true)));
            }

            $emailAgain = $reginfo['email'];
        } else {
            $args = SessionUtil::getVar('Users_Admin_modifyRegistration', array(), '/', false);
            SessionUtil::delVar('Users_Admin_modifyRegistration');

            if (!isset($args) || empty($args) || !isset($args['reginfo']) || empty($args['reginfo']) || !isset($args['registrationErrors']) || empty($args['registrationErrors'])) {
                return LogUtil::registerError($this->__('Error! Invalid registration id, or invalid arguments returned after validation.'),
                    ModUtil::url('Users', 'admin', 'viewRegistrations', array('restoreview' => true)));
            }

            $reginfo = $args['reginfo'];
            $emailAgain = $args['emailagain'];

            $registrationErrors = $args['registrationErrors'];
            // For now do it this way. Later maybe show the messages with the field--and if that's
            // done, then $errorFields and $errorMessages not needed--we'd just pass $registrationErrors directly.
            $errorInfo = ModUtil::apiFunc('Users', 'user', 'processRegistrationErrorsForDisplay', array('registrationErrors' => $registrationErrors));
        }

        $restoreView = FormUtil::getPassedValue('restoreview', 'view', 'GET');
        if ($restoreView == 'view') {
            $cancelUrl = ModUtil::url('Users', 'admin', 'viewRegistrations', array('restoreview' => true));
        } else {
            $cancelUrl = ModUtil::url('Users', 'admin', 'displayRegistration', array('uid' => $reginfo['uid']));
        }

        // So expiration can be displayed
        $regExpireDays = $this->getVar('reg_expiredays', 0);
        if (!$reginfo['isverified'] && !empty($reginfo['verificationsent']) && ($regExpireDays > 0)) {
            try {
                $expiresUTC = new DateTime($reginfo['verificationsent'], new DateTimeZone('UTC'));
            } catch (Exception $e) {
                $expiresUTC = new DateTime(UserUtil::EXPIRED, new DateTimeZone('UTC'));
            }
            $expiresUTC->modify("+{$regExpireDays} days");
            $reginfo['validuntil'] = DateUtil::formatDatetime($expiresUTC->format(UserUtil::DATETIME_FORMAT),
                $this->__('%m-%d-%Y %H:%M'));
        }

        $modVars = $this->getVars();
        $profileModName = System::getVar('profilemodule', '');
        $profileModAvailable = !empty($profileModName) && ModUtil::available($profileModName);

        $rendererArgs['reginfo'] = $reginfo;
        $rendererArgs['emailagain'] = $emailAgain;
        $rendererArgs['sitename'] = System::getVar('sitename', System::getHost());
        $rendererArgs['errorMessages'] = (isset($errorInfo['errorMessages']) && !empty($errorInfo['errorMessages'])) ? $errorInfo['errorMessages'] : array();
        $rendererArgs['errorFields'] = (isset($errorInfo['errorFields']) && !empty($errorInfo['errorFields'])) ? $errorInfo['errorFields'] : array();
        $rendererArgs['registrationErrors'] = (isset($registrationErrors) && !empty($registrationErrors)) ? $registrationErrors : array();
        $rendererArgs['usePwdStrengthMeter'] = (isset($modVars['use_password_strength_meter']) && !empty($modVars['use_password_strength_meter'])) ? $modVars['use_password_strength_meter'] : false;
        $rendererArgs['showProps'] = $profileModAvailable && isset($modVars['reg_optitems']) && $modVars['reg_optitems'];
        $rendererArgs['profileModName'] = $profileModName;
        $rendererArgs['restoreview'] = $restoreView;
        $rendererArgs['cancelurl'] = $cancelUrl;

        // Return the output that has been generated by this function
        $this->view->add_core_data()
            ->assign($rendererArgs);
        return $this->view->fetch('users_admin_modifyregistration.tpl');
    }

    /**
     * Processes the results of modifyRegistration.
     *
     * @return bool True on success; otherwise false.
     */
    public function updateRegistration()
    {
        // check permisisons
        if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_ADD)) {
            return LogUtil::registerPermissionError();
        }

        if (!SecurityUtil::confirmAuthKey('Users')) {
            return LogUtil::registerAuthidError(ModUtil::url('Users', 'admin', 'view'));
        }

        $reginfo = FormUtil::getPassedValue('reginfo', null, 'POST');
        $reginfo['dynadata'] = FormUtil::getPassedValue('dynadata', array(), 'POST');

        $restoreView = FormUtil::getPassedValue('restoreview', 'view', 'POST');
        if ($restoreView == 'display') {
            $doneUrl = ModUtil::url('Users', 'admin', 'displayRegistration', array('uid' => $reginfo['uid']));
        } else {
            $doneUrl = ModUtil::url('Users', 'admin', 'viewRegistrations', array('restoreview' => true));
        }

        $checkMode = 'modify';
        $setPassword = false;
        $emailAgain = FormUtil::getPassedValue('emailagain', null, 'POST');
        $sendPassword = false;

        $registrationErrors = ModUtil::apiFunc('Users', 'registration', 'getRegistrationErrors', array(
            'checkmode'     => $checkMode,
            'reginfo'       => $reginfo,
            'emailagain'    => $emailAgain,
        ));

        if ($registrationErrors) {
            SessionUtil::setVar('reginfo', $reginfo, 'Users_Admin_modifyRegistration', true, true);
            SessionUtil::setVar('emailagain', $emailAgain, 'Users_Admin_modifyRegistration', true, true);
            SessionUtil::setVar('registrationErrors', $registrationErrors, 'Users_Admin_modifyRegistration', true, true);

            return System::redirect(ModUtil::url('Users', 'admin', 'modifyRegistration'));
        }

        $oldReginfo = UserUtil::getVars($reginfo['uid'], false, '', true);

        foreach ($reginfo as $field => $value) {
            if (substr($field, 0, 2) != '__') {
                switch ($field) {
                    case 'uid':
                        // No update
                        break;
                    case 'dynadata':
                        if (isset($value) && is_array($value) && !empty($value)) {
                            $value = serialize($value);
                            UserUtil::setVar($field, $value, $reginfo['uid']);
                            // NOTE: Issuance of a user.update event or an item update hook call is controlled by setVar
                        }
                        break;
                    case 'uname':
                        if ($value != $oldReginfo[$field]) {
                            $updateUserObj = array(
                                'uid'   => $reginfo['uid'],
                                'uname' => $reginfo['uname'],
                            );
                            DBUtil::updateObject($updateUserObj, 'users', '', 'uid', false, false);
                            // NOTE: This is a registration, not a "real" user, so no user.update event and no item
                            // update hook call.
                            // TODO - Should we fire a special registration.update event?
                        }
                        break;
                    case 'email':
                        // if email has changed, update it and send a new verification email (if user is not verified yet)
                        if ($value != $oldReginfo[$field]) {
                            $updateUserObj = array(
                                'uid'   => $reginfo['uid'],
                                'email' => $reginfo['email'],
                            );
                            DBUtil::updateObject($updateUserObj, 'users', '', 'uid', false, false);

                            $approvalOrder = $this->getVar('moderation_order', UserUtil::APPROVAL_BEFORE);
                            if (!$oldReginfo['isverified'] && (($approvalOrder != UserUtil::APPROVAL_BEFORE) || $oldReginfo['isapproved'])) {
                                $oldReginfo[$field] = $value;
                                $verificationSent = ModUtil::apiFunc('Users', 'registration', 'sendVerificationCode',
                                                        array('reginfo'   => $oldReginfo,
                                                              'force'     => true));
                            }
                        }
                        break;
                    default:
                        if ($value != $oldReginfo[$field]) {
                            UserUtil::setVar($field, $value, $reginfo['uid']);
                            // NOTE: Issuance of a user.update event or an item update hook call is controlled by setVar
                        }
                }
            }
        }

        if ($reginfo) {
            LogUtil::registerStatus($this->__('Done! Updated registration.'));
        } else {
            LogUtil::registerError($this->__('Error! Could not update the registration.'));
        }

        return System::redirect($doneUrl);
    }

    /**
     * Renders and processes the admin's force-verify form.
     *
     * Renders and processes a form confirming an administrators desire to skip verification for
     * a registration record, approve it and add it to the users table.
     *
     * @return string|bool The rendered template; true on success; otherwise false.
     */
    public function verifyRegistration()
    {
        if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_MODERATE)) {
            return LogUtil::registerPermissionError();
        }

        $uid = FormUtil::getPassedValue('uid', null, 'GETPOST');
        $forceVerification = $this->currentUserIsAdmin() && FormUtil::getPassedValue('force', false, 'GETPOST');
        $restoreView = FormUtil::getPassedValue('restoreview', 'view', 'GETPOST');

        if (!isset($uid) || !is_numeric($uid) || ((int)$uid != $uid)) {
            return LogUtil::registerArgsError();
        }

        // Got just a uid.
        $reginfo = ModUtil::apiFunc('Users', 'registration', 'get', array('uid' => $uid));
        if (!$reginfo) {
            return LogUtil::registerError($this->__f('Error! Unable to retrieve registration record with uid \'%1$s\'', $uid));
        }

        if ($restoreView == 'display') {
            $cancelUrl = ModUtil::url('Users', 'admin', 'displayRegistration', array('uid' => $reginfo['uid']));
        } else {
            $cancelUrl = ModUtil::url('Users', 'admin', 'viewRegistrations', array('restoreview' => true));
        }

        $approvalOrder = $this->getVar('moderation_order', UserUtil::APPROVAL_BEFORE);

        if ($reginfo['isverified']) {
            return LogUtil::registerError(
                $this->__f('Error! A verification code cannot be sent for the registration record for \'%1$s\'. It is already verified.', $reginfo['uname']),
                null,
                $cancelUrl);
        } elseif (!$forceVerification && ($approvalOrder == UserUtil::APPROVAL_BEFORE) && !$reginfo['isapproved']) {
            return LogUtil::registerError(
                $this->__f('Error! A verification code cannot be sent for the registration record for \'%1$s\'. It must first be approved.', $reginfo['uname']),
                null,
                $cancelUrl);
        }

        if (!FormUtil::getPassedValue('confirmed', false, 'GETPOST') || !SecurityUtil::confirmAuthKey('Users')) {
            // Bad or no auth key, or bad or no confirmation, so display confirmation.

            // ...for the Profile module's display of dud items (it assumes a full user).
            // Be sure that this $reginfo is never used to update the database!
            $reginfo['__ATTRIBUTES__'] = array_merge($reginfo['__ATTRIBUTES__'], $reginfo['dynadata']);

            // So expiration can be displayed
            $regExpireDays = $this->getVar('reg_expiredays', 0);
            if (!$reginfo['isverified'] && $reginfo['verificationsent'] && ($regExpireDays > 0)) {
                try {
                    $expiresUTC = new DateTime($reginfo['verificationsent'], new DateTimeZone('UTC'));
                } catch (Exception $e) {
                    $expiresUTC = new DateTime(UserUtil::EXPIRED, new DateTimeZone('UTC'));
                }
                $expiresUTC->modify("+{$regExpireDays} days");
                $reginfo['validuntil'] = DateUtil::formatDatetime($expiresUTC->format(UserUtil::DATETIME_FORMAT),
                    $this->__('%m-%d-%Y %H:%M'));
            }

            if (ModUtil::available('Legal')) {
                $touActive = ModUtil::getVar('Legal', 'termsofuse', true);
                $ppActive = ModUtil::getVar('Legal', 'privacypolicy', true);
            } else {
                $touActive = false;
                $ppActive = false;
            }

            $this->view->add_core_data()
                ->assign('reginfo', $reginfo)
                ->assign('restoreview', $restoreView)
                ->assign('force', $forceVerification)
                ->assign('cancelurl', $cancelUrl)
                ->assign('touActive', $touActive)
                ->assign('ppActive', $ppActive);

            return $this->view->fetch('users_admin_verifyregistration.tpl');
        } else {
            $verificationSent = ModUtil::apiFunc('Users', 'registration', 'sendVerificationCode', array(
                'reginfo'   => $reginfo,
                'force'     => $forceVerification,
            ));

            if (!$verificationSent) {
                return LogUtil::registerError($this->__f('Sorry! There was a problem sending a verification code to \'%1$s\'.', $reginfo['uname']), null, $cancelUrl);
            } else {
                return LogUtil::registerStatus($this->__f('Done! Verification code sent to \'%1$s\'.', $reginfo['uname']), $cancelUrl);
            }
        }
    }

    /**
     * Renders and processes a form confirming an administrators desire to approve a registration.
     *
     * If the registration record is also verified (or verification is not needed) a users table
     * record is created.
     *
     * @return string|bool The rendered template; true on success; otherwise false.
     */
    public function approveRegistration()
    {
        if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_MODERATE)) {
            return LogUtil::registerPermissionError();
        }

        $uid = FormUtil::getPassedValue('uid', null, 'GETPOST');
        $forceVerification = $this->currentUserIsAdmin() && FormUtil::getPassedValue('force', false, 'GETPOST');
        $restoreView = FormUtil::getPassedValue('restoreview', 'view', 'GETPOST');

        if (!isset($uid) || !is_numeric($uid) || ((int)$uid != $uid)) {
            return LogUtil::registerArgsError();
        }

        // Got just an id.
        $reginfo = ModUtil::apiFunc('Users', 'registration', 'get', array('uid' => $uid));
        if (!$reginfo) {
            return LogUtil::registerError($this->__f('Error! Unable to retrieve registration record with uid \'%1$s\'', $uid));
        }

        if ($restoreView == 'display') {
            $cancelUrl = ModUtil::url('Users', 'admin', 'displayRegistration', array('uid' => $reginfo['uid']));
        } else {
            $cancelUrl = ModUtil::url('Users', 'admin', 'viewRegistrations', array('restoreview' => true));
        }

        $approvalOrder = $this->getVar('moderation_order', UserUtil::APPROVAL_BEFORE);

        if ($reginfo['isapproved'] && !$forceVerification) {
            return LogUtil::registerError(
                $this->__f('Warning! Nothing to do! The registration record with uid \'%1$s\' is already approved.', $reginfo['uid']),
                null,
                $cancelUrl);
        } elseif (!$forceVerification && ($approvalOrder == UserUtil::APPROVAL_AFTER) && !$reginfo['isapproved']
                && !SecurityUtil::checkPermission('Users::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerError(
                $this->__f('Error! The registration record with uid \'%1$s\' cannot be approved. The registration\'s e-mail address must first be verified.', $reginfo['uid']),
                null,
                $cancelUrl);
        } elseif ($forceVerification && (!isset($reginfo['pass']) || empty($reginfo['pass']))) {
            return LogUtil::registerError(
                $this->__f('Error! E-mail verification cannot be skipped for \'%1$s\'. The user must establish a password as part of the verification process.', $reginfo['uname']),
                null,
                $cancelUrl);
        }

        if (!FormUtil::getPassedValue('confirmed', false, 'GETPOST') || !SecurityUtil::confirmAuthKey('Users')) {
            // Bad or no auth key, or bad or no confirmation, so display confirmation.

            // ...for the Profile module's display of dud items (it assumes a full user).
            // Be sure that this $reginfo is never used to update the database!
            $reginfo['__ATTRIBUTES__'] = array_merge($reginfo['__ATTRIBUTES__'], $reginfo['dynadata']);

            // So expiration can be displayed
            $regExpireDays = $this->getVar('reg_expiredays', 0);
            if (!$reginfo['isverified'] && !empty($reginfo['verificationsent']) && ($regExpireDays > 0)) {
                try {
                    $expiresUTC = new DateTime($reginfo['verificationsent'], new DateTimeZone('UTC'));
                } catch (Exception $e) {
                    $expiresUTC = new DateTime(UserUtil::EXPIRED, new DateTimeZone('UTC'));
                }
                $expiresUTC->modify("+{$regExpireDays} days");
                $reginfo['validuntil'] = DateUtil::formatDatetime($expiresUTC->format(UserUtil::DATETIME_FORMAT),
                    $this->__('%m-%d-%Y %H:%M'));
            }

            if (ModUtil::available('Legal')) {
                $touActive = ModUtil::getVar('Legal', 'termsofuse', true);
                $ppActive = ModUtil::getVar('Legal', 'privacypolicy', true);
            } else {
                $touActive = false;
                $ppActive = false;
            }

            $this->view->add_core_data()
                ->assign('reginfo', $reginfo)
                ->assign('restoreview', $restoreView)
                ->assign('force', $forceVerification)
                ->assign('cancelurl', $cancelUrl)
                ->assign('touActive', $touActive)
                ->assign('ppActive', $ppActive);

            return $this->view->fetch('users_admin_approveregistration.tpl');
        } else {
            $approved = ModUtil::apiFunc('Users', 'registration', 'approve', array(
                'reginfo'   => $reginfo,
                'force'     => $forceVerification,
            ));

            if (!$approved) {
                return LogUtil::registerError($this->__f('Sorry! There was a problem approving the registration for \'%1$s\'.', $reginfo['uname']), null, $cancelUrl);
            } else {
                if (isset($approved['uid'])) {
                    return LogUtil::registerStatus($this->__f('Done! The registration for \'%1$s\' has been approved and a new user account has been created.', $reginfo['uname']), $cancelUrl);
                } else {
                    return LogUtil::registerStatus($this->__f('Done! The registration for \'%1$s\' has been approved and is awaiting e-mail verification.', $reginfo['uname']), $cancelUrl);
                }
            }
        }
    }

    /**
     * Render and process a form confirming the administrator's rejection of a registration.
     *
     * If the denial is confirmed, the registration is deleted from the database.
     *
     * @return string|bool The rendered template; true on success; otherwise false.
     */
    public function denyRegistration()
    {
        if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_DELETE)) {
            return LogUtil::registerPermissionError();
        }

        $uid = FormUtil::getPassedValue('uid', null, 'GETPOST');
        $restoreView = FormUtil::getPassedValue('restoreview', 'view', 'GETPOST');

        if (!isset($uid) || !is_numeric($uid) || ((int)$uid != $uid)) {
            return LogUtil::registerArgsError();
        }

        // Got just a uid.
        $reginfo = ModUtil::apiFunc('Users', 'registration', 'get', array('uid' => $uid));
        if (!$reginfo) {
            return LogUtil::registerError($this->__f('Error! Unable to retrieve registration record with uid \'%1$s\'', $uid));
        }

        if ($restoreView == 'display') {
            $cancelUrl = ModUtil::url('Users', 'admin', 'displayRegistration', array('uid' => $reginfo['uid']));
        } else {
            $cancelUrl = ModUtil::url('Users', 'admin', 'viewRegistrations', array('restoreview' => true));
        }

        if (!FormUtil::getPassedValue('confirmed', false, 'GETPOST') || !SecurityUtil::confirmAuthKey('Users')) {
            // Bad or no auth key, or bad or no confirmation, so display confirmation.

            // ...for the Profile module's display of dud items (it assumes a full user).
            // Be sure that this $reginfo is never used to update the database!
            $reginfo['__ATTRIBUTES__'] = array_merge($reginfo['__ATTRIBUTES__'], $reginfo['dynadata']);

            // So expiration can be displayed
            $regExpireDays = $this->getVar('reg_expiredays', 0);
            if (!$reginfo['isverified'] && !empty($reginfo['verificationsent']) && ($regExpireDays > 0)) {
                try {
                    $expiresUTC = new DateTime($reginfo['verificationsent'], new DateTimeZone('UTC'));
                } catch (Exception $e) {
                    $expiresUTC = new DateTime(UserUtil::EXPIRED, new DateTimeZone('UTC'));
                }
                $expiresUTC->modify("+{$regExpireDays} days");
                $reginfo['validuntil'] = DateUtil::formatDatetime($expiresUTC->format(UserUtil::DATETIME_FORMAT),
                    $this->__('%m-%d-%Y %H:%M'));
            }

            if (ModUtil::available('Legal')) {
                $touActive = ModUtil::getVar('Legal', 'termsofuse', true);
                $ppActive = ModUtil::getVar('Legal', 'privacypolicy', true);
            } else {
                $touActive = false;
                $ppActive = false;
            }

            $this->view->add_core_data()
                ->assign('reginfo', $reginfo)
                ->assign('restoreview', $restoreView)
                ->assign('force', $forceVerification)
                ->assign('cancelurl', $cancelUrl)
                ->assign('touActive', $touActive)
                ->assign('ppActive', $ppActive);

            return $this->view->fetch('users_admin_denyregistration.tpl');
        } else {
            $sendNotification = FormUtil::getPassedValue('usernotify', false, 'POST');
            $reason = FormUtil::getPassedValue('reason', '', 'POST');

            $denied = ModUtil::apiFunc('Users', 'registration', 'remove', array(
                'reginfo'   => $reginfo,
            ));

            if (!$denied) {
                return LogUtil::registerError($this->__f('Sorry! There was a problem deleting the registration for \'%1$s\'.', $reginfo['uname']), null, $cancelUrl);
            } else {
                if ($sendNotification) {
                    $siteurl   = System::getBaseUrl();
                    $rendererArgs = array(
                        'sitename'  => System::getVar('sitename'),
                        'siteurl'   => substr($siteurl, 0, strlen($siteurl)-1),
                        'reginfo'   => $reginfo,
                        'reason'    => $reason,
                    );

                    $sent = ModUtil::apiFunc('Users', 'user', 'sendNotification', array(
                        'toAddress'         => $reginfo['email'],
                        'notificationType'  => 'regdeny',
                        'templateArgs'      => $rendererArgs
                    ));
                }
                return LogUtil::registerStatus($this->__f('Done! The registration for \'%1$s\' has been denied and deleted.', $reginfo['uname']), $cancelUrl);
            }
        }
    }

    /**
     * Edit user configuration settings.
     *
     * @see    function settings_admin_main()
     *
     * @return string HTML string containing the rendered template.
     */
    public function modifyConfig()
    {
        // Security check
        if (!(SecurityUtil::checkPermission('Users::', '::', ACCESS_ADMIN))) {
            return LogUtil::registerPermissionError();
        }

        $config = SessionUtil::getVar('config', array(), 'Users_Admin_modifyConfig', false);
        $errorFields = SessionUtil::getVar('errorFields', array(), 'Users_Admin_modifyConfig', false);
        SessionUtil::delVar('Users_Admin_modifyConfig');

        $authmodules = array();
        $modules = ModUtil::getModulesCapableOf('authentication');
        foreach ($modules as $modinfo) {
            if (ModUtil::available($modinfo['name'])) {
                $authmodules[] = $modinfo;
            }
        }

        $profileModule = System::getVar('profilemodule', '');

        // assign the module vars
        $this->view->assign('config', empty($config) ? $this->getVars() : $config)
                   ->assign('errorFields', $errorFields)
                   ->assign('profile', (!empty($profileModule) && ModUtil::available($profileModule)))
                   ->assign('legal', ModUtil::available('Legal'))
                   ->assign('tou_active', ModUtil::getVar('Legal', 'termsofuse', true))
                   ->assign('pp_active',  ModUtil::getVar('Legal', 'privacypolicy', true))
                   ->assign('authmodules', $authmodules);

        // Return the output that has been generated by this function
        return $this->view->fetch('users_admin_modifyconfig.tpl');
    }

    /**
     * Update user configuration settings.
     *
     * Available Post Parameters:
     * - config (array) An associative array of configuration settings for the Users module.
     *
     * @see    Setting_Admin::main()
     *
     * @return bool True if configuration saved; false if permission error.
     */
    public function updateConfig()
    {
        // security check
        if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        // get our input
        $config = FormUtil::getPassedValue('config', '', 'POST');

        $errorFields = array();
        if (!isset($config['anonymous']) || empty($config['anonymous'])) {
            $errorFields['users_anonymous'] = true;
            $this->registerError($this->__('You must provide a display name for anonymous users.'));
        }
        if (!isset($config['itemsperpage']) || empty($config['itemsperpage']) || !is_numeric($config['itemsperpage'])
                || ((int)$config['itemsperpage'] != $config['itemsperpage'])
                || (($config['itemsperpage'] < 1) && ($config['itemsperpage'] != -1))) {

            $errorFields['users_itemsperpage'] = true;
            $this->registerError($this->__('The number of items displayed per page must be a positive integer, or -1 to display all items.'));
        }
        if (!isset($config['avatarpath']) || empty($config['avatarpath'])) {
            $errorFields['users_avatarpath'] = true;
            $this->registerError($this->__('You must provide a path to user\'s avatar images.'));
        }
        if (!isset($config['gravatarimage']) || empty($config['gravatarimage'])) {
            $errorFields['users_gravatarimage'] = true;
            $this->registerError($this->__('You must provide a file name for the default gravatar image.'));
        }
        if (!isset($config['userimg']) || empty($config['userimg'])) {
            $errorFields['users_userimg'] = true;
            $this->registerError($this->__('You must provide a path to account page images.'));
        }
        if (!isset($config['accountitemsperpage']) || empty($config['accountitemsperpage']) || !is_numeric($config['accountitemsperpage'])
                || ((int)$config['accountitemsperpage'] != $config['accountitemsperpage']) || ($config['accountitemsperpage'] < 1)) {

            $errorFields['users_accountitemsperpage'] = true;
            $this->registerError($this->__('The number of links per account page must be a positive integer.'));
        }
        if (!isset($config['accountitemsperrow']) || empty($config['accountitemsperrow']) || !is_numeric($config['accountitemsperrow'])
                || ((int)$config['accountitemsperrow'] != $config['accountitemsperrow']) || ($config['accountitemsperrow'] < 1)) {

            $errorFields['users_accountitemsperrow'] = true;
            $this->registerError($this->__('The number of links per account page row must be a positive integer.'));
        }
        if (!isset($config['minpass']) || empty($config['minpass']) || !is_numeric($config['minpass'])
                || ((int)$config['minpass'] != $config['minpass']) || ($config['minpass'] < 1)) {

            $errorFields['users_minpass'] = true;
            $this->registerError($this->__('The minimum password length must be a positive integer.'));
        }
        if (!isset($config['minage']) || !is_numeric($config['minage']) || ((int)$config['minage'] != $config['minage'])
                || ($config['minage'] < 0)) {

            $errorFields['users_minage'] = true;
            $this->registerError($this->__('The minimum age permitted to register must be zero (0) or a positive integer.'));
        }
        if (!isset($config['reg_expiredays']) || !is_numeric($config['reg_expiredays'])
                || ((int)$config['reg_expiredays'] != $config['reg_expiredays']) || ($config['reg_expiredays'] < 0)) {

            $errorFields['reg_expiredays'] = true;
            $this->registerError($this->__('The number of days before a registration pending verification is expired must be zero (0) or a positive integer.'));
        }
        if (isset($config['reg_question']) && !empty($config['reg_question'])) {
            if (!isset($config['reg_answer']) || empty($config['reg_answer'])) {
                $errorFields['users_reg_answer'] = true;
                $this->registerError($this->__('If a spam protection question is provided, then the corresponding answer must also be provided.'));
            }
        }
        if (!isset($config['chgemail_expiredays']) || !is_numeric($config['chgemail_expiredays'])
                || ((int)$config['chgemail_expiredays'] != $config['chgemail_expiredays']) || ($config['chgemail_expiredays'] < 0)) {

            $errorFields['chgemail_expiredays'] = true;
            $this->registerError($this->__('The number of days before an e-mail change request pending verification is expired must be zero (0) or a positive integer.'));
        }
        if (!isset($config['chgpass_expiredays']) || !is_numeric($config['chgpass_expiredays'])
                || ((int)$config['chgpass_expiredays'] != $config['chgpass_expiredays']) || ($config['chgpass_expiredays'] < 0)) {

            $errorFields['chgpass_expiredays'] = true;
            $this->registerError($this->__('The number of days before a password reset request pending verification is expired must be zero (0) or a positive integer.'));
        }

        if (!empty($errorFields)) {
            SessionUtil::setVar('config', $config, 'Users_Admin_modifyConfig');
            SessionUtil::setVar('errorFields', $errorFields, 'Users_Admin_modifyConfig');
        } else {
            if (!isset($config['reg_noregreasons'])) {
                $config['reg_noregreasons'] = '';
            }

            if (!isset($config['reg_optitems'])) {
                $config['reg_optitems'] = 0;
            }

            $this->setVar('itemsperpage', $config['itemsperpage'])
                 ->setVar('accountdisplaygraphics', $config['accountdisplaygraphics'])
                 ->setVar('accountitemsperpage', $config['accountitemsperpage'])
                 ->setVar('accountitemsperrow', $config['accountitemsperrow'])
                 ->setVar('changepassword', $config['changepassword'])
                 ->setVar('changeemail', $config['changeemail'])
                 ->setVar('userimg', $config['userimg'])
                 ->setVar('reg_uniemail', $config['reg_uniemail'])
                 ->setVar('reg_optitems', $config['reg_optitems'])
                 ->setVar('reg_allowreg', $config['reg_allowreg'])
                 ->setVar('reg_noregreasons', $config['reg_noregreasons'])
                 ->setVar('moderation', $config['moderation'])
                 ->setVar('moderation_order', $config['moderation_order'])
                 ->setVar('reg_verifyemail', $config['reg_verifyemail'])
                 ->setVar('reg_expiredays', $config['reg_expiredays'])
                 ->setVar('reg_notifyemail', $config['reg_notifyemail'])
                 ->setVar('reg_Illegaldomains', $config['reg_Illegaldomains'])
                 ->setVar('reg_Illegalusername', $config['reg_Illegalusername'])
                 ->setVar('reg_Illegaluseragents', $config['reg_Illegaluseragents'])
                 ->setVar('minage', $config['minage'])
                 ->setVar('minpass', $config['minpass'])
                 ->setVar('anonymous', $config['anonymous'])
                 ->setVar('loginviaoption', $config['loginviaoption'])
                 ->setVar('hash_method', $config['hash_method'])
                 ->setVar('login_redirect', $config['login_redirect'])
                 ->setVar('reg_question', $config['reg_question'])
                 ->setVar('reg_answer', $config['reg_answer'])
                 ->setVar('use_password_strength_meter', $config['use_password_strength_meter'])
                 ->setVar('avatarpath', $config['avatarpath'])
                 ->setVar('allowgravatars', $config['allowgravatars'])
                 ->setVar('gravatarimage', $config['gravatarimage'])
                 ->setVar('default_authmodule', $config['default_authmodule'])
                 ->setVar('login_displayinactive', $config['login_displayinactive'])
                 ->setVar('login_displayverify', $config['login_displayverify'])
                 ->setVar('login_displayapproval', $config['login_displayapproval'])
                 ->setVar('chgemail_expiredays', $config['chgemail_expiredays'])
                 ->setVar('chgpass_expiredays', $config['chgpass_expiredays']);

            if (ModUtil::available('Legal')) {
                ModUtil::setVar('Legal', 'termsofuse', $config['termsofuse']);
                ModUtil::setVar('Legal', 'privacypolicy', $config['privacypolicy']);
            } else {
                ModUtil::setVar('Legal', 'termsofuse', false);
                ModUtil::setVar('Legal', 'privacypolicy', false);
            }

            // the module configuration has been updated successfuly
            LogUtil::registerStatus($this->__('Done! Saved module configuration.'));
        }

        return System::redirect(ModUtil::url('Users', 'admin', 'modifyConfig'));
    }

    /**
     * Show the form to choose a CSV file and import several users from this file.
     *
     * Available Post Parameters:
     * - confirmed  (int|bool) True if the user has confirmed the upload/import.
     * - importFile (array)    Structured information about the file to import, from <input type="file" name="fileFieldName" ... /> and stored in $_FILES['fileFieldName'].
     *                         See http://php.net/manual/en/features.file-upload.post-method.php .
     * - delimiter  (int)      A code indicating the type of delimiter found in the import file. 1 = comma, 2 = semicolon, 3 = colon.
     *
     * @param array $args All arguments passed to the function.
     *                    $args['confirmed'] (int|bool) True if the user has confirmed the upload/import. Used
     *                      as the default if $_POST['confirmed'] is not set. Allows this function to be called
     *                      internally, rather than as a result of a form post.
     *                    $args['importFile'] (array) Information about the file to import. Used as the default
     *                      if $_FILES['importFile'] is not set. Allows this function to be called internally,
     *                      rather than as a result of a form post.
     *                    $args['delimiter'] (int) A code indicating the delimiter used in the file. Used as the
     *                      default if $_POST['delimiter'] is not set. Allows this function to be called internally,
     *                      rather than as a result of a form post.
     *
     * @return redirect user to admin main page if success and show again the forn otherwise
     */
    public function import($args)
    {
        // security check
        if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_ADD)) {
            return LogUtil::registerPermissionError();
        }

        // get input values
        $confirmed     = FormUtil::getPassedValue('confirmed', (isset($args['confirmed']) ? $args['confirmed'] : null), 'POST');

        // set default parameters
        $minpass = $this->getVar('minpass');
        $defaultGroup = ModUtil::getVar('Groups', 'defaultgroup');

        if ($confirmed == 1) {
            // get other import values
            $importFile = FormUtil::getPassedValue('importFile', (isset($args['importFile']) ? $args['importFile'] : null), 'FILES');
            $delimiter = FormUtil::getPassedValue('delimiter', (isset($args['delimiter']) ? $args['delimiter'] : null), 'POST');
            $importResults = ModUtil::func('Users', 'admin', 'uploadImport',
                                       array('importFile' => $importFile,
                                             'delimiter' => $delimiter));
            if ($importResults == '') {
                // the users have been imported successfully
                LogUtil::registerStatus($this->__('Done! Users imported successfully.'));
                return System::redirect(ModUtil::url('Users', 'admin', 'main'));
            }
        }

        // shows the form
        $post_max_size = ini_get('post_max_size');
        // get default group
        $group = ModUtil::apiFunc('Groups','user','get', array('gid' => $defaultGroup));
        $defaultGroup = $defaultGroup . ' (' . $group['name'] . ')';

        $this->view->assign('importResults', $importResults)
                   ->assign('post_max_size', $post_max_size)
                   ->assign('minpass', $minpass)
                   ->assign('defaultGroup', $defaultGroup);

        return $this->view->fetch('users_admin_import.tpl');
    }

    /**
     * Show the form to export a CSV file of users.
     *
     * Available Post Parameters:
     * - confirmed       (int|bool) True if the user has confirmed the export.
     * - exportFile      (array)    Filename of the file to export (optional) (default=users.csv)
     * - delimiter       (int)      A code indicating the type of delimiter found in the export file. 1 = comma, 2 = semicolon, 3 = colon, 4 = tab.
     * - exportEmail     (int)      Flag to export email addresses, 1 for yes.
     * - exportTitles    (int)      Flag to export a title row, 1 for yes.
     * - exportLastLogin (int)      Flag to export the last login date/time, 1 for yes.
     * - exportRegDate   (int)      Flag to export the registration date/time, 1 for yes.
     *
     * @param array $args All arguments passed to the function.
     *
     * @return redirect user to the form if confirmed not 1, else export the csv file.
     */
    public function exporter($args)
    {
        // security check
        if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        // get input values
        $confirmed     = FormUtil::getPassedValue('confirmed', (isset($args['confirmed']) ? $args['confirmed'] : null), 'POST');

        if ($confirmed == 1) {
            // get other import values
            $exportFile = FormUtil::getPassedValue('exportFile', (isset($args['exportFile']) ? $args['exportFile'] : null), 'POST');
            $delimiter = FormUtil::getPassedValue('delimiter', (isset($args['delimiter']) ? $args['delimiter'] : null), 'POST');
            $email = FormUtil::getPassedValue('exportEmail', (isset($args['exportEmail']) ? $args['exportEmail'] : null), 'POST');
            $titles = FormUtil::getPassedValue('exportTitles', (isset($args['exportTitles']) ? $args['exportTitles'] : null), 'POST');
            $lastLogin = FormUtil::getPassedValue('exportLastLogin', (isset($args['exportLastLogin']) ? $args['exportLastLogin'] : null), 'POST');
            $regDate = FormUtil::getPassedValue('exportRegDate', (isset($args['exportRegDate']) ? $args['exportRegDate'] : null), 'POST');
            $groups = FormUtil::getPassedValue('exportGroups', (isset($args['exportGroups']) ? $args['exportGroups'] : null), 'POST');

            $email = (!isset($email) || $email !=='1') ? false : true;
            $titles = (!isset($titles) || $titles !== '1') ? false : true;
            $lastLogin = (!isset($lastLogin) || $lastLogin !=='1') ? false : true;
            $regDate = (!isset($regDate) || $regDate !== '1') ? false : true;
            $groups = (!isset($groups) || $groups !== '1') ? false : true;

            if (!isset($delimiter) || $delimiter == '') {
                $delimiter = 1;
            }
            switch ($delimiter) {
                case 1:
                    $delimiter = ",";
                    break;
                case 2:
                    $delimiter = ";";
                    break;
                case 3:
                    $delimiter = ":";
                    break;
                case 4:
                    $delimiter = chr(9);
            }
            if (!isset($exportFile) || $exportFile == '') {
                $exportFile = 'users.csv';
            }
            if (!strrpos($exportFile, '.csv')) {
                $exportFile .= '.csv';
            }

            $colnames = array();

            //get all user fields
            if (ModUtil::available('Profile')) {
                $userfields = ModUtil::apiFunc('Profile', 'user', 'getallactive');

                foreach ($userfields as $item) {
                    $colnames[] = $item['prop_attribute_name'];
                }
            }

            // title fields
            if ($titles == 1) {
                $titlerow = array('id', 'uname');

                //titles for optional data
                if ($email == 1) {
                    array_push($titlerow, 'email');
                }
                if ($regDate == 1) {
                    array_push($titlerow, 'user_regdate');
                }
                if ($lastLogin == 1) {
                    array_push($titlerow, 'lastlogin');
                }
                if ($groups == 1) {
                    array_push($titlerow, 'groups');
                }

                array_merge($titlerow, $colnames);
            } else {
                $titlerow = array();
            }

            //get all users
            $users = ModUtil::apiFunc('Users', 'user', 'getAll');

            // get all groups
            $allgroups = ModUtil::apiFunc('Groups', 'user', 'getall');
            $groupnames = array();
            foreach ($allgroups as $groupitem) {
                $groupnames[$groupitem['gid']] = $groupitem['name'];
            }

            // data for csv
            $datarows = array();

            //loop every user gettin user id and username and all user fields and push onto result array.
            foreach ($users as $user) {
                $uservars = UserUtil::getVars($user['uid']);

                $result = array();

                array_push($result, $uservars['uid'], $uservars['uname']);

                //checks for optional data
                if ($email == 1) {
                    array_push($result, $uservars['email']);
                }
                if ($regDate == 1) {
                    array_push($result, $uservars['user_regdate']);
                }
                if ($lastLogin == 1) {
                    array_push($result, $uservars['lastlogin']);
                }

                if ($groups == 1) {
                    $usergroups = ModUtil::apiFunc('Groups', 'user', 'getusergroups',
                                            array('uid'   => $uservars['uid'],
                                                  'clean' => true));

                    $groupstring = "";

                    foreach ($usergroups as $group) {
                        $groupstring .= $groupnames[$group] . chr(124);
                    }

                    $groupstring = rtrim($groupstring, chr(124));


                    array_push($result, $groupstring);
                }

                foreach ($colnames as $colname) {
                    array_push($result, $uservars['__ATTRIBUTES__'][$colname]);
                }

                array_push($datarows, $result);
            }

            //export the csv file
            FileUtil::exportCSV($datarows, $titlerow, $delimiter, '"', $exportFile);
        }

        if (SecurityUtil::checkPermission('Groups::', '::', ACCESS_READ)) {
            $this->view->assign('groups', '1');
        }

        return $this->view->fetch('users_admin_export.tpl');
    }

    /**
     * Import several users from a CSV file. Checks needed values and format.
     *
     * Available Parameters:
     * - importFile (array) Structured information about the file to import, from <input type="file" name="fileFieldName" ... /> and stored in $_FILES['fileFieldName'].
     *                        See http://php.net/manual/en/features.file-upload.post-method.php .
     * - delimiter  (int)   A code indicating the type of delimiter found in the import file. 1 = comma, 2 = semicolon, 3 = colon.
     *
     * @param array $args All arguments passed to the function.
     *                    $args['importFile'] (array) Information about the file to import. Used as the default
     *                      if $_FILES['importFile'] is not set. Allows this function to be called internally,
     *                      rather than as a result of a form post.
     *                    $args['delimiter'] (int) A code indicating the delimiter used in the file. Used as the
     *                      default if $_POST['delimiter'] is not set. Allows this function to be called internally,
     *                      rather than as a result of a form post.
     *
     * @return a empty message if success or an error message otherwise
     */
    public function uploadImport($args)
    {
        // security check
        if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_ADD)) {
            return LogUtil::registerPermissionError();
        }

        // get import values
        $importFile = FormUtil::getPassedValue('importFile', (isset($args['importFile']) ? $args['importFile'] : null), 'FILES');
        $delimiter = FormUtil::getPassedValue('delimiter', (isset($args['delimiter']) ? $args['delimiter'] : null), 'POST');

        // get needed values
        $is_admin = (SecurityUtil::checkPermission('Users::', '::', ACCESS_ADMIN)) ? true : false;
        $minpass = $this->getVar('minpass');
        $defaultGroup = ModUtil::getVar('Groups', 'defaultgroup'); // Create output object;
        // calcs $pregcondition needed to verify illegal usernames
        $reg_illegalusername = $this->getVar('reg_Illegalusername');
        $pregcondition = '';
        if (!empty($reg_illegalusername)) {
            $usernames = explode(" ", $reg_illegalusername);
            $count = count($usernames);
            $pregcondition = "/((";
            for ($i = 0; $i < $count; $i++) {
                if ($i != $count-1) {
                    $pregcondition .= $usernames[$i] . ")|(";
                } else {
                    $pregcondition .= $usernames[$i] . "))/iAD";
                }
            }
        }

        // get available groups
        $allGroups = ModUtil::apiFunc('Groups','user','getall');

        // create an array with the groups identities where the user can add other users
        $allGroupsArray = array();
        foreach ($allGroups as $group) {
            if (SecurityUtil::checkPermission('Groups::', $group['gid'] . '::', ACCESS_EDIT)) {
                $allGroupsArray[] = $group['gid'];
            }
        }

        // check if the user's email must be unique
        $reg_uniemail = $this->getVar('reg_uniemail');

        // get the CSV delimiter
        switch ($delimiter) {
            case 1:
                $delimiterChar = ",";
                break;
            case 2:
                $delimiterChar = ";";
                break;
            case 3:
                $delimiterChar = ":";
                break;
        }

        // check that the user have selected a file
        $fileName = $importFile['name'];
        if ($fileName == '') {
            return $this->__("Error! You have not chosen any file.");
        }

        // check if user have selected a correct file
        if (FileUtil::getExtension($fileName) != 'csv') {
            return $this->__("Error! The file extension is incorrect. The only allowed extension is csv.");
        }

        // read the choosen file
        if (!$lines = file($importFile['tmp_name'])) {
            return $this->__("Error! It has not been possible to read the import file.");
        }
        $expectedFields = array('uname', 'pass', 'email', 'activated', 'sendmail', 'groups');
        $counter = 0;
        $importValues = array();
        // read the lines and create an array with the values. Check if the values passed are correct and set the default values if it is necessary
        foreach ($lines as $line_num => $line) {
            $line = str_replace('"', '', trim($line));
            if ($counter == 0) {
                // check the fields defined in the first row
                $firstLineArray = explode($delimiterChar, $line);
                foreach ($firstLineArray as $field) {
                    if (!in_array(trim($field), $expectedFields)) {
                        return $this->__f("Error! The import file does not have the expected field %s in the first row. Please check your import file.", array($field));
                    }
                }
                $counter++;
                continue;
            }
            // get and check the second and following lines
            $lineArray = array();
            $lineArray = DataUtil::formatForOS(explode($delimiterChar, $line));

            // check if the line have all the needed values
            if (count($lineArray) != count($firstLineArray)) {
                return $this->__f('Error! The number of parameters in line %s is not correct. Please check your import file.', $counter);
            }
            $importValues[] = array_combine($firstLineArray, $lineArray);

            // check all the obtained values
            // check user name
            $uname = trim($importValues[$counter - 1]['uname']);
            if ($uname == '' || strlen($uname) > 25) {
                return $this->__f('Sorry! The user name is not valid in line %s. The user name is mandatory and the maximum length is 25 characters. Please check your import file.',
                    $counter);
            }

            // check if it is a valid user name
            // admins are allowed to add any usernames, even those defined as being illegal
            if (!$is_admin && $pregcondition != '') {
                // check for illegal usernames
                if (preg_match($pregcondition, $uname)) {
                    return $this->__f('Sorry! The user name %1$s is reserved and cannot be registered in line %2$s. Please check your import file.', array($uname, $counter));
                }
            }

            // check if the user name is valid because spaces or invalid characters
            if (preg_match("/[[:space:]]/", $uname) || !System::varValidate($uname, 'uname')) {
                return $this->__f('Sorry! The user name %1$s cannot contain spaces in line %2$s. Please check your import file.', array($uname, $counter));
            }

            // check if the user name is repeated
            if (in_array($uname, $usersArray)) {
                return $this->__f('Sorry! The user name %1$s is repeated in line %2$s, and it cannot be used twice for creating accounts. Please check your import file.',
                    array($uname, $counter));
            }
            $usersArray[] = $uname;

            // check password
            $pass = (string)trim($importValues[$counter - 1]['pass']);
            if ($pass == '') {
                return $this->__f('Sorry! You did not provide a password in line %s. Please check your import file.', $counter);
            }

            // check password lenght
            if (strlen($pass) <  $minpass) {
                return $this->__f('Sorry! The password must be at least %1$s characters long in line %2$s. Please check your import file.', array($minpass, $counter));
            }

            // check email
            $email = trim($importValues[$counter - 1]['email']);
            if ($email == '') {
                return $this->__f('Sorry! You did not provide a email in line %s. Please check your import file.', $counter);
            }

            // check email format
            if (!System::varValidate($email, 'email')) {
                return $this->__f('Sorry! The e-mail address you entered was incorrectly formatted or is unacceptable for other reasons in line %s. Please check your import file.', $counter);
            }

            // check if email is unique only if it is necessary
            if ($reg_uniemail == 1) {
                if (in_array($email, $emailsArray)) {
                    return $this->__f('Sorry! The %1$s e-mail address is repeated in line %2$s, and it cannot be used twice for creating accounts. Please check your import file.',
                        array($email, $counter));
                }
                $emailsArray[] = $email;
            }

            $activated = trim($importValues[$counter - 1]['activated']);
            // check activation value and set 1 as default if it is not defined or it is incorrect
            if (!$activated || ($activated != UserUtil::ACTIVATED_INACTIVE &&
                $activated != UserUtil::ACTIVATED_ACTIVE &&
                $activated != UserUtil::ACTIVATED_INACTIVE_TOUPP &&
                $activated != UserUtil::ACTIVATED_INACTIVE_PWD &&
                $activated != UserUtil::ACTIVATED_INACTIVE_PWD_TOUPP)) {
                    $importValues[$counter - 1]['activated'] = UserUtil::ACTIVATED_ACTIVE;
            }

            // check send mail and set 0 as default if it is not defined
            $importValues[$counter - 1]['sendMail'] = ($importValues[$counter - 1]['sendMail'] != 0 || $importValues[$counter - 1]['sendMail'] == '') ? 1 : 0;

            // check groups and set defaultGroup as default if there are not groups defined
            $groups = trim($importValues[$counter - 1]['groups']);
            if ($groups == '') {
                $importValues[$counter - 1]['groups'] = $defaultGroup;
            } else {
                $groupsArray = explode('|', $groups);
                foreach ($groupsArray as $group) {
                    if (!in_array($group, $allGroupsArray)) {
                        return $this->__f('Sorry! The identity of the group %1$s is not not valid in line %2$s. Perhaps it do not exist. Please check your import file.', array($group, $counter));
                    }
                }
            }
            $counter++;
        }

        // seams that the import file is formated correctly and its values are valid
        if (empty($importValues)) {
            return $this->__("Error! The import file does not have values.");
        }

        // check if users exists in database
        $usersInDB = ModUtil::apiFunc('Users', 'admin', 'checkMultipleExistence',
                                      array('valuesArray' => $usersArray,
                                            'key' => 'uname'));
        if ($usersInDB === false) {
            return $this->__("Error! Trying to read the existing user names in database.");
        } else {
            if (count($usersInDB) > 0) {
                return $this->__("Sorry! One or more user names really exist in database. The user names must be uniques.");
            }
        }

        // check if emails exists in data base in case the email have to be unique
        if ($reg_uniemail == 1) {
            $emailsInDB = ModUtil::apiFunc('Users', 'admin', 'checkMultipleExistence',
                                          array('valuesArray' => $emailsArray,
                                                'key' => 'email'));
            if ($emailsInDB === false) {
                return $this->__("Error! Trying to read the existing users' email addressess in database.");
            } else {
                if (count($emailsInDB) > 0) {
                    return $this->__("Sorry! One or more users' email addresses exist in the database. Each user's e-mail address must be unique.");
                }
            }
        }

        // seems that the values in import file are ready. Procceed creating users
        if (!ModUtil::apiFunc('Users', 'admin', 'createImport', array('importValues' => $importValues))) {
            return $this->__("Error! The creation of users has failed.");
        }

        return '';
    }
}
