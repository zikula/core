<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ZAuthModule\Entity\RepositoryInterface;

use Doctrine\Common\Collections\Selectable;
use Doctrine\Common\Persistence\ObjectRepository;
use Zikula\ZAuthModule\Entity\AuthenticationMappingEntity;

interface AuthenticationMappingRepositoryInterface extends ObjectRepository, Selectable
{
    public function persistAndFlush(AuthenticationMappingEntity $entity);

    public function removeByZikulaId($uid);

    /**
     * @param integer $uid
     * @return AuthenticationMappingEntity
     */
    public function getByZikulaId($uid);

    /**
     * @param $uid
     * @param bool $value
     */
    public function setEmailVerification($uid, $value = true);

    public function query(array $filter = [], array $sort = [], $limit = 0, $offset = 0, $exprType = 'and');
}
