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
     * @var int
     */
    private $id;

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

    public function __construct(int $id, string $event, string $listener, int $priority = 0)
    {
        $this->id = $id;
        $this->event = $event;
        $this->listener = $listener;
        $this->priority = $priority;
    }

    public function getId(): int
    {
        return $this->id;
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
