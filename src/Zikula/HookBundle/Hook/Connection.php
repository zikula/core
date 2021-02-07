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

namespace Zikula\Bundle\HookBundle\Hook;

class Connection
{
    /**
     * @var string
     */
    private $event;

    /**
     * @var string
     */
    private $listener;

    /**
     * @var int
     */
    private $priority;

    public function __construct(string $event, string $listener, int $priority = 0)
    {
        $this->event = $event;
        $this->listener = $listener;
        $this->priority = $priority;
    }

    public function getEvent(): string
    {
        return $this->event;
    }

    public function getListener(): string
    {
        return $this->listener;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }
}
