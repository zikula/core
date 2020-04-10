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

namespace Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Traits;

/**
 * Hook entity trait.
 */
trait HookEntityTrait
{
    public function getId(): int
    {
        return $this->id;
    }

    public function setSowner(string $sowner): void
    {
        $this->sowner = $sowner;
    }

    public function getSowner(): string
    {
        return $this->sowner;
    }

    public function setPowner(string $powner): void
    {
        $this->powner = $powner;
    }

    public function getPowner(): string
    {
        return $this->powner;
    }

    public function setSareaid(string $subscriberAreaId): void
    {
        $this->sareaid = $subscriberAreaId;
    }

    public function getSareaid(): string
    {
        return $this->sareaid;
    }

    public function setPareaid(string $providerAreaId): void
    {
        $this->pareaid = $providerAreaId;
    }

    public function getPareaid(): string
    {
        return $this->pareaid;
    }
}
