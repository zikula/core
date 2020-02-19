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
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ObjectRepository;
use Zikula\ZAuthModule\Entity\AuthenticationMappingEntity;

interface AuthenticationMappingRepositoryInterface extends ObjectRepository, Selectable
{
    public function persistAndFlush(AuthenticationMappingEntity $entity): void;

    public function removeByZikulaId(int $userId): void;

    public function getByZikulaId(int $userId): AuthenticationMappingEntity;

    public function setEmailVerification(int $userId, bool $value = true): void;

    /**
     * @return Paginator|AuthenticationMappingEntity[]
     */
    public function query(
        array $filter = [],
        array $sort = [],
        int $limit = 0,
        int $offset = 0,
        string $exprType = 'and'
    );

    public function getByExpiredPasswords();
}
