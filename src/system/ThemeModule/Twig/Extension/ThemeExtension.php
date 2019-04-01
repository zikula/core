<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ThemeModule\Twig\Extension;

use Exception;
use InvalidArgumentException;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Zikula\ThemeModule\Api\ApiInterface\PageAssetApiInterface;
use Zikula\ThemeModule\Engine\Asset;
use Zikula\ThemeModule\Engine\AssetBag;

class ThemeExtension extends AbstractExtension
{
    /**
     * @var PageAssetApiInterface
     */
    private $pageAssetApi;

    /**
     * @var Asset
     */
    private $assetHelper;

    public function __construct(PageAssetApiInterface $pageAssetApi, Asset $assetHelper)
    {
        $this->pageAssetApi = $pageAssetApi;
        $this->assetHelper = $assetHelper;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('pageAddAsset', [$this, 'pageAddAsset']),
            new TwigFunction('getPreviewImagePath', [$this, 'getPreviewImagePath'], ['is_safe' => ['html']]),
            new TwigFunction('zasset', [$this, 'getAssetPath'])
        ];
    }

    /**
     * Zikula allows only the following asset types:
     * <ul>
     *  <li>stylesheet</li>
     *  <li>javascript</li>
     *  <li>header</li>
     *  <li>footer</li>
     * </ul>
     */
    public function pageAddAsset(string $type, string $value, int $weight = AssetBag::WEIGHT_DEFAULT): void
    {
        $this->pageAssetApi->add($type, $value, $weight);
    }

    /**
     * Get path to theme preview image.
     */
    public function getPreviewImagePath(string $themeName, string $size = 'medium'): string
    {
        if (!isset($themeName)) {
            throw new InvalidArgumentException('Invalid theme name.');
        }

        if (!in_array($size, ['large', 'medium', 'small'])) {
            $size = 'medium';
        }

        try {
            $imagePath = $this->assetHelper->resolve('@' . $themeName . ':images/preview_' . $size . '.png');
        } catch (Exception $exception) {
            $imagePath = $this->assetHelper->resolve('@ZikulaThemeModule:images/preview_' . $size . '.png');
        }

        return $imagePath;
    }

    /**
     * Resolves a given asset path.
     */
    public function getAssetPath(string $path): string
    {
        return $this->assetHelper->resolve($path);
    }
}
