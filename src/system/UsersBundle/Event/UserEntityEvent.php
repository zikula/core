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

namespace Zikula\UsersBundle\Event;

use Zikula\UsersBundle\Entity\User;

/**
 * A 'Generic' event that accepts a UserEntity on construction and sets
 * an immutable datetime object for tracking purposes.
 */
class UserEntityEvent
{
    private ?User $user;

    private \DateTimeImmutable $date;

    public function __construct(?User $user)
    {
        $this->user = $user;
        $this->date = new \DateTimeImmutable('now');
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }
}
