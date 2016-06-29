<?php
/**
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
use Zikula\GroupsModule\Helper\CommonHelper;
use Zikula\ThemeModule\Engine\Annotation\Theme;

/**
 * @Route("/admin")
 *
 * Administrative controllers for the groups module
 */
class AdminController extends AbstractController
{
    /**
     * @Route("")
     *
     * Main administration function
     *
     * @throws AccessDeniedException if the user doesn't have edit permission to any groups
     *
     * @return RedirectResponse
     */
    public function indexAction()
    {
        $any_access = false;

        // get all groups from the API
        $groups = ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'getall');
        if (is_array($groups)) {
            foreach ($groups as $group) {
                if ($this->hasPermission('ZikulaGroupsModule::', $group['gid'] . '::', ACCESS_EDIT)) {
                    $any_access = true;
                    break;
                }
            }
        }

        if (!$any_access) {
            // we found no groups that we are allowed to administer
            throw new AccessDeniedException();
        }

        return $this->redirectToRoute('zikulagroupsmodule_admin_view');
    }

    /**
     * @Route("/view/{startnum}", requirements={"startnum" = "\d+"})
     * @Method("GET")
     * @Theme("admin")
     * @Template
     *
     * View all groups
     *
     * This function creates a tabular output of all group items in the module.
     *
     * @param integer $startnum
     *
     * @return Response symfony response object
     *
     * @throws AccessDeniedException Thrown if the user hasn't permissions to administer any groups
     */
    public function viewAction($startnum = 0)
    {
        $itemsPerPage = $this->getVar('itemsperpage', 25);

        // get the default user group
        $defaultGroup = $this->getVar('defaultgroup');

        // get the primary admin group
        $primaryAdminGroup = $this->getVar('primaryadmingroup', 2);

        // The user API function is called.
        $items = ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'getall', [
            'startnum' => $startnum,
            'numitems' => $itemsPerPage
        ]);

        // Setting various defines
        $groupsCommon = new CommonHelper();
        $typeLabels = $groupsCommon->gtypeLabels();
        $stateLabels = $groupsCommon->stateLabels();

        $groups = [];
        $router = $this->get('router');

        foreach ($items as $item) {
            if (!$this->hasPermission('ZikulaGroupsModule::', $item['gid'].'::', ACCESS_EDIT)) {
                continue;
            }

            // Options for the item.
            $options = [];

            $routeArgs = ['gid' => $item['gid']];
            $editUrl = $router->generate('zikulagroupsmodule_admin_modify', $routeArgs);
            $membersUrl = $router->generate('zikulagroupsmodule_admin_groupmembership', $routeArgs);

            $options[] = [
                'url' => $router->generate('zikulagroupsmodule_admin_modify', $routeArgs),
                'title'   => $this->__('Edit'),
                'icon' => 'pencil'
            ];

            if ($this->hasPermission('ZikulaGroupsModule::', $item['gid'].'::', ACCESS_DELETE)
                    && $item['gid'] != $defaultGroup && $item['gid'] != $primaryAdminGroup) {
                $options[] = [
                    'url' => $router->generate('zikulagroupsmodule_admin_delete', $routeArgs),
                    'title'   => $this->__('Delete'),
                    'icon' => 'trash-o'
                ];
            }

            $options[] = [
                'url' => $router->generate('zikulagroupsmodule_admin_groupmembership', $routeArgs),
                'title'   => $this->__('Group membership'),
                'icon' => 'users'
            ];

            $nbuser = ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'countgroupmembers', ['gid' => $item['gid']]);

            $groups[] = [
                'name' => $item['name'],
                'gid'         => $item['gid'],
                'gtype'       => $item['gtype'],
                'gtypelbl'    => $typeLabels[$item['gtype']],
                'description' => (!empty($item['description']) ? $item['description'] : ''),
                'prefix'      => $item['prefix'],
                'state'       => $item['state'],
                'statelbl'    => $stateLabels[$item['state']],
                'nbuser'      => ($nbuser != false ? $nbuser : 0),
                'nbumax'      => $item['nbumax'],
                'link'        => $item['link'],
                'uidmaster'   => $item['uidmaster'],
                'options'     => $options,
                'editurl'     => $editUrl,
                'membersurl'  => $membersUrl
            ];
        }

        if (count($groups) == 0) {
            // groups array is empty
            throw new AccessDeniedException();
        }

        // The admin API function is called. This fetch the pending applications if any.
        // permission check for the group is done in this function
        $users = ModUtil::apiFunc('ZikulaGroupsModule', 'admin', 'getapplications', [
            'startnum' => $startnum,
            'numitems' => $itemsPerPage
        ]);

        return [
            'groups' => $groups,
            //'groupTypes' => $typeLabels,
            //'states' => $stateLabels,
            'userItems' => $users,
            'defaultGroup' => $defaultGroup,
            'primaryAdminGroup' => $primaryAdminGroup,
            'pager' => [
                'amountOfItems' => ModUtil::apiFunc('ZikulaGroupsModule', 'admin', 'countitems'),
                'itemsPerPage' => $itemsPerPage
            ]
        ];
    }

    /**
     * @Route("/new")
     * @Theme("admin")
     * @Template
     *
     * Display a form to add a new group.
     *
     * @param Request $request
     *
     * @return Response symfony response object.
     *
     * @throws AccessDeniedException Thrown if the user doesn't have add access to the module
     */
    public function newgroupAction(Request $request)
    {
        if (!$this->hasPermission('ZikulaGroupsModule::', '::', ACCESS_ADD)) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm('Zikula\GroupsModule\Form\Type\CreateGroupType', [], [
            'translator' => $this->get('translator.default')
        ]);

        if ($form->handleRequest($request)->isValid()) {
            if ($form->get('save')->isClicked()) {
                $formData = $form->getData();

                $name = isset($formData['name']) ? $formData['name'] : '';

                // check if group exists
                $check = ModUtil::apiFunc('ZikulaGroupsModule', 'admin', 'getgidbyname', ['name' => $name]);
                if (false != $check) {
                    // Group already exists
                    $this->addFlash('error', $this->__('Error! There is already a group with that name.'));

                    return $this->redirectToRoute('zikulagroupsmodule_admin_view');
                }

                try {
                    // create new group
                    $gid = ModUtil::apiFunc('ZikulaGroupsModule', 'admin', 'create', $formData);
                    if (false != $gid) {
                        // Success
                        $this->addFlash('status', $this->__('Done! Created the group.'));
                    } else {
                        $this->addFlash('error', $this->__('Error! A problem occurred while attempting to create the group. The group has not been created.'));
                    }
                } catch (\RuntimeException $e) {
                    $this->addFlash('error', $e->getMessage());
                }
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Operation cancelled.'));
            }

            return $this->redirectToRoute('zikulagroupsmodule_admin_view');
        }

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/modify/{gid}", requirements={"gid" = "^[1-9]\d*$"})
     * @Theme("admin")
     * @Template
     *
     * Modify a group.
     *
     * @param Request $request
     * @param integer $gid     the id of the group to be modified
     *
     * @return Response symfony response object.
     *
     * @throws NotFoundHttpException Thrown if the requested group isn't found
     * @throws AccessDeniedException Thrown if the user doesn't have edit access to the group
     */
    public function modifyAction(Request $request, $gid = 0)
    {
        $group = ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'get', ['gid' => $gid]);
        if (!$group) {
            throw new NotFoundHttpException($this->__('Sorry! No such group found.'));
        }

        if (!$this->hasPermission('ZikulaGroupsModule::', $group['gid'].'::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        $formData = [];
        foreach (['gid', 'name', 'gtype', 'state', 'nbumax', 'description'] as $fieldName) {
            $formData[$fieldName] = $group[$fieldName];
        }

        $form = $this->createForm('Zikula\GroupsModule\Form\Type\EditGroupType', $formData, [
            'translator' => $this->get('translator.default')
        ]);

        if ($form->handleRequest($request)->isValid()) {
            if ($form->get('save')->isClicked()) {
                $formData = $form->getData();

                $name = isset($formData['name']) ? $formData['name'] : '';

                // check if group exists
                $check = ModUtil::apiFunc('ZikulaGroupsModule', 'admin', 'getgidbyname', ['name' => $name]);
                if (false != $check && $gid != $check) {
                    // Group already exists
                    $this->addFlash('error', $this->__('Error! There is already a group with that name.'));

                    return $this->redirectToRoute('zikulagroupsmodule_admin_view');
                }

                try {
                    // update the group
                    if (ModUtil::apiFunc('ZikulaGroupsModule', 'admin', 'update', $formData)) {
                        // Success
                        $this->addFlash('status', $this->__('Done! Updated the group.'));
                    } else {
                        $this->addFlash('error', $this->__('Error! A problem occurred while attempting to update the group. The group has not been updated.'));
                    }
                } catch (\RuntimeException $e) {
                    $this->addFlash('error', $e->getMessage());
                }
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Operation cancelled.'));
            }

            return $this->redirectToRoute('zikulagroupsmodule_admin_view');
        }

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/delete", requirements={"gid"="\d+"})
     * @Theme("admin")
     * @Template
     *
     * Deletes a group.
     *
     * @param Request $request
     * @param int     $gid     the id of the group to be deleted
     *
     * @return Response|void response object if no confirmation, void otherwise
     *
     * @throws NotFoundHttpException Thrown if the requested group is not found
     * @throws \InvalidArgumentException Thrown if the requested group id is invalid
     * @throws AccessDeniedException Thrown if the user doesn't have delete access to the group
     */
    public function deleteAction(Request $request, $gid)
    {
        if ($gid < 1) {
            throw new \InvalidArgumentException($this->__('Invalid Group ID.'));
        }

        $group = ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'get', ['gid' => $gid]);
        if (!$group) {
            throw new NotFoundHttpException($this->__('Sorry! No such group found.'));
        }

        if (!$this->hasPermission('ZikulaGroupsModule::', $group['gid'].'::', ACCESS_DELETE)) {
            throw new AccessDeniedException();
        }

        // get the user default group - we do not allow its deletion
        $defaultGroup = $this->getVar('defaultgroup', 1);
        if ($group['gid'] == $defaultGroup) {
            $this->addFlash('error', $this->__('Error! You cannot delete the default user group.'));

            return $this->redirectToRoute('zikulagroupsmodule_admin_view');
        }

        // get the user default group - we do not allow its deletion
        $primaryAdminGroup = $this->getVar('primaryadmingroup', 2);
        if ($group['gid'] == $primaryAdminGroup) {
            $this->addFlash('error', $this->__('Error! You cannot delete the primary administration group.'));

            return $this->redirectToRoute('zikulagroupsmodule_admin_view');
        }

        $formValues = [
            'gid' => $gid
        ];

        $form = $this->createForm('Zikula\GroupsModule\Form\Type\DeleteGroupType', $formValues, [
            'translator' => $this->get('translator.default')
        ]);

        if ($form->handleRequest($request)->isValid()) {
            if ($form->get('delete')->isClicked()) {
                $formData = $form->getData();

                try {
                    // delete group
                    $delete = ModUtil::apiFunc('ZikulaGroupsModule', 'admin', 'delete', ['gid' => $formData['gid']]);
                    if ($delete) {
                        $this->addFlash('status', $this->__('Done! Group deleted.'));
                    } else {
                        $this->addFlash('error', $this->__('Error! A problem occurred while attempting to delete the group. The group has not been deleted.'));
                    }
                } catch (\RuntimeException $e) {
                    $this->addFlash('error', $e->getMessage());
                }
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Operation cancelled.'));
            }

            return $this->redirectToRoute('zikulagroupsmodule_admin_view');
        }

        return [
            'form' => $form->createView(),
            'group' => $group
        ];
    }

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
     * @param string  $action  Name of desired action.
     * @param int     $userid  Id of the user.
     * @param int     $gid     Id of the group.
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
                    $reasonTitle = $this->__f('Concerning your %s group membership application', $group['name']);
                    if (empty($reason)) {
                        // Get Default TEXT
                        $reason = $this->__('Sorry! This is a message to inform you with regret that your application for membership of the aforementioned private group has been rejected.');
                    }
                } elseif ($action == 'accept') {
                    $reasonTitle = $this->__f('Done! The user has been added to the %s group.', $group['name']);
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

            return $this->redirectToRoute('zikulagroupsmodule_admin_view');
        }

        return [
            'form' => $form->createView(),
            'action' => $action
        ];
    }

    /**
     * @Route("/config")
     *
     * Display a form to modify configuration parameters of the module.
     *
     * @return Response symfony response object
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin access to the module
     */
    public function modifyconfigAction()
    {
        @trigger_error('The zikulagroupsmodule_admin_config route is deprecated. please use zikulagroupsmodule_config_config instead.', E_USER_DEPRECATED);

        return $this->redirectToRoute('zikulagroupsmodule_config_config');
    }
}
