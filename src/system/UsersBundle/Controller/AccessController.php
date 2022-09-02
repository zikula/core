<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersBundle\Controller;

use LogicException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\UsersBundle\Api\ApiInterface\CurrentUserApiInterface;
use Zikula\UsersBundle\AuthenticationMethodInterface\NonReEntrantAuthenticationMethodInterface;
use Zikula\UsersBundle\AuthenticationMethodInterface\ReEntrantAuthenticationMethodInterface;
use Zikula\UsersBundle\Collector\AuthenticationMethodCollector;
use Zikula\UsersBundle\Entity\User;
use Zikula\UsersBundle\Event\LoginFormPostCreatedEvent;
use Zikula\UsersBundle\Event\LoginFormPostValidatedEvent;
use Zikula\UsersBundle\Event\UserPostLoginFailureEvent;
use Zikula\UsersBundle\Event\UserPostLoginSuccessEvent;
use Zikula\UsersBundle\Event\UserPostLogoutSuccessEvent;
use Zikula\UsersBundle\Event\UserPreLoginSuccessEvent;
use Zikula\UsersBundle\Exception\InvalidAuthenticationMethodLoginFormException;
use Zikula\UsersBundle\Form\Type\DefaultLoginType;
use Zikula\UsersBundle\Helper\AccessHelper;
use Zikula\UsersBundle\Helper\RegistrationHelper;
use Zikula\UsersBundle\Repository\UserRepositoryInterface;
use Zikula\UsersBundle\UsersConstant;

class AccessController extends AbstractController
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    /**
     * @throws InvalidAuthenticationMethodLoginFormException
     */
    #[Route('/login', name: 'zikulausersbundle_access_login')]
    public function login(
        Request $request,
        CurrentUserApiInterface $currentUserApi,
        UserRepositoryInterface $userRepository,
        AuthenticationMethodCollector $authenticationMethodCollector,
        AccessHelper $accessHelper,
        EventDispatcherInterface $eventDispatcher,
        RegistrationHelper $registrationHelper
    ): Response {
        if ($currentUserApi->isLoggedIn()) {
            return $this->redirectToRoute('zikulausersbundle_account_menu');
        }
        $returnUrl = $request->query->get('returnUrl', '');

        $session = $request->hasSession() ? $request->getSession() : null;

        $selectedMethod = null !== $session ? $session->get('authenticationMethod') : '';
        $selectedMethod = $request->query->get('authenticationMethod', $selectedMethod);
        if (empty($selectedMethod) && count($authenticationMethodCollector->getActiveKeys()) > 1) {
            // there are multiple authentication methods available and none selected yet, so let the user choose one
            return $this->render('@ZikulaUsers/Access/authenticationMethodSelector.html.twig', [
                'collector' => $authenticationMethodCollector,
                'path' => 'zikulausersbundle_access_login'
            ]);
        }
        if (empty($selectedMethod) && 1 === count($authenticationMethodCollector->getActiveKeys())) {
            // there is only one authentication method available, so use this
            $selectedMethod = $authenticationMethodCollector->getActiveKeys()[0];
        }
        if (null !== $session) {
            // save method to session for reEntrant needs
            $session->set('authenticationMethod', $selectedMethod);
            if (!empty($returnUrl)) {
                // save returnUrl to session for reEntrant needs
                $session->set('returnUrl', $returnUrl);
            }
        }

        $authenticationMethod = $authenticationMethodCollector->get($selectedMethod);
        $rememberMe = false;

        $loginHeader = $this->renderView('@ZikulaUsers/Access/loginHeader.html.twig');
        $loginFooter = $this->renderView('@ZikulaUsers/Access/loginFooter.html.twig');

        $form = null;
        if ($authenticationMethod instanceof NonReEntrantAuthenticationMethodInterface) {
            $form = $this->createForm($authenticationMethod->getLoginFormClassName());
            if (!$form->has('rememberme')) {
                throw new InvalidAuthenticationMethodLoginFormException();
            }
            $loginFormEvent = new LoginFormPostCreatedEvent($form);
            $eventDispatcher->dispatch($loginFormEvent);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $data = $form->getData();
                $rememberMe = $data['rememberme'];
                $uid = $authenticationMethod->authenticate($data);
            } else {
                return $this->render($authenticationMethod->getLoginTemplateName(), [
                    'loginHeader' => $loginHeader,
                    'loginFooter' => $loginFooter,
                    'form' => $form->createView(),
                    'additionalTemplates' => isset($loginFormEvent) ? $loginFormEvent->getTemplates() : [],
                    'registrationEnabled' => $registrationHelper->isRegistrationEnabled(),
                ]);
            }
        } elseif ($authenticationMethod instanceof ReEntrantAuthenticationMethodInterface) {
            // provide temp value for uid until form gives real value.
            $uid = Request::METHOD_POST === $request->getMethod() ? UsersConstant::USER_ID_ANONYMOUS : $authenticationMethod->authenticate();
            $hasListeners = $eventDispatcher->hasListeners(LoginFormPostCreatedEvent::class);
            if ($hasListeners) {
                $form = $this->createForm(DefaultLoginType::class, ['uid' => $uid]);
                $loginFormEvent = new LoginFormPostCreatedEvent($form);
                $eventDispatcher->dispatch($loginFormEvent);
                if ($form->count() > 3) { // count > 3 means that the LoginFormPostCreatedEvent event added some form children
                    $form->handleRequest($request);
                    if ($form->isSubmitted() && $form->isValid()) {
                        $uid = $form->get('uid')->getData();
                        $rememberMe = $form->get('rememberme')->getData();
                    } else {
                        return $this->render('@ZikulaUsers/Access/defaultLogin.html.twig', [
                            'loginHeader' => $loginHeader,
                            'loginFooter' => $loginFooter,
                            'form' => $form->createView(),
                            'additionalTemplates' => isset($loginFormEvent) ? $loginFormEvent->getTemplates() : []
                        ]);
                    }
                }
            }
        } else {
            throw new LogicException($this->translator->trans('Invalid authentication method.'));
        }
        $user = null;
        if (isset($uid)) {
            /** @var User $user */
            $user = $userRepository->find($uid);
            if (isset($user)) {
                if ($accessHelper->loginAllowed($user)) {
                    if (isset($form)) {
                        $formDataEvent = new LoginFormPostValidatedEvent($form, $user);
                        $eventDispatcher->dispatch($formDataEvent);
                    }
                    $userPreSuccessLoginEvent = new UserPreLoginSuccessEvent($user, $selectedMethod);
                    $eventDispatcher->dispatch($userPreSuccessLoginEvent);
                    if (!$userPreSuccessLoginEvent->isPropagationStopped()) {
                        $returnUrlFromSession = null !== $session ? $session->get('returnUrl', $returnUrl) : $returnUrl;
                        $returnUrlFromSession = urldecode($returnUrlFromSession);
                        $accessHelper->login($user, $rememberMe);
                        $userPostSuccessLoginEvent = new UserPostLoginSuccessEvent($user, $selectedMethod);
                        $userPostSuccessLoginEvent->setRedirectUrl($returnUrlFromSession);
                        $eventDispatcher->dispatch($userPostSuccessLoginEvent);
                        $returnUrl = $userPostSuccessLoginEvent->getRedirectUrl();
                    } else {
                        if ($userPreSuccessLoginEvent->hasFlashes()) {
                            $this->addFlash('danger', $userPreSuccessLoginEvent->getFlashesAsString());
                        }
                        $returnUrl = $userPreSuccessLoginEvent->getRedirectUrl();
                    }

                    return !empty($returnUrl) ? $this->redirect($this->sanitizeReturnUrl($request, $returnUrl)) : $this->redirectToRoute('home');
                }
            }
        }
        // login failed
        $this->addFlash('error', 'Login failed.');
        if (null !== $session) {
            $session->remove('authenticationMethod');
        }
        $userPostFailLoginEvent = new UserPostLoginFailureEvent($user, $authenticationMethod->getAlias());
        $userPostFailLoginEvent->setRedirectUrl($returnUrl);
        $eventDispatcher->dispatch($userPostFailLoginEvent);
        $returnUrl = $userPostFailLoginEvent->getRedirectUrl();

        return !empty($returnUrl) ? $this->redirect($this->sanitizeReturnUrl($request, $returnUrl)) : $this->redirectToRoute('home');
    }

    #[Route('/logout/{returnUrl}', name: 'zikulausersbundle_access_logout')]
    public function logout(
        Request $request,
        CurrentUserApiInterface $currentUserApi,
        UserRepositoryInterface $userRepository,
        AccessHelper $accessHelper,
        EventDispatcherInterface $eventDispatcher,
        string $returnUrl = null
    ): RedirectResponse {
        if ($currentUserApi->isLoggedIn()) {
            $uid = $currentUserApi->get('uid');
            $user = $userRepository->find($uid);
            if ($accessHelper->logout()) {
                $eventDispatcher->dispatch(new UserPostLogoutSuccessEvent($user));
            } else {
                $this->addFlash('error', 'Error! You have not been logged out.');
            }
        }

        return isset($returnUrl)
            ? $this->redirect($this->sanitizeReturnUrl($request, $returnUrl))
            : $this->redirectToRoute('home', ['_locale' => $this->getParameter('locale')])
        ;
    }

    private function sanitizeReturnUrl(Request $request, $returnUrl = null)
    {
        if (null === $returnUrl || empty($returnUrl)) {
            return $returnUrl;
        }

        if (false !== mb_strpos($returnUrl, $request->getUriForPath(''))) {
            return $returnUrl;
        }

        if ('/' !== mb_substr($returnUrl, 0, 1)) {
            $returnUrl = '/' . $returnUrl;
        }

        return $request->getUriForPath($returnUrl);
    }
}
