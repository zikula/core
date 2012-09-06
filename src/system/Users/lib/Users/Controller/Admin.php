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
class Users_Controller_Admin extends Zikula_AbstractController
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
        // Disable caching by default.
        $this->view->setCaching(Zikula_View::CACHE_DISABLED);
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
     * Redirects users to the "view" page.
     *
     * @return string HTML string containing the rendered view template.
     */
    public function main()
    {
        // Security check will be done in view()
        $this->redirect(ModUtil::url($this->name, 'admin', 'view'));
    }

    /**
     * Shows all items and lists the administration options.
     *
     * Parameters passed via GET:
     * --------------------------
     * numeric startnum The ordinal number at which to start displaying user records.
     * string  letter   The first letter of the user names to display.
     * string  sort     The field on which to sort the data.
     * string  sortdir  Either 'ASC' for an ascending sort (a to z) or 'DESC' for a descending sort (z to a).
     *
     * Parameters passed via POST:
     * ---------------------------
     * None.
     *
     * Parameters passed via SESSION:
     * ------------------------------
     * None.
     *
     * @return string HTML string containing the rendered template.
     *
     * @throws Zikula_Exception_Forbidden Thrown if the current user does not have moderate access, or if the method of accessing this function is improper.
     */
    public function view()
    {
        if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_MODERATE)) {
            throw new Zikula_Exception_Forbidden();
        }

        // we need this value multiple times, so we keep it
        $itemsPerPage = $this->getVar(Users_Constant::MODVAR_ITEMS_PER_PAGE);

        // Get parameters from whatever input we need.
        if (!$this->request->isGet()) {
            throw new Zikula_Exception_Forbidden();
        }

        $sort = $this->request->query->get('sort', isset($args['sort']) ? $args['sort'] : 'uname');
        $sortDirection = $this->request->query->get('sortdir', isset($args['sortdir']) ? $args['sortdir'] : 'ASC');
        $sortArgs = array(
            $sort => $sortDirection,
        );
        if (!isset($sortArgs['uname'])) {
            $sortArgs['uname'] = 'ASC';
        }

        $getAllArgs = array(
            'startnum'  => $this->request->query->get('startnum', isset($args['startnum']) ? $args['startnum'] : null),
            'numitems'  => $itemsPerPage,
            'letter'    => $this->request->query->get('letter', isset($args['letter']) ? $args['letter'] : null),
            'sort'      => $sortArgs,
        );

        // Get all users as specified by the arguments.
        $userList = ModUtil::apiFunc($this->name, 'user', 'getAll', $getAllArgs);

        // Get all groups
        $groups = ModUtil::apiFunc('Groups', 'user', 'getall');

        // check what groups can access the user
        $userGroupsAccess = array();
        $groupsArray = array();
        $canSeeGroups = !empty($groups);

        foreach ($groups as $group) {
            $userGroupsAccess[$group['gid']] = array('gid' => $group['gid']);

            // rewrite the groups array with the group id as key and the group name as value
            $groupsArray[$group['gid']] = array('name' => DataUtil::formatForDisplayHTML($group['name']));
        }

        // Get the current user's uid
        $currentUid = UserUtil::getVar('uid');

        // Determine the available options
        $currentUserHasReadAccess = SecurityUtil::checkPermission($this->name . '::', 'ANY', ACCESS_READ);
        $currentUserHasModerateAccess = SecurityUtil::checkPermission($this->name . '::', 'ANY', ACCESS_MODERATE);
        $currentUserHasEditAccess = SecurityUtil::checkPermission($this->name . '::', 'ANY', ACCESS_EDIT);
        $currentUserHasDeleteAccess = SecurityUtil::checkPermission($this->name . '::', 'ANY', ACCESS_DELETE);
        $availableOptions = array(
            'lostUsername'  => $currentUserHasModerateAccess,
            'lostPassword'  => $currentUserHasModerateAccess,
            'toggleForcedPasswordChange' => $currentUserHasEditAccess,
            'modify'        => $currentUserHasEditAccess,
            'deleteUsers'   => $currentUserHasDeleteAccess,
        );

        // Loop through each returned item adding in the options that the user has over
        // each item based on the permissions the user has.
        foreach ($userList as $key => $userObj) {
            $isCurrentUser      = ($userObj['uid'] == $currentUid);
            $isGuestAccount     = ($userObj['uid'] == 1);
            $isAdminAccount     = ($userObj['uid'] == 2);
            $hasUsersPassword   = (!empty($userObj['pass']) && ($userObj['pass'] != Users_Constant::PWD_NO_USERS_AUTHENTICATION));
            $currentUserHasReadAccess       = !$isGuestAccount && SecurityUtil::checkPermission($this->name . '::', "{$userObj['uname']}::{$userObj['uid']}", ACCESS_READ);
            $currentUserHasModerateAccess   = !$isGuestAccount && SecurityUtil::checkPermission($this->name . '::', "{$userObj['uname']}::{$userObj['uid']}", ACCESS_MODERATE);
            $currentUserHasEditAccess       = !$isGuestAccount && SecurityUtil::checkPermission($this->name . '::', "{$userObj['uname']}::{$userObj['uid']}", ACCESS_EDIT);
            $currentUserHasDeleteAccess     = !$isGuestAccount && !$isAdminAccount && !$isCurrentUser && SecurityUtil::checkPermission($this->name . '::', "{$userObj['uname']}::{$userObj['uid']}", ACCESS_DELETE);

            $userList[$key]['options'] = array(
                'lostUsername'              => $currentUserHasModerateAccess,
                'lostPassword'              => $currentUserHasModerateAccess,
                'toggleForcedPasswordChange'=> $hasUsersPassword && $currentUserHasEditAccess,
                'modify'                    => $currentUserHasEditAccess,
                'deleteUsers'               => $currentUserHasDeleteAccess,
            );

            if ($isGuestAccount) {
                $userList[$key]['userGroupsView'] = array();
            } else {
                // get user groups
                $userGroups = ModUtil::apiFunc('Groups', 'user', 'getusergroups', array(
                    'uid'   => $userObj['uid'],
                    'clean' => 1
                ));
                // we need an associative array by the key to compare with the groups that the user can see
                $userGroupsByKey = array();
                foreach ($userGroups as $gid) {
                    $userGroupsByKey[$gid] = array('gid' => $gid);
                }
                $userList[$key]['userGroupsView'] = array_intersect_key($userGroupsAccess, $userGroupsByKey);
            }
            // format the dates
            if (!empty($userObj['user_regdate']) && ($userObj['user_regdate'] != '0000-00-00 00:00:00') && ($userObj['user_regdate'] != '1970-01-01 00:00:00')) {
                $userList[$key]['user_regdate'] = DateUtil::formatDatetime($userObj['user_regdate'], $this->__('%m-%d-%Y'));
            } else {
                $userList[$key]['user_regdate'] = '---';
            }

            if (!empty($userObj['lastlogin']) && ($userObj['lastlogin'] != '0000-00-00 00:00:00') && ($userObj['lastlogin'] != '1970-01-01 00:00:00')) {
                $userList[$key]['lastlogin'] = DateUtil::formatDatetime($userObj['lastlogin'], $this->__('%m-%d-%Y'));
            } else {
                $userList[$key]['lastlogin'] = '---';
            }

            $userList[$key]['_Users_mustChangePassword'] = (isset($userObj['__ATTRIBUTES__']) && isset($userObj['__ATTRIBUTES__']['_Users_mustChangePassword']) && $userObj['__ATTRIBUTES__']['_Users_mustChangePassword']);
        }

        $pager = array(
            'numitems'     => ModUtil::apiFunc($this->name, 'user', 'countItems', array('letter' => $getAllArgs['letter'])),
            'itemsperpage' => $itemsPerPage,
        );

        // Assign the items to the template & return output
        return $this->view->assign('usersitems', $userList)
            ->assign('pager', $pager)
            ->assign('allGroups', $groupsArray)
            ->assign('canSeeGroups', $canSeeGroups)
            ->assign('sort', $sort)
            ->assign('sortdir', $sortDirection)
            ->assign('available_options', $availableOptions)
            ->fetch('users_admin_view.tpl');
    }

    /**
     * Add a new user to the system.
     *
     * Parameters passed via GET:
     * --------------------------
     * None.
     *
     * Parameters passed via POST:
     * ---------------------------
     * See the definition of {@link Users_Controller_FormData_NewUserForm}.
     *
     * Parameters passed via SESSION:
     * ------------------------------
     * None.
     *
     * @return string HTML string containing the rendered template.
     *
     * @throws Zikula_Exception_Forbidden Thrown if the current user does not have add access, or if the method of accessing this function is improper.
     */
    public function newUser()
    {
        // The user must have ADD access to submit a new user record.
        if (!SecurityUtil::checkPermission($this->name . '::', '::', ACCESS_ADD)) {
            throw new Zikula_Exception_Forbidden();
        }

        // When new user registration is disabled, the user must have ADMIN access instead of ADD access.
        if (!$this->getVar(Users_Constant::MODVAR_REGISTRATION_ENABLED, false) && !SecurityUtil::checkPermission($this->name . '::', '::', ACCESS_ADMIN)) {
            $registrationUnavailableReason = $this->getVar(Users_Constant::MODVAR_REGISTRATION_DISABLED_REASON, $this->__('Sorry! New user registration is currently disabled.'));
            $this->registerError($registrationUnavailableReason);
            // TODO - The home page typically does not display errors.
            $this->redirect(System::getHomepageUrl());
        }

        $proceedToForm = true;
        $formData = new Users_Controller_FormData_NewUserForm('users_newuser', $this->serviceManager);
        $errorFields = array();
        $errorMessages = array();

        if ($this->request->isPost()) {
            // Returning from a form POST operation. Process the input.
            $this->checkCsrfToken();

            $formData->setFromRequestCollection($this->request->request);

            $registrationArgs = array(
                'checkMode'         => 'new',
                'emailagain'        => $formData->getField('emailagain')->getData(),
                'setpass'           => (bool)$formData->getField('setpass')->getData(),
                'antispamanswer'    => '',
            );
            $registrationArgs['passagain'] = $registrationArgs['setpass'] ? $formData->getField('passagain')->getData() : '';

            $registrationInfo = array(
                'uname'         => $formData->getField('uname')->getData(),
                'pass'          => $registrationArgs['setpass'] ? $formData->getField('pass')->getData() : '',
                'passreminder'  => $registrationArgs['setpass'] ? $this->__('(Password provided by site administrator)') : '',
                'email'         => mb_strtolower($formData->getField('email')->getData()),
            );
            $registrationArgs['reginfo'] = $registrationInfo;

            $sendPass = $formData->getField('sendpass')->getData();

            if ($formData->isValid()) {
                $errorFields = ModUtil::apiFunc($this->name, 'registration', 'getRegistrationErrors', $registrationArgs);
            } else {
                $errorFields = $formData->getErrorMessages();
            }

            $event = new Zikula_Event('module.users.ui.validate_edit.new_user', $registrationInfo, array(), new Zikula_Hook_ValidationProviders());
            $validators = $this->eventManager->notify($event)->getData();

            $hook = new Zikula_ValidationHook('users.ui_hooks.user.validate_edit', $validators);
            $this->notifyHooks($hook);
            $validators = $hook->getValidators();

            if (empty($errorFields) && !$validators->hasErrors()) {
                // TODO - Future functionality to suppress e-mail notifications, see ticket #2351
                //$currentUserEmail = UserUtil::getVar('email');
                //$adminNotifyEmail = $this->getVar('reg_notifyemail', '');
                //$adminNotification = (strtolower($currentUserEmail) != strtolower($adminNotifyEmail));

                $registeredObj = ModUtil::apiFunc($this->name, 'registration', 'registerNewUser', array(
                    'reginfo'           => $registrationInfo,
                    'sendpass'          => $sendPass,
                    'usernotification'  => true,
                    'adminnotification' => true,
                ));

                if (isset($registeredObj) && $registeredObj) {
                    $event = new Zikula_Event('module.users.ui.process_edit.new_user', $registeredObj);
                    $this->eventManager->notify($event);

                    $hook = new Zikula_ProcessHook('users.ui_hooks.user.process_edit', $registeredObj['uid']);
                    $this->notifyHooks($hook);

                    if ($registeredObj['activated'] == Users_Constant::ACTIVATED_PENDING_REG) {
                        $this->registerStatus($this->__('Done! Created new registration application.'));
                    } elseif (isset($registeredObj['activated'])) {
                        $this->registerStatus($this->__('Done! Created new user account.'));
                    } else {
                        $this->registerError($this->__('Warning! New user information has been saved, however there may have been an issue saving it properly.'));
                    }

                    $proceedToForm = false;
                } else {
                    $this->registerError($this->__('Error! Could not create the new user account or registration application.'));
                }
            }
        } elseif (!$this->request->isGet()) {
            throw new Zikula_Exception_Forbidden();
        }

        if ($proceedToForm) {
            return $this->view->assign_by_ref('formData', $formData)
                    ->assign('mode', 'new')
                    ->assign('errorMessages', $errorMessages)
                    ->assign('errorFields', $errorFields)
                    ->fetch('users_admin_newuser.tpl');
        } else {
            $this->redirect(ModUtil::url($this->name, 'admin', 'view'));
        }
    }

    /**
     * Renders a user search form used by both the search operation and the mail users operation.
     *
     * @param string $callbackFunc Either 'search' or 'mailUsers', indicating which operation is calling this function.
     *
     * @return string The rendered output from the template, appropriate for the indicated operation.
     */
    protected function renderSearchForm($callbackFunc = 'search')
    {
        // get group items
        $groups = ModUtil::apiFunc('Groups', 'user', 'getAll');

        return $this->view->assign('groups', $groups)
                ->assign('callbackFunc', $callbackFunc)
                ->fetch('users_admin_search.tpl');
    }

    /**
     * Gathers the user input from a rendered search form, and also makes the appropriate hook calls.
     *
     * Parameters passed via GET:
     * --------------------------
     * None.
     *
     * Parameters passed via POST:
     * ---------------------------
     * string  uname         A fragment of a user name on which to search using an SQL LIKE clause. The user name will be
     *                              surrounded by wildcards.
     * integer ugroup        A group id in which to search (only users who are members of the specified group are returned).
     * string  email         A fragment of an e-mail address on which to search using an SQL LIKE clause. The e-mail address
     *                              will be surrounded by wildcards.
     * string  regdateafter  An SQL date-time (in the form '1970-01-01 00:00:00'); only user accounts with a registration date
     *                              after the date specified will be returned.
     * string  regdatebefore An SQL date-time (in the form '1970-01-01 00:00:00'); only user accounts with a registration date
     *                              before the date specified will be returned.
     * array   dynadata      An array of search values to be passed to the designated profile module. Only those user records
     *                              also satisfying the profile module's search of its dataare returned.
     *
     * Parameters passed via SESSION:
     * ------------------------------
     * None.
     *
     * @param string $callbackFunc Either 'search' or 'mailUsers', indicating which operation is calling this function.
     *
     * @return array|boolean An array of search results, which may be empty; false if the search was unsuccessful.
     */
    protected function getSearchResults($callbackFunc = 'search')
    {
        $findUsersArgs = array(
            'uname'         => $this->request->request->get('uname', null),
            'email'         => $this->request->request->get('email', null),
            'ugroup'        => $this->request->request->get('ugroup', null),
            'regdateafter'  => $this->request->request->get('regdateafter', null),
            'regdatebefore' => $this->request->request->get('regdatebefore', null),
        );

        if ($callbackFunc == 'mailUsers') {
              $processEditEvent = $this->eventManager->notify(new Zikula_Event('users.mailuserssearch.process_edit', null, array(), $findUsersArgs));
        } else {
            $processEditEvent = $this->eventManager->notify(new Zikula_Event('users.search.process_edit', null, array(), $findUsersArgs));
        }

        $findUsersArgs = $processEditEvent->getData();

        // call the api
        return ModUtil::apiFunc($this->name, 'admin', 'findUsers', $findUsersArgs);
    }

    /**
     * Displays a user account search form, or the search results from a post.
     *
     * Parameters passed via GET:
     * --------------------------
     * None.
     *
     * Parameters passed via POST:
     * ---------------------------
     * See the definition of {@link getSearchResults()}.
     *
     * Parameters passed via SESSION:
     * ------------------------------
     * None.
     *
     * @return string HTML string containing the rendered template.
     *
     * @throws Zikula_Exception_Forbidden Thrown if the current user does not have moderate access, or if the method of accessing this function is improper.
     */
    public function search()
    {
        if (!SecurityUtil::checkPermission($this->name . '::', '::', ACCESS_MODERATE)) {
            throw new Zikula_Exception_Forbidden();
        }

        if ($this->request->isPost()) {
            $this->checkCsrfToken();

            $usersList = $this->getSearchResults();

            if ($usersList) {
                $currentUid = UserUtil::getVar('uid');

                $actions = array();
                foreach ($usersList as $key => $user) {
                    $actions[$key] = array(
                        'modifyUrl'    => false,
                        'deleteUrl'    => false,
                    );
                    if ($user['uid'] != 1) {
                        if (SecurityUtil::checkPermission($this->name.'::', $user['uname'].'::'.$user['uid'], ACCESS_EDIT)) {
                            $actions[$key]['modifyUrl'] = ModUtil::url($this->name, 'admin', 'modify', array('userid' => $user['uid']));
                        }
                        if (($currentUid != $user['uid'])
                                && SecurityUtil::checkPermission($this->name.'::', $user['uname'].'::'.$user['uid'], ACCESS_DELETE)) {
                            $actions[$key]['deleteUrl'] = ModUtil::url($this->name, 'admin', 'deleteusers', array('userid' => $user['uid']));
                        }
                    }
                }
            } else {
                $this->registerError($this->__('Sorry! No matching users found.'));
            }
        }

        if (isset($usersList) && $usersList) {
            return $this->view->assign('items', $usersList)
                    ->assign('actions', $actions)
                    ->assign('deleteUsers', SecurityUtil::checkPermission($this->name . '::', '::', ACCESS_ADMIN))
                    ->fetch('users_admin_search_results.tpl');
        } elseif ($this->request->isGet() || ($this->request->isPost() && (!isset($usersList) || !$usersList))) {
            return $this->renderSearchForm('search');
        } else {
            throw new Zikula_Exception_Forbidden();
        }
    }

    /**
     * Search for users and then compose an email to them.
     *
     * Parameters passed via GET:
     * --------------------------
     * None.
     *
     * Parameters passed via POST:
     * ---------------------------
     * string formid The form id posting to this function. Used to determine the workflow.
     *
     * See also the definition of {@link getSearchResults()}.
     *
     * Parameters passed via SESSION:
     * ------------------------------
     * None.
     *
     * @return string HTML string containing the rendered template.
     *
     * @throws Zikula_Exception_Fatal Thrown if the function enters an unknown state.
     *
     * @throws Zikula_Exception_Forbidden Thrown if the current user does not have comment access, or if the method of accessing this function is improper.
     */
    public function mailUsers()
    {
        if (!SecurityUtil::checkPermission($this->name . '::MailUsers', '::', ACCESS_COMMENT)) {
            throw new Zikula_Exception_Forbidden();
        }

        if ($this->request->isPost()) {
            $this->checkCsrfToken();

            $formId = $this->request->request->get('formid', 'UNKNOWN');

            if ($formId == 'users_search') {
                $userList = $this->getSearchResults('mailUsers');

                if (!isset($userList) || !$userList) {
                    $this->registerError($this->__('Sorry! No matching users found.'));
                }
            } elseif ($formId == 'users_mailusers') {
                $uid = $this->request->request->get('userid', null);
                $sendmail = $this->request->request->get('sendmail', null);

                $mailSent = ModUtil::apiFunc($this->name, 'admin', 'sendmail', array(
                    'uid'       => $uid,
                    'sendmail'  => $sendmail,
                ));
            } else {
                throw new Zikula_Exception_Fatal($this->__f('An unknown form type was received by %1$s.', array('mailUsers')));
            }
        } elseif (!$this->request->isGet()) {
            throw new Zikula_Exception_Forbidden();
        }

        if ($this->request->isGet() || (($formId == 'users_search') && (!isset($userList) || !$userList)) || (($formId == 'users_mailusers') && !$mailSent)) {
            return $this->renderSearchForm('mailUsers');
        } elseif ($formId == 'users_search') {
            return $this->view->assign('items', $userList)
                ->assign('mailusers', SecurityUtil::checkPermission($this->name . '::MailUsers', '::', ACCESS_COMMENT))
                ->fetch('users_admin_mailusers.tpl');
        } elseif ($formId == 'users_mailusers') {
            $this->redirect(ModUtil::url($this->name, 'admin', 'main'));
        } else {
            throw new Zikula_Exception_Fatal($this->__f('The %1$s function has entered an unknown state.', array('mailUsers')));
        }
    }

    /**
     * Display a form to edit one user account, and process that edit request.
     *
     * Parameters passed via GET:
     * --------------------------
     * numeric userid The user id of the user to be modified.
     * string  uname  The user name of the user to be modified.
     *
     * Parameters passed via POST:
     * ---------------------------
     * array access_permissions An array used to modify a user's group membership.
     *
     * See also the definition of {@link Users_Controller_FormData_ModifyUserForm}.
     *
     * Parameters passed via SESSION:
     * ------------------------------
     * None.
     *
     * @return string HTML string containing the rendered template.
     *
     * @throws Zikula_Exception_Forbidden Thrown if the current user does not have edit access, or if the method of accessing this function is improper.
     */
    public function modify()
    {
        // security check for generic edit access
        if (!SecurityUtil::checkPermission('Users::', 'ANY', ACCESS_EDIT)) {
            throw new Zikula_Exception_Forbidden();
        }

        $proceedToForm = true;

        $formData = new Users_Controller_FormData_ModifyUserForm('users_modify', $this->serviceManager);

        if ($this->request->isPost()) {
            $this->checkCsrfToken();

            $formData->setFromRequestCollection($this->request->request);
            $accessPermissions = $this->request->request->get('access_permissions', null);
            $user = $formData->toUserArray(true);
            $originalUser = UserUtil::getVars($user['uid']);
            $userAttributes = isset($originalUser['__ATTRIBUTES__']) ? $originalUser['__ATTRIBUTES__'] : array();

            // security check for this record
            if (!SecurityUtil::checkPermission('Users::', "{$originalUser['uname']}::{$originalUser['uid']}", ACCESS_EDIT)) {
                throw new Zikula_Exception_Forbidden();
            }

            if ($formData->isValid()) {
                $registrationArgs = array(
                    'checkmode'         => 'modify',
                    'emailagain'        => $formData->getField('emailagain')->getData(),
                    'setpass'           => (bool)$formData->getField('setpass')->getData(),
                    'antispamanswer'    => '',
                );
                $registrationArgs['passagain'] = $registrationArgs['setpass'] ? $formData->getField('passagain')->getData() : '';

                $registrationArgs['reginfo'] = $user;

                $errorFields = ModUtil::apiFunc($this->name, 'registration', 'getRegistrationErrors', $registrationArgs);
            } else {
                $errorFields = $formData->getErrorMessages();
            }

            $event = new Zikula_Event('module.users.ui.validate_edit.modify_user', $user, array(), new Zikula_Hook_ValidationProviders());
            $validators = $this->eventManager->notify($event)->getData();

            $hook = new Zikula_ValidationHook('users.ui_hooks.user.validate_edit', $validators);
            $this->notifyHooks($hook);
            $validators = $hook->getValidators();

            if (!$errorFields && !$validators->hasErrors()) {
                if ($originalUser['uname'] != $user['uname']) {
                    // UserUtil::setVar does not allow uname to be changed.
                    // UserUtil::setVar('uname', $user['uname'], $originalUser['uid']);
                    $updatedUserObj = array(
                        'uid'   => $originalUser['uid'],
                        'uname' => $user['uname'],
                    );
                    DBUtil::updateObject($updatedUserObj, 'users', '', 'uid');
                    $eventArgs = array(
                        'action'    => 'setVar',
                        'field'     => 'uname',
                        'attribute' => null,
                    );
                    $eventData = array(
                        'old_value' => $originalUser['uname'],
                    );
                    $updateEvent = new Zikula_Event('user.account.update', $updatedUserObj, $eventArgs, $eventData);
                    $this->eventManager->notify($updateEvent);
                }
                if ($originalUser['email'] != $user['email']) {
                    UserUtil::setVar('email', $user['email'], $originalUser['uid']);
                }
                if ($originalUser['activated'] != $user['activated']) {
                    UserUtil::setVar('activated', $user['activated'], $originalUser['uid']);
                }
                if ($originalUser['theme'] != $user['theme']) {
                    UserUtil::setVar('theme', $user['theme'], $originalUser['uid']);
                }
                if ($formData->getField('setpass')->getData()) {
                    UserUtil::setPassword($user['pass'], $originalUser['uid']);
                    UserUtil::setVar('passreminder', $user['passreminder'], $originalUser['uid']);
                }

                $user = UserUtil::getVars($user['uid'], true);

                // TODO - This all needs to move to a Groups module hook.
                if (isset($accessPermissions)) {
                    // Fixing a high numitems to be sure to get all groups
                    $groups = ModUtil::apiFunc('Groups', 'user', 'getAll', array('numitems' => 10000));
                    $curUserGroupMembership = ModUtil::apiFunc('Groups', 'user', 'getUserGroups', array('uid' => $user['uid']));

                    foreach ($groups as $group) {
                        if (in_array($group['gid'], $accessPermissions)) {
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
                                    'uid' => $user['uid']
                                ));
                                $curUserGroupMembership[] = $group;
                            }
                        } else {
                            // We don't need to do a complex check, if the user is not in the group, the SQL will not return
                            // an error anyway.
                            ModUtil::apiFunc('Groups', 'admin', 'removeUser', array(
                                'gid' => $group['gid'],
                                'uid' => $user['uid']
                            ));
                        }
                    }
                }

                $event = new Zikula_Event('module.users.ui.process_edit.modify_user', $user);
                $this->eventManager->notify($event);

                $hook = new Zikula_ProcessHook('users.ui_hooks.user.process_edit', $user['uid']);
                $this->notifyHooks($hook);

                $this->registerStatus($this->__("Done! Saved user's account information."));
                $proceedToForm = false;
            }
        } elseif ($this->request->isGet()) {
            $uid    = $this->request->query->get('userid', null);
            $uname  = $this->request->query->get('uname', null);

            // check arguments
            if (is_null($uid) && is_null($uname)) {
                $this->registerError(LogUtil::getErrorMsgArgs());
                $proceedToForm = false;
            }

            // retreive userid from uname
            if (is_null($uid) && !empty($uname)) {
                $uid = UserUtil::getIdFromName($uname);
            }

            // warning for guest account
            if ($uid == 1) {
                $this->registerError($this->__("Error! You can't edit the guest account."));
                $proceedToForm = false;
            }

            // get the user vars
            $originalUser = UserUtil::getVars($uid);
            if ($originalUser == false) {
                $this->registerError($this->__('Sorry! No such user found.'));
                $proceedToForm = false;
            }
            $userAttributes = isset($originalUser['__ATTRIBUTES__']) ? $originalUser['__ATTRIBUTES__'] : array();

            $formData->setFromArray($originalUser);
            $formData->getField('emailagain')->setData($originalUser['email']);
            $formData->getField('pass')->setData('');

            $accessPermissions = array();
            $errorFields = array();
        } else {
            throw new Zikula_Exception_Forbidden();
        }

        if ($proceedToForm) {
            // security check for this record
            if (!SecurityUtil::checkPermission('Users::', "{$originalUser['uname']}::{$originalUser['uid']}", ACCESS_EDIT)) {
                throw new Zikula_Exception_Forbidden();
            }

            // groups
            $gidsUserMemberOf = array();
            $allGroups = ModUtil::apiFunc('Groups', 'user', 'getall');

            if (!empty($accessPermissions)) {
                $gidsUserMemberOf = $accessPermissions;
                $accessPermissions = array();
            } else {
                $groupsUserMemberOf = ModUtil::apiFunc('Groups', 'user', 'getusergroups', array('uid' => $originalUser['uid']));
                foreach ($groupsUserMemberOf as $user_group) {
                    $gidsUserMemberOf[] = $user_group['gid'];
                }
            }

            foreach ($allGroups as $group) {
                if (SecurityUtil::checkPermission('Groups::', "{$group['gid']}::", ACCESS_EDIT)) {
                    $accessPermissions[$group['gid']] = array();
                    $accessPermissions[$group['gid']]['name'] = $group['name'];

                    if (in_array($group['gid'], $gidsUserMemberOf) || in_array($group['gid'], $gidsUserMemberOf)) {
                        $accessPermissions[$group['gid']]['access'] = true;
                    } else {
                        $accessPermissions[$group['gid']]['access'] = false;
                    }
                }
            }

            if (!isset($userAttributes['realname'])) {
                $userAttributes['realname'] = '';
            }

            return $this->view->assign_by_ref('formData', $formData)
                ->assign('user_attributes', $userAttributes)
                ->assign('defaultGroupId', ModUtil::getVar('Groups', 'defaultgroup', 1))
                ->assign('primaryAdminGroupId', ModUtil::getVar('Groups', 'primaryadmingroup', 2))
                ->assign('accessPermissions', $accessPermissions)
                ->assign('errorFields', $errorFields)
                ->fetch('users_admin_modify.tpl');
        } else {
            $this->redirect(ModUtil::url($this->name, 'admin', 'view'));
        }
    }

    /**
     * Allows an administrator to send a user his user name via email.
     *
     * Parameters passed via GET:
     * --------------------------
     * numeric userid The user id of the user to be modified.
     *
     * Parameters passed via POST:
     * ---------------------------
     * numeric userid The user id of the user to be modified.
     *
     * Parameters passed via SESSION:
     * ------------------------------
     * None.
     *
     * @return void
     *
     * @throws Zikula_Exception_Forbidden Thrown if the current user does not have moderate access.
     *
     * @todo The link on the view page should be a mini form, and should post.
     *
     * @todo This should have a confirmation page.
     */
    public function lostUsername()
    {
        if ($this->request->isPost()) {
            $this->checkCsrfToken();
            $uid = $this->request->request->get('userid', null);
        } else {
            $this->checkCsrfToken($this->request->query->get('csrftoken'));
            $uid = $this->request->query->get('userid', null);
        }

        if (!isset($uid) || !is_numeric($uid) || ((int)$uid != $uid) || ($uid <= 1)) {
            $this->registerError(LogUtil::getErrorMsgArgs())
                ->redirect(ModUtil::url($this->name, 'admin', 'view'));
        }

        $user = UserUtil::getVars($uid);
        if (!$user) {
            $this->registerError($this->__('Sorry! Unable to retrieve information for that user id.'))
                    ->redirect(ModUtil::url($this->name, 'admin', 'view'));
        }

        if (!SecurityUtil::checkPermission('Users::', "{$user['uname']}::{$user['uid']}", ACCESS_MODERATE)) {
            throw new Zikula_Exception_Forbidden();
        }

        $userNameSent = ModUtil::apiFunc($this->name, 'user', 'mailUname', array(
            'idfield'       => 'uid',
            'id'            => $user['uid'],
            'adminRequest'  => true,
        ));

        if ($userNameSent) {
            $this->registerStatus($this->__f('Done! The user name for \'%s\' has been sent via e-mail.', $user['uname']))
                    ->redirect(ModUtil::url($this->name, 'admin', 'view'));
        } elseif (!$this->request->getSession()->hasMessages(Zikula_Session::MESSAGE_ERROR)) {
            $this->registerError($this->__f('Sorry! There was an unknown error while trying to send the user name for \'%s\'.', $user['uname']))
                    ->redirect(ModUtil::url($this->name, 'admin', 'view'));
        }
    }

    /**
     * Allows an administrator to send a user a password recovery verification code.
     *
     * Parameters passed via GET:
     * --------------------------
     * numeric userid The user id of the user to be modified.
     *
     * Parameters passed via POST:
     * ---------------------------
     * None.
     *
     * Parameters passed via SESSION:
     * ------------------------------
     * None.
     *
     * @return bool True on success and redirect; otherwise false.
     *
     * @throws Zikula_Exception_Forbidden Thrown if the current user does not have moderate access.
     *
     * @todo The link on the view page should be a mini form, and should post.
     *
     * @todo This should have a confirmation page.
     */
    public function lostPassword()
    {
        $this->checkCsrfToken($this->request->query->get('csrftoken'));

        $uid = $this->request->query->get('userid', null);

        if (!isset($uid) || !is_numeric($uid) || ((int)$uid != $uid) || ($uid <= 1)) {
            $this->registerError(LogUtil::getErrorMsgArgs())
                    ->redirect(ModUtil::url($this->name, 'admin', 'view'));
        }

        $user = UserUtil::getVars($uid);
        if (!$user) {
            $this->registerError($this->__('Sorry! Unable to retrieve information for that user id.'));

            return false;
        }

        if (!SecurityUtil::checkPermission('Users::', "{$user['uname']}::{$user['uid']}", ACCESS_MODERATE)) {
            throw new Zikula_Exception_Forbidden();
        }

        $confirmationCodeSent = ModUtil::apiFunc($this->name, 'user', 'mailConfirmationCode', array(
            'idfield'       => 'uid',
            'id'            => $user['uid'],
            'adminRequest'  => true,
        ));

        if ($confirmationCodeSent) {
            $this->registerStatus($this->__f('Done! The password recovery verification code for %s has been sent via e-mail.', $user['uname']));
        }

        $this->redirect(ModUtil::url($this->name, 'admin', 'view'));
    }

    /**
     * Display a form to confirm the deletion of one user, and then process the deletion.
     *
     * Parameters passed via GET:
     * --------------------------
     * numeric userid The user id of the user to be deleted.
     * string  uname  The user name of the user to be deleted.
     *
     * Parameters passed via POST:
     * ---------------------------
     * array   userid         The array of user ids of the users to be deleted.
     * boolean process_delete True to process the posted userid list, and delete the corresponding accounts; false or null to confirm first.
     *
     * Parameters passed via SESSION:
     * ------------------------------
     * None.
     *
     * @return string HTML string containing the rendered template.
     *
     * @throws Zikula_Exception_Forbidden Thrown if the current user does not have delete access, or if the method of accessing this function is improper.
     */
    public function deleteUsers()
    {
        // check permissions
        if (!SecurityUtil::checkPermission('Users::', 'ANY', ACCESS_DELETE)) {
            throw new Zikula_Exception_Forbidden();
        }

        $proceedToForm = false;
        $processDelete = false;

        if ($this->request->isPost()) {
            $userid = $this->request->request->get('userid', null);
            $processDelete = $this->request->request->get('process_delete', false);
            $proceedToForm = !$processDelete;
        } elseif ($this->request->isGet()) {
            $userid = $this->request->query->get('userid', null);
            $uname  = $this->request->query->get('uname', null);

            // retreive userid from uname
            if (empty($userid) && !empty($uname)) {
                $userid = UserUtil::getIdFromName($users);
            }

            $proceedToForm = true;
        } else {
            throw new Zikula_Exception_Forbidden();
        }

        if (empty($userid)) {
            $this->registerError($this->__('Sorry! No such user found.'));
            $proceedToForm = false;
        }

        if (!is_array($userid)) {
            $userid = array($userid);
        }

        $currentUser = UserUtil::getVar('uid');
        $users = array();
        foreach ($userid as $key => $uid) {
            if ($uid == 1) {
                $this->registerError($this->__("Error! You can't delete the guest account."));
                $proceedToForm = false;
                $processDelete = false;
            } elseif ($uid == 2) {
                $this->registerError($this->__("Error! You can't delete the primary administrator account."));
                $proceedToForm = false;
                $processDelete = false;
            } elseif ($uid == $currentUser) {
                $this->registerError($this->__("Error! You can't delete the account you are currently logged into."));
                $proceedToForm = false;
                $processDelete = false;
            }

            // get the user vars
            $users[$key] = UserUtil::getVars($uid);
            if ($users[$key] == false) {
                $this->registerError($this->__('Sorry! No such user found.'));
                $proceedToForm = false;
                $processDelete = false;
            }
        }

        if ($processDelete) {
            $valid = true;
            foreach ($userid as $uid) {
                $event = new Zikula_Event('module.users.ui.validate_delete', null, array('id' => $uid), new Zikula_Hook_ValidationProviders());
                $validators = $this->eventManager->notify($event)->getData();

                $hook = new Zikula_ValidationHook('users.ui_hooks.user.validate_delete', $validators);
                $this->notifyHooks($hook);
                $validators = $hook->getValidators();

                if ($validators->hasErrors()) {
                    $valid = false;
                }
            }

            $proceedToForm = false;
            if ($valid) {
                $deleted = ModUtil::apiFunc($this->name, 'admin', 'deleteUser', array('uid' => $userid));

                if ($deleted) {
                    foreach ($userid as $uid) {
                        $event = new Zikula_Event('module.users.ui.process_delete', null, array('id' => $uid));
                        $this->eventManager->notify($event);

                        $hook = new Zikula_ProcessHook('users.ui_hooks.user.process_delete', $uid);
                        $this->notifyHooks($hook);
                    }
                    $count = count($userid);
                    $this->registerStatus($this->_fn('Done! Deleted %1$d user account.', 'Done! Deleted %1$d user accounts.', $count, array($count)));
                }
            }
        }

        if ($proceedToForm) {
            return $this->view->assign('users', $users)
                ->fetch('users_admin_deleteusers.tpl');
        } else {
            $this->redirect(ModUtil::url($this->name, 'admin', 'view'));
        }
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
            $approvalOrder = $this->getVar('moderation_order', Users_Constant::APPROVAL_BEFORE);

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
                        'display'       =>                  ModUtil::url($this->name, 'admin', 'displayRegistration',   array('uid' => $reginfo['uid'])),
                        'modify'        =>                  ModUtil::url($this->name, 'admin', 'modifyRegistration',    array('uid' => $reginfo['uid'], 'restoreview' => $restoreView)),
                        'verify'        => $enableVerify ?  ModUtil::url($this->name, 'admin', 'verifyRegistration',    array('uid' => $reginfo['uid'], 'restoreview' => $restoreView)) : false,
                        'approve'       => $enableApprove ? ModUtil::url($this->name, 'admin', 'approveRegistration',   array('uid' => $reginfo['uid'])) : false,
                        'deny'          =>                  ModUtil::url($this->name, 'admin', 'denyRegistration',      array('uid' => $reginfo['uid'])),
                        'approveForce'  => $enableForced ?  ModUtil::url($this->name, 'admin', 'approveRegistration',   array('uid' => $reginfo['uid'], 'force' => true)) : false,
                    );
                }
            } elseif (SecurityUtil::checkPermission('Users::', '::', ACCESS_DELETE)) {
                $actions['count'] = 5;
                foreach ($reglist as $key => $reginfo) {
                    $enableVerify = !$reginfo['isverified'] && (($approvalOrder != Users_Constant::APPROVAL_BEFORE) || $reginfo['isapproved']);
                    $enableApprove = !$reginfo['isapproved'] && (($approvalOrder != Users_Constant::APPROVAL_AFTER) || $reginfo['isverified']);
                    $actions['list'][$reginfo['uid']] = array(
                        'display'       =>                  ModUtil::url($this->name, 'admin', 'displayRegistration',   array('uid' => $reginfo['uid'])),
                        'modify'        =>                  ModUtil::url($this->name, 'admin', 'modifyRegistration',    array('uid' => $reginfo['uid'], 'restoreview' => $restoreView)),
                        'verify'        => $enableVerify ?  ModUtil::url($this->name, 'admin', 'verifyRegistration',    array('uid' => $reginfo['uid'], 'restoreview' => $restoreView)) : false,
                        'approve'       => $enableApprove ? ModUtil::url($this->name, 'admin', 'approveRegistration',   array('uid' => $reginfo['uid'])) : false,
                        'deny'          =>                  ModUtil::url($this->name, 'admin', 'denyRegistration',      array('uid' => $reginfo['uid'])),
                    );
                }
            } elseif (SecurityUtil::checkPermission('Users::', '::', ACCESS_ADD)) {
                $actions['count'] = 4;
                foreach ($reglist as $key => $reginfo) {
                    $actionUrlArgs['uid'] = $reginfo['uid'];
                    $enableVerify = !$reginfo['isverified'] && (($approvalOrder != Users_Constant::APPROVAL_BEFORE) || $reginfo['isapproved']);
                    $enableApprove = !$reginfo['isapproved'] && (($approvalOrder != Users_Constant::APPROVAL_AFTER) || $reginfo['isverified']);
                    $actions['list'][$reginfo['uid']] = array(
                        'display'       =>                  ModUtil::url($this->name, 'admin', 'displayRegistration',   array('uid' => $reginfo['uid'])),
                        'modify'        =>                  ModUtil::url($this->name, 'admin', 'modifyRegistration',    array('uid' => $reginfo['uid'], 'restoreview' => $restoreView)),
                        'verify'        => $enableVerify ?  ModUtil::url($this->name, 'admin', 'verifyRegistration',    array('uid' => $reginfo['uid'], 'restoreview' => $restoreView)) : false,
                        'approve'       => $enableApprove ? ModUtil::url($this->name, 'admin', 'approveRegistration',   array('uid' => $reginfo['uid'])) : false,
                    );
                }
            } elseif (SecurityUtil::checkPermission('Users::', '::', ACCESS_EDIT)) {
                $actions['count'] = 3;
                foreach ($reglist as $key => $reginfo) {
                    $actionUrlArgs['uid'] = $reginfo['uid'];
                    $enableVerify = !$reginfo['isverified'] && (($approvalOrder != Users_Constant::APPROVAL_BEFORE) || $reginfo['isapproved']);
                    $actions['list'][$reginfo['uid']] = array(
                        'display'       =>                  ModUtil::url($this->name, 'admin', 'displayRegistration',   array('uid' => $reginfo['uid'])),
                        'modify'        =>                  ModUtil::url($this->name, 'admin', 'modifyRegistration',    array('uid' => $reginfo['uid'], 'restoreview' => $restoreView)),
                        'verify'        => $enableVerify ?  ModUtil::url($this->name, 'admin', 'verifyRegistration',    array('uid' => $reginfo['uid'], 'restoreview' => $restoreView)) : false,
                    );
                }
            } elseif (SecurityUtil::checkPermission('Users::', '::', ACCESS_MODERATE)) {
                $actions['count'] = 2;
                foreach ($reglist as $key => $reginfo) {
                    $actionUrlArgs['uid'] = $reginfo['uid'];
                    $enableVerify = !$reginfo['isverified'] && (($approvalOrder != Users_Constant::APPROVAL_BEFORE) || $reginfo['isapproved']);
                    $actions['list'][$reginfo['uid']] = array(
                        'display'       =>                  ModUtil::url($this->name, 'admin', 'displayRegistration',   array('uid' => $reginfo['uid'])),
                        'verify'        => $enableVerify ?  ModUtil::url($this->name, 'admin', 'verifyRegistration',    array('uid' => $reginfo['uid'], 'restoreview' => $restoreView)) : false,
                    );
                }
            }
        }

        return $actions;
    }

    /**
     * Shows all the registration requests (applications), and the options available to the current user.
     *
     * Parameters passed via GET:
     * --------------------------
     * string  restorview If returning from an action, and the previous view should be restored, then the value should be 'view';
     *                          otherwise not present.
     * integer startnum   The ordinal number of the first record to display, especially if using itemsperpage to limit
     *                          the number of records on a single page.
     *
     * Parameters passed via POST:
     * ---------------------------
     * numeric userid The user id of the user to be deleted.
     *
     * Parameters passed via SESSION:
     * ------------------------------
     * Namespace: Zikula_Users
     * Variable:  Users_Controller_Admin_viewRegistrations
     * Type:      array
     * Contents:  An array containing the parameters to restore the view configuration prior to executing an action.
     *
     * @return string HTML string containing the rendered template.
     *
     * @throws Zikula_Exception_Forbidden Thrown if the current user does not have moderate access.
     */
    public function viewRegistrations()
    {
        // security check
        if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_MODERATE)) {
            throw new Zikula_Exception_Forbidden();
        }

        $regCount = ModUtil::apiFunc($this->name, 'registration', 'countAll');
        $limitNumRows = $this->getVar(Users_Constant::MODVAR_ITEMS_PER_PAGE, Users_Constant::DEFAULT_ITEMS_PER_PAGE);
        if (!is_numeric($limitNumRows) || ((int)$limitNumRows != $limitNumRows) || (($limitNumRows < 1) && ($limitNumRows != -1))) {
            $limitNumRows = 25;
        }

        $backFromAction = $this->request->query->get('restoreview', false);

        if ($backFromAction) {
            $returnArgs = $this->request->getSession()->get('Users_Controller_Admin_viewRegistrations', array('startnum' => 1), 'Zikula_Users');
            $this->request->getSession()->del('Users_admin_viewRegistrations', 'Zikula_Users');

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
            $this->redirect(ModUtil::url($this->name, 'admin', 'viewRegistrations', $returnArgs));
        } else {
            $reset = false;

            $startNum = $this->request->query->get('startnum', 1);
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
                $this->redirect(ModUtil::url($this->name, 'admin', 'viewRegistrations', $returnArgs));
            }
        }

        $sessionVars = array(
            'startnum'  => ($limitOffset + 1),
        );
        $this->request->getSession()->set('Users_Controller_Admin_viewRegistrations', $sessionVars, 'Zikula_Users');

        $reglist = ModUtil::apiFunc($this->name, 'registration', 'getAll', array('limitoffset' => $limitOffset, 'limitnumrows' => $limitNumRows));

        if (($reglist === false) || !is_array($reglist)) {
            if (!$this->request->getSession()->hasMessages(Zikula_Session::MESSAGE_ERROR)) {
                $this->registerError($this->__('An error occurred while trying to retrieve the registration records.'));
            }
            $this->redirect(ModUtil::url($this->name, 'admin'), null, 500);
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

        return $this->view->assign('reglist', $reglist)
                          ->assign('actions', $actions)
                          ->assign('pager', $pager)
                          ->fetch('users_admin_viewregistrations.tpl');
    }

    /**
     * Displays the information on a single registration request.
     *
     * Parameters passed via GET:
     * --------------------------
     * numeric uid The id of the registration request (id) to retrieve and display.
     *
     * Parameters passed via POST:
     * ---------------------------
     * None.
     *
     * Parameters passed via SESSION:
     * ------------------------------
     * None.
     *
     * @return string HTML string containing the rendered template.
     *
     * @throws Zikula_Exception_Forbidden Thrown if the current user does not have moderate access, or if the method of accessing this function is improper.
     */
    public function displayRegistration()
    {
        if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_MODERATE)) {
            throw new Zikula_Exception_Forbidden();
        }

        // Get parameters from whatever input we need.
        // (Note that the name of the passed parameter is 'userid' but that it
        // is actually a registration application id.)
        if ($this->request->isGet()) {
            $uid = $this->request->query->get('uid', null);
        } else {
            throw new Zikula_Exception_Forbidden();
        }

        if (empty($uid) || !is_numeric($uid)) {
            $this->registerError(LogUtil::getErrorMsgArgs());
            $this->redirect(ModUtil::url($this->name, 'admin', 'viewRegistrations', array('return' => true)));
        }

        $reginfo = ModUtil::apiFunc($this->name, 'registration', 'get', array('uid' => $uid));
        if (!$reginfo) {
            // get application could fail (return false) because of a nonexistant
            // record, no permission to read an existing record, or a database error
            $this->registerError($this->__('Unable to retrieve registration record. '
                . 'The record with the specified id might not exist, or you might not have permission to access that record.'));

            return false;
        }

        // So expiration can be displayed
        $regExpireDays = $this->getVar('reg_expiredays', 0);
        if (!$reginfo['isverified'] && !empty($reginfo['verificationsent']) && ($regExpireDays > 0)) {
            try {
                $expiresUTC = new DateTime($reginfo['verificationsent'], new DateTimeZone('UTC'));
            } catch (Exception $e) {
                $expiresUTC = new DateTime(Users_Constant::EXPIRED, new DateTimeZone('UTC'));
            }
            $expiresUTC->modify("+{$regExpireDays} days");
            $reginfo['validuntil'] = DateUtil::formatDatetime($expiresUTC->format(Users_Constant::DATETIME_FORMAT),
                $this->__('%m-%d-%Y %H:%M'));
        }

        $actions = $this->getActionsForRegistrations(array($reginfo), 'display');

        return $this->view->assign('reginfo', $reginfo)
            ->assign('actions', $actions)
            ->fetch('users_admin_displayregistration.tpl');
    }

    /**
     * Display a form to edit one tegistration account.
     *
     * Parameters passed via GET:
     * --------------------------
     * numeric uid        The id of the registration request (id) to retrieve and display.
     * string  restorview To restore the main view to use the filtering options present prior to executing this function, then 'view',
     *                          otherwise not present.
     *
     * Parameters passed via POST:
     * ---------------------------
     * string restorview To restore the main view to use the filtering options present prior to executing this function, then 'view',
     *                          otherwise not present.
     *
     * See also the definition of {@link Users_Controller_FormData_ModifyRegistrationForm}.
     *
     * Parameters passed via SESSION:
     * ------------------------------
     * None.
     *
     * @return string|bool The rendered template; false on error.
     *
     * @throws Zikula_Exception_Forbidden Thrown if the current user does not have edit access, or if the method of accessing this function is improper.
     */
    public function modifyRegistration()
    {
        if (!SecurityUtil::checkPermission('Users::', 'ANY', ACCESS_EDIT)) {
            throw new Zikula_Exception_Forbidden();
        }

        $proceedToForm = true;

        $formData = new Users_Controller_FormData_ModifyRegistrationForm('users_modifyreg', $this->serviceManager);
        $errorFields = array();
        $errorMessages = array();

        if ($this->request->isPost()) {
            $this->checkCsrfToken();

            $formData->setFromRequestCollection($this->request->request);

            $restoreView = $this->request->request->get('restoreview', 'view');

            $registration = $formData->toUserArray(true);
            $originalRegistration = UserUtil::getVars($registration['uid'], false, 'uid', true);
            $userAttributes = isset($originalRegistration['__ATTRIBUTES__']) ? $originalRegistration['__ATTRIBUTES__'] : array();

            // security check for this record
            if (!SecurityUtil::checkPermission('Users::', "{$originalRegistration['uname']}::{$originalRegistration['uid']}", ACCESS_EDIT)) {
                throw new Zikula_Exception_Forbidden();
            }

            if ($formData->isValid()) {
                $registrationArgs = array(
                    'reginfo'           => $registration,
                    'checkmode'         => 'modify',
                    'emailagain'        => $formData->getField('emailagain')->getData(),
                    'setpass'           => false,
                    'passagain'         => '',
                    'antispamanswer'    => '',
                );
                $errorFields = ModUtil::apiFunc($this->name, 'registration', 'getRegistrationErrors', $registrationArgs);
            } else {
                $errorFields = $formData->getErrorMessages();
            }

            $event = new Zikula_Event('module.users.ui.validate_edit.modify_registration', $registration, array(), new Zikula_Hook_ValidationProviders());
            $validators = $this->eventManager->notify($event)->getData();

            $hook = new Zikula_ValidationHook('users.ui_hooks.registration.validate_edit', $validators);
            $this->notifyHooks($hook);
            $validators = $hook->getValidators();

            if (!$errorFields && !$validators->hasErrors()) {
                $emailUpdated = false;
                if ($originalRegistration['uname'] != $registration['uname']) {
                    // UserUtil::setVar does not allow uname to be changed.
                    // UserUtil::setVar('uname', $registration['uname'], $originalRegistration['uid']);
                    $updatedRegistrationObj = array(
                        'uid'   => $originalRegistration['uid'],
                        'uname' => $registration['uname'],
                    );
                    DBUtil::updateObject($updatedRegistrationObj, 'users', '', 'uid');
                    $eventArgs = array(
                        'action'    => 'setVar',
                        'field'     => 'uname',
                        'attribute' => null,
                    );
                    $eventData = array(
                        'old_value' => $originalRegistration['uname'],
                    );
                    $updateEvent = new Zikula_Event('user.registration.update', $updatedRegistrationObj, $eventArgs, $eventData);
                    $this->eventManager->notify($updateEvent);
                }
                if ($originalRegistration['theme'] != $registration['theme']) {
                    UserUtil::setVar('theme', $registration['theme'], $originalRegistration['uid']);
                }
                if ($originalRegistration['email'] != $registration['email']) {
                    UserUtil::setVar('email', $registration['email'], $originalRegistration['uid']);
                    $emailUpdated = true;
                }

                $registration = UserUtil::getVars($registration['uid'], true, 'uid', true);

                if ($emailUpdated) {
                    $approvalOrder = $this->getVar('moderation_order', Users_Constant::APPROVAL_BEFORE);
                    if (!$originalRegistration['isverified'] && (($approvalOrder != Users_Constant::APPROVAL_BEFORE) || $originalRegistration['isapproved'])) {
                        $verificationSent = ModUtil::apiFunc($this->name, 'registration', 'sendVerificationCode', array(
                            'reginfo'   => $registration,
                            'force'     => true,
                        ));
                    }
                }

                $event = new Zikula_Event('module.users.ui.process_edit.modify_registration', $registration);
                $this->eventManager->notify($event);

                $hook = new Zikula_ProcessHook('users.ui_hooks.registration.process_edit', $registration['uid']);
                $this->notifyHooks($hook);

                $this->registerStatus($this->__("Done! Saved user's account information."));
                $proceedToForm = false;
            }

        } elseif ($this->request->isGet()) {
            $uid = $this->request->query->get('uid', null);

            if (!is_int($uid)) {
                if (!is_numeric($uid) || ((string)((int)$uid) != $uid)) {
                    $this->registerError($this->__('Error! Invalid registration uid.'));
                    $this->redirect(ModUtil::url($this->name, 'admin', 'viewRegistrations', array('restoreview' => true)));
                }
            }

            $registration = ModUtil::apiFunc($this->name, 'registration', 'get', array('uid' => $uid));

            if (!$registration) {
                $this->registerError($this->__('Error! Unable to load registration record.'));
                $this->redirect(ModUtil::url($this->name, 'admin', 'viewRegistrations', array('restoreview' => true)));
            }
            $userAttributes = isset($registration['__ATTRIBUTES__']) ? $registration['__ATTRIBUTES__'] : array();

            $formData->setFromArray($registration);
            $formData->getField('emailagain')->setData($registration['email']);

            $restoreView = $this->request->query->get('restoreview', 'view');
        } else {
            throw new Zikula_Exception_Forbidden();
        }

        if ($proceedToForm) {
            // security check for this record
            if (!SecurityUtil::checkPermission('Users::', "{$registration['uname']}::{$registration['uid']}", ACCESS_EDIT)) {
                throw new Zikula_Exception_Forbidden();
            }

            $rendererArgs = array(
                'user_attributes'       => $userAttributes,
                'errorMessages'         => $errorMessages,
                'errorFields'           => $errorFields,
                'restoreview'           => $restoreView,
            );

            // Return the output that has been generated by this function
            return $this->view->assign_by_ref('formData', $formData)
                ->assign($rendererArgs)
                ->fetch('users_admin_modifyregistration.tpl');
        } else {
            if ($restoreView == 'view') {
                $this->redirect(ModUtil::url($this->name, 'admin', 'viewRegistrations', array('restoreview' => true)));
            } else {
                $this->redirect(ModUtil::url($this->name, 'admin', 'displayRegistration', array('uid' => $registration['uid'])));
            }
        }
    }

    /**
     * Renders and processes the admin's force-verify form.
     *
     * Renders and processes a form confirming an administrators desire to skip verification for
     * a registration record, approve it and add it to the users table.
     *
     * Parameters passed via GET:
     * --------------------------
     * numeric uid        The id of the registration request (id) to verify.
     * boolean force      True to force the registration to be verified.
     * string  restorview To restore the main view to use the filtering options present prior to executing this function, then 'view',
     *                          otherwise not present.
     *
     * Parameters passed via POST:
     * ---------------------------
     * numeric uid        The id of the registration request (uid) to verify.
     * boolean force      True to force the registration to be verified.
     * boolean confirmed  True to execute this function's action.
     * string  restorview To restore the main view to use the filtering options present prior to executing this function, then 'view',
     *                          otherwise not present.
     *
     * Parameters passed via SESSION:
     * ------------------------------
     * None.
     *
     * @return string|bool The rendered template; true on success; otherwise false.
     *
     * @throws Zikula_Exception_Forbidden Thrown if the current user does not have moderate access, or if the method of accessing this function is improper.
     */
    public function verifyRegistration()
    {
        if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_MODERATE)) {
            throw new Zikula_Exception_Forbidden();
        }

        if ($this->request->isGet()) {
            $uid = $this->request->query->get('uid', null);
            $forceVerification = $this->currentUserIsAdmin() && $this->request->query->get('force', false);
            $restoreView = $this->request->query->get('restoreview', 'view');
            $confirmed = false;
        } elseif ($this->request->isPost()) {
            $this->checkCsrfToken();
            $uid = $this->request->request->get('uid', null);
            $forceVerification = $this->currentUserIsAdmin() && $this->request->request->get('force', false);
            $restoreView = $this->request->request->get('restoreview', 'view');
            $confirmed = $this->request->request->get('confirmed', false);
        } else {
            throw new Zikula_Exception_Forbidden();
        }

        if (!isset($uid) || !is_numeric($uid) || ((int)$uid != $uid)) {
            $this->registerError(LogUtil::getErrorMsgArgs());

            return false;
        }

        // Got just a uid.
        $reginfo = ModUtil::apiFunc($this->name, 'registration', 'get', array('uid' => $uid));
        if (!$reginfo) {
            $this->registerError($this->__f('Error! Unable to retrieve registration record with uid \'%1$s\'', $uid));

            return false;
        }

        if ($restoreView == 'display') {
            $cancelUrl = ModUtil::url($this->name, 'admin', 'displayRegistration', array('uid' => $reginfo['uid']));
        } else {
            $cancelUrl = ModUtil::url($this->name, 'admin', 'viewRegistrations', array('restoreview' => true));
        }

        $approvalOrder = $this->getVar('moderation_order', Users_Constant::APPROVAL_BEFORE);

        if ($reginfo['isverified']) {
            $this->registerError($this->__f('Error! A verification code cannot be sent for the registration record for \'%1$s\'. It is already verified.', $reginfo['uname']));
            $this->redirect($cancelUrl);
        } elseif (!$forceVerification && ($approvalOrder == Users_Constant::APPROVAL_BEFORE) && !$reginfo['isapproved']) {
            $this->registerError($this->__f('Error! A verification code cannot be sent for the registration record for \'%1$s\'. It must first be approved.', $reginfo['uname']));
            $this->redirect($cancelUrl);
        }

        if (!$confirmed) {
            // So expiration can be displayed
            $regExpireDays = $this->getVar('reg_expiredays', 0);
            if (!$reginfo['isverified'] && $reginfo['verificationsent'] && ($regExpireDays > 0)) {
                try {
                    $expiresUTC = new DateTime($reginfo['verificationsent'], new DateTimeZone('UTC'));
                } catch (Exception $e) {
                    $expiresUTC = new DateTime(Users_Constant::EXPIRED, new DateTimeZone('UTC'));
                }
                $expiresUTC->modify("+{$regExpireDays} days");
                $reginfo['validuntil'] = DateUtil::formatDatetime($expiresUTC->format(Users_Constant::DATETIME_FORMAT),
                    $this->__('%m-%d-%Y %H:%M'));
            }

            return $this->view->assign('reginfo', $reginfo)
                              ->assign('restoreview', $restoreView)
                              ->assign('force', $forceVerification)
                              ->assign('cancelurl', $cancelUrl)
                              ->fetch('users_admin_verifyregistration.tpl');

        } else {
            $verificationSent = ModUtil::apiFunc($this->name, 'registration', 'sendVerificationCode', array(
                'reginfo'   => $reginfo,
                'force'     => $forceVerification,
            ));

            if (!$verificationSent) {
                $this->registerError($this->__f('Sorry! There was a problem sending a verification code to \'%1$s\'.', $reginfo['uname']));
                $this->redirect($cancelUrl);
            } else {
                $this->registerStatus($this->__f('Done! Verification code sent to \'%1$s\'.', $reginfo['uname']));
                $this->redirect($cancelUrl);
            }
        }
    }

    /**
     * Renders and processes a form confirming an administrators desire to approve a registration.
     *
     * If the registration record is also verified (or verification is not needed) a users table
     * record is created.
     *
     * Parameters passed via GET:
     * --------------------------
     * numeric uid        The id of the registration request (id) to approve.
     * boolean force      True to force the registration to be approved.
     * string  restorview To restore the main view to use the filtering options present prior to executing this function, then 'view',
     *                          otherwise not present.
     *
     * Parameters passed via POST:
     * ---------------------------
     * numeric uid        The id of the registration request (uid) to approve.
     * boolean force      True to force the registration to be approved.
     * string  restorview To restore the main view to use the filtering options present prior to executing this function, then 'view',
     *                          otherwise not present.
     *
     * Parameters passed via SESSION:
     * ------------------------------
     * None.
     *
     * @return string|bool The rendered template; true on success; otherwise false.
     *
     * @throws Zikula_Exception_Forbidden Thrown if the current user does not have moderate access, or if the method of accessing this function is improper.
     */
    public function approveRegistration()
    {
        if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_MODERATE)) {
            throw new Zikula_Exception_Forbidden();
        }

        if ($this->request->isGet()) {
            $uid = $this->request->query->get('uid', null);
            $forceVerification = $this->currentUserIsAdmin() && $this->request->query->get('force', false);
            $restoreView = $this->request->query->get('restoreview', 'view');
        } elseif ($this->request->isPost()) {
            $uid = $this->request->request->get('uid', null);
            $forceVerification = $this->currentUserIsAdmin() && $this->request->request->get('force', false);
            $restoreView = $this->request->request->get('restoreview', 'view');
        } else {
            throw new Zikula_Exception_Forbidden();
        }

        if (!isset($uid) || !is_numeric($uid) || ((int)$uid != $uid)) {
            $this->registerError(LogUtil::getErrorMsgArgs());

            return false;
        }

        // Got just an id.
        $reginfo = ModUtil::apiFunc($this->name, 'registration', 'get', array('uid' => $uid));
        if (!$reginfo) {
            $this->registerError($this->__f('Error! Unable to retrieve registration record with uid \'%1$s\'', $uid));

            return false;
        }

        if ($restoreView == 'display') {
            $cancelUrl = ModUtil::url($this->name, 'admin', 'displayRegistration', array('uid' => $reginfo['uid']));
        } else {
            $cancelUrl = ModUtil::url($this->name, 'admin', 'viewRegistrations', array('restoreview' => true));
        }

        $approvalOrder = $this->getVar('moderation_order', Users_Constant::APPROVAL_BEFORE);

        if ($reginfo['isapproved'] && !$forceVerification) {
            $this->registerError($this->__f('Warning! Nothing to do! The registration record with uid \'%1$s\' is already approved.', $reginfo['uid']));
            $this->redirect($cancelUrl);
        } elseif (!$forceVerification && ($approvalOrder == Users_Constant::APPROVAL_AFTER) && !$reginfo['isapproved']
                && !SecurityUtil::checkPermission('Users::', '::', ACCESS_ADMIN)) {
            $this->registerError($this->__f('Error! The registration record with uid \'%1$s\' cannot be approved. The registration\'s e-mail address must first be verified.', $reginfo['uid']));
            $this->redirect($cancelUrl);
        } elseif ($forceVerification && (!isset($reginfo['pass']) || empty($reginfo['pass']))) {
            $this->registerError($this->__f('Error! E-mail verification cannot be skipped for \'%1$s\'. The user must establish a password as part of the verification process.', $reginfo['uname']));
            $this->redirect($cancelUrl);
        }


        $confirmed = $this->request->query->get('confirmed', $this->request->request->get('confirmed', false));
        if (!$confirmed) {
            // Bad or no auth key, or bad or no confirmation, so display confirmation.

            // So expiration can be displayed
            $regExpireDays = $this->getVar('reg_expiredays', 0);
            if (!$reginfo['isverified'] && !empty($reginfo['verificationsent']) && ($regExpireDays > 0)) {
                try {
                    $expiresUTC = new DateTime($reginfo['verificationsent'], new DateTimeZone('UTC'));
                } catch (Exception $e) {
                    $expiresUTC = new DateTime(Users_Constant::EXPIRED, new DateTimeZone('UTC'));
                }
                $expiresUTC->modify("+{$regExpireDays} days");
                $reginfo['validuntil'] = DateUtil::formatDatetime($expiresUTC->format(Users_Constant::DATETIME_FORMAT),
                    $this->__('%m-%d-%Y %H:%M'));
            }

            return $this->view->assign('reginfo', $reginfo)
                              ->assign('restoreview', $restoreView)
                              ->assign('force', $forceVerification)
                              ->assign('cancelurl', $cancelUrl)
                              ->fetch('users_admin_approveregistration.tpl');

        } else {
            $this->checkCsrfToken();

            $approved = ModUtil::apiFunc($this->name, 'registration', 'approve', array(
                'reginfo'   => $reginfo,
                'force'     => $forceVerification,
            ));

            if (!$approved) {
                $this->registerError($this->__f('Sorry! There was a problem approving the registration for \'%1$s\'.', $reginfo['uname']));
                $this->redirect($cancelUrl);
            } else {
                if (isset($approved['uid'])) {
                    $this->registerStatus($this->__f('Done! The registration for \'%1$s\' has been approved and a new user account has been created.', $reginfo['uname']));
                    $this->redirect($cancelUrl);
                } else {
                    $this->registerStatus($this->__f('Done! The registration for \'%1$s\' has been approved and is awaiting e-mail verification.', $reginfo['uname']));
                    $this->redirect($cancelUrl);
                }
            }
        }
    }

    /**
     * Render and process a form confirming the administrator's rejection of a registration.
     *
     * If the denial is confirmed, the registration is deleted from the database.
     *
     * Parameters passed via GET:
     * --------------------------
     * numeric uid        The id of the registration request (id) to deny.
     * string  restorview To restore the main view to use the filtering options present prior to executing this function, then 'view',
     *                          otherwise not present.
     *
     * Parameters passed via POST:
     * ---------------------------
     * numeric uid        The id of the registration request (uid) to deny.
     * boolean confirmed  True to execute this function's action.
     * boolean usernorify True to notify the user that his registration request was denied; otherwise false.
     * string  reason     The reason the registration request was denied, included in the notification.
     * string  restorview To restore the main view to use the filtering options present prior to executing this function, then 'view',
     *                          otherwise not present.
     *
     * Parameters passed via SESSION:
     * ------------------------------
     * None.
     *
     * @return string|bool The rendered template; true on success; otherwise false.
     *
     * @throws Zikula_Exception_Forbidden Thrown if the user does not have delete access, or if the method used to access this function is improper.
     */
    public function denyRegistration()
    {
        if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_DELETE)) {
            throw new Zikula_Exception_Forbidden();
        }

        if ($this->request->isGet()) {
            $uid = $this->request->query->get('uid', null);
            $restoreView = $this->request->query->get('restoreview', 'view');
            $confirmed = false;
        } elseif ($this->request->isPost()) {
            $this->checkCsrfToken();
            $uid = $this->request->request->get('uid', null);
            $restoreView = $this->request->request->get('restoreview', 'view');
            $sendNotification = $this->request->request->get('usernotify', false);
            $reason = $this->request->request->get('reason', '');
            $confirmed = $this->request->request->get('confirmed', false);
        } else {
            throw new Zikula_Exception_Forbidden();
        }

        if (!isset($uid) || !is_numeric($uid) || ((int)$uid != $uid)) {
            $this->registerError(LogUtil::getErrorMsgArgs());

            return false;
        }

        // Got just a uid.
        $reginfo = ModUtil::apiFunc($this->name, 'registration', 'get', array('uid' => $uid));
        if (!$reginfo) {
            $this->registerError($this->__f('Error! Unable to retrieve registration record with uid \'%1$s\'', $uid));

            return false;
        }

        if ($restoreView == 'display') {
            $cancelUrl = ModUtil::url($this->name, 'admin', 'displayRegistration', array('uid' => $reginfo['uid']));
        } else {
            $cancelUrl = ModUtil::url($this->name, 'admin', 'viewRegistrations', array('restoreview' => true));
        }


        if (!$confirmed) {
            // Bad or no auth key, or bad or no confirmation, so display confirmation.

            // So expiration can be displayed
            $regExpireDays = $this->getVar('reg_expiredays', 0);
            if (!$reginfo['isverified'] && !empty($reginfo['verificationsent']) && ($regExpireDays > 0)) {
                try {
                    $expiresUTC = new DateTime($reginfo['verificationsent'], new DateTimeZone('UTC'));
                } catch (Exception $e) {
                    $expiresUTC = new DateTime(Users_Constant::EXPIRED, new DateTimeZone('UTC'));
                }
                $expiresUTC->modify("+{$regExpireDays} days");
                $reginfo['validuntil'] = DateUtil::formatDatetime($expiresUTC->format(Users_Constant::DATETIME_FORMAT),
                    $this->__('%m-%d-%Y %H:%M'));
            }

            return $this->view->assign('reginfo', $reginfo)
                              ->assign('restoreview', $restoreView)
                              ->assign('cancelurl', $cancelUrl)
                              ->fetch('users_admin_denyregistration.tpl');

        } else {
            $denied = ModUtil::apiFunc($this->name, 'registration', 'remove', array(
                'reginfo'   => $reginfo,
            ));

            if (!$denied) {
                $this->registerError($this->__f('Sorry! There was a problem deleting the registration for \'%1$s\'.', $reginfo['uname']));
                $this->redirect($cancelUrl);
            } else {
                if ($sendNotification) {
                    $siteurl   = System::getBaseUrl();
                    $rendererArgs = array(
                        'sitename'  => System::getVar('sitename'),
                        'siteurl'   => substr($siteurl, 0, strlen($siteurl)-1),
                        'reginfo'   => $reginfo,
                        'reason'    => $reason,
                    );

                    $sent = ModUtil::apiFunc($this->name, 'user', 'sendNotification', array(
                        'toAddress'         => $reginfo['email'],
                        'notificationType'  => 'regdeny',
                        'templateArgs'      => $rendererArgs
                    ));
                }
                $this->registerStatus($this->__f('Done! The registration for \'%1$s\' has been denied and deleted.', $reginfo['uname']));
                $this->redirect($cancelUrl);
            }
        }
    }

    /**
     * Edit and update module configuration settings.
     *
     * Parameters passed via GET:
     * --------------------------
     * None.
     *
     * Parameters passed via POST:
     * ---------------------------
     * See the definition of {@link Users_Controller_FormData_ConfigForm}.
     *
     * Parameters passed via SESSION:
     * ------------------------------
     * None.
     *
     * @return string The rendered configuration settings template.
     *
     * @throws Zikula_Exception_Fatal     Thrown if the function is accessed improperly.
     * @throws Zikula_Exception_Forbidden Thrown if the current user does not have admin access.
     */
    public function config()
    {
        // Security check
        if (!(SecurityUtil::checkPermission($this->name . '::', '::', ACCESS_ADMIN))) {
            throw new Zikula_Exception_Forbidden();
        }

        $configData = new Users_Controller_FormData_ConfigForm('users_config', $this->serviceManager);
        $errorFields = array();

        if ($this->request->isPost()) {
            $this->checkCsrfToken();

            $modVars = $this->request->request;
            $configData->setFromRequestCollection($modVars);

            if ($configData->isValid()) {
                $modVars = $configData->toArray();
                $this->setVars($modVars);
                $this->registerStatus($this->__('Done! Users module settings have been saved.'));
                $event = new Zikula_Event('module.users.config.updated', null, array(), $modVars);
                $this->eventManager->notify($event);
            } else {
                $errorFields = $configData->getErrorMessages();
                $errorCount = count($errorFields);
                $this->registerError($this->_fn('There was a problem with one of the module settings. Please review the message below, correct the error, and resubmit your changes.',
                        'There were problems with %1$d module settings. Please review the messages below, correct the errors, and resubmit your changes.',
                        $errorCount, array($errorCount)));
            }
        } elseif (!$this->request->isGet()) {
            throw new Zikula_Exception_Fatal();
        }

        return $this->view->assign_by_ref('configData', $configData)
            ->assign('errorFields', $errorFields)
            ->fetch('users_admin_config.tpl');
    }

    /**
     * Show the form to choose a CSV file and import several users from this file.
     *
     * Parameters passed via the $args array:
     * --------------------------------------
     * boolean $args['confirmed']  True if the user has confirmed the upload/import. Used as the default if $_POST['confirmed']
     *                                  is not set. Allows this function to be called internally, rather than as a result of a form post.
     * array   $args['importFile'] Information about the file to import. Used as the default if $_FILES['importFile'] is not set.
     *                                  Allows this function to be called internally, rather than as a result of a form post.
     * integer $args['delimiter']  A code indicating the delimiter used in the file. Used as the default if $_POST['delimiter']
     *                                  is not set. Allows this function to be called internally, rather than as a result of a form post.
     *
     * Parameters passed via GET:
     * --------------------------
     * None.
     *
     * Parameters passed via POST:
     * ---------------------------
     * boolean confirmed  True if the user has confirmed the upload/import.
     * array   importFile Structured information about the file to import, from <input type="file" name="fileFieldName" ... /> and stored
     *                          in $_FILES['fileFieldName']. See http://php.net/manual/en/features.file-upload.post-method.php .
     * integer delimiter  A code indicating the type of delimiter found in the import file. 1 = comma, 2 = semicolon, 3 = colon.
     *
     * Parameters passed via SESSION:
     * ------------------------------
     * None.
     *
     * @param array $args All arguments passed to the function.
     *
     * @return redirect user to admin main page if success and show again the forn otherwise
     *
     * @throws Zikula_Exception_Fatal     Thrown if the $args parameter is not valid.
     * @throws Zikula_Exception_Forbidden Thrown if the current user does not have add access.
     */
    public function import($args)
    {
        // security check
        if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_ADD)) {
            throw new Zikula_Exception_Forbidden();
        }

        // get input values. Check for direct function call first because calling function might be either get or post
        if (isset($args) && is_array($args) && !empty($args)) {
            $confirmed = isset($args['confirmed']) ? $args['confirmed'] : false;
        } elseif (isset($args) && !is_array($args)) {
            throw new Zikula_Exception_Fatal(LogUtil::getErrorMsgArgs());
        } elseif ($this->request->isGet()) {
            $confirmed = false;
        } elseif ($this->request->isPost()) {
            $this->checkCsrfToken();
            $confirmed = $this->request->request->get('confirmed', false);
        }

        // set default parameters
        $minpass = $this->getVar('minpass');
        $defaultGroup = ModUtil::getVar('Groups', 'defaultgroup');

        if ($confirmed) {
            // get other import values
            $importFile = $this->request->files->get('importFile', isset($args['importFile']) ? $args['importFile'] : null);
            $delimiter = $this->request->request->get('delimiter', isset($args['delimiter']) ? $args['delimiter'] : null);
            $importResults = $this->uploadImport($importFile, $delimiter);
            if ($importResults == '') {
                // the users have been imported successfully
                $this->registerStatus($this->__('Done! Users imported successfully.'));
                $this->redirect(ModUtil::url($this->name, 'admin', 'main'));
            }
        }

        // shows the form
        $post_max_size = ini_get('post_max_size');
        // get default group
        $group = ModUtil::apiFunc('Groups','user','get', array('gid' => $defaultGroup));
        $defaultGroup = $defaultGroup . ' (' . $group['name'] . ')';

        return $this->view->assign('importResults', isset($importResults) ? $importResults : '')
                ->assign('post_max_size', $post_max_size)
                ->assign('minpass', $minpass)
                ->assign('defaultGroup', $defaultGroup)
                ->fetch('users_admin_import.tpl');
    }

    /**
     * Show the form to export a CSV file of users.
     *
     * Parameters passed via the $args array:
     * --------------------------------------
     * boolean $args['confirmed']       True if the user has confirmed the export.
     * string  $args['exportFile']      Filename of the file to export (optional) (default=users.csv)
     * integer $args['delimiter']       A code indicating the type of delimiter found in the export file. 1 = comma, 2 = semicolon, 3 = colon, 4 = tab.
     * integer $args['exportEmail']     Flag to export email addresses, 1 for yes.
     * integer $args['exportTitles']    Flag to export a title row, 1 for yes.
     * integer $args['exportLastLogin'] Flag to export the last login date/time, 1 for yes.
     * integer $args['exportRegDate']   Flag to export the registration date/time, 1 for yes.
     * integer $args['exportGroups']    Flag to export the group membership, 1 for yes.
     *
     * Parameters passed via GET:
     * --------------------------
     * None.
     *
     * Parameters passed via POST:
     * ---------------------------
     * boolean confirmed       True if the user has confirmed the export.
     * string  exportFile      Filename of the file to export (optional) (default=users.csv)
     * integer delimiter       A code indicating the type of delimiter found in the export file. 1 = comma, 2 = semicolon, 3 = colon, 4 = tab.
     * integer exportEmail     Flag to export email addresses, 1 for yes.
     * integer exportTitles    Flag to export a title row, 1 for yes.
     * integer exportLastLogin Flag to export the last login date/time, 1 for yes.
     * integer exportRegDate   Flag to export the registration date/time, 1 for yes.
     * integer exportGroups    Flag to export the group membership, 1 for yes.
     *
     * Parameters passed via SESSION:
     * ------------------------------
     * None.
     *
     * @param array $args All arguments passed to the function.
     *
     * @return redirect user to the form if confirmed not 1, else export the csv file.
     *
     * @throws Zikula_Exception_Fatal     Thrown if parameters are passed via the $args array, but $args is invalid.
     * @throws Zikula_Exception_Forbidden Thrown if the current user does not have admin access, or method this function was accessed is invalid.
     */
    public function exporter($args)
    {
        // security check
        if (!SecurityUtil::checkPermission('Users::', '::', ACCESS_ADMIN)) {
            throw new Zikula_Exception_Forbidden();
        }

        // get input values. Check for direct function call first because calling function might be either get or post
        if (isset($args) && is_array($args) && !empty($args)) {
            $confirmed  = isset($args['confirmed']) ? $args['confirmed'] : false;
            $exportFile = isset($args['exportFile']) ? $args['exportFile'] : null;
            $delimiter  = isset($args['delimiter']) ? $args['delimiter'] : null;
            $email      = isset($args['exportEmail']) ? $args['exportEmail'] : null;
            $titles     = isset($args['exportTitles']) ? $args['exportTitles'] : null;
            $lastLogin  = isset($args['exportLastLogin']) ? $args['exportLastLogin'] : null;
            $regDate    = isset($args['exportRegDate']) ? $args['exportRegDate'] : null;
            $groups     = isset($args['exportGroups']) ? $args['exportGroups'] : null;
        } elseif (isset($args) && !is_array($args)) {
            throw new Zikula_Exception_Fatal(LogUtil::getErrorMsgArgs());
        } elseif ($this->request->isGet()) {
            $confirmed = false;
        } elseif ($this->request->isPost()) {
            $this->checkCsrfToken();
            $confirmed  = $this->request->request->get('confirmed', false);
            $exportFile = $this->request->request->get('exportFile', null);
            $delimiter  = $this->request->request->get('delimiter', null);
            $email      = $this->request->request->get('exportEmail', null);
            $titles     = $this->request->request->get('exportTitles', null);
            $lastLogin  = $this->request->request->get('exportLastLogin', null);
            $regDate    = $this->request->request->get('exportRegDate', null);
            $groups     = $this->request->request->get('exportGroups', null);
        } else {
            throw new Zikula_Exception_Forbidden();
        }

        if ($confirmed) {
            // get other import values
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
            $users = ModUtil::apiFunc($this->name, 'user', 'getAll');

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
     * Parameters passed via GET:
     * --------------------------
     * None.
     *
     * Parameters passed via POST:
     * ---------------------------
     * None.
     *
     * Parameters passed via SESSION:
     * ------------------------------
     * None.
     *
     * @param array $importFile Information about the file to import. Used as the default
     *                            if $_FILES['importFile'] is not set. Allows this function to be called internally,
     *                            rather than as a result of a form post.
     * @param integer $delimiter A code indicating the delimiter used in the file. Used as the
     *                            default if $_POST['delimiter'] is not set. Allows this function to be called internally,
     *                            rather than as a result of a form post.
     *
     * @return a empty message if success or an error message otherwise
     */
    protected function uploadImport(array $importFile, $delimiter)
    {
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
        $allGroups = ModUtil::apiFunc('Groups', 'user', 'getall');

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
                    if (!in_array(trim(strtolower($field)), $expectedFields)) {
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

            // check password length
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

            // validate activation value
            $importValues[$counter - 1]['activated'] = isset($importValues[$counter - 1]['activated']) ? (int)$importValues[$counter - 1]['activated'] : Users_Constant::ACTIVATED_ACTIVE;
            $activated = $importValues[$counter - 1]['activated'];
            if (($activated != Users_Constant::ACTIVATED_INACTIVE) && ($activated != Users_Constant::ACTIVATED_ACTIVE)) {
                return $this->__f('Error! The CSV is not valid: the "activated" column must contain 0 or 1 only.');
            }

            // validate sendmail
            $importValues[$counter - 1]['sendmail'] = isset($importValues[$counter - 1]['sendmail']) ? (int)$importValues[$counter - 1]['sendmail'] : 0;
            if ($importValues[$counter - 1]['sendmail'] < 0 || $importValues[$counter - 1]['sendmail'] > 1) {
                return $this->__f('Error! The CSV is not valid: the "sendmail" column must contain 0 or 1 only.');
            }

            // check groups and set defaultGroup as default if there are not groups defined
            $importValues[$counter - 1]['groups'] = isset($importValues[$counter - 1]['groups']) ? (int)$importValues[$counter - 1]['groups'] : '';
            $groups = $importValues[$counter - 1]['groups'];
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
        $usersInDB = ModUtil::apiFunc($this->name, 'admin', 'checkMultipleExistence',
                                      array('valuesarray' => $usersArray,
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
            $emailsInDB = ModUtil::apiFunc($this->name, 'admin', 'checkMultipleExistence',
                                          array('valuesarray' => $emailsArray,
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
        if (!ModUtil::apiFunc($this->name, 'admin', 'createImport', array('importvalues' => $importValues))) {
            return $this->__("Error! The creation of users has failed.");
        }

        return '';
    }

    /**
     * Sets or resets a user's need to changed his password on his next attempt at logging ing.
     *
     * Parameters passed via GET:
     * --------------------------
     * numeric userid The uid of the user for whom a change of password should be forced (or canceled).
     *
     * Parameters passed via POST:
     * ---------------------------
     * numeric userid                    The uid of the user for whom a change of password should be forced (or canceled).
     * boolean user_must_change_password True to force the user to change his password at his next log-in attempt, otherwise false.
     *
     * Parameters passed via SESSION:
     * ------------------------------
     * None.
     *
     * @return string The rendered output from either the template for confirmation.
     *
     * @throws Zikula_Exception_Fatal Thrown if a user id is not specified, is invalid, or does not point to a valid account record,
     *                                      or the account record is not in a consistent state.
     * @throws Zikula_Exception_Forbidden Thrown if the current user does not have edit access for the account record.
     */
    public function toggleForcedPasswordChange()
    {
        if ($this->request->isGet()) {
            $uid = $this->request->query->get('userid', false);

            if (!$uid || !is_numeric($uid) || ((int)$uid != $uid)) {
                throw new Zikula_Exception_Fatal(LogUtil::getErrorMsgArgs());
            }

            $userObj = UserUtil::getVars($uid);

            if (!isset($userObj) || !$userObj || !is_array($userObj) || empty($userObj)) {
                throw new Zikula_Exception_Fatal(LogUtil::getErrorMsgArgs());
            }

            if (!SecurityUtil::checkPermission('Users::', "{$userObj['uname']}::{$uid}", ACCESS_EDIT)) {
                throw new Zikula_Exception_Forbidden();
            }

            $userMustChangePassword = UserUtil::getVar('_Users_mustChangePassword', $uid, false);

            return $this->view->assign('user_obj', $userObj)
                ->assign('user_must_change_password', $userMustChangePassword)
                ->fetch('users_admin_toggleforcedpasswordchange.tpl');
        } elseif ($this->request->isPost()) {
            $this->checkCsrfToken();

            $uid = $this->request->request->get('userid', false);
            $userMustChangePassword = $this->request->request->get('user_must_change_password', false);

            if (!$uid || !is_numeric($uid) || ((int)$uid != $uid)) {
                throw new Zikula_Exception_Fatal(LogUtil::getErrorMsgArgs());
            }

            // Force reload of User object into cache.
            $userObj = UserUtil::getVars($uid);

            if (!SecurityUtil::checkPermission('Users::', "{$userObj['uname']}::{$uid}", ACCESS_EDIT)) {
                throw new Zikula_Exception_Forbidden();
            }

            if ($userMustChangePassword) {
                UserUtil::setVar('_Users_mustChangePassword', $userMustChangePassword, $uid);
            } else {
                UserUtil::delVar('_Users_mustChangePassword', $uid);
            }

            // Force reload of User object into cache.
            $userObj = UserUtil::getVars($uid, true);

            if ($userMustChangePassword) {
                if (isset($userObj['__ATTRIBUTES__']) && isset($userObj['__ATTRIBUTES__']['_Users_mustChangePassword'])) {
                    $this->registerStatus($this->__f('Done! A password change will be required the next time %1$s logs in.', array($userObj['uname'])));
                } else {
                    throw new Zikula_Exception_Fatal();
                }
            } else {
                if (isset($userObj['__ATTRIBUTES__']) && isset($userObj['__ATTRIBUTES__']['_Users_mustChangePassword'])) {
                    throw new Zikula_Exception_Fatal();
                } else {
                    $this->registerStatus($this->__f('Done! A password change will no longer be required for %1$s.', array($userObj['uname'])));
                }
            }

            $this->redirect(ModUtil::url($this->name, 'admin', 'view'));
        } else {
            throw new Zikula_Exception_Forbidden();
        }
    }
}
