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

use Doctrine\Common\Collections\ArrayCollection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Form;
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
use Zikula\GroupsModule\Constant;
use Zikula\ThemeModule\Engine\Annotation\Theme;
use Zikula\UsersModule\Api\ApiInterface\CurrentUserApiInterface;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\UsersModule\Entity\RepositoryInterface\UserRepositoryInterface;
use Zikula\UsersModule\Entity\UserEntity;
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
use Zikula\UsersModule\RegistrationEvents;
use Zikula\UsersModule\UserEvents;

/**
 * Class UserAdministrationController
 * @Route("/admin")
 */
class UserAdministrationController extends AbstractController
{
    /**
     * @Route("/list/{sort}/{sortdir}/{letter}/{startnum}")
     * @Theme("admin")
     * @Template("ZikulaUsersModule:UserAdministration:list.html.twig")
     *
     * @param Request $request
     * @param UserRepositoryInterface $userRepository
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
        UserRepositoryInterface $userRepository,
        RouterInterface $router,
        AdministrationActionsHelper $actionsHelper,
        $sort = 'uid',
        $sortdir = 'DESC',
        $letter = 'all',
        $startnum = 0
    ) {
        if (!$this->hasPermission('ZikulaUsersModule', '::', ACCESS_MODERATE)) {
            throw new AccessDeniedException();
        }
        $startnum = $startnum > 0 ? $startnum - 1 : 0;

        $sortableColumns = new SortableColumns($router, 'zikulausersmodule_useradministration_list', 'sort', 'sortdir');
        $sortableColumns->addColumns([new Column('uname'), new Column('uid'), new Column('user_regdate'), new Column('lastlogin'), new Column('activated')]);
        $sortableColumns->setOrderByFromRequest($request);
        $sortableColumns->setAdditionalUrlParameters([
            'letter' => $letter,
            'startnum' => $startnum
        ]);

        $filter = [];
        if (!empty($letter) && 'all' != $letter) {
            $filter['uname'] = ['operator' => 'like', 'operand' => "$letter%"];
        }
        $limit = $this->getVar(UsersConstant::MODVAR_ITEMS_PER_PAGE, UsersConstant::DEFAULT_ITEMS_PER_PAGE);
        $users = $userRepository->query($filter, [$sort => $sortdir], $limit, $startnum);

        return [
            'sort' => $sortableColumns->generateSortableColumns(),
            'pager' => [
                'count' => $users->count(),
                'limit' => $limit
            ],
            'actionsHelper' => $actionsHelper,
            'users' => $users
        ];
    }

    /**
     * Called from UsersModule/Resources/public/js/Zikula.Users.Admin.View.js
     * to populate a username search
     *
     * @Route("/getusersbyfragmentastable", methods = {"POST"}, options={"expose"=true})
     *
     * @param Request $request
     * @param UserRepositoryInterface $userRepository
     * @param AdministrationActionsHelper $actionsHelper
     *
     * @return PlainResponse
     */
    public function getUsersByFragmentAsTableAction(
        Request $request,
        UserRepositoryInterface $userRepository,
        AdministrationActionsHelper $actionsHelper
    ) {
        if (!$this->hasPermission('ZikulaUsersModule', '::', ACCESS_MODERATE)) {
            return new PlainResponse('');
        }
        $fragment = $request->request->get('fragment');
        $filter = [
            'activated' => ['operator' => 'notIn', 'operand' => [
                UsersConstant::ACTIVATED_PENDING_REG,
                UsersConstant::ACTIVATED_PENDING_DELETE
            ]],
            'uname' => ['operator' => 'like', 'operand' => "$fragment%"]
        ];
        $users = $userRepository->query($filter);

        return $this->render('@ZikulaUsersModule/UserAdministration/userlist.html.twig', [
            'users' => $users,
            'actionsHelper' => $$actionsHelper
        ], new PlainResponse());
    }

    /**
     * @Route("/user/modify/{user}", requirements={"user" = "^[1-9]\d*$"})
     * @Theme("admin")
     * @Template("ZikulaUsersModule:UserAdministration:modify.html.twig")
     *
     * @param Request $request
     * @param UserEntity $user
     * @param HookDispatcherInterface $hookDispatcher
     *
     * @return array|RedirectResponse
     */
    public function modifyAction(Request $request, UserEntity $user, HookDispatcherInterface $hookDispatcher)
    {
        if (!$this->hasPermission('ZikulaUsersModule::', $user->getUname() . "::" . $user->getUid(), ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }
        if (UsersConstant::USER_ID_ANONYMOUS === $user->getUid()) {
            throw new AccessDeniedException($this->__("Error! You can't edit the guest account."));
        }
        $dispatcher = $this->get('event_dispatcher');

        $form = $this->createForm(AdminModifyUserType::class, $user);
        $originalUserName = $user->getUname();
        $originalGroups = $user->getGroups()->toArray();
        $formEvent = new UserFormAwareEvent($form);
        $dispatcher->dispatch(UserEvents::EDIT_FORM, $formEvent);
        $form->handleRequest($request);

        $hook = new ValidationHook(new ValidationProviders());
        $hookDispatcher->dispatch(UserManagementUiHooksSubscriber::EDIT_VALIDATE, $hook);
        $validators = $hook->getValidators();

        if ($form->isSubmitted() && $form->isValid() && !$validators->hasErrors()) {
            if ($form->get('submit')->isClicked()) {
                $user = $form->getData();
                $this->checkSelf($user, $originalGroups);
                $this->get('doctrine')->getManager()->flush($user);
                $eventArgs = [
                    'action'    => 'setVar',
                    'field'     => 'uname',
                    'attribute' => null,
                ];
                $eventData = ['old_value' => $originalUserName];
                $updateEvent = new GenericEvent($user, $eventArgs, $eventData);
                $dispatcher->dispatch(UserEvents::UPDATE_ACCOUNT, $updateEvent);
                $formDataEvent = new UserFormDataEvent($user, $form);
                $dispatcher->dispatch(UserEvents::EDIT_FORM_HANDLE, $formDataEvent);
                $hookDispatcher->dispatch(UserManagementUiHooksSubscriber::EDIT_PROCESS, new ProcessHook($user->getUid()));

                $this->addFlash('status', $this->__("Done! Saved user's account information."));
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Operation cancelled.'));
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
     * @Theme("admin")
     * @Template("ZikulaUsersModule:UserAdministration:approve.html.twig")
     *
     * @param Request $request
     * @param UserEntity $user
     * @param RegistrationHelper $registrationHelper
     * @param MailHelper $mailHelper
     * @param bool $force
     *
     * @return array
     */
    public function approveAction(
        Request $request,
        UserEntity $user,
        RegistrationHelper $registrationHelper,
        MailHelper $mailHelper,
        $force = false
    ) {
        if (!$this->hasPermission('ZikulaUsersModule', '::', ACCESS_MODERATE)) {
            throw new AccessDeniedException();
        }

        $forceVerification = $this->hasPermission('ZikulaUsersModule', '::', ACCESS_ADMIN) && $force;
        $form = $this->createForm(ApproveRegistrationConfirmationType::class, [
            'user' => $user->getUid(),
            'force' => $forceVerification
        ], [
            'buttonLabel' => $this->__('Approve')
        ]);
        $redirectToRoute = 'zikulausersmodule_useradministration_list';

        if (!$forceVerification) {
            if ($user->isApproved()) {
                $this->addFlash('error', $this->__f('Warning! Nothing to do! %sub% is already approved.', ['%sub%' => $user->getUname()]));

                return $this->redirectToRoute($redirectToRoute);
            }
            if (!$user->isApproved() && !$this->hasPermission('ZikulaUsersModule::', '::', ACCESS_ADMIN)) {
                $this->addFlash('error', $this->__f('Error! %sub% cannot be approved.', ['%sub%' => $user->getUname()]));

                return $this->redirectToRoute($redirectToRoute);
            }
        }

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('confirm')->isClicked()) {
                $registrationHelper->approve($user);
                if (UsersConstant::ACTIVATED_PENDING_REG == $user->getActivated()) {
                    $notificationErrors = $mailHelper->createAndSendRegistrationMail($user, true, false);
                } else {
                    $notificationErrors = $mailHelper->createAndSendUserMail($user, true, false);
                }

                if ($notificationErrors) {
                    $this->addFlash('error', implode('<br />', $notificationErrors));
                }
                $this->addFlash('status', $this->__f('Done! %sub% has been approved.', ['%sub%' => $user->getUname()]));
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Operation cancelled.'));
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
     * @Theme("admin")
     * @Template("ZikulaUsersModule:UserAdministration:delete.html.twig")
     *
     * @param Request $request
     * @param CurrentUserApiInterface $currentUserApi
     * @param UserRepositoryInterface $userRepository
     * @param HookDispatcherInterface $hookDispatcher
     * @param UserEntity|null $user
     *
     * @return array
     */
    public function deleteAction(
        Request $request,
        CurrentUserApiInterface $currentUserApi,
        UserRepositoryInterface $userRepository,
        HookDispatcherInterface $hookDispatcher,
        UserEntity $user = null
    ) {
        if (!$this->hasPermission('ZikulaUsersModule', '::', ACCESS_DELETE)) {
            throw new AccessDeniedException();
        }
        $users = new ArrayCollection();
        if ('POST' == $request->getMethod()) {
            $deleteForm = $this->createForm(DeleteType::class, [], [
                'choices' => $userRepository->queryBySearchForm(),
                'action' => $this->generateUrl('zikulausersmodule_useradministration_delete')
            ]);
            $deleteForm->handleRequest($request);
            if ($deleteForm->isSubmitted() && $deleteForm->isValid()) {
                $data = $deleteForm->getData();
                $users = $data['users'];
            }
        } else {
            if (isset($user)) {
                $users->add($user);
            }
        }
        $uids = [];
        foreach ($users as $user) {
            $uids[] = $user->getUid();
        }
        $usersImploded = implode(',', $uids);

        $deleteConfirmationForm = $this->createForm(DeleteConfirmationType::class, [
            'users' => $usersImploded
        ]);
        $deleteConfirmationForm->handleRequest($request);
        if (!$deleteConfirmationForm->isSubmitted() && $users instanceof ArrayCollection && $users->isEmpty()) {
            throw new \InvalidArgumentException($this->__('No users selected.'));
        }
        if ($deleteConfirmationForm->isSubmitted()) {
            if ($deleteConfirmationForm->get('cancel')->isClicked()) {
                $this->addFlash('success', $this->__('Operation cancelled.'));

                return $this->redirectToRoute('zikulausersmodule_useradministration_list');
            }
            $userIdsImploded = $deleteConfirmationForm->get('users')->getData();
            $userIds = explode(',', $userIdsImploded);
            $valid = true;
            foreach ($userIds as $k => $uid) {
                if (in_array($uid, [UsersConstant::USER_ID_ANONYMOUS, UsersConstant::USER_ID_ADMIN, $currentUserApi->get('uid')])) {
                    unset($userIds[$k]);
                    $this->addFlash('danger', $this->__f('You are not allowed to delete Uid %uid', ['%uid' => $uid]));
                    continue;
                }
                $event = new GenericEvent(null, ['id' => $uid], new ValidationProviders());
                $validators = $this->get('event_dispatcher')->dispatch(UserEvents::DELETE_VALIDATE, $event)->getData();
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
                    $eventName = UsersConstant::ACTIVATED_ACTIVE == $deletedUser->getActivated() ? UserEvents::DELETE_ACCOUNT : RegistrationEvents::DELETE_REGISTRATION;
                    $this->get('event_dispatcher')->dispatch($eventName, new GenericEvent($deletedUser->getUid()));
                    $this->get('event_dispatcher')->dispatch(UserEvents::DELETE_PROCESS, new GenericEvent(null, ['id' => $deletedUser->getUid()]));
                    $hookDispatcher->dispatch(UserManagementUiHooksSubscriber::DELETE_PROCESS, new ProcessHook($deletedUser->getUid()));
                    $userRepository->removeAndFlush($deletedUser);
                }
                $this->addFlash('success', $this->_fn('User deleted!', '%n users deleted!', count($deletedUsers), ['%n' => count($deletedUsers)]));

                return $this->redirectToRoute('zikulausersmodule_useradministration_list');
            }
        }

        return [
            'users' => $users,
            'form' => $deleteConfirmationForm->createView()
        ];
    }

    /**
     * @Route("/search")
     * @Theme("admin")
     * @Template("ZikulaUsersModule:UserAdministration:search.html.twig")
     *
     * @param Request $request
     * @param UserRepositoryInterface $userRepository
     * @param VariableApiInterface $variableApi
     *
     * @return array
     */
    public function searchAction(
        Request $request,
        UserRepositoryInterface $userRepository,
        VariableApiInterface $variableApi
    ) {
        if (!$this->hasPermission('ZikulaUsersModule', '::', ACCESS_MODERATE)) {
            throw new AccessDeniedException();
        }
        $form = $this->createForm(SearchUserType::class, []);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $resultsForm = $this->createForm(DeleteType::class, [], [
                'choices' => $userRepository->queryBySearchForm($form->getData()),
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
     *
     * @param Request $request
     * @param UserRepositoryInterface $userRepository
     * @param VariableApiInterface $variableApi
     * @param MailHelper $mailHelper
     *
     * @return RedirectResponse
     */
    public function mailUsersAction(
        Request $request,
        UserRepositoryInterface $userRepository,
        VariableApiInterface $variableApi,
        MailHelper $mailHelper
    ) {
        if (!$this->hasPermission('ZikulaUsersModule', '::MailUsers', ACCESS_COMMENT)) {
            throw new AccessDeniedException();
        }
        $mailForm = $this->buildMailForm($variableApi);
        $mailForm->handleRequest($request);
        if ($mailForm->isSubmitted() && $mailForm->isValid()) {
            $data = $mailForm->getData();
            $users = $userRepository->query(['uid' => ['operator' => 'in', 'operand' => explode(',', $data['userIds'])]]);
            if (empty($users)) {
                throw new \InvalidArgumentException($this->__('No users found.'));
            }
            if ($mailHelper->mailUsers($users, $data)) {
                $this->addFlash('success', $this->__('Mail sent!'));
            } else {
                $this->addFlash('error', $this->__('Could not send mail.'));
            }
        } else {
            $this->addFlash('error', $this->__('Could not send mail.'));
        }

        return $this->redirectToRoute('zikulausersmodule_useradministration_search');
    }

    /**
     * @param VariableApiInterface $variableApi
     *
     * @return Form
     */
    private function buildMailForm(VariableApiInterface $variableApi)
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
     *
     * @param CurrentUserApiInterface $currentUserApi
     * @param VariableApiInterface $variableApi
     * @param UserEntity $userBeingModified
     * @param array $originalGroups
     */
    private function checkSelf(
        CurrentUserApiInterface $currentUserApi,
        VariableApiInterface $variableApi,
        UserEntity $userBeingModified,
        array $originalGroups
    ) {
        $currentUserId = $currentUserApi->get('uid');
        if ($currentUserId != $userBeingModified->getUid()) {
            return;
        }

        // current user not allowed to deactivate self
        if (UsersConstant::ACTIVATED_ACTIVE != $userBeingModified->getActivated()) {
            $this->addFlash('info', $this->__('You are not allowed to alter your own active state.'));
            $userBeingModified->setActivated(UsersConstant::ACTIVATED_ACTIVE);
        }
        // current user not allowed to remove self from default group
        $defaultGroup = $variableApi->get('ZikulaGroupsModule', 'defaultgroup', 1);
        if (!$userBeingModified->getGroups()->containsKey($defaultGroup)) {
            $this->addFlash('info', $this->__('You are not allowed to remove yourself from the default group.'));
            $userBeingModified->getGroups()->add($originalGroups[$defaultGroup]);
        }
        // current user not allowed to remove self from admin group if currently a member
        if (isset($originalGroups[Constant::GROUP_ID_ADMIN]) && !$userBeingModified->getGroups()->containsKey(Constant::GROUP_ID_ADMIN)) {
            $this->addFlash('info', $this->__('You are not allowed to remove yourself from the primary administrator group.'));
            $userBeingModified->getGroups()->add($originalGroups[Constant::GROUP_ID_ADMIN]);
        }
    }
}
