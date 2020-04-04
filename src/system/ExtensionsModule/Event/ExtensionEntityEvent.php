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

namespace Zikula\ExtensionsModule\Event;

use Zikula\ExtensionsModule\Entity\ExtensionEntity;

class ExtensionEntityEvent
{
    private $extensionEntity;

    public function __construct(ExtensionEntity $extensionEntity)
    {
        $this->extensionEntity = $extensionEntity;
    }

    public function getExtension(): ExtensionEntity
    {
        return $this->extensionEntity;
    }
}
