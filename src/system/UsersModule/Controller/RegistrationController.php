<?php
/**
 * Copyright Zikula Foundation 2016 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\UsersModule\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Bundle\HookBundle\Hook\ProcessHook;
use Zikula\Bundle\HookBundle\Hook\ValidationHook;
use Zikula\Bundle\HookBundle\Hook\ValidationProviders;
use Zikula\Core\Controller\AbstractController;
use Zikula\UsersModule\AuthenticationMethodInterface\NonReEntrantAuthenticationMethodInterface;
use Zikula\UsersModule\AuthenticationMethodInterface\ReEntrantAuthenticationMethodInterface;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\Core\Event\GenericEvent;
use Zikula\UsersModule\Container\HookContainer;
use Zikula\UsersModule\Entity\UserEntity;
use Zikula\UsersModule\RegistrationEvents;

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
     * @return array
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
        // @todo see AccessController::loginAction for code duplication
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
        if (!isset($authenticationMethodId)) {
            $redirectUri = $this->generateUrl('zikulausersmodule_registration_register', [], UrlGeneratorInterface::ABSOLUTE_URL);
            $uid = $authenticationMethod->authenticate(['redirectUri' => $redirectUri]);
            if (isset($uid)) {
                throw new \Exception('User already exists!');
            }
            if ($authenticationMethod instanceof ReEntrantAuthenticationMethodInterface) {
                $request->getSession()->set('authenticationMethodId', $authenticationMethod->getId());
                $userEntity = new UserEntity();
                $authenticationMethod->updateUserEntity($userEntity);
                $validationErrors = $this->get('validator')->validate($userEntity); // Symfony\Component\Validator\ConstraintViolation[]
                $hasListeners = $this->get('event_dispatcher')->hasListeners(RegistrationEvents::NEW_FORM);
                $hookBindings = $this->get('hook_dispatcher')->getBindingsFor('subscriber.users.ui_hooks.registration');
                if (!$hasListeners && count($validationErrors) == 0 && count($hookBindings) == 0) {
                    // @todo need to check anti-spam question exists here? And therefore must be asked?
                    // @todo !!! process registration - no further user interaction needed
                }
            }
        }

        $formClassName = ($authenticationMethod instanceof NonReEntrantAuthenticationMethodInterface)
            ? $authenticationMethod->getRegistrationFormClassName()
            : 'Zikula\UsersModule\Form\Type\DefaultRegistrationType';
        $form = $this->createForm($formClassName);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = new GenericEvent($form->getData(), [], new ValidationProviders());
            $validators = $this->get('event_dispatcher')->dispatch(RegistrationEvents::NEW_VALIDATE, $event)->getData();

            // Validate the hook
            $hook = new ValidationHook($validators);
            $this->get('hook_dispatcher')->dispatch(HookContainer::REGISTRATION_VALIDATE, $hook);
            $validators = $hook->getValidators();

            if ($form->get('submit')->isClicked() && !$validators->hasErrors()) {
                $formData = $form->getData();
                $userEntity = new UserEntity();
                $userEntity->merge($formData['user']);
                $notificationErrors = $this->get('zikula_users_module.helper.registration_helper')->registerNewUser($userEntity);

                if (!empty($notificationErrors)) {
                    // The main registration process failed.
                    $this->addFlash('error', $this->__('Error! Could not create the new user account or registration application. Please check with a site administrator before re-registering.'));
                    foreach ($notificationErrors as $notificationError) {
                        $this->addFlash('error', $notificationError);
                    }
                    $event = $this->get('event_dispatcher')->dispatch(RegistrationEvents::REGISTRATION_FAILED, new GenericEvent(null, ['redirectUrl' => '']));
                    $redirectUrl = $event->hasArgument('redirectUrl') ? $event->getArgument('redirectUrl') : '';

                    return !empty($redirectUrl) ? $this->redirect($redirectUrl) : $this->redirectToRoute('home');
                } else {
                    // The main registration completed successfully.
                    $formData['id'] = $authenticationMethodId;
                    $formData['uid'] = $userEntity->getUid();
                    $authenticationMethod->register($formData);
                    // @todo if register fails, then we have to handle the failure.
                    // Allow hook-like events to process the registration...
                    $this->get('event_dispatcher')->dispatch(RegistrationEvents::NEW_PROCESS, new GenericEvent($userEntity));
                    // ...and hooks to process the registration.
                    $this->get('hook_dispatcher')->dispatch(HookContainer::REGISTRATION_PROCESS, new ProcessHook($userEntity->getUid()));

                    // Register the appropriate status or error to be displayed to the user, depending on the account's activated status.
                    $canLogIn = $userEntity->getActivated() == UsersConstant::ACTIVATED_ACTIVE;
                    $autoLogIn = $this->getVar(UsersConstant::MODVAR_REGISTRATION_AUTO_LOGIN, UsersConstant::DEFAULT_REGISTRATION_AUTO_LOGIN);
                    $this->generateRegistrationFlashMessage($userEntity->getActivated(), $autoLogIn);

                    // Notify that we are completing a registration session.
                    $event = $this->get('event_dispatcher')->dispatch(RegistrationEvents::REGISTRATION_SUCCEEDED, new GenericEvent($userEntity, ['redirectUrl' => '']));
                    $redirectUrl = $event->hasArgument('redirectUrl') ? $event->getArgument('redirectUrl') : '';

                    if ($autoLogIn && $this->get('zikula_users_module.helper.access_helper')->loginAllowed($userEntity)) {
                        $this->get('zikula_users_module.helper.access_helper')->login($userEntity, $selectedMethod);
                    } elseif (!empty($redirectUrl)) {
                        return $this->redirect($redirectUrl);
                    } elseif (!$canLogIn) {
                        return $this->redirectToRoute('home');
                    } else {
                        return $this->redirectToRoute('zikulausersmodule_access_login');
                    }
                }
            }

            return !empty($redirectUrl) ? $this->redirect($redirectUrl) : $this->redirectToRoute('home');
        }

        // Notify that we are beginning a registration session.
        $this->get('event_dispatcher')->dispatch(RegistrationEvents::REGISTRATION_STARTED, new GenericEvent());

        $templateName = ($authenticationMethod instanceof NonReEntrantAuthenticationMethodInterface)
            ? $authenticationMethod->getRegistrationTemplateName()
            : '@ZikulaUsersModule\Registration\defaultRegister.html.twig';

        return $this->render($templateName, [
            'form' => $form->createView(),
            'modvars' => $this->getVars()
        ]);
    }

    /**
     * Throw an exception if the user agent has been banned in the UserModule settings.
     *
     * @param Request $request
     * @throws AccessDeniedException if User Agent is banned.
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
