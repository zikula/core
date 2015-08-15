<?php

namespace Zikula\Core\Theme\Asset;

use Zikula\Core\Theme\AssetBag;

interface ResolverInterface
{
    public function compile();

    /**
     * @return AssetBag
     */
    public function getBag();
}