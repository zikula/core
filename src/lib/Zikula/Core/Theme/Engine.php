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
     * indicator whether theme is twig-based (default false to force setting)
     * remove all checks for this in Core-2.0
     * @deprecated
     * @var bool
     */
    private $themeIsTwigBased = false;
    private $themeIsOverridden = false;
    private $requestAttributes;

    function __construct(EngineInterface $templatingService, RequestStack $requestStack)
    {
        $this->templatingService = $templatingService;
        $this->requestAttributes = $requestStack->getCurrentRequest()->attributes->all();
        $this->themeName = $this->getCurrentTheme($requestStack->getCurrentRequest());
        // @TODO Note usage of Util classes (ThemeUtil) This must be corrected.
        $this->themeBundle = \ThemeUtil::getTheme($this->themeName);
        if (null !== $this->themeBundle) {
            $this->initTheme();
        }
    }

    /**
     * Set the theme name. Use theme from UserUtil if empty
     * Set themeIsTwigBased value (bool) based on themeName
     */
    private function initTheme()
    {
        // @TODO refactor at 2.0 to assume all themes are Core-2.0 type bundles (w/o Version class)
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

    public function getThemeName()
    {
        return $this->themeName;
    }

    public function themeIsOverridden()
    {
        return $this->themeIsOverridden;
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

    private function overrideThemeIfRequired(Response $response)
    {
        // If Response is an AdminResponse, then change theme to the requested Admin theme (if set)
        if ($response instanceof AdminResponse) {
            // @TODO remove usage of Util classes
            $adminTheme = \ModUtil::getVar('ZikulaAdminModule', 'admintheme');
            $this->themeIsOverridden = true;
            // @TODO is all this below desired in 2.0 ?
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

    private function getCurrentTheme(Request $request)
    {
        $themeByRequest = $request->get('theme', null);
        return !empty($themeByRequest) ? $themeByRequest : \System::getVar('Default_Theme');
    }
}
