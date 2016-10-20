<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\PermissionsModule\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Zikula\Core\Controller\AbstractController;

/**
 * @deprecated
 * Class AdminController
 */
class AdminController extends AbstractController
{
    /**
     * @Route("/")
     * @return RedirectResponse
     */
    public function indexAction()
    {
        @trigger_error('The zikulapermissionsmodule_admin_index route is deprecated. please use zikulapermissionsmodule_permission_list instead.', E_USER_DEPRECATED);

        // Security check will be done in view()
        return $this->redirectToRoute('zikulapermissionsmodule_permission_list');
    }

    /**
     * @Route("/view")
     * @return RedirectResponse
     */
    public function viewAction()
    {
        @trigger_error('The zikulapermissionsmodule_admin_view route is deprecated. please use zikulapermissionsmodule_permission_list instead.', E_USER_DEPRECATED);

        return $this->redirectToRoute('zikulapermissionsmodule_permission_list');
    }

    /**
     * @Route("/config")
     * @return RedirectResponse
     */
    public function modifyconfigAction()
    {
        @trigger_error('The zikulapermissionsmodule_admin_modifyconfig route is deprecated. please use zikulapermissionsmodule_config_config instead.', E_USER_DEPRECATED);

        return $this->redirectToRoute('zikulapermissionsmodule_config_config');
    }
}
