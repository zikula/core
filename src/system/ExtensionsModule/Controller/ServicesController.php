<?php
/**
 * Copyright Zikula Foundation 2016 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\ExtensionsModule\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Core\Controller\AbstractController;
use Zikula\Core\Event\GenericEvent;
use Zikula\ThemeModule\Engine\Annotation\Theme;

/**
 * Class ServicesController
 * @package Zikula\ExtensionsModule\Controller
 * @Route("/services")
 */
class ServicesController extends AbstractController
{
    /**
     * @Route("/{moduleName}", options={"zkNoBundlePrefix" = 1})
     * @Method("GET")
     * @Theme("admin")
     * @Template
     *
     * Display services available to the module
     *
     * @param $moduleName
     * @throws AccessDeniedException Thrown if the user doesn't have admin permissions over the module
     * @return Response
     */
    public function moduleServicesAction($moduleName)
    {
        if (!$this->hasPermission($moduleName . '::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        // notify EVENT here to gather any system service links
        $event = new GenericEvent(null, array('modname' => $moduleName));
        $this->get('event_dispatcher')->dispatch('module_dispatch.service_links', $event);
        $sublinks = $event->getData();
        $templateParameters = [];
        $templateParameters['sublinks'] = $sublinks;
        $templateParameters['currentmodule'] = $moduleName;

        return $templateParameters;
    }
}
