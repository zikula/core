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

use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Zikula\Core\Controller\AbstractController;
use Zikula\GroupsModule\Entity\GroupEntity;

/**
 * @Route("/admin")
 * @deprecated
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
        @trigger_error('This method is deprecated. Please use MembershipController::adminListAction', E_USER_DEPRECATED);

        return $this->redirectToRoute('zikulagroupsmodule_membership_adminlist', ['gid' => $gid, 'letter' => $letter, 'startNum' => $startNum]);
    }

    /**
     * @Route("/adduser/{uid}/{gid}/{csrfToken}", requirements={"gid" = "^[1-9]\d*$", "uid" = "^[1-9]\d*$"})
     */
    public function adduserAction($uid, $gid, $csrfToken)
    {
        @trigger_error('This method is deprecated. Please use MembershipController::addAction', E_USER_DEPRECATED);

        return $this->redirectToRoute('zikulagroupsmodule_membership_add', ['uid' => $uid, 'gid' => $gid, 'csrfToken' => $csrfToken]);
    }

    /**
     * @Route("/removeuser/{gid}/{uid}", requirements={"gid" = "^[1-9]\d*$", "uid" = "^[1-9]\d*$"})
     */
    public function removeuserAction(Request $request, $gid = 0, $uid = 0)
    {
        @trigger_error('This method is deprecated. Please use MembershipController::addAction', E_USER_DEPRECATED);

        return $this->redirectToRoute('zikulagroupsmodule_membership_remove', ['uid' => $uid, 'gid' => $gid, 'request' => $request]);
    }

    /**
     * @Route("/pendingusers/{action}/{userid}/{gid}", requirements={"action" = "deny|accept", "userid" = "^[1-9]\d*$", "gid" = "^[1-9]\d*$"})
     */
    public function userpendingAction(Request $request, $action = 'accept', $userid = 0, $gid = 0)
    {
        @trigger_error('This method is deprecated. Please use ApplicationController::adminAction', E_USER_DEPRECATED);
        $application = $this->get('zikula_groups_module.group_application_repository')->findOneBy(['group' => $gid, 'user' => $userid]);

        return $this->redirectToRoute('zikulagroupsmodule_application_admin', ['request' => $request, 'action' => $action, 'app_id' => $application->getApp_id()]);
    }

    /**
     * @Route("")
     */
    public function indexAction()
    {
        @trigger_error('This method is deprecated. Please use GroupController::listAction', E_USER_DEPRECATED);

        return $this->redirectToRoute('zikulagroupsmodule_group_adminlist');
    }

    /**
     * @Route("/view/{startnum}", requirements={"startnum" = "\d+"})
     */
    public function viewAction($startnum = 0)
    {
        @trigger_error('This method is deprecated. Please use GroupController::listAction', E_USER_DEPRECATED);

        return $this->redirectToRoute('zikulagroupsmodule_group_adminlist', ['startnum' => $startnum]);
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
