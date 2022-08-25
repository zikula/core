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
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\ExtensionsBundle\AbstractTheme;
use Zikula\ThemeBundle\Engine\Annotation\Theme as ThemeAttribute;

/**
 * Class Engine
 *
 * The Theme Engine class is responsible to manage all aspects of theme management using the classes referenced below.
 *
 * @see \Zikula\ThemeBundle\Engine\*
 * @see \Zikula\ThemeBundle\EventListener\*
 * @see \Zikula\ExtensionsBundle\AbstractTheme
 *
 * The Engine works by intercepting the Response sent by the module controller (the controller action is the
 * 'primary actor'). It takes this response and "wraps" the theme around it and filters the resulting html to add
 * required page assets and variables and then sends the resulting Response to the browser. e.g.
 *     Request -> Controller -> CapturedResponse -> Filter -> ThemedResponse
 *
 * In this altered Symfony Request/Response cycle, the theme can be altered by the Controller Action through Annotation
 * @see \Zikula\ThemeBundle\Engine\Annotation\Theme
 * The annotation only excepts defined values.
 *
 * Themes are fully-qualified Symfony bundles with specific requirements
 * @see https://github.com/zikula/SpecTheme
 * Themes can define 'realms' which determine specific templates based on Request
 */
class Engine
{
    /**
     * The instance of the currently active theme.
     */
    private AbstractTheme $activeThemeBundle;

    /**
     * Realm is a present value in the theme config determining which page templates to utilize.
     */
    private string $realm;

    /**
     * Value of the active method Theme annotation.
     */
    private ?string $annotationValue = null;

    private bool $installed;

    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly ZikulaHttpKernelInterface $kernel,
        private readonly AssetFilter $filterService,
        private readonly string $defaultTheme,
        private readonly ?string $adminTheme,
        string $installed
    ) {
        $this->installed = '0.0.0' !== $installed;
    }

    /**
     * Wrap the response in the theme.
     * @api Core-2.0
     */
    public function wrapResponseInTheme(Response $response): Response
    {
        $activeTheme = $this->getTheme();
        $activeTheme->addStylesheet();
        $moduleName = $this->requestStack->getMainRequest()->attributes->get('_zkModule');
        $themedResponse = $activeTheme->generateThemedResponse($this->getRealm(), $response, $moduleName);

        $themedResponse->setStatusCode($response->getStatusCode());

        return $this->filter($themedResponse);
    }

    /**
     * @api Core-2.0
     */
    public function getTheme(): ?AbstractTheme
    {
        if (!isset($this->activeThemeBundle) && $this->installed) {
            $this->setActiveTheme();
        }

        return $this->activeThemeBundle;
    }

    /**
     * Get the template realm.
     * @api Core-2.0
     */
    public function getRealm(): string
    {
        if (!isset($this->realm)) {
            $this->setMatchingRealm();
        }

        return $this->realm;
    }

    /**
     * @api Core-2.0
     */
    public function getAnnotationValue(): ?string
    {
        return $this->annotationValue;
    }

    /**
     * Change a theme based on the annotationValue.
     * @return bool|string
     * @throws ReflectionException
     * @api Core-2.0
     */
    public function changeThemeByAnnotation(string $controllerClassName, string $method)
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
                    $newThemeName = $this->adminTheme;
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

    public function positionIsAvailableInTheme(string $name): bool
    {
        $config = $this->getTheme()->getConfig();
        if (empty($config)) {
            return true;
        }
        foreach ($config as $realm => $definition) {
            if (isset($definition['block']['positions'][$name])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Find the realm in the theme.yaml that matches the given path, route or bundle.
     * Four 'alias' realms may be defined and do not require a pattern:
     *  1) 'master' (required) this is the default realm. any non-matching value will utilize the master realm
     *  2) 'home' (optional) will be used when the route matches `home` (or previewing a theme)
     *  3) 'error' (optional) will be used if an exception is thrown
     *  4) 'admin' (optional) will be used when the annotationValue is 'admin'
     * Uses regex to find the FIRST match to a pattern to one of three possible request attribute values.
     *  1) path   e.g. /pages/display/welcome-to-pages-content-manager
     *  2) route  e.g. zikulapagesbundle_user_display
     *  3) bundle e.g. zikulapagesbundle (case insensitive)
     */
    private function setMatchingRealm(): void
    {
        $themeConfig = $this->getTheme()->getConfig();
        // defining an admin realm overrides all other options for 'admin' annotated methods
        if ('admin' === $this->annotationValue && isset($themeConfig['admin'])) {
            $this->realm = 'admin';

            return;
        }
        $pathInfo = null;
        $requestAttributes = [];
        $request = $this->requestStack->getMainRequest();
        if (null !== $request) {
            $requestAttributes = $request->attributes->all();
            if (isset($requestAttributes['_route']) && 'home' === $requestAttributes['_route']) {
                $this->realm = 'home';

                return;
            }
            if (isset($requestAttributes['error']) && $requestAttributes['error'] && isset($themeConfig['error'])) {
                $this->realm = 'error';

                return;
            }
            $pathInfo = $request->getPathInfo();
        }

        unset($themeConfig['admin'], $themeConfig['home'], $themeConfig['master'], $themeConfig['error']); // remove to avoid scanning/matching in loop
        foreach ($themeConfig as $realm => $config) {
            if (!empty($config['pattern'])) {
                $pattern = ';' . str_replace('/', '\\/', $config['pattern']) . ';i'; // delimiters are ; and i means case-insensitive
                $valuesToMatch = [];
                if (isset($pathInfo)) {
                    $valuesToMatch[] = $pathInfo; // e.g. /pages/display/welcome-to-pages-content-manager
                }
                if (isset($requestAttributes['_route'])) {
                    $valuesToMatch[] = $requestAttributes['_route']; // e.g. zikulapagesbundle_user_display
                }
                if (isset($requestAttributes['_zkModule'])) {
                    $valuesToMatch[] = $requestAttributes['_zkModule']; // e.g. zikulapagesmodule
                }
                foreach ($valuesToMatch as $value) {
                    $match = preg_match($pattern, $value);
                    if (1 === $match) {
                        $this->realm = $realm;

                        return; // use first match and do not continue to attempt to match patterns
                    }
                }
            }
        }

        $this->realm = 'master';
    }

    /**
     * Set the theme based on:
     *  1) manual setting
     *  2) the default system theme
     */
    public function setActiveTheme(string $newThemeName = null, $annotation = ''): void
    {
        $activeTheme = !empty($newThemeName) ? $newThemeName : $this->defaultTheme;

        if (!empty($annotation)) {
            $this->annotationValue = $annotation;
        }
        $this->activeThemeBundle = $this->kernel->getTheme($activeTheme);
        $this->activeThemeBundle->loadThemeVars();
    }

    /**
     * Filter the Response to add page assets and vars and return.
     */
    private function filter(Response $response): Response
    {
        $jsAssets = [];
        $cssAssets = [];
        $filteredContent = $this->filterService->filter($response->getContent(), $jsAssets, $cssAssets);
        $response->setContent($filteredContent);

        return $response;
    }
}
