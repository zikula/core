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
         */
        $this->themeName = empty($themeName) ? \UserUtil::getTheme() : $themeName;
        $themeBundle = \ThemeUtil::getTheme($this->themeName);
        if (null !== $themeBundle) {
            $versionClass = $themeBundle->getVersionClass();
            if (!class_exists($versionClass)) {
                $this->themeIsTwigBased = true;

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
        // @todo determine proper template? and location
        // @todo NOTE: 'pagetype' is temporary var in the template
        // @todo remove twigBased check in 2.0
        if ($this->themeIsTwigBased) {
            return $this->templatingService->renderResponse($this->themeName . '::master.html.twig', array('maincontent' => $response->getContent(), 'pagetype' => 'admin'));
        } else {
            return false;
        }
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
        if ($this->themeIsTwigBased) {
            return $this->templatingService->render($this->themeName . ':Blocks:block.html.twig', $block);
        } else {
            return false;
        }
    }
}
