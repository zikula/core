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

use ModUtil;
use UserUtil;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Zikula\Core\Controller\AbstractController;
use Zikula\Core\Response\PlainResponse;
use Zikula\GroupsModule\Entity\GroupEntity;
use Zikula\ThemeModule\Engine\Annotation\Theme;
use Zikula\UsersModule\Constant as UsersConstant;

/**
 * @Route("/admin")
 *
 * Administrative controllers for the groups module
 */
class AdminController extends AbstractController
{
    /**
     * @Route("/membership/{gid}/{letter}/{startNum}", requirements={"gid" = "^[1-9]\d*$", "letter" = "[a-zA-Z]|\*", "startNum" = "\d+"})
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
    public function groupmembershipAction($gid = 0, $letter = '*', $startNum = 0)
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
     * @Route("/adduser/{uid}/{gid}/{csrfToken}", requirements={"gid" = "^[1-9]\d*$", "uid" = "^[1-9]\d*$"})
     *
     * Add user to a group.
     *
     * @param $uid
     * @param $gid
     * @param $csrfToken
     * @return RedirectResponse
     */
    public function adduserAction($uid, $gid, $csrfToken)
    {
        $this->get('zikula_core.common.csrf_token_handler')->validate($csrfToken);
        if (!$this->hasPermission('ZikulaGroupsModule::', $gid . '::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }
        $group = $this->get('zikula_groups_module.group_repository')->find($gid);
        if (!$group) {
            throw new \InvalidArgumentException($this->__('Sorry! No such group found.'));
        }
        $userEntity = $this->get('zikula_users_module.user_repository')->find($uid);
        if (!$userEntity) {
            throw new \InvalidArgumentException($this->__('Sorry! No such user found.'));
        }

        $userEntity->addGroup($group);
        $this->get('doctrine')->getManager()->flush();
        $this->addFlash('status', $this->__('Done! The user was added to the group.'));

        return $this->redirectToRoute('zikulagroupsmodule_admin_groupmembership', ['gid' => $gid]);
    }

    /**
     * @Route("/removeuser")
     * @Theme("admin")
     * @Template
     *
     * Remove a user from a group.
     *
     * @param Request $request
     *
     * @return mixed Response|void symfony response object if confirmation isn't provided, void otherwise
     *
     * @throws AccessDeniedException Thrown if the user doesn't have edit access to the group
     * @throws \InvalidArgumentException Thrown if the requested group id or User id is invalid
     */
    public function removeuserAction(Request $request)
    {
        $gid = $uid = 0;
        if ($request->isMethod('GET')) {
            $gid = $request->query->getDigits('gid');
            $uid = $request->query->getDigits('uid');
        } elseif ($request->isMethod('POST')) {
            $postVars = $request->request->get('zikulagroupsmodule_removeuser');
            $gid = isset($postVars['gid']) ? $postVars['gid'] : 0;
            $uid = isset($postVars['uid']) ? $postVars['uid'] : 0;
        }
        if ($gid < 1 || $uid < 1) {
            throw new \InvalidArgumentException($this->__('Invalid Group ID or User ID.'));
        }

        $group = ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'get', ['gid' => $gid]);
        if (!$group) {
            throw new NotFoundHttpException($this->__('Sorry! No such group found.'));
        }

        if (!$this->hasPermission('ZikulaGroupsModule::', $gid.'::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        $formValues = [
            'gid' => $gid,
            'uid' => $uid
        ];

        $form = $this->createForm('Zikula\GroupsModule\Form\Type\RemoveUserType', $formValues, [
            'translator' => $this->get('translator.default')
        ]);

        if ($form->handleRequest($request)->isValid()) {
            if ($form->get('remove')->isClicked()) {
                $formData = $form->getData();

                try {
                    // remove user
                    if (ModUtil::apiFunc('ZikulaGroupsModule', 'admin', 'removeuser', ['gid' => $formData['gid'], 'uid' => $formData['uid']])) {
                        // Success
                        $this->addFlash('status', $this->__('Done! The user was removed from the group.'));
                    } else {
                        $this->addFlash('error', $this->__('Error! A problem occurred while attempting to remove the user. The user has not been removed from the group.'));
                    }
                } catch (\RuntimeException $e) {
                    $this->addFlash('error', $e->getMessage());
                }
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Operation cancelled.'));
            }

            return $this->redirectToRoute('zikulagroupsmodule_admin_groupmembership', ['gid' => $formData['gid']]);
        }

        return [
            'form' => $form->createView(),
            'group' => $group,
            'uname' => UserUtil::getVar('uname', $uid)
        ];
    }

    /**
     * @Route("/pendingusers/{action}/{userid}/{gid}", requirements={"action" = "deny|accept", "userid" = "^[1-9]\d*$", "gid" = "^[1-9]\d*$"})
     * @Theme("admin")
     * @Template
     *
     * display a list of group applications
     *
     * @param Request $request
     * @param string  $action  Name of desired action
     * @param int     $userid  Id of the user
     * @param int     $gid     Id of the group
     *
     * @return Response symfony response object
     *
     * @throws \InvalidArgumentException Thrown if either the gid or userid parameters are not provided or
     *                                          if the action parameter isn't one of 'deny' or 'accept'
     * @throws \RuntimeException Thrown if the requested action couldn't be carried out
     */
    public function userpendingAction(Request $request, $action = 'accept', $userid = 0, $gid = 0)
    {
        if ($gid < 1 || $userid < 1) {
            throw new \InvalidArgumentException($this->__('Invalid Group ID or User ID.'));
        }

        $group = ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'get', ['gid' => $gid]);
        if (!$group) {
            throw new NotFoundHttpException($this->__('Sorry! No such group found.'));
        }

        $appInfo = ModUtil::apiFunc('ZikulaGroupsModule', 'admin', 'getapplicationinfo', ['gid' => $gid, 'userid' => $userid]);

        $formValues = [
            'gid' => $gid,
            'userid' => $userid,
            'action' => $action,
            'userName' => UserUtil::getVar('uname', $userid),
            'application' => $appInfo['application']
        ];
        if ($action == 'deny') {
            $formValues['reason'] = $this->__('Sorry! This is a message to inform you with regret that your application for membership of the aforementioned private group has been rejected.');
        }

        $form = $this->createForm('Zikula\GroupsModule\Form\Type\ManageApplicationType', $formValues, [
            'translator' => $this->get('translator.default')
        ]);

        if ($form->handleRequest($request)->isValid()) {
            if ($form->get('save')->isClicked()) {
                $formData = $form->getData();

                $sendtag = isset($formData['sendtag']) ? $formData['sendtag'] : 0;
                $reason = isset($formData['reason']) ? $formData['reason'] : '';

                $reasonTitle = '';
                if ($action == 'deny') {
                    $reasonTitle = $this->__f('Concerning your %s group membership application', ['%s' => $group['name']]);
                    if (empty($reason)) {
                        // Get Default TEXT
                        $reason = $this->__('Sorry! This is a message to inform you with regret that your application for membership of the aforementioned private group has been rejected.');
                    }
                } elseif ($action == 'accept') {
                    $reasonTitle = $this->__f('Done! The user has been added to the %s group.', ['%s' => $group['name']]);
                    if (empty($reason)) {
                        // Get Default TEXT
                        $reason = $this->__('Done! Your application has been accepted. You have been granted all the privileges assigned to the group of which you are now member.');
                    }
                }

                try {
                    $result = ModUtil::apiFunc('ZikulaGroupsModule', 'admin', 'pendingaction', [
                        'userid'      => $userid,
                        'gid'         => $gid,
                        'sendtag'     => $sendtag,
                        'reason'      => $reason,
                        'reasontitle' => $reasonTitle,
                        'action'      => $action
                    ]);

                    if (!$result) {
                        if ($action == 'deny') {
                            $this->addFlash('error', $this->__("Error! Could not execute 'Reject' action."));
                        } else {
                            $this->addFlash('error', $this->__("Error! Could not execute 'Accept' action."));
                        }
                    } else {
                        if ($action == 'accept') {
                            $this->addFlash('status', $this->__('Done! The user was added to the group.'));
                        } else {
                            $this->addFlash('status', $this->__("Done! The user's application for group membership has been rejected."));
                        }
                    }
                } catch (\RuntimeException $e) {
                    $this->addFlash('error', $e->getMessage());
                }
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Operation cancelled.'));
            }

            return $this->redirectToRoute('zikulagroupsmodule_group_list');
        }

        return [
            'form' => $form->createView(),
            'action' => $action
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

        return $this->render('@ZikulaGroupsModule/Admin/userlist.html.twig', [
            'users' => $users,
            'gid' => $request->get('gid'),
            'csrfToken' => $request->get('csrfToken')
        ], new PlainResponse());
    }

    /**
     * @Route("")
     */
    public function indexAction()
    {
        @trigger_error('This method is deprecated. Please use GroupController::listAction', E_USER_DEPRECATED);

        return $this->redirectToRoute('zikulagroupsmodule_group_list');
    }

    /**
     * @Route("/view/{startnum}", requirements={"startnum" = "\d+"})
     */
    public function viewAction($startnum = 0)
    {
        @trigger_error('This method is deprecated. Please use GroupController::listAction', E_USER_DEPRECATED);

        return $this->redirectToRoute('zikulagroupsmodule_group_list', ['startnum' => $startnum]);
    }

    /**
     * @Route("/new")
     */
    public function newgroupAction(Request $request)
    {
        @trigger_error('This method is deprecated. Please use GroupController::newgroupAction', E_USER_DEPRECATED);

        return $this->redirectToRoute('zikulagroupsmodule_group_create');
    }

    /**
     * @Route("/modify/{gid}", requirements={"gid" = "^[1-9]\d*$"})
     */
    public function modifyAction(Request $request, GroupEntity $groupEntity)
    {
        @trigger_error('This method is deprecated. Please use GroupController::modifyAction', E_USER_DEPRECATED);

        return $this->redirectToRoute('zikulagroupsmodule_group_edit', ['gid' => $groupEntity->getGid()]);
    }

    /**
     * @Route("/delete", requirements={"gid"="\d+"})
     */
    public function deleteAction(Request $request, GroupEntity $groupEntity)
    {
        @trigger_error('This method is deprecated. Please use GroupController::deleteAction', E_USER_DEPRECATED);

        return $this->redirectToRoute('zikulagroupsmodule_group_remove', ['gid' => $groupEntity->getGid()]);
    }

    /**
     * @Route("/config")
     */
    public function modifyconfigAction()
    {
        @trigger_error('The zikulagroupsmodule_admin_config route is deprecated. please use zikulagroupsmodule_config_config instead.', E_USER_DEPRECATED);

        return $this->redirectToRoute('zikulagroupsmodule_config_config');
    }
}
