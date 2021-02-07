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

namespace Zikula\Bundle\HookBundle\HookEvent;

abstract class HookEvent
{
    /**
     * ID of the hooked object
     * @var string
     */
    protected $id;

    /**
     * Intentionally casting the id as a string to allow for UUID or other non-integer types.
     */
    public function __construct(?string $id = null)
    {
        $this->id = $id;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    abstract public function getTitle(): string;

    abstract public function getInfo(): string;
}
