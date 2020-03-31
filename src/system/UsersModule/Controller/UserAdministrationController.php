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

use InvalidArgumentException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
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
use Zikula\GroupsModule\Constant;
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
use Zikula\UsersModule\Form\Type\AdminModifyUserType;
use Zikula\UsersModule\Form\Type\DeleteConfirmationType;
use Zikula\UsersModule\Form\Type\DeleteType;
use Zikula\UsersModule\Form\Type\MailType;
use Zikula\UsersModule\Form\Type\RegistrationType\ApproveRegistrationConfirmationType;
use Zikula\UsersModule\Form\Type\SearchUserType;
use Zikula\UsersModule\Helper\AdministrationActionsHelper;
use Zikula\UsersModule\Helper\MailHelper;
use Zikula\UsersModule\Helper\RegistrationHelper;
use Zikula\UsersModule\HookSubscriber\UserManagementUiHooksSubscriber;
use Zikula\UsersModule\UserEvents;

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
     * @Template("@ZikulaUsersModule/UserAdministration/list.html.twig")
     */
    public function listAction(
        Request $request,
        UserRepositoryInterface $userRepository,
        RouterInterface $router,
        AdministrationActionsHelper $actionsHelper,
        AuthenticationMethodCollector $authenticationMethodCollector,
        string $sort = 'uid',
        string $sortdir = 'DESC',
        string $letter = 'all',
        int $page = 1
    ): array {
        $sortableColumns = new SortableColumns($router, 'zikulausersmodule_useradministration_list', 'sort', 'sortdir');
        $sortableColumns->addColumns([new Column('uname'), new Column('uid'), new Column('registrationDate'), new Column('lastLogin'), new Column('activated')]);
        $sortableColumns->setOrderByFromRequest($request);
        $sortableColumns->setAdditionalUrlParameters([
            'letter' => $letter,
            'page' => $page
        ]);

        $filter = [];
        if (!empty($letter) && 'all' !== $letter) {
            $filter['uname'] = ['operator' => 'like', 'operand' => "${letter}%"];
        }
        $itemsPerPage = $this->getVar(UsersConstant::MODVAR_ITEMS_PER_PAGE, UsersConstant::DEFAULT_ITEMS_PER_PAGE);
        $paginator = $userRepository->query($filter, [$sort => $sortdir], $itemsPerPage, $page);
        $paginator->setRoute('zikulausersmodule_useradministration_list');
        $routeParameters = [
            'sort' => $sort,
            'sortdir' => $sortdir,
            'letter' => $letter,
        ];
        $paginator->setRouteParameters($routeParameters);

        return [
            'sort' => $sortableColumns->generateSortableColumns(),
            'actionsHelper' => $actionsHelper,
            'authMethodCollector' => $authenticationMethodCollector,
            'alpha' => new AlphaFilter('zikulausersmodule_useradministration_list', $routeParameters, $letter),
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
        UserRepositoryInterface $userRepository,
        AdministrationActionsHelper $actionsHelper
    ): Response {
        if (!$this->hasPermission('ZikulaUsersModule', '::', ACCESS_MODERATE)) {
            return new PlainResponse('');
        }
        $fragment = $request->request->get('fragment');
        $filter = [
            'activated' => ['operator' => 'notIn', 'operand' => [
                UsersConstant::ACTIVATED_PENDING_REG,
                UsersConstant::ACTIVATED_PENDING_DELETE
            ]],
            'uname' => ['operator' => 'like', 'operand' => "${fragment}%"]
        ];
        $users = $userRepository->query($filter);

        return $this->render('@ZikulaUsersModule/UserAdministration/userlist.html.twig', [
            'users' => $users,
            'actionsHelper' => $actionsHelper
        ], new PlainResponse());
    }

    /**
     * @Route("/user/modify/{user}", requirements={"user" = "^[1-9]\d*$"})
     * @Theme("admin")
     * @Template("@ZikulaUsersModule/UserAdministration/modify.html.twig")
     *
     * @return array|RedirectResponse
     * @throws AccessDeniedException Thrown if the user hasn't edit permissions for the user record
     */
    public function modifyAction(
        Request $request,
        UserEntity $user,
        CurrentUserApiInterface $currentUserApi,
        VariableApiInterface $variableApi,
        EventDispatcherInterface $eventDispatcher,
        HookDispatcherInterface $hookDispatcher
    ) {
        if (!$this->hasPermission('ZikulaUsersModule::', $user->getUname() . '::' . $user->getUid(), ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }
        if (UsersConstant::USER_ID_ANONYMOUS === $user->getUid()) {
            throw new AccessDeniedException($this->trans("Error! You can't edit the guest account."));
        }

        $form = $this->createForm(AdminModifyUserType::class, $user);
        $originalUserName = $user->getUname();
        $originalGroups = $user->getGroups()->toArray();
        $formEvent = new UserFormAwareEvent($form);
        $eventDispatcher->dispatch($formEvent, UserEvents::EDIT_FORM);
        $form->handleRequest($request);

        $hook = new ValidationHook(new ValidationProviders());
        $hookDispatcher->dispatch(UserManagementUiHooksSubscriber::EDIT_VALIDATE, $hook);
        $validators = $hook->getValidators();

        if ($form->isSubmitted() && $form->isValid() && !$validators->hasErrors()) {
            if ($form->get('submit')->isClicked()) {
                $user = $form->getData();
                $this->checkSelf($currentUserApi, $variableApi, $user, $originalGroups);

                $formDataEvent = new UserFormDataEvent($user, $form);
                $eventDispatcher->dispatch($formDataEvent, UserEvents::EDIT_FORM_HANDLE);

                $this->getDoctrine()->getManager()->flush();

                $eventArgs = [
                    'action'    => 'setVar',
                    'field'     => 'uname',
                    'attribute' => null,
                ];
                $eventData = ['old_value' => $originalUserName];
                $updateEvent = new GenericEvent($user, $eventArgs, $eventData);
                $eventDispatcher->dispatch($updateEvent, UserEvents::UPDATE_ACCOUNT);

                $hookDispatcher->dispatch(UserManagementUiHooksSubscriber::EDIT_PROCESS, new ProcessHook($user->getUid()));

                $this->addFlash('status', "Done! Saved user's account information.");
            } elseif ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', 'Operation cancelled.');
            }

            return $this->redirectToRoute('zikulausersmodule_useradministration_list');
        }

        return [
            'form' => $form->createView(),
            'additional_templates' => isset($formEvent) ? $formEvent->getTemplates() : []
        ];
    }

    /**
     * @Route("/approve/{user}/{force}", requirements={"user" = "^[1-9]\d*$"})
     * @PermissionCheck("moderate")
     * @Theme("admin")
     * @Template("@ZikulaUsersModule/UserAdministration/approve.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function approveAction(
        Request $request,
        UserEntity $user,
        RegistrationHelper $registrationHelper,
        MailHelper $mailHelper,
        bool $force = false
    ) {
        $forceVerification = $this->hasPermission('ZikulaUsersModule', '::', ACCESS_ADMIN) && $force;
        $form = $this->createForm(ApproveRegistrationConfirmationType::class, [
            'user' => $user->getUid(),
            'force' => $forceVerification
        ], [
            'buttonLabel' => $this->trans('Approve')
        ]);
        $redirectToRoute = 'zikulausersmodule_useradministration_list';

        if (!$forceVerification) {
            if ($user->isApproved()) {
                $this->addFlash('error', $this->trans('Warning! Nothing to do! %sub% is already approved.', ['%sub%' => $user->getUname()]));

                return $this->redirectToRoute($redirectToRoute);
            }
            if (!$user->isApproved() && !$this->hasPermission('ZikulaUsersModule::', '::', ACCESS_ADMIN)) {
                $this->addFlash('error', $this->trans('Error! %sub% cannot be approved.', ['%sub%' => $user->getUname()]));

                return $this->redirectToRoute($redirectToRoute);
            }
        }

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('confirm')->isClicked()) {
                $registrationHelper->approve($user);
                if (UsersConstant::ACTIVATED_PENDING_REG === $user->getActivated()) {
                    $notificationErrors = $mailHelper->createAndSendRegistrationMail($user, true, false);
                } else {
                    $notificationErrors = $mailHelper->createAndSendUserMail($user, true, false);
                }

                if ($notificationErrors) {
                    $this->addFlash('error', implode('<br />', $notificationErrors));
                }
                $this->addFlash('status', $this->trans('Done! %sub% has been approved.', ['%sub%' => $user->getUname()]));
            } elseif ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', 'Operation cancelled.');
            }

            return $this->redirectToRoute($redirectToRoute);
        }

        return [
            'form' => $form->createView(),
            'user' => $user
        ];
    }

    /**
     * @Route("/delete/{user}", requirements={"user" = "^[1-9]\d*$"})
     * @PermissionCheck("delete")
     * @Theme("admin")
     * @Template("@ZikulaUsersModule/UserAdministration/delete.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function deleteAction(
        Request $request,
        CurrentUserApiInterface $currentUserApi,
        UserRepositoryInterface $userRepository,
        HookDispatcherInterface $hookDispatcher,
        EventDispatcherInterface $eventDispatcher,
        UserEntity $user = null
    ) {
        $uids = [];
        if (!isset($user) && 'POST' === $request->getMethod() && $request->request->has('zikulausersmodule_delete')) {
            $uids = $request->request->get('zikulausersmodule_delete')['users'];
        } elseif (isset($user)) {
            $uids = [$user->getUid()];
        }
        $usersImploded = implode(',', $uids);

        $deleteConfirmationForm = $this->createForm(DeleteConfirmationType::class, [
            'users' => $usersImploded
        ]);
        $deleteConfirmationForm->handleRequest($request);
        if (empty($uids) && !$deleteConfirmationForm->isSubmitted()) {
            throw new InvalidArgumentException($this->trans('No users selected.'));
        }
        if ($deleteConfirmationForm->isSubmitted()) {
            if ($deleteConfirmationForm->get('cancel')->isClicked()) {
                $this->addFlash('success', 'Operation cancelled.');

                return $this->redirectToRoute('zikulausersmodule_useradministration_list');
            }
            $userIdsImploded = $deleteConfirmationForm->get('users')->getData();
            $userIds = explode(',', $userIdsImploded);
            $valid = true;
            foreach ($userIds as $k => $uid) {
                if (in_array($uid, [UsersConstant::USER_ID_ANONYMOUS, UsersConstant::USER_ID_ADMIN, $currentUserApi->get('uid')], true)) {
                    unset($userIds[$k]);
                    $this->addFlash('danger', $this->trans('You are not allowed to delete user id %uid%', ['%uid%' => $uid]));
                    continue;
                }
                $event = new GenericEvent(null, ['id' => $uid], new ValidationProviders());
                $validators = $eventDispatcher->dispatch($event, UserEvents::DELETE_VALIDATE)->getData();
                $hook = new ValidationHook($validators);
                $hookDispatcher->dispatch(UserManagementUiHooksSubscriber::DELETE_VALIDATE, $hook);
                $validators = $hook->getValidators();
                if ($validators->hasErrors()) {
                    $valid = false;
                }
            }
            if ($valid && $deleteConfirmationForm->isValid()) {
                // send email to 'denied' registrations. see MailHelper::sendNotification (regdeny) #2915
                $deletedUsers = $userRepository->query(['uid' => ['operator' => 'in', 'operand' => $userIds]]);
                foreach ($deletedUsers as $deletedUser) {
                    if (UsersConstant::ACTIVATED_ACTIVE === $deletedUser->getActivated()) {
                        $eventDispatcher->dispatch(new GenericEvent($deletedUser->getUid()), UserEvents::DELETE_ACCOUNT);
                    } else {
                        $eventDispatcher->dispatch(new RegistrationPostDeletedEvent($deletedUser));
                    }
                    $eventDispatcher->dispatch(new GenericEvent(null, ['id' => $deletedUser->getUid()]), UserEvents::DELETE_PROCESS);
                    $hookDispatcher->dispatch(UserManagementUiHooksSubscriber::DELETE_PROCESS, new ProcessHook($deletedUser->getUid()));
                    $userRepository->removeAndFlush($deletedUser);
                }
                $this->addFlash(
                    'success',
                    /** @Desc("{count, plural,\n  one   {User deleted!}\n  other {# users deleted!}\n}") */
                    $this->getTranslator()->trans(
                        'plural_n.users.deleted',
                        ['%count%' => count($deletedUsers)]
                    )
                );

                return $this->redirectToRoute('zikulausersmodule_useradministration_list');
            }
        }
        $users = $userRepository->findByUids($uids);

        return [
            'users' => $users,
            'form' => $deleteConfirmationForm->createView()
        ];
    }

    /**
     * @Route("/search")
     * @PermissionCheck("moderate")
     * @Theme("admin")
     * @Template("@ZikulaUsersModule/UserAdministration/search.html.twig")
     *
     * @return array|Response
     */
    public function searchAction(
        Request $request,
        UserRepositoryInterface $userRepository,
        VariableApiInterface $variableApi
    ) {
        $form = $this->createForm(SearchUserType::class, []);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $resultsForm = $this->createForm(DeleteType::class, [], [
                'choices' => $userRepository->queryBySearchForm($form->getData(), 250),
                'action' => $this->generateUrl('zikulausersmodule_useradministration_delete')
            ]);

            return $this->render('@ZikulaUsersModule/UserAdministration/searchResults.html.twig', [
                'resultsForm' => $resultsForm->createView(),
                'mailForm' => $this->buildMailForm($variableApi)->createView()
            ]);
        }

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/mail")
     * @PermissionCheck({"$_zkModule::MailUsers", "::", "comment"})
     */
    public function mailUsersAction(
        Request $request,
        UserRepositoryInterface $userRepository,
        VariableApiInterface $variableApi,
        MailHelper $mailHelper
    ): RedirectResponse {
        $mailForm = $this->buildMailForm($variableApi);
        $mailForm->handleRequest($request);
        if ($mailForm->isSubmitted() && $mailForm->isValid()) {
            $data = $mailForm->getData();
            $users = $userRepository->query(['uid' => ['operator' => 'in', 'operand' => explode(',', $data['userIds'])]]);
            if (empty($users)) {
                throw new InvalidArgumentException($this->trans('No users found.'));
            }
            if ($mailHelper->mailUsers($users, $data)) {
                $this->addFlash('success', 'Done! Mail sent.');
            } else {
                $this->addFlash('error', 'Could not send mail.');
            }
        } else {
            $this->addFlash('error', 'Could not send mail.');
        }

        return $this->redirectToRoute('zikulausersmodule_useradministration_search');
    }

    private function buildMailForm(VariableApiInterface $variableApi): FormInterface
    {
        return $this->createForm(MailType::class, [
            'from' => $variableApi->getSystemVar('sitename'),
            'replyto' => $variableApi->getSystemVar('adminmail'),
            'format' => 'text',
            'batchsize' => 100
        ], [
            'action' => $this->generateUrl('zikulausersmodule_useradministration_mailusers')
        ]);
    }

    /**
     * Prevent user from modifying certain aspects of self.
     */
    private function checkSelf(
        CurrentUserApiInterface $currentUserApi,
        VariableApiInterface $variableApi,
        UserEntity $userBeingModified,
        array $originalGroups = []
    ): void {
        $currentUserId = $currentUserApi->get('uid');
        if ($currentUserId !== $userBeingModified->getUid()) {
            return;
        }

        // current user not allowed to deactivate self
        if (UsersConstant::ACTIVATED_ACTIVE !== $userBeingModified->getActivated()) {
            $this->addFlash('info', 'You are not allowed to alter your own active state.');
            $userBeingModified->setActivated(UsersConstant::ACTIVATED_ACTIVE);
        }
        // current user not allowed to remove self from default group
        $defaultGroup = $variableApi->get('ZikulaGroupsModule', 'defaultgroup', 1);
        if (!$userBeingModified->getGroups()->containsKey($defaultGroup)) {
            $this->addFlash('info', 'You are not allowed to remove yourself from the default group.');
            $userBeingModified->getGroups()->add($originalGroups[$defaultGroup]);
        }
        // current user not allowed to remove self from admin group if currently a member
        if (isset($originalGroups[Constant::GROUP_ID_ADMIN]) && !$userBeingModified->getGroups()->containsKey(Constant::GROUP_ID_ADMIN)) {
            $this->addFlash('info', 'You are not allowed to remove yourself from the primary administrator group.');
            $userBeingModified->getGroups()->add($originalGroups[Constant::GROUP_ID_ADMIN]);
        }
    }
}
