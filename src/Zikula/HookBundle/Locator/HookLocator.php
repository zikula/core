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

namespace Zikula\Bundle\HookBundle\Locator;

use Zikula\Bundle\HookBundle\HookEvent\HookEvent;
use Zikula\Bundle\HookBundle\HookEventListener\HookEventListenerInterface;

class HookLocator
{
    private $hookEvents = [];

    private $hookEventListeners = [];

    public function __construct(iterable $hookEvents = [], iterable $hookEventListeners = [])
    {
        foreach ($hookEvents as $hookEvent) {
            $this->addHookEvent($hookEvent);
        }
        foreach ($hookEventListeners as $hookEventListener) {
            $this->addHookEventListener($hookEventListener);
        }
    }

    private function addHookEvent(HookEvent $event)
    {
        $this->hookEvents[get_class($event)] = $event;
    }

    private function addHookEventListener(HookEventListenerInterface $eventListener)
    {
        $this->hookEventListeners[get_class($eventListener)] = $eventListener;
    }

    public function getHookEvents(): array
    {
        return $this->hookEvents;
    }

    public function getHookEventListeners(): array
    {
        return $this->hookEventListeners;
    }

    public function isListener(string $listenerClassname): bool
    {
        return isset($this->hookEventListeners[$listenerClassname]);
    }

    public function getListener(string $listenerClassname): ?HookEventListenerInterface
    {
        return $this->hookEventListeners[$listenerClassname];
    }
}
