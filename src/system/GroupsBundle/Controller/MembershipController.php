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

namespace Zikula\GroupsBundle\Controller;

use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use InvalidArgumentException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Zikula\Bundle\CoreBundle\Controller\AbstractController;
use Zikula\Bundle\CoreBundle\Response\PlainResponse;
use Zikula\ExtensionsBundle\Api\ApiInterface\VariableApiInterface;
use Zikula\GroupsBundle\Entity\GroupEntity;
use Zikula\GroupsBundle\Event\GroupPostUserAddedEvent;
use Zikula\GroupsBundle\Event\GroupPostUserRemovedEvent;
use Zikula\GroupsBundle\Form\Type\RemoveUserType;
use Zikula\GroupsBundle\Helper\CommonHelper;
use Zikula\GroupsBundle\Repository\GroupRepositoryInterface;
use Zikula\PermissionsBundle\Annotation\PermissionCheck;
use Zikula\ThemeBundle\Engine\Annotation\Theme;
use Zikula\UsersBundle\Api\ApiInterface\CurrentUserApiInterface;
use Zikula\UsersBundle\Constant as UsersConstant;
use Zikula\UsersBundle\Entity\UserEntity;
use Zikula\UsersBundle\Repository\UserRepositoryInterface;
use Zikula\UsersBundle\Repository\UserSessionRepositoryInterface;

#[Route('/groups/membership')]
class MembershipController extends AbstractController
{
    /**
     * @PermissionCheck({"$_zkModule::memberslist", "::", "overview"})
     * @Template("@ZikulaGroups/Membership/list.html.twig")
     *
     * Display all members of a group to a user.
     */
    #[Route('/list/{gid}/{letter}/{page}', name: 'zikulagroupsbundle_membership_listmemberships', methods: ['GET'],
        requirements: ['gid' => "^[1-9]\d*$", 'letter' => "[a-zA-Z]|\*", 'page' => "\d+"])
    ]
    public function listMemberships(
        GroupEntity $group,
        VariableApiInterface $variableApi,
        UserSessionRepositoryInterface $userSessionRepository,
        string $letter = '*',
        int $page = 1
    ): array {
        $groupsCommon = new CommonHelper($this->getTranslator());
        $inactiveLimit = $variableApi->getSystemVar('secinactivemins');
        $dateTime = new DateTime();
        $dateTime->modify('-' . $inactiveLimit . 'minutes');

        return [
            'group' => $group,
            'groupTypes' => $groupsCommon->gtypeLabels(),
            'states' => $groupsCommon->stateLabels(),
            'usersOnline' => $userSessionRepository->getUsersSince($dateTime)
        ];
    }

    /**
     * @PermissionCheck({"$_zkModule::", "$gid::", "edit"})
     * @Theme("admin")
     * @Template("@ZikulaGroups/Membership/adminList.html.twig")
     *
     * Display all members of a group to an admin.
     */
    #[Route('/admin/list/{gid}/{letter}/{page}', name: 'zikulagroupsbundle_membership_adminlist', methods: ['GET'],
        requirements: ['gid' => "^[1-9]\d*$", 'letter' => "[a-zA-Z]|\*", 'page' => "\d+"])
    ]
    public function adminList(
        GroupEntity $group,
        string $letter = '*',
        int $page = 1
    ): array {
        return [
            'group' => $group
        ];
    }

    /**
     * @PermissionCheck({"$_zkModule::", "$gid::", "edit"})
     *
     * Add user to a group.
     *
     * @throws AccessDeniedException Thrown if the CSRF token is invalid
     */
    #[Route('/admin/add/{uid}/{gid}/{token}', name: 'zikulagroupsbundle_membership_add', requirements: ['gid' => "^[1-9]\d*$", 'uid' => "^[1-9]\d*$"])]
    public function add(
        UserEntity $userEntity,
        GroupEntity $group,
        string $token,
        ManagerRegistry $doctrine,
        EventDispatcherInterface $eventDispatcher
    ): RedirectResponse {
        if (!$this->isCsrfTokenValid('membership-add', $token)) {
            throw new AccessDeniedException();
        }

        if ($userEntity->getGroups()->contains($group)) {
            $this->addFlash('warning', 'The selected user is already a member of this group.');
        } else {
            $userEntity->addGroup($group);
            $doctrine->getManager()->flush();
            $this->addFlash('status', 'Done! The user was added to the group.');
            // Let other modules know that we have updated a group.
            $eventDispatcher->dispatch(new GroupPostUserAddedEvent($group, $userEntity));
        }

        return $this->redirectToRoute('zikulagroupsbundle_membership_adminlist', ['gid' => $group->getGid()]);
    }

    /**
     * @PermissionCheck("overview")
     *
     * Process request by the current user to join a group
     *
     * @throws AccessDeniedException Thrown if the user isn't logged in
     */
    #[Route('/join/{gid}', name: 'zikulagroupsbundle_membership_join', requirements: ['gid' => "^[1-9]\d*$"])]
    public function join(
        GroupEntity $group,
        ManagerRegistry $doctrine,
        EventDispatcherInterface $eventDispatcher,
        CurrentUserApiInterface $currentUserApi,
        UserRepositoryInterface $userRepository
    ): RedirectResponse {
        if (!$currentUserApi->isLoggedIn()) {
            throw new AccessDeniedException($this->trans('Error! You must register for a user account on this site before you can join a group.'));
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
            $doctrine->getManager()->flush();
            $this->addFlash('success', $this->trans('Joined the "%groupName%" group', ['%groupName%' => $group->getName()]));
            // Let other modules know that we have updated a group.
            $eventDispatcher->dispatch(new GroupPostUserAddedEvent($group, $userEntity));
        }

        return $this->redirectToRoute('zikulagroupsbundle_group_listgroups');
    }

    /**
     * @PermissionCheck({"$_zkModule::", "$gid::", "edit"})
     * @Theme("admin")
     * @Template("@ZikulaGroups/Membership/remove.html.twig")
     *
     * Remove a user from a group.
     *
     * @return array|Response
     *
     * @throws InvalidArgumentException
     */
    #[Route('/admin/remove/{gid}/{uid}}', name: 'zikulagroupsbundle_membership_remove', requirements: ['gid' => "^[1-9]\d*$", 'uid' => "^[1-9]\d*$"])]
    public function remove(
        Request $request,
        ManagerRegistry $doctrine,
        EventDispatcherInterface $eventDispatcher,
        GroupRepositoryInterface $groupRepository,
        UserRepositoryInterface $userRepository,
        int $gid = 0,
        int $uid = 0
    ) {
        if ($request->isMethod(Request::METHOD_POST)) {
            $postVars = $request->request->get('zikulagroupsbundle_removeuser');
            $gid = $postVars['gid'] ?? 0;
            $uid = $postVars['uid'] ?? 0;
        }
        if ($gid < 1 || $uid < 1) {
            throw new InvalidArgumentException($this->trans('Invalid Group ID or User ID.'));
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
                $doctrine->getManager()->flush();
                $this->addFlash('status', 'Done! The user was removed from the group.');
                $eventDispatcher->dispatch(new GroupPostUserRemovedEvent($group, $user));
            } elseif ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', 'Operation cancelled.');
            }

            return $this->redirectToRoute('zikulagroupsbundle_membership_adminlist', ['gid' => $group->getGid()]);
        }

        return [
            'form' => $form->createView(),
            'group' => $group,
            'uname' => $user->getUname()
        ];
    }

    /**
     * @PermissionCheck("overview")
     *
     * Process request by current user to leave a group
     *
     * @throws AccessDeniedException Thrown if the user isn't logged in
     */
    #[Route('/leave/{gid}', name: 'zikulagroupsbundle_membership_leave', requirements: ['gid' => "^[1-9]\d*$"])]
    public function leave(
        GroupEntity $group,
        ManagerRegistry $doctrine,
        EventDispatcherInterface $eventDispatcher,
        CurrentUserApiInterface $currentUserApi,
        UserRepositoryInterface $userRepository
    ): RedirectResponse {
        if (!$currentUserApi->isLoggedIn()) {
            throw new AccessDeniedException($this->trans('Error! You must be logged in before you can leave a group.'));
        }
        /** @var UserEntity $userEntity */
        $userEntity = $userRepository->find($currentUserApi->get('uid'));
        $userEntity->removeGroup($group);
        $doctrine->getManager()->flush();
        $this->addFlash('success', $this->trans('Left the "%groupName%" group', ['%groupName%' => $group->getName()]));
        // Let other modules know that we have updated a group.
        $eventDispatcher->dispatch(new GroupPostUserRemovedEvent($group, $userEntity));

        return $this->redirectToRoute('zikulagroupsbundle_group_listgroups');
    }

    /**
     * Called from UsersBundle/Resources/public/js/Zikula.Users.Admin.View.js
     * to populate a username search
     */
    #[Route('/admin/getusersbyfragmentastable', name: 'zikulagroupsbundle_membership_getusersbyfragmentastable', methods: ['POST'], options: ['expose' => true])]
    public function getUsersByFragmentAsTable(
        Request $request,
        UserRepositoryInterface $userRepository
    ): Response {
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

        return $this->render('@ZikulaGroupsBundle/Membership/userlist.html.twig', [
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
        $messages[] = $this->trans('Error! You cannot apply to join the requested group');
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
