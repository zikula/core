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
use Zikula\Core\Event\GenericEvent;
use Zikula\Core\Response\AdminResponse;

class Engine
{
    private $themeName = '';
    /**
     * @var \Zikula\Core\AbstractTheme
     */
    private $themeBundle = null;
    /**
     * flag indicating whether the theme has been overridden by Response type
     * @var bool
     */
    private $themeIsOverridden = false;
    private $requestAttributes;

    function __construct(RequestStack $requestStack)
    {
        $request = $requestStack->getCurrentRequest(); // not available during installation
        if (is_object($request)) {
            $this->requestAttributes = $request->attributes->all();
            $this->themeName = $this->getCurrentTheme($requestStack->getCurrentRequest());
            // @todo Note usage of ThemeUtil class must be removed.
            $this->themeBundle = \ThemeUtil::getTheme($this->themeName);
        }
    }

    /**
     * wrap the response in the theme.
     *
     * @param Response $response
     * @return Response|bool (false if theme is not twigBased)
     */
    public function wrapResponseInTheme(Response $response)
    {
        $this->overrideThemeIfRequired($response);

        // @todo remove twigBased check in 2.0
        if (!$this->themeBundle->isTwigBased()) {
            return false;
        }

        return $this->themeBundle->generateThemedResponse($response);
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
        if (!$this->themeBundle->isTwigBased()) {
            return false;
        }

        return $this->themeBundle->generateThemedBlock($block);
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
        // check other Response types here...

        if ($this->themeIsOverridden) {
            // load new bundle into Engine
            $this->themeBundle = \ThemeUtil::getTheme($this->themeName);
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
