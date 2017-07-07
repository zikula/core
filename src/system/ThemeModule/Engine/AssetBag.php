<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ThemeModule\Engine;

/**
 * Class AssetBag
 *
 * This class provides an abstracted method of collecting, managing and retrieving page assets.
 * Each asset should be assigned a 'weight' which helps determine in what order the assets are loaded into the page.
 *  - A lighter weight loads before a heavier weight.
 *  - Assets of the same weight cannot be guaranteed to load in any specific order.
 *  - Duplicate assets with different weights will be loaded according to the lighter weight.
 *  - Assets not given a weight are assigned the self::WEIGHT_DEFAULT (100)
 *  - Core assets are loaded at weights 0, 1, 2, etc.
 * @see \Zikula\ThemeModule\EventListener\DefaultPageAssetSetterListener::setDefaultPageAssets()
 * @see \Zikula\Bundle\CoreBundle\Twig\Extension\CoreExtension::pageAddAsset()
 */
class AssetBag implements \IteratorAggregate, \Countable
{
    const WEIGHT_JQUERY = 20;
    const WEIGHT_BOOTSTRAP_JS = 21;
    const WEIGHT_BOOTSTRAP_ZIKULA = 22;
    const WEIGHT_HTML5SHIV = 23;
    const WEIGHT_ROUTER_JS = 24;
    const WEIGHT_ROUTES_JS = 25;
    const WEIGHT_JS_TRANSLATOR = 26;
    const WEIGHT_ZIKULA_JS_TRANSLATOR = 27;
    const WEIGHT_JS_TRANSLATIONS = 28;
    const WEIGHT_DEFAULT = 100;
    const WEIGHT_THEME_STYLESHEET = 120;

    /**
     * array format:
     * $assets = [value => weight, value => weight, value => weight]
     * @var array
     */
    private $assets = [];

    public function __construct()
    {
    }

    /**
     * Add an array of assets to the Bag
     * A string value is allowed also
     *
     * @param string|array $asset
     */
    public function add($asset)
    {
        // ensure value is an array
        if (!is_array($asset)) {
            $asset = [$asset => self::WEIGHT_DEFAULT];
        }
        foreach ($asset as $source => $weight) {
            if ((isset($this->assets[$source]) && $this->assets[$source] > $weight) || !isset($this->assets[$source])) {
                // keep original weight if lighter. set if not set already.
                $this->assets[$source] = $weight;
            }
        }
        asort($this->assets); // put array in order by weight
    }

    public function remove($var)
    {
        unset($this->assets[$var]);
    }

    public function clear()
    {
        $this->assets = [];
    }

    public function all()
    {
        // returns sorted asset
        return array_keys($this->assets);
    }

    /**
     * Returns an iterator for parameters.
     *
     * @return \ArrayIterator An \ArrayIterator instance
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->assets);
    }

    /**
     * Returns the number of parameters.
     *
     * @return int The number of parameters
     */
    public function count()
    {
        return count($this->assets);
    }
}
