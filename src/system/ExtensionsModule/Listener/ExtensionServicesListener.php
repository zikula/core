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

namespace Zikula\ExtensionsModule\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Core\Event\GenericEvent;

class ExtensionServicesListener implements EventSubscriberInterface
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        RouterInterface $router,
        TranslatorInterface $translator
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->router = $router;
        $this->translator = $translator;
    }

    public static function getSubscribedEvents()
    {
        return [
            'zikula.link_collector' => 'linkCollectorResponder'
        ];
    }

    /**
     * Respond to zikula.link_collector events.
     *
     * Create a BC Layer for the zikula.link_collector event to gather Hook-related links.
     */
    public function linkCollectorResponder(GenericEvent $event): void
    {
        $event->setArgument('modname', $event->getSubject());
        $event->setArgument('modfunc', [1 => 'getLinks']);
        $event->setArgument('api', true);

        // notify EVENT here to gather any system service links
        $args = ['modname' => $event->getArgument('modname')];
        $localevent = new GenericEvent($event->getSubject(), $args);
        $this->eventDispatcher->dispatch($localevent, 'module_dispatch.service_links');
        $sublinks = $localevent->getData();

        if (!empty($sublinks)) {
            $event->data[] = [
                'url' => $this->router->generate('zikulaextensionsmodule_services_moduleservices', ['moduleName' => $event['modname']]),
                'text' => $this->translator->trans('Services'),
                'icon' => 'cogs',
                'links' => $sublinks
            ];
        }
    }
}
