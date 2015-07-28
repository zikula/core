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

class Engine
{
    private $templatingService;
    private $themeName = '';

    function __construct(EngineInterface $templatingService)
    {
        $this->templatingService = $templatingService;
    }

    /**
     * Set the theme name. Use theme from UserUtil if empty
     * @param $themeName
     */
    public function setTheme($themeName)
    {
        $this->themeName = empty($themeName) ? $this->getTheme() : $themeName;
    }

    /**
     * wrap the response in the theme.
     * use response content as maincontent
     *
     * @param Response $response
     * @return Response
     */
    public function wrapResponseInTheme(Response $response)
    {
        // @todo determine proper template? and location
        return $this->templatingService->renderResponse($this->themeName . '::master.html.twig', array('maincontent' => $response->getContent(), 'pagetype' => 'admin'));
    }

    /**
     * Is theme twig based (e.g. Core-2.0 theme)
     *
     * @deprecated @2.0 (assume all themes are twig based in Core-2.0)
     * @return bool
     */
    public function themeIsTwigBased()
    {
        $themeBundle = $this->getThemeBundle($this->themeName);
        if (null !== $themeBundle) {
            $versionClass = $themeBundle->getVersionClass();
            if (!class_exists($versionClass)) {

                return true;
            }
        }

        return false;
    }

    /**
     * return currently active theme
     * @todo replace with 2.0-compatible solution
     *
     * @deprecated @2.0
     * @return string
     */
    private function getTheme()
    {
        return \UserUtil::getTheme();
    }

    /**
     * return the bundle for a theme
     * @todo replace with a 2.0-compatible solution
     *
     * @deprecated @2.0
     * @param $themeName
     * @return null|\Zikula\Core\AbstractTheme
     */
    private function getThemeBundle($themeName)
    {
        return \ThemeUtil::getTheme($themeName);
    }
}
