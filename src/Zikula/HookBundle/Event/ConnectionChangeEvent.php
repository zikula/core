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

namespace Zikula\Zikula\Bundle\HookBundle\Event;

/**
 * This is a notification event only. Listeners cannot modify the event data.
 * $eventName and $listenerName will be provided in every case.
 */
class ConnectionChangeEvent
{
    /* @var string */
    private $eventName;

    /* @var string */
    private $listenerName;

    /* @var int|null */
    private $priority;

    /* @var string */
    private $action;

    public function __construct(string $eventName, string $listenerName, ?int $priority, string $action)
    {
        $this->eventName = $eventName;
        $this->listenerName = $listenerName;
        $this->priority = $priority;
        $this->action = $action;
    }

    public function getEventName(): string
    {
        return $this->eventName;
    }

    public function getListenerName(): string
    {
        return $this->listenerName;
    }

    public function getPriority(): ?int
    {
        return $this->priority;
    }

    public function getAction(): string
    {
        return $this->action;
    }
}
