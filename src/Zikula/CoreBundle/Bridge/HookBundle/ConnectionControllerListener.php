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

namespace Zikula\Bundle\CoreBundle\Bridge\HookBundle;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Bundle\HookBundle\Controller\ConnectionController;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;
use Zikula\ThemeModule\Engine\Engine;

class ConnectionControllerListener implements EventSubscriberInterface
{
    /* @var VariableApiInterface */
    private $variableApi;

    /* @var Engine */
    private $themeEngine;

    /* @var PermissionApiInterface */
    private $permissionApi;

    public function __construct(
        VariableApiInterface $variableApi,
        Engine $themeEngine,
        PermissionApiInterface $permissionApi
    ) {
        $this->variableApi = $variableApi;
        $this->themeEngine = $themeEngine;
        $this->permissionApi = $permissionApi;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => [
                ['protectAndTheme', 100]
            ]
        ];
    }

    /**
     * This listener protects the HookBundle ConnectionController and limits to admin privileges.
     * It also sets the theme to the selected admin theme setting and sets the annotation in the theme engine.
     */
    public function protectAndTheme(ControllerEvent $event)
    {
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
        $adminThemeName = $this->variableApi->get('ZikulaAdminModule', 'admintheme', '');
        $this->themeEngine->setActiveTheme($adminThemeName, 'admin');
    }
}
