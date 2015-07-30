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

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Yaml\Yaml;
use Zikula\Core\Event\GenericEvent;
use Zikula\Core\Response\AdminResponse;

class Engine
{
    /**
     * @var EngineInterface
     */
    private $templatingService;
    private $themeName = '';
    /**
     * @var \Zikula\Core\AbstractTheme
     */
    private $themeBundle = null;
    /**
     * @var array
     */
    private $themeConfig = array();
    /**
     * indicator flag whether theme is twig-based (default false to force setting)
     * remove all checks for this in Core-2.0
     * @deprecated
     * @var bool
     */
    private $themeIsTwigBased = false;
    /**
     * flag indicating whether the theme has been overridden by Response type
     * @var bool
     */
    private $themeIsOverridden = false;
    private $requestAttributes;

    function __construct(EngineInterface $templatingService, RequestStack $requestStack)
    {
        $this->templatingService = $templatingService;
        $request = $requestStack->getCurrentRequest(); // not available during installation
        if (is_object($request)) {
            $this->requestAttributes = $request->attributes->all();
            $this->themeName = $this->getCurrentTheme($requestStack->getCurrentRequest());
            // @todo Note usage of Util classes (ThemeUtil) This must be corrected.
            $this->themeBundle = \ThemeUtil::getTheme($this->themeName);
            if (null !== $this->themeBundle) {
                $this->initTheme();
            }
        }
    }

    /**
     * wrap the response in the theme.
     * use response content as maincontent
     *
     * @param Response $response
     * @return Response|bool (false if theme is not twigBased)
     */
    public function wrapResponseInTheme(Response $response)
    {
        $this->overrideThemeIfRequired($response);

        // @todo remove twigBased check in 2.0
        if (!$this->themeIsTwigBased) {
            return false;
        }

        // @todo determine proper template? and location
        // @todo NOTE: 'pagetype' is temporary var in the template
        $template = $this->themeConfig['master']['page'];

        return $this->templatingService->renderResponse($this->themeName . ':' . $template, array('maincontent' => $response->getContent(), 'pagetype' => 'admin'));
    }

    /**
     * wrap a block in the theme's block template
     *
     * @param array $block
     * @return bool|string (false if theme is not twigBased)
     */
    public function wrapBlockInTheme(array $block)
    {
        // @todo remove twigBased check in 2.0
        if (!$this->themeIsTwigBased) {
            return false;
        }

        $template = $this->themeConfig['master']['block']['positions'][$block['position']];

        return $this->templatingService->render($this->themeName . ':' . $template, $block);
    }

    /**
     * @deprecated This will not be needed >=2.0 (when Smarty is removed)
     * may consider leaving this present and public in 2.0 (unsure)
     * @return string
     */
    public function getThemeName()
    {
        return $this->themeName;
    }

    /**
     * @deprecated This will not be needed >=2.0 (when Smarty is removed)
     * @return bool
     */
    public function themeIsOverridden()
    {
        return $this->themeIsOverridden;
    }

    /**
     * load the theme config.
     * @todo this could be used to accomplish other tasks. If this is NOT requuired, then eliminate the method and
     *       perform loadThemeConfig() directly in the constructor
     * set themeIsTwigBased (to be removed in 2.0)
     */
    private function initTheme()
    {
        // @todo refactor at 2.0 to assume all themes are Core-2.0 type bundles (w/o Version class)
        $versionClass = $this->themeBundle->getVersionClass();
        if (class_exists($versionClass)) {
            $this->themeIsTwigBased = false;
        } else {
            // theme is Core-2.0 type. init and load config
            $this->themeIsTwigBased = true;
            $this->loadThemeConfig();
        }
    }

    /**
     * load the theme configuration from the config/theme.yml file
     */
    private function loadThemeConfig()
    {
        $configPath = $this->themeBundle->getConfigPath() . '/theme.yml';
        if (file_exists($configPath)) {
            $this->themeConfig = Yaml::parse($configPath);
        }
    }

    /**
     * Override the theme based on the Response type (e.g. AdminResponse)
     * Set a public flag themeIsOverridden for use by Smarty
     *
     * @param Response $response
     */
    private function overrideThemeIfRequired(Response $response)
    {
        // If Response is an AdminResponse, then change theme to the requested Admin theme (if set)
        if ($response instanceof AdminResponse) {
            // @todo remove usage of Util classes
            $adminTheme = \ModUtil::getVar('ZikulaAdminModule', 'admintheme');
            $this->themeIsOverridden = true;
            // @todo is all this below desired in 2.0 ?
            if (!empty($adminTheme)) {
                $themeInfo = \ThemeUtil::getInfo(\ThemeUtil::getIDFromName($adminTheme));
                if ($themeInfo
                    && $themeInfo['state'] == \ThemeUtil::STATE_ACTIVE
                    && is_dir('themes/' . \DataUtil::formatForOS($themeInfo['directory']))) {
                        $localEvent = new GenericEvent(null, array('type' => 'admin-theme'), $themeInfo['name']);
                        $this->themeName = \EventUtil::dispatch('user.gettheme', $localEvent)->getData();
                        $_GET['type'] = 'admin'; // required for smarty and FormUtil::getPassedValue() to use the right pagetype from pageconfigurations.ini
                }
            }
        }

        if ($this->themeIsOverridden) {
            $this->initTheme();
        }
    }

    /**
     * Set the theme based on:
     *  1) the request params (e.g. `?theme=MySpecialTheme`)
     *  2) the default system theme
     * @param Request $request
     * @return mixed
     */
    private function getCurrentTheme(Request $request)
    {
        $themeByRequest = $request->get('theme', null);
        // @todo do we want to allow changing the theme by the request?
        // @todo what about $this->requestAttributes['_theme'] ?
        return !empty($themeByRequest) ? $themeByRequest : \System::getVar('Default_Theme');
    }
}
