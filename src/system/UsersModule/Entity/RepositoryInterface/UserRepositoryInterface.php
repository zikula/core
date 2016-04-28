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

use Zikula\UsersModule\Entity\UserEntity;
use Doctrine\ORM\Tools\Pagination\Paginator;

interface UserRepositoryInterface
{
    public function findByUids($uids);

    public function persistAndFlush(UserEntity $user);

    public function removeAndFlush(UserEntity $user);

    /**
     * @param UserEntity $user
     * @param $approvedOn
     * @param null $approvedBy if null, user is 'self-approved'
     */
    public function setApproved(UserEntity $user, $approvedOn, $approvedBy = null);

    /**
     * @param $id
     * @param null $lockMode
     * @param null $lockVersion
     * @return UserEntity
     */
    public function find($id, $lockMode = null, $lockVersion = null);

    /**
     * @param array $formData
     * @return Paginator
     */
    public function queryBySearchForm(array $formData);

    /**
     * @param array $filter
     * @param array $sort
     * @param int $limit
     * @param int $offset
     * @param string (and|or) $exprType expression type to use in the filter
     * @return Paginator
     */
    public function query(array $filter = [], array $sort = [], $limit = 0, $offset = 0, $exprType = 'and');

    /**
     * @param array $filter
     * @param string (and|or) $exprType expression type to use in the filter
     * @return integer
     */
    public function count(array $filter = [], $exprType = 'and');
}
