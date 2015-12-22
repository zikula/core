<?php

namespace Zikula\Core\Theme\Asset;

use Zikula\Core\Theme\AssetBag;

/**
 * Interface ResolverInterface
 * @package Zikula\Core\Theme\Asset
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
