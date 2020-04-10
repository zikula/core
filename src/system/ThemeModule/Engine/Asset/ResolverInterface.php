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

namespace Zikula\ThemeModule\Engine\Asset;

use Zikula\ThemeModule\Engine\AssetBag;

/**
 * Interface ResolverInterface
 *
 * Provide an interface for Resolver classes.
 */
interface ResolverInterface
{
    public function compile(): string;

    public function getBag(): AssetBag;
}
