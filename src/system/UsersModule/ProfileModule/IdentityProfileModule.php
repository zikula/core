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

namespace Zikula\UsersModule\ProfileModule;

use Zikula\UsersModule\Api\ApiInterface\CurrentUserApiInterface;
use Zikula\UsersModule\Entity\UserEntity;
use Zikula\UsersModule\Repository\UserRepositoryInterface;

class IdentityProfileModule implements ProfileModuleInterface
{
    public function __construct(private readonly UserRepositoryInterface $userRepository, private readonly CurrentUserApiInterface $currentUserApi)
    {
    }

    public function getDisplayName($userId = null): string
    {
        if (!isset($userId)) {
            return $this->currentUserApi->get('uname');
        }

        /** @var UserEntity $user */
        $user = $this->userRepository->find($userId);

        return null !== $user ? $user->getUname() : '';
    }

    public function getProfileUrl($userId = null): string
    {
        return '#';
    }

    public function getAvatar($userId = null, array $parameters = []): string
    {
        return '';
    }

    public function getBundleName(): string
    {
        return 'ZikulaUsersModule';
    }
}
