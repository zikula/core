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

use UserUtil;
use SecurityUtil;
use ModUtil;
use FileUtil;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Core\Exception\FatalErrorException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * @deprecated
 * @Route("/admin")
 *
 * Administrator-initiated actions for the Users module.
 */
class AdminController extends \Zikula_AbstractController
{
    /**
     * @Route("")
     * @return RedirectResponse
     */
    public function indexAction()
    {
        @trigger_error('This method is deprecated. Please use UserAdministrationController::listAction', E_USER_DEPRECATED);

        return new RedirectResponse($this->get('router')->generate('zikulausersmodule_useradministration_list'));
    }

    /**
     * @Route("/view")
     * @return RedirectResponse
     */
    public function viewAction(Request $request)
    {
        @trigger_error('This method is deprecated. Please use UserAdministrationController::listAction', E_USER_DEPRECATED);

        return new RedirectResponse($this->get('router')->generate('zikulausersmodule_useradministration_list'));
    }

    /**
     * @Route("/newuser")
     * @return RedirectResponse
     */
    public function newUserAction(Request $request)
    {
        @trigger_error('This method is deprecated. Please use UserAdministrationController::createAction', E_USER_DEPRECATED);

        return new RedirectResponse($this->get('router')->generate('zikulausersmodule_useradministration_create'));
    }

    /**
     * @Route("/legacy-search")
     * @return RedirectResponse
     */
    public function searchAction(Request $request)
    {
        @trigger_error('This method is deprecated. Please use UserAdministrationController::searchAction', E_USER_DEPRECATED);

        return new RedirectResponse($this->get('router')->generate('zikulausersmodule_useradministration_search'));
    }

    /**
     * @Route("/mailusers")
     * @return RedirectResponse
     */
    public function mailUsersAction(Request $request)
    {
        @trigger_error('This method is deprecated. Please use UserAdministrationController::searchAction', E_USER_DEPRECATED);

        return new RedirectResponse($this->get('router')->generate('zikulausersmodule_useradministration_search'));
    }

    /**
     * @Route("/modify")
     * @return RedirectResponse
     */
    public function modifyAction(Request $request)
    {
        @trigger_error('This method is deprecated. Please use UserAdministrationController::modifyAction', E_USER_DEPRECATED);

        return new RedirectResponse($this->get('router')->generate('zikulausersmodule_useradministration_modify', ['user' => $request->get('uid', null)]));
    }

    /**
     * @Route("/lostusername")
     * @return RedirectResponse
     */
    public function lostUsernameAction(Request $request)
    {
        @trigger_error('This method is deprecated. Please use UserAdministrationController::sendUserNameAction', E_USER_DEPRECATED);

        return new RedirectResponse($this->get('router')->generate('zikulausersmodule_useradministration_sendusername', ['user' => $request->get('userid')]));
    }

    /**
     * @Route("/lostpassword/{userid}", requirements={"userid" = "^[1-9]\d*$"})
     * @return RedirectResponse
     */
    public function lostPasswordAction(Request $request, $userid)
    {
        @trigger_error('This method is deprecated. Please use UserAdministrationController::sendConfirmation', E_USER_DEPRECATED);

        return new RedirectResponse($this->get('router')->generate('zikulausersmodule_useradministration_sendconfirmation', ['user' => $userid]));
    }

    /**
     * @Route("/deleteusers")
     * @return RedirectResponse
     */
    public function deleteUsersAction(Request $request)
    {
        @trigger_error('This method is deprecated. Please use UserAdministrationController::deleteAction', E_USER_DEPRECATED);

        return new RedirectResponse($this->get('router')->generate('zikulausersmodule_useradministration_delete'));
    }

    /**
     * @Route("/viewregistrations")
     * @return RedirectResponse
     */
    public function viewRegistrationsAction(Request $request)
    {
        @trigger_error('This method is deprecated. Please use RegistrationAdministrationController::listAction', E_USER_DEPRECATED);

        return new RedirectResponse($this->get('router')->generate('zikulausersmodule_registrationadministration_list'));
    }

    /**
     * @Route("/displayregistration/{uid}", requirements={"uid" = "^[1-9]\d*$"})
     * @return RedirectResponse
     */
    public function displayRegistrationAction(Request $request, $uid)
    {
        @trigger_error('This method is deprecated. Please use RegistrationAdministrationController::displayAction', E_USER_DEPRECATED);

        return new RedirectResponse($this->get('router')->generate('zikulausersmodule_registrationadministration_display', ['user' => $uid]));
    }

    /**
     * @Route("/modifyregistration")
     * @return RedirectResponse
     */
    public function modifyRegistrationAction(Request $request)
    {
        @trigger_error('This method is deprecated. Please use RegistrationAdministrationController::modifyAction', E_USER_DEPRECATED);

        return new RedirectResponse($this->get('router')->generate('zikulausersmodule_registrationadministration_modify', ['user' => $request->get('uid', null)]));
    }

    /**
     * @Route("/verifyregistration")
     * @return RedirectResponse
     */
    public function verifyRegistrationAction(Request $request)
    {
        @trigger_error('This method is deprecated. Please use RegistrationAdministrationController::verifyAction', E_USER_DEPRECATED);

        return new RedirectResponse($this->get('router')->generate('zikulausersmodule_registrationadministration_verify', ['user' => $request->get('uid', null)]));
    }

    /**
     * @Route("/approveregistration")
     * @return RedirectResponse
     */
    public function approveRegistrationAction(Request $request)
    {
        @trigger_error('This method is deprecated. Please use RegistrationAdministrationController::approveAction', E_USER_DEPRECATED);

        return new RedirectResponse($this->get('router')->generate('zikulausersmodule_registrationadministration_approve', [
            'user' => $request->get('uid', null),
            'force' => $request->get('force', false)
        ]));
    }

    /**
     * @Route("/denyregistration")
     * @return RedirectResponse
     */
    public function denyRegistrationAction(Request $request)
    {
        @trigger_error('This method is deprecated. Please use RegistrationAdministrationController::denyAction', E_USER_DEPRECATED);

        return new RedirectResponse($this->get('router')->generate('zikulausersmodule_registrationadministration_deny', ['user' => $request->get('uid', null)]));
    }

    /**
     * @Route("/config")
     * @return RedirectResponse
     */
    public function configAction(Request $request)
    {
        @trigger_error('This method is deprecated. Please use ConfigController::configAction', E_USER_DEPRECATED);

        return new RedirectResponse($this->get('router')->generate('zikulausersmodule_config_config'));
    }

    /**
     * @Route("/import")
     * @return RedirectResponse
     */
    public function importAction(Request $request)
    {
        @trigger_error('This method is deprecated. Please use FileIOController::importAction', E_USER_DEPRECATED);

        return new RedirectResponse($this->get('router')->generate('zikulausersmodule_fileio_import'));
    }

    /**
     * @Route("/export")
     * @return RedirectResponse
     */
    public function exporterAction(Request $request)
    {
        @trigger_error('This method is deprecated. Please use FileIOController::exportAction', E_USER_DEPRECATED);

        return new RedirectResponse($this->get('router')->generate('zikulausersmodule_fileio_export'));
    }

    /**
     * @Route("/forcepasswordchange")
     * @return RedirectResponse
     */
    public function toggleForcedPasswordChangeAction(Request $request)
    {
        @trigger_error('This method is deprecated. Please use UserAdministrationController::togglePasswordChangeAction', E_USER_DEPRECATED);

        return new RedirectResponse($this->get('router')->generate('zikulausersmodule_useradministration_togglepasswordchange', ['user' => $request->get('userid')]));
    }
}
