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

class HookCollector
{
    /**
     * @var HookProviderInterface[]
     * e.g. [<areaName> => <ServiceObject>]
     */
    protected $providerHooks = [];

    /**
     * @var HookSubscriberInterface[]
     * e.g. [<areaName> => <ServiceObject>]
     */
    protected $subscriberHooks = [];

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
        foreach ($service->getProviderTypes() as $type) {
            $existingInStorage = $this->getProviderByAreaAndType($areaName, $type);
            if (!empty($existingInStorage)) {
                throw new \InvalidArgumentException('Attempting to register a hook provider with a duplicate area name. (' . $areaName . ')');
            }
        }
        $this->providerHooks[$areaName] = $service;
        $this->providerHooks[$areaName]->setServiceId($serviceId);
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
     * Get a HookInterface from the collection by areaName.
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
        return $this->getAreasByOwner($this->providerHooks, $owner);
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
     * Get a HookInterface from the collection by areaName.
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
        return $this->getAreasByOwner($this->subscriberHooks, $owner);
    }

    /**
     * @param array $hooks
     * @param $owner
     * @return array of areas for one owner
     */
    private function getAreasByOwner(array $hooks, $owner)
    {
        $result = [];
        foreach ($hooks as $area => $hook) {
            if ($hook->getOwner() == $owner) {
                $result[] = $area;
            }
        }

        return $result;
    }
}
