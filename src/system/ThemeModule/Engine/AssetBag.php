<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ThemeModule\Engine;

use ArrayIterator;
use Countable;
use IteratorAggregate;

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
 * @see \Zikula\ThemeModule\Twig\Extension\AssetExtension::pageAddAsset()
 */
class AssetBag implements IteratorAggregate, Countable
{
    public const WEIGHT_JQUERY = 20;

    public const WEIGHT_JQUERY_UI = 25;

    public const WEIGHT_BOOTSTRAP_JS = 30;

    public const WEIGHT_BOOTSTRAP_ZIKULA = 31;

    public const WEIGHT_ROUTER_JS = 40;

    public const WEIGHT_ROUTES_JS = 41;

    public const WEIGHT_JS_TRANSLATOR = 50;

    public const WEIGHT_DEFAULT = 100;

    public const WEIGHT_JS_TRANSLATIONS = 110;

    public const WEIGHT_THEME_STYLESHEET = 120;

    /**
     * Array format:
     * $assets = [value => weight, value => weight, value => weight]
     * @var array
     */
    private $assets = [];

    /**
     * Add an array of assets or a single asset (string) to the bag.
     *
     * @param string|array $asset
     */
    public function add($asset): void
    {
        // ensure value is an array
        if (!is_array($asset)) {
            $asset = [$asset => self::WEIGHT_DEFAULT];
        }

        foreach ($asset as $source => $weight) {
            if (!isset($this->assets[$source]) || (isset($this->assets[$source]) && $this->assets[$source] > $weight)) {
                // keep original weight if lighter. set if not set already.
                $this->assets[$source] = $weight;
            }
        }
        asort($this->assets); // put array in order by weight
    }

    public function remove($var): void
    {
        if (!is_array($var)) {
            unset($this->assets[$var]);

            return;
        }
        $source = array_key_first($var);
        $weight = $var[$source];
        if (isset($this->assets[$source]) && $weight === $this->assets[$source]) {
            unset($this->assets[$source]);
        }
    }

    public function clear(): void
    {
        $this->assets = [];
    }

    public function all(): array
    {
        return array_keys($this->assets);
    }

    public function allWithWeight(): array
    {
        return $this->assets;
    }

    /**
     * Returns an iterator for parameters.
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->assets);
    }

    /**
     * Returns the number of parameters.
     */
    public function count(): int
    {
        return count($this->assets);
    }
}
