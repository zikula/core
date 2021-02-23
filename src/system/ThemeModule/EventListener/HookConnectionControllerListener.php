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

namespace Zikula\ThemeModule\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Bundle\HookBundle\Controller\ConnectionController;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\ThemeModule\Engine\Engine;

class HookConnectionControllerListener implements EventSubscriberInterface
{
    /* @var VariableApiInterface */
    private $variableApi;

    /* @var Engine */
    private $themeEngine;

    public function __construct(
        VariableApiInterface $variableApi,
        Engine $themeEngine
    ) {
        $this->variableApi = $variableApi;
        $this->themeEngine = $themeEngine;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => [
                ['theme', 100]
            ]
        ];
    }

    /**
     * This listener sets the theme to the selected admin theme setting and sets the
     * annotation in the theme engine for the HookConnection Controller.
     */
    public function theme(ControllerEvent $event)
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
        $adminThemeName = $this->variableApi->get('ZikulaAdminModule', 'admintheme', '');
        $this->themeEngine->setActiveTheme($adminThemeName, 'admin');
    }
}
