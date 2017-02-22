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

use Zikula\UsersModule\Api\CurrentUserApi;
use Zikula\UsersModule\Entity\RepositoryInterface\UserRepositoryInterface;

class IdentityProfileModule implements ProfileModuleInterface
{
    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     * @var CurrentUserApi
     */
    private $currentUserApi;

    /**
     * IdentityProfileModule constructor.
     * @param UserRepositoryInterface $userRepository
     * @param CurrentUserApi $currentUserApi
     */
    public function __construct(UserRepositoryInterface $userRepository, CurrentUserApi $currentUserApi)
    {
        $this->userRepository = $userRepository;
        $this->currentUserApi = $currentUserApi;
    }

    /**
* @inheritDoc
     */
    public function getDisplayName($uid = null)
    {
        if (!isset($uid)) {
            return $this->currentUserApi->get('uname');
        }

        return $this->userRepository->find($uid)->getUname();
    }

    /**
* @inheritDoc
     */
    public function getProfileUrl($uid = null)
    {
        return '#';
    }
}
