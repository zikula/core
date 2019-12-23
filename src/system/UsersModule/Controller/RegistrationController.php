<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Controller;

use Exception;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\LegacyEventDispatcherProxy;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Zikula\Bundle\HookBundle\Dispatcher\HookDispatcherInterface;
use Zikula\Bundle\HookBundle\Hook\ProcessHook;
use Zikula\Bundle\HookBundle\Hook\ValidationHook;
use Zikula\Bundle\HookBundle\Hook\ValidationProviders;
use Zikula\Core\Controller\AbstractController;
use Zikula\Core\Event\GenericEvent;
use Zikula\UsersModule\Api\ApiInterface\CurrentUserApiInterface;
use Zikula\UsersModule\AuthenticationMethodInterface\NonReEntrantAuthenticationMethodInterface;
use Zikula\UsersModule\AuthenticationMethodInterface\ReEntrantAuthenticationMethodInterface;
use Zikula\UsersModule\Collector\AuthenticationMethodCollector;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\UsersModule\Entity\RepositoryInterface\UserRepositoryInterface;
use Zikula\UsersModule\Entity\UserEntity;
use Zikula\UsersModule\Event\UserFormAwareEvent;
use Zikula\UsersModule\Event\UserFormDataEvent;
use Zikula\UsersModule\Exception\InvalidAuthenticationMethodRegistrationFormException;
use Zikula\UsersModule\Form\Type\RegistrationType\DefaultRegistrationType;
use Zikula\UsersModule\Helper\AccessHelper;
use Zikula\UsersModule\Helper\RegistrationHelper;
use Zikula\UsersModule\HookSubscriber\RegistrationUiHooksSubscriber;
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
     * @Route("/register", methods = {"GET", "POST"}, options={"zkNoBundlePrefix"=1})
     *
     * @return Response|RedirectResponse
     *
     * @throws InvalidAuthenticationMethodRegistrationFormException
     * @throws Exception
     */
    public function registerAction(
        Request $request,
        CurrentUserApiInterface $currentUserApi,
        UserRepositoryInterface $userRepository,
        AuthenticationMethodCollector $authenticationMethodCollector,
        RegistrationHelper $registrationHelper,
        AccessHelper $accessHelper,
        EventDispatcherInterface $eventDispatcher,
        HookDispatcherInterface $hookDispatcher,
        ValidatorInterface $validator
    ) {
        if ($currentUserApi->isLoggedIn()) {
            return $this->redirectToRoute('zikulausersmodule_account_menu');
        }
        // Check if registration is enabled
        if (!$this->getVar(UsersConstant::MODVAR_REGISTRATION_ENABLED, UsersConstant::DEFAULT_REGISTRATION_ENABLED)) {
            return $this->render('@ZikulaUsersModule/Registration/registration_disabled.html.twig');
        }
        $this->throwExceptionForBannedUserAgents($request);

        $session = $request->hasSession() ? $request->getSession() : null;

        // Display the authentication method selector if required
        $selectedMethod = null !== $session ? $session->get('authenticationMethod') : '';
        $selectedMethod = $request->query->get('authenticationMethod', $selectedMethod);
        if (empty($selectedMethod) && count($authenticationMethodCollector->getActiveKeys()) > 1) {
            return $this->render('@ZikulaUsersModule/Access/authenticationMethodSelector.html.twig', [
                'collector' => $authenticationMethodCollector,
                'path' => 'zikulausersmodule_registration_register'
            ]);
        }
        if (empty($selectedMethod) && 1 === count($authenticationMethodCollector->getActiveKeys())) {
            $selectedMethod = $authenticationMethodCollector->getActiveKeys()[0];
        }
        if (null !== $session) {
            $session->set('authenticationMethod', $selectedMethod); // save method to session for reEntrant needs
        }

        $authenticationMethod = $authenticationMethodCollector->get($selectedMethod);
        $authenticationMethodId = null;
        if (null !== $session) {
            $session->get('authenticationMethodId');
        }

        // authenticate user if required && check to make sure user doesn't already exist.
        $userData = [];
        if (!isset($authenticationMethodId)) {
            $redirectUri = $this->generateUrl('zikulausersmodule_registration_register', [], UrlGeneratorInterface::ABSOLUTE_URL);
            $uid = $authenticationMethod->authenticate(['redirectUri' => $redirectUri]);
            if (isset($uid)) {
                throw new Exception('User already exists!');
            }
            if ($authenticationMethod instanceof ReEntrantAuthenticationMethodInterface) {
                $authenticationMethodId = $authenticationMethod->getId();
                if (null !== $session) {
                    $session->set('authenticationMethodId', $authenticationMethodId);
                }
                $userData = [
                    'uname' => $authenticationMethod->getUname(),
                    'email' => $authenticationMethod->getEmail()
                ];
            }
        }

        $formClassName = ($authenticationMethod instanceof NonReEntrantAuthenticationMethodInterface)
            ? $authenticationMethod->getRegistrationFormClassName()
            : DefaultRegistrationType::class;
        $form = $this->createForm($formClassName, $userData);
        if (!$form->has('uname') || !$form->has('email')) {
            throw new InvalidAuthenticationMethodRegistrationFormException();
        }
        $eventDispatcher = LegacyEventDispatcherProxy::decorate($eventDispatcher);
        $hasListeners = $eventDispatcher->hasListeners(UserEvents::EDIT_FORM);
        $hookBindings = $hookDispatcher->getBindingsFor('subscriber.users.ui_hooks.registration');
        if ($authenticationMethod instanceof ReEntrantAuthenticationMethodInterface && !empty($userData) && !$hasListeners && 0 === count($hookBindings)) {
            // skip form display and process immediately.
            $userData['_token'] = $this->get('security.csrf.token_manager')->getToken($form->getName())->getValue();
            $userData['submit'] = true;
            $form->submit($userData);
        } else {
            $formEvent = new UserFormAwareEvent($form);
            $eventDispatcher->dispatch($formEvent, UserEvents::EDIT_FORM);
            $form->handleRequest($request);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('submit')->isClicked()) {
                // Validate the hook
                $hook = new ValidationHook(new ValidationProviders());
                $hookDispatcher->dispatch(RegistrationUiHooksSubscriber::REGISTRATION_VALIDATE, $hook);
                $validators = $hook->getValidators();

                if (!$validators->hasErrors()) {
                    $formData = $form->getData();
                    $userEntity = new UserEntity();
                    $userEntity->setUname($formData['uname']);
                    $userEntity->setEmail($formData['email']);
                    $userEntity->setAttribute(UsersConstant::AUTHENTICATION_METHOD_ATTRIBUTE_KEY, $authenticationMethod->getAlias());
                    $validationErrors = $validator->validate($userEntity);
                    if (count($validationErrors) > 0) {
                        foreach ($validationErrors as $validationError) {
                            $this->addFlash('error', $validationError->getMessage());
                        }

                        return $this->redirectToRoute('zikulausersmodule_registration_register'); // try again.
                    }

                    $registrationHelper->registerNewUser($userEntity);

                    $formData['id'] = $authenticationMethodId;
                    $formData['uid'] = $userEntity->getUid();
                    $externalRegistrationSuccess = $authenticationMethod->register($formData);
                    if (true !== $externalRegistrationSuccess) {
                        // revert registration
                        $this->addFlash('error', $this->__('The registration process failed.'));
                        $userRepository->removeAndFlush($userEntity);
                        $eventDispatcher->dispatch(new GenericEvent($userEntity->getUid()), RegistrationEvents::DELETE_REGISTRATION);

                        return $this->redirectToRoute('zikulausersmodule_registration_register'); // try again.
                    }
                    $formDataEvent = new UserFormDataEvent($userEntity, $form);
                    $eventDispatcher->dispatch($formDataEvent, UserEvents::EDIT_FORM_HANDLE);
                    $hookDispatcher->dispatch(RegistrationUiHooksSubscriber::REGISTRATION_PROCESS, new ProcessHook($userEntity->getUid()));

                    // Register the appropriate status or error to be displayed to the user, depending on the account's activated status.
                    $canLogIn = UsersConstant::ACTIVATED_ACTIVE === $userEntity->getActivated();
                    $autoLogIn = $this->getVar(UsersConstant::MODVAR_REGISTRATION_AUTO_LOGIN, UsersConstant::DEFAULT_REGISTRATION_AUTO_LOGIN);
                    $this->generateRegistrationFlashMessage($userEntity->getActivated(), $autoLogIn);

                    // Notify that we are completing a registration session.
                    $event = $eventDispatcher->dispatch(new GenericEvent($userEntity, ['redirectUrl' => '']), RegistrationEvents::REGISTRATION_SUCCEEDED);
                    $redirectUrl = $event->hasArgument('redirectUrl') ? $event->getArgument('redirectUrl') : '';

                    if ($autoLogIn && $accessHelper->loginAllowed($userEntity)) {
                        $accessHelper->login($userEntity);

                        return !empty($redirectUrl) ? $this->redirect($redirectUrl) : $this->redirectToRoute('home');
                    }
                    if (!empty($redirectUrl)) {
                        return $this->redirect($redirectUrl);
                    }
                    if (!$canLogIn) {
                        return $this->redirectToRoute('home');
                    }

                    return $this->redirectToRoute('zikulausersmodule_access_login');
                }
            }
            if (null !== $session) {
                $session->invalidate();
            }

            return !empty($redirectUrl) ? $this->redirect($redirectUrl) : $this->redirectToRoute('home');
        }

        // Notify that we are beginning a registration session.
        $eventDispatcher->dispatch(new GenericEvent(), RegistrationEvents::REGISTRATION_STARTED);

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
     * @throws AccessDeniedException if User Agent is banned
     */
    private function throwExceptionForBannedUserAgents(Request $request): void
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
     */
    private function generateRegistrationFlashMessage(int $activatedStatus, bool $autoLogIn = false): void
    {
        if (UsersConstant::ACTIVATED_PENDING_REG === $activatedStatus) {
            $this->addFlash('status', $this->__('Done! Your registration request has been saved and is pending. Please check your e-mail periodically for a message from us.'));
        } elseif (UsersConstant::ACTIVATED_ACTIVE === $activatedStatus) {
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
