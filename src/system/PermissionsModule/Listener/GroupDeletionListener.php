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

namespace Zikula\PermissionsModule\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Zikula\GroupsModule\Event\GroupPreDeletedEvent;
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
            GroupPreDeletedEvent::class => ['preDelete', 5]
        ];
    }

    public function preDelete(GroupPreDeletedEvent $event): void
    {
        $this->permissionRepository->deleteGroupPermissions($event->getGroup()->getGid());
    }
}
