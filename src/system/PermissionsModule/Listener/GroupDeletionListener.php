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

namespace Zikula\PermissionsModule\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Zikula\Bundle\CoreBundle\Event\GenericEvent;
use Zikula\GroupsModule\GroupEvents;
use Zikula\PermissionsModule\Entity\RepositoryInterface\PermissionRepositoryInterface;

class GroupDeletionListener implements EventSubscriberInterface
{
    /**
     * @var PermissionRepositoryInterface
     */
    private $permissionRepository;

    public function __construct(PermissionRepositoryInterface $permissionRepository)
    {
        $this->permissionRepository = $permissionRepository;
    }

    public static function getSubscribedEvents()
    {
        return [
            GroupEvents::GROUP_PRE_DELETE => ['preDelete', 5]
        ];
    }

    /**
     * Listener for the `group.pre_delete` event.
     *
     * Occurs before a group is deleted from the system. All handlers are notified.
     * The full group record to be deleted is available as the subject.
     *
     * You can access general data available in the event.
     *
     * The event name:
     *     `echo 'Event: ' . $event->getName();`
     *
     * @param GenericEvent $event The event instance
     */
    public function preDelete(GenericEvent $event): void
    {
        $group = $event->getSubject();

        $this->permissionRepository->deleteGroupPermissions($group->getGid());
    }
}
