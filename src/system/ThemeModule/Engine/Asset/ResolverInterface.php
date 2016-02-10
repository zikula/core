<?php

namespace Zikula\ThemeModule\Engine\Asset;

use Zikula\ThemeModule\Engine\AssetBag;

/**
 * Interface ResolverInterface
 * @package Zikula\ThemeModule\Engine\Asset
 *
 * Provide an interface for Resolver classes.
 */
interface ResolverInterface
{
    public function compile();

    /**
     * @return AssetBag
     */
    public function getBag();
}
