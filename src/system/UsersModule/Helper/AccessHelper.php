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

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Core\Event\GenericEvent;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\PermissionsModule\Api\PermissionApi;
use Zikula\UsersModule\AccessEvents;
use Zikula\UsersModule\Constant as UsersConstant;
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
     * @var VariableApi
     */
    private $variableApi;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * AccessHelper constructor.
     * @param Session $session
     * @param UserRepository $userRepository
     * @param PermissionApi $permissionApi
     * @param VariableApi $variableApi
     * @param TranslatorInterface $translator
     */
    public function __construct(
        Session $session,
        UserRepository $userRepository,
        PermissionApi $permissionApi,
        VariableApi $variableApi,
        TranslatorInterface $translator,
        EventDispatcherInterface $eventDistpatcher
    ) {
        $this->session = $session;
        $this->userRepository = $userRepository;
        $this->permissionApi = $permissionApi;
        $this->variableApi = $variableApi;
        $this->translator = $translator;
        $this->eventDispatcher = $eventDistpatcher;
    }

    /**
     * @param UserEntity $user
     * @param string $method authentication method alias
     * @return bool
     */
    public function loginAllowed(UserEntity $user, $method)
    {
        $displayVerifyPending = $this->variableApi->get(UsersConstant::MODNAME, UsersConstant::MODVAR_LOGIN_DISPLAY_VERIFY_STATUS, UsersConstant::DEFAULT_LOGIN_DISPLAY_VERIFY_STATUS);
        $displayApprovalPending = $this->variableApi->get(UsersConstant::MODNAME, UsersConstant::MODVAR_LOGIN_DISPLAY_APPROVAL_STATUS, UsersConstant::DEFAULT_LOGIN_DISPLAY_VERIFY_STATUS);
        $moderationOrder = $this->variableApi->get(UsersConstant::MODNAME, UsersConstant::MODVAR_REGISTRATION_APPROVAL_SEQUENCE, UsersConstant::DEFAULT_REGISTRATION_APPROVAL_SEQUENCE);
        $siteOff = $this->variableApi->get(VariableApi::CONFIG, 'siteoff', false);

        switch ($user->getActivated()) {
            case UsersConstant::ACTIVATED_ACTIVE:
                $eventArgs = [
                    'authenticationMethod' => $method,
                    'uid' => $user->getUid(),
                ];
                $event = new GenericEvent($user, $eventArgs);
                $this->eventDispatcher->dispatch(AccessEvents::LOGIN_VETO, $event);
                if ($event->isPropagationStopped()) {
                    // @todo should this return something else from the event args?
                    return false;
                }
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
                if (!$user->isVerified()
                    && (($moderationOrder == UsersConstant::APPROVAL_AFTER) || ($moderationOrder == UsersConstant::APPROVAL_ANY)
                        || '' != $user->getApproved_By())
                    && $displayVerifyPending
                ) {
                    $this->session->getFlashBag()->add('error', $this->translator->__('Your request to register with this site is still waiting for verification of your e-mail address. Please check your inbox for a message from us.'));
                } elseif ((($moderationOrder == UsersConstant::APPROVAL_BEFORE) || ($moderationOrder == UsersConstant::APPROVAL_ANY))
                    && $displayApprovalPending
                    && '' == $user->getApproved_By()
                ) {
                    $this->session->getFlashBag()->add('error', $this->translator->__('Your request to register with this site is still waiting for approval from a site administrator.'));
                }

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
