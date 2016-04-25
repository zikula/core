<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SettingsModule\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Zikula\Core\Controller\AbstractController;

/**
 * Class AdminController
 * @deprecated remove at Core-2.0
 */
class AdminController extends AbstractController
{
    /**
     * @Route("/admin")
     *
     * @return RedirectResponse
     */
    public function indexAction()
    {
        @trigger_error('The zikulasettingsmodule_admin_index route is deprecated. please use zikulasettingsmodule_settings_main instead.', E_USER_DEPRECATED);

        return $this->redirectToRoute('zikulasettingsmodule_settings_main');
    }
}
