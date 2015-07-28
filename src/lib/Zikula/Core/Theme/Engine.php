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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Yaml\Yaml;

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
    private $themeConfig;
    /**
     * indicator whether theme is twig-based (default false to force setting)
     * remove all checks for this in Core-2.0
     * @deprecated
     * @var bool
     */
    private $themeIsTwigBased = false;

    function __construct(EngineInterface $templatingService)
    {
        $this->templatingService = $templatingService;
    }

    /**
     * Set the theme name. Use theme from UserUtil if empty
     * Set themeIsTwigBased value (bool) based on themeName
     * @param $themeName
     */
    public function setTheme($themeName)
    {
        /**
         * @TODO Note usage of Util classes (UserUtil, ThemeUtil) This must be removed.
         * @TODO refactor at 2.0 to assume all themes are Core-2.0 type bundles (w/o Version class)
         */
        $this->themeName = empty($themeName) ? \UserUtil::getTheme() : $themeName;
        $this->themeBundle = \ThemeUtil::getTheme($this->themeName);
        if (null !== $this->themeBundle) {
            $versionClass = $this->themeBundle->getVersionClass();
            if (!class_exists($versionClass)) {
                $this->themeIsTwigBased = true;
                $this->loadThemeConfig();

                return;
            }
        }
        $this->themeIsTwigBased = false;
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
     * load the theme configuration from the config/theme.yml file
     */
    private function loadThemeConfig()
    {
        $configPath = $this->themeBundle->getConfigPath() . '/theme.yml';
        if (file_exists($configPath)) {
            $this->themeConfig = Yaml::parse($configPath);
        }
    }
}
