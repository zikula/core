<?php
/**
 * Copyright 2015 Zikula Foundation
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Core\Theme;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Common\Annotations\Reader;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;

/**
 * Class Engine
 * @package Zikula\Core\Theme
 *
 * The Theme Engine class is responsible to manage all aspects of theme management using the classes referenced below.
 * @see \Zikula\Core\Theme\*
 * @see \Zikula\Bundle\CoreBundle\EventListener\Theme\*
 * @see \Zikula\Core\AbstractTheme
 *
 * The Engine works by intercepting the Response sent by the module controller (the controller action is the
 * 'primary actor'). It takes this response and "wraps" the theme around it and filters the resulting html to add
 * required page assets and variables and then sends the resulting Response to the browser. e.g.
 *     Request -> Controller -> CapturedResponse -> Filter -> ThemedResponse
 *
 * In this altered Symfony Request/Response cycle, the theme can be altered by the Controller Action through Annotation.
 * @see \Zikula\Core\Theme\Annotation\Theme
 * The annotation only excepts defined values.
 *
 * Themes are fully-qualified Symfony bundles with specific requirements.
 * @see https://github.com/zikula/SpecTheme
 * Themes can define 'realms' which determine specific templates based on Request.
 */
class Engine
{
    /**
     * The instance of the currently active theme.
     * @var \Zikula\Core\AbstractTheme
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
     * All the request attributes plus a few additional values.
     * @var array
     */
    private $requestAttributes;
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
     * The filter service.
     * @var Filter
     */
    private $filterService;

    /**
     * Engine constructor.
     * @param RequestStack $requestStack
     * @param Reader $annotationReader
     * @param ZikulaKernel $kernel
     * @param \Zikula\Core\Theme\Filter $filter
     */
    public function __construct(RequestStack $requestStack, Reader $annotationReader, ZikulaKernel $kernel, $filter)
    {
        $this->annotationReader = $annotationReader;
        $this->kernel = $kernel;
        $this->filterService = $filter;
        if (null !== $requestStack->getCurrentRequest()) {
            $this->setRequestAttributes($requestStack->getCurrentRequest());
        }
    }

    /**
     * Initialize the theme engine based on the Request.
     * @api Core-2.0
     * @param Request $request
     */
    public function setRequestAttributes(Request $request)
    {
        if ($this->kernel->getContainer()->getParameter('installed')) {
            $this->setActiveTheme(null, $request);
        }
        $this->requestAttributes = $request->attributes->all();
        $this->requestAttributes['pathInfo'] = $request->getPathInfo();
        $this->requestAttributes['lct'] = $request->query->get('lct', null); // @todo BC remove at Core-2.0
    }

    /**
     * Wrap the response in the theme.
     * @api Core-2.0
     * @param Response $response @todo change typecast to ThemedResponse in 2.0
     * @return Response|bool (false if theme is not twigBased)
     */
    public function wrapResponseInTheme(Response $response)
    {
        // @todo remove twigBased check in 2.0
        if (!isset($this->activeThemeBundle) || !$this->activeThemeBundle->isTwigBased()) {
            return false;
        }
        // wrap page in unique div
        $realm = $this->getRealm();
        $content = '<div id="z-maincontent" class="'
            . ($realm == 'home' ? 'z-homepage ' : '')
            . 'z-module-' . strtolower($this->requestAttributes['_zkModule']) . '">'
            . $response->getContent()
            . '</div>';
        $response->setContent($content);

        $themedResponse = $this->activeThemeBundle->generateThemedResponse($realm, $response);
        $filteredResponse = $this->filter($themedResponse);

        return $filteredResponse;
    }

    /**
     * Wrap a block in the theme's block template.
     * @api Core-2.0
     * @todo consider changing block to a Response
     * @param array $block
     * @return bool|string (false if theme is not twigBased)
     */
    public function wrapBlockInTheme(array $block)
    {
        // @todo remove twigBased check in 2.0
        if (!isset($this->activeThemeBundle) || !$this->activeThemeBundle->isTwigBased()) {
            return false;
        }

        return $this->activeThemeBundle->generateThemedBlock($this->getRealm(), $block);
    }

    /**
     * @deprecated This will not be needed >=2.0 (when Smarty is removed)
     * may consider leaving this present and public in 2.0 (unsure).
     * @return string
     */
    public function getThemeName()
    {
        return $this->activeThemeBundle->getName();
    }

    /**
     * @api Core-2.0
     * @return \Zikula\Core\AbstractTheme
     */
    public function getTheme()
    {
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
        $themeAnnotation = $this->annotationReader->getMethodAnnotation($reflectionMethod, 'Zikula\Core\Theme\Annotation\Theme');
        if (isset($themeAnnotation)) {
            // method annotations contain `@Theme` so set theme based on value
            $this->annotationValue = $themeAnnotation->value;
            switch ($themeAnnotation->value) {
                case 'admin':
                    $newThemeName = \ModUtil::getVar('ZikulaAdminModule', 'admintheme', '');
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
     * Find the realm in the theme.yml that matches the given path, route or module.
     * Three 'alias' realms may be defined and do not require a pattern:
     *  1) 'master' (required) this is the default realm. any non-matching value will utilize the master realm
     *  2) 'home' (optional) will be used when the path matches `^/$`
     *  3) 'admin' (optional) will be used when the annotationValue is 'admin'
     *     until Core-2.0 the 'admin' realm will also be used when _zkType or lct are 'admin' (BC)
     * Uses regex to find the FIRST match to a pattern to one of three possible request attribute values.
     *  1) path   e.g. /pages/display/welcome-to-pages-content-manager
     *  2) route  e.g. zikulapagesmodule_user_display
     *  3) module e.g. zikulapagesmodule (case insensitive)
     *
     * @return int|string
     */
    private function setMatchingRealm()
    {
        $themeConfig = $this->activeThemeBundle->getConfig();
        // defining an admin realm overrides all other options for 'admin' annotated methods
        if ($this->annotationValue == 'admin' && isset($themeConfig['admin'])) {
            $this->realm = 'admin';
            return;
        }
        // @todo BC remove at Core-2.0
        if ((isset($this->requestAttributes['_zkType']) && $this->requestAttributes['_zkType'] == 'admin') || (isset($this->requestAttributes['lct']))) {
            $this->realm = 'admin';
            return;
        }
        // match `/` for home realm
        if (preg_match(';^\\/$;', $this->requestAttributes['pathInfo']) && isset($themeConfig['home'])) {
            $this->realm = 'home';
            return;
        }
        unset($themeConfig['admin'], $themeConfig['home'], $themeConfig['master']); // remove to avoid scanning/matching in loop
        foreach ($themeConfig as $realm => $config) {
            // @todo is there a faster way to do this?
            if (!empty($config['pattern'])) {
                $pattern = ';' . str_replace('/', '\\/', $config['pattern']) . ';i'; // delimiters are ; and i means case-insensitive
                $valuesToMatch = [];
                if (isset($this->requestAttributes['pathInfo'])) {
                    $valuesToMatch[] = $this->requestAttributes['pathInfo']; // e.g. /pages/display/welcome-to-pages-content-manager
                }
                if (isset($this->requestAttributes['_route'])) {
                    $valuesToMatch[] = $this->requestAttributes['_route']; // e.g. zikulapagesmodule_user_display
                }
                if (isset($this->requestAttributes['_zkModule'])) {
                    $valuesToMatch[] = $this->requestAttributes['_zkModule']; // e.g. zikulapagesmodule
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
     *  2) the request params (e.g. `?theme=MySpecialTheme`)
     *  3) the request attributes (e.g. `_theme`)
     *  4) the default system theme
     * @param string|null $newThemeName
     * @param Request|null $request
     * @return mixed
     * kernel::getTheme() @throws \InvalidArgumentException if theme is invalid.
     */
    private function setActiveTheme($newThemeName = null, Request $request = null)
    {
        $activeTheme = !empty($newThemeName) ? $newThemeName : \System::getVar('Default_Theme');
        if (isset($request)) {
            // @todo do we want to allow changing the theme by the request?
            $themeByRequest = $request->get('theme', null);
            if (!empty($themeByRequest)) {
                $activeTheme = $themeByRequest;
            }
            $themeByRequest = $request->attributes->get('_theme');
            if (!empty($themeByRequest)) {
                $activeTheme = $themeByRequest;
            }
        }
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
        // @todo START legacy block - remove at Core-2.0
        $baseUri = \System::getBaseUri();
        $jsAssets = [];
        $javascripts = \JCSSUtil::prepareJavascripts(\PageUtil::getVar('javascript'));
        $i = 60;
        $legacyAjaxScripts = 0;
        foreach ($javascripts as $javascript) {
            $javascript = (!empty($baseUri) && (false === strpos($javascript, $baseUri))) ? "$baseUri/$javascript" : "/$javascript";
            // Add legacy ajax scripts (like prototype/scriptaculous) at the lightest weight (0) and in order from there.
            // Add others after core default assets (like jQuery) but before pageAddAsset default weight (100) and in order from there.
            $jsAssets[$javascript] = (false !== strpos($javascript, 'javascript/ajax/')) ? $legacyAjaxScripts++ : $i++;

        }
        $cssAssets = [];
        $stylesheets = \PageUtil::getVar('stylesheet');
        $i = 60;
        foreach ($stylesheets as $stylesheet) {
            $stylesheet = $baseUri . '/' . $stylesheet;
            $cssAssets[$stylesheet] = $i++; // add before pageAddAsset default weight (100)
        }
        // @todo END legacy block - remove at Core-2.0

        $filteredContent = $this->filterService->filter($response->getContent(), $jsAssets, $cssAssets);
        $response->setContent($filteredContent);
        return $response;
    }
}
