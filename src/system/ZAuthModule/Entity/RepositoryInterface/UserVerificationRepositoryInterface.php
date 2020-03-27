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

namespace Zikula\ZAuthModule\Entity\RepositoryInterface;

use Doctrine\Common\Collections\Selectable;
use Doctrine\Persistence\ObjectRepository;
use Zikula\UsersModule\Entity\UserEntity;
use Zikula\ZAuthModule\Entity\UserVerificationEntity;
use Zikula\ZAuthModule\ZAuthConstant;

interface UserVerificationRepositoryInterface extends ObjectRepository, Selectable
{
    public function persistAndFlush(UserVerificationEntity $entity): void;

    public function removeAndFlush(UserVerificationEntity $entity): void;

    public function removeByZikulaId(int $userId): void;

    /**
     * @return UserEntity[] records deleted
     */
    public function purgeExpiredRecords(
        int $daysOld,
        int $changeType = ZAuthConstant::VERIFYCHGTYPE_REGEMAIL,
        bool $deleteUserEntities = true
    ): array;

    /**
     * Removes a record from the users_verifychg table for a specified user id and change type.
     *
     * @param int $userId The uid of the verifychg record to remove. Required
     * @param int|array $types The changetype(s) of the verifychg record to remove. If more
     *                         than one type is to be removed, use an array. Optional. If
     *                         not specifed, all verifychg records for the user will be
     *                         removed. Note: specifying an empty array will remove none
     */
    public function resetVerifyChgFor(int $userId, $types = null): void;

    public function isVerificationEmailSent(int $userId): bool;

    /**
     * Set a confirmation code.
     */
    public function setVerificationCode(
        int $userId,
        int $changeType = ZAuthConstant::VERIFYCHGTYPE_PWD,
        string $hashedConfirmationCode = null,
        string $email = null
    ): void;
}
