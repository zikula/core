<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ThemeModule\Engine;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Common\Annotations\Reader;
use Zikula\BlocksModule\Api\BlockApi;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;
use Zikula\ExtensionsModule\Api\VariableApi;

/**
 * Class Engine
 *
 * The Theme Engine class is responsible to manage all aspects of theme management using the classes referenced below.
 * @see \Zikula\ThemeModule\Engine\*
 * @see \Zikula\ThemeModule\EventListener\*
 * @see \Zikula\ThemeModule\AbstractTheme
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
     * @var \Zikula\ThemeModule\AbstractTheme
     */
    private $activeThemeBundle = null;

    /**
     * Realm is a present value in the theme config determining which page templates to utilize.
     * @var string
     */
    private $realm;

    /**
     * AnnotationValue is the value of the active method Theme annotation.
     * @var null|string
     */
    private $annotationValue = null;

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
     * @var \Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel
     */
    private $kernel;

    /**
     * @var AssetFilter
     */
    private $filterService;

    /**
     * @var BlockApi
     */
    private $blockApi;

    /**
     * @var VariableApi
     */
    private $variableApi;

    /**
     * Engine constructor.
     * @param RequestStack $requestStack
     * @param Reader $annotationReader
     * @param ZikulaKernel $kernel
     * @param AssetFilter $filter
     * @param BlockApi $blockApi
     * @param VariableApi $variableApi
     */
    public function __construct(RequestStack $requestStack, Reader $annotationReader, ZikulaKernel $kernel, AssetFilter $filter, BlockApi $blockApi, VariableApi $variableApi)
    {
        $this->requestStack = $requestStack;
        $this->annotationReader = $annotationReader;
        $this->kernel = $kernel;
        $this->filterService = $filter;
        $this->blockApi = $blockApi;
        $this->variableApi = $variableApi;
    }

    /**
     * Wrap the response in the theme.
     * @api Core-2.0
     * @param Response $response
     * @return Response
     */
    public function wrapResponseInTheme(Response $response)
    {
        $activeTheme = $this->getTheme();
        $moduleName = $this->requestStack->getMasterRequest()->attributes->get('_zkModule');
        $themedResponse = $activeTheme->generateThemedResponse($this->getRealm(), $response, $moduleName);
        $filteredResponse = $this->filter($themedResponse);

        return $filteredResponse;
    }

    /**
     * Wrap the block content in the theme block template and wrap that with a unique div if required.
     * @api Core-2.0
     * @param string $content
     * @param string $title
     * @param string $blockType
     * @param integer $bid
     * @param string $positionName
     * @return string
     */
    public function wrapBlockContentInTheme($content, $title, $blockType, $bid, $positionName)
    {
        $themeConfig = $this->getTheme()->getConfig();
        $wrap = isset($themeConfig['blockWrapping']) ? $themeConfig['blockWrapping'] : true;

        return $wrap ? $this->getTheme()->wrapBlockContentWithUniqueDiv($content, $positionName, $blockType, $bid) : $content;
    }

    /**
     * @api Core-2.0
     * @return \Zikula\ThemeModule\AbstractTheme
     */
    public function getTheme()
    {
        if (!isset($this->activeThemeBundle) && $this->kernel->getContainer()->getParameter('installed')) {
            $this->setActiveTheme();
        }

        return $this->activeThemeBundle;
    }

    /**
     * Get the template realm.
     * @api Core-2.0
     * @return string
     */
    public function getRealm()
    {
        if (!isset($this->realm)) {
            $this->setMatchingRealm();
        }

        return $this->realm;
    }

    /**
     * @api Core-2.0
     * @return null|string
     */
    public function getAnnotationValue()
    {
        return $this->annotationValue;
    }

    /**
     * Change a theme based on the annotationValue.
     * @api Core-2.0
     * @param string $controllerClassName
     * @param string $method
     * @return bool|string
     */
    public function changeThemeByAnnotation($controllerClassName, $method)
    {
        $reflectionClass = new \ReflectionClass($controllerClassName);
        $reflectionMethod = $reflectionClass->getMethod($method);
        $themeAnnotation = $this->annotationReader->getMethodAnnotation($reflectionMethod, 'Zikula\ThemeModule\Engine\Annotation\Theme');
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

    /**
     * @param $name
     * @return bool
     */
    public function positionIsAvailableInTheme($name)
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
     * Find the realm in the theme.yml that matches the given path, route or module.
     * Three 'alias' realms may be defined and do not require a pattern:
     *  1) 'master' (required) this is the default realm. any non-matching value will utilize the master realm
     *  2) 'home' (optional) will be used when the path matches `^/$`
     *  3) 'admin' (optional) will be used when the annotationValue is 'admin'
     * Uses regex to find the FIRST match to a pattern to one of three possible request attribute values.
     *  1) path   e.g. /pages/display/welcome-to-pages-content-manager
     *  2) route  e.g. zikulapagesmodule_user_display
     *  3) module e.g. zikulapagesmodule (case insensitive)
     *
     * @return int|string
     */
    private function setMatchingRealm()
    {
        $themeConfig = $this->getTheme()->getConfig();
        // defining an admin realm overrides all other options for 'admin' annotated methods
        if ($this->annotationValue == 'admin' && isset($themeConfig['admin'])) {
            $this->realm = 'admin';

            return;
        }
        $request = $this->requestStack->getMasterRequest();
        $requestAttributes = $request->attributes->all();
        // match `/` for home realm
        if (isset($requestAttributes['_route']) && $requestAttributes['_route'] == 'home') {
            $this->realm = 'home';

            return;
        }

        unset($themeConfig['admin'], $themeConfig['home'], $themeConfig['master']); // remove to avoid scanning/matching in loop
        $pathInfo = $request->getPathInfo();
        foreach ($themeConfig as $realm => $config) {
            // @todo is there a faster way to do this?
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
                    if ($match === 1) {
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
     * @param string|null $newThemeName
     * @return mixed
     * kernel::getTheme() @throws \InvalidArgumentException if theme is invalid
     */
    private function setActiveTheme($newThemeName = null)
    {
        $activeTheme = !empty($newThemeName) ? $newThemeName : $this->variableApi->getSystemVar('Default_Theme');
        try {
            $this->activeThemeBundle = $this->kernel->getTheme($activeTheme);
            $this->activeThemeBundle->loadThemeVars();
        } catch (\Exception $e) {
            // fail silently, this is a Core < 1.4 theme.
        }
    }

    /**
     * Filter the Response to add page assets and vars and return.
     * @param Response $response
     * @return Response
     */
    private function filter(Response $response)
    {
        $jsAssets = [];
        $cssAssets = [];
        $filteredContent = $this->filterService->filter($response->getContent(), $jsAssets, $cssAssets);
        $response->setContent($filteredContent);

        return $response;
    }
}
