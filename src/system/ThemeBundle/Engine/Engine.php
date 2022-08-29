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

namespace Zikula\ThemeBundle\Engine;

use ReflectionClass;
use ReflectionException;
use Zikula\ThemeBundle\Engine\Annotation\Theme as ThemeAttribute;

/**
 * The Theme Engine class is responsible to manage all aspects of theme management using the classes referenced below.
 *
 * @see \Zikula\ThemeBundle\Engine\*
 * @see \Zikula\ThemeBundle\EventListener\*
 *
 * The theme can be altered by the Controller Action through Annotation
 * @see \Zikula\ThemeBundle\Engine\Annotation\Theme
 */
class Engine
{
    /**
     * Class name of currently active instance of ThemedDashboardController.
     */
    private ?string $activeThemeControllerClass;

    /**
     * Value of the active method Theme annotation.
     */
    private ?string $annotationValue = null;

    private bool $installed;

    public function __construct(
        private readonly string $defaultDashboard,
        private readonly ?string $adminDashboard,
        string $installed
    ) {
        $this->installed = '0.0.0' !== $installed;
    }

    public function getActiveDashboardControllerClass(): string
    {
        return $this->activeThemeControllerClass ?: $this->defaultDashboard;
    }

    /**
     * Change a theme based on the annotation value.
     *
     * @throws ReflectionException
     */
    public function changeThemeByAnnotation(string $controllerClassName, string $method): bool|string
    {
        $reflectionClass = new ReflectionClass($controllerClassName);
        $reflectionMethod = $reflectionClass->getMethod($method);
        $attributes = $reflectionMethod->getAttributes(ThemeAttribute::class);
        $themeAttribute = 0 < count($attributes) ? $attributes[0] : null;
        if (isset($themeAttribute)) {
            $themeAttribute = $themeAttribute->newInstance();
            // method annotations contain `#[Theme]` so set theme based on value
            $this->annotationValue = $themeAttribute->value;
            switch ($themeAttribute->value) {
                case 'admin':
                    $newThemeName = $this->adminDashboard;
                    break;
                default:
                    $newThemeName = $themeAttribute->value;
            }
            if (!empty($newThemeName)) {
                $this->setActiveTheme($newThemeName);

                return $newThemeName;
            }
        }

        return false;
    }

    /**
     * Set the theme based on:
     *  1) manual setting
     *  2) the default system theme
     */
    public function setActiveTheme(string $newThemeName = null, $annotation = ''): void
    {
        $this->activeThemeControllerClass = !empty($newThemeName) && class_exists($newThemeName) ? $newThemeName : $this->defaultDashboard;

        if (!empty($annotation)) {
            $this->annotationValue = $annotation;
        }
    }
}
