<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ThemeModule\Twig\Extension;

use Zikula\ThemeModule\Api\ApiInterface\PageAssetApiInterface;
use Zikula\ThemeModule\Engine\Asset;
use Zikula\ThemeModule\Engine\AssetBag;

class ThemeExtension extends \Twig_Extension
{
    /**
     * @var PageAssetApiInterface
     */
    private $pageAssetApi;

    /**
     * @var Asset
     */
    private $assetHelper;

    /**
     * ThemeExtension constructor.
     * @param PageAssetApiInterface $pageAssetApi
     * @param Asset $assetHelper
     */
    public function __construct(PageAssetApiInterface $pageAssetApi, Asset $assetHelper)
    {
        $this->pageAssetApi = $pageAssetApi;
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
            new \Twig_SimpleFunction('pageAddAsset', [$this, 'pageAddAsset']),
            new \Twig_SimpleFunction('getPreviewImagePath', [$this, 'getPreviewImagePath'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('zasset', [$this, 'getAssetPath']),
        ];
    }

    public function getFilters()
    {
        return [];
    }

    /**
     * Zikula allows only the following asset types
     * <ul>
     *  <li>stylesheet</li>
     *  <li>javascript</li>
     *  <li>header</li>
     *  <li>footer</li>
     * </ul>
     *
     * @param string $type
     * @param string $value
     * @param int $weight
     */
    public function pageAddAsset($type, $value, $weight = AssetBag::WEIGHT_DEFAULT)
    {
        $this->pageAssetApi->add($type, $value, $weight);
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

    /**
     * Resolves a given asset path.
     *
     * @param string $path
     * @return string
     */
    public function getAssetPath($path)
    {
        return $this->assetHelper->resolve($path);
    }
}
