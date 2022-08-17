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

namespace Zikula\UsersModule\Helper;

use DateTime;
use Symfony\Component\HttpFoundation\RequestStack;
use Zikula\Bundle\CoreBundle\HttpFoundation\Session\ZikulaSessionStorage;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\UsersModule\Entity\RepositoryInterface\UserRepositoryInterface;
use Zikula\UsersModule\Entity\UserEntity;

class AccessHelper
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly UserRepositoryInterface $userRepository,
        private readonly PermissionApiInterface $permissionApi,
        private readonly VariableApiInterface $variableApi
    ) {
    }

    public function loginAllowed(UserEntity $user): bool
    {
        $request = $this->requestStack->getCurrentRequest();
        $session = null !== $request && $request->hasSession() ? $request->getSession() : null;

        switch ($user->getActivated()) {
            case UsersConstant::ACTIVATED_ACTIVE:
                return true;
            case UsersConstant::ACTIVATED_INACTIVE:
                if (null !== $session) {
                    $session->getFlashBag()->add('error', 'Login denied: Your account has been disabled. Please contact a site administrator for more information.');
                }

                return false;
            case UsersConstant::ACTIVATED_PENDING_DELETE:
                if (null !== $session) {
                    $session->getFlashBag()->add('error', 'Login denied: Your account has been disabled and is scheduled for removal. Please contact a site administrator for more information.');
                }

                return false;
            case UsersConstant::ACTIVATED_PENDING_REG:
                if (null !== $session) {
                    $session->getFlashBag()->add('info', 'Cannot login yet. Your request to register with this site is pending or awaiting verification.');
                }

                return false;
            default:
                if (null !== $session) {
                    $session->getFlashBag()->add('error', 'Login denied!');
                }
        }

        return false;
    }

    public function login(UserEntity $user, bool $rememberMe = false): void
    {
        $user->setLastlogin(new DateTime());
        $this->userRepository->persistAndFlush($user);
        $lifetime = 0;
        if ($rememberMe && ZikulaSessionStorage::SECURITY_LEVEL_HIGH !== $this->variableApi->getSystemVar('seclevel', ZikulaSessionStorage::SECURITY_LEVEL_MEDIUM)) {
            $lifetime = 2 * 365 * 24 * 60 * 60; // two years
        }
        $request = $this->requestStack->getCurrentRequest();
        if (null !== $request && $request->hasSession() && ($session = $request->getSession())) {
            $session->migrate(true, $lifetime);
            $session->set('uid', $user->getUid());
            if ($rememberMe) {
                $session->set('rememberme', 1);
            }
        }
        $this->permissionApi->resetPermissionsForUser($user->getUid());
    }

    public function logout(): bool
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null !== $request && $request->hasSession() && ($session = $request->getSession())) {
            $session->invalidate();
        }

        return true;
    }
}
