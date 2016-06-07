<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Helper;


use Symfony\Component\HttpFoundation\Session\Session;
use Zikula\PermissionsModule\Api\PermissionApi;
use Zikula\UsersModule\Entity\Repository\UserRepository;
use Zikula\UsersModule\Entity\UserEntity;

class AccessHelper
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var PermissionApi
     */
    private $permissionApi;

    /**
     * AccessHelper constructor.
     * @param Session $session
     * @param UserRepository $userRepository
     * @param PermissionApi $permissionApi
     */
    public function __construct(Session $session, UserRepository $userRepository, PermissionApi $permissionApi)
    {
        $this->session = $session;
        $this->userRepository = $userRepository;
        $this->permissionApi = $permissionApi;
    }

    public function login(UserEntity $user, $method, $rememberMe = false)
    {
        $nowUTC = new \DateTime(null, new \DateTimeZone('UTC'));
        $user->setLastlogin($nowUTC);
        $this->userRepository->persistAndFlush($user);
        $this->session->start();
        $this->session->set('uid', $user->getUid());
        $this->session->set('authenticationMethod', $method);
        if ($rememberMe) {
            $this->session->set('rememberme', 1);
        }
        $this->permissionApi->resetPermissionsForUser($user->getUid());
    }

    public function logout()
    {
        $this->session->invalidate();

        return true;
    }
}
