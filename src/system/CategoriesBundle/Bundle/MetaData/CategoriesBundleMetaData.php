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

namespace Zikula\CategoriesBundle\Bundle\MetaData;

use Symfony\Component\Translation\TranslatableMessage;
use Zikula\CoreBundle\Bundle\MetaData\BundleMetaDataInterface;
use function Symfony\Component\Translation\t;

class CategoriesBundleMetaData implements BundleMetaDataInterface
{
    public function getDisplayName(): TranslatableMessage
    {
        return t('Categories');
    }

    public function getDescription(): TranslatableMessage
    {
        return t('Categories integration and administration.');
    }

    public function getIcon(): string
    {
        return 'fas fa-sitemap';
    }

    public function getCategorizableEntityClasses(): array
    {
        return [];
    }
}
