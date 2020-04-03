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

namespace Zikula\GroupsModule\Event;

use Zikula\GroupsModule\Entity\GroupApplicationEntity;

/**
 * A 'Generic' event that accepts a GroupApplicationEntity on construction and sets
 * an immutable datetime object for tracking purposes.
 */
class GroupApplicationEntityEvent
{
    /**
     * @var GroupApplicationEntity
     */
    private $groupApplication;

    /**
     * @var \DateTimeImmutable
     */
    private $date;

    public function __construct(GroupApplicationEntity $groupApplicationEntity)
    {
        $this->groupApplication = $groupApplicationEntity;
        $this->date = new \DateTimeImmutable('now');
    }

    public function getGroupApplication(): GroupApplicationEntity
    {
        return $this->groupApplication;
    }

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }
}
