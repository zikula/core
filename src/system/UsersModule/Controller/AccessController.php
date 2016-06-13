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

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Zikula\Core\Controller\AbstractController;
use Zikula\Core\Event\GenericEvent;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\UsersModule\AccessEvents;
use Zikula\UsersModule\AuthenticationMethodInterface\NonReEntrantAuthenticationMethodInterface;
use Zikula\UsersModule\AuthenticationMethodInterface\ReEntrantAuthenticationmethodInterface;
use Zikula\UsersModule\Entity\UserEntity;
use Zikula\UsersModule\UserEvents;

class AccessController extends AbstractController
{
    /**
     * @todo change route
     * @Route("/login-new/{returnUrl}", options={"zkNoBundlePrefix"=1})
     * @param Request $request
     * @param null $returnUrl
     * @return string
     */
    public function loginAction(Request $request, $returnUrl = null)
    {
        if ($this->get('zikula_users_module.current_user')->isLoggedIn()) {
            return $this->redirectToRoute('zikulausersmodule_account_menu');
        }
        if ($this->get('zikula_extensions_module.api.variable')->get(VariableApi::CONFIG, 'siteoff', false)) {
            $this->addFlash('error', $this->__('The site is currently unavailable. Attempts to login will fail unless the user has full Admin rights.'));
        }

        $authenticationMethodCollector = $this->get('zikula_users_module.internal.authentication_method_collector');
        $selectedMethod = $request->query->get('authenticationMethod', $request->getSession()->get('authenticationMethod', null));
        if (empty($selectedMethod) && count($authenticationMethodCollector->getActiveKeys()) > 1) {
            return $this->render('@ZikulaUsersModule/Access/authenticationMethodSelector.html.twig', ['collector' => $authenticationMethodCollector]);
        } else {
            if (empty($selectedMethod) && count($authenticationMethodCollector->getActiveKeys()) == 1) {
                $selectedMethod = $authenticationMethodCollector->getActiveKeys()[0];
            }
            $request->getSession()->set('authenticationMethod', $selectedMethod); // save method to session for reEntrant needs
            $request->getSession()->set('returnUrl', $returnUrl); // save returnUrl to session for reEntrant needs
        }
        $authenticationMethod = $authenticationMethodCollector->get($selectedMethod);
        $rememberMe = false;

        if ($authenticationMethod instanceof NonReEntrantAuthenticationMethodInterface) {
            $form = $this->createForm($authenticationMethod->getLoginFormClassName());
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $data = $form->getData();
                $rememberMe = $data['rememberme']; // @todo cannot enforce contract w/ third party module to contain this field
                $uid = $authenticationMethod->authenticate($data);
            } else {
                return $this->render($authenticationMethod->getLoginTemplateName(), [
                    'form' => $form->createView()
                ]);
            }
        } elseif ($authenticationMethod instanceof ReEntrantAuthenticationmethodInterface) {
            $uid = $authenticationMethod->authenticate([]);
        } else {
            throw new \LogicException($this->__('Invalid authentication method.'));
        }
        if (isset($uid)) {
            // authentication succeeded
            $user = $this->get('zikula_users_module.user_repository')->find($uid);
            if (isset($user)) {
                // events
                // hooks
                if ($this->get('zikula_users_module.helper.access_helper')->loginAllowed($user, $selectedMethod)) {
                    $this->get('zikula_users_module.helper.access_helper')->login($user, $selectedMethod, $rememberMe);
                    $returnUrl = $this->dispatchLoginSuccessEvent($user, $selectedMethod, $request->getSession()->get('returnUrl', null));
                }

                return isset($returnUrl) ? $this->redirect($returnUrl) : $this->redirectToRoute('home');
            }
        }
        // login failed - no uid available
        $this->addFlash('error', $this->__('Login failed'));

        return $this->redirectToRoute('home');
    }

    private function dispatchLoginSuccessEvent(UserEntity $user, $selectedMethod, $returnUrl)
    {
        $eventArgs = [
            'authenticationMethod' => $selectedMethod,
            'redirecturl' => $returnUrl,
        ];
        if (isset($isFirstLogin)) {
            // @todo compute isFirstLogin
            $eventArgs['isFirstLogin'] = $isFirstLogin;
        }
        $event = new GenericEvent($user, $eventArgs);
        $event = $this->get('event_dispatcher')->dispatch(AccessEvents::LOGIN_SUCCESS, $event);

        return $event->hasArgument('redirecturl') ? $event->getArgument('redirecturl') : $returnUrl;
    }

    /**
     * @todo change route
     * @Route("/logout-new/{returnUrl}", options={"zkNoBundlePrefix"=1})
     * @param Request $request
     * @param null $returnUrl
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function logoutAction(Request $request, $returnUrl = null)
    {
        $currentUser = $this->get('zikula_users_module.current_user');
        if ($currentUser->isLoggedIn()) {
            $uid = $currentUser->get('uid');
            $user = $this->get('zikula_users_module.user_repository')->find($uid);
            if ($this->get('zikula_users_module.helper.access_helper')->logout()) {
                $event = new GenericEvent($user, [
                    'authenticationMethod' => $request->getSession()->get('authenticationMethod'),
                    'uid' => $uid,
                ]);
                $this->get('event_dispatcher')->dispatch(UserEvents::USER_LOGOUT_SUCCESS, $event);
            } else {
                $this->addFlash('error', $this->__('Error! You have not been logged out.'));
            }
        }

        return isset($returnUrl) ? $this->redirect($returnUrl) : $this->redirectToRoute('home');
    }
}
