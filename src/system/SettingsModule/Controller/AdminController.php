<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
  *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\SettingsModule\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * Class AdminController
 * @deprecated remove at Core-2.0
 * @package Zikula\SettingsModule\Controller
 */
class AdminController extends \Zikula_AbstractController
{
    /**
     * @Route("/admin")
     *
     * @return RedirectResponse
     */
    public function indexAction()
    {
        return new RedirectResponse($this->get('router')->generate('zikulasettingsmodule_settings_main', array(), RouterInterface::ABSOLUTE_URL));
    }
}
