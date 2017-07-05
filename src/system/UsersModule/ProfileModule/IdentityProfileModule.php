<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\ProfileModule;

use Zikula\UsersModule\Api\ApiInterface\CurrentUserApiInterface;
use Zikula\UsersModule\Entity\RepositoryInterface\UserRepositoryInterface;

class IdentityProfileModule implements ProfileModuleInterface
{
    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     * @var CurrentUserApiInterface
     */
    private $currentUserApi;

    /**
     * IdentityProfileModule constructor.
     * @param UserRepositoryInterface $userRepository
     * @param CurrentUserApiInterface $currentUserApi
     */
    public function __construct(UserRepositoryInterface $userRepository, CurrentUserApiInterface $currentUserApi)
    {
        $this->userRepository = $userRepository;
        $this->currentUserApi = $currentUserApi;
    }

    /**
     * {@inheritdoc}
     */
    public function getDisplayName($uid = null)
    {
        if (!isset($uid)) {
            return $this->currentUserApi->get('uname');
        }

        return $this->userRepository->find($uid)->getUname();
    }

    /**
     * {@inheritdoc}
     */
    public function getProfileUrl($uid = null)
    {
        return '#';
    }

    /**
     * {@inheritdoc}
     */
    public function getAvatar($uid = null, array $parameters = [])
    {
        return '';
    }
}
