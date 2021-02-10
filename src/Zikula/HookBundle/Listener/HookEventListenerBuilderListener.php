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

namespace Zikula\Bundle\HookBundle\Listener;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Zikula\Bundle\HookBundle\Hook\Connection;
use Zikula\Bundle\HookBundle\Locator\HookLocator;

class HookEventListenerBuilderListener implements EventSubscriberInterface
{
    /**
     * @var HookLocator
     */
    private $hookLocator;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var bool
     */
    private $installed;

    public function __construct(
        HookLocator $hookLocator,
        EventDispatcherInterface $eventDispatcher,
        string $installed
    ) {
        $this->hookLocator = $hookLocator;
        $this->eventDispatcher = $eventDispatcher;
        $this->installed = '0.0.0' !== $installed;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [
                ['addHookEventListeners', 1000]
            ],
        ];
    }

    public function addHookEventListeners(RequestEvent $event): void
    {
        if (!$this->installed || !$event->isMasterRequest()) {
            return;
        }

        foreach ($this->getConnections() as $connection) {
            if ($this->hookLocator->isListener($connection->getListener())) {
                $listener = $this->hookLocator->getListener($connection->getListener());
                $callable = [$listener, 'execute'];
                $this->eventDispatcher->addListener($connection->getEvent(), $callable, $connection->getPriority());
            }
        }
    }

    /**
     * @todo remove in favor of Storage class
     */
    private function getConnections(): array
    {
        return [
            new Connection('App\\HookEvent\\AppDisplayHookEvent', 'App\\HookListener\\AppDisplayHookEventListener'),
            new Connection('App\\HookEvent\\AppFilterHookEvent', 'App\\HookListener\\AppFilterHookEventListener'),
            new Connection('App\\HookEvent\\AppPostValidationFormHookEvent', 'App\\HookListener\\AppPostValidationFormHookEventListener'),
            new Connection('App\\HookEvent\\AppPreHandleRequestFormHookEvent', 'App\\HookListener\\AppPreHandleRequestFormHookEventListener')
        ];
    }
}
