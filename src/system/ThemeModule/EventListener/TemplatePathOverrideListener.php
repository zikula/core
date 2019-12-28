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

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Loader\LoaderInterface;
use Zikula\Core\Controller\AbstractController;
use Zikula\ThemeModule\Engine\Engine;

/**
 * This class adds the theme Resources path to the search path when locating assets like templates.
 * This listener only works when new "namespaced" (e.g. "@Bundle/template.html.twig") is used.
 * If old name-scheme (Bundle:template) or controller annotations ("@Template") are used
 * the \Zikula\ThemeModule\HttpKernel\ZikulaKernel::locateResource method is used instead
 */
class TemplatePathOverrideListener implements EventSubscriberInterface
{
    /**
     * @var LoaderInterface
     */
    private $loader;

    /**
     * @var Engine
     */
    private $themeEngine;

    public function __construct(LoaderInterface $loader, Engine $themeEngine)
    {
        $this->loader = $loader;
        $this->themeEngine = $themeEngine;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => [
                ['setUpThemePathOverrides']
            ]
        ];
    }

    /**
     * Add theme path to searchable paths when locating templates using name-spaced scheme.
     */
    public function setUpThemePathOverrides(ControllerEvent $event): void
    {
        $controller = $event->getController();

        if (!is_array($controller)) {
            return;
        }

        $controller = $controller[0];
        if (!($controller instanceof AbstractController)) {
            return;
        }

        // add theme path to template locator

        $theme = $this->themeEngine->getTheme();
        if (!$theme) {
            return;
        }

        $bundleName = $controller->getName();
        $overridePath = $theme->getPath() . '/Resources/' . $bundleName . '/views';
        if (!is_readable($overridePath)) {
            return;
        }

        $paths = $this->loader->getPaths($bundleName);
        // inject themeOverridePath before the original path in the array
        array_splice($paths, count($paths) - 1, 0, [$overridePath]);
        $this->loader->setPaths($paths, $bundleName);
    }
}
