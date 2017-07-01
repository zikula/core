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
use Zikula\Bundle\HookBundle\HookSubscriberInterface;
use Zikula\ExtensionsModule\Api\ApiInterface\CapabilityApiInterface;

class HookCollector
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
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * HookCollector constructor.
     * @deprecated
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    /**
     * Add a service to the collection.
     * @param string $areaName
     * @param $serviceId
     * @param HookProviderInterface $service
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
            if (!empty($existingInStorage)) {
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
    public function getProviderByAreaAndType($areaId, $type)
    {
        return $this->em->createQueryBuilder()->select('t')
            ->from(HookProviderEntity::class, 't')
            ->where('t.pareaid = ?1')
            ->andWhere('t.hooktype = ?2')
            ->getQuery()
            ->setParameter(1, $areaId)
            ->setParameter(2, $type)
            ->getArrayResult();
    }

    /**
     * Get a HookProviderInterface from the collection by areaName.
     * @param $areaName
     * @return HookProviderInterface|null
     */
    public function getProvider($areaName)
    {
        return isset($this->providerHooks[$areaName]) ? $this->providerHooks[$areaName] : null;
    }

    /**
     * @param $areaName
     * @return bool
     */
    public function hasProvider($areaName)
    {
        return isset($this->providerHooks[$areaName]);
    }

    /**
     * Get all the providers in the collection.
     * @return HookProviderInterface[]
     */
    public function getProviders()
    {
        return $this->providerHooks;
    }

    /**
     * @return array of all hook provider areas
     */
    public function getProviderAreas()
    {
        return array_keys($this->providerHooks);
    }

    /**
     * @param $owner
     * @return array of provider areas for one owner
     */
    public function getProviderAreasByOwner($owner)
    {
        return isset($this->providersByOwner[$owner]) ? array_keys($this->providersByOwner[$owner]) : [];
    }

    /**
     * Add a service to the collection.
     * @param string $areaName
     * @param HookSubscriberInterface $service
     */
    public function addSubscriber($areaName, HookSubscriberInterface $service)
    {
        if (isset($this->subscriberHooks[$areaName])) {
            throw new \InvalidArgumentException('Attempting to register a hook subscriber with a duplicate area name. (' . $areaName . ')');
        }
        // @deprecated
        foreach ($service->getEvents() as $eventName) {
            $existingSubscriber = $this->getSubscriberByEventName($eventName);
            if (!empty($existingSubscriber)) {
                throw new \InvalidArgumentException('Attempting to register a hook subscriber with a duplicate area name. (' . $areaName . ')');
            }
        }
        $this->subscriberHooks[$areaName] = $service;
        $this->subscribersByOwner[$service->getOwner()][$areaName] = $service;
    }

    /**
     * @deprecated
     */
    public function getSubscriberByEventName($eventName)
    {
        return $this->em->createQueryBuilder()->select('t')
            ->from(HookSubscriberEntity::class, 't')
            ->where('t.eventname = ?1')
            ->getQuery()->setParameter(1, $eventName)
            ->getArrayResult();
    }

    /**
     * Get a HookSubscriberInterface from the collection by areaName.
     * @param $areaName
     * @return HookSubscriberInterface|null
     */
    public function getSubscriber($areaName)
    {
        return isset($this->subscriberHooks[$areaName]) ? $this->subscriberHooks[$areaName] : null;
    }

    /**
     * @param $areaName
     * @return bool
     */
    public function hasSubscriber($areaName)
    {
        return isset($this->subscriberHooks[$areaName]);
    }

    /**
     * Get all the subscribers in the collection.
     * @return HookSubscriberInterface[]
     */
    public function getSubscribers()
    {
        return $this->subscriberHooks;
    }

    /**
     * @return array of all hook subscriber areas
     */
    public function getSubscriberAreas()
    {
        return array_keys($this->subscriberHooks);
    }

    /**
     * @param $owner
     * @return array of subscriber areas for one owner
     */
    public function getSubscriberAreasByOwner($owner)
    {
        return isset($this->subscribersByOwner[$owner]) ? array_keys($this->subscribersByOwner[$owner]) : [];
    }

    /**
     * Is moduleName capable of hook type?
     * @param $moduleName
     * @param string $type
     * @return bool
     */
    public function isCapable($moduleName, $type = CapabilityApiInterface::HOOK_SUBSCRIBER)
    {
        if (in_array($type, [CapabilityApiInterface::HOOK_SUBSCRIBER, CapabilityApiInterface::HOOK_PROVIDER])) {
            $variable = substr($type, 5) . 'sByOwner';
        } else {
            $variable = 'subscribersByOwner';
        }
        $array = $this->$variable;

        return isset($array[$moduleName]);
    }

    /**
     * Return all owners capable of hook type
     * @param string $type
     * @return array
     */
    public function getOwnersCapableOf($type = CapabilityApiInterface::HOOK_SUBSCRIBER)
    {
        if (in_array($type, [CapabilityApiInterface::HOOK_SUBSCRIBER, CapabilityApiInterface::HOOK_PROVIDER])) {
            $variable = substr($type, 5) . 'sByOwner';
        } else {
            $variable = 'subscribersByOwner';
        }
        $array = $this->$variable;

        return array_keys($array);
    }
}
