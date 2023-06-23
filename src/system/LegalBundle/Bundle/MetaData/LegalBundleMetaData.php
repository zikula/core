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

namespace Zikula\LegalBundle\Bundle\MetaData;

use Symfony\Component\Translation\TranslatableMessage;
use Zikula\CoreBundle\Bundle\MetaData\BundleMetaDataInterface;
use function Symfony\Component\Translation\t;

class LegalBundleMetaData implements BundleMetaDataInterface
{
    public function getDisplayName(): TranslatableMessage
    {
        return t('Legal docs');
    }

    public function getDescription(): TranslatableMessage
    {
        return t('Site legal documents integration.');
    }

    public function getIcon(): string
    {
        return 'fas fa-gavel';
    }

    public function getCategorizableEntityClasses(): array
    {
        return [];
    }
}
