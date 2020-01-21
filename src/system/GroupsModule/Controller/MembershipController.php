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

namespace Zikula\GroupsModule\Controller;

use DateTime;
use Exception;
use InvalidArgumentException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Zikula\Core\Controller\AbstractController;
use Zikula\Core\Event\GenericEvent;
use Zikula\Core\Response\PlainResponse;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\GroupsModule\Entity\GroupEntity;
use Zikula\GroupsModule\Entity\RepositoryInterface\GroupRepositoryInterface;
use Zikula\GroupsModule\Form\Type\RemoveUserType;
use Zikula\GroupsModule\GroupEvents;
use Zikula\GroupsModule\Helper\CommonHelper;
use Zikula\ThemeModule\Engine\Annotation\Theme;
use Zikula\UsersModule\Api\ApiInterface\CurrentUserApiInterface;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\UsersModule\Entity\RepositoryInterface\UserRepositoryInterface;
use Zikula\UsersModule\Entity\RepositoryInterface\UserSessionRepositoryInterface;
use Zikula\UsersModule\Entity\UserEntity;

/**
 * @Route("/membership")
 *
 * Administrative controllers for the groups module
 */
class MembershipController extends AbstractController
{
    /**
     * @Route("/list/{gid}/{letter}/{startNum}", methods = {"GET"}, requirements={"gid" = "^[1-9]\d*$", "letter" = "[a-zA-Z]|\*", "startNum" = "\d+"})
     * @Template("@ZikulaGroupsModule/Membership/list.html.twig")
     *
     * Display all members of a group to a user.
     *
     * @throws AccessDeniedException Thrown if the user hasn't permissions to view any groups
     * @throws Exception
     */
    public function listAction(
        GroupEntity $group,
        VariableApiInterface $variableApi,
        UserSessionRepositoryInterface $userSessionRepository,
        string $letter = '*',
        int $startNum = 0
    ): array {
        if (!$this->hasPermission('ZikulaGroupsModule::memberslist', '::', ACCESS_OVERVIEW)) {
            throw new AccessDeniedException();
        }
        $groupsCommon = new CommonHelper($this->getTranslator());
        $inactiveLimit = $variableApi->getSystemVar('secinactivemins');
        $dateTime = new DateTime();
        $dateTime->modify('-' . $inactiveLimit . 'minutes');

        return [
            'group' => $group,
            'groupTypes' => $groupsCommon->gtypeLabels(),
            'states' => $groupsCommon->stateLabels(),
            'usersOnline' => $userSessionRepository->getUsersSince($dateTime),
            'pager' => [
                'amountOfItems' => $group->getUsers()->count(),
                'itemsPerPage' => $this->getVar('itemsperpage', 25)
            ],
        ];
    }

    /**
     * @Route("/admin/list/{gid}/{letter}/{startNum}", methods = {"GET"}, requirements={"gid" = "^[1-9]\d*$", "letter" = "[a-zA-Z]|\*", "startNum" = "\d+"})
     * @Theme("admin")
     * @Template("@ZikulaGroupsModule/Membership/adminList.html.twig")
     *
     * Display all members of a group to an admin.
     *
     * @throws AccessDeniedException Thrown if the user hasn't permissions to edit any groups
     */
    public function adminListAction(
        GroupEntity $group,
        string $letter = '*',
        int $startNum = 0
    ): array {
        if (!$this->hasPermission('ZikulaGroupsModule::', $group->getGid() . '::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        return [
            'group' => $group,
            'pager' => [
                'amountOfItems' => $group->getUsers()->count(),
                'itemsPerPage' => $this->getVar('itemsperpage', 25)
            ]
        ];
    }

    /**
     * @Route("/admin/add/{uid}/{gid}/{token}", requirements={"gid" = "^[1-9]\d*$", "uid" = "^[1-9]\d*$"})
     *
     * Add user to a group.
     *
     * @throws AccessDeniedException Thrown if the user hasn't permissions to edit the group
     */
    public function addAction(
        UserEntity $userEntity,
        GroupEntity $group,
        string $token,
        EventDispatcherInterface $eventDispatcher
    ): RedirectResponse {
        if (!$this->hasPermission('ZikulaGroupsModule::', $group->getGid() . '::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }
        if (!$this->isCsrfTokenValid('membership-add', $token)) {
            throw new AccessDeniedException();
        }

        if ($userEntity->getGroups()->contains($group)) {
            $this->addFlash('warning', 'The selected user is already a member of this group.');
        } else {
            $userEntity->addGroup($group);
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('status', 'Done! The user was added to the group.');
            // Let other modules know that we have updated a group.
            $addUserEvent = new GenericEvent(['gid' => $group->getGid(), 'uid' => $userEntity->getUid()]);
            $eventDispatcher->dispatch($addUserEvent, GroupEvents::GROUP_ADD_USER);
        }

        return $this->redirectToRoute('zikulagroupsmodule_membership_adminlist', ['gid' => $group->getGid()]);
    }

    /**
     * @Route("/join/{gid}", requirements={"gid" = "^[1-9]\d*$"})
     *
     * Process request by the current user to join a group
     *
     * @throws AccessDeniedException Thrown if the user hasn't permissions to view any groups
     */
    public function joinAction(
        GroupEntity $group,
        EventDispatcherInterface $eventDispatcher,
        CurrentUserApiInterface $currentUserApi,
        UserRepositoryInterface $userRepository
    ): RedirectResponse {
        if (!$this->hasPermission('ZikulaGroupsModule::', '::', ACCESS_OVERVIEW)) {
            throw new AccessDeniedException();
        }
        if (!$currentUserApi->isLoggedIn()) {
            throw new AccessDeniedException($this->trans('Sorry! You must register for a user account on this site before you can join a group.'));
        }
        /** @var UserEntity $userEntity */
        $userEntity = $userRepository->find($currentUserApi->get('uid'));
        $groupTypeIsPrivate = CommonHelper::GTYPE_PRIVATE === $group->getGtype();
        $groupTypeIsCore = CommonHelper::GTYPE_CORE === $group->getGtype();
        $groupStateIsClosed = CommonHelper::STATE_CLOSED === $group->getState();
        $groupCountIsLimit = 0 < $group->getNbumax() && $group->getUsers()->count() > $group->getNbumax();
        $alreadyGroupMember = $group->getUsers()->contains($userEntity);
        if ($groupTypeIsPrivate || $groupTypeIsCore || $groupStateIsClosed || $groupCountIsLimit || $alreadyGroupMember) {
            $this->addFlash(
                'error',
                $this->getSpecificGroupMessage($groupTypeIsPrivate, $groupTypeIsCore, $groupStateIsClosed, $groupCountIsLimit, $alreadyGroupMember)
            );
        } else {
            $userEntity->addGroup($group);
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', $this->trans('Joined the "%groupName%" group', ['%groupName%' => $group->getName()]));
            // Let other modules know that we have updated a group.
            $addUserEvent = new GenericEvent(['gid' => $group->getGid(), 'uid' => $userEntity->getUid()]);
            $eventDispatcher->dispatch($addUserEvent, GroupEvents::GROUP_ADD_USER);
        }

        return $this->redirectToRoute('zikulagroupsmodule_group_list');
    }

    /**
     * @Route("/admin/remove/{gid}/{uid}", requirements={"gid" = "^[1-9]\d*$", "uid" = "^[1-9]\d*$"})
     * @Theme("admin")
     * @Template("@ZikulaGroupsModule/Membership/remove.html.twig")
     *
     * Remove a user from a group.
     *
     * @return array|Response
     *
     * @throws AccessDeniedException Thrown if the user hasn't permissions to edit the group
     * @throws InvalidArgumentException
     */
    public function removeAction(
        Request $request,
        EventDispatcherInterface $eventDispatcher,
        GroupRepositoryInterface $groupRepository,
        UserRepositoryInterface $userRepository,
        int $gid = 0,
        int $uid = 0
    ) {
        if ($request->isMethod('POST')) {
            $postVars = $request->request->get('zikulagroupsmodule_removeuser');
            $gid = $postVars['gid'] ?? 0;
            $uid = $postVars['uid'] ?? 0;
        }
        if ($gid < 1 || $uid < 1) {
            throw new InvalidArgumentException($this->trans('Invalid Group ID or User ID.'));
        }
        if (!$this->hasPermission('ZikulaGroupsModule::', $gid . '::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }
        $group = $groupRepository->find($gid);
        if (!$group) {
            throw new InvalidArgumentException($this->trans('Invalid Group ID.'));
        }
        $user = $userRepository->find($uid);
        if (!$user) {
            throw new InvalidArgumentException($this->trans('Invalid User ID.'));
        }

        $form = $this->createForm(RemoveUserType::class, [
            'gid' => $gid,
            'uid' => $uid
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('remove')->isClicked()) {
                $user->removeGroup($group);
                $this->getDoctrine()->getManager()->flush();
                $this->addFlash('status', 'Done! The user was removed from the group.');
                $removeUserEvent = new g(null, ['gid' => $gid, 'uid' => $uid]);
                $eventDispatcher->dispatch($removeUserEvent, GroupEvents::GROUP_REMOVE_USER);
            } elseif ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', 'Operation cancelled.');
            }

            return $this->redirectToRoute('zikulagroupsmodule_membership_adminlist', ['gid' => $group->getGid()]);
        }

        return [
            'form' => $form->createView(),
            'group' => $group,
            'uname' => $user->getUname()
        ];
    }

    /**
     * @Route("/leave/{gid}", requirements={"gid" = "^[1-9]\d*$"})
     *
     * Process request by current user to leave a group
     *
     * @throws AccessDeniedException Thrown if the user hasn't permissions to view any groups
     */
    public function leaveAction(
        GroupEntity $group,
        EventDispatcherInterface $eventDispatcher,
        CurrentUserApiInterface $currentUserApi,
        UserRepositoryInterface $userRepository
    ): RedirectResponse {
        if (!$this->hasPermission('ZikulaGroupsModule::', '::', ACCESS_OVERVIEW)) {
            throw new AccessDeniedException();
        }
        if (!$currentUserApi->isLoggedIn()) {
            throw new AccessDeniedException($this->trans('Sorry! You must be logged in before you can leave a group.'));
        }
        /** @var UserEntity $userEntity */
        $userEntity = $userRepository->find($currentUserApi->get('uid'));
        $userEntity->removeGroup($group);
        $this->getDoctrine()->getManager()->flush();
        $this->addFlash('success', $this->trans('Left the "%groupName%" group', ['%groupName%' => $group->getName()]));
        // Let other modules know that we have updated a group.
        $removeUserEvent = new GenericEvent(['gid' => $group->getGid(), 'uid' => $userEntity->getUid()]);
        $eventDispatcher->dispatch($removeUserEvent, GroupEvents::GROUP_REMOVE_USER);

        return $this->redirectToRoute('zikulagroupsmodule_group_list');
    }

    /**
     * @Route("/admin/getusersbyfragmentastable", methods = {"POST"}, options={"expose"=true})
     *
     * Called from UsersModule/Resources/public/js/Zikula.Users.Admin.View.js
     * to populate a username search
     */
    public function getUsersByFragmentAsTableAction(Request $request, UserRepositoryInterface $userRepository): Response
    {
        if (!$this->hasPermission('ZikulaGroupsodule', '::', ACCESS_EDIT)) {
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

        return $this->render('@ZikulaGroupsModule/Membership/userlist.html.twig', [
            'users' => $users,
            'gid' => $request->query->get('gid')
        ], new PlainResponse());
    }

    private function getSpecificGroupMessage(
        bool $groupTypeIsPrivate,
        bool $groupTypeIsCore,
        bool $groupStateIsClosed,
        bool $groupCountIsLimit,
        bool $alreadyGroupMember
    ): string {
        $messages = [];
        $messages[] = $this->trans('Sorry!, You cannot apply to join the requested group');
        if ($groupTypeIsPrivate) {
            $messages[] = $this->trans('This group is a private group');
        }
        if ($groupTypeIsCore) {
            $messages[] = $this->trans('This group is a core-only group');
        }
        if ($groupStateIsClosed) {
            $messages[] = $this->trans('This group is closed.');
        }
        if ($groupCountIsLimit) {
            $messages[] = $this->trans('This group is has reached its membership limit.');
        }
        if ($alreadyGroupMember) {
            $messages[] = $this->trans('You are already a member of this group.');
        }

        return implode('<br />', $messages);
    }
}
