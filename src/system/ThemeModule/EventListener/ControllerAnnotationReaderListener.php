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

namespace Zikula\ThemeModule\EventListener;

use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Zikula\ThemeModule\Engine\Engine;

/**
 * This class reads annotations from a controller and submits them to the theme engine to
 * potentially change the theme based on that annotation.
 * @see \Zikula\ThemeModule\Engine\Annotation\Theme
 */
class ControllerAnnotationReaderListener implements EventSubscriberInterface
{
    private $themeEngine;

    public function __construct(Engine $themeEngine)
    {
        $this->themeEngine = $themeEngine;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => [
                ['readControllerAnnotations']
            ]
        ];
    }

    /**
     * Read the controller annotations and change theme if the annotation indicate that need
     */
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
