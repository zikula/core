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

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Loader\LoaderInterface;
use Zikula\ThemeBundle\Engine\Engine;

/**
 * This class adds the theme Resources path to the search path when locating assets like templates.
 */
class AddThemePathsToLoaderListener implements EventSubscriberInterface
{
    private bool $completed = false;

    public function __construct(
        private readonly LoaderInterface $loader,
        private readonly Engine $themeEngine
    ) {
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [
                ['addThemePaths', 2056]
            ]
        ];
    }

    /**
     * Add available theme paths to searchable paths when locating templates.
     */
    public function addThemePaths(RequestEvent $event): void
    {
        if (!$event->isMasterRequest() || $event->getRequest()->isXmlHttpRequest() || true === $this->completed) {
            return;
        }
        $theme = $this->themeEngine->getTheme();
        if (!$theme) {
            return;
        }
        $finder = new Finder();
        $directories = $finder->directories()
            ->in($theme->getPath() . '/Resources')
            ->depth('== 0')
            ->exclude(['config', 'public', 'translations', 'doc', 'docs', 'views', 'meta', 'workflows'])
            ->ignoreUnreadableDirs();
        foreach ($directories as $directory) {
            /** @var \Symfony\Component\Finder\SplFileInfo $directory */
            $bundleName = $directory->getFilename();
            $paths = $this->loader->getPaths($bundleName);
            // inject themeOverridePath before the original path in the array
            array_splice($paths, count($paths) - 1, 0, [$directory->getPathname() . '/views']);
            $this->loader->setPaths($paths, $bundleName);
        }
        $this->completed = true; // only run this once per request
    }
}
