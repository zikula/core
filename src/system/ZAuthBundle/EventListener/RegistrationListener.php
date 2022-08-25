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

namespace Zikula\ZAuthBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Zikula\PermissionsBundle\Api\ApiInterface\PermissionApiInterface;
use Zikula\UsersBundle\Api\ApiInterface\CurrentUserApiInterface;
use Zikula\UsersBundle\Event\ActiveUserPreCreatedEvent;
use Zikula\UsersBundle\Event\RegistrationPostApprovedEvent;
use Zikula\UsersBundle\Event\RegistrationPostSuccessEvent;
use Zikula\UsersBundle\UsersConstant;
use Zikula\ZAuthBundle\Helper\RegistrationVerificationHelper;
use Zikula\ZAuthBundle\Repository\AuthenticationMappingRepositoryInterface;
use Zikula\ZAuthBundle\ZAuthConstant;

class RegistrationListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly CurrentUserApiInterface $currentUserApi,
        private readonly PermissionApiInterface $permissionApi,
        private readonly AuthenticationMappingRepositoryInterface $mappingRepository,
        private readonly RegistrationVerificationHelper $registrationVerificationHelper,
        private readonly bool $mailVerificationRequired
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ActiveUserPreCreatedEvent::class => ['vetoFullUserCreate'],
            RegistrationPostSuccessEvent::class => ['sendEmailVerificationEmail'],
            RegistrationPostApprovedEvent::class => ['forceRegistrationApproval'],
        ];
    }

    public function vetoFullUserCreate(ActiveUserPreCreatedEvent $event): void
    {
        $userEntity = $event->getUser();
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
        $userMustVerify = $this->mailVerificationRequired;
        if (null !== $request && $request->hasSession() && ($session = $request->getSession())) {
            $userMustVerify = $session->has(ZAuthConstant::SESSION_EMAIL_VERIFICATION_STATE)
                ? 'Y' === $session->get(ZAuthConstant::SESSION_EMAIL_VERIFICATION_STATE)
                : $userMustVerify;
        }
        if ($userMustVerify && !$isAdmin) {
            $event->stopPropagation();
        }
    }

    public function sendEmailVerificationEmail(RegistrationPostSuccessEvent $event): void
    {
        $userEntity = $event->getUser();
        if (null !== $userEntity->getUid()) {
            $mapping = $this->mappingRepository->getByZikulaId($userEntity->getUid());
            if (isset($mapping) && !$mapping->isVerifiedEmail()) {
                $this->verificationHelper->sendVerificationCode($mapping);
            }
        }
    }

    public function forceRegistrationApproval(RegistrationPostApprovedEvent $event): void
    {
        $this->mappingRepository->setEmailVerification($event->getUser()->getUid());
    }
}
