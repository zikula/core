<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Controller;

use Zikula\Core\Event\GenericEvent;
use Zikula\Core\Response\PlainResponse;
use Zikula\Bundle\HookBundle\Hook\ValidationHook;
use Zikula\Bundle\HookBundle\Hook\ValidationProviders;
use Zikula\Core\Response\Ajax\AjaxResponse;
use ModUtil;
use SecurityUtil;
use Zikula;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route; // used in annotations - do not remove
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method; // used in annotations - do not remove

/**
 * @Route("/ajax")
 *
 * Access to actions initiated through AJAX for the Users module.
 */
class AjaxController extends \Zikula_Controller_AbstractAjax
{
    /**
     * Create and configure the view for the controller.
     *
     * @return void
     *
     * NOTE: This is necessary because the Zikula_Controller_AbstractAjax overrides this method located in Zikula_AbstractController.
     */
    protected function configureView()
    {
        $this->setView();
        $this->view->setController($this);
        $this->view->assign('controller', $this);
    }

    /**
     * @Route("/getusers", options={"expose"=true})
     * @Method("POST")
     *
     * Performs a user search based on the user name fragment entered so far.
     *
     * @param Request $request
     *
     * Parameters passed via POST:
     * ---------------------------
     * string fragment A partial user name entered by the user.
     *
     * @return string PlainResponse response object with list of users matching the criteria.
     */
    public function getUsersAction(Request $request)
    {
        $this->checkAjaxToken();

        if (SecurityUtil::checkPermission('ZikulaUsersModule::', '::', ACCESS_MODERATE)) {
            $fragment = $request->request->get('fragment');

            $qb = $this->entityManager->createQueryBuilder();
            $query = $qb->select('u')
                 ->from('ZikulaUsersModule:UserEntity', 'u')
                 ->where($qb->expr()->like('u.uname', ':fragment'))
                 ->setParameter('fragment', '%' . $fragment . '%')
                 ->getQuery();

            $results = $query->getArrayResult();
            $this->view->assign('results', $results);
        }

        $output = $this->view->fetch('Ajax/getusers.tpl');

        return new PlainResponse($output);
    }

    /**
     * @Route("/getregistrationerrors", options={"expose"=true})
     * @Method("POST")
     *
     * Validate new user information entered by the user.
     *
     * @param Request $request
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
     * @return array AjaxResponse containing error messages and message counts.
     *
     * @throws AccessDeniedException Thrown if registration is disbled.
     */
    public function getRegistrationErrorsAction(Request $request)
    {
        $this->checkAjaxToken();
        $userOrRegistration = array(
            'uid'           => $request->request->get('uid', null),
            'uname'         => $request->request->get('uname', null),
            'pass'          => $request->request->get('pass', null),
            'passreminder'  => $request->request->get('passreminder', null),
            'email'         => $request->request->get('email', null),
        );

        $eventType = $request->request->get('event_type', 'new_registration');
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

        $emailAgain         = $request->request->get('emailagain', '');
        $setPassword        = $request->request->get('setpass', false);
        $passwordAgain      = $request->request->get('passagain', '');
        $antiSpamUserAnswer = $request->request->get('antispamanswer', '');

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
            $returnValue['errorMessages'][] = $this->_fn('There was an error with one of the fields below. Please review the message, and correct your entry.', 'There were errors with %1$d of the fields below. Please review the messages, and correct your entries.', $totalErrors, array($totalErrors));
            $returnValue['errorMessagesCount']++;
        }

        return new AjaxResponse($returnValue);
    }
}
