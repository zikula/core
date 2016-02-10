<?php
/*** Copyright Zikula Foundation 2015 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\ThemeModule\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Zikula\Core\Controller\AbstractController;
use Zikula\ThemeModule\Engine\Engine;

/**
 * Class TemplatePathOverrideListener
 * @package Zikula\ThemeModule\EventListener
 *
 * This class adds the theme Resources path to the search path when locating assets like templates.
 * This listener only works when new "namespaced" (e.g. "@Bundle/template.html.twig") is used.
 * If old name-scheme (Bundle:template) or controller annotations ("@Template") are used
 * the \Zikula\ThemeModule\HttpKernel\ZikulaKernel::locateResource method is used instead
 */
class TemplatePathOverrideListener implements EventSubscriberInterface
{
    private $loader;
    private $themeEngine;

    public function __construct(\Twig_Loader_Filesystem $loader, Engine $themeEngine)
    {
        $this->loader = $loader;
        $this->themeEngine = $themeEngine;
    }

    /**
     * Add ThemePath to searchable paths when locating templates using name-spaced scheme
     * @param FilterControllerEvent $event
     * @throws \Twig_Error_Loader
     */
    public function setUpThemePathOverrides(FilterControllerEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }
        // add theme path to template locator
        $controller = $event->getController()[0];
        if ($controller instanceof AbstractController) {
            $theme = $this->themeEngine->getTheme();
            $bundleName = $controller->getName();
            if ($theme) {
                $overridePath = $theme->getPath() . '/Resources/' . $bundleName . '/views';
                if (is_readable($overridePath)) {
                    $paths = $this->loader->getPaths($bundleName);
                    // inject themeOverridePath before the original path in the array
                    array_splice($paths, count($paths) - 1, 0, array($overridePath));
                    $this->loader->setPaths($paths, $bundleName);
                }
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::CONTROLLER => array(
                array('setUpThemePathOverrides'),
            ),
        );
    }
}
