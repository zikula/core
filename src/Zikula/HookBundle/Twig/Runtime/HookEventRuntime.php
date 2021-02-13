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

namespace Zikula\Bundle\HookBundle\Twig\Runtime;

use Psr\EventDispatcher\EventDispatcherInterface;
use Twig\Extension\RuntimeExtensionInterface;
use Zikula\Bundle\HookBundle\Hook\Connection;
use Zikula\Bundle\HookBundle\HookEvent\FilterHookEvent;
use Zikula\Bundle\HookBundle\HookEvent\HookEvent;
use Zikula\Bundle\HookBundle\HookEventListener\HookEventListenerInterface;
use Zikula\Bundle\HookBundle\Repository\HookConnectionRepository;

class HookEventRuntime implements RuntimeExtensionInterface
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    private $hookConnectionRespository;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        HookConnectionRepository $hookConnectionRepository
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->hookConnectionRespository = $hookConnectionRepository;
    }

    public function dispatchFilterHookEvent(string $content, string $filterEventName): string
    {
        if (\class_exists($filterEventName) && \is_subclass_of($filterEventName, FilterHookEvent::class)) {
            $hook = $this->eventDispatcher->dispatch((new $filterEventName())->setData($content));

            return $hook->getData();
        }

        return $content;
    }

    public function getConnection(HookEvent $event, HookEventListenerInterface $listener): ?Connection
    {
        if (null !== $connection = $this->hookConnectionRespository->isConnected(get_class($event), get_class($listener))) {
            return $connection;
        }

        return null;
    }

    public function connectionEligibile(HookEvent $event, HookEventListenerInterface $listener): bool
    {
        return \is_subclass_of($event, $listener->listensTo());
    }
}
