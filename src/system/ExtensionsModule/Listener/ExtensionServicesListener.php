<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ExtensionsModule\Listener;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\RouterInterface;
use Zikula\Core\Event\GenericEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class ExtensionServicesListener
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
        ];
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
        $event->setArgument('modfunc', [1 => 'getLinks']);
        $event->setArgument('api', true);

        // notify EVENT here to gather any system service links
        $args = ['modname' => $event->getArgument('modname')];
        $localevent = new \Zikula\Core\Event\GenericEvent($event->getSubject(), $args);
        $this->eventDispatcher->dispatch('module_dispatch.service_links', $localevent);
        $sublinks = $localevent->getData();

        if (!empty($sublinks)) {
            $event->data[] = [
                'url' => $this->router->generate('zikulaextensionsmodule_services_moduleservices', ['moduleName' => $event['modname']]),
                'text' => __('Services'),
                'icon' => 'cogs',
                'links' => $sublinks
            ];
        }
    }
}
