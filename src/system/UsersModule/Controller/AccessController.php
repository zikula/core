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
use Zikula\UsersModule\AuthenticationMethodInterface\NonReEntrantAuthenticationMethodInterface;
use Zikula\UsersModule\AuthenticationMethodInterface\ReEntrantAuthenticationmethodInterface;
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

        $authenticationMethodCollector = $this->get('zikula_users_module.internal.authentication_method_collector');
        $selectedMethod = $request->query->get('authenticationMethod', $request->getSession()->get('authenticationMethod', null));
        if (empty($selectedMethod)) {
            return $this->render('@ZikulaUsersModule/Access/authenticationMethodSelector.html.twig', ['collector' => $authenticationMethodCollector]);
        } else {
            $request->getSession()->set('authenticationMethod', $selectedMethod); // save method to session for reEntrant needs
        }
        $authenticationMethod = $authenticationMethodCollector->get($selectedMethod);

        if ($authenticationMethod instanceof NonReEntrantAuthenticationMethodInterface) {
            $form = $this->createForm($authenticationMethod->getLoginFormClassName(), [], [
                'translator' => $this->getTranslator()
            ]);

            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $data = $form->getData();
                $uid = $authenticationMethod->authenticate($data);
                if (isset($uid)) {
                    $user = $this->get('zikula_users_module.user_repository')->find($uid);
                    if (isset($user)) {
                        // events
                        // hooks
                        $this->get('zikula_users_module.helper.access_helper')->login($user, $selectedMethod, $data['rememberme']);

                        return isset($returnUrl) ? $this->redirect($returnUrl) : $this->redirectToRoute('home');
                    }
                }
            }

            return $this->render($authenticationMethod->getLoginTemplateName(), [
                'form' => $form->createView()
            ]);
        } elseif ($authenticationMethod instanceof ReEntrantAuthenticationmethodInterface) {
            $uid = $authenticationMethod->authenticate([]);
            // @todo remove code duplication from above
            if (isset($uid)) {
                $user = $this->get('zikula_users_module.user_repository')->find($uid);
                if (isset($user)) {
                    // events
                    // hooks
                    $this->get('zikula_users_module.helper.access_helper')->login($user, $selectedMethod);

                    return isset($returnUrl) ? $this->redirect($returnUrl) : $this->redirectToRoute('home');
                }
            }
        } else {
            throw new \LogicException('Invalid authentication method.');
        }
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
            $authenticationMethod = $request->getSession()->get('authenticationMethod');
            if ($this->get('zikula_users_module.helper.access_helper')->logout()) {
                $event = new GenericEvent($user, [
                    'authenticationMethod' => $authenticationMethod,
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
