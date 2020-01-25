<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ExtensionsModule\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Zikula\Bundle\CoreBundle\Controller\AbstractController;
use Zikula\Bundle\CoreBundle\Event\GenericEvent;
use Zikula\ThemeModule\Engine\Annotation\Theme;

/**
 * Class ServicesController
 * @Route("/services")
 */
class ServicesController extends AbstractController
{
    /**
     * @Route("/{moduleName}", methods = {"GET"}, options={"zkNoBundlePrefix" = 1})
     * @Theme("admin")
     * @Template("@ZikulaExtensionsModule/Services/moduleServices.html.twig")
     *
     * Display services available to the module
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin permissions for the module
     */
    public function moduleServicesAction(EventDispatcherInterface $eventDispatcher, string $moduleName): array
    {
        if (!$this->hasPermission($moduleName . '::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        // notify EVENT here to gather any system service links
        $event = new GenericEvent(null, ['modname' => $moduleName]);
        $eventDispatcher->dispatch($event, 'module_dispatch.service_links');
        $sublinks = $event->getData();

        return [
            'sublinks' => $sublinks,
            'currentmodule' => $moduleName
        ];
    }
}
