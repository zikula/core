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

/**
 * Occurs before updating the state of an extension. The event itself cannot affect the workflow unless
 * an exception is thrown to completely halt. For example, performing a permissions check.
 */
class ExtensionPreStateChangeEvent extends ExtensionEntityEvent
{
    /**
     * The intended new state for the extension.
     * @var int
     */
    private $newState;

    public function __construct(ExtensionEntity $extensionEntity, int $newState)
    {
        parent::__construct($extensionEntity);
        $this->newState = $newState;
    }

    public function getNewState(): int
    {
        return $this->newState;
    }
}
