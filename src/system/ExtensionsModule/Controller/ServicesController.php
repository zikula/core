<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
        $event = new GenericEvent(null, ['modname' => $moduleName]);
        $this->get('event_dispatcher')->dispatch('module_dispatch.service_links', $event);
        $sublinks = $event->getData();

        $templateParameters = [
            'sublinks' => $sublinks,
            'currentmodule' => $moduleName
        ];

        return $templateParameters;
    }
}
