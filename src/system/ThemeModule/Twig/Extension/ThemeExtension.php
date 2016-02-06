<?php
/**
 * Copyright Zikula Foundation 2016 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv3 (or at your option any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\ThemeModule\Twig\Extension;

use Zikula\Core\Theme\Asset;

class ThemeExtension extends \Twig_Extension
{
    /**
     * @var Asset
     */
    private $assetHelper;

    /**
     * ThemeExtension constructor.
     * @param $assetHelper
     */
    public function __construct(Asset $assetHelper)
    {
        $this->assetHelper = $assetHelper;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'zikulathememodule';
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('getPreviewImage', [$this, 'getPreviewImage'], ['is_safe' => ['html']])
        ];
    }

    public function getFilters()
    {
        return [];
    }

    /**
     * Display a theme preview image.
     *
     * @param $themeName
     * @param string $size
     * @param null $domId
     * @return string
     */
    public function getPreviewImage($themeName, $size = 'medium', $domId = null)
    {
        if (!isset($themeName)) {
            throw new \InvalidArgumentException('Invalid theme name.');
        }

        if (!in_array($size, array('large', 'medium', 'small'))) {
            $size = 'medium';
        }

        $idString = isset($domId) ? " id=\"$domId\"" : "";

        $themeInfo = \ThemeUtil::getInfo(\ThemeUtil::getIDFromName($themeName));
        $theme = \ThemeUtil::getTheme($themeInfo['name']);
        $filesrc = null;
        if (null === $theme) {
            if (file_exists($this->assetHelper->getSiteRoot() . "themes/{$themeInfo['directory']}/images/preview_{$size}.png")) {
                $filesrc = $this->assetHelper->getSiteRoot() . "/themes/{$themeInfo['directory']}/images/preview_{$size}.png";
            }
        } else {
            try {
                $filesrc = $this->assetHelper->resolve('@' . $themeName . ':images/preview_' . $size . '.png');
            } catch (\Exception $e) {
            }
        }
        if (!$filesrc) {
            $filesrc = $this->assetHelper->resolve('@ZikulaThemeModule:images/preview_' . $size . '.png');
        }

        return "<img{$idString} src=\"{$filesrc}\" alt=\"\" />";
    }
}
