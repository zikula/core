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

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\OrderBy;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Zikula\Bundle\HookBundle\Collector\HookCollector;
use Zikula\Bundle\HookBundle\Dispatcher\Exception\InvalidArgumentException;
use Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\RepositoryInterface\HookBindingRepositoryInterface;
use Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\RepositoryInterface\HookRuntimeRepositoryInterface;
use Zikula\Bundle\HookBundle\Dispatcher\StorageInterface;
use Zikula\Bundle\HookBundle\HookProviderInterface;
use Zikula\Bundle\HookBundle\HookSubscriberInterface;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;

/**
 * Doctrine class.
 */
class DoctrineStorage implements StorageInterface
{
    /**
     * @deprecated
     */
    use TranslatorTrait;

    /**
     * @deprecated
     */
    const PROVIDER = 'p';
    /**
     * @deprecated
     */
    const SUBSCRIBER = 's';

    /**
     * @var Entity\HookRuntimeEntity[]
     */
    private $runtimeHandlers = [];

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var HookBindingRepositoryInterface
     */
    private $hookBindingRepository;

    /**
     * @var HookRuntimeRepositoryInterface
     */
    private $hookRuntimeRepository;

    /**
     * @deprecated
     * @var SessionInterface
     */
    private $session;

    /**
     * @var HookCollector
     */
    private $hookCollector;

    public function __construct(
        EntityManagerInterface $em,
        HookBindingRepositoryInterface $hookBindingRepository,
        HookRuntimeRepositoryInterface $hookRuntimeRepository,
        SessionInterface $session, // @deprecated do not inject at Core-2.0
        TranslatorInterface $translator, // @deprecated do not inject at Core-2.0
        HookCollector $hookCollector
    ) {
        $this->em = $em;
        $this->hookBindingRepository = $hookBindingRepository;
        $this->hookRuntimeRepository = $hookRuntimeRepository;
        $this->session = $session; // @deprecated do not inject at Core-2.0
        $this->setTranslator($translator); // @deprecated do not inject at Core-2.0
        $this->hookCollector = $hookCollector;
    }

    /**
     * @deprecated
     * @param $translator
     */
    public function setTranslator($translator)
    {
        $this->translator = $translator;
    }

    /**
     * @deprecated
     */
    public function registerSubscriber($owner, $subOwner, $areaName, $areaType, $category, $eventName)
    {
        $this->registerArea($areaName, self::SUBSCRIBER, $owner, $subOwner, $category);

        // Now we have an areaId we can register a subscriber, but first test if the subscriber is already registered.
        $existingSubscriber = $this->getSubscriberByEventName($eventName);
        if (!empty($existingSubscriber)) {
            $this->session->getFlashBag()->add('warning', $this->__f('The hook subscriber "%sub" could not be registered for "%own" because it is registered already.', [
                '%sub' => $eventName,
                '%own' => $owner
            ]));

            return;
        }

        $subscriber = new Entity\HookSubscriberEntity();
        $subscriber->setOwner($owner);
        $subscriber->setCategory($category);
        $subscriber->setEventname($eventName);
        $subscriber->setHooktype($areaType);
        $subscriber->setSareaid($areaName);
        $subscriber->setSubowner($subOwner);
        $this->em->persist($subscriber);
        $this->em->flush();
    }

    /**
     * @deprecated
     */
    public function getSubscriberByEventName($eventName)
    {
        return $this->em->createQueryBuilder()->select('t')
                 ->from(Entity\HookSubscriberEntity::class, 't')
                 ->where('t.eventname = ?1')
                 ->getQuery()->setParameter(1, $eventName)
                 ->getArrayResult();
    }

    /**
     * @todo perhaps we move the `remove bindings` part to an uninstall listener - then whole method could be deleted...
     * @param $areaName
     */
    public function unregisterSubscriberByArea($areaName)
    {
        $areaId = $this->getAreaId($areaName); // @todo remove
        $bindingAreaNames = [$areaName]; // @todo remove and set parameter below to $areaName

        if ($areaId) {
            // @deprecated

            // delete subscriber entry
            $this->em->createQueryBuilder()
                ->delete(Entity\HookSubscriberEntity::class, 't')
                ->where('t.sareaid = ?1')
                ->getQuery()->setParameter(1, $areaName)
                ->execute();
            // clean areas
            $this->em->createQueryBuilder()
                ->delete(Entity\HookAreaEntity::class, 't')
                ->where('t.areaname = ?1')
                ->getQuery()->setParameter(1, $areaName)
                ->execute();
            $bindingAreaNames[] = $areaId;
        }

        // remove bindings
        $this->hookBindingRepository->deleteByAreaNames($bindingAreaNames, 'sareaid');

        $this->generateRuntimeHandlers();
    }

    /**
     * @deprecated
     */
    public function registerProvider($owner, $subOwner, $areaName, $hookType, $category, $className, $method, $serviceId = null)
    {
        $this->registerArea($areaName, self::PROVIDER, $owner, $subOwner, $category);

        $existingProvider = $this->getProviderByAreaAndType($areaName, $hookType);
        if (!empty($existingProvider)) {
            $this->session->getFlashBag()->add('warning', $this->__f('The hook provider for area "%parea" of type "%type" could not be registered for "%own" because it already exists.', [
                '%parea' => $areaName,
                '%type' => $hookType,
                '%own' => $owner
            ]));

            return;
        }

        $provider = new Entity\HookProviderEntity();
        $provider->setOwner($owner);
        $provider->setSubowner($subOwner);
        $provider->setPareaid($areaName);
        $provider->setHooktype($hookType);
        $provider->setCategory($category);
        $provider->setClassname($className);
        $provider->setMethod($method);
        $provider->setServiceid($serviceId);
        $this->em->persist($provider);
        $this->em->flush();
    }

    /**
     * @deprecated
     */
    public function getProviderByAreaAndType($areaId, $type)
    {
        return $this->em->createQueryBuilder()->select('t')
            ->from(Entity\HookProviderEntity::class, 't')
            ->where('t.pareaid = ?1')
            ->andWhere('t.hooktype = ?2')
            ->getQuery()
            ->setParameter(1, $areaId)
            ->setParameter(2, $type)
            ->getArrayResult();
    }

    /**
     * @todo perhaps we move the `remove bindings` part to an uninstall listener - then whole method could be deleted...
     * @param $areaName
     */
    public function unregisterProviderByArea($areaName)
    {
        $areaId = $this->getAreaId($areaName); // @todo remove
        $bindingAreaNames = [$areaName]; // @todo remove and set parameter below to $areaName

        if ($areaId) {
            // @deprecated

            // delete provider entry
            $this->em->createQueryBuilder()
                ->delete(Entity\HookProviderEntity::class, 't')
                ->where('t.pareaid = ?1')
                ->getQuery()->setParameter(1, $areaName)
                ->execute();
            // clean area
            $this->em->createQueryBuilder()
                ->delete(Entity\HookAreaEntity::class, 't')
                ->where('t.areaname = ?1')
                ->getQuery()->setParameter(1, $areaName)
                ->execute();
            $bindingAreaNames[] = $areaId;
        }

        // remove bindings
        $this->hookBindingRepository->deleteByAreaNames($bindingAreaNames, 'pareaid');

        $this->generateRuntimeHandlers();
    }

    /**
     * @deprecated
     */
    public function getSubscribersByOwner($owner)
    {
        return $this->em->createQueryBuilder()->select('t')
                    ->from(Entity\HookSubscriberEntity::class, 't')
                    ->where('t.owner = ?1')
                    ->getQuery()->setParameter(1, $owner)
                    ->getArrayResult();
    }

    /**
     * @deprecated
     */
    public function getSubscriberAreasByOwner($owner)
    {
        return $this->getAreasByOwner($owner, self::SUBSCRIBER);
    }

    /**
     * @deprecated
     */
    public function getProviderAreasByOwner($owner)
    {
        return $this->getAreasByOwner($owner, self::PROVIDER);
    }

    /**
     * @deprecated
     */
    private function getAreasByOwner($owner, $type)
    {
        $dql = "SELECT t.areaname
            FROM Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookAreaEntity t
            WHERE t.owner = :owner
            AND t.areatype = :type";
        $query = $this->em->createQuery($dql);
        $query->setParameter('owner', $owner);
        $query->setParameter('type', $type);
        $results = $query->getResult();
        // reformat result array to flat array
        $resultArray = [];
        foreach ($results as $k => $result) {
            $resultArray[$k] = $result['areaname'];
        }

        return $resultArray;
    }

    /**
     * @deprecated
     */
    public function getOwnerByArea($areaName)
    {
        $hookarea = $this->em->createQueryBuilder()->select('t')
                    ->from(Entity\HookAreaEntity::class, 't')
                    ->where('t.areaname = ?1')
                    ->getQuery()->setParameter(1, $areaName)
                    ->getSingleResult();

        return $hookarea->getOwner();
    }

    private function generateRuntimeHandlers()
    {
        // truncate runtime
        $this->hookRuntimeRepository->truncate();

        foreach ($this->getBindings() as $binding) {
            $this->addRuntimeHandlers($binding['sareaid'], $binding['pareaid']);
        }
    }

    private function addRuntimeHandlers($subscriberArea, $providerArea)
    {
        $subscriberAreaObject = $this->getByAreaName($subscriberArea);
        $providerAreaObject = $this->getByAreaName($providerArea, 'provider');

        if ($subscriberAreaObject instanceof HookSubscriberInterface) {
            $subscribers = $subscriberAreaObject->getEvents(); // @todo at Core-2.0 assume instance of HookSubscriberInterface
        } else {
            // @deprecated
            $subscribers = $this->em->createQueryBuilder()->select('t.hooktype', 't.eventname')
                ->from(Entity\HookSubscriberEntity::class, 't')
                ->where('t.sareaid = ?1')
                ->indexBy('t', 't.hooktype')
                ->getQuery()->setParameter(1, $subscriberArea)
                ->getResult();
            foreach ($subscribers as $k => $subscriber) {
                $subscribers[$k] = $subscriber['eventname'];
            }
        }

        if (!$subscribers) {
            return false;
        }

        foreach ($subscribers as $hookType => $eventName) {
            unset($method);
            if ($providerAreaObject instanceof HookProviderInterface) {
                // @todo at Core-2.0 refactor and assume instance of HookProviderInterface
                $types = $providerAreaObject->getProviderTypes();
                if (isset($types[$hookType])) {
                    $method = $types[$hookType];
                    $className = get_class($providerAreaObject);
                    $serviceId = $providerAreaObject->getServiceId();
                }
            } else {
                // @deprecated
                $provider = $this->em->createQueryBuilder()->select('t')
                    ->from(Entity\HookProviderEntity::class, 't')
                    ->where('t.pareaid = ?1 AND t.hooktype = ?2')
                    ->setParameters([1 => $providerArea, 2 => $hookType])
                    ->getQuery()
                    ->getArrayResult();
                if ($provider) {
                    $provider = $provider[0];
                    $method = $provider['method'];
                    $className = $provider['classname'];
                    $serviceId = $provider['serviceid'];
                }
            }

            if (isset($method)) {
                $binding = new Entity\HookRuntimeEntity();
                $binding->setSowner($subscriberAreaObject->getOwner());
                $binding->setPowner($providerAreaObject->getOwner());
                $binding->setSareaid($subscriberArea);
                $binding->setPareaid($providerArea);
                $binding->setEventname($eventName);
                $binding->setClassname($className);
                $binding->setMethod($method);
                $binding->setServiceid($serviceId);
                $binding->setPriority(10);
                $this->em->persist($binding);
            }
        }
        $this->em->flush();

        return true;
    }

    public function getRuntimeHandlers()
    {
        $this->runtimeHandlers = $this->hookRuntimeRepository->findAll();

        return $this->runtimeHandlers;
    }

    public function bindSubscriber($subscriberArea, $providerArea)
    {
        $sa = $this->getByAreaName($subscriberArea, 'subscriber');
        $pa = $this->getByAreaName($providerArea, 'provider');

        $binding = new Entity\HookBindingEntity();
        $binding->setSowner($sa->getOwner());
        $binding->setPowner($pa->getOwner());
        $binding->setSareaid($subscriberArea);
        $binding->setPareaid($providerArea);
        $binding->setCategory($sa->getCategory());
        $binding->setSortorder(999);
        $this->em->persist($binding);
        $this->em->flush();

        $this->generateRuntimeHandlers();
    }

    public function unbindSubscriber($subscriberArea, $providerArea)
    {
        $this->hookBindingRepository->deleteByBothAreas($subscriberArea, $providerArea);
        $this->generateRuntimeHandlers();
    }

    /**
     * @deprecated - this method should be eliminated and simply use the first part, assuming nonPersisted hook...
     * @param $areaName
     * @param string $type
     * @return null|object|Entity\HookAreaEntity
     */
    private function getByAreaName($areaName, $type = 'subscriber')
    {
        $getter = 'get' . ucfirst($type);
        $hasser = 'has' . ucfirst($type);
        if ($this->hookCollector->$hasser($areaName)) {
            $area = $this->hookCollector->$getter($areaName);
        } else {
            $area = $this->em->getRepository(Entity\HookAreaEntity::class)
                ->findOneBy(['areaname' => $areaName]);
        }

        return $area;
    }

    private function getBindings()
    {
        return $this->hookBindingRepository->findBy([], ['sareaid' => 'ASC', 'sortorder' => 'ASC']);
    }

    /**
     * @param $areaName
     * @param string $type
     * @return array
     */
    public function getBindingsFor($areaName, $type = 'subscriber')
    {
        $type = in_array($type, ['subscriber', 'provider']) ? $type : 'subscriber'; // validate field
        $area = $this->getByAreaName($areaName);

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
            $area = $this->getByAreaName($result['pareaid'], 'provider');
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
        if ($this->hookCollector->hasSubscriber($subscriberArea)) {
            $subscriberTypes = $this->hookCollector->getSubscriber($subscriberArea)->getEvents(); // array('hookType' => 'eventName')
            $subscriberTypes = array_keys($subscriberTypes);
            $subscriberCategory = $this->hookCollector->getSubscriber($subscriberArea)->getCategory();
        } else {
            // @deprecated
            $subscriberEntities =
                $this->em->getRepository(Entity\HookSubscriberEntity::class)
                    ->findBy(['sareaid' => $subscriberArea]);
            $subscriberTypes = [];
            foreach ($subscriberEntities as $hookSubscriberEntity) {
                $subscriberTypes[] = $hookSubscriberEntity->getHooktype();
                $subscriberCategory = $hookSubscriberEntity->getCategory(); // will all be same
            }
        }

        if (!$subscriberTypes) {
            return false;
        }

        foreach ($subscriberTypes as $subscriberType) {
            if ($this->hookCollector->hasProvider($providerArea)) {
                $providerTypes = $this->hookCollector->getProvider($providerArea)->getProviderTypes();
                $providerCategory = $this->hookCollector->getProvider($providerArea)->getCategory();
                foreach (array_keys($providerTypes) as $providerType) {
                    // @todo $providerType could be an array?
                    if ($subscriberCategory == $providerCategory && $subscriberType == $providerType) {
                        return true;
                    }
                }
            } else {
                // @deprecated
                $hookProvider =
                    $this->em->createQueryBuilder()->select('t')
                        ->from(Entity\HookProviderEntity::class, 't')
                        ->where('t.pareaid = ?1 AND t.hooktype = ?2 AND t.category = ?3')
                        ->setParameters([
                            1 => $providerArea,
                            2 => $subscriberType,
                            3 => $subscriberCategory
                        ])
                        ->getQuery()
                        ->getArrayResult();

                if ($hookProvider) {
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

    /**
     * @deprecated
     */
    private function registerArea($areaName, $areaType, $owner, $subOwner, $category)
    {
        if ($areaType !== self::PROVIDER && $areaType !== self::SUBSCRIBER) {
            throw new InvalidArgumentException('$areaType must be "p" or "s"');
        }

        // if there is an area registered, if not, create it.
        $areaId = $this->getAreaId($areaName);
        if (!$areaId) {
            // There is no area id so create one.
            $subscriberArea = new Entity\HookAreaEntity();
            $subscriberArea->setAreaname($areaName);
            $subscriberArea->setOwner($owner);
            $subscriberArea->setSubowner($subOwner);
            $subscriberArea->setAreatype($areaType);
            $subscriberArea->setCategory($category);
            $this->em->persist($subscriberArea);
            $this->em->flush();

            $areaId = $subscriberArea->getId();
        }

        return $areaId;
    }

    /**
     * @deprecated
     */
    private function getAreaId($areaName)
    {
        // return $areaName?
        $hookArea = $this->em->getRepository(Entity\HookAreaEntity::class)
                   ->findOneBy(['areaname' => $areaName]);

        if (!$hookArea) {
            return false;
        }

        return $hookArea->getId();
    }
}
