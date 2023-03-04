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

namespace Zikula\UsersBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

trait UserAttributesTrait
{
    
    private Collection $attributes;

    public function getAttributes(): Collection
    {
        return $this->attributes;
    }

    public function getAttributeValue(string $name): string
    {
        return $this->getAttributes()->offsetExists($name) ? $this->getAttributes()->get($name)->getValue() : '';
    }

    public function setAttributes(ArrayCollection $attributes): self
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * @param mixed $value
     */
    public function setAttribute(string $name, $value): self
    {
        if (isset($this->attributes[$name])) {
            $this->attributes[$name]->setValue($value);
        } else {
            $this->attributes[$name] = new UserAttribute($this, $name, $value);
        }

        return $this;
    }

    public function delAttribute(string $name): self
    {
        if (isset($this->attributes[$name])) {
            $this->attributes->remove($name);
        }

        return $this;
    }

    public function hasAttribute(string $name): bool
    {
        return $this->attributes->containsKey($name);
    }
}
