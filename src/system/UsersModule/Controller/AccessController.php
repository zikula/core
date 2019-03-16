<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Zikula\Bundle\HookBundle\Dispatcher\HookDispatcherInterface;
use Zikula\Bundle\HookBundle\Hook\ProcessHook;
use Zikula\Bundle\HookBundle\Hook\ValidationHook;
use Zikula\Core\Controller\AbstractController;
use Zikula\Core\Event\GenericEvent;
use Zikula\UsersModule\AccessEvents;
use Zikula\UsersModule\Api\ApiInterface\CurrentUserApiInterface;
use Zikula\UsersModule\AuthenticationMethodInterface\NonReEntrantAuthenticationMethodInterface;
use Zikula\UsersModule\AuthenticationMethodInterface\ReEntrantAuthenticationMethodInterface;
use Zikula\UsersModule\Collector\AuthenticationMethodCollector;
use Zikula\UsersModule\Constant;
use Zikula\UsersModule\Entity\RepositoryInterface\UserRepositoryInterface;
use Zikula\UsersModule\Entity\UserEntity;
use Zikula\UsersModule\Event\UserFormAwareEvent;
use Zikula\UsersModule\Event\UserFormDataEvent;
use Zikula\UsersModule\Exception\InvalidAuthenticationMethodLoginFormException;
use Zikula\UsersModule\Form\Type\DefaultLoginType;
use Zikula\UsersModule\Helper\AccessHelper;
use Zikula\UsersModule\HookSubscriber\LoginUiHooksSubscriber;

class AccessController extends AbstractController
{
    /**
     * @Route("/login", options={"zkNoBundlePrefix"=1})
     *
     * @param Request $request
     * @param CurrentUserApiInterface $currentUserApi
     * @param UserRepositoryInterface $userRepository
     * @param AuthenticationMethodCollector $authenticationMethodCollector
     * @param AccessHelper $accessHelper
     *
     * @return Response
     * @throws InvalidAuthenticationMethodLoginFormException
     */
    public function loginAction(
        Request $request,
        CurrentUserApiInterface $currentUserApi,
        UserRepositoryInterface $userRepository,
        AuthenticationMethodCollector $authenticationMethodCollector,
        AccessHelper $accessHelper,
        HookDispatcherInterface $hookDispatcher
    ) {
        if ($currentUserApi->isLoggedIn()) {
            return $this->redirectToRoute('zikulausersmodule_account_menu');
        }
        $returnUrl = $request->query->get('returnUrl', null);

        $selectedMethod = $request->query->get('authenticationMethod', $request->getSession()->get('authenticationMethod', null));
        if (empty($selectedMethod) && count($authenticationMethodCollector->getActiveKeys()) > 1) {
            return $this->render('@ZikulaUsersModule/Access/authenticationMethodSelector.html.twig', [
                'collector' => $authenticationMethodCollector,
                'path' => 'zikulausersmodule_access_login'
            ]);
        } else {
            if (empty($selectedMethod) && 1 == count($authenticationMethodCollector->getActiveKeys())) {
                $selectedMethod = $authenticationMethodCollector->getActiveKeys()[0];
            }
            $request->getSession()->set('authenticationMethod', $selectedMethod); // save method to session for reEntrant needs
            if (!empty($returnUrl)) {
                $request->getSession()->set('returnUrl', $returnUrl);
            } // save returnUrl to session for reEntrant needs
        }
        $authenticationMethod = $authenticationMethodCollector->get($selectedMethod);
        $rememberMe = false;
        $dispatcher = $this->get('event_dispatcher');

        $dispatcher->dispatch(AccessEvents::LOGIN_STARTED, new GenericEvent());

        $form = null;
        if ($authenticationMethod instanceof NonReEntrantAuthenticationMethodInterface) {
            $form = $this->createForm($authenticationMethod->getLoginFormClassName());
            if (!$form->has('rememberme')) {
                throw new InvalidAuthenticationMethodLoginFormException();
            }
            $loginFormEvent = new UserFormAwareEvent($form);
            $dispatcher->dispatch(AccessEvents::AUTHENTICATION_FORM, $loginFormEvent);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $data = $form->getData();
                $rememberMe = $data['rememberme'];
                $uid = $authenticationMethod->authenticate($data);
            } else {
                return $this->render($authenticationMethod->getLoginTemplateName(), [
                    'form' => $form->createView(),
                    'additional_templates' => isset($loginFormEvent) ? $loginFormEvent->getTemplates() : []
                ]);
            }
        } elseif ($authenticationMethod instanceof ReEntrantAuthenticationMethodInterface) {
            $uid = ('POST' == $request->getMethod()) ? Constant::USER_ID_ANONYMOUS : $authenticationMethod->authenticate(); // provide temp value for uid until form gives real value.
            $hasListeners = $dispatcher->hasListeners(AccessEvents::AUTHENTICATION_FORM);
            $hookBindings = $hookDispatcher->getBindingsFor('subscriber.users.ui_hooks.login_screen');
            if ($hasListeners || count($hookBindings) > 0) {
                $form = $this->createForm(DefaultLoginType::class, ['uid' => $uid]);
                $loginFormEvent = new UserFormAwareEvent($form);
                $dispatcher->dispatch(AccessEvents::AUTHENTICATION_FORM, $loginFormEvent);
                if ($form->count() > 3) { // count > 3 means that the AUTHENTICATION_FORM event added some form children
                    $form->handleRequest($request);
                    if ($form->isValid() && $form->isSubmitted()) {
                        $uid = $form->get('uid')->getData();
                        $rememberMe = $form->get('rememberme')->getData();
                    } else {
                        return $this->render('@ZikulaUsersModule/Access/defaultLogin.html.twig', [
                            'form' => $form->createView(),
                            'additional_templates' => isset($loginFormEvent) ? $loginFormEvent->getTemplates() : []
                        ]);
                    }
                }
            }
        } else {
            throw new \LogicException($this->__('Invalid authentication method.'));
        }
        $user = null;
        if (isset($uid)) {
            $user = $userRepository->find($uid);
            if (isset($user)) {
                $hook = new ValidationHook();
                $hookDispatcher->dispatch(LoginUiHooksSubscriber::LOGIN_VALIDATE, $hook);
                $validators = $hook->getValidators();
                if (!$validators->hasErrors() && $accessHelper->loginAllowed($user)) {
                    if (isset($form)) {
                        $formDataEvent = new UserFormDataEvent($user, $form);
                        $dispatcher->dispatch(AccessEvents::AUTHENTICATION_FORM_HANDLE, $formDataEvent);
                    }
                    $hookDispatcher->dispatch(LoginUiHooksSubscriber::LOGIN_PROCESS, new ProcessHook($user));
                    $event = new GenericEvent($user, ['authenticationMethod' => $selectedMethod]);
                    $dispatcher->dispatch(AccessEvents::LOGIN_VETO, $event);
                    if (!$event->isPropagationStopped()) {
                        $returnUrlFromSession = urldecode($request->getSession()->get('returnUrl', $returnUrl));
                        $accessHelper->login($user, $rememberMe);
                        $returnUrl = $this->dispatchLoginSuccessEvent($user, $selectedMethod, $returnUrlFromSession);
                    } else {
                        if ($event->hasArgument('flash')) {
                            $this->addFlash('danger', $event->getArgument('flash'));
                        }
                        $returnUrl = $event->getArgument('returnUrl');
                    }

                    return !empty($returnUrl) ? $this->redirect($returnUrl) : $this->redirectToRoute('home');
                }
            }
        }
        // login failed
        // implement auto-register setting here. If true, do so and proceed. #2915
        $this->addFlash('error', $this->__('Login failed.'));
        $request->getSession()->remove('authenticationMethod');
        $returnUrl = $this->dispatchLoginFailedEvent($user, $returnUrl, $authenticationMethod);

        return !empty($returnUrl) ? $this->redirect($returnUrl) : $this->redirectToRoute('home');
    }

    /**
     * @param UserEntity $user
     * @param $selectedMethod
     * @param $returnUrl
     * @return mixed
     */
    private function dispatchLoginSuccessEvent(UserEntity $user, $selectedMethod, $returnUrl)
    {
        $eventArgs = [
            'authenticationMethod' => $selectedMethod,
            'returnUrl' => $returnUrl,
        ];
        $defaultLastLogin = new \DateTime("1970-01-01 00:00:00");
        $actualLastLogin = $user->getLastlogin();
        if (empty($actualLastLogin) || $actualLastLogin == $defaultLastLogin) {
            $eventArgs['isFirstLogin'] = true;
        }
        $event = new GenericEvent($user, $eventArgs);
        $event = $this->get('event_dispatcher')->dispatch(AccessEvents::LOGIN_SUCCESS, $event);

        return $event->hasArgument('returnUrl') ? $event->getArgument('returnUrl') : $returnUrl;
    }

    /**
     * @param UserEntity|null $user
     * @param $returnUrl
     * @param $authenticationMethod
     * @return mixed
     */
    private function dispatchLoginFailedEvent(UserEntity $user = null, $returnUrl, $authenticationMethod)
    {
        $eventArgs = [
            'authenticationMethod' => $authenticationMethod,
            'returnUrl' => $returnUrl,
        ];
        $event = new GenericEvent($user, $eventArgs);
        $event = $this->get('event_dispatcher')->dispatch(AccessEvents::LOGIN_FAILED, $event);

        return $event->hasArgument('returnUrl') ? $event->getArgument('returnUrl') : $returnUrl;
    }

    /**
     * @Route("/logout/{returnUrl}", options={"zkNoBundlePrefix"=1})
     *
     * @param Request $request
     * @param CurrentUserApiInterface $currentUserApi
     * @param UserRepositoryInterface $userRepository
     * @param AccessHelper $accessHelper
     * @param null $returnUrl
     *
     * @return RedirectResponse
     */
    public function logoutAction(
        Request $request,
        CurrentUserApiInterface $currentUserApi,
        UserRepositoryInterface $userRepository,
        AccessHelper $accessHelper,
        $returnUrl = null
    ) {
        if ($currentUserApi->isLoggedIn()) {
            $uid = $currentUserApi->get('uid');
            $user = $userRepository->find($uid);
            if ($accessHelper->logout()) {
                $event = new GenericEvent($user, [
                    'authenticationMethod' => $request->getSession()->get('authenticationMethod'),
                    'uid' => $uid,
                ]);
                $this->get('event_dispatcher')->dispatch(AccessEvents::LOGOUT_SUCCESS, $event);
            } else {
                $this->addFlash('error', $this->__('Error! You have not been logged out.'));
            }
        }

        return isset($returnUrl) ? $this->redirect($returnUrl) : $this->redirectToRoute('home', ['_locale' => $this->container->getParameter('locale')]);
    }
}
