<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
