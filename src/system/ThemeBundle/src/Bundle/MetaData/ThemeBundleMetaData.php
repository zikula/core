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

namespace Zikula\ThemeBundle\Bundle\MetaData;

use Symfony\Component\Translation\TranslatableMessage;
use Zikula\CoreBundle\Bundle\MetaData\BundleMetaDataInterface;
use function Symfony\Component\Translation\t;

class ThemeBundleMetaData implements BundleMetaDataInterface
{
    public function getDisplayName(): TranslatableMessage
    {
        return t('Theme management');
    }

    public function getDescription(): TranslatableMessage
    {
        return t('Dashboard integration and extension.');
    }

    public function getIcon(): string
    {
        return 'fas fa-palette';
    }

    public function getCategorizableEntityClasses(): array
    {
        return [];
    }
}
