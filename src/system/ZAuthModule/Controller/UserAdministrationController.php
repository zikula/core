<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ZAuthModule\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Bundle\HookBundle\Dispatcher\HookDispatcherInterface;
use Zikula\Bundle\HookBundle\Hook\ProcessHook;
use Zikula\Bundle\HookBundle\Hook\ValidationHook;
use Zikula\Bundle\HookBundle\Hook\ValidationProviders;
use Zikula\Component\SortableColumns\Column;
use Zikula\Component\SortableColumns\SortableColumns;
use Zikula\Core\Controller\AbstractController;
use Zikula\Core\Event\GenericEvent;
use Zikula\Core\Response\PlainResponse;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\ThemeModule\Engine\Annotation\Theme;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\UsersModule\Collector\AuthenticationMethodCollector;
use Zikula\UsersModule\Entity\RepositoryInterface\UserRepositoryInterface;
use Zikula\UsersModule\Entity\UserEntity;
use Zikula\UsersModule\Event\UserFormAwareEvent;
use Zikula\UsersModule\Event\UserFormDataEvent;
use Zikula\UsersModule\Helper\MailHelper as UsersMailHelper;
use Zikula\UsersModule\Helper\RegistrationHelper;
use Zikula\UsersModule\HookSubscriber\UserManagementUiHooksSubscriber;
use Zikula\UsersModule\RegistrationEvents;
use Zikula\UsersModule\UserEvents;
use Zikula\ZAuthModule\Api\ApiInterface\PasswordApiInterface;
use Zikula\ZAuthModule\Entity\AuthenticationMappingEntity;
use Zikula\ZAuthModule\Entity\RepositoryInterface\AuthenticationMappingRepositoryInterface;
use Zikula\ZAuthModule\Form\Type\AdminCreatedUserType;
use Zikula\ZAuthModule\Form\Type\AdminModifyUserType;
use Zikula\ZAuthModule\Form\Type\SendVerificationConfirmationType;
use Zikula\ZAuthModule\Form\Type\TogglePasswordConfirmationType;
use Zikula\ZAuthModule\Helper\AdministrationActionsHelper;
use Zikula\ZAuthModule\Helper\LostPasswordVerificationHelper;
use Zikula\ZAuthModule\Helper\MailHelper;
use Zikula\ZAuthModule\Helper\RegistrationVerificationHelper;
use Zikula\ZAuthModule\ZAuthConstant;

/**
 * Class UserAdministrationController
 * @Route("/admin")
 */
class UserAdministrationController extends AbstractController
{
    /**
     * @Route("/list/{sort}/{sortdir}/{letter}/{startnum}")
     * @Theme("admin")
     * @Template("ZikulaZAuthModule:UserAdministration:list.html.twig")
     *
     * @param Request $request
     * @param AuthenticationMappingRepositoryInterface $authenticationMappingRepository
     * @param RouterInterface $router
     * @param AdministrationActionsHelper $actionsHelper
     * @param string $sort
     * @param string $sortdir
     * @param string $letter
     * @param integer $startnum
     *
     * @return array
     */
    public function listAction(
        Request $request,
        AuthenticationMappingRepositoryInterface $authenticationMappingRepository,
        RouterInterface $router,
        AdministrationActionsHelper $actionsHelper,
        $sort = 'uid',
        $sortdir = 'DESC',
        $letter = 'all',
        $startnum = 0
    ) {
        if (!$this->hasPermission('ZikulaZAuthModule', '::', ACCESS_MODERATE)) {
            throw new AccessDeniedException();
        }
        $startnum = $startnum > 0 ? $startnum - 1 : 0;

        $sortableColumns = new SortableColumns($router, 'zikulazauthmodule_useradministration_list', 'sort', 'sortdir');
        $sortableColumns->addColumns([new Column('uname'), new Column('uid')]);
        $sortableColumns->setOrderByFromRequest($request);
        $sortableColumns->setAdditionalUrlParameters([
            'letter' => $letter,
            'startnum' => $startnum
        ]);

        $filter = [];
        if (!empty($letter) && 'all' != $letter) {
            $filter['uname'] = ['operator' => 'like', 'operand' => "$letter%"];
        }
        $limit = 25;

        $mappings = $authenticationMappingRepository->query($filter, [$sort => $sortdir], $limit, $startnum);

        return [
            'sort' => $sortableColumns->generateSortableColumns(),
            'pager' => [
                'count' => $mappings->count(),
                'limit' => $limit
            ],
            'actionsHelper' => $actionsHelper,
            'mappings' => $mappings
        ];
    }

    /**
     * Called from UsersModule/Resources/public/js/Zikula.Users.Admin.View.js
     * to populate a username search
     *
     * @Route("/getusersbyfragmentastable", methods = {"POST"}, options={"expose"=true})
     *
     * @param Request $request
     * @param AuthenticationMappingRepositoryInterface $authenticationMappingRepository
     * @param AdministrationActionsHelper $actionsHelper
     *
     * @return PlainResponse
     */
    public function getUsersByFragmentAsTableAction(
        Request $request,
        AuthenticationMappingRepositoryInterface $authenticationMappingRepository,
        AdministrationActionsHelper $actionsHelper
    ) {
        if (!$this->hasPermission('ZikulaZAuthModule', '::', ACCESS_MODERATE)) {
            return new PlainResponse('');
        }
        $fragment = $request->request->get('fragment');
        $filter = [
            'uname' => ['operator' => 'like', 'operand' => "$fragment%"]
        ];
        $mappings = $authenticationMappingRepository->query($filter);

        return $this->render('@ZikulaZAuthModule/UserAdministration/userlist.html.twig', [
            'mappings' => $mappings,
            'actionsHelper' => $actionsHelper
        ], new PlainResponse());
    }

    /**
     * @Route("/user/create")
     * @Theme("admin")
     * @Template("ZikulaZAuthModule:UserAdministration:create.html.twig")
     *
     * @param Request $request
     * @param VariableApiInterface $variableApi
     * @param AuthenticationMethodCollector $authenticationMethodCollector
     * @param UserRepositoryInterface $userRepository
     * @param RegistrationHelper $registrationHelper
     * @param UsersMailHelper $mailHelper
     * @param HookDispatcherInterface $hookDispatcher
     *
     * @return array
     */
    public function createAction(
        Request $request,
        VariableApiInterface $variableApi,
        AuthenticationMethodCollector $authenticationMethodCollector,
        UserRepositoryInterface $userRepository,
        RegistrationHelper $registrationHelper,
        UsersMailHelper $mailHelper,
        HookDispatcherInterface $hookDispatcher
    ) {
        if (!$this->hasPermission('ZikulaZAuthModule', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }
        $dispatcher = $this->get('event_dispatcher');

        $mapping = new AuthenticationMappingEntity();
        $form = $this->createForm(AdminCreatedUserType::class, $mapping, [
            'minimumPasswordLength' => $variableApi->get('ZikulaZAuthModule', ZAuthConstant::MODVAR_PASSWORD_MINIMUM_LENGTH, ZAuthConstant::DEFAULT_PASSWORD_MINIMUM_LENGTH)
        ]);
        $formEvent = new UserFormAwareEvent($form);
        $dispatcher->dispatch(UserEvents::EDIT_FORM, $formEvent);
        $form->handleRequest($request);

        $hook = new ValidationHook(new ValidationProviders());
        $hookDispatcher->dispatch(UserManagementUiHooksSubscriber::EDIT_VALIDATE, $hook);
        $validators = $hook->getValidators();

        if ($form->isSubmitted() && $form->isValid() && !$validators->hasErrors()) {
            if ($form->get('submit')->isClicked()) {
                $mapping = $form->getData();
                $passToSend = $form['sendpass']->getData() ? $mapping->getPass() : '';
                $authMethodName = (ZAuthConstant::AUTHENTICATION_METHOD_EITHER == $mapping->getMethod()) ? ZAuthConstant::AUTHENTICATION_METHOD_UNAME : $mapping->getMethod();
                $authMethod = $authenticationMethodCollector->get($authMethodName);
                $user = new UserEntity();
                $user->merge($mapping->getUserEntityData());
                $user->setAttribute(UsersConstant::AUTHENTICATION_METHOD_ATTRIBUTE_KEY, $mapping->getMethod());
                $registrationHelper->registerNewUser($user);
                if (UsersConstant::ACTIVATED_PENDING_REG == $user->getActivated()) {
                    $notificationErrors = $mailHelper->createAndSendRegistrationMail($user, $form['usernotification']->getData(), $form['adminnotification']->getData(), $passToSend);
                } else {
                    $notificationErrors = $mailHelper->createAndSendUserMail($user, $form['usernotification']->getData(), $form['adminnotification']->getData(), $passToSend);
                }
                if (!empty($notificationErrors)) {
                    $this->addFlash('error', $this->__('Errors creating user!'));
                    $this->addFlash('error', implode('<br />', $notificationErrors));
                }
                $mapping->setUid($user->getUid());
                if (!$authMethod->register($mapping->toArray())) {
                    $this->addFlash('error', $this->__('The create process failed for an unknown reason.'));
                    $userRepository->removeAndFlush($user);
                    $dispatcher->dispatch(RegistrationEvents::DELETE_REGISTRATION, new GenericEvent($user->getUid()));

                    return $this->redirectToRoute('zikulazauthmodule_useradministration_list');
                }
                $formDataEvent = new UserFormDataEvent($user, $form);
                $dispatcher->dispatch(UserEvents::EDIT_FORM_HANDLE, $formDataEvent);
                $hook = new ProcessHook($user->getUid());
                $hookDispatcher->dispatch(UserManagementUiHooksSubscriber::EDIT_PROCESS, $hook);
                $dispatcher->dispatch(RegistrationEvents::REGISTRATION_SUCCEEDED, new GenericEvent($user));

                if (UsersConstant::ACTIVATED_PENDING_REG == $user->getActivated()) {
                    $this->addFlash('status', $this->__('Done! Created new registration application.'));
                } elseif (null !== $user->getActivated()) {
                    $this->addFlash('status', $this->__('Done! Created new user account.'));
                } else {
                    $this->addFlash('error', $this->__('Warning! New user information has been saved, however there may have been an issue saving it properly.'));
                }

                return $this->redirectToRoute('zikulazauthmodule_useradministration_list');
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Operation cancelled.'));
            }
        }

        return [
            'form' => $form->createView(),
            'additional_templates' => isset($formEvent) ? $formEvent->getTemplates() : []
        ];
    }

    /**
     * @Route("/user/modify/{mapping}", requirements={"mapping" = "^[1-9]\d*$"})
     * @Theme("admin")
     * @Template("ZikulaZAuthModule:UserAdministration:modify.html.twig")
     *
     * @param Request $request
     * @param AuthenticationMappingEntity $mapping
     * @param VariableApiInterface $variableApi
     * @param PasswordApiInterface $passwordApi
     * @param UserRepositoryInterface $userRepository
     * @param AuthenticationMappingRepositoryInterface $authenticationMappingRepository
     * @param HookDispatcherInterface $hookDispatcher
     *
     * @return array|RedirectResponse
     */
    public function modifyAction(
        Request $request,
        AuthenticationMappingEntity $mapping,
        VariableApiInterface $variableApi,
        PasswordApiInterface $passwordApi,
        UserRepositoryInterface $userRepository,
        AuthenticationMappingRepositoryInterface $authenticationMappingRepository,
        HookDispatcherInterface $hookDispatcher
    ) {
        if (!$this->hasPermission('ZikulaZAuthModule::', $mapping->getUname() . '::' . $mapping->getUid(), ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }
        if (1 === $mapping->getUid()) {
            throw new AccessDeniedException($this->__("Error! You can't edit the guest account."));
        }
        $dispatcher = $this->get('event_dispatcher');

        $form = $this->createForm(AdminModifyUserType::class, $mapping, [
            'minimumPasswordLength' => $variableApi->get('ZikulaZAuthModule', ZAuthConstant::MODVAR_PASSWORD_MINIMUM_LENGTH, ZAuthConstant::DEFAULT_PASSWORD_MINIMUM_LENGTH)
        ]);
        $originalMapping = clone $mapping;
        $formEvent = new UserFormAwareEvent($form);
        $dispatcher->dispatch(UserEvents::EDIT_FORM, $formEvent);
        $form->handleRequest($request);

        $hook = new ValidationHook(new ValidationProviders());
        $hookDispatcher->dispatch(UserManagementUiHooksSubscriber::EDIT_VALIDATE, $hook);
        $validators = $hook->getValidators();

        if ($form->isSubmitted() && $form->isValid() && !$validators->hasErrors()) {
            if ($form->get('submit')->isClicked()) {
                /** @var AuthenticationMappingEntity $mapping */
                $mapping = $form->getData();
                if ($form->get('setpass')->getData()) {
                    $mapping->setPass($passwordApi->getHashedPassword($mapping->getPass()));
                } else {
                    $mapping->setPass($originalMapping->getPass());
                }
                $authenticationMappingRepository->persistAndFlush($mapping);
                $userEntity = $userRepository->find($mapping->getUid());
                $userEntity->merge($mapping->getUserEntityData());
                $userRepository->persistAndFlush($userEntity);
                $eventArgs = [
                    'action'    => 'setVar',
                    'field'     => 'uname',
                    'attribute' => null,
                ];
                $eventData = ['old_value' => $originalMapping->getUname()];
                $updateEvent = new GenericEvent($userEntity, $eventArgs, $eventData);
                $dispatcher->dispatch(UserEvents::UPDATE_ACCOUNT, $updateEvent);

                $formDataEvent = new UserFormDataEvent($userEntity, $form);
                $dispatcher->dispatch(UserEvents::EDIT_FORM_HANDLE, $formDataEvent);
                $hookDispatcher->dispatch(UserManagementUiHooksSubscriber::EDIT_PROCESS, new ProcessHook($mapping->getUid()));

                $this->addFlash('status', $this->__("Done! Saved user's account information."));
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Operation cancelled.'));
            }

            return $this->redirectToRoute('zikulazauthmodule_useradministration_list');
        }

        return [
            'form' => $form->createView(),
            'additional_templates' => isset($formEvent) ? $formEvent->getTemplates() : []
        ];
    }

    /**
     * @Route("/verify/{mapping}", requirements={"mapping" = "^[1-9]\d*$"})
     * @Theme("admin")
     * @Template("ZikulaZAuthModule:UserAdministration:verify.html.twig")
     *
     * @param Request $request
     * @param AuthenticationMappingEntity $mapping
     * @param AuthenticationMappingRepositoryInterface $authenticationMappingRepository
     * @param RegistrationVerificationHelper $registrationVerificationHelper
     *
     * @return array
     */
    public function verifyAction(
        Request $request,
        AuthenticationMappingEntity $mapping,
        AuthenticationMappingRepositoryInterface $authenticationMappingRepository,
        RegistrationVerificationHelper $registrationVerificationHelper
    ) {
        if (!$this->hasPermission('ZikulaZAuthModule', '::', ACCESS_MODERATE)) {
            throw new AccessDeniedException();
        }
        $form = $this->createForm(SendVerificationConfirmationType::class, [
            'mapping' => $mapping->getId()
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('confirm')->isClicked()) {
                $mapping = $authenticationMappingRepository->find($form->get('mapping')->getData());
                $verificationSent = $registrationVerificationHelper->sendVerificationCode($mapping);
                if (!$verificationSent) {
                    $this->addFlash('error', $this->__f('Sorry! There was a problem sending a verification code to %sub%.', ['%sub%' => $mapping->getUname()]));
                } else {
                    $this->addFlash('status', $this->__f('Done! Verification code sent to %sub%.', ['%sub%' => $mapping->getUname()]));
                }
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Operation cancelled.'));
            }

            return $this->redirectToRoute('zikulazauthmodule_useradministration_list');
        }

        return [
            'form' => $form->createView(),
            'mapping' => $mapping
        ];
    }

    /**
     * @Route("/send-confirmation/{mapping}", requirements={"mapping" = "^[1-9]\d*$"})
     *
     * @param Request $request
     * @param AuthenticationMappingEntity $mapping
     * @param LostPasswordVerificationHelper $lostPasswordVerificationHelper
     * @param MailHelper $mailHelper
     *
     * @return RedirectResponse
     */
    public function sendConfirmationAction(
        Request $request,
        AuthenticationMappingEntity $mapping,
        LostPasswordVerificationHelper $lostPasswordVerificationHelper,
        MailHelper $mailHelper
    ) {
        if (!$this->hasPermission('ZikulaZAuthModule', $mapping->getUname() . '::' . $mapping->getUid(), ACCESS_MODERATE)) {
            throw new AccessDeniedException();
        }
        $changePasswordExpireDays = $this->getVar(ZAuthConstant::MODVAR_EXPIRE_DAYS_CHANGE_PASSWORD, ZAuthConstant::DEFAULT_EXPIRE_DAYS_CHANGE_PASSWORD);
        $lostPasswordId = $lostPasswordVerificationHelper->createLostPasswordId($mapping);
        $mailSent = $mailHelper->sendNotification($mapping->getEmail(), 'lostpassword', [
            'uname' => $mapping->getUname(),
            'validDays' => $changePasswordExpireDays,
            'lostPasswordId' => $lostPasswordId,
            'requestedByAdmin' => true
        ]);
        if ($mailSent) {
            $this->addFlash('status', $this->__f('Done! The password recovery verification link for %s has been sent via e-mail.', ['%s' => $mapping->getUname()]));
        }

        return $this->redirectToRoute('zikulazauthmodule_useradministration_list');
    }

    /**
     * @Route("/send-username/{mapping}", requirements={"mapping" = "^[1-9]\d*$"})
     *
     * @param Request $request
     * @param AuthenticationMappingEntity $mapping
     * @param MailHelper $mailHelper
     *
     * @return RedirectResponse
     */
    public function sendUserNameAction(
        Request $request,
        AuthenticationMappingEntity $mapping,
        MailHelper $mailHelper
    ) {
        if (!$this->hasPermission('ZikulaZAuthModule', $mapping->getUname() . '::' . $mapping->getUid(), ACCESS_MODERATE)) {
            throw new AccessDeniedException();
        }
        $mailSent = $mailHelper->sendNotification($mapping->getEmail(), 'lostuname', [
            'uname' => $mapping->getUname(),
            'requestedByAdmin' => true,
        ]);

        if ($mailSent) {
            $this->addFlash('status', $this->__f('Done! The user name for %s has been sent via e-mail.', ['%s' => $mapping->getUname()]));
        }

        return $this->redirectToRoute('zikulazauthmodule_useradministration_list');
    }

    /**
     * @Route("/toggle-password-change/{user}", requirements={"user" = "^[1-9]\d*$"})
     * @Theme("admin")
     * @Template("ZikulaZAuthModule:UserAdministration:togglePasswordChange.html.twig")
     *
     * @param Request $request
     * @param UserEntity $user // note: this is intentionally left as UserEntity instead of mapping because of need to access attributes
     *
     * @return array|RedirectResponse
     */
    public function togglePasswordChangeAction(Request $request, UserEntity $user)
    {
        if (!$this->hasPermission('ZikulaZAuthModule', $user->getUname() . '::' . $user->getUid(), ACCESS_MODERATE)) {
            throw new AccessDeniedException();
        }
        if ($user->getAttributes()->containsKey(ZAuthConstant::REQUIRE_PASSWORD_CHANGE_KEY)) {
            $mustChangePass = $user->getAttributes()->get(ZAuthConstant::REQUIRE_PASSWORD_CHANGE_KEY);
        } else {
            $mustChangePass = false;
        }
        $form = $this->createForm(TogglePasswordConfirmationType::class, [
            'uid' => $user->getUid()
        ], [
            'mustChangePass' => $mustChangePass
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('toggle')->isClicked()) {
                if ($user->getAttributes()->containsKey(ZAuthConstant::REQUIRE_PASSWORD_CHANGE_KEY) && (bool)$user->getAttributes()->get(ZAuthConstant::REQUIRE_PASSWORD_CHANGE_KEY)) {
                    $user->getAttributes()->remove(ZAuthConstant::REQUIRE_PASSWORD_CHANGE_KEY);
                    $this->addFlash('success', $this->__f('Done! A password change will no longer be required for %uname.', ['%uname' => $user->getUname()]));
                } else {
                    $user->setAttribute(ZAuthConstant::REQUIRE_PASSWORD_CHANGE_KEY, true);
                    $this->addFlash('success', $this->__f('Done! A password change will be required the next time %uname logs in.', ['%uname' => $user->getUname()]));
                }
                $this->get('doctrine')->getManager()->flush();
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('info', $this->__('Operation cancelled.'));
            }

            return $this->redirectToRoute('zikulazauthmodule_useradministration_list');
        }

        return [
            'form' => $form->createView(),
            'mustChangePass' => $mustChangePass,
            'user' => $user
        ];
    }
}
