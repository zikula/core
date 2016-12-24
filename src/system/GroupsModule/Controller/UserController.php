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
use Zikula\GroupsModule\Helper\CommonHelper;

/**
 * Class UserController
 * @deprecated
 */
class UserController extends AbstractController
{
    /**
     * @Route("")
     */
    public function indexAction()
    {
        @trigger_error('The zikulagroupsmodule_user_index route is deprecated. Please use zikulagroupsmodule_group_list instead.', E_USER_DEPRECATED);

        return $this->redirectToRoute('zikulagroupsmodule_group_list');
    }

    /**
     * @Route("/view/{startnum}", requirements={"startnum" = "\d+"})
     */
    public function viewAction($startnum = 0)
    {
        @trigger_error('The zikulagroupsmodule_user_view route is deprecated. Please use zikulagroupsmodule_group_list instead.', E_USER_DEPRECATED);

        return $this->redirectToRoute('zikulagroupsmodule_group_list', ['startnum' => $startnum]);
    }

    /**
     * @Route("/membership/{action}/{gid}", requirements={"action" = "subscribe|unsubscribe|cancel", "gid" = "^[1-9]\d*$"})
     */
    public function membershipAction(Request $request, $action = 'cancel', $gid = 0)
    {
        @trigger_error('The zikulagroupsmodule_user_membership route is deprecated.', E_USER_DEPRECATED);
        $group = $this->get('zikula_groups_module.group_repository')->find($gid);
        if ($action == 'unsubscribe') {
            return $this->redirectToRoute('zikulagroupsmodule_membership_leave', ['gid' => $gid]);
        } else if ($action == 'subscribe' && $group->getType() == CommonHelper::GTYPE_PRIVATE) {
            return $this->redirectToRoute('zikulagroupsmodule_application_create', ['gid' => $gid]);
        }

        return $this->redirectToRoute('zikulagroupsmodule_membership_join', ['gid' => $gid]);
    }

    /**
     * @Route("/memberlist/{gid}/{startnum}", requirements={"gid" = "^[1-9]\d*$", "startnum" = "\d+"})
     */
    public function memberslistAction($gid = 0, $startnum = 0)
    {
        @trigger_error('The zikulagroupsmodule_user_memberslist route is deprecated. Please use zikulagroupsmodule_membership_list instead.', E_USER_DEPRECATED);

        return $this->redirectToRoute('zikulagroupsmodule_membership_list', ['gid' => $gid, 'startnum' => $startnum]);
    }
}
