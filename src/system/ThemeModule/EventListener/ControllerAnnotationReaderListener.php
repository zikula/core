<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ThemeModule\EventListener;

use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Zikula\ThemeModule\Engine\Engine;

/**
 * Class ControllerAnnotationReaderListener
 * @package Zikula\ThemeModule\EventListener
 *
 * This class reads annotations from a controller and submits them to the theme engine to
 * potentially change the theme based on that annotation. (e.g. @Theme("admin") annotation)
 * @see \Zikula\ThemeModule\Engine\Annotation\Theme
 */
class ControllerAnnotationReaderListener implements EventSubscriberInterface
{
    private $themeEngine;

    public function __construct(Engine $themeEngine)
    {
        $this->themeEngine = $themeEngine;
    }

    /**
     * Read the controller annotations and change theme if the annotation indicate that need
     * @param FilterControllerEvent $event
     */
    public function readControllerAnnotations(FilterControllerEvent $event)
    {
        if (!$event->isMasterRequest()) {
            // prevents calling this for controller usage within a template or elsewhere
            return;
        }
        $controller = $event->getController();
        list($controller, $method) = $controller;
        // the controller could be a proxy, e.g. when using the JMSSecuriyExtraBundle or JMSDiExtraBundle
        $controllerClassName = ClassUtils::getClass($controller);
        $this->themeEngine->changeThemeByAnnotation($controllerClassName, $method);
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => [
                ['readControllerAnnotations']
            ]
        ];
    }
}
