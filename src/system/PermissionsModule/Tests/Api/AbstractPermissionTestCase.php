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

namespace Zikula\PermissionsModule\Tests\Api;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\IdentityTranslator;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\GroupsModule\Constant as GroupsConstant;
use Zikula\PermissionsModule\Entity\RepositoryInterface\PermissionRepositoryInterface;
use Zikula\PermissionsModule\Tests\Api\Fixtures\StubPermissionRepository;
use Zikula\UsersModule\Api\ApiInterface\CurrentUserApiInterface;
use Zikula\UsersModule\Constant;
use Zikula\UsersModule\Entity\RepositoryInterface\UserRepositoryInterface;
use Zikula\UsersModule\Entity\UserEntity;

/**
 * @
 */
class AbstractPermissionTestCase extends TestCase
{
    /**
     * for testing purposes only.
     */
    public const RANDOM_USER_ID = 99;

    /**
     * @var PermissionRepositoryInterface
     */
    protected $permRepo;

    /**
     * @var UserEntity
     */
    protected $user;

    /**
     * @var UserRepositoryInterface
     */
    protected $userRepo;

    /**
     * @var CurrentUserApiInterface
     */
    protected $currentUserApi;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

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
