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

namespace Zikula\BlocksModule\Collectible;

/**
 * Class PendingContentCollectible
 */
class PendingContentCollectible
{
    /**
     * Pending item type.
     *
     * @var string
     */
    protected $type;

    /**
     * Pending item description.
     *
     * @var string
     */
    protected $description;

    /**
     * Number of pending items.
     *
     * @var int
     */
    protected $number;

    /**
     * Route id.
     *
     * @var string
     */
    protected $route;

    /**
     * Arguments for route.
     *
     * @var array
     */
    protected $args;

    public function __construct(string $type, string $description, int $number, string $route, array $args = [])
    {
        $this->type = $type;
        $this->description = $description;
        $this->number = $number;
        $this->route = $route;
        $this->args = $args;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getNumber(): int
    {
        return $this->number;
    }

    public function getRoute(): string
    {
        return $this->route;
    }

    public function getArgs(): array
    {
        return $this->args;
    }
}
