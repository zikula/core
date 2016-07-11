<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ZAuthModule\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Zikula\Core\Event\GenericEvent;
use Zikula\UsersModule\RegistrationEvents;
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

    /**
     * UserDeleteListener constructor.
     * @param AuthenticationMappingRepositoryInterface $mappingRepository
     * @param UserVerificationRepositoryInterface $verificationRepository
     */
    public function __construct(AuthenticationMappingRepositoryInterface $mappingRepository, UserVerificationRepositoryInterface $verificationRepository)
    {
        $this->mappingRepository = $mappingRepository;
        $this->verificationRepository = $verificationRepository;
    }

    public function deleteUsers(GenericEvent $event)
    {
        $deletedUid = $event->getSubject();
        $this->mappingRepository->removeByZikulaId($deletedUid);
        $this->verificationRepository->removeByZikulaId($deletedUid);
    }

    public static function getSubscribedEvents()
    {
        return [
            UserEvents::DELETE_ACCOUNT => [
                'deleteUsers'
            ],
            RegistrationEvents::DELETE_REGISTRATION => [
                'deleteUsers'
            ]
        ];
    }
}
