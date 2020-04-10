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

namespace Zikula\GroupsModule\Event;

use Zikula\GroupsModule\Entity\GroupEntity;

/**
 * A 'Generic' event that accepts a GroupEntity on construction and sets
 * an immutable datetime object for tracking purposes.
 */
class GroupEntityEvent
{
    /**
     * @var GroupEntity
     */
    private $group;

    /**
     * @var \DateTimeImmutable
     */
    private $date;

    public function __construct(GroupEntity $groupEntity)
    {
        $this->group = $groupEntity;
        $this->date = new \DateTimeImmutable('now');
    }

    public function getGroup(): GroupEntity
    {
        return $this->group;
    }

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }
}
