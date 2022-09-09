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

namespace Zikula\PermissionsBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Zikula\GroupsBundle\Event\GroupPreDeletedEvent;
use Zikula\PermissionsBundle\Repository\PermissionRepositoryInterface;

class GroupDeletionListener implements EventSubscriberInterface
{
    public function __construct(private readonly PermissionRepositoryInterface $permissionRepository)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            GroupPreDeletedEvent::class => ['preDelete', 5],
        ];
    }

    public function preDelete(GroupPreDeletedEvent $event): void
    {
        $this->permissionRepository->deleteGroupPermissions($event->getGroup()->getGid());
    }
}