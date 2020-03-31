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
use Zikula\Bundle\CoreBundle\Doctrine\Paginator;
use Zikula\ZAuthModule\Entity\AuthenticationMappingEntity;

interface AuthenticationMappingRepositoryInterface extends ObjectRepository, Selectable
{
    public function persistAndFlush(AuthenticationMappingEntity $entity): void;

    public function removeByZikulaId(int $userId): void;

    public function getByZikulaId(int $userId): AuthenticationMappingEntity;

    public function setEmailVerification(int $userId, bool $value = true): void;

    /**
     * @return Paginator
     */
    public function query(
        array $filter = [],
        array $sort = [],
        string $exprType = 'and',
        int $page = 1,
        int $pageSize = 25
    ): Paginator;

    public function getByExpiredPasswords();
}
