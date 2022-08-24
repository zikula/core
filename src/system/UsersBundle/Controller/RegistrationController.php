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

use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\UsersBundle\Api\ApiInterface\CurrentUserApiInterface;
use Zikula\UsersBundle\AuthenticationMethodInterface\NonReEntrantAuthenticationMethodInterface;
use Zikula\UsersBundle\AuthenticationMethodInterface\ReEntrantAuthenticationMethodInterface;
use Zikula\UsersBundle\Collector\AuthenticationMethodCollector;
use Zikula\UsersBundle\Entity\UserEntity;
use Zikula\UsersBundle\Event\EditUserFormPostCreatedEvent;
use Zikula\UsersBundle\Event\EditUserFormPostValidatedEvent;
use Zikula\UsersBundle\Event\RegistrationPostDeletedEvent;
use Zikula\UsersBundle\Event\RegistrationPostSuccessEvent;
use Zikula\UsersBundle\Exception\InvalidAuthenticationMethodRegistrationFormException;
use Zikula\UsersBundle\Form\Type\RegistrationType\DefaultRegistrationType;
use Zikula\UsersBundle\Helper\AccessHelper;
use Zikula\UsersBundle\Helper\RegistrationHelper;
use Zikula\UsersBundle\Repository\UserRepositoryInterface;
use Zikula\UsersBundle\UsersConstant;
use Zikula\UsersBundle\Validator\Constraints\ValidUserFieldsValidator;

class RegistrationController extends AbstractController
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly bool $registrationRequiresApproval,
        private readonly ?string $registrationDisabledReason,
        private readonly bool $useAutoLogin,
        private readonly ?string $illegalUserAgents
    ) {
    }

    /**
     * Display the registration form.
     *
     * @return Response|RedirectResponse
     *
     * @throws InvalidAuthenticationMethodRegistrationFormException
     * @throws Exception
     */
    #[Route('/register', name: 'zikulausersbundle_registration_register', methods: ['GET', 'POST'])]
    public function register(
        Request $request,
        CurrentUserApiInterface $currentUserApi,
        UserRepositoryInterface $userRepository,
        AuthenticationMethodCollector $authenticationMethodCollector,
        RegistrationHelper $registrationHelper,
        AccessHelper $accessHelper,
        EventDispatcherInterface $eventDispatcher,
        ValidatorInterface $validator
    ) {
        if ($currentUserApi->isLoggedIn()) {
            return $this->redirectToRoute('zikulausersbundle_account_menu');
        }
        // Check if registration is enabled
        if (!$registrationHelper->isRegistrationEnabled()) {
            return $this->render('@ZikulaUsers/Registration/registration_disabled.html.twig', [
                'reason' => $this->registrationDisabledReason,
            ]);
        }
        $this->throwExceptionForBannedUserAgents($request);

        $session = $request->hasSession() ? $request->getSession() : null;

        // Display the authentication method selector if required
        $selectedMethod = null !== $session ? $session->get('authenticationMethod') : '';
        $selectedMethod = $request->query->get('authenticationMethod', $selectedMethod);
        if (empty($selectedMethod) && 1 < count($authenticationMethodCollector->getActiveKeys())) {
            return $this->render('@ZikulaUsers/Access/authenticationMethodSelector.html.twig', [
                'collector' => $authenticationMethodCollector,
                'path' => 'zikulausersbundle_registration_register',
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
            $authenticationMethodId = $session->get('authenticationMethodId');
        }

        // authenticate user if required && check to make sure user doesn't already exist.
        $userData = [];
        if (!isset($authenticationMethodId)) {
            $redirectUri = $this->generateUrl('zikulausersbundle_registration_register', [], UrlGeneratorInterface::ABSOLUTE_URL);
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
        $hasListeners = $eventDispatcher->hasListeners(EditUserFormPostCreatedEvent::class);
        if ($authenticationMethod instanceof ReEntrantAuthenticationMethodInterface && !empty($userData) && !$hasListeners) {
            // skip form display and process immediately.
            $userData['_token'] = $this->get('security.csrf.token_manager')->getToken($form->getName())->getValue();
            $userData['submit'] = true;
            $form->submit($userData);
        } else {
            $editUserFormPostCreatedEvent = new EditUserFormPostCreatedEvent($form);
            $eventDispatcher->dispatch($editUserFormPostCreatedEvent);
            $form->handleRequest($request);
        }

        if ($form->isSubmitted()) {
            if ($form->has('cancel') && $form->get('cancel')->isClicked()) {
                return $this->redirectToRoute('home');
            }
        }
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('submit')->isClicked()) {
                $formData = $form->getData();
                $userEntity = new UserEntity();
                $userEntity->setUname($formData['uname']);
                $userEntity->setEmail($formData['email']);
                $userEntity->setLocale($request->getLocale());
                $userEntity->setAttribute(UsersConstant::AUTHENTICATION_METHOD_ATTRIBUTE_KEY, $authenticationMethod->getAlias());
                $validationErrors = $validator->validate($userEntity);
                if (count($validationErrors) > 0) {
                    $codes = [];
                    /** @var ConstraintViolationInterface $validationError */
                    foreach ($validationErrors as $validationError) {
                        $this->addFlash('error', $validationError->getMessage());
                        $codes[] = $validationError->getCode();
                    }
                    if ($authenticationMethod instanceof ReEntrantAuthenticationMethodInterface) {
                        $session->remove('authenticationMethod');
                    }
                    $route = 'zikulausersbundle_registration_register';
                    if (in_array(ValidUserFieldsValidator::DUP_EMAIL_ALT_AUTH, $codes, true)) {
                        $route = 'zikulausersbundle_access_login';
                    }

                    return $this->redirectToRoute($route); // try again.
                }

                $registrationHelper->registerNewUser($userEntity);

                $formData['id'] = $authenticationMethodId;
                $formData['uid'] = $userEntity->getUid();
                $externalRegistrationSuccess = $authenticationMethod->register($formData);
                if (true !== $externalRegistrationSuccess) {
                    // revert registration
                    $this->addFlash('error', 'The registration process failed.');
                    $userRepository->removeAndFlush($userEntity);
                    $eventDispatcher->dispatch(new RegistrationPostDeletedEvent($userEntity));

                    return $this->redirectToRoute('zikulausersbundle_registration_register'); // try again.
                }
                $eventDispatcher->dispatch(new EditUserFormPostValidatedEvent($form, $userEntity));

                // Register the appropriate status or error to be displayed to the user, depending on the account's activated status.
                $canLogIn = UsersConstant::ACTIVATED_ACTIVE === $userEntity->getActivated();
                $this->generateRegistrationFlashMessage($userEntity->getActivated(), $this->useAutoLogin);

                // Notify that we are completing a registration session.
                $eventDispatcher->dispatch($event = new RegistrationPostSuccessEvent($userEntity));
                $redirectUrl = $event->getRedirectUrl();

                if ($this->useAutoLogin && $accessHelper->loginAllowed($userEntity)) {
                    $accessHelper->login($userEntity);

                    return !empty($redirectUrl) ? $this->redirect($redirectUrl) : $this->redirectToRoute('home');
                }
                if (!empty($redirectUrl)) {
                    return $this->redirect($redirectUrl);
                }
                if (!$canLogIn) {
                    return $this->redirectToRoute('home');
                }

                return $this->redirectToRoute('zikulausersbundle_access_login');
            }
            if (null !== $session) {
                $session->invalidate();
            }

            return $this->redirectToRoute('home');
        }

        $templateName = $authenticationMethod instanceof NonReEntrantAuthenticationMethodInterface
            ? $authenticationMethod->getRegistrationTemplateName()
            : '@ZikulaUsersBundle\Registration\defaultRegister.html.twig';

        return $this->render($templateName, [
            'form' => $form->createView(),
            'registrationRequiresApproval' => $this->registrationRequiresApproval,
            'additionalTemplates' => isset($editUserFormPostCreatedEvent) ? $editUserFormPostCreatedEvent->getTemplates() : []
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
        $illegalUserAgents = $this->illegalUserAgents ?? '';
        // Convert the comma-separated list into a regexp pattern.
        $pattern = ['/^(\s*,\s*)+/D', '/\b(\s*,\s*)+\b/D', '/(\s*,\s*)+$/D'];
        $replace = ['', '|', ''];
        $illegalUserAgents = preg_replace($pattern, $replace, preg_quote($illegalUserAgents, '/'));
        // Check for emptiness here, in case there were just spaces and commas in the original string.
        if (!empty($illegalUserAgents) && preg_match("/^({$illegalUserAgents})/iD", $userAgent)) {
            throw new AccessDeniedException($this->translator->trans('Sorry! The user agent you are using (the browser or other software you are using to access this site) is banned from the registration process.'));
        }
    }

    /**
     * Add flash message to session based on registration results.
     */
    private function generateRegistrationFlashMessage(int $activatedStatus, bool $autoLogIn = false): void
    {
        if (UsersConstant::ACTIVATED_PENDING_REG === $activatedStatus) {
            $this->addFlash('status', 'Done! Your registration request has been saved and is pending. Please check your e-mail periodically for a message from us.');
        } elseif (UsersConstant::ACTIVATED_ACTIVE === $activatedStatus) {
            // The account is saved, and is active.
            if ($autoLogIn) {
                // No errors and auto-login is turned on. A simple post-log-in message.
                $this->addFlash('status', 'Done! Your account has been created.');
            } else {
                // No errors, and no auto-login. A simple message telling the user he may log in.
                $this->addFlash('status', 'Done! Your account has been created and you may now log in.');
            }
        }
    }
}
