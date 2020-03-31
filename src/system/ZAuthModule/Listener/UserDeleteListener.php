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
use Zikula\Bundle\CoreBundle\Event\GenericEvent;
use Zikula\UsersModule\Event\RegistrationPostDeletedEvent;
use Zikula\UsersModule\UserEvents;
use Zikula\ZAuthModule\Entity\RepositoryInterface\AuthenticationMappingRepositoryInterface;
use Zikula\ZAuthModule\Entity\RepositoryInterface\UserVerificationRepositoryInterface;

class UserDeleteListener implements EventSubscriberInterface
{
    /**
     * @var AuthenticationMappingRepositoryInterface
     */
    private $mappingRepository;

    /**
     * @var UserVerificationRepositoryInterface
     */
    private $verificationRepository;

    public function __construct(
        AuthenticationMappingRepositoryInterface $mappingRepository,
        UserVerificationRepositoryInterface $verificationRepository
    ) {
        $this->mappingRepository = $mappingRepository;
        $this->verificationRepository = $verificationRepository;
    }

    public static function getSubscribedEvents()
    {
        return [
            UserEvents::DELETE_ACCOUNT => [
                'deleteUser'
            ],
            RegistrationPostDeletedEvent::class => [
                'deleteRegistration'
            ]
        ];
    }

    public function deleteUser(GenericEvent $event): void
    {
        $deletedUid = (int) $event->getSubject();
        $this->mappingRepository->removeByZikulaId($deletedUid);
        $this->verificationRepository->removeByZikulaId($deletedUid);
    }

    public function deleteRegistration(RegistrationPostDeletedEvent $event): void
    {
        $this->mappingRepository->removeByZikulaId($event->getUser()->getUid());
        $this->verificationRepository->removeByZikulaId($event->getUser()->getUid());
    }
}
