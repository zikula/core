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
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\PermissionsModule\Api\PermissionApi;
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
     * @var PermissionApi
     */
    private $permissionApi;

    /**
     * @var VariableApi
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
     * @param PermissionApi $permissionApi
     * @param VariableApi $variableApi
     * @param TranslatorInterface $translator
     */
    public function __construct(
        Session $session,
        UserRepositoryInterface $userRepository,
        PermissionApi $permissionApi,
        VariableApi $variableApi,
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
        $siteOff = $this->variableApi->get(VariableApi::CONFIG, 'siteoff', false);

        switch ($user->getActivated()) {
            case UsersConstant::ACTIVATED_ACTIVE:
                if ($siteOff && !$this->permissionApi->hasPermission('::', '::', ACCESS_ADMIN)) {
                    return false;
                }

                return true;
            case UsersConstant::ACTIVATED_INACTIVE:
                $this->session->getFlashBag()->add('error', $this->translator->__('Your account has been disabled. Please contact a site administrator for more information.'));

                return false;
            case UsersConstant::ACTIVATED_PENDING_DELETE:
                $this->session->getFlashBag()->add('error', $this->translator->__('Your account has been disabled and is scheduled for removal. Please contact a site administrator for more information.'));

                return false;
            case UsersConstant::ACTIVATED_PENDING_REG:
                $this->session->getFlashBag()->add('error', $this->translator->__('Your request to register with this site is still waiting for approval from a site administrator.'));

                return false;
            default:
                $this->session->getFlashBag()->add('error', $this->translator->__('Nope'));

                return false;
        }
    }

    /**
     * @param UserEntity $user
     * @param string $method authentication method alias
     * @param bool $rememberMe
     */
    public function login(UserEntity $user, $method, $rememberMe = false)
    {
        $nowUTC = new \DateTime(null, new \DateTimeZone('UTC'));
        $user->setLastlogin($nowUTC);
        $this->userRepository->persistAndFlush($user);
        $this->session->clear();
        $this->session->start();
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
