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
use Symfony\Component\HttpFoundation\Response;
use Zikula\Core\Event\GenericEvent;
use Zikula\Core\Response\AdminResponse;

class Engine
{
    /**
     * @var \Zikula\Core\AbstractTheme
     */
    private $themeBundle = null;
    private $realm;
    /**
     * flag indicating whether the theme has been overridden by Response type
     * @var bool
     */
    private $themeIsOverridden = false;
    private $requestAttributes;

    /**
     * Initialize the theme engine based on the Request
     * @param Request $request
     */
    public function initFromRequest(Request $request)
    {
        $this->requestAttributes = $request->attributes->all();
        $this->requestAttributes['pathInfo'] = $request->getPathInfo();
        $themeName = $this->getCurrentTheme($request);
        // @todo remove usage of ThemeUtil class
        $this->themeBundle = \ThemeUtil::getTheme($themeName);
    }

    /**
     * wrap the response in the theme.
     *
     * @param Response $response @todo change typecast to ThemedResponse in 2.0
     * @return Response|bool (false if theme is not twigBased)
     */
    public function wrapResponseInTheme(Response $response)
    {
        $this->overrideThemeIfRequired($response);

        // original OR overridden theme may not be twig based
        // @todo remove twigBased check in 2.0
        if (!$this->themeBundle->isTwigBased()) {
            return false;
        }

        return $this->themeBundle->generateThemedResponse($response);
    }

    /**
     * wrap a block in the theme's block template
     * @todo consider changing block to a Response
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
        return $this->themeBundle->getName();
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
     * Find the realm in the theme.yml that matches the given path, route or module
     * Uses regex to match a pattern to one of three possible values
     *
     * @todo is there a faster way to do this?
     * @return int|string
     */
    private function setMatchingRealm()
    {
        foreach ($this->themeBundle->getConfig() as $realm => $config) {
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
     * Get the template realm
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
     * Override the theme based on the Response type (e.g. AdminResponse)
     * Set a public flag themeIsOverridden for use by Smarty
     *
     * @param Response $response
     */
    private function overrideThemeIfRequired(Response $response)
    {
        // If Response is an AdminResponse, then change theme to the requested Admin theme (if set)
        // BC: (_zkType == 'admin') indicates a legacy response that must be overridden if theme is twig-based
        // this second test can be removed at 2.0
        if (($response instanceof AdminResponse) || $this->requestAttributes['_zkType'] == 'admin') {
            // @todo remove usage of Util classes
            $themeName = \ModUtil::getVar('ZikulaAdminModule', 'admintheme');
            if (empty($themeName)) {
                return; // no admin theme set
            }
            $this->themeIsOverridden = true;
            // @todo is all this below desired in 2.0 ?
            if (!empty($themeName)) {
                $themeInfo = \ThemeUtil::getInfo(\ThemeUtil::getIDFromName($themeName));
                if ($themeInfo
                    && $themeInfo['state'] == \ThemeUtil::STATE_ACTIVE
                    && is_dir('themes/' . \DataUtil::formatForOS($themeInfo['directory']))) {
                        $localEvent = new GenericEvent(null, array('type' => 'admin-theme'), $themeInfo['name']);
                        $themeName = \EventUtil::dispatch('user.gettheme', $localEvent)->getData();
                        $_GET['type'] = 'admin'; // required for smarty and FormUtil::getPassedValue() to use the right pagetype from pageconfigurations.ini
                }
            }
        }
        // @todo check other Response types here...

        if ($this->themeIsOverridden) {
            // load new bundle into Engine
            $this->themeBundle = \ThemeUtil::getTheme($themeName);
            // try to set realm based on response
            $this->realm = isset($this->themeBundle->getConfig()['admin']) ? 'admin' : null;
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
