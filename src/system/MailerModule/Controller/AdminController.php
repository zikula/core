<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\MailerModule\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Core\Controller\AbstractController;
use Zikula\ThemeModule\Engine\Annotation\Theme;

/**
 * Class AdminController
 * @Route("/admin")
 */
class AdminController extends AbstractController
{
    /**
     * @Route("")
     * @deprecated remove at Core-2.0
     */
    public function indexAction()
    {
        @trigger_error('The zikulamailermodule_admin_index route is deprecated. please use zikulamailermodule_config_config instead.', E_USER_DEPRECATED);

        return $this->redirectToRoute('zikulamailermodule_config_config');
    }

    /**
     * @Route("/config")
     * @Theme("admin")
     *
     * @param Request $request
     * @throws AccessDeniedException Thrown if the user doesn't have admin access to the module
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function configAction(Request $request)
    {
        if (!$this->hasPermission('ZikulaMailerModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        @trigger_error('The zikulamailermodule_admin_config route is deprecated. please use zikulamailermodule_config_config instead.', E_USER_DEPRECATED);

        return $this->redirectToRoute('zikulamailermodule_config_config');
    }
}
