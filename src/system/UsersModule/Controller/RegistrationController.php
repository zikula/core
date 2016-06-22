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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
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
     * @Template
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
        $userEntity = new UserEntity();

        // authenticate user if required && check to make sure user doesn't already exist.
        if (!isset($authenticationMethodId)) {
            $redirectUri = $this->generateUrl('zikulausersmodule_registration_register', [], UrlGeneratorInterface::ABSOLUTE_URL);
            $uid = $authenticationMethod->authenticate(['redirectUri' => $redirectUri]);
            if (isset($uid)) {
                throw new \Exception('User already exists!');
            }
            if ($authenticationMethod instanceof ReEntrantAuthenticationMethodInterface) {
                $request->getSession()->set('authenticationMethodId', $authenticationMethod->getId());
                $authenticationMethod->updateUserEntity($userEntity);
                $validationErrors = $this->get('validator')->validate($userEntity); // Symfony\Component\Validator\ConstraintViolation[]
                $hasListeners = $this->get('event_dispatcher')->hasListeners(RegistrationEvents::NEW_FORM);
                $hookBindings = $this->get('hook_dispatcher')->getBindingsFor('subscriber.users.ui_hooks.registration');
                if (!$hasListeners && count($validationErrors) == 0 && count($hookBindings) == 0) {
                    // @todo !!! process registration - no further user interaction needed
                }
            }
        }

        $formClassName = ($authenticationMethod instanceof NonReEntrantAuthenticationMethodInterface)
            ? $authenticationMethod->getRegistrationFormClassName()
            : 'Zikula\UsersModule\Form\Type\DefaultRegistrationType';
        $form = $this->createForm($formClassName, $userEntity);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $event = new GenericEvent($form->getData(), [], new ValidationProviders());
            $validators = $this->get('event_dispatcher')->dispatch(RegistrationEvents::NEW_VALIDATE, $event)->getData();

            // Validate the hook
            $hook = new ValidationHook($validators);
            $this->get('hook_dispatcher')->dispatch(HookContainer::REGISTRATION_VALIDATE, $hook);
            $validators = $hook->getValidators();

            if ($form->get('submit')->isClicked() && $form->isValid() && !$validators->hasErrors()) {
                /** @var UserEntity $userEntity */ // @todo maybe this shouldn't be a UserEntity, but simply an array $formData
                $userEntity = $form->getData();
                // save pass and passreminder since they are emptied in next func @todo refactor
                $pass = $userEntity->getPass();
                $passReminder = $userEntity->getPassreminder();
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
                    // @todo may be easier just to pass the entire $formData array here (amending the authenticationMethodId)
                    $authenticationMethod->register([
                        'id' => $authenticationMethodId, // comes from session and earlier authentication
                        'uid' => $userEntity->getUid(),
                        'pass' => $pass,
                        'passreminder' => $passReminder,
                        'email' => $userEntity->getEmail(),
                        'uname' => $userEntity->getUname()
                    ]);
                    // Allow hook-like events to process the registration...
                    $this->get('event_dispatcher')->dispatch(RegistrationEvents::NEW_PROCESS, new GenericEvent($userEntity));
                    // ...and hooks to process the registration.
                    $this->get('hook_dispatcher')->dispatch(HookContainer::REGISTRATION_PROCESS, new ProcessHook($userEntity->getUid()));

                    // Register the appropriate status or error to be displayed to the user, depending on the account's
                    // activated status, whether registrations are moderated, whether e-mail addresses need to be verified, etc.
                    $canLogIn = $userEntity->getActivated() == UsersConstant::ACTIVATED_ACTIVE;
                    $autoLogIn = $this->getVar(UsersConstant::MODVAR_REGISTRATION_AUTO_LOGIN, UsersConstant::DEFAULT_REGISTRATION_AUTO_LOGIN);
                    $this->generateRegistrationFlashMessage($userEntity->getActivated(), $autoLogIn);

                    // Notify that we are completing a registration session.
                    $event = $this->get('event_dispatcher')->dispatch(RegistrationEvents::REGISTRATION_SUCCEEDED, new GenericEvent($userEntity, ['redirectUrl' => '']));
                    $redirectUrl = $event->hasArgument('redirectUrl') ? $event->getArgument('redirectUrl') : '';

                    if ($autoLogIn && $this->get('zikula_users_module.helper.access_helper')->loginAllowed($userEntity, $selectedMethod)) {
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
            if ($form->get('cancel')->isClicked()) {
                $request->getSession()->clear();
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
     * @Route("/verify-registration/{uname}/{verifycode}")
     * @Template
     * @param Request $request
     * @param null|string $uname
     * @param null|string $verifycode
     *
     * Render and process a registration e-mail verification code.
     *
     * This function will render and display to the user a form allowing him to enter
     * a verification code sent to him as part of the registration process. If the user's
     * registration does not have a password set (e.g., if an admin created the registration),
     * then he is prompted for it at this time. This function also processes the results of
     * that form, setting the registration record to verified (if appropriate), saving the password
     * (if provided) and if the registration record is also approved (or does not require it)
     * then a new user account is created.
     *
     * @return array
     */
    public function verifyAction(Request $request, $uname = null, $verifycode = null)
    {
        if ($this->get('zikula_users_module.current_user')->isLoggedIn()) {
            return $this->redirectToRoute('zikulausersmodule_account_menu');
        }

        $setPass = false;
        $this->get('zikula_users_module.helper.registration_helper')->purgeExpired(); // remove expired registrations
        $userEntity = $this->get('zikula_users_module.user_repository')->findOneBy(['uname' => $uname]);
        if ($userEntity) {
            $setPass = null == $userEntity->getPass() || '' == $userEntity->getPass();
        }
        $form = $this->createForm('Zikula\UsersModule\Form\Type\VerifyRegistrationType',
            [
                'uname' => $uname,
                'verifycode' => $verifycode
            ],
            [
                'translator' => $this->getTranslator(),
                'setpass' => $setPass,
                'passwordReminderEnabled' => $this->getVar(UsersConstant::MODVAR_PASSWORD_REMINDER_ENABLED),
                'passwordReminderMandatory' => $this->getVar(UsersConstant::MODVAR_PASSWORD_REMINDER_MANDATORY)
            ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $userEntity = $this->get('zikula_users_module.user_repository')->find($reginfo['uid']);
            if (isset($data['pass'])) {
                $userEntity->setPass($data['pass']); // temp set to unhashed - will be hashed in registerNewUser() method
            }
            $userEntity->setAttribute('_Users_isVerified', 1);
            if ($this->getVar(UsersConstant::MODVAR_PASSWORD_REMINDER_ENABLED) && isset($data['passreminder'])) {
                $userEntity->setPassreminder($data['passreminder']);
            }
            $this->get('zikula_users_module.helper.registration_helper')->registerNewUser($userEntity, false, true, false, false);
            $this->get('zikula_users_module.user_verification_repository')->resetVerifyChgFor($userEntity->getUid(), UsersConstant::VERIFYCHGTYPE_REGEMAIL);

            switch ($userEntity->getActivated()) {
                case UsersConstant::ACTIVATED_PENDING_REG:
                    if ('' == $userEntity->getApproved_By()) {
                        $this->addFlash('status', $this->__('Done! Your account has been verified, and is awaiting administrator approval.'));
                    } else {
                        $this->addFlash('status', $this->__('Done! Your account has been verified. Your registration request is still pending completion. Please contact the site administrator for more information.'));
                    }
                    break;
                case UsersConstant::ACTIVATED_ACTIVE:
                    if ($userEntity->getPass() != UsersConstant::PWD_NO_USERS_AUTHENTICATION) {
                        // The users module was used to register that account.
                        $this->addFlash('status', $this->__('Done! Your account has been verified. You may now log in with your user name and password.'));
                    } else {
                        // A third party module was used to register that account.
                        $this->addFlash('status', $this->__('Done! Your account has been verified. You may now log in.'));
                    }

                    return $this->redirectToRoute('zikulausersmodule_access_login');
                    break;
                default:
                    $this->addFlash('status', $this->__('Done! Your account has been verified.'));
                    $this->addFlash('status', $this->__('Your new account is not active yet. Please contact the site administrator for more information.'));
                    break;
            }
        }

        return [
            'form' => $form->createView(),
            'modvars' => $this->getVars()
        ];
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
            // The account is saved and is pending either moderator approval, e-mail verification, or both.
            $moderation = $this->getVar(UsersConstant::MODVAR_REGISTRATION_APPROVAL_REQUIRED, UsersConstant::DEFAULT_REGISTRATION_APPROVAL_REQUIRED);
            $moderationOrder = $this->getVar(UsersConstant::MODVAR_REGISTRATION_APPROVAL_SEQUENCE, UsersConstant::DEFAULT_REGISTRATION_APPROVAL_SEQUENCE);
            $verifyEmail = $this->getVar(UsersConstant::MODVAR_REGISTRATION_VERIFICATION_MODE, UsersConstant::DEFAULT_REGISTRATION_VERIFICATION_MODE);

            if ($moderation && ($verifyEmail != UsersConstant::VERIFY_NO)) {
                // Pending both moderator approval, and e-mail verification. Set the appropriate message
                // based on the order of approval/verification set.
                if ($moderationOrder == UsersConstant::APPROVAL_AFTER) {
                    // Verification then approval.
                    $this->addFlash('status', $this->__('Done! Your registration request has been saved. Remember that your e-mail address must be verified and your request must be approved before you will be able to log in. Please check your e-mail for an e-mail address verification message. Your account will not be approved until after the verification process is completed.'));
                } elseif ($moderationOrder == UsersConstant::APPROVAL_BEFORE) {
                    // Approval then verification.
                    $this->addFlash('status', $this->__('Done! Your registration request has been saved. Remember that your request must be approved and your e-mail address must be verified before you will be able to log in. Please check your e-mail periodically for a message from us. You will receive a message after we have reviewed your request.'));
                } else {
                    // Approval and verification in any order.
                    $this->addFlash('status', $this->__('Done! Your registration request has been saved. Remember that your e-mail address must be verified and your request must be approved before you will be able to log in. Please check your e-mail for an e-mail address verification message.'));
                }
            } elseif ($moderation) {
                // Pending moderator approval only.
                $this->addFlash('status', $this->__('Done! Your registration request has been saved. Remember that your request must be approved before you will be able to log in. Please check your e-mail periodically for a message from us. You will receive a message after we have reviewed your request.'));
            } elseif ($verifyEmail != UsersConstant::VERIFY_NO) {
                // Pending e-mail address verification only.
                $this->addFlash('status', $this->__('Done! Your registration request has been saved. Remember that your e-mail address must be verified before you will be able to log in. Please check your e-mail for an e-mail address verification message.'));
            } else {
                // Some unknown state! Should never get here, but just in case...
                $this->addFlash('error', $this->__('Your registration request has been saved, however your current registration status could not be determined. Please contact the site administrator regarding the status of your request.'));
            }
        } elseif ($activatedStatus == UsersConstant::ACTIVATED_ACTIVE) {
            // The account is saved, and is active (no moderator approval, no e-mail verification, and the user can log in now).
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
