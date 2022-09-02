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

namespace Zikula\UsersBundle\Repository;

use DateTime;
use Doctrine\Common\Collections\Selectable;
use Doctrine\ORM\Internal\Hydration\IterableResult;
use Doctrine\Persistence\ObjectRepository;
use Zikula\Bundle\CoreBundle\Doctrine\PaginatorInterface;
use Zikula\UsersBundle\Entity\User;

interface UserRepositoryInterface extends ObjectRepository, Selectable
{
    public function findByUids(array $userIds = []): array;

    public function persistAndFlush(User $user): void;

    public function removeAndFlush(User $user): void;

    /**
     * If $approvedBy is null, user will be considered as 'self-approved'.
     */
    public function setApproved(User $user, DateTime $approvedOn, int $approvedBy = null): void;

    /**
     * @return User[]
     */
    public function queryBySearchForm(array $formData = []);

    /**
     * Find users for a search result
     *
     * @return User[]
     */
    public function getSearchResults(array $words = []);

    /**
     * Fetch a collection of users. Optionally filter, sort, limit, offset results.
     * filter = [field => value, field => value, field => ['operator => '!=', 'operand' => value], ...]
     * when value is not an array, operator is assumed to be '='
     *
     * @return PaginatorInterface
     */
    public function paginatedQuery(
        array $filter = [],
        array $sort = [],
        string $exprType = 'and',
        int $page = 1,
        int $pageSize = 25
    );

    /**
     * @return User[]
     */
    public function query(
        array $filter = [],
        array $sort = [],
        string $exprType = 'and'
    );

    public function count(array $filter = [], string $exprType = 'and'): int;

    /**
     * Return all users as memory-saving iterable result.
     */
    public function findAllAsIterable(): IterableResult;

    /**
     * @return User[]
     */
    public function searchActiveUser(array $unameFilter = []);

    public function countDuplicateUnames(string $uname, ?int $uid = null): int;

    public function getByEmailAndAuthMethod(string $email, string $authMethod): array;
}
