<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\RepositoryInterface;

use Doctrine\Common\Collections\Selectable;
use Doctrine\Common\Persistence\ObjectRepository;

interface HookBindingRepositoryInterface extends ObjectRepository, Selectable
{
    public function deleteByAreaNames(array $areaNames, $type = 'sareaid');

    public function deleteByBothAreas($subscriberArea, $providerArea);

    public function selectByAreaName($areaName, $type = 'sareaid');

    public function setSortOrder($order, $subscriberAreaName, $providerAreaName);

    public function findOneOrNullByAreas($subscriberArea, $providerArea);

    public function findByOwners($subscriberOwner, $providerOwner);
}
