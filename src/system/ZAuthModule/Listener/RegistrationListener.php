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

namespace Zikula\ZAuthModule\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Zikula\Core\Event\GenericEvent;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;
use Zikula\UsersModule\Api\ApiInterface\CurrentUserApiInterface;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\UsersModule\RegistrationEvents;
use Zikula\ZAuthModule\Entity\RepositoryInterface\AuthenticationMappingRepositoryInterface;
use Zikula\ZAuthModule\Helper\RegistrationVerificationHelper;
use Zikula\ZAuthModule\ZAuthConstant;

class RegistrationListener implements EventSubscriberInterface
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var CurrentUserApiInterface
     */
    private $currentUserApi;

    /**
     * @var PermissionApiInterface
     */
    private $permissionApi;

    /**
     * @var VariableApiInterface
     */
    private $variableApi;

    /**
     * @var AuthenticationMappingRepositoryInterface
     */
    private $mappingRepository;

    /**
     * @var RegistrationVerificationHelper
     */
    private $verificationHelper;

    public function __construct(
        RequestStack $requestStack,
        CurrentUserApiInterface $currentUserApi,
        PermissionApiInterface $permissionApi,
        VariableApiInterface $variableApi,
        AuthenticationMappingRepositoryInterface $mappingRepository,
        RegistrationVerificationHelper $registrationVerificationHelper
    ) {
        $this->requestStack = $requestStack;
        $this->currentUserApi = $currentUserApi;
        $this->permissionApi = $permissionApi;
        $this->variableApi = $variableApi;
        $this->mappingRepository = $mappingRepository;
        $this->verificationHelper = $registrationVerificationHelper;
    }

    public static function getSubscribedEvents()
    {
        return [
            RegistrationEvents::FULL_USER_CREATE_VETO => [
                'vetoFullUserCreate'
            ],
            RegistrationEvents::REGISTRATION_SUCCEEDED => [
                'sendEmailVerificationEmail'
            ],
            RegistrationEvents::FORCE_REGISTRATION_APPROVAL => [
                'forceRegistrationApproval'
            ]
        ];
    }

    public function vetoFullUserCreate(GenericEvent $event): void
    {
        $userEntity = $event->getSubject();
        if ($userEntity->getAttributes()->containsKey(UsersConstant::AUTHENTICATION_METHOD_ATTRIBUTE_KEY)) {
            $method = $userEntity->getAttributeValue(UsersConstant::AUTHENTICATION_METHOD_ATTRIBUTE_KEY);
            if (!in_array($method, [ZAuthConstant::AUTHENTICATION_METHOD_EMAIL, ZAuthConstant::AUTHENTICATION_METHOD_UNAME, ZAuthConstant::AUTHENTICATION_METHOD_EITHER], true)) {
                return;
            }
        }
        if (null !== $userEntity->getUid()) {
            $mapping = $this->mappingRepository->getByZikulaId($userEntity->getUid());
        }
        if (isset($mapping) && $mapping->isVerifiedEmail()) {
            return;
        }

        // user doesn't exist or email is not verified
        $isAdmin = $this->currentUserApi->isLoggedIn() && $this->permissionApi->hasPermission('ZikulaZAuthModule::', '::', ACCESS_EDIT);
        $request = $this->requestStack->getCurrentRequest();
        $userMustVerify = $this->variableApi->get('ZikulaZAuthModule', ZAuthConstant::MODVAR_EMAIL_VERIFICATION_REQUIRED, ZAuthConstant::DEFAULT_EMAIL_VERIFICATION_REQUIRED);
        if (null !== $request && $request->hasSession() && null !== $request->getSession()) {
            $userMustVerify = $request->hasSession()->has(ZAuthConstant::MODVAR_EMAIL_VERIFICATION_REQUIRED)
                ? 'Y' === $request->hasSession()->get(ZAuthConstant::MODVAR_EMAIL_VERIFICATION_REQUIRED)
                : $userMustVerify;
        }
        if ($userMustVerify && !$isAdmin) {
            $event->stopPropagation();
        }
    }

    public function sendEmailVerificationEmail(GenericEvent $event): void
    {
        $userEntity = $event->getSubject();
        if (null !== $userEntity->getUid()) {
            $mapping = $this->mappingRepository->getByZikulaId($userEntity->getUid());
            if (isset($mapping) && !$mapping->isVerifiedEmail()) {
                $this->verificationHelper->sendVerificationCode($mapping);
            }
        }
    }

    public function forceRegistrationApproval(GenericEvent $event): void
    {
        $userEntity = $event->getSubject();
        $this->mappingRepository->setEmailVerification($userEntity->getUid());
    }
}
