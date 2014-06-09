<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Module\UsersModule\Controller;

use Zikula\Core\Event\GenericEvent;
use Zikula\Core\Response\PlainResponse;
use Zikula\Core\Hook\ValidationHook;
use Zikula\Core\Hook\ValidationProviders;
use Zikula\Core\Response\Ajax\AjaxResponse;
use Zikula_View;
use ModUtil;
use SecurityUtil;
use Zikula;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Debug\Exception\FatalErrorException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Access to actions initiated through AJAX for the Users module.
 */
class AjaxController extends \Zikula_Controller_AbstractAjax
{
    /**
     * Performs a user search based on the user name fragment entered so far.
     *
     * Parameters passed via POST:
     * ---------------------------
     * string fragment A partial user name entered by the user.
     *
     * @return string PlainResponse response object with list of users matching the criteria.
     */
    public function getUsersAction()
    {
        $this->checkAjaxToken();
        $view = Zikula_View::getInstance($this->name);

        $results = array();
        if (SecurityUtil::checkPermission('ZikulaUsersModule::', '::', ACCESS_MODERATE)) {
            $fragment = $this->request->query->get('fragment', $this->request->request->get('fragment'));

            $qb = $this->entityManager->createQueryBuilder();
            $query = $qb->select('u')
                 ->from('ZikulaUsersModule:UserEntity', 'u')
                 ->where($qb->expr()->like('u.uname', ':fragment'))
                 ->setParameter('fragment', '%' . $fragment . '%')
                 ->getQuery();

            $results = $query->getArrayResult();
        }

        $dataType = $this->request->query->get('dataType', '');
        if ($dataType == 'json') {
            return new PlainResponse(json_encode($results));
        }

        $view->assign('results', $results);
        $output = $view->fetch('Ajax/getusers.tpl');

        return new PlainResponse($output);
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
     * @return array A AjaxResponse containing error messages and message counts.
     *
     * @throws AccessDeniedExceptionThrown if registration is disbled.
     */
    public function getRegistrationErrorsAction()
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
        if (($eventType == 'new_registration') && !$this->getVar('reg_allowreg', true) && !SecurityUtil::checkPermission('ZikulaUsersModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException($this->__('Sorry! New user registration is currently disabled.'));
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

        if ($registrationErrors) {
            foreach ($registrationErrors as $field => $message) {
                $returnValue['errorFields'][$field] = $message;
                $returnValue['errorFieldsCount']++;
            }
        }

        $event = new GenericEvent($userOrRegistration, array(), new ValidationProviders());
        $this->getDispatcher()->dispatch("module.users.ui.validate_edit.{$eventType}", $event);
        $validators =  $event->getData();

        $hook = new ValidationHook($validators);
        if (($eventType == 'new_user') || ($eventType == 'modify_user')) {
            $this->dispatchHooks('users.ui_hooks.user.validate_edit', $hook);
        } else {
            $this->dispatchHooks('users.ui_hooks.registration.validate_edit', $hook);
        }
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

        return new AjaxResponse($returnValue);
    }

    /**
     * Retrieve the form fields for the login form that are appropriate for the selected authentication method.
     *
     * Parameters passed via POST:
     * ---------------------------
     * string form_type             An indicator of the type of form the fields will appear on.
     * array  authentication_method An array containing the authentication module name ('modname') and authentication method name ('method').
     *
     * @return AjaxResponse An AJAX response containing the form field contents, and the module name and method name of the selected authentication method.
     *
     * @throws FatalErrorException Thrown if the authentication module name or method name are not valid.
     */
    public function getLoginFormFieldsAction()
    {
        $this->checkAjaxToken();
        $formType = $this->request->request->get('form_type', false);
        $selectedAuthenticationMethod = $this->request->request->get('authentication_method', array());
        $modname = (isset($selectedAuthenticationMethod['modname']) && !empty($selectedAuthenticationMethod['modname']) ? $selectedAuthenticationMethod['modname'] : false);
        $method = (isset($selectedAuthenticationMethod['method']) && !empty($selectedAuthenticationMethod['method']) ? $selectedAuthenticationMethod['method'] : false);

        if (empty($modname) || !is_string($modname)) {
            throw new FatalErrorException($this->__('An invalid authentication module name was received.'));
        } elseif (!ModUtil::available($modname)) {
            throw new FatalErrorException($this->__f('The \'%1$s\' module is not in an available state.', array($modname)));
        } elseif (!ModUtil::isCapable($modname, 'authentication')) {
            throw new FatalErrorException($this->__f('The \'%1$s\' module is not an authentication module.', array($modname)));
        }

        $loginFormFields = ModUtil::func($modname, 'Authentication', 'getLoginFormFields', array(
            'form_type' => $formType,
            'method'    => $method,
        ));
        if ($loginFormFields instanceof Response) {
            // Forward compatability. @todo Remove check in 1.5.0
            $loginFormFields = $loginFormFields->getContent();
        }

        return new AjaxResponse(array(
            'content'   => $loginFormFields,
            'modname'   => $modname,
            'method'    => $method,
        ));
    }

    /**
     * Retrieve the form fields for the login form that are appropriate for the selected authentication method.
     *
     * @deprecated since 1.4.0 use $this->getLoginFormFieldsAction instead
     *
     * @todo Remove in 1.5.0
     */
    public function getLoginFormFields()
    {
        return $this->getLoginFormFieldsAction();
    }
}
