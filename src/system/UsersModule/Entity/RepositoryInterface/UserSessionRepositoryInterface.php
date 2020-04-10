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

namespace Zikula\UsersModule\Entity\RepositoryInterface;

use DateTime;
use Doctrine\Common\Collections\Selectable;
use Doctrine\Persistence\ObjectRepository;
use Zikula\UsersModule\Entity\UserSessionEntity;

interface UserSessionRepositoryInterface extends ObjectRepository, Selectable
{
    /**
     * Return an array of UID's that have logged in since the provided datetime.
     */
    public function getUsersSince(DateTime $dateTime): array;

    public function countUsersSince(DateTime $dateTime): int;

    public function countGuestsSince(DateTime $dateTime): int;

    public function clearUnsavedData(): void;

    public function persistAndFlush(UserSessionEntity $entity): void;

    public function removeAndFlush(string $id): void;

    public function gc(string $level, int $inactiveMinutes, int $days): void;
}
