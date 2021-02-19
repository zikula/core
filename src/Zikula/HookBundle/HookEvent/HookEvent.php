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
     * @var mixed
     */
    protected $id;

    final public function __construct()
    {
        // there should not be a constructor in HookEvents. Use setters.
    }

    /**
     * There is no argument casting here in order to allow for UUID or other non-integer types.
     */
    final public function setId($id = null): self
    {
        $this->id = $id;

        return $this;
    }

    final public function getId(): ?string
    {
        return $this->id;
    }

    final public function getClassname(): string
    {
        return static::class;
    }

    abstract public function getTitle(): string;

    abstract public function getInfo(): string;
}
