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
use Zikula\GroupsModule\Helper\CommonHelper;

/**
 * User controllers for the groups module
 */
class UserController extends AbstractController
{
    /**
     * @Route("")
     *
     * Groups Module main user function
     *
     * @return RedirectResponse
     */
    public function indexAction()
    {
        @trigger_error('The zikulagroupsmodule_user_index route is deprecated. please use zikulagroupsmodule_user_view instead.', E_USER_DEPRECATED);

        return $this->redirectToRoute('zikulagroupsmodule_user_view');
    }

    /**
     * @Route("/view/{startnum}", requirements={"startnum" = "\d+"})
     * @Method("GET")
     * @Template
     *
     * Display items
     *
     * @param integer $startnum
     *
     * @return Response symfony response object
     *
     * @throws AccessDeniedException Thrown if the user doesn't have overview access to the module
     */
    public function viewAction($startnum = 0)
    {
        if (!$this->hasPermission('ZikulaGroupsModule::', '::', ACCESS_OVERVIEW)) {
            throw new AccessDeniedException();
        }

        $itemsPerPage = $this->getVar('itemsperpage', 25);

        $currentUserApi = $this->get('zikula_users_module.current_user');
        $isLoggedIn = $currentUserApi->isLoggedIn();

        // get groups (not core, only private and public ones)
        $groups = ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'getallgroups', [
            'startnum' => $startnum,
            'numitems' => $itemsPerPage
        ]);

        $templateParameters = [
            'mainPage' => true
        ];

        // The return value of the function is checked here, and if the function
        // failed then an appropriate message is posted.
        if (!$groups) {
            $templateParameters['nogroups'] = true;

            return $templateParameters;
        }

        $groupItems = [];

        $groupsCommon = new CommonHelper();
        $typeLabels = $groupsCommon->gtypeLabels();
        $stateLabels = $groupsCommon->stateLabels();

        foreach ($groups as $group) {
            if ($this->hasPermission('ZikulaGroupsModule::', $group['gid'].'::', ACCESS_OVERVIEW)) {
                $group['typeLabel']  = $typeLabels[$group['gtype']];
                $group['stateLabel'] = $stateLabels[$group['state']];

                if (true == $isLoggedIn && $this->hasPermission('ZikulaGroupsModule::', $group['gid'].'::', ACCESS_READ)) {
                    // The right to apply
                    $groupItems[] = $this->get('twig')->render('@ZikulaGroupsModule/User/grouprow_read.html.twig', $group);
                } else {
                    // No right to apply
                    $groupItems[] = $this->get('twig')->render('@ZikulaGroupsModule/User/grouprow_overview.tpl', $group);
                }
            }
        }

        $templateParameters['nogroups'] = false;
        $templateParameters['items'] = $groupItems;
        $templateParameters['pager'] = [
            'amountOfItems' => ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'countitems'),
            'itemsPerPage' => $itemsPerPage
        ];

        return $templateParameters;
    }

    /**
     * @Route("/membership/{action}/{gid}", requirements={"action" = "subscribe|unsubscribe|cancel", "gid" = "^[1-9]\d*$"})
     * @Template
     *
     * Display the membership of a public group.
     *
     * @param Request $request
     * @param string $action
     * @param integer $gid
     *
     * @return Response symfony response object
     *
     * @throws \InvalidArgumentException Thrown if the group id is < 1
     * @throws AccessDeniedException Thrown if the user isn't logged in or
     *                                          if the user doesn't have overview access to the module
     * @throws NotFoundHttpException Thrown if the group cannot be found
     */
    public function membershipAction(Request $request, $action = 'cancel', $gid = 0)
    {
        if (!$this->hasPermission('ZikulaGroupsModule::', '::', ACCESS_OVERVIEW)) {
            throw new AccessDeniedException();
        }

        if ($gid < 1) {
            throw new \InvalidArgumentException($this->__('Invalid Group ID received'));
        }

        $currentUserApi = $this->get('zikula_users_module.current_user');

        if (!$currentUserApi->isLoggedIn()) {
            throw new AccessDeniedException($this->__('Error! You must register for a user account on this site before you can apply for membership of a group.'));
        }

        // Check if the group exists
        $group = ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'get', ['gid' => $gid]);
        if (!$group) {
            throw new NotFoundHttpException($this->__('Error! That group does not exist.'));
        }

        // And lastly, we must check if he didn't rewrote the url,
        // that is he applying to an open group and that the group is open
        // $isopen = ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'getginfo', ['gid' => $gid]);
        if ($action == 'subscribe') {
            if (ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'isgroupmember', ['gid' => $gid, 'uid' => $currentUserApi->get('uid')])) {
                $this->addFlash('error', $this->__('Error! You are already a member of this group.'));

                return $this->redirectToRoute('zikulagroupsmodule_user_view');
            }

            if ($group['gtype'] == CommonHelper::GTYPE_CORE) {
                $this->addFlash('error', $this->__('Sorry! You cannot apply for membership of that group.'));

                return $this->redirectToRoute('zikulagroupsmodule_user_view');
            }

            if ($group['nbumax'] != 0) {
                if (($group['nbumax'] - $group['nbuser']) <= 0) {
                    $this->addFlash('error', $this->__('Sorry! That group has reached full membership.'));

                    return $this->redirectToRoute('zikulagroupsmodule_user_view');
                }
            }

            if ($group['state'] == CommonHelper::STATE_CLOSED) {
                $this->addFlash('error', $this->__('Sorry! That group is closed.'));

                return $this->redirectToRoute('zikulagroupsmodule_user_view');
            }
        }

        $formData = [
            'gid' => $gid,
            'theAction' => $action,
            'groupType' => $group['gtype'],
            'groupName' => $group['name'],
            'groupDescription' => $group['description'],
            'applyText' => ''
        ];

        $form = $this->createForm('Zikula\GroupsModule\Form\Type\MembershipApplicationType',
            $formData, [
                'translator' => $this->get('translator.default'),
                'theAction' => $action,
                'groupType' => $group['gtype']
            ]
        );

        if ($form->handleRequest($request)->isValid()) {
            if ($form->get('apply')->isClicked()) {
                $formData = $form->getData();

                $gid = $formData['gid'];
                $action = $formData['theAction'];
                $groupType = $formData['groupType'];

                if (empty($gid) || !is_numeric($gid) || empty($action)) {
                    throw new \InvalidArgumentException($this->__('Invalid arguments received'));
                }

                $applyText = '';
                if ($action == 'subscribe' && $groupType == CommonHelper::GTYPE_PRIVATE) {
                    $applyText = isset($formData['applyText']) ? $formData['applyText'] : '';
                }

                $result = ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'userupdate', [
                    'gid'       => $gid,
                    'action'    => $action,
                    'gtype'     => $groupType,
                    'applytext' => $applyText
                ]);

                if (true == $result) {
                    $this->addFlash('status', $this->__('Done! Saved the action.'));
                }
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Operation cancelled.'));
            }

            return $this->redirectToRoute('zikulagroupsmodule_user_view');
        }

        return [
            'mainPage' => false,
            'form' => $form->createView(),
            'groupType' => $group['gtype'],
            'theAction' => $action
        ];
    }

    /**
     * @Route("/memberlist/{gid}/{startnum}", requirements={"gid" = "^[1-9]\d*$", "startnum" = "\d+"})
     * @Method("GET")
     * @Template
     *
     * display the membership of a group
     *
     * @param integer $gid
     * @param integer $startnum
     *
     * @return Response symfony response object
     *
     * @throws \InvalidArgumentException Thrown if the gid < 1
     * @throws NotFoundHttpException Thrown if the requested group isn't found
     * @throws AccessDeniedException Thrown if the user doesn't have overview access to the memberslist component of the module
     */
    public function memberslistAction($gid = 0, $startnum = 0)
    {
        if ($gid < 1) {
            throw new \InvalidArgumentException($this->__('Invalid Group ID received'));
        }

        if (!$this->hasPermission('ZikulaGroupsModule::memberslist', '::', ACCESS_OVERVIEW)) {
            throw new AccessDeniedException();
        }

        $itemsPerPage = $this->getVar('itemsperpage', 25);

        $group = ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'get', [
            'gid' => $gid,
            'numitems' => $itemsPerPage,
            'startnum' => $startnum
        ]);

        if (!$group) {
            throw new NotFoundHttpException($this->__('Error! Could not load data.'));
        }

        $groupsCommon = new CommonHelper();
        $typeLabels = $groupsCommon->gtypeLabels();
        $stateLabels = $groupsCommon->stateLabels();

        $group['typelbl']  = $typeLabels[$group['gtype']];
        $group['statelbl'] = $stateLabels[$group['state']];

        $templateParameters = [
            'mainPage' => false,
            'group' => $group
        ];

        $members = false;
        if ($group['members']) {
            $onlines = ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'whosonline');

            $members = [];
            foreach ($group['members'] as $userid) {
                $userInfo = UserUtil::getVars($userid['uid']);

                $userInfo['isonline'] = false;
                if (is_array($onlines)) {
                    foreach ($onlines as $online) {
                        if ($online['uid'] == $userid['uid']) {
                            $userInfo['isonline'] = true;
                            break;
                        }
                    }
                }
                $members[] = $userInfo;
            }

            // test of sorting data
            if (!empty($members)) {
                $sortAarr = [];
                foreach ($members as $res) {
                    $sortAarr[] = strtolower($res['uname']);
                }
                array_multisort($sortAarr, SORT_ASC, $members);
            }
        }

        $currentUserApi = $this->get('zikula_users_module.current_user');
        $isMember = false;
        if ($currentUserApi->isLoggedIn()) {
            $isMember = ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'isgroupmember', ['gid' => $gid, 'uid' => $currentUserApi->get('uid')]);
        }

        $templateParameters['members'] = $members;
        $templateParameters['isMember'] = $isMember;
        $templateParameters['pager'] = [
            'amountOfItems' => ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'countgroupmembers', ['gid' => $gid]),
            'itemsPerPage' => $itemsPerPage
        ];

        return $templateParameters;
    }
}
