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

use Zikula\ThemeModule\Engine\Asset;

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
            new \Twig_SimpleFunction('getPreviewImagePath', [$this, 'getPreviewImagePath'], ['is_safe' => ['html']])
        ];
    }

    public function getFilters()
    {
        return [];
    }

    /**
     * Get path to theme preview image.
     *
     * @param $themeName
     * @param string $size
     * @return string
     */
    public function getPreviewImagePath($themeName, $size = 'medium')
    {
        if (!isset($themeName)) {
            throw new \InvalidArgumentException('Invalid theme name.');
        }

        if (!in_array($size, array('large', 'medium', 'small'))) {
            $size = 'medium';
        }

        $themeInfo = \ThemeUtil::getInfo(\ThemeUtil::getIDFromName($themeName));
        $theme = \ThemeUtil::getTheme($themeInfo['name']);
        $imagePath = null;
        if (null === $theme) {
            if (file_exists($this->assetHelper->getSiteRoot() . "themes/{$themeInfo['directory']}/images/preview_{$size}.png")) {
                $imagePath = $this->assetHelper->getSiteRoot() . "/themes/{$themeInfo['directory']}/images/preview_{$size}.png";
            }
        } else {
            try {
                $imagePath = $this->assetHelper->resolve('@' . $themeName . ':images/preview_' . $size . '.png');
            } catch (\Exception $e) {
            }
        }
        if (!$imagePath) {
            $imagePath = $this->assetHelper->resolve('@ZikulaThemeModule:images/preview_' . $size . '.png');
        }

        return $imagePath;
    }
}
