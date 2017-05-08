<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Tests\Api\Fixtures;

use Doctrine\Common\Collections\Criteria;
use Zikula\UsersModule\Constant;
use Zikula\UsersModule\Entity\RepositoryInterface\UserRepositoryInterface;
use Zikula\UsersModule\Entity\UserEntity;

class MockUserRepository implements UserRepositoryInterface
{
    private $users = [];

    public function __construct()
    {
        $user = new UserEntity();
        $user->setUid(Constant::USER_ID_ANONYMOUS);
        $user->setUname('guest');
        $user->setActivated(Constant::ACTIVATED_ACTIVE);
        $this->users[Constant::USER_ID_ANONYMOUS] = $user;

        $user = new UserEntity();
        $user->setUid(42);
        $user->setUname('FooName');
        $user->setEmail('foo@foo.com');
        $user->setActivated(Constant::ACTIVATED_ACTIVE);
        $user->setAttribute('legs', 2);
        $this->users[42] = $user;
    }

    public function find($id)
    {
        return isset($id) ? $this->users[$id] : null;
    }

    public function findAll()
    {
        return $this->users;
    }

    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        // TODO: Implement findBy() method.
    }

    public function findOneBy(array $criteria)
    {
        // TODO: Implement findOneBy() method.
    }

    public function getClassName()
    {
        // TODO: Implement getClassName() method.
    }

    public function matching(Criteria $criteria)
    {
        // TODO: Implement matching() method.
    }

    public function findByUids($uids)
    {
        // TODO: Implement findByUids() method.
    }

    public function persistAndFlush(UserEntity $user)
    {
        // TODO: Implement persistAndFlush() method.
    }

    public function removeAndFlush(UserEntity $user)
    {
        // TODO: Implement removeAndFlush() method.
    }

    public function setApproved(UserEntity $user, $approvedOn, $approvedBy = null)
    {
        // TODO: Implement setApproved() method.
    }

    public function queryBySearchForm(array $formData)
    {
        // TODO: Implement queryBySearchForm() method.
    }

    public function getSearchResults(array $words)
    {
        // TODO: Implement getSearchResults() method.
    }

    public function query(array $filter = [], array $sort = [], $limit = 0, $offset = 0, $exprType = 'and')
    {
        // TODO: Implement query() method.
    }

    public function count(array $filter = [], $exprType = 'and')
    {
        // TODO: Implement count() method.
    }

    public function findAllAsIterable()
    {
        // TODO: Implement findAllAsIterable() method.
    }
}
