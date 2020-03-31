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

namespace Zikula\ZAuthModule\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Translation\Extractor\Annotation\Desc;
use Zikula\Bundle\CoreBundle\Controller\AbstractController;
use Zikula\Bundle\CoreBundle\Event\GenericEvent;
use Zikula\Bundle\CoreBundle\Filter\AlphaFilter;
use Zikula\Bundle\CoreBundle\Response\PlainResponse;
use Zikula\Bundle\HookBundle\Dispatcher\HookDispatcherInterface;
use Zikula\Bundle\HookBundle\Hook\ProcessHook;
use Zikula\Bundle\HookBundle\Hook\ValidationHook;
use Zikula\Bundle\HookBundle\Hook\ValidationProviders;
use Zikula\Component\SortableColumns\Column;
use Zikula\Component\SortableColumns\SortableColumns;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\GroupsModule\Entity\GroupEntity;
use Zikula\PermissionsModule\Annotation\PermissionCheck;
use Zikula\ThemeModule\Engine\Annotation\Theme;
use Zikula\UsersModule\Api\ApiInterface\CurrentUserApiInterface;
use Zikula\UsersModule\Collector\AuthenticationMethodCollector;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\UsersModule\Entity\RepositoryInterface\UserRepositoryInterface;
use Zikula\UsersModule\Entity\UserEntity;
use Zikula\UsersModule\Event\RegistrationPostDeletedEvent;
use Zikula\UsersModule\Event\UserFormAwareEvent;
use Zikula\UsersModule\Event\UserFormDataEvent;
use Zikula\UsersModule\Helper\MailHelper as UsersMailHelper;
use Zikula\UsersModule\Helper\RegistrationHelper;
use Zikula\UsersModule\HookSubscriber\UserManagementUiHooksSubscriber;
use Zikula\UsersModule\RegistrationEvents;
use Zikula\UsersModule\UserEvents;
use Zikula\ZAuthModule\Entity\AuthenticationMappingEntity;
use Zikula\ZAuthModule\Entity\RepositoryInterface\AuthenticationMappingRepositoryInterface;
use Zikula\ZAuthModule\Form\Type\AdminCreatedUserType;
use Zikula\ZAuthModule\Form\Type\AdminModifyUserType;
use Zikula\ZAuthModule\Form\Type\BatchForcePasswordChangeType;
use Zikula\ZAuthModule\Form\Type\SendVerificationConfirmationType;
use Zikula\ZAuthModule\Form\Type\TogglePasswordConfirmationType;
use Zikula\ZAuthModule\Helper\AdministrationActionsHelper;
use Zikula\ZAuthModule\Helper\LostPasswordVerificationHelper;
use Zikula\ZAuthModule\Helper\MailHelper;
use Zikula\ZAuthModule\Helper\RegistrationVerificationHelper;
use Zikula\ZAuthModule\ZAuthConstant;

/**
 * Class UserAdministrationController
 *
 * @Route("/admin")
 */
class UserAdministrationController extends AbstractController
{
    /**
     * @Route("/list/{sort}/{sortdir}/{letter}/{page}", methods = {"GET"}, requirements={"page" = "\d+"})
     * @PermissionCheck("moderate")
     * @Theme("admin")
     * @Template("@ZikulaZAuthModule/UserAdministration/list.html.twig")
     */
    public function listAction(
        Request $request,
        AuthenticationMappingRepositoryInterface $authenticationMappingRepository,
        RouterInterface $router,
        AdministrationActionsHelper $actionsHelper,
        string $sort = 'uid',
        string $sortdir = 'DESC',
        string $letter = 'all',
        int $page = 1
    ): array {
        $sortableColumns = new SortableColumns($router, 'zikulazauthmodule_useradministration_list', 'sort', 'sortdir');
        $sortableColumns->addColumns([new Column('uname'), new Column('uid')]);
        $sortableColumns->setOrderByFromRequest($request);
        $sortableColumns->setAdditionalUrlParameters([
            'letter' => $letter,
            'page' => $page
        ]);

        $filter = [];
        if (!empty($letter) && 'all' !== $letter) {
            $filter['uname'] = ['operator' => 'like', 'operand' => "${letter}%"];
        }
        $pageSize = $this->getVar(ZAuthConstant::MODVAR_ITEMS_PER_PAGE, ZAuthConstant::DEFAULT_ITEMS_PER_PAGE);
        $paginator = $authenticationMappingRepository->query($filter, [$sort => $sortdir], 'and', $page, $pageSize);
        $paginator->setRoute('zikulazauthmodule_useradministration_list');
        $routeParameters = [
            'sort' => $sort,
            'sortdir' => $sortdir,
            'letter' => $letter,
        ];
        $paginator->setRouteParameters($routeParameters);

        return [
            'sort' => $sortableColumns->generateSortableColumns(),
            'actionsHelper' => $actionsHelper,
            'alpha' => new AlphaFilter('zikulazauthmodule_useradministration_list', $routeParameters, $letter),
            'paginator' => $paginator
        ];
    }

    /**
     * Called from UsersModule/Resources/public/js/Zikula.Users.Admin.View.js
     * to populate a username search
     *
     * @Route("/getusersbyfragmentastable", methods = {"POST"}, options={"expose"=true, "i18n"=false})
     */
    public function getUsersByFragmentAsTableAction(
        Request $request,
        AuthenticationMappingRepositoryInterface $authenticationMappingRepository,
        AdministrationActionsHelper $actionsHelper
    ): Response {
        if (!$this->hasPermission('ZikulaZAuthModule', '::', ACCESS_MODERATE)) {
            return new PlainResponse('');
        }
        $fragment = $request->request->get('fragment');
        $filter = [
            'uname' => ['operator' => 'like', 'operand' => $fragment . '%']
        ];
        $mappings = $authenticationMappingRepository->query($filter);

        return $this->render('@ZikulaZAuthModule/UserAdministration/userlist.html.twig', [
            'mappings' => $mappings->getResults(),
            'actionsHelper' => $actionsHelper
        ], new PlainResponse());
    }

    /**
     * @Route("/user/create")
     * @PermissionCheck("admin")
     * @Theme("admin")
     * @Template("@ZikulaZAuthModule/UserAdministration/create.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function createAction(
        Request $request,
        VariableApiInterface $variableApi,
        AuthenticationMethodCollector $authenticationMethodCollector,
        UserRepositoryInterface $userRepository,
        RegistrationHelper $registrationHelper,
        UsersMailHelper $mailHelper,
        EventDispatcherInterface $eventDispatcher,
        HookDispatcherInterface $hookDispatcher
    ) {
        $mapping = new AuthenticationMappingEntity();
        $form = $this->createForm(AdminCreatedUserType::class, $mapping, [
            'minimumPasswordLength' => $variableApi->get('ZikulaZAuthModule', ZAuthConstant::MODVAR_PASSWORD_MINIMUM_LENGTH, ZAuthConstant::PASSWORD_MINIMUM_LENGTH)
        ]);
        $formEvent = new UserFormAwareEvent($form);
        $eventDispatcher->dispatch($formEvent, UserEvents::EDIT_FORM);
        $form->handleRequest($request);

        $hook = new ValidationHook(new ValidationProviders());
        $hookDispatcher->dispatch(UserManagementUiHooksSubscriber::EDIT_VALIDATE, $hook);
        $validators = $hook->getValidators();

        if ($form->isSubmitted() && $form->isValid() && !$validators->hasErrors()) {
            if ($form->get('submit')->isClicked()) {
                $mapping = $form->getData();
                $passToSend = $form['sendpass']->getData() ? $mapping->getPass() : '';
                $authMethodName = (ZAuthConstant::AUTHENTICATION_METHOD_EITHER === $mapping->getMethod()) ? ZAuthConstant::AUTHENTICATION_METHOD_UNAME : $mapping->getMethod();
                $authMethod = $authenticationMethodCollector->get($authMethodName);

                if ($request->hasSession() && ($session = $request->getSession())) {
                    $session->set(ZAuthConstant::MODVAR_EMAIL_VERIFICATION_REQUIRED, ($form['usermustverify']->getData() ? 'Y' : 'N'));
                }

                $userData = $mapping->getUserEntityData();
                if (null === $userData['uid']) {
                    unset($userData['uid']);
                }
                $user = new UserEntity();
                $user->merge($userData);
                $user->setAttribute(UsersConstant::AUTHENTICATION_METHOD_ATTRIBUTE_KEY, $mapping->getMethod());
                $registrationHelper->registerNewUser($user);
                if (UsersConstant::ACTIVATED_PENDING_REG === $user->getActivated()) {
                    $notificationErrors = $mailHelper->createAndSendRegistrationMail($user, $form['usernotification']->getData(), $form['adminnotification']->getData(), $passToSend);
                } else {
                    $notificationErrors = $mailHelper->createAndSendUserMail($user, $form['usernotification']->getData(), $form['adminnotification']->getData(), $passToSend);
                }
                if (!empty($notificationErrors)) {
                    $this->addFlash('error', 'Errors creating user!');
                    $this->addFlash('error', implode('<br />', $notificationErrors));
                }
                $mapping->setUid($user->getUid());
                $mapping->setVerifiedEmail(!$form['usermustverify']->getData());
                if (!$authMethod->register($mapping->toArray())) {
                    $this->addFlash('error', 'The create process failed for an unknown reason.');
                    $userRepository->removeAndFlush($user);
                    $eventDispatcher->dispatch(new RegistrationPostDeletedEvent($user));

                    return $this->redirectToRoute('zikulazauthmodule_useradministration_list');
                }
                $formDataEvent = new UserFormDataEvent($user, $form);
                $eventDispatcher->dispatch($formDataEvent, UserEvents::EDIT_FORM_HANDLE);
                $hook = new ProcessHook($user->getUid());
                $hookDispatcher->dispatch(UserManagementUiHooksSubscriber::EDIT_PROCESS, $hook);
                $eventDispatcher->dispatch(new GenericEvent($user), RegistrationEvents::REGISTRATION_SUCCEEDED);

                if (UsersConstant::ACTIVATED_PENDING_REG === $user->getActivated()) {
                    $this->addFlash('status', 'Done! Created new registration application.');
                } elseif (null !== $user->getActivated()) {
                    $this->addFlash('status', 'Done! Created new user account.');
                } else {
                    $this->addFlash('error', 'Warning! New user information has been saved, however there may have been an issue saving it properly.');
                }

                return $this->redirectToRoute('zikulazauthmodule_useradministration_list');
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', 'Operation cancelled.');
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
     * @Template("@ZikulaZAuthModule/UserAdministration/modify.html.twig")
     *
     * @return array|RedirectResponse
     * @throws AccessDeniedException Thrown if the user hasn't edit permissions for the mapping record
     */
    public function modifyAction(
        Request $request,
        AuthenticationMappingEntity $mapping,
        VariableApiInterface $variableApi,
        EncoderFactoryInterface $encoderFactory,
        UserRepositoryInterface $userRepository,
        AuthenticationMappingRepositoryInterface $authenticationMappingRepository,
        EventDispatcherInterface $eventDispatcher,
        HookDispatcherInterface $hookDispatcher
    ) {
        if (!$this->hasPermission('ZikulaZAuthModule::', $mapping->getUname() . '::' . $mapping->getUid(), ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }
        if (1 === $mapping->getUid()) {
            throw new AccessDeniedException($this->trans("Error! You can't edit the guest account."));
        }

        $form = $this->createForm(AdminModifyUserType::class, $mapping, [
            'minimumPasswordLength' => $variableApi->get('ZikulaZAuthModule', ZAuthConstant::MODVAR_PASSWORD_MINIMUM_LENGTH, ZAuthConstant::PASSWORD_MINIMUM_LENGTH)
        ]);
        $originalMapping = clone $mapping;
        $formEvent = new UserFormAwareEvent($form);
        $eventDispatcher->dispatch($formEvent, UserEvents::EDIT_FORM);
        $form->handleRequest($request);

        $hook = new ValidationHook(new ValidationProviders());
        $hookDispatcher->dispatch(UserManagementUiHooksSubscriber::EDIT_VALIDATE, $hook);
        $validators = $hook->getValidators();

        if ($form->isSubmitted() && $form->isValid() && !$validators->hasErrors()) {
            if ($form->get('submit')->isClicked()) {
                /** @var AuthenticationMappingEntity $mapping */
                $mapping = $form->getData();
                if ($form->get('setpass')->getData()) {
                    $mapping->setPass($encoderFactory->getEncoder($mapping)->encodePassword($mapping->getPass(), null));
                } else {
                    $mapping->setPass($originalMapping->getPass());
                }
                $authenticationMappingRepository->persistAndFlush($mapping);
                /** @var UserEntity $user */
                $user = $userRepository->find($mapping->getUid());
                $user->merge($mapping->getUserEntityData());
                $userRepository->persistAndFlush($user);
                $eventArgs = [
                    'action'    => 'setVar',
                    'field'     => 'uname',
                    'attribute' => null,
                ];
                $eventData = ['old_value' => $originalMapping->getUname()];
                $updateEvent = new GenericEvent($user, $eventArgs, $eventData);
                $eventDispatcher->dispatch($updateEvent, UserEvents::UPDATE_ACCOUNT);

                $formDataEvent = new UserFormDataEvent($user, $form);
                $eventDispatcher->dispatch($formDataEvent, UserEvents::EDIT_FORM_HANDLE);
                $hookDispatcher->dispatch(UserManagementUiHooksSubscriber::EDIT_PROCESS, new ProcessHook($mapping->getUid()));

                $this->addFlash('status', "Done! Saved user's account information.");
            } elseif ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', 'Operation cancelled.');
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
     * @PermissionCheck("moderate")
     * @Theme("admin")
     * @Template("@ZikulaZAuthModule/UserAdministration/verify.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function verifyAction(
        Request $request,
        AuthenticationMappingEntity $mapping,
        AuthenticationMappingRepositoryInterface $authenticationMappingRepository,
        RegistrationVerificationHelper $registrationVerificationHelper
    ) {
        $form = $this->createForm(SendVerificationConfirmationType::class, [
            'mapping' => $mapping->getId()
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('confirm')->isClicked()) {
                /** @var AuthenticationMappingEntity $modifiedMapping */
                $modifiedMapping = $authenticationMappingRepository->find($form->get('mapping')->getData());
                $verificationSent = $registrationVerificationHelper->sendVerificationCode($modifiedMapping);
                if (!$verificationSent) {
                    $this->addFlash('error', $this->trans('Sorry! There was a problem sending a verification code to %sub%.', ['%sub%' => $modifiedMapping->getUname()]));
                } else {
                    $this->addFlash('status', $this->trans('Done! Verification code sent to %sub%.', ['%sub%' => $modifiedMapping->getUname()]));
                }
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', 'Operation cancelled.');
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
     * @throws AccessDeniedException Thrown if the user hasn't moderate permissions for the mapping record
     */
    public function sendConfirmationAction(
        AuthenticationMappingEntity $mapping,
        LostPasswordVerificationHelper $lostPasswordVerificationHelper,
        MailHelper $mailHelper
    ): RedirectResponse {
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
            $this->addFlash('status', $this->trans('Done! The password recovery verification link for %userName% has been sent via e-mail.', ['%userName%' => $mapping->getUname()]));
        }

        return $this->redirectToRoute('zikulazauthmodule_useradministration_list');
    }

    /**
     * @Route("/send-username/{mapping}", requirements={"mapping" = "^[1-9]\d*$"})
     *
     * @throws AccessDeniedException Thrown if the user hasn't moderate permissions for the mapping record
     */
    public function sendUserNameAction(
        AuthenticationMappingEntity $mapping,
        MailHelper $mailHelper
    ): RedirectResponse {
        if (!$this->hasPermission('ZikulaZAuthModule', $mapping->getUname() . '::' . $mapping->getUid(), ACCESS_MODERATE)) {
            throw new AccessDeniedException();
        }
        $mailSent = $mailHelper->sendNotification($mapping->getEmail(), 'lostuname', [
            'uname' => $mapping->getUname(),
            'requestedByAdmin' => true,
        ]);

        if ($mailSent) {
            $this->addFlash('status', $this->trans('Done! The user name for %userName% has been sent via e-mail.', ['%userName%' => $mapping->getUname()]));
        }

        return $this->redirectToRoute('zikulazauthmodule_useradministration_list');
    }

    /**
     * @Route("/toggle-password-change/{user}", requirements={"user" = "^[1-9]\d*$"})
     * @Theme("admin")
     * @Template("@ZikulaZAuthModule/UserAdministration/togglePasswordChange.html.twig")
     *
     * @param Request $request
     * @param UserEntity $user // note: this is intentionally left as UserEntity instead of mapping because of need to access attributes
     *
     * @return array|RedirectResponse
     * @throws AccessDeniedException Thrown if the user hasn't moderate permissions for the user record
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
                    $this->addFlash('success', $this->trans('Done! A password change will no longer be required for %userName%.', ['%userName%' => $user->getUname()]));
                } else {
                    $user->setAttribute(ZAuthConstant::REQUIRE_PASSWORD_CHANGE_KEY, true);
                    $this->addFlash('success', $this->trans('Done! A password change will be required the next time %userName% logs in.', ['%userName%' => $user->getUname()]));
                }
                $this->getDoctrine()->getManager()->flush();
            } elseif ($form->get('cancel')->isClicked()) {
                $this->addFlash('info', 'Operation cancelled.');
            }

            return $this->redirectToRoute('zikulazauthmodule_useradministration_list');
        }

        return [
            'form' => $form->createView(),
            'mustChangePass' => $mustChangePass,
            'user' => $user
        ];
    }

    /**
     * @Route("/batch-force-password-change")
     * @PermissionCheck("admin")
     * @Theme("admin")
     * @Template("@ZikulaZAuthModule/UserAdministration/batchForcePasswordChange.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function batchForcePasswordChangeAction(
        CurrentUserApiInterface $currentUserApi,
        Request $request
    ) {
        $form = $this->createForm(BatchForcePasswordChangeType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('submit')->isClicked()) {
                $users = $this->getUsers($form->get('group')->getData(), $currentUserApi->get('uid'));
                $count = 0;
                foreach ($users as $user) {
                    $user->setAttribute(ZAuthConstant::REQUIRE_PASSWORD_CHANGE_KEY, true);
                    $count++;
                    if (0 === $count % 20) {
                        $this->getDoctrine()->getManager()->flush(); // flush manager every 20 reps
                    }
                }
                $this->getDoctrine()->getManager()->flush(); // flush remaining
                /** @Desc("{count, plural,\n  one   {# user was processed!}\n  other {# users were processed!}\n}") */
                $this->addFlash('info', $this->trans('plural_n.users.processed.', ['%count%' => $count]));
            } elseif ($form->get('cancel')->isClicked()) {
                $this->addFlash('info', 'Operation cancelled.');
            }

            return $this->redirectToRoute('zikulazauthmodule_useradministration_list');
        }

        return [
            'form' => $form->createView(),
        ];
    }

    private function getUsers($selector, int $currentUser)
    {
        if (!in_array($selector, ['all', 'old']) && !is_int($selector)) {
            return [];
        }
        $authenticationMappingRepository = $this->getDoctrine()->getRepository(AuthenticationMappingEntity::class);
        $toUsersArray = function($mappings) {
            $uids = [];
            foreach ($mappings as $mapping) {
                $uids[] = $mapping->getUid();
            }
            $userRepository = $this->getDoctrine()->getRepository(UserEntity::class);

            return $userRepository->findByUids($uids);
        };
        switch ($selector) {
            case 'old':
                $mappings = $authenticationMappingRepository->getByExpiredPasswords();
                $users = $toUsersArray($mappings);
                break;
            case 'all':
                $mappings = $authenticationMappingRepository->findAll();
                $users = $toUsersArray($mappings);
                break;
            default: //group id
                $groupRepository = $this->getDoctrine()->getRepository(GroupEntity::class);
                $group = $groupRepository->find($selector);
                if ($group && $group instanceof GroupEntity) {
                    $users = $group->getUsers();
                    foreach ($users as $user) {
                        if (!$authenticationMappingRepository->getByZikulaId($user->getUid())) {
                            $users->removeElement($user);
                        }
                    }
                    $users = $users->toArray();
                }
        }

        if (is_array($users) && isset($users[$currentUser])) {
            // remove the current user (admin) and guest user if included
            unset($users[$currentUser], $users[1]);
        }

        return $users;
    }
}
