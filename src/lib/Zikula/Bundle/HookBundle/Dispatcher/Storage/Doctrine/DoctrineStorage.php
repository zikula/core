<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine;

use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\Query\Expr\OrderBy;
use Doctrine\Persistence\ManagerRegistry;
use Zikula\Bundle\HookBundle\Collector\HookCollectorInterface;
use Zikula\Bundle\HookBundle\Dispatcher\Exception\InvalidArgumentException;
use Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookRuntimeEntity;
use Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\RepositoryInterface\HookBindingRepositoryInterface;
use Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\RepositoryInterface\HookRuntimeRepositoryInterface;
use Zikula\Bundle\HookBundle\Dispatcher\StorageInterface;

/**
 * Doctrine class.
 */
class DoctrineStorage implements StorageInterface
{
    /**
     * @var HookRuntimeEntity[]
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
        ManagerRegistry $doctrine,
        HookBindingRepositoryInterface $hookBindingRepository,
        HookRuntimeRepositoryInterface $hookRuntimeRepository,
        HookCollectorInterface $hookCollector
    ) {
        $this->objectManager = $doctrine->getManager();
        $this->hookBindingRepository = $hookBindingRepository;
        $this->hookRuntimeRepository = $hookRuntimeRepository;
        $this->hookCollector = $hookCollector;
    }

    private function generateRuntimeHandlers(): void
    {
        // truncate runtime
        $this->hookRuntimeRepository->truncate();

        $bindings = $this->hookBindingRepository->findBy([], ['sareaid' => 'ASC', 'sortorder' => 'ASC']);
        foreach ($bindings as $binding) {
            $this->addRuntimeHandlers($binding['sareaid'], $binding['pareaid']);
        }
    }

    private function addRuntimeHandlers(string $subscriberArea, string $providerArea): bool
    {
        $subscriberAreaObject = $this->hookCollector->getSubscriber($subscriberArea);
        $providerAreaObject = $this->hookCollector->getProvider($providerArea);
        if (null === $subscriberAreaObject) {
            throw new InvalidArgumentException('Invalid subscriber.');
        }
        if (null === $providerAreaObject) {
            throw new InvalidArgumentException('Invalid provider.');
        }

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
                    $hookRuntimeEntity->setPriority(10);
                    $this->objectManager->persist($hookRuntimeEntity);
                }
            }
        }
        $this->objectManager->flush();

        return true;
    }

    public function bindSubscriber(string $subscriberArea, string $providerArea): void
    {
        $subscriberAreaObject = $this->hookCollector->getSubscriber($subscriberArea);
        $providerAreaObject = $this->hookCollector->getProvider($providerArea);
        if (null === $subscriberAreaObject) {
            throw new InvalidArgumentException('Invalid subscriber.');
        }
        if (null === $providerAreaObject) {
            throw new InvalidArgumentException('Invalid provider.');
        }

        $binding = new Entity\HookBindingEntity();
        $binding->setSowner($subscriberAreaObject->getOwner());
        $binding->setPowner($providerAreaObject->getOwner());
        $binding->setSareaid($subscriberArea);
        $binding->setPareaid($providerArea);
        $binding->setCategory($subscriberAreaObject->getCategory());
        $binding->setSortorder(999);
        $this->objectManager->persist($binding);
        $this->objectManager->flush();

        $this->generateRuntimeHandlers();
    }

    public function unbindSubscriber(string $subscriberArea, string $providerArea): void
    {
        $this->hookBindingRepository->deleteByBothAreas($subscriberArea, $providerArea);
        $this->generateRuntimeHandlers();
    }

    public function getBindingsFor(string $areaName, string $type = 'subscriber'): array
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
     * Sort bindings in order of appearance from $providerAreaIds.
     */
    public function setBindOrder(string $subscriberAreaName, array $providerAreaNames): void
    {
        $counter = 1;
        foreach ($providerAreaNames as $providerAreaName) {
            $this->hookBindingRepository->setSortOrder($counter, $subscriberAreaName, $providerAreaName);
            $counter++;
        }

        $this->generateRuntimeHandlers();
    }

    public function getRuntimeMetaByEventName(string $eventName)
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

    public function getBindingBetweenAreas(string $subscriberArea, string $providerArea)
    {
        return $this->hookBindingRepository->findOneOrNullByAreas($subscriberArea, $providerArea);
    }

    /**
     * Binding between hook areas should be allowed if:
     *   1. *Category* is the same for both
     *   2. the provider and subscriber have implemented at least one of same *hookType*
     */
    public function isAllowedBindingBetweenAreas(string $subscriberArea, string $providerArea): bool
    {
        $subscriberTypes = [];
        $subscriberCategory = '';
        if ($this->hookCollector->hasSubscriber($subscriberArea)) {
            $subscriberAreaObject = $this->hookCollector->getSubscriber($subscriberArea);
            if (null !== $subscriberAreaObject) {
                $subscriberTypes = $subscriberAreaObject->getEvents(); // array('hookType' => 'eventName')
                $subscriberTypes = array_keys($subscriberTypes);
                $subscriberCategory = $subscriberAreaObject->getCategory();
            }
        }

        if (empty($subscriberTypes)) {
            return false;
        }

        foreach ($subscriberTypes as $subscriberType) {
            if (!$this->hookCollector->hasProvider($providerArea)) {
                continue;
            }
            $providerAreaObject = $this->hookCollector->getProvider($providerArea);
            if (null === $providerAreaObject) {
                continue;
            }

            $providerTypes = $providerAreaObject->getProviderTypes();
            $providerCategory = $providerAreaObject->getCategory();
            foreach (array_keys($providerTypes) as $providerType) {
                if ($subscriberCategory === $providerCategory && $subscriberType === $providerType) {
                    return true;
                }
            }
        }

        return false;
    }

    public function getBindingsBetweenOwners(string $subscriberOwner, string $providerOwner): array
    {
        return $this->hookBindingRepository->findByOwners($subscriberOwner, $providerOwner);
    }
}
