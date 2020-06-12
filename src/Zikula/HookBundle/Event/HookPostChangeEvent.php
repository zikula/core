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

namespace Zikula\Bundle\HookBundle\Event;

/**
 * Hook post change event to allow reactions on binding changes.
 */
class HookPostChangeEvent
{
    /**
     * @var string
     */
    private $subscriberArea;

    /**
     * @var string
     */
    private $providerArea;

    /**
     * @var string
     */
    private $action;

    public function __construct(
        string $subscriberArea,
        string $providerArea,
        string $action
    ) {
        $this->subscriberArea = $subscriberArea;
        $this->providerArea = $providerArea;
        $this->action = $action;
    }

    public function getSubscriberArea(): string
    {
        return $this->subscriberArea;
    }

    public function getProviderArea(): string
    {
        return $this->providerArea;
    }

    public function getAction(): string
    {
        return $this->action;
    }
}
