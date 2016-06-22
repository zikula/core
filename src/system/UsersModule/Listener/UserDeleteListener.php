<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Zikula\Core\Event\GenericEvent;
use Zikula\UsersModule\Entity\RepositoryInterface\AuthenticationMappingRepositoryInterface;
use Zikula\UsersModule\RegistrationEvents;
use Zikula\UsersModule\UserEvents;

class UserDeleteListener implements EventSubscriberInterface
{
    /**
     * @var AuthenticationMappingRepositoryInterface
     */
    private $mappingRepository;

    /**
     * UserDeleteListener constructor.
     * @param $mappingRepository
     */
    public function __construct(AuthenticationMappingRepositoryInterface $mappingRepository)
    {
        $this->mappingRepository = $mappingRepository;
    }

    public function deleteUsers(GenericEvent $event)
    {
        $deletedUid = $event->getSubject();
        $this->mappingRepository->removeByZikulaId($deletedUid);
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
