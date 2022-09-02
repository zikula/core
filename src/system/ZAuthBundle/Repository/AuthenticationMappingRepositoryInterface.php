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

namespace Zikula\ZAuthBundle\Repository;

use Doctrine\Common\Collections\Selectable;
use Doctrine\Persistence\ObjectRepository;
use Zikula\Bundle\CoreBundle\Doctrine\PaginatorInterface;
use Zikula\ZAuthBundle\Entity\AuthenticationMapping;

interface AuthenticationMappingRepositoryInterface extends ObjectRepository, Selectable
{
    public function persistAndFlush(AuthenticationMapping $entity): void;

    public function removeByZikulaId(int $userId): void;

    public function getByZikulaId(int $userId): ?AuthenticationMapping;

    public function setEmailVerification(int $userId, bool $value = true): void;

    public function query(
        array $filter = [],
        array $sort = [],
        string $exprType = 'and',
        int $page = 1,
        int $pageSize = 25
    ): PaginatorInterface;

    public function getByExpiredPasswords();
}
