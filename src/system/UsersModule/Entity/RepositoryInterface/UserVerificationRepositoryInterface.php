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

use Zikula\UsersModule\Entity\UserVerificationEntity;

interface UserVerificationRepositoryInterface
{
    public function persistAndFlush(UserVerificationEntity $entity);
    public function removeAndFlush(UserVerificationEntity $entity);

    /**
     * @param integer $daysOld
     * @return array UserEntity[] records deleted
     */
    public function purgeExpiredRecords($daysOld);
    
    public function findOneBy(array $criteria, array $orderBy = null);
}
