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

namespace Zikula\ExtensionsModule\Event;

use Zikula\ExtensionsModule\AbstractExtension;
use Zikula\ExtensionsModule\Entity\ExtensionEntity;

class ExtensionStateEvent
{
    /**
     * @var null|AbstractExtension
     */
    private $extensionBundle;

    /**
     * @var null|ExtensionEntity
     */
    private $extensionEntity;

    public function __construct(?AbstractExtension $extensionBundle = null, ?ExtensionEntity $extensionEntity = null)
    {
        $this->extensionBundle = $extensionBundle;
        $this->extensionEntity = $extensionEntity;
    }

    public function getExtensionBundle(): ?AbstractExtension
    {
        return $this->extensionBundle;
    }

    public function getExtensionEntity(): ?ExtensionEntity
    {
        return $this->extensionEntity;
    }
}
