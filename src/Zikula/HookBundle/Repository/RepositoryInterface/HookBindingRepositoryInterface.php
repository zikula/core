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

namespace Zikula\Bundle\HookBundle\Repository\RepositoryInterface;

use Doctrine\Common\Collections\Selectable;
use Doctrine\Persistence\ObjectRepository;

/**
 * @deprecated remove at Core 4.0.0
 */
interface HookBindingRepositoryInterface extends ObjectRepository, Selectable
{
    public function deleteByBothAreas(string $subscriberArea, string $providerArea): void;

    public function selectByAreaName(string $areaName, string $type = 'sareaid'): array;

    public function setSortOrder(int $order, string $subscriberAreaName, string $providerAreaName): void;

    public function findOneOrNullByAreas(string $subscriberArea, string $providerArea);

    public function findByOwners(string $subscriberOwner, string $providerOwner): array;

    public function deleteAllByOwner(string $ownerstring): void;
}
