<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Helper;

use Symfony\Component\HttpFoundation\Session\Session;
use Zikula\Bridge\HttpFoundation\ZikulaSessionStorage;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\UsersModule\Entity\RepositoryInterface\UserRepositoryInterface;
use Zikula\UsersModule\Entity\UserEntity;

class AccessHelper
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     * @var PermissionApiInterface
     */
    private $permissionApi;

    /**
     * @var VariableApiInterface
     */
    private $variableApi;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * AccessHelper constructor.
     * @param Session $session
     * @param UserRepositoryInterface $userRepository
     * @param PermissionApiInterface $permissionApi
     * @param VariableApiInterface $variableApi
     * @param TranslatorInterface $translator
     */
    public function __construct(
        Session $session,
        UserRepositoryInterface $userRepository,
        PermissionApiInterface $permissionApi,
        VariableApiInterface $variableApi,
        TranslatorInterface $translator
    ) {
        $this->session = $session;
        $this->userRepository = $userRepository;
        $this->permissionApi = $permissionApi;
        $this->variableApi = $variableApi;
        $this->translator = $translator;
    }

    /**
     * @param UserEntity $user
     * @return bool
     */
    public function loginAllowed(UserEntity $user)
    {
        switch ($user->getActivated()) {
            case UsersConstant::ACTIVATED_ACTIVE:
                return true;
            case UsersConstant::ACTIVATED_INACTIVE:
                $this->session->getFlashBag()->add('error', $this->translator->__('Login Denied: Your account has been disabled. Please contact a site administrator for more information.'));

                return false;
            case UsersConstant::ACTIVATED_PENDING_DELETE:
                $this->session->getFlashBag()->add('error', $this->translator->__('Login Denied: Your account has been disabled and is scheduled for removal. Please contact a site administrator for more information.'));

                return false;
            case UsersConstant::ACTIVATED_PENDING_REG:
                $this->session->getFlashBag()->add('error', $this->translator->__('Login Denied: Your request to register with this site is pending or awaiting verification.'));

                return false;
            default:
                $this->session->getFlashBag()->add('error', $this->translator->__('Login Denied!'));

                return false;
        }
    }

    /**
     * @param UserEntity $user
     * @param bool $rememberMe
     */
    public function login(UserEntity $user, $rememberMe = false)
    {
        $user->setLastlogin(new \DateTime());
        $this->userRepository->persistAndFlush($user);
        $lifetime = 0;
        if ($rememberMe && ZikulaSessionStorage::SECURITY_LEVEL_HIGH != $this->variableApi->getSystemVar('seclevel', ZikulaSessionStorage::SECURITY_LEVEL_MEDIUM)) {
            $lifetime = 2 * 365 * 24 * 60 * 60; // two years
        }
        $this->session->migrate(true, $lifetime);
        $this->session->set('uid', $user->getUid());
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
