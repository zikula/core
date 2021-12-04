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

namespace Zikula\Bundle\HookBundle\Entity;

/**
 * @deprecated remove at Core 4.0.0
 */
trait HookEntityTrait
{
    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getSowner(): string
    {
        return $this->sowner;
    }

    public function setSowner(string $sowner): self
    {
        $this->sowner = $sowner;

        return $this;
    }

    public function getPowner(): string
    {
        return $this->powner;
    }

    public function setPowner(string $powner): self
    {
        $this->powner = $powner;

        return $this;
    }

    public function getSareaid(): string
    {
        return $this->sareaid;
    }

    public function setSareaid(string $subscriberAreaId): self
    {
        $this->sareaid = $subscriberAreaId;

        return $this;
    }

    public function getPareaid(): string
    {
        return $this->pareaid;
    }

    public function setPareaid(string $providerAreaId): self
    {
        $this->pareaid = $providerAreaId;

        return $this;
    }
}
