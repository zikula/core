<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Entity\RepositoryInterface;

use Doctrine\Common\Collections\Selectable;
use Doctrine\Common\Persistence\ObjectRepository;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\UsersModule\Entity\UserVerificationEntity;

interface UserVerificationRepositoryInterface extends ObjectRepository, Selectable
{
    public function persistAndFlush(UserVerificationEntity $entity);

    public function removeAndFlush(UserVerificationEntity $entity);

    /**
     * @param integer $daysOld
     * @param int $changeType
     * @param bool $deleteUserEntities
     * @return array UserEntity[] records deleted
     */
    public function purgeExpiredRecords($daysOld, $changeType = UsersConstant::VERIFYCHGTYPE_REGEMAIL, $deleteUserEntities = true);

    public function resetVerifyChgFor($uid, $types = null);

    public function isVerificationEmailSent($uid);

    public function setVerificationCode($uid, $changeType = UsersConstant::VERIFYCHGTYPE_PWD, $email = null);
}
