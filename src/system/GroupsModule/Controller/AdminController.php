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
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Zikula\Core\Controller\AbstractController;
use Zikula\GroupsModule\Entity\GroupEntity;
use Zikula\GroupsModule\Form\Type\ManageApplicationType;
use Zikula\ThemeModule\Engine\Annotation\Theme;

/**
 * @Route("/admin")
 *
 * Administrative controllers for the groups module
 */
class AdminController extends AbstractController
{
    /**
     * @Route("/membership/{gid}/{letter}/{startNum}", requirements={"gid" = "^[1-9]\d*$", "letter" = "[a-zA-Z]|\*", "startNum" = "\d+"})
     */
    public function groupmembershipAction($gid = 0, $letter = '*', $startNum = 0)
    {
        @trigger_error('This method is deprecated. Please use MembershipAdministrationController::listAction', E_USER_DEPRECATED);

        return $this->redirectToRoute('zikulagroupsmodule_membershipadministration_list', ['gid' => $gid, 'letter' => $letter, 'startNum' => $startNum]);
    }

    /**
     * @Route("/adduser/{uid}/{gid}/{csrfToken}", requirements={"gid" = "^[1-9]\d*$", "uid" = "^[1-9]\d*$"})
     */
    public function adduserAction($uid, $gid, $csrfToken)
    {
        @trigger_error('This method is deprecated. Please use MembershipAdministrationController::addAction', E_USER_DEPRECATED);

        return $this->redirectToRoute('zikulagroupsmodule_membershipadministration_add', ['uid' => $uid, 'gid' => $gid, 'csrfToken' => $csrfToken]);
    }

    /**
     * @Route("/removeuser/{gid}/{uid}", requirements={"gid" = "^[1-9]\d*$", "uid" = "^[1-9]\d*$"})
     */
    public function removeuserAction(Request $request, $gid = 0, $uid = 0)
    {
        @trigger_error('This method is deprecated. Please use MembershipAdministrationController::addAction', E_USER_DEPRECATED);

        return $this->redirectToRoute('zikulagroupsmodule_membershipadministration_remove', ['uid' => $uid, 'gid' => $gid, 'request' => $request]);
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

        $form = $this->createForm(ManageApplicationType::class, $formValues, [
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
