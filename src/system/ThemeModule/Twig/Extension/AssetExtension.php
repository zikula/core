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

use Zikula\ThemeModule\Api\PageAssetApi;
use Zikula\ThemeModule\Engine\AssetBag;

class AssetExtension extends \Twig_Extension
{
    /**
     * @var PageAssetApi
     */
    private $pageAssetApi;

    /**
     * AssetExtension constructor.
     * @param PageAssetApi $pageAssetApi
     */
    public function __construct(PageAssetApi $pageAssetApi)
    {
        $this->pageAssetApi = $pageAssetApi;
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
        ];
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
}
