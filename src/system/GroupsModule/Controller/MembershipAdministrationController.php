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
use Zikula\GroupsModule\GroupEvents;
use Zikula\ThemeModule\Engine\Annotation\Theme;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\UsersModule\Entity\UserEntity;

/**
 * @Route("/admin/membership")
 *
 * Administrative controllers for the groups module
 */
class MembershipAdministrationController extends AbstractController
{
    /**
     * @Route("/list/{gid}/{letter}/{startNum}", requirements={"gid" = "^[1-9]\d*$", "letter" = "[a-zA-Z]|\*", "startNum" = "\d+"})
     * @Method("GET")
     * @Theme("admin")
     * @Template
     *
     * Display all members of a group.
     *
     * @param integer $gid the id of the group to list membership for
     * @param string $letter the letter from the alpha filter
     * @param integer $startNum the start item number for the pager
     *
     * @return array
     *
     * @throws \InvalidArgumentException Thrown if the requested group id is invalid
     * @throws AccessDeniedException Thrown if the user doesn't have edit access to the group
     */
    public function listAction($gid = 0, $letter = '*', $startNum = 0)
    {
        if (!$this->hasPermission('ZikulaGroupsModule::', $gid . '::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }
        $group = $this->get('zikula_groups_module.group_repository')->find($gid);
        if (!$group) {
            throw new \InvalidArgumentException($this->__('Invalid Group ID.'));
        }

        return [
            'group' => $group,
//            'pager' => [
//                'amountOfItems' => count($usersNotInGroup),
//                'itemsPerPage' => $this->getVar('itemsperpage', 25);
//            ],
            'csrfToken' => $this->get('zikula_core.common.csrf_token_handler')->generate()
        ];
    }

    /**
     * @Route("/add/{uid}/{gid}/{csrfToken}", requirements={"gid" = "^[1-9]\d*$", "uid" = "^[1-9]\d*$"})
     *
     * Add user to a group.
     *
     * @param $uid
     * @param $gid
     * @param $csrfToken
     * @return RedirectResponse
     */
    public function addAction($uid, $gid, $csrfToken)
    {
        $this->get('zikula_core.common.csrf_token_handler')->validate($csrfToken);
        if (!$this->hasPermission('ZikulaGroupsModule::', $gid . '::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }
        $group = $this->get('zikula_groups_module.group_repository')->find($gid);
        if (!$group) {
            throw new \InvalidArgumentException($this->__('Invalid Group ID.'));
        }
        /** @var UserEntity $userEntity */
        $userEntity = $this->get('zikula_users_module.user_repository')->find($uid);
        if (!$userEntity) {
            throw new \InvalidArgumentException($this->__('Invalid User ID.'));
        }

        if ($userEntity->getGroups()->contains($group)) {
            $this->addFlash('warning', $this->__('The selected user is already a member of this group.'));
        } else {
            $userEntity->addGroup($group);
            $this->get('doctrine')->getManager()->flush();
            $this->addFlash('status', $this->__('Done! The user was added to the group.'));
            // Let other modules know that we have updated a group.
            $adduserEvent = new GenericEvent(['gid' => $gid, 'uid' => $uid]);
            $this->get('event_dispatcher')->dispatch(GroupEvents::GROUP_ADD_USER, $adduserEvent);
        }

        return $this->redirectToRoute('zikulagroupsmodule_membershipadministration_list', ['gid' => $gid]);
    }

    /**
     * @Route("/remove/{gid}/{uid}", requirements={"gid" = "^[1-9]\d*$", "uid" = "^[1-9]\d*$"})
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
                $removeuserEvent = new GenericEvent(null, ['gid' => $gid, 'uid' => $uid]);
                $this->get('event_dispatcher')->dispatch(GroupEvents::GROUP_REMOVE_USER, $removeuserEvent);
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Operation cancelled.'));
            }

            return $this->redirectToRoute('zikulagroupsmodule_membershipadministration_list', ['gid' => $group->getGid()]);
        }

        return [
            'form' => $form->createView(),
            'group' => $group,
            'uname' => $user->getUname()
        ];
    }

    /**
     * Called from UsersModule/Resources/public/js/Zikula.Users.Admin.View.js
     * to populate a username search
     *
     * @Route("/getusersbyfragmentastable", options={"expose"=true})
     * @Method("POST")
     * @param Request $request
     * @return Response
     */
    public function getUsersByFragmentAsTableAction(Request $request)
    {
        if (!$this->hasPermission('ZikulaGroupsodule', '::', ACCESS_MODERATE)) {
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

        return $this->render('@ZikulaGroupsModule/MembershipAdministration/userlist.html.twig', [
            'users' => $users,
            'gid' => $request->get('gid'),
            'csrfToken' => $request->get('csrfToken')
        ], new PlainResponse());
    }
}
