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

namespace Zikula\ZAuthModule\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Zikula\UsersModule\Event\ActiveUserPostDeletedEvent;
use Zikula\UsersModule\Event\RegistrationPostDeletedEvent;
use Zikula\UsersModule\Event\UserEntityEvent;
use Zikula\ZAuthModule\Repository\AuthenticationMappingRepositoryInterface;
use Zikula\ZAuthModule\Repository\UserVerificationRepositoryInterface;

class UserDeleteListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly AuthenticationMappingRepositoryInterface $mappingRepository,
        private readonly UserVerificationRepositoryInterface $verificationRepository
    ) {
    }

    public static function getSubscribedEvents()
    {
        return [
            ActiveUserPostDeletedEvent::class => [
                'deleteUser'
            ],
            RegistrationPostDeletedEvent::class => [
                'deleteUser'
            ]
        ];
    }

    public function deleteUser(UserEntityEvent $event): void
    {
        $deletedUid = (int) $event->getUser()->getUid();
        $this->mappingRepository->removeByZikulaId($deletedUid);
        $this->verificationRepository->removeByZikulaId($deletedUid);
    }
}
