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

namespace Zikula\RoutesModule\Event;

class RoutesNewlyAvailableEvent
{
    /**
     * @var array
     */
    private $extensionIds = [];

    public function __construct(array $extensionIds)
    {
        $this->extensionIds = $extensionIds;
    }

    public function getExtensionIds(): array
    {
        return $this->extensionIds;
    }
}
