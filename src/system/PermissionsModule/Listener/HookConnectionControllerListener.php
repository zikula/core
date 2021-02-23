<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\PermissionsModule\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Bundle\HookBundle\Controller\ConnectionController;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;

class HookConnectionControllerListener implements EventSubscriberInterface
{
    /* @var PermissionApiInterface */
    private $permissionApi;

    public function __construct(
        PermissionApiInterface $permissionApi
    ) {
        $this->permissionApi = $permissionApi;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => [
                ['protect', 100]
            ]
        ];
    }

    /**
     * This listener protects the HookBundle ConnectionController and limits to admin privileges.
     */
    public function protect(ControllerEvent $event)
    {
        if (!class_exists(ConnectionController::class)) {
            return;
        }
        if (!$event->isMasterRequest()) {
            return;
        }
        $controller = $event->getController();
        if (!is_array($controller)) {
            return;
        }
        [$controller, $method] = $controller;
        if (!($controller instanceof ConnectionController)) {
            return;
        }
        if (!$this->permissionApi->hasPermission('HookBundle::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }
    }
}
