<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\GroupsModule\Controller;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Zikula\Core\Controller\AbstractController;
use Zikula\Core\Event\GenericEvent;
use Zikula\Core\Response\PlainResponse;
use Zikula\GroupsModule\Entity\GroupEntity;
use Zikula\GroupsModule\GroupEvents;
use Zikula\GroupsModule\Helper\CommonHelper;
use Zikula\ThemeModule\Engine\Annotation\Theme;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\UsersModule\Entity\UserEntity;

/**
 * @Route("/membership")
 *
 * Administrative controllers for the groups module
 */
class MembershipController extends AbstractController
{
    /**
     * @Route("/list/{gid}/{letter}/{startNum}", requirements={"gid" = "^[1-9]\d*$", "letter" = "[a-zA-Z]|\*", "startNum" = "\d+"})
     * @Method("GET")
     * @Template
     *
     * Display all members of a group to a user
     *
     * @param GroupEntity $group
     * @param string $letter the letter from the alpha filter
     * @param integer $startNum the start item number for the pager
     * @return array
     */
    public function listAction(GroupEntity $group, $letter = '*', $startNum = 0)
    {
        if (!$this->hasPermission('ZikulaGroupsModule::memberslist', '::', ACCESS_OVERVIEW)) {
            throw new AccessDeniedException();
        }
        $groupsCommon = new CommonHelper($this->getTranslator());
        $inactiveLimit = $this->get('zikula_extensions_module.api.variable')->getSystemVar('secinactivemins');
        $dateTime = new \DateTime();
        $dateTime->modify('-' . $inactiveLimit . 'minutes');

        return [
            'group' => $group,
            'groupTypes' => $groupsCommon->gtypeLabels(),
            'states' => $groupsCommon->stateLabels(),
            'usersOnline' => $this->get('zikula_users_module.user_session_repository')->getUsersSince($dateTime),
            'pager' => [
                'amountOfItems' => $group->getUsers()->count(),
                'itemsPerPage' => $this->getVar('itemsperpage', 25)
            ],
        ];
    }

    /**
     * @Route("/admin/list/{gid}/{letter}/{startNum}", requirements={"gid" = "^[1-9]\d*$", "letter" = "[a-zA-Z]|\*", "startNum" = "\d+"})
     * @Method("GET")
     * @Theme("admin")
     * @Template
     *
     * Display all members of a group to an admin
     *
     * @param GroupEntity $group
     * @param string $letter the letter from the alpha filter
     * @param integer $startNum the start item number for the pager
     * @return array
     */
    public function adminListAction(GroupEntity $group, $letter = '*', $startNum = 0)
    {
        if (!$this->hasPermission('ZikulaGroupsModule::', $group->getGid() . '::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        return [
            'group' => $group,
            'pager' => [
                'amountOfItems' => $group->getUsers()->count(),
                'itemsPerPage' => $this->getVar('itemsperpage', 25)
            ],
            'csrfToken' => $this->get('zikula_core.common.csrf_token_handler')->generate()
        ];
    }

    /**
     * @Route("/admin/add/{uid}/{gid}/{csrfToken}", requirements={"gid" = "^[1-9]\d*$", "uid" = "^[1-9]\d*$"})
     *
     * Add user to a group.
     *
     * @param UserEntity $userEntity
     * @param GroupEntity $group
     * @param $csrfToken
     * @return RedirectResponse
     */
    public function addAction(UserEntity $userEntity, GroupEntity $group, $csrfToken)
    {
        $this->get('zikula_core.common.csrf_token_handler')->validate($csrfToken);
        if (!$this->hasPermission('ZikulaGroupsModule::', $group->getGid() . '::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        if ($userEntity->getGroups()->contains($group)) {
            $this->addFlash('warning', $this->__('The selected user is already a member of this group.'));
        } else {
            $userEntity->addGroup($group);
            $this->get('doctrine')->getManager()->flush();
            $this->addFlash('status', $this->__('Done! The user was added to the group.'));
            // Let other modules know that we have updated a group.
            $addUserEvent = new GenericEvent(['gid' => $group->getGid(), 'uid' => $userEntity->getUid()]);
            $this->get('event_dispatcher')->dispatch(GroupEvents::GROUP_ADD_USER, $addUserEvent);
        }

        return $this->redirectToRoute('zikulagroupsmodule_membership_adminlist', ['gid' => $group->getGid()]);
    }

    /**
     * Process request by the current user to join a group
     * @Route("/join/{gid}", requirements={"gid" = "^[1-9]\d*$"})
     * @param GroupEntity $group
     * @return RedirectResponse
     */
    public function joinAction(GroupEntity $group)
    {
        if (!$this->hasPermission('ZikulaGroupsModule::', '::', ACCESS_OVERVIEW)) {
            throw new AccessDeniedException();
        }
        $currentUserApi = $this->get('zikula_users_module.current_user');
        if (!$currentUserApi->isLoggedIn()) {
            throw new AccessDeniedException($this->__('Sorry! You must register for a user account on this site before you can join a group.'));
        }
        /** @var UserEntity $userEntity */
        $userEntity = $this->get('zikula_users_module.user_repository')->find($currentUserApi->get('uid'));
        $groupTypeIsPrivate = $group->getGtype() == CommonHelper::GTYPE_PRIVATE;
        $groupTypeIsCore = $group->getGtype() == CommonHelper::GTYPE_CORE;
        $groupStateIsClosed = $group->getState() == CommonHelper::STATE_CLOSED;
        $groupCountIsLimit = $group->getNbumax() > 0 && $group->getUsers()->count() > $group->getNbumax();
        $alreadyGroupMember = $group->getUsers()->contains($userEntity);
        if ($groupTypeIsPrivate || $groupTypeIsCore || $groupStateIsClosed || $groupCountIsLimit || $alreadyGroupMember) {
            $this->addFlash('error', $this->getSpecificGroupMessage($groupTypeIsPrivate, $groupTypeIsCore, $groupStateIsClosed, $groupCountIsLimit, $alreadyGroupMember));
        } else {
            $userEntity->addGroup($group);
            $this->get('doctrine')->getManager()->flush();
            $this->addFlash('success', $this->__f('Joined the "%group" group', ['%group' => $group->getName()]));
            // Let other modules know that we have updated a group.
            $addUserEvent = new GenericEvent(['gid' => $group->getGid(), 'uid' => $userEntity->getUid()]);
            $this->get('event_dispatcher')->dispatch(GroupEvents::GROUP_ADD_USER, $addUserEvent);
        }

        return $this->redirectToRoute('zikulagroupsmodule_group_list');
    }

    /**
     * @Route("/admin/remove/{gid}/{uid}", requirements={"gid" = "^[1-9]\d*$", "uid" = "^[1-9]\d*$"})
     * @Theme("admin")
     * @Template
     *
     * Remove a user from a group.
     *
     * @param Request $request
     * @param int $gid
     * @param int $uid
     * @return mixed Response|void symfony response object if confirmation isn't provided, void otherwise
     */
    public function removeAction(Request $request, $gid = 0, $uid = 0)
    {
        if ($request->isMethod('POST')) {
            $postVars = $request->request->get('zikulagroupsmodule_removeuser');
            $gid = isset($postVars['gid']) ? $postVars['gid'] : 0;
            $uid = isset($postVars['uid']) ? $postVars['uid'] : 0;
        }
        if ($gid < 1 || $uid < 1) {
            throw new \InvalidArgumentException($this->__('Invalid Group ID or User ID.'));
        }
        if (!$this->hasPermission('ZikulaGroupsModule::', $gid.'::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }
        $group = $this->get('zikula_groups_module.group_repository')->find($gid);
        if (!$group) {
            throw new \InvalidArgumentException($this->__('Invalid Group ID.'));
        }
        $user = $this->get('zikula_users_module.user_repository')->find($uid);
        if (!$user) {
            throw new \InvalidArgumentException($this->__('Invalid User ID.'));
        }

        $form = $this->createForm('Zikula\GroupsModule\Form\Type\RemoveUserType', [
            'gid' => $gid,
            'uid' => $uid
        ], [
            'translator' => $this->get('translator.default')
        ]);

        if ($form->handleRequest($request)->isValid()) {
            if ($form->get('remove')->isClicked()) {
                $user->removeGroup($group);
                $this->get('doctrine')->getManager()->flush();
                $this->addFlash('status', $this->__('Done! The user was removed from the group.'));
                $removeUserEvent = new GenericEvent(null, ['gid' => $gid, 'uid' => $uid]);
                $this->get('event_dispatcher')->dispatch(GroupEvents::GROUP_REMOVE_USER, $removeUserEvent);
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Operation cancelled.'));
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
     * Process request by current user to leave a group
     * @Route("/leave/{gid}", requirements={"gid" = "^[1-9]\d*$"})
     * @param GroupEntity $group
     * @return RedirectResponse
     */
    public function leaveAction(GroupEntity $group)
    {
        if (!$this->hasPermission('ZikulaGroupsModule::', '::', ACCESS_OVERVIEW)) {
            throw new AccessDeniedException();
        }
        $currentUserApi = $this->get('zikula_users_module.current_user');
        if (!$currentUserApi->isLoggedIn()) {
            throw new AccessDeniedException($this->__('Sorry! You must be logged in before you can leave a group.'));
        }
        /** @var UserEntity $userEntity */
        $userEntity = $this->get('zikula_users_module.user_repository')->find($currentUserApi->get('uid'));
        $userEntity->removeGroup($group);
        $this->get('doctrine')->getManager()->flush();
        $this->addFlash('success', $this->__f('Left the "%group" group', ['%group' => $group->getName()]));
        // Let other modules know that we have updated a group.
        $removeUserEvent = new GenericEvent(['gid' => $group->getGid(), 'uid' => $userEntity->getUid()]);
        $this->get('event_dispatcher')->dispatch(GroupEvents::GROUP_REMOVE_USER, $removeUserEvent);

        return $this->redirectToRoute('zikulagroupsmodule_group_list');
    }

    /**
     * Called from UsersModule/Resources/public/js/Zikula.Users.Admin.View.js
     * to populate a username search
     *
     * @Route("/admin/getusersbyfragmentastable", options={"expose"=true})
     * @Method("POST")
     * @param Request $request
     * @return Response
     */
    public function getUsersByFragmentAsTableAction(Request $request)
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
            'uname' => ['operator' => 'like', 'operand' => "$fragment%"]
        ];
        $users = $this->get('zikula_users_module.user_repository')->query($filter);

        return $this->render('@ZikulaGroupsModule/Membership/userlist.html.twig', [
            'users' => $users,
            'gid' => $request->get('gid'),
            'csrfToken' => $request->get('csrfToken')
        ], new PlainResponse());
    }

    private function getSpecificGroupMessage($groupTypeIsPrivate, $groupTypeIsCore, $groupStateIsClosed, $groupCountIsLimit, $alreadyGroupMember)
    {
        $messages = [];
        $messages[] = $this->__('Sorry!, You cannot apply to join the requested group');
        if ($groupTypeIsPrivate) {
            $messages[] = $this->__('This group is a private group');
        }
        if ($groupTypeIsCore) {
            $messages[] = $this->__('This group is a core-only group');
        }
        if ($groupStateIsClosed) {
            $messages[] = $this->__('This group is closed.');
        }
        if ($groupCountIsLimit) {
            $messages[] = $this->__('This group is has reached its membership limit.');
        }
        if ($alreadyGroupMember) {
            $messages[] = $this->__('You are already a member of this group.');
        }

        return implode ('<br>', $messages);
    }
}
