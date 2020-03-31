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

namespace Zikula\UsersModule\Event;

use Zikula\UsersModule\Entity\UserEntity;

/**
 * A 'Generic' event that requires a UserEntity on construction and sets
 * an immutable datetime object for tracking purposes.
 */
class UserEntityEvent
{
    /**
     * @var UserEntity
     */
    private $user;

    /**
     * @var \DateTimeImmutable
     */
    private $date;

    public function __construct(UserEntity $user)
    {
        $this->user = $user;
        $this->date = new \DateTimeImmutable('now');
    }

    public function getUser(): UserEntity
    {
        return $this->user;
    }

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }
}
