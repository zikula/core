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

namespace Zikula\ZAuthBundle\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Zikula\UsersBundle\Event\ActiveUserPostDeletedEvent;
use Zikula\UsersBundle\Event\RegistrationPostDeletedEvent;
use Zikula\UsersBundle\Event\UserEntityEvent;
use Zikula\ZAuthBundle\Repository\AuthenticationMappingRepositoryInterface;
use Zikula\ZAuthBundle\Repository\UserVerificationRepositoryInterface;

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
