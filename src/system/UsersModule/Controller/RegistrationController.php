<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Bundle\HookBundle\Hook\ProcessHook;
use Zikula\Bundle\HookBundle\Hook\ValidationHook;
use Zikula\Bundle\HookBundle\Hook\ValidationProviders;
use Zikula\Core\Controller\AbstractController;
use Zikula\Core\Event\GenericEvent;
use Zikula\UsersModule\AuthenticationMethodInterface\NonReEntrantAuthenticationMethodInterface;
use Zikula\UsersModule\AuthenticationMethodInterface\ReEntrantAuthenticationMethodInterface;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\UsersModule\Container\HookContainer;
use Zikula\UsersModule\Entity\UserEntity;
use Zikula\UsersModule\Event\UserFormAwareEvent;
use Zikula\UsersModule\Event\UserFormDataEvent;
use Zikula\UsersModule\Exception\InvalidAuthenticationMethodRegistrationFormException;
use Zikula\UsersModule\RegistrationEvents;
use Zikula\UsersModule\UserEvents;

/**
 * Class RegistrationController
 * @Route("")
 */
class RegistrationController extends AbstractController
{
    /**
     * Display the registration form.
     *
     * @Route("/register", options={"zkNoBundlePrefix"=1})
     * @Method({"GET", "POST"})
     * @param Request $request
     * @return Response|RedirectResponse
     * @throws InvalidAuthenticationMethodRegistrationFormException
     * @throws \Exception
     */
    public function registerAction(Request $request)
    {
        if ($this->get('zikula_users_module.current_user')->isLoggedIn()) {
            return $this->redirectToRoute('zikulausersmodule_account_menu');
        }
        // Check if registration is enabled
        if (!$this->getVar(UsersConstant::MODVAR_REGISTRATION_ENABLED, UsersConstant::DEFAULT_REGISTRATION_ENABLED)) {
            return $this->render('@ZikulaUsersModule/Registration/registration_disabled.html.twig');
        }
        $this->throwExceptionForBannedUserAgents($request);

        // Display the authentication method selector if required
        $authenticationMethodCollector = $this->get('zikula_users_module.internal.authentication_method_collector');
        $selectedMethod = $request->query->get('authenticationMethod', $request->getSession()->get('authenticationMethod', null));
        if (empty($selectedMethod) && count($authenticationMethodCollector->getActiveKeys()) > 1) {
            return $this->render('@ZikulaUsersModule/Access/authenticationMethodSelector.html.twig', [
                'collector' => $authenticationMethodCollector,
                'path' => 'zikulausersmodule_registration_register'
            ]);
        } else {
            if (empty($selectedMethod) && count($authenticationMethodCollector->getActiveKeys()) == 1) {
                $selectedMethod = $authenticationMethodCollector->getActiveKeys()[0];
            }
            $request->getSession()->set('authenticationMethod', $selectedMethod); // save method to session for reEntrant needs
        }
        $authenticationMethod = $authenticationMethodCollector->get($selectedMethod);
        $authenticationMethodId = $request->getSession()->get('authenticationMethodId');

        // authenticate user if required && check to make sure user doesn't already exist.
        $userData = [];
        if (!isset($authenticationMethodId)) {
            $redirectUri = $this->generateUrl('zikulausersmodule_registration_register', [], UrlGeneratorInterface::ABSOLUTE_URL);
            $uid = $authenticationMethod->authenticate(['redirectUri' => $redirectUri]);
            if (isset($uid)) {
                throw new \Exception('User already exists!');
            }
            if ($authenticationMethod instanceof ReEntrantAuthenticationMethodInterface) {
                $authenticationMethodId = $authenticationMethod->getId();
                $request->getSession()->set('authenticationMethodId', $authenticationMethodId);
                $userData = [
                    'uname' => $authenticationMethod->getUname(),
                    'email' => $authenticationMethod->getEmail()
                ];
            }
        }
        $dispatcher = $this->get('event_dispatcher');
        $hookDispatcher = $this->get('hook_dispatcher');

        $formClassName = ($authenticationMethod instanceof NonReEntrantAuthenticationMethodInterface)
            ? $authenticationMethod->getRegistrationFormClassName()
            : 'Zikula\UsersModule\Form\RegistrationType\DefaultRegistrationType';
        $form = $this->createForm($formClassName, $userData);
        if (!$form->has('uname') || !$form->has('email')) {
            throw new InvalidAuthenticationMethodRegistrationFormException();
        }
        $hasListeners = $dispatcher->hasListeners(UserEvents::EDIT_FORM);
        $hookBindings = $hookDispatcher->getBindingsFor('subscriber.users.ui_hooks.registration');
        if ($authenticationMethod instanceof ReEntrantAuthenticationMethodInterface && !empty($userData) && !$hasListeners && count($hookBindings) == 0) {
            // skip form display and process immediately.
            $userData['_token'] = $this->get('security.csrf.token_manager')->getToken($form->getName())->getValue();
            $userData['submit'] = true;
            $form->submit($userData);
        } else {
            $formEvent = new UserFormAwareEvent($form);
            $dispatcher->dispatch(UserEvents::EDIT_FORM, $formEvent);
            $form->handleRequest($request);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('submit')->isClicked()) {
                // Validate the hook
                $hook = new ValidationHook(new ValidationProviders());
                $hookDispatcher->dispatch(HookContainer::REGISTRATION_VALIDATE, $hook);
                $validators = $hook->getValidators();

                if (!$validators->hasErrors()) {
                    $formData = $form->getData();
                    $userEntity = new UserEntity();
                    $userEntity->setUname($formData['uname']);
                    $userEntity->setEmail($formData['email']);
                    $userEntity->setAttribute(UsersConstant::AUTHENTICATION_METHOD_ATTRIBUTE_KEY, $authenticationMethod->getAlias());
                    $validationErrors = $this->get('validator')->validate($userEntity);
                    if (count($validationErrors) > 0) {
                        foreach ($validationErrors as $validationError) {
                            $this->addFlash('error', $validationError->getMessage());
                        }

                        return $this->redirectToRoute('zikulausersmodule_registration_register'); // try again.
                    }

                    $this->get('zikula_users_module.helper.registration_helper')->registerNewUser($userEntity);

                    $formData['id'] = $authenticationMethodId;
                    $formData['uid'] = $userEntity->getUid();
                    $externalRegistrationSuccess = $authenticationMethod->register($formData);
                    if (true !== $externalRegistrationSuccess) {
                        // revert registration
                        $this->addFlash('error', $this->__('The registration process failed.'));
                        $this->get('zikula_users_module.user_repository')->removeAndFlush($userEntity);
                        $dispatcher->dispatch(RegistrationEvents::DELETE_REGISTRATION, new GenericEvent($userEntity->getUid()));

                        return $this->redirectToRoute('zikulausersmodule_registration_register'); // try again.
                    }
                    $formDataEvent = new UserFormDataEvent($userEntity, $form);
                    $dispatcher->dispatch(UserEvents::EDIT_FORM_HANDLE, $formDataEvent);
                    $hookDispatcher->dispatch(HookContainer::REGISTRATION_PROCESS, new ProcessHook($userEntity->getUid()));

                    // Register the appropriate status or error to be displayed to the user, depending on the account's activated status.
                    $canLogIn = $userEntity->getActivated() == UsersConstant::ACTIVATED_ACTIVE;
                    $autoLogIn = $this->getVar(UsersConstant::MODVAR_REGISTRATION_AUTO_LOGIN, UsersConstant::DEFAULT_REGISTRATION_AUTO_LOGIN);
                    $this->generateRegistrationFlashMessage($userEntity->getActivated(), $autoLogIn);

                    // Notify that we are completing a registration session.
                    $event = $dispatcher->dispatch(RegistrationEvents::REGISTRATION_SUCCEEDED, new GenericEvent($userEntity, ['redirectUrl' => '']));
                    $redirectUrl = $event->hasArgument('redirectUrl') ? $event->getArgument('redirectUrl') : '';

                    if ($autoLogIn && $this->get('zikula_users_module.helper.access_helper')->loginAllowed($userEntity)) {
                        $this->get('zikula_users_module.helper.access_helper')->login($userEntity);
                    } elseif (!empty($redirectUrl)) {
                        return $this->redirect($redirectUrl);
                    } elseif (!$canLogIn) {
                        return $this->redirectToRoute('home');
                    } else {
                        return $this->redirectToRoute('zikulausersmodule_access_login');
                    }
                }
            }
            $request->getSession()->invalidate();

            return !empty($redirectUrl) ? $this->redirect($redirectUrl) : $this->redirectToRoute('home');
        }

        // Notify that we are beginning a registration session.
        $dispatcher->dispatch(RegistrationEvents::REGISTRATION_STARTED, new GenericEvent());

        $templateName = ($authenticationMethod instanceof NonReEntrantAuthenticationMethodInterface)
            ? $authenticationMethod->getRegistrationTemplateName()
            : '@ZikulaUsersModule\Registration\defaultRegister.html.twig';

        return $this->render($templateName, [
            'form' => $form->createView(),
            'additional_templates' => isset($formEvent) ? $formEvent->getTemplates() : []
        ]);
    }

    /**
     * Throw an exception if the user agent has been banned in the UserModule settings.
     *
     * @param Request $request
     * @throws AccessDeniedException if User Agent is banned
     */
    private function throwExceptionForBannedUserAgents(Request $request)
    {
        // Check for illegal user agents trying to register.
        $userAgent = $request->server->get('HTTP_USER_AGENT', '');
        $illegalUserAgents = $this->getVar(UsersConstant::MODVAR_REGISTRATION_ILLEGAL_AGENTS, '');
        // Convert the comma-separated list into a regexp pattern.
        $pattern = ['/^(\s*,\s*)+/D', '/\b(\s*,\s*)+\b/D', '/(\s*,\s*)+$/D'];
        $replace = ['', '|', ''];
        $illegalUserAgents = preg_replace($pattern, $replace, preg_quote($illegalUserAgents, '/'));
        // Check for emptiness here, in case there were just spaces and commas in the original string.
        if (!empty($illegalUserAgents) && preg_match("/^({$illegalUserAgents})/iD", $userAgent)) {
            throw new AccessDeniedException($this->__('Sorry! The user agent you are using (the browser or other software you are using to access this site) is banned from the registration process.'));
        }
    }

    /**
     * Add flash message to session based on registration results.
     *
     * @param bool $activatedStatus
     * @param bool $autoLogIn
     */
    private function generateRegistrationFlashMessage($activatedStatus, $autoLogIn = false)
    {
        if ($activatedStatus == UsersConstant::ACTIVATED_PENDING_REG) {
            $this->addFlash('status', $this->__('Done! Your registration request has been saved and is pending. Please check your e-mail periodically for a message from us.'));
        } elseif ($activatedStatus == UsersConstant::ACTIVATED_ACTIVE) {
            // The account is saved, and is active.
            if ($autoLogIn) {
                // No errors and auto-login is turned on. A simple post-log-in message.
                $this->addFlash('status', $this->__('Done! Your account has been created.'));
            } else {
                // No errors, and no auto-login. A simple message telling the user he may log in.
                $this->addFlash('status', $this->__('Done! Your account has been created and you may now log in.'));
            }
        }
    }
}
