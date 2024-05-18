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

namespace Zikula\CoreBundle\Bundle\MetaData;

use Symfony\Component\Translation\TranslatableMessage;

interface BundleMetaDataInterface
{
    public function getDisplayName(): TranslatableMessage;

    public function getDescription(): TranslatableMessage;

    public function getIcon(): string;
}
