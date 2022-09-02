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

namespace Zikula\UsersBundle\ProfileBundle;

use Zikula\UsersBundle\Api\ApiInterface\CurrentUserApiInterface;
use Zikula\UsersBundle\Entity\User;
use Zikula\UsersBundle\Repository\UserRepositoryInterface;

class IdentityProfileBundle implements ProfileBundleInterface
{
    public function __construct(private readonly UserRepositoryInterface $userRepository, private readonly CurrentUserApiInterface $currentUserApi)
    {
    }

    public function getDisplayName($userId = null): string
    {
        if (!isset($userId)) {
            return $this->currentUserApi->get('uname');
        }

        /** @var User $user */
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
        return 'ZikulaUsersBundle';
    }
}
