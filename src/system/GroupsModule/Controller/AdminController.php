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
use Users_Constant;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Zikula\Core\Controller\AbstractController;
use Zikula\GroupsModule\Entity\GroupEntity;
use Zikula\ThemeModule\Engine\Annotation\Theme;

/**
 * @Route("/admin")
 *
 * Administrative controllers for the groups module
 */
class AdminController extends AbstractController
{
    /**
     * @Route("/membership/{gid}/{letter}/{startnum}", requirements={"gid" = "^[1-9]\d*$", "letter" = "[a-zA-Z]|\*", "startnum" = "\d+"})
     * @Method("GET")
     * @Theme("admin")
     * @Template
     *
     * Display all members of a group.
     *
     * @param integer $gid the id of the group to list membership for
     * @param string $letter the letter from the alpha filter
     * @param integer $startnum the start item number for the pager
     *
     * @return Response symfony response object
     *
     * @throws \InvalidArgumentException Thrown if the requested group id is invalid
     * @throws AccessDeniedException Thrown if the user doesn't have edit access to the group
     */
    public function groupmembershipAction($gid = 0, $letter = '*', $startnum = 0)
    {
        if ($gid < 1) {
            throw new \InvalidArgumentException($this->__('Invalid Group ID.'));
        }

        // The user API function is called.
        $group = ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'get', [
            'gid'      => $gid,
            'startnum' => $startnum,
            'numitems' => $this->getVar('itemsperpage')
        ]);

        if (!$this->hasPermission('ZikulaGroupsModule::', $group['gid'].'::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        $users = $group['members'];

        $currentUid = UserUtil::getVar('uid');
        $defaultGroup = $this->getVar('defaultgroup', 1);
        $primaryAdminGroup = $this->getVar('primaryadmingroup', 2);

        $groupMembers = [];

        if (is_array($users)) {
            foreach ($users as $user) {
                $options = [];

                if ($user['uid'] == $currentUid
                    && ($group['gid'] == $defaultGroup || $group['gid'] == $primaryAdminGroup)) {
                    $options[] = [];
                } else {
                    $options[] = [
                        'url' => $this->get('router')->generate('zikulagroupsmodule_admin_removeuser', ['gid' => $group['gid'], 'uid' => $user['uid']]),
                        'icon' => 'user-times',
                        'uid'     => $user['uid'],
                        'title'   => $this->__('Remove user from group')
                    ];
                }

                $isRegistration = UserUtil::isRegistration($user['uid']);
                $groupMembers[] = [
                    'uname'   => UserUtil::getVar('uname', $user['uid'], null, $isRegistration),
                    'name'    => UserUtil::getVar('name', $user['uid'], null, $isRegistration),
                    'email'   => UserUtil::getVar('email', $user['uid'], null, $isRegistration),
                    'uid'     => $user['uid'],
                    'options' => $options
                ];
            }
        }

        // sort alphabetically.
        $sortAarr = [];
        foreach ($groupMembers as $res) {
            $sortAarr[] = strtolower($res['uname']);
        }
        array_multisort($sortAarr, SORT_ASC, $groupMembers);


        // check for a letter parameter
        if (strlen($letter) != 1) {
            $letter = '*';
        }

        switch ($letter) {
            case '*':
                // read allusers
                $field = '';
                $expression = '';
                break;

            default:
                $field = 'uname';
                $expression = $letter . '%';
        }

        $users = UserUtil::getAll('uname', 'ASC', null, null, '', $field, $expression);

        $allUsers = [];
        foreach ($users as $user) {
            if ($user['uid'] == 0 || strtolower($user['uname']) == 'anonymous' || strtolower($user['uname']) == 'guest'
                || $user['uname'] == $this->getVar(Users_Constant::MODVAR_ANONYMOUS_DISPLAY_NAME)
            ) {
                continue;
            }
            $alias = '';
            if (!empty($user['name'])) {
                $alias = ' (' . $user['name'] . ')';
            }
            $allUsers[$user['uid']] = $user['uname'] . $alias;
        }

        // Now lets remove the users that are currently part of the group
        // flip the array so we have the user id's as the key
        // this makes the array the same is the group members array
        // from the get function
        $flippedUsers = array_flip($allUsers);
        // now lets diff the array
        $diffedUsers = array_diff($flippedUsers, array_keys($group['members']));
        // now flip the array back
        $allUsers = array_flip($diffedUsers);
        // sort the users by user name
        natcasesort($allUsers);

        return [
            'group' => $group,
            'groupMembers' => $groupMembers,
            // the users not in the group
            'pager' => [
                'amountOfItems' => ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'countgroupmembers', ['gid' => $gid]),
                'itemsPerPage' => $this->getVar('itemsperpage')
            ],
            'uids' => $allUsers,
            'csrfToken' => $this->get('zikula_core.common.csrf_token_handler')->generate()
        ];
    }

    /**
     * @Route("/adduser")
     * @Method("POST")
     *
     * Add user(s) to a group.
     *
     * @param Request $request
     *
     *       int   $gid The id of the group
     *       mixed $uid The id of the user (int) or an array of userids
     *
     * @return RedirectResponse
     *
     * @throws \InvalidArgumentException Thrown if the requested group id is invalid
     */
    public function adduserAction(Request $request)
    {
        $this->get('zikula_core.common.csrf_token_handler')->validate($request->request->get('csrftoken'));

        // Get parameters from the request
        $gid = $request->request->getDigits('gid', 0);
        $uid = $request->request->get('uid', null);

        if ($gid < 1) {
            throw new \InvalidArgumentException($this->__('Invalid Group ID.'));
        }

        $group = ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'get', ['gid' => $gid]);
        if (!$group) {
            throw new NotFoundHttpException($this->__('Sorry! No such group found.'));
        }

        if (is_array($uid)) {
            $totalUsersAdded = 0;
            $totalUsersNotadded = 0;

            foreach ($uid as $id) {
                if (!ModUtil::apiFunc('ZikulaGroupsModule', 'admin', 'adduser', ['gid' => $gid, 'uid' => $id])) {
                    $totalUsersNotadded++;
                } else {
                    $totalUsersAdded++;
                }
            }

            if ($totalUsersAdded > 0) {
                $this->addFlash('status', $this->_fn('Done! %s user was added to the group.', 'Done! %s users were added to the group.', $totalUsersAdded, ['%s' => $totalUsersAdded]));
            }
            if ($totalUsersNotadded > 0) {
                $this->addFlash('error', $this->_fn('Error! %s user was not added to the group.', 'Error! %s users were not added to the group.', $totalUsersNotadded, ['%s' => $totalUsersNotadded]));
            }
        } else {
            if (!ModUtil::apiFunc('ZikulaGroupsModule', 'admin', 'adduser', ['gid' => $gid, 'uid' => $uid])) {
                $this->addFlash('error', $this->__('Error! A problem occurred and the user was not added to the group.'));
            } else {
                $this->addFlash('status', $this->__('Done! The user was added to the group.'));
            }
        }

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
