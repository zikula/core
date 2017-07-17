<?php

/*
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
use Doctrine\ORM\Internal\Hydration\IterableResult;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Zikula\UsersModule\Entity\UserEntity;

interface UserRepositoryInterface extends ObjectRepository, Selectable
{
    public function findByUids($uids);

    public function persistAndFlush(UserEntity $user);

    public function removeAndFlush(UserEntity $user);

    /**
     * @param UserEntity $user
     * @param \DateTime $approvedOn
     * @param string $approvedBy if null, user is 'self-approved'
     */
    public function setApproved(UserEntity $user, $approvedOn, $approvedBy = null);

    /**
     * @param array $formData
     * @return Paginator|UserEntity[]
     */
    public function queryBySearchForm(array $formData);

    /**
     * Find users for a search result
     * @param array $words
     * @return UserEntity[]
     */
    public function getSearchResults(array $words);

    /**
     * Fetch a collection of users. Optionally filter, sort, limit, offset results.
     *   filter = [field => value, field => value, field => ['operator' => '!=', 'operand' => value], ...]
     *   when value is not an array, operator is assumed to be '='
     *
     * @param array $filter
     * @param array $sort
     * @param int $limit
     * @param int $offset
     * @param string $exprType expression type to use in the filter (and|or)
     * @return Paginator|UserEntity[]
     */
    public function query(array $filter = [], array $sort = [], $limit = 0, $offset = 0, $exprType = 'and');

    /**
     * @param array $filter
     * @param string (and|or) $exprType expression type to use in the filter
     * @return integer
     */
    public function count(array $filter = [], $exprType = 'and');

    /**
     * Return all users as memory-saving iterable result.
     * @return IterableResult
     */
    public function findAllAsIterable();
}
