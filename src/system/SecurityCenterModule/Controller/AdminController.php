<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SecurityCenterModule\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Zikula\Core\Controller\AbstractController;

/**
 * @Route("/admin")
 *
 * Administrative controllers for the security centre module
 */
class AdminController extends AbstractController
{
    /**
     * @Route("")
     *
     * The main administration function.
     *
     * @deprecated since 1.4.3 use config controller instead
     *
     * @return RedirectResponse
     */
    public function indexAction()
    {
        @trigger_error('The zikulasecuritycentermodule_admin_index route is deprecated. please use zikulasecuritycentermodule_config_config instead.', E_USER_DEPRECATED);

        return $this->redirectToRoute('zikulasecuritycentermodule_config_config');
    }

    /**
     * Route not needed here because method is legacy-only
     *
     * The main administration function.
     *
     * @deprecated since 1.4.3 use config controller instead
     *
     * @return RedirectResponse
     */
    public function mainAction()
    {
        @trigger_error('The zikulasecuritycentermodule_admin_main route is deprecated. please use zikulasecuritycentermodule_config_config instead.', E_USER_DEPRECATED);

        return $this->redirectToRoute('zikulasecuritycentermodule_config_config');
    }
}
