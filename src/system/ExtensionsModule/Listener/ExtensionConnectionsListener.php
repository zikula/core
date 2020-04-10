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

namespace Zikula\ExtensionsModule\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\ExtensionsModule\Event\ConnectionsMenuEvent;
use Zikula\MenuModule\ExtensionMenu\ExtensionMenuEvent;
use Zikula\MenuModule\ExtensionMenu\ExtensionMenuInterface;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;

class ExtensionConnectionsListener implements EventSubscriberInterface
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var PermissionApiInterface
     */
    private $permissionApi;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        TranslatorInterface $translator,
        PermissionApiInterface $permissionApi
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->translator = $translator;
        $this->permissionApi = $permissionApi;
    }

    public static function getSubscribedEvents()
    {
        return [
            ExtensionMenuEvent::class => ['addConnectionsMenu', -256]
        ];
    }

    public function addConnectionsMenu(ExtensionMenuEvent $event): void
    {
        if (ExtensionMenuInterface::TYPE_ADMIN !== $event->getMenuType()) {
            return;
        }
        if (!$this->permissionApi->hasPermission($event->getBundleName() . '::', '::', ACCESS_ADMIN)) {
            return;
        }
        $event->getMenu()->addChild('connections')
            ->setLabel($this->translator->trans('Connections'))
            ->setAttribute('icon', 'fas fa-concierge-bell')
        ;
        $connectionsEvent = new ConnectionsMenuEvent($event->getMenu(), $event->getBundleName());
        $this->eventDispatcher->dispatch($connectionsEvent);
        if (!$event->getMenu()->getChild('connections')->hasChildren()) {
            $event->getMenu()->removeChild('connections');
        }
    }
}
