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

namespace Zikula\ThemeBundle\EventListener;

use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Zikula\ThemeBundle\Engine\Engine;

/**
 * Submits a given controller to the theme engine to potentially change the theme based on its attribute.
 * @see \Zikula\ThemeBundle\Engine\Annotation\Theme
 */
class ControllerAnnotationReaderListener implements EventSubscriberInterface
{
    public function __construct(private readonly Engine $themeEngine)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => ['readControllerAnnotations'],
        ];
    }

    public function readControllerAnnotations(ControllerEvent $event): void
    {
        if (!$event->isMasterRequest()) {
            // prevents calling this for controller usage within a template or elsewhere
            return;
        }
        $controller = $event->getController();
        if (!is_array($controller)) {
            return;
        }
        [$controller, $method] = $controller;
        // the controller could be a proxy, e.g. when using the JMSSecurityExtraBundle or JMSDiExtraBundle
        $controllerClassName = ClassUtils::getClass($controller);
        $this->themeEngine->changeThemeByAnnotation($controllerClassName, $method);
    }
}
