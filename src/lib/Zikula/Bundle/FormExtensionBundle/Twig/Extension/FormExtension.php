<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\FormExtensionBundle\Twig\Extension;

use Zikula\ThemeModule\Engine\Asset;
use Zikula\ThemeModule\Engine\AssetBag;
use Zikula\ThemeModule\Engine\ParameterBag;

class FormExtension extends \Twig_Extension
{
    /**
     * @var Asset
     */
    private $assetHelper;

    /**
     * @var AssetBag
     */
    private $jsAssetBag;

    /**
     * @var ParameterBag
     */
    private $pageVars;

    /**
     * FormExtension constructor.
     *
     * @param Asset $assetHelper
     * @param AssetBag $jsAssetBag
     * @param ParameterBag $pageVars
     */
    public function __construct(Asset $assetHelper, AssetBag $jsAssetBag, ParameterBag $pageVars)
    {
        $this->assetHelper = $assetHelper;
        $this->jsAssetBag = $jsAssetBag;
        $this->pageVars = $pageVars;
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('polyfill', [$this, 'polyfill'])
        ];
    }

    /**
     * Adds polyfill features to be included into the page.
     *
     * @param array $features List of desired polyfills
     */
    public function polyfill(array $features = ['forms', 'forms-ext'])
    {
        $this->jsAssetBag->add([$this->assetHelper->resolve('webshim/js-webshim/minified/polyfiller.js') => AssetBag::WEIGHT_JQUERY + 1]);
        $this->jsAssetBag->add([$this->assetHelper->resolve('bundles/core/js/polyfiller.init.js') => AssetBag::WEIGHT_JQUERY + 2]);

        $existingFeatures = $this->pageVars->get('polyfill_features', []);
        $features = array_unique(array_merge($existingFeatures, $features));
        $this->pageVars->set('polyfill_features', $features);
    }
}
