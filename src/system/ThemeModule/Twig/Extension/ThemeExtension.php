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

        if (!in_array($size, ['large', 'medium', 'small'])) {
            $size = 'medium';
        }

        try {
            $imagePath = $this->assetHelper->resolve('@' . $themeName . ':images/preview_' . $size . '.png');
        } catch (\Exception $e) {
            $imagePath = $this->assetHelper->resolve('@ZikulaThemeModule:images/preview_' . $size . '.png');
        }

        return $imagePath;
    }
}
