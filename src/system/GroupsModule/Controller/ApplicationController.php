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

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Zikula\Core\Controller\AbstractController;
use Zikula\GroupsModule\Helper\CommonHelper;
use Zikula\ThemeModule\Engine\Annotation\Theme;

/**
 * @Route("/application")
 */
class ApplicationController extends AbstractController
{
    /**
     * @Route("/pending/{action}/{userid}/{gid}", requirements={"action" = "deny|accept", "userid" = "^[1-9]\d*$", "gid" = "^[1-9]\d*$"})
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
     * @return array|RedirectResponse
     *
     * @throws \InvalidArgumentException Thrown if either the gid or userid parameters are not provided or
     *                                          if the action parameter isn't one of 'deny' or 'accept'
     * @throws \RuntimeException Thrown if the requested action couldn't be carried out
     */
    public function pendingAction(Request $request, $action = 'accept', $userid = 0, $gid = 0)
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

            return $this->redirectToRoute('zikulagroupsmodule_group_adminlist');
        }

        return [
            'form' => $form->createView(),
            'action' => $action
        ];
    }

    /**
     * @Route("/update/{action}/{gid}", requirements={"action" = "subscribe|unsubscribe|cancel", "gid" = "^[1-9]\d*$"})
     * @Template
     *
     * Display the membership of a public group.
     *
     * @param Request $request
     * @param string $action
     * @param integer $gid
     *
     * @return array|RedirectResponse
     *
     * @throws \InvalidArgumentException Thrown if the group id is < 1
     * @throws AccessDeniedException Thrown if the user isn't logged in or
     *                                          if the user doesn't have overview access to the module
     * @throws NotFoundHttpException Thrown if the group cannot be found
     */
    public function updateAction(Request $request, $action = 'cancel', $gid = 0)
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

                return $this->redirectToRoute('zikulagroupsmodule_group_list');
            }

            if ($group['gtype'] == CommonHelper::GTYPE_CORE) {
                $this->addFlash('error', $this->__('Sorry! You cannot apply for membership of that group.'));

                return $this->redirectToRoute('zikulagroupsmodule_group_list');
            }

            if ($group['nbumax'] != 0) {
                if (($group['nbumax'] - $group['nbuser']) <= 0) {
                    $this->addFlash('error', $this->__('Sorry! That group has reached full membership.'));

                    return $this->redirectToRoute('zikulagroupsmodule_group_list');
                }
            }

            if ($group['state'] == CommonHelper::STATE_CLOSED) {
                $this->addFlash('error', $this->__('Sorry! That group is closed.'));

                return $this->redirectToRoute('zikulagroupsmodule_group_list');
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

            return $this->redirectToRoute('zikulagroupsmodule_group_list');
        }

        return [
            'mainPage' => false,
            'form' => $form->createView(),
            'groupType' => $group['gtype'],
            'theAction' => $action
        ];
    }
}
