<?php
/**
 * Created by PhpStorm.
 * User: craig
 * Date: 8/11/15
 * Time: 7:13 PM
 */

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