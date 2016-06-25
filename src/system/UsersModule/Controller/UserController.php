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

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * @deprecated
 * User controllers for the Users module.
 */
class UserController extends \Zikula_AbstractController
{
    /**
     * Note: No Route needed here because this is legacy-only
     * @return RedirectResponse symfony response object
     */
    public function mainAction()
    {
        @trigger_error('This method is deprecated. Please use AccountController::menuAction', E_USER_DEPRECATED);

        return new RedirectResponse($this->get('router')->generate('zikulausersmodule_account_menu'));
    }

    /**
     * @Route("/useraccount", options={"zkNoBundlePrefix"=1})
     * @return RedirectResponse
     */
    public function indexAction(Request $request)
    {
        @trigger_error('This method is deprecated. Please use AccountController::menuAction', E_USER_DEPRECATED);

        return new RedirectResponse($this->get('router')->generate('zikulausersmodule_account_menu'));
    }

    /**
     * @Route("/view")
     * @return RedirectResponse
     */
    public function viewAction()
    {
        @trigger_error('This method is deprecated. Please use AccountController::menuAction', E_USER_DEPRECATED);

        return new RedirectResponse($this->get('router')->generate('zikulausersmodule_account_menu'));
    }

    /**
     * @Route("/register", options={"zkNoBundlePrefix"=1})
     * @return RedirectResponse
     */
    public function registerAction(Request $request)
    {
        @trigger_error('This method is deprecated. Please use RegistrationController::selectRegistrationMethodAction', E_USER_DEPRECATED);
        $subRequest = $this->getContainer()
            ->get('request_stack')
            ->getCurrentRequest()
            ->duplicate($request->query->all(), null, ['_controller' => 'ZikulaUsersModule:Registration:selectRegistrationMethod']);

        return $this->getContainer()->get('http_kernel')->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
    }

    /**
     * @Route("/lost-account-details")
     * @return RedirectResponse
     */
    public function lostPwdUnameAction()
    {
        @trigger_error('This method is deprecated. Please use AccountController::menuAction', E_USER_DEPRECATED);

        return new RedirectResponse($this->get('router')->generate('zikulausersmodule_account_menu'));
    }

    /**
     * @Route("/lost-username")
     * @return RedirectResponse
     */
    public function lostUnameAction(Request $request)
    {
        @trigger_error('This method is deprecated. Please use AccountController::lostUserNameAction', E_USER_DEPRECATED);

        return new RedirectResponse($this->get('router')->generate('zikulausersmodule_account_lostusername'));
    }

    /**
     * @Route("/lost-password")
     * @return RedirectResponse
     */
    public function lostPasswordAction(Request $request)
    {
        @trigger_error('This method is deprecated. Please use AccountController::lostPasswordAction', E_USER_DEPRECATED);

        return new RedirectResponse($this->get('router')->generate('zikulausersmodule_account_lostpassword'));
    }

    /**
     * @Route("/lost-password/code")
     * @return RedirectResponse
     */
    public function lostPasswordCodeAction(Request $request)
    {
        @trigger_error('This method is deprecated. Please use AccountController::confirmationCodeAction', E_USER_DEPRECATED);

        return new RedirectResponse($this->get('router')->generate('zikulausersmodule_account_confirmationcode'));
    }

    /**
     * @Route("/login", options={"zkNoBundlePrefix"=1})
     * @return RedirectResponse
     */
    public function loginAction(Request $request)
    {
        @trigger_error('This method is deprecated. Please use AccessController::loginAction', E_USER_DEPRECATED);

        return new RedirectResponse($this->get('router')->generate('zikulausersmodule_access_login', ['returnUrl' => $request->query->get('returnpage')]));
    }

    /**
     * @Route("/logout", options={"zkNoBundlePrefix"=1})
     * @return RedirectResponse
     */
    public function logoutAction(Request $request)
    {
        @trigger_error('This method is deprecated. Please use AccessController::logoutAction', E_USER_DEPRECATED);

        return new RedirectResponse($this->get('router')->generate('zikulausersmodule_access_logout'));
    }

    /**
     * @Route("/verify-registration")
     * @return RedirectResponse
     */
    public function verifyRegistrationAction(Request $request)
    {
        @trigger_error('This method is deprecated. Please use RegistrationController::verifyAction', E_USER_DEPRECATED);

        return new RedirectResponse($this->get('router')->generate('zikulazauthmodule_registration_verify', ['uname' => $request->get('uname'), 'verifycode' => $request->get('verifycode')]));
    }

    /**
     * LEGACY user account activation.
     * This is a Core-1.2 era method and is only kept here to properly redirect if it is used.
     * @return RedirectResponse
     */
    public function activationAction(Request $request)
    {
        @trigger_error('This method is removed.', E_USER_DEPRECATED);
        $request->getSession()->getFlashBag()->add('error', $this->__('Warning! This method is no longer functional. You must re-register.'));

        return new RedirectResponse($this->get('router')->generate('zikulausersmodule_registration_register'));
    }

    /**
     * @return RedirectResponse
     */
    public function siteOffLoginAction(Request $request)
    {
        @trigger_error('This method is deprecated. Please use AccessController::loginAction', E_USER_DEPRECATED);

        return new RedirectResponse($this->get('router')->generate('zikulausersmodule_access_login'));
    }

    /**
     * @Route("/password")
     * @return RedirectResponse
     */
    public function changePasswordAction(Request $request)
    {
        @trigger_error('This method is deprecated. Please use AccountController::changePasswordAction', E_USER_DEPRECATED);

        return new RedirectResponse($this->get('router')->generate('zikulausersmodule_account_changepassword'));
    }

    /**
     * @Route("/email")
     * @return RedirectResponse
     */
    public function changeEmailAction()
    {
        @trigger_error('This method is deprecated. Please use AccountController::menuAction', E_USER_DEPRECATED);

        return new RedirectResponse($this->get('router')->generate('zikulausersmodule_account_changeemail'));
    }

    /**
     * @Route("/email/confirm/{confirmcode}")
     * @return RedirectResponse
     */
    public function confirmChEmailAction(Request $request, $confirmcode = null)
    {
        @trigger_error('This method is deprecated. Please use AccountController::confirmChangeEmailAction', E_USER_DEPRECATED);

        return new RedirectResponse($this->get('router')->generate('zikulausersmodule_account_confirmchangedemail', ['code' => $confirmcode]));
    }

    /**
     * @Route("/lang")
     * @return RedirectResponse
     */
    public function changeLangAction()
    {
        @trigger_error('This method is deprecated. Please use AccountController::changeLanguageAction', E_USER_DEPRECATED);

        return new RedirectResponse($this->get('router')->generate('zikulausersmodule_account_changelanguage'));
    }

    /**
     * @return RedirectResponse
     */
    public function loginScreenAction($args)
    {
        @trigger_error('This method is deprecated. Please use AccessController::loginAction', E_USER_DEPRECATED);

        return new RedirectResponse($this->get('router')->generate('zikulausersmodule_access_login'), 301);
    }
}
