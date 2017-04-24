<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('getPreviewImagePath', [$this, 'getPreviewImagePath'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('zasset', [$this, 'getAssetPath']),
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

        if (!in_array($size, ['large', 'medium', 'small'])) {
            $size = 'medium';
        }

        $themeInfo = \ThemeUtil::getInfo(\ThemeUtil::getIDFromName($themeName));
        $theme = \ThemeUtil::getTheme($themeInfo['name']);
        $imagePath = null;
        if (null === $theme) {
            if (file_exists($this->assetHelper->getSiteRoot() . "/themes/{$themeInfo['directory']}/images/preview_{$size}.png")) {
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

    public function getAssetPath($path)
    {
        return $this->assetHelper->resolve($path);
    }
}
