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
use Zikula\Bundle\HookBundle\HookEventListener\HookEventListenerInterface;
use Zikula\Bundle\HookBundle\Locator\HookLocator;
use Zikula\Bundle\HookBundle\Repository\HookConnectionRepository;

class HookEventListenerBuilderListener implements EventSubscriberInterface
{
    /**
     * @var HookLocator
     */
    private $hookLocator;

    /**
     * @var HookConnectionRepository
     */
    private $connectionRepository;

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
        HookConnectionRepository $connectionRepository,
        EventDispatcherInterface $eventDispatcher,
        string $installed
    ) {
        $this->hookLocator = $hookLocator;
        $this->connectionRepository = $connectionRepository;
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

        foreach ($this->connectionRepository->getAll() as $connection) {
            if ($this->hookLocator->isListener($connection->getListener())) {
                $listener = $this->hookLocator->getListener($connection->getListener());
                $callable = [$listener, HookEventListenerInterface::EXECUTE_METHOD];
                $this->eventDispatcher->addListener($connection->getEvent(), $callable, $connection->getPriority());
            }
        }
    }
}
