<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Helper;

use DateTime;
use Symfony\Component\HttpFoundation\RequestStack;
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
     * @var RequestStack
     */
    private $requestStack;

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

    public function __construct(
        RequestStack $requestStack,
        UserRepositoryInterface $userRepository,
        PermissionApiInterface $permissionApi,
        VariableApiInterface $variableApi,
        TranslatorInterface $translator
    ) {
        $this->requestStack = $requestStack;
        $this->userRepository = $userRepository;
        $this->permissionApi = $permissionApi;
        $this->variableApi = $variableApi;
        $this->translator = $translator;
    }

    public function loginAllowed(UserEntity $user): bool
    {
        $flashBag = null;
        $request = $this->requestStack->getCurrentRequest();
        if (null !== $request && $request->hasSession() && null !== $request->getSession()) {
            $flashBag = $request->getSession()->getFlashBag();
        }

        switch ($user->getActivated()) {
            case UsersConstant::ACTIVATED_ACTIVE:
                return true;
            case UsersConstant::ACTIVATED_INACTIVE:
                if (null !== $flashBag) {
                    $flashBag->add('error', $this->translator->__('Login Denied: Your account has been disabled. Please contact a site administrator for more information.'));
                }

                return false;
            case UsersConstant::ACTIVATED_PENDING_DELETE:
                if (null !== $flashBag) {
                    $flashBag->add('error', $this->translator->__('Login Denied: Your account has been disabled and is scheduled for removal. Please contact a site administrator for more information.'));
                }

                return false;
            case UsersConstant::ACTIVATED_PENDING_REG:
                if (null !== $flashBag) {
                    $flashBag->add('error', $this->translator->__('Login Denied: Your request to register with this site is pending or awaiting verification.'));
                }

                return false;
            default:
                if (null !== $flashBag) {
                    $flashBag->add('error', $this->translator->__('Login Denied!'));
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
        if (null !== $request && $request->hasSession() && null !== $request->getSession()) {
            $request->getSession()->migrate(true, $lifetime);
            $request->getSession()->set('uid', $user->getUid());
            if ($rememberMe) {
                $request->getSession()->set('rememberme', 1);
            }
        }
        $this->permissionApi->resetPermissionsForUser($user->getUid());
    }

    public function logout(): bool
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null !== $request && $request->hasSession() && null !== $request->getSession()) {
            $request->getSession()->invalidate();
        }

        return true;
    }
}
