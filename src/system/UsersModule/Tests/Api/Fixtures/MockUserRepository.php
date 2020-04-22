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

namespace Zikula\UsersModule\Tests\Api\Fixtures;

use DateTime;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Internal\Hydration\IterableResult;
use Zikula\UsersModule\Constant;
use Zikula\UsersModule\Entity\RepositoryInterface\UserRepositoryInterface;
use Zikula\UsersModule\Entity\UserEntity;

class MockUserRepository implements UserRepositoryInterface
{
    /**
     * @var array
     */
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
    }

    public function findOneBy(array $criteria)
    {
    }

    public function getClassName()
    {
    }

    public function matching(Criteria $criteria)
    {
    }

    public function findByUids(array $userIds = []): array
    {
        return [];
    }

    public function persistAndFlush(UserEntity $user): void
    {
    }

    public function removeAndFlush(UserEntity $user): void
    {
    }

    public function setApproved(UserEntity $user, DateTime $approvedOn, int $approvedBy = null): void
    {
    }

    public function queryBySearchForm(array $formData = [])
    {
    }

    public function getSearchResults(array $words = [])
    {
    }

    public function query(
        array $filter = [],
        array $sort = [],
        string $exprType = 'and'
    ) {
    }

    public function paginatedQuery(array $filter = [], array $sort = [], string $exprType = 'and', int $page = 1, int $pageSize = 25)
    {
    }

    public function count(array $filter = [], string $exprType = 'and'): int
    {
        return 123;
    }

    public function findAllAsIterable(): IterableResult
    {
    }

    public function searchActiveUser(array $unameFilter = [], int $limit = 50)
    {
    }

    public function countDuplicateUnames(string $uname, ?int $uid = NULL): int
    {
    }

    public function getByEmailAndAuthMethod(string $email, string $authMethod): array
    {
    }
}
