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

namespace Zikula\PermissionsBundle\Tests\Api;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\IdentityTranslator;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\GroupsBundle\Constant as GroupsConstant;
use Zikula\PermissionsBundle\Repository\PermissionRepositoryInterface;
use Zikula\PermissionsBundle\Tests\Api\Fixtures\StubPermissionRepository;
use Zikula\UsersBundle\Api\ApiInterface\CurrentUserApiInterface;
use Zikula\UsersBundle\Constant;
use Zikula\UsersBundle\Entity\UserEntity;
use Zikula\UsersBundle\Repository\UserRepositoryInterface;

class AbstractPermissionTestCase extends TestCase
{
    /**
     * for testing purposes only.
     */
    public const RANDOM_USER_ID = 99;

    protected PermissionRepositoryInterface $permRepo;

    protected UserEntity $user;

    protected UserRepositoryInterface $userRepo;

    protected CurrentUserApiInterface $currentUserApi;

    protected TranslatorInterface $translator;

    protected function setUp(): void
    {
        $this->permRepo = new StubPermissionRepository();
        $this->user = $this
            ->getMockBuilder(UserEntity::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->userRepo = $this
            ->getMockBuilder(UserRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->userRepo
            ->method('findByUids')
            ->with($this->anything())
            ->willReturnCallback(function (array $uids) {
                $groups = new ArrayCollection();
                // getGroups returns [gid => $group, gid => $group, ...]
                if (in_array(self::RANDOM_USER_ID, $uids, true)) {
                    $groups = new ArrayCollection([GroupsConstant::GROUP_ID_USERS => []]);
                } elseif (in_array(Constant::USER_ID_ADMIN, $uids, true)) {
                    $groups = new ArrayCollection([GroupsConstant::GROUP_ID_USERS => [], GroupsConstant::GROUP_ID_ADMIN => []]);
                }
                $this->user
                    ->method('getGroups')
                    ->willReturn($groups);

                return [$this->user]; // must return an array of users.
            });
        $this->currentUserApi = $this
            ->getMockBuilder(CurrentUserApiInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->translator = new IdentityTranslator();
    }
}
