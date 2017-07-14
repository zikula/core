<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Query\Expr\OrderBy;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Zikula\Bundle\HookBundle\Collector\HookCollectorInterface;
use Zikula\Bundle\HookBundle\Dispatcher\StorageInterface;
use Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\RepositoryInterface\HookBindingRepositoryInterface;
use Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\RepositoryInterface\HookRuntimeRepositoryInterface;

/**
 * Doctrine class.
 */
class DoctrineStorage implements StorageInterface
{
    /**
     * @var Entity\HookRuntimeEntity[]
     */
    private $runtimeHandlers = [];

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var HookBindingRepositoryInterface
     */
    private $hookBindingRepository;

    /**
     * @var HookRuntimeRepositoryInterface
     */
    private $hookRuntimeRepository;

    /**
     * @var HookCollectorInterface
     */
    private $hookCollector;

    public function __construct(
        RegistryInterface $doctrine,
        HookBindingRepositoryInterface $hookBindingRepository,
        HookRuntimeRepositoryInterface $hookRuntimeRepository,
        HookCollectorInterface $hookCollector
    ) {
        $this->objectManager = $doctrine->getManager();
        $this->hookBindingRepository = $hookBindingRepository;
        $this->hookRuntimeRepository = $hookRuntimeRepository;
        $this->hookCollector = $hookCollector;
    }

    private function generateRuntimeHandlers()
    {
        // truncate runtime
        $this->hookRuntimeRepository->truncate();

        $bindings = $this->hookBindingRepository->findBy([], ['sareaid' => 'ASC', 'sortorder' => 'ASC']);
        foreach ($bindings as $binding) {
            $this->addRuntimeHandlers($binding['sareaid'], $binding['pareaid']);
        }
    }

    private function addRuntimeHandlers($subscriberArea, $providerArea)
    {
        $subscriberAreaObject = $this->hookCollector->getSubscriber($subscriberArea);
        $providerAreaObject = $this->hookCollector->getProvider($providerArea);
        $subscribers = $subscriberAreaObject->getEvents();

        if (!$subscribers) {
            return false;
        }

        foreach ($subscribers as $hookType => $eventName) {
            $types = $providerAreaObject->getProviderTypes();
            if (isset($types[$hookType])) {
                $methods = is_array($types[$hookType]) ? $types[$hookType] : [$types[$hookType]];
                foreach ($methods as $method) {
                    $hookRuntimeEntity = new Entity\HookRuntimeEntity();
                    $hookRuntimeEntity->setSowner($subscriberAreaObject->getOwner());
                    $hookRuntimeEntity->setPowner($providerAreaObject->getOwner());
                    $hookRuntimeEntity->setSareaid($subscriberArea);
                    $hookRuntimeEntity->setPareaid($providerArea);
                    $hookRuntimeEntity->setEventname($eventName);
                    $hookRuntimeEntity->setClassname(get_class($providerAreaObject));
                    $hookRuntimeEntity->setMethod($method);
                    $hookRuntimeEntity->setServiceid($providerAreaObject->getServiceId());
                    $hookRuntimeEntity->setPriority(10);
                    $this->objectManager->persist($hookRuntimeEntity);
                }
            }
        }
        $this->objectManager->flush();

        return true;
    }

    public function bindSubscriber($subscriberArea, $providerArea)
    {
        $sa = $this->hookCollector->getSubscriber($subscriberArea);
        $pa = $this->hookCollector->getProvider($providerArea);

        $binding = new Entity\HookBindingEntity();
        $binding->setSowner($sa->getOwner());
        $binding->setPowner($pa->getOwner());
        $binding->setSareaid($subscriberArea);
        $binding->setPareaid($providerArea);
        $binding->setCategory($sa->getCategory());
        $binding->setSortorder(999);
        $this->objectManager->persist($binding);
        $this->objectManager->flush();

        $this->generateRuntimeHandlers();
    }

    public function unbindSubscriber($subscriberArea, $providerArea)
    {
        $this->hookBindingRepository->deleteByBothAreas($subscriberArea, $providerArea);
        $this->generateRuntimeHandlers();
    }

    /**
     * @param $areaName
     * @param string $type
     * @return array
     */
    public function getBindingsFor($areaName, $type = 'subscriber')
    {
        $type = in_array($type, ['subscriber', 'provider']) ? $type : 'subscriber'; // validate field
        $area = $this->hookCollector->getSubscriber($areaName);

        if (!isset($area)) {
            return [];
        }

        $order = new OrderBy();
        $order->add('t.sortorder', 'ASC');
        $order->add('t.sareaid', 'ASC');
        $fieldMap = ['subscriber' => 'sareaid', 'provider' => 'pareaid'];
        $results = $this->hookBindingRepository->selectByAreaName($areaName, $fieldMap[$type]);

        $areas = [];
        foreach ($results as $result) {
            $area = $this->hookCollector->getProvider($result['pareaid']);
            $areas[] = [
                'areaname' => $result['pareaid'],
                'category' => $area->getCategory()
            ];
        }

        return $areas;
    }

    /**
     * sort bindings in order of appearance from $providerAreaIds
     * @param string $subscriberAreaName
     * @param array $providerAreaNames
     */
    public function setBindOrder($subscriberAreaName, array $providerAreaNames)
    {
        $counter = 1;
        foreach ($providerAreaNames as $providerAreaName) {
            $this->hookBindingRepository->setSortOrder($counter, $subscriberAreaName, $providerAreaName);
            $counter++;
        }

        $this->generateRuntimeHandlers();
    }

    public function getRuntimeMetaByEventName($eventName)
    {
        if (!isset($this->runtimeHandlers[$eventName])) {
            $this->runtimeHandlers[$eventName] = $this->hookRuntimeRepository->getOneOrNullByEventName($eventName);
        }
        if ($this->runtimeHandlers[$eventName]) {
            return [
                'areaid' => $this->runtimeHandlers[$eventName]->getSareaid(),
                'owner' => $this->runtimeHandlers[$eventName]->getSowner()
            ];
        }

        return false;
    }

    public function getBindingBetweenAreas($subscriberArea, $providerArea)
    {
        return $this->hookBindingRepository->findOneOrNullByAreas($subscriberArea, $providerArea);
    }

    /**
     * binding between hook areas should be allowed if:
     *   1. *Category* is the same for both
     *   2. the provider and subscriber have implemented at least one of same *hookType*
     * @param $subscriberArea
     * @param $providerArea
     * @return bool
     */
    public function isAllowedBindingBetweenAreas($subscriberArea, $providerArea)
    {
        $subscriberTypes = [];
        $subscriberCategory = '';
        if ($this->hookCollector->hasSubscriber($subscriberArea)) {
            $subscriberTypes = $this->hookCollector->getSubscriber($subscriberArea)->getEvents(); // array('hookType' => 'eventName')
            $subscriberTypes = array_keys($subscriberTypes);
            $subscriberCategory = $this->hookCollector->getSubscriber($subscriberArea)->getCategory();
        }

        if (empty($subscriberTypes)) {
            return false;
        }

        foreach ($subscriberTypes as $subscriberType) {
            if (!$this->hookCollector->hasProvider($providerArea)) {
                continue;
            }

            $providerTypes = $this->hookCollector->getProvider($providerArea)->getProviderTypes();
            $providerCategory = $this->hookCollector->getProvider($providerArea)->getCategory();
            foreach (array_keys($providerTypes) as $providerType) {
                if ($subscriberCategory == $providerCategory && $subscriberType == $providerType) {
                    return true;
                }
            }
        }

        return false;
    }

    public function getBindingsBetweenOwners($subscriberOwner, $providerOwner)
    {
        return $this->hookBindingRepository->findByOwners($subscriberOwner, $providerOwner);
    }
}
