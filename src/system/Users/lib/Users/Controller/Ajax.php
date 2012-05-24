<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Access to actions initiated through AJAX for the Users module.
 */
class Users_Controller_Ajax extends Zikula_Controller_AbstractAjax
{
    /**
     * Performs a user search based on the user name fragment entered so far.
     *
     * Parameters passed via POST:
     * ---------------------------
     * string fragment A partial user name entered by the user.
     *
     * @return string Zikula_Response_Ajax_Plain with list of users matching the criteria.
     */
    public function getUsers()
    {
        $this->checkAjaxToken();
        $view = Zikula_View::getInstance($this->name);

        if (SecurityUtil::checkPermission('Users::', '::', ACCESS_MODERATE)) {
            $fragment = $this->request->query->get('fragment', $this->request->request->get('fragment'));

            ModUtil::dbInfoLoad($this->name);
            $tables = DBUtil::getTables();

            $usersColumn = $tables['users_column'];

            $where = 'WHERE ' . $usersColumn['uname'] . ' REGEXP \'(' . DataUtil::formatForStore($fragment) . ')\'';
            $results = DBUtil::selectObjectArray('users', $where);

            $view->assign('results', $results);
        }

        $output = $view->fetch('users_ajax_getusers.tpl');

        return new Zikula_Response_Ajax_Plain($output);
    }

    /**
     * Validate new user information entered by the user.
     *
     * Parameters passed via POST:
     * ---------------------------
     * string  uname          The proposed user name for the user record.
     * string  email          The proposed e-mail address for the user record.
     * string  emailagain     A verification of the proposed e-mail address for the user record.
     * boolean setpass        True if the password is to be set or changed; otherwise false.
     * string  pass           The proposed password for the new user record.
     * string  passreminder   The proposed password reminder for the user record.
     * string  passagain      A verification of the proposed password for the user record.
     * string  antispamanswer The user-entered answer to the registration question.
     * string  checkmode      Either 'new' or 'modify', depending on whether the record is a new user or an existing user or registration.
     *
     * @return array A Zikula_Response_Ajax containing error messages and message counts.
     *
     * @throws Zikula_Exception_Forbidden Thrown if registration is disbled.
     */
    public function getRegistrationErrors()
    {
        $this->checkAjaxToken();
        $userOrRegistration = array(
            'uid'           => $this->request->request->get('uid', null),
            'uname'         => $this->request->request->get('uname', null),
            'pass'          => $this->request->request->get('pass', null),
            'passreminder'  => $this->request->request->get('passreminder', null),
            'email'         => $this->request->request->get('email', null),
        );

        $eventType = $this->request->request->get('event_type', 'new_registration');
        if (($eventType == 'new_registration') || ($eventType == 'new_user')) {
            $checkMode = 'new';
        } else {
            $checkMode = 'modify';
        }

        // Check if registration is disabled and the user is not an admin.
        if (($eventType == 'new_registration') && !$this->getVar('reg_allowreg', true) && !SecurityUtil::checkPermission('Users::', '::', ACCESS_ADMIN)) {
            throw new Zikula_Exception_Forbidden($this->__('Sorry! New user registration is currently disabled.'));
        }

        $returnValue = array(
            'errorMessagesCount'    => 0,
            'errorMessages'         => array(),
            'errorFieldsCount'      => 0,
            'errorFields'           => array(),
            'validatorErrorsCount'  => 0,
            'validatorErrors'       => array(),
        );

        $emailAgain         = $this->request->request->get('emailagain', '');
        $setPassword        = $this->request->request->get('setpass', false);
        $passwordAgain      = $this->request->request->get('passagain', '');
        $antiSpamUserAnswer = $this->request->request->get('antispamanswer', '');

        $registrationErrors = ModUtil::apiFunc($this->name, 'registration', 'getRegistrationErrors', array(
            'checkmode'         => $checkMode,
            'reginfo'           => $userOrRegistration,
            'setpass'           => $setPassword,
            'passagain'         => $passwordAgain,
            'emailagain'        => $emailAgain,
            'antispamanswer'    => $antiSpamUserAnswer
        ));

        $errorMessages = array();
        $errorFields = array();
        $fields = array();
        if ($registrationErrors) {
            foreach ($registrationErrors as $field => $message) {
                $returnValue['errorFields'][$field] = $message;
                $returnValue['errorFieldsCount']++;
            }
        }

        $event = new Zikula_Event("module.users.ui.validate_edit.{$eventType}", $userOrRegistration, array(), new Zikula_Hook_ValidationProviders());
        $validators = $this->eventManager->notify($event)->getData();

        if (($eventType == 'new_user') || ($eventType == 'modify_user')) {
            $hook = new Zikula_ValidationHook('users.ui_hooks.user.validate_edit', $validators);
        } else {
            $hook = new Zikula_ValidationHook('users.ui_hooks.registration.validate_edit', $validators);
        }
        $this->notifyHooks($hook);
        $validators = $hook->getValidators();

        if ($validators->hasErrors()) {
            $areaErrorCollections = $validators->getCollection();
            foreach ($areaErrorCollections as $area => $errorCollection) {
                $returnValue['validatorErrors'][$area]['errorFields'] = $errorCollection->getErrors();
                $returnValue['validatorErrors'][$area]['errorFieldsCount'] = count($returnValue['validatorErrors'][$area]['errorFields']);
                $returnValue['validatorErrorsCount']++;
            }
        }

        $totalErrors = $returnValue['errorFieldsCount'];
        foreach ($returnValue['validatorErrors'] as $area => $errorInfo) {
            $totalErrors += $errorInfo['errorFieldsCount'];
        }
        if ($totalErrors > 0) {
            $returnValue['errorMessages'][] = $this->_fn('There was an error with one of the fields, below. Please review the message, and correct your entry.',
                    'There were errors with %1$d of the fields, below. Please review the messages, and correct your entries.',
                    $totalErrors, array($totalErrors));
            $returnValue['errorMessagesCount']++;
        }

        return new Zikula_Response_Ajax($returnValue);
    }

    /**
     * Retrieve the form fields for the login form that are appropriate for the selected authentication method.
     *
     * Parameters passed via POST:
     * ---------------------------
     * string form_type             An indicator of the type of form the fields will appear on.
     * array  authentication_method An array containing the authentication module name ('modname') and authentication method name ('method').
     *
     * @return Zikula_Response_Ajax An AJAX response containing the form field contents, and the module name and method name of the selected authentication method.
     *
     * @throws Zikula_Exception_Fatal Thrown if the authentication module name or method name are not valid.
     */
    public function getLoginFormFields()
    {
        $this->checkAjaxToken();
        $formType = $this->request->request->get('form_type', false);
        $selectedAuthenticationMethod = $this->request->request->get('authentication_method', array());
        $modname = (isset($selectedAuthenticationMethod['modname']) && !empty($selectedAuthenticationMethod['modname']) ? $selectedAuthenticationMethod['modname'] : false);
        $method = (isset($selectedAuthenticationMethod['method']) && !empty($selectedAuthenticationMethod['method']) ? $selectedAuthenticationMethod['method'] : false);

        if (empty($modname) || !is_string($modname)) {
            throw new Zikula_Exception_Fatal($this->__('An invalid authentication module name was received.'));
        } elseif (!ModUtil::available($modname)) {
            throw new Zikula_Exception_Fatal($this->__f('The \'%1$s\' module is not in an available state.', array($modname)));
        } elseif (!ModUtil::isCapable($modname, 'authentication')) {
            throw new Zikula_Exception_Fatal($this->__f('The \'%1$s\' module is not an authentication module.', array($modname)));
        }

        $loginFormFields = ModUtil::func($modname, 'Authentication', 'getLoginFormFields', array(
            'form_type' => $formType,
            'method'    => $method,
        ));

        return new Zikula_Response_Ajax(array(
            'content'   => $loginFormFields,
            'modname'   => $modname,
            'method'    => $method,
        ));
    }
}
