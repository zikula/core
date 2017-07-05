<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\HookBundle\Collector;

use Doctrine\ORM\EntityManagerInterface;
use Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookProviderEntity;
use Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookSubscriberEntity;
use Zikula\Bundle\HookBundle\HookProviderInterface;
use Zikula\Bundle\HookBundle\HookSelfAllowedProviderInterface;
use Zikula\Bundle\HookBundle\HookSubscriberInterface;

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
     * @deprecated
     * @var EntityManagerInterface|null
     */
    private $em;

    /**
     * HookCollector constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager = null)
    {
        $this->em = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function addProvider($areaName, $serviceId, HookProviderInterface $service)
    {
        if (isset($this->providerHooks[$areaName])) {
            throw new \InvalidArgumentException('Attempting to register a hook provider with a duplicate area name. (' . $areaName . ')');
        }
        // @deprecated
        $providerTypes = $service->getProviderTypes();
        foreach (array_keys($providerTypes) as $type) {
            $existingInStorage = $this->getProviderByAreaAndType($areaName, $type);
            if (!empty($existingInStorage) && $existingInStorage[0]['owner'] != $service->getOwner()) {
                // assumes an owner would not mistakenly register same area names in order to allow module upgrade
                throw new \InvalidArgumentException('Attempting to register a hook provider with a duplicate area name. (' . $areaName . ')');
            }
        }
        $service->setServiceId($serviceId);
        $this->providerHooks[$areaName] = $service;
        $this->providersByOwner[$service->getOwner()][$areaName] = $service;
    }

    /**
     * @deprecated
     */
    private function getProviderByAreaAndType($areaId, $type)
    {
        return isset($this->em) ? $this->em->createQueryBuilder()->select('t')
            ->from(HookProviderEntity::class, 't')
            ->where('t.pareaid = ?1')
            ->andWhere('t.hooktype = ?2')
            ->getQuery()
            ->setParameter(1, $areaId)
            ->setParameter(2, $type)
            ->getArrayResult() : [];
    }

    /**
     * {@inheritdoc}
     */
    public function getProvider($areaName)
    {
        return isset($this->providerHooks[$areaName]) ? $this->providerHooks[$areaName] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function hasProvider($areaName)
    {
        return isset($this->providerHooks[$areaName]);
    }

    /**
     * {@inheritdoc}
     */
    public function getProviders()
    {
        return $this->providerHooks;
    }

    /**
     * {@inheritdoc}
     */
    public function getProviderAreas()
    {
        return array_keys($this->providerHooks);
    }

    /**
     * {@inheritdoc}
     */
    public function getProviderAreasByOwner($owner)
    {
        return isset($this->providersByOwner[$owner]) ? array_keys($this->providersByOwner[$owner]) : [];
    }

    /**
     * {@inheritdoc}
     */
    public function addSubscriber($areaName, HookSubscriberInterface $service)
    {
        if (isset($this->subscriberHooks[$areaName])) {
            throw new \InvalidArgumentException('Attempting to register a hook subscriber with a duplicate area name. (' . $areaName . ')');
        }
        // @deprecated
        foreach ($service->getEvents() as $eventName) {
            $existingSubscriber = $this->getSubscriberByEventName($eventName);
            if (!empty($existingSubscriber) && $existingSubscriber[0]['owner'] != $service->getOwner()) {
                // assumes an owner would not mistakenly register same area names in order to allow module upgrade
                throw new \InvalidArgumentException('Attempting to register a hook subscriber with a duplicate area name. (' . $areaName . ')');
            }
        }
        $this->subscriberHooks[$areaName] = $service;
        $this->subscribersByOwner[$service->getOwner()][$areaName] = $service;
    }

    /**
     * @deprecated
     */
    private function getSubscriberByEventName($eventName)
    {
        return isset($this->em) ? $this->em->createQueryBuilder()->select('t')
            ->from(HookSubscriberEntity::class, 't')
            ->where('t.eventname = ?1')
            ->getQuery()->setParameter(1, $eventName)
            ->getArrayResult() : [];
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscriber($areaName)
    {
        return isset($this->subscriberHooks[$areaName]) ? $this->subscriberHooks[$areaName] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function hasSubscriber($areaName)
    {
        return isset($this->subscriberHooks[$areaName]);
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribers()
    {
        return $this->subscriberHooks;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscriberAreas()
    {
        return array_keys($this->subscriberHooks);
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscriberAreasByOwner($owner)
    {
        return isset($this->subscribersByOwner[$owner]) ? array_keys($this->subscribersByOwner[$owner]) : [];
    }

    /**
     * {@inheritdoc}
     */
    public function isCapable($moduleName, $type = self::HOOK_SUBSCRIBER)
    {
        if (!in_array($type, [self::HOOK_SUBSCRIBER, self::HOOK_PROVIDER, self::HOOK_SUBSCRIBE_OWN])) {
            throw new \InvalidArgumentException('Only hook_provider, hook_subscriber and subscriber_own are valid values.');
        }
        if (self::HOOK_SUBSCRIBE_OWN == $type) {
            return $this->containsSelfAllowedProvider($moduleName);
        }
        $variable = substr($type, 5) . 'sByOwner';
        $array = $this->$variable;

        return isset($array[$moduleName]);
    }

    /**
     * {@inheritdoc}
     */
    public function getOwnersCapableOf($type = self::HOOK_SUBSCRIBER)
    {
        if (!in_array($type, [self::HOOK_SUBSCRIBER, self::HOOK_PROVIDER])) {
            throw new \InvalidArgumentException('Only hook_provider and hook_subscriber are valid values.');
        }
        $variable = substr($type, 5) . 'sByOwner';
        $array = $this->$variable;

        return array_keys($array);
    }

    /**
     * Does $moduleName contain at least one SelfAllowedProvider?
     * @param $moduleName
     * @return bool
     */
    private function containsSelfAllowedProvider($moduleName)
    {
        if (isset($this->providersByOwner[$moduleName])) {
            foreach ($this->providersByOwner[$moduleName] as $provider) {
                if ($provider instanceof HookSelfAllowedProviderInterface) {
                    return true;
                }
            }
        }

        return false;
    }
}
