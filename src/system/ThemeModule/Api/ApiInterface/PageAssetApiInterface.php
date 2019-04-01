<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ThemeModule\Api\ApiInterface;

use Zikula\ThemeModule\Engine\AssetBag;

interface PageAssetApiInterface
{
    /**
     * Zikula allows only the following asset types:
     * <ul>
     *  <li>stylesheet</li>
     *  <li>javascript</li>
     *  <li>header</li>
     *  <li>footer</li>
     * </ul>
     */
    public function add(string $type, string $value, int $weight = AssetBag::WEIGHT_DEFAULT): void;
}
