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

namespace Zikula\Bundle\HookBundle\Collector;

use InvalidArgumentException;
use function Symfony\Component\String\s;
use Zikula\Bundle\HookBundle\HookProviderInterface;
use Zikula\Bundle\HookBundle\HookSelfAllowedProviderInterface;
use Zikula\Bundle\HookBundle\HookSubscriberInterface;

/**
 * @deprecated remove at Core 4.0.0
 */
class HookCollector implements HookCollectorInterface
{
    /**
     * @var HookProviderInterface[]
     * e.g. [<areaName> => <serviceObject>]
     */
    private $providerHooks = [];

    /**
     * @var array
     * e.g. [<moduleName> => [<areaName> => <serviceObject>, <areaName> => <serviceObject>, ...]]
     */
    private $providersByOwner = [];

    /**
     * @var HookSubscriberInterface[]
     * e.g. [<areaName> => <serviceObject>]
     */
    private $subscriberHooks = [];

    /**
     * @var array
     * e.g. [<moduleName> => [<areaName> => <serviceObject>, <areaName> => <serviceObject>, ...]]
     */
    private $subscribersByOwner = [];

    /**
     * @param HookProviderInterface[] $providers
     * @param HookSubscriberInterface[] $subscribers
     */
    public function __construct(iterable $providers = [], iterable $subscribers = [])
    {
        foreach ($providers as $provider) {
            $this->addProvider($provider);
        }
        foreach ($subscribers as $subscriber) {
            $this->addSubscriber($subscriber);
        }
    }

    public function addProvider(HookProviderInterface $service): void
    {
        $areaName = $service->getAreaName();
        if (isset($this->providerHooks[$areaName])) {
            throw new InvalidArgumentException('Attempting to register a hook provider with a duplicate area name. (' . $areaName . ')');
        }
        $this->providerHooks[$areaName] = $service;
        $this->providersByOwner[$service->getOwner()][$areaName] = $service;
    }

    public function getProvider(string $areaName): ?HookProviderInterface
    {
        return $this->providerHooks[$areaName] ?? null;
    }

    public function hasProvider(string $areaName): bool
    {
        return isset($this->providerHooks[$areaName]);
    }

    public function getProviders(): iterable
    {
        return $this->providerHooks;
    }

    public function getProviderAreas(): array
    {
        return array_keys($this->providerHooks);
    }

    public function getProviderAreasByOwner(string $owner): array
    {
        return isset($this->providersByOwner[$owner]) ? array_keys($this->providersByOwner[$owner]) : [];
    }

    public function addSubscriber(HookSubscriberInterface $service): void
    {
        $areaName = $service->getAreaName();
        if (isset($this->subscriberHooks[$areaName])) {
            throw new InvalidArgumentException('Attempting to register a hook subscriber with a duplicate area name. (' . $areaName . ')');
        }
        $this->subscriberHooks[$areaName] = $service;
        $this->subscribersByOwner[$service->getOwner()][$areaName] = $service;
    }

    public function getSubscriber(string $areaName): ?HookSubscriberInterface
    {
        return $this->subscriberHooks[$areaName] ?? null;
    }

    public function hasSubscriber(string $areaName): bool
    {
        return isset($this->subscriberHooks[$areaName]);
    }

    public function getSubscribers(): iterable
    {
        return $this->subscriberHooks;
    }

    public function getSubscriberAreas(): array
    {
        return array_keys($this->subscriberHooks);
    }

    public function getSubscriberAreasByOwner(string $owner): array
    {
        return isset($this->subscribersByOwner[$owner]) ? array_keys($this->subscribersByOwner[$owner]) : [];
    }

    public function isCapable(string $moduleName, string $type = self::HOOK_SUBSCRIBER): bool
    {
        if (!in_array($type, [self::HOOK_SUBSCRIBER, self::HOOK_PROVIDER, self::HOOK_SUBSCRIBE_OWN], true)) {
            throw new InvalidArgumentException('Only hook_provider, hook_subscriber and subscriber_own are valid values.');
        }
        if (self::HOOK_SUBSCRIBE_OWN === $type) {
            return $this->containsSelfAllowedProvider($moduleName);
        }
        $variable = s($type)->slice(5)->append('sByOwner')->toString();
        $array = $this->{$variable};

        return isset($array[$moduleName]);
    }

    public function getOwnersCapableOf(string $type = self::HOOK_SUBSCRIBER): array
    {
        if (!in_array($type, [self::HOOK_SUBSCRIBER, self::HOOK_PROVIDER], true)) {
            throw new InvalidArgumentException('Only hook_provider and hook_subscriber are valid values.');
        }
        $variable = s($type)->slice(5)->append('sByOwner')->toString();
        $array = $this->{$variable};

        return array_keys($array);
    }

    /**
     * Does $moduleName contain at least one SelfAllowedProvider?
     */
    private function containsSelfAllowedProvider(string $moduleName): bool
    {
        if (!isset($this->providersByOwner[$moduleName])) {
            return false;
        }

        foreach ($this->providersByOwner[$moduleName] as $provider) {
            if ($provider instanceof HookSelfAllowedProviderInterface) {
                return true;
            }
        }

        return false;
    }
}
