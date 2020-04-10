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

class Hook
{
    /**
     * Subscriber object id.
     *
     * @var integer
     */
    protected $id;

    /**
     * Subscriber area id.
     *
     * @var string
     */
    protected $areaId;

    /**
     * @var string
     */
    protected $caller;

    public function getCaller(): string
    {
        return $this->caller;
    }

    public function setCaller(string $caller): self
    {
        $this->caller = $caller;

        return $this;
    }

    public function getId()/*: int type hint currently disabled as UsersModule assigns a UserEntity for LoginUiHooksSubscriber::LOGIN_PROCESS */
    {
        return $this->id;
    }

    public function getAreaId(): ?string
    {
        return $this->areaId;
    }

    public function setAreaId(string $areaId): self
    {
        $this->areaId = $areaId;

        return $this;
    }
}
