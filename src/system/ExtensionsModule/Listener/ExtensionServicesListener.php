<?php
/**
 * Copyright 2016 Zikula Foundation
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula_View
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\ExtensionsModule\Listener;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\RouterInterface;
use Zikula\Core\Event\GenericEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class ExtensionServicesListener
 * @package Zikula\ExtensionsModule\Listener
 */
class ExtensionServicesListener implements EventSubscriberInterface
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;
    private $router;

    /**
     * ExtensionServicesListener constructor.
     * @param $eventDispatcher
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, RouterInterface $router)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->router = $router;
    }

    public static function getSubscribedEvents()
    {
        return [
            'zikula.link_collector' => 'linkCollectorResponder',
            'module_dispatch.postexecute' => 'addServiceLink' // deprecated event
        ];
    }

    /**
     * Dynamically add menu links to administration for system services.
     *
     * Listens for 'module_dispatch.postexecute' events.
     * @deprecated remove at Core-2.0
     * move logic to linkCollectorResponder
     *
     * @param \Zikula_Event $event The event handler.
     *
     * @return void
     */
    public function addServiceLink(\Zikula_Event $event)
    {
        // check if this is for this handler
        if (!($event['modfunc'][1] == 'getLinks' && $event['type'] == 'admin' && $event['api'] == true)) {
            return;
        }

        // notify EVENT here to gather any system service links
        $args = array('modname' => $event->getArgument('modname'));
        $localevent = new \Zikula\Core\Event\GenericEvent($event->getSubject(), $args);
        $this->eventDispatcher->dispatch('module_dispatch.service_links', $localevent);
        $sublinks = $localevent->getData();

        if (!empty($sublinks)) {
            $event->data[] = array(
                'url' => $this->router->generate('zikulaextensionsmodule_services_moduleservices', array('moduleName' => $event['modname'])),
                'text' => __('Services'),
                'icon' => 'cogs',
                'links' => $sublinks);
        }
    }

    /**
     * Respond to zikula.link_collector events.
     *
     * Create a BC Layer for the zikula.link_collector event to gather Hook-related links.
     *
     * @param GenericEvent $event
     */
    public function linkCollectorResponder(GenericEvent $event)
    {
        $event->setArgument('modname', $event->getSubject());
        $event->setArgument('modfunc', array(1 => 'getLinks'));
        $event->setArgument('api', true);
        $this->addServiceLink($event);
    }
}
