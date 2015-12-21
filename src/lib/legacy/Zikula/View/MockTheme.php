<?php
/**
 * Copyright Zikula Foundation 2015 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula_View
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * @deprecated immediately
 * This is simply a Mock class to allow for methods of Zikula_View_Theme to be used without throwing exceptions
 * 
 * Class Zikula_View_MockTheme
 */
class Zikula_View_MockTheme extends Zikula_View_Theme
{
    public function __construct(Zikula_ServiceManager $serviceManager, $themeName)
    {
    }

    protected function startOutputBuffering()
    {
    }

    public static function getInstance($themeName = '', $caching = null, $cache_id = null)
    {
        return new self(null, null);
    }

    public function themefooter(\Symfony\Component\HttpFoundation\Response $response = null)
    {
        return $response;
    }

    public function themesidebox($block)
    {
        return '';
    }

    public function get_template_path($template)
    {
        return false;
    }

    public function clear_cacheid_allthemes($cache_ids, $themes = null)
    {
        return true;
    }

    public function _get_auto_filename($path, $auto_source = null, $auto_id = null, $themedir = null)
    {
        return '';
    }

    public function load_config()
    {
    }

    public function _templateOverride(Zikula_Event $event)
    {
    }

    public function clear_cssjscombinecache()
    {
        return true;
    }

    public function clear_theme_config()
    {
        return true;
    }

    public function getName()
    {
    }

    public function getDirectory()
    {
    }

    public function getVersion()
    {
    }

    public function getState()
    {
    }

    public function getXhtml()
    {
    }

    public function getThemePath()
    {
    }

    public function getImagePath()
    {
    }

    public function getImageLangPath()
    {
    }

    public function getStylePath()
    {
    }

    public function getScriptPath()
    {
    }

    public function getThemeConfig()
    {
    }

    public function isHomePage()
    {
    }

    public function getUid()
    {
    }

    public function getIsLoggedIn()
    {
    }

    public function getPageType()
    {
    }

    public function getQstring()
    {
    }

    public function getRequestUri()
    {
    }

    public function setCacheId($cache_id)
    {
    }

    public function setThemeConfig($themeconfig)
    {
    }
}
