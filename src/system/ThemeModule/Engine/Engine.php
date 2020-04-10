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

namespace Zikula\ThemeModule\Engine;

use Doctrine\Common\Annotations\Reader;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\ExtensionsModule\AbstractTheme;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\ThemeModule\Engine\Annotation\Theme as ThemeAnnotation;

/**
 * Class Engine
 *
 * The Theme Engine class is responsible to manage all aspects of theme management using the classes referenced below.
 *
 * @see \Zikula\ThemeModule\Engine\*
 * @see \Zikula\ThemeModule\EventListener\*
 * @see \Zikula\ExtensionsModule\AbstractTheme
 *
 * The Engine works by intercepting the Response sent by the module controller (the controller action is the
 * 'primary actor'). It takes this response and "wraps" the theme around it and filters the resulting html to add
 * required page assets and variables and then sends the resulting Response to the browser. e.g.
 *     Request -> Controller -> CapturedResponse -> Filter -> ThemedResponse
 *
 * In this altered Symfony Request/Response cycle, the theme can be altered by the Controller Action through Annotation
 * @see \Zikula\ThemeModule\Engine\Annotation\Theme
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
     *
     * @var AbstractTheme
     */
    private $activeThemeBundle;

    /**
     * Realm is a present value in the theme config determining which page templates to utilize.
     * @var string
     */
    private $realm;

    /**
     * AnnotationValue is the value of the active method Theme annotation.
     * @var null|string
     */
    private $annotationValue;

    /**
     * The requestStack.
     * @var RequestStack
     */
    private $requestStack;

    /**
     * The doctrine annotation reader service.
     * @var Reader
     */
    private $annotationReader;

    /**
     * @var ZikulaHttpKernelInterface
     */
    private $kernel;

    /**
     * @var AssetFilter
     */
    private $filterService;

    /**
     * @var VariableApiInterface
     */
    private $variableApi;

    public function __construct(
        RequestStack $requestStack,
        Reader $annotationReader,
        ZikulaHttpKernelInterface $kernel,
        AssetFilter $filter,
        VariableApiInterface $variableApi
    ) {
        $this->requestStack = $requestStack;
        $this->annotationReader = $annotationReader;
        $this->kernel = $kernel;
        $this->filterService = $filter;
        $this->variableApi = $variableApi;
    }

    /**
     * Wrap the response in the theme.
     * @api Core-2.0
     */
    public function wrapResponseInTheme(Response $response): Response
    {
        $activeTheme = $this->getTheme();
        $activeTheme->addStylesheet();
        $moduleName = $this->requestStack->getMasterRequest()->attributes->get('_zkModule');
        $themedResponse = $activeTheme->generateThemedResponse($this->getRealm(), $response, $moduleName);

        $themedResponse->setStatusCode($response->getStatusCode());

        return $this->filter($themedResponse);
    }

    /**
     * Wrap the block content in the theme block template and wrap that with a unique div if required.
     * @api Core-2.0
     */
    public function wrapBlockContentInTheme(
        string $content,
        string $title,
        string $blockType,
        int $blockId,
        string $positionName
    ): string {
        $content = $this->getTheme()->generateThemedBlockContent($this->getRealm(), $positionName, $content, $title);

        $themeConfig = $this->getTheme()->getConfig();
        $wrap = $themeConfig['blockWrapping'] ?? true;

        return $wrap ? $this->getTheme()->wrapBlockContentWithUniqueDiv($content, $positionName, $blockType, $blockId) : $content;
    }

    /**
     * @api Core-2.0
     */
    public function getTheme(): ?AbstractTheme
    {
        if (!isset($this->activeThemeBundle) && '0.0.0' !== $this->kernel->getContainer()->getParameter('installed')) {
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
        $themeAnnotation = $this->annotationReader->getMethodAnnotation($reflectionMethod, ThemeAnnotation::class);
        if (isset($themeAnnotation)) {
            // method annotations contain `@Theme` so set theme based on value
            $this->annotationValue = $themeAnnotation->value;
            switch ($themeAnnotation->value) {
                case 'admin':
                    $newThemeName = $this->variableApi->get('ZikulaAdminModule', 'admintheme', '');
                    break;
                case 'print':
                    $newThemeName = 'ZikulaPrinterTheme';
                    break;
                case 'atom':
                    $newThemeName = 'ZikulaAtomTheme';
                    break;
                case 'rss':
                    $newThemeName = 'ZikulaRssTheme';
                    break;
                default:
                    $newThemeName = $themeAnnotation->value;
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
     * Find the realm in the theme.yaml that matches the given path, route or module.
     * Three 'alias' realms may be defined and do not require a pattern:
     *  1) 'master' (required) this is the default realm. any non-matching value will utilize the master realm
     *  2) 'home' (optional) will be used when the path matches `^/$`
     *  3) 'admin' (optional) will be used when the annotationValue is 'admin'
     * Uses regex to find the FIRST match to a pattern to one of three possible request attribute values.
     *  1) path   e.g. /pages/display/welcome-to-pages-content-manager
     *  2) route  e.g. zikulapagesmodule_user_display
     *  3) module e.g. zikulapagesmodule (case insensitive)
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
        $request = $this->requestStack->getMasterRequest();
        if (null !== $request) {
            $requestAttributes = $request->attributes->all();
            // match `/` for home realm
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
                    $valuesToMatch[] = $requestAttributes['_route']; // e.g. zikulapagesmodule_user_display
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
    public function setActiveTheme(string $newThemeName = null): void
    {
        $activeTheme = !empty($newThemeName) ? $newThemeName : $this->variableApi->getSystemVar('Default_Theme');

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
