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

namespace Zikula\ThemeBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Zikula\CoreBundle\Bundle\MetaData\BundleMetaDataInterface;
use Zikula\CoreBundle\Bundle\MetaData\MetaDataAwareBundleInterface;
use Zikula\ThemeBundle\Bundle\MetaData\ThemeBundleMetaData;

class ZikulaThemeBundle extends Bundle implements MetaDataAwareBundleInterface
{
    public function getMetaData(): BundleMetaDataInterface
    {
        return $this->container->get(ThemeBundleMetaData::class);
    }
}
