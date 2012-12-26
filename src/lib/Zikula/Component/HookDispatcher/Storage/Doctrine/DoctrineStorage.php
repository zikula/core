<?php
/**
 * Copyright 2010 Zikula Foundation
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package HookDispatcher
 * @subpackage Storage
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Component\HookDispatcher\Storage\Doctrine;

use Zikula\Component\HookDispatcher\StorageInterface;
use Zikula\Component\HookDispatcher\Exception\InvalidArgumentException;
use\Doctrine\ORM\EntityManager;

/**
 * Doctrine class.
 */
class DoctrineStorage implements StorageInterface
{
    const PROVIDER = 'p';
    const SUBSCRIBER = 's';

    private $runtimeHandlers = array();

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }


    public function registerSubscriber($owner, $subOwner, $areaName, $areaType, $category, $eventName)
    {
        $areaId = $this->registerArea($areaName, self::SUBSCRIBER, $owner, $subOwner, $category);

        // Now we have an areaId we can register a subscriber
        $subscriber = new Entity\HookSubscriberEntity();
        $subscriber->setOwner($owner);
        $subscriber->setCategory($category);
        $subscriber->setEventname($eventName);
        $subscriber->setHooktype($areaType);
        $subscriber->setSareaid($areaId);
        $subscriber->setSubowner($subOwner);
        $this->em->persist($subscriber);
        $this->em->flush();
    }

    public function getSubscriberByEventName($eventName)
    {
        return $this->em->createQueryBuilder()->select('t')
                 ->from('Zikula\Component\HookDispatcher\Storage\Doctrine\Entity\HookSubscriberEntity', 't')
                 ->where('t.eventname = ?')
                 ->getQuery()->setParameter(1, $eventName)
                 ->getArrayResult();
    }

    public function unregisterSubscriberByArea($areaName)
    {
        $areaId = $this->getAreaId($areaName);

        if (!$areaId) {
            return;
        }

        // delete subscriber entry
        $this->em->createQueryBuilder()
                 ->delete('Zikula\Component\HookDispatcher\Storage\Doctrine\Entity\HookSubscriberEntity')
                 ->where('sareaid = ?')
                 ->getQuery()->setParameter(1, $areaId)
                 ->execute();

        // remove bindings
        $this->em->createQueryBuilder()
                 ->delete('Zikula\Component\HookDispatcher\Storage\Doctrine\Entity\HookBindingEntity')
                 ->where('sareaid = ?')
                 ->getQuery()->setParameter(1, $areaId)
                 ->execute();

        // clean areas
        $this->em->createQueryBuilder()
                 ->delete('Zikula\Component\HookDispatcher\Storage\Doctrine\Entity\HookAreaEntity')
                 ->where('id = ?')
                 ->getQuery()->setParameter(1, $areaId)
                 ->execute();

        $this->generateRuntimeHandlers();
    }

    public function registerProvider($owner, $subOwner, $areaName, $hookType, $category, $className, $method, $serviceId=null)
    {
        $pareaId = $this->registerArea($areaName, self::PROVIDER, $owner, $subOwner, $category);

        $provider = new Entity\HookProviderEntity();
        $provider->setOwner($owner);
        $provider->setSubowner($subOwner);
        $provider->setPareaid($pareaId);
        $provider->setHooktype($hookType);
        $provider->setCategory($category);
        $provider->setClassname($className);
        $provider->setMethod($method);
        $provider->setServiceid($serviceId);
        $this->em->persist($provider);
        $this->em->flush();
    }

    public function unregisterProviderByArea($areaName)
    {
        $areaId = $this->getAreaId($areaName);

        if (!$areaId) {
            return;
        }

        // delete provider entry
        $this->em->createQueryBuilder()
                 ->delete('Zikula\Component\HookDispatcher\Storage\Doctrine\Entity\HookProviderEntity')
                 ->where('pareaid = ?')
                 ->getQuery()->setParameter(1, $areaId)
                 ->execute();

        // remove bindings
        $this->em->createQueryBuilder()
                 ->delete('Zikula\Component\HookDispatcher\Storage\Doctrine\Entity\HookBindingEntity')
                 ->where('pareaid = ?')
                 ->getQuery()->setParameter(1, $areaId)
                 ->execute();

        // clean area
        $this->em->createQueryBuilder()
                 ->delete('Zikula\Component\HookDispatcher\Storage\Doctrine\Entity\HookAreaEntity')
                 ->where('id = ?')
                 ->getQuery()->setParameter(1, $areaId)
                 ->execute();

        $this->generateRuntimeHandlers();
    }

    public function getSubscribersByOwner($owner)
    {
        return $this->em->createQueryBuilder()->select('t')
                    ->from('Zikula\Component\HookDispatcher\Storage\Doctrine\Entity\HookSubscriberEntity', 't')
                    ->where('t.owner = ?')
                    ->getQuery()->setParameter(1, $owner)
                    ->getArrayResult();
    }

    public function getSubscriberAreasByOwner($owner)
    {
        return (array) $this->em->createQueryBuilder()
                            ->select('DISTINCT t.areaname')->select('t')
                            ->from('Zikula\Component\HookDispatcher\Storage\Doctrine\Entity\HookAreaEntity', 't')
                            ->where('t.owner = ? AND t.areatype = ?')
                            ->getQuery()->setParameters(array(1 => $owner, 2 => self::SUBSCRIBER))
                            ->getSingleScalarResult();
    }

    public function getProviderAreasByOwner($owner)
    {
        return (array) $this->em->createQueryBuilder()
                            ->select('DISTINCT t.areaname')->select('t')
                            ->from('Zikula\Component\HookDispatcher\Storage\Doctrine\Entity\HookAreaEntity', 't')
                            ->where('t.owner = ? AND t.areatype = ?')
                            ->getQuery()->setParameters(array(1 => $owner, 2 => self::PROVIDER))
                            ->getSingleScalarResult();
    }

    public function getOwnerByArea($areaName)
    {
        return $this->em->createQueryBuilder()->select('t')
                    ->from('Zikula\Component\HookDispatcher\Storage\Doctrine\Entity\HookAreaEntity', 't')
                    ->where('areaname = ?')
                    ->getQuery()->setParameter(1, $areaName)
                    ->getSingleScalarResult();
    }

    private function generateRuntimeHandlers()
    {
        // truncate runtime
        $this->em->createQueryBuilder()
             ->delete('Zikula\Component\HookDispatcher\Storage\Doctrine\Entity\HookRuntimeEntity')
             ->getQuery()
             ->execute();

        foreach ($this->getBindings() as $binding) {
            $this->addRuntimeHandlers($binding['sareaid'], $binding['pareaid']);
        }
    }

    private function addRuntimeHandlers($subscriberAreaId, $providerAreaId)
    {
        $sa = $this->em->find('Zikula\Component\HookDispatcher\Storage\Doctrine\Entity\HookAreaEntity',
                              $subscriberAreaId);
        $pa = $this->em->find('Zikula\Component\HookDispatcher\Storage\Doctrine\Entity\HookAreaEntity',
                              $providerAreaId);

        $subscribers = $this->em->createQueryBuilder()->select('t')
                            ->from('Zikula\Component\HookDispatcher\Storage\Doctrine\Entity\HookSubscriberEntity', 't')
                            ->where('t.sareaid = ?')
                            ->getQuery()->setParameter(1, $subscriberAreaId)
                            ->getArrayResult();

        if (!$subscribers) {
            return false;
        }

        foreach ($subscribers as $subscriber) {
            $provider = $this->em->createQueryBuilder()->select('t')
                             ->from('Zikula\Component\HookDispatcher\Storage\Doctrine\Entity\HookProviderEntity', 't')
                             ->where('t.pareaid = ? AND t.hooktype = ?')
                             ->getQuery()->setParameters(array(1 => $providerAreaId, 2 => $subscriber['hooktype']))
                             ->getArrayResult();

            if ($provider) {
                $provider = $provider[0];
                $binding = new Entity\HookRuntimeEntity();
                $binding->setSowner($sa->getOwner());
                $binding->setSubsowner($sa->getSubowner());
                $binding->setPowner($pa->getOwner());
                $binding->setSubpowner($pa->getSubowner());
                $binding->setSareaid($subscriberAreaId);
                $binding->setPareaid($providerAreaId);
                $binding->setEventname($subscriber['eventname']);
                $binding->setClassname($provider['classname']);
                $binding->setMethod($provider['method']);
                $binding->setServiceid($provider['serviceid']);
                $binding->setPriority(10);
                $this->em->persist($binding);
                $this->em->flush();
            }
        }

        return true;
    }

    public function getRuntimeHandlers()
    {
        $this->runtimeHandlers =
            $this->em->createQueryBuilder()->select('t')
                 ->from('Zikula\Component\HookDispatcher\Storage\Doctrine\Entity\HookRuntimeEntity', 't')
                 ->getQuery()
                 ->getArrayResult();

        return $this->runtimeHandlers;
    }

    public function bindSubscriber($subscriberArea, $providerArea)
    {
        $sa = $this->em->getRepository('Zikula\Component\HookDispatcher\Storage\Doctrine\Entity\HookAreaEntity')
                   ->findOneByAreaname($subscriberArea);
        $pa = $this->em->getRepository('Zikula\Component\HookDispatcher\Storage\Doctrine\Entity\HookAreaEntity')
                   ->findOneByAreaname($providerArea);

        if ($sa->getCategory() != $pa->getCategory()) {
            throw new \LogicException('Cannot bind areas from different categories.');
        }

        $binding = new Entity\HookBindingEntity();
        $binding->setSowner($sa->getOwner());
        $binding->setSubsowner($sa->getSubowner());
        $binding->setPowner($pa->getOwner());
        $binding->setSubpowner($pa->getSubowner());
        $binding->setSareaid($sa->getId());
        $binding->setPareaid($pa->getId());
        $binding->setCategory($sa->getCategory());
        $binding->setSortorder(999);
        $this->em->persist($binding);
        $this->em->flush();

        $this->generateRuntimeHandlers();
    }

    public function unbindSubscriber($subscriberArea, $providerArea)
    {
        $sa = $this->em->getRepository('Zikula\Component\HookDispatcher\Storage\Doctrine\Entity\HookAreaEntity')
                   ->findOneByAreaname($subscriberArea);
        $pa = $this->em->getRepository('Zikula\Component\HookDispatcher\Storage\Doctrine\Entity\HookAreaEntity')
                   ->findOneByAreaname($providerArea);

        $subscriberAreaId = $sa->getId();
        $providerAreaId = $pa->getId();

        $this->em->createQueryBuilder()
                 ->delete('Zikula\Component\HookDispatcher\Storage\Doctrine\Entity\HookBindingEntity')
                 ->where('pareaid = ? AND sareaid = ?')
                 ->getQuery()->setParameters(array(1 => $providerAreaId, 2 => $subscriberAreaId))
                 ->execute();

        $this->generateRuntimeHandlers();
    }

    private function getBindings()
    {
        $order = new \Doctrine\ORM\Query\Expr\OrderBy();
        $order->add('t.sareaid', 'ASC');
        $order->add('t.sortorder', 'ASC');

        return $this->em->createQueryBuilder()
                 ->from('Zikula\Component\HookDispatcher\Storage\Doctrine\Entity\HookBindingEntity', 't')
                 ->orderBy($order)
                 ->getQuery()
                 ->getArrayResult();
    }

    public function getBindingsFor($areaName)
    {
        $area = $this->em->getRepository('Zikula\Component\HookDispatcher\Storage\Doctrine\Entity\HookBindingEntity')
                     ->findOneByAreaname($areaName);

        if (!$area) {
            return array();
        }

        if ($area->getAreatype() == self::PROVIDER) {
            $table = 'Zikula_Doctrine_Model_HookProvider';
            $areaIdField = 'pareaid';
        } elseif ($area->getAreatype() == self::SUBSCRIBER) {
            $table = 'Zikula_Doctrine_Model_HookSubscriber';
            $areaIdField = 'sareaid';
        }

        $order = new \Doctrine\ORM\Query\Expr\OrderBy();
        $order->add('t.sortorder', 'ASC');
        $order->add('t.sareaid', 'ASC');
        $results = $this->em->createQueryBuilder()
                         ->from('Zikula\Component\HookDispatcher\Storage\Doctrine\Entity\HookBindingEntity', 't')
                         ->where("$areaIdField = ?")
                         ->orderBy($order)
                         ->getQuery()->setParameter(1, $area->getId())
                         ->getArrayResult();

        $areas = array();
        foreach ($results as $result) {
            $area = $this->em->find('Zikula\Component\HookDispatcher\Storage\Doctrine\Entity\HookAreaEntity',
                                    $result['pareaid']);
            $areas[] = array('areaname' => $area->getAreaname(), 'category' => $area->getCategory());
        }

        return $areas;
    }

    public function setBindOrder($subscriberAreaName, array $providerAreaNames)
    {
        $sareaId = $this->em->getRepository('Zikula\Component\HookDispatcher\Storage\Doctrine\Entity\HookAreaEntity')
                        ->findOneByAreaname($subscriberAreaName)
                        ->getId();

        // convert provider areanames to ids
        $providerAreaIds = array();
        foreach ($providerAreaNames as $name) {
            $providerAreaIds[] =
                $this->em->getRepository('Zikula\Component\HookDispatcher\Storage\Doctrine\Entity\HookAreaEntity')
                     ->findOneByAreaname($name)
                     ->getId();
        }

        // sort bindings in order of appearance from $providerAreaIds
        $counter = 1;
        foreach ($providerAreaIds as $id) {
            $this->em->createQueryBuilder()
                 ->update('Zikula\Component\HookDispatcher\Storage\Doctrine\Entity\HookBindingEntity', 't')
                 ->set('t.sortorder', $counter)
                 ->where('t.sareaid = ? AND t.pareaid = ?')
                 ->getQuery()->setParameters(array(1 => $sareaId, 2 => $id))
                ->execute();
            $counter++;
        }

        $this->generateRuntimeHandlers();
    }

    public function getRuntimeMetaByEventName($eventName)
    {
        foreach ($this->runtimeHandlers as $handler) {
            if ($handler['eventname'] == $eventName) {
                return array('areaid' => $handler['sareaid'], 'owner' => $handler['sowner']);
            }
        }

        return false;
    }

    public function getBindingBetweenAreas($subscriberArea, $providerArea)
    {
        $sareaId = $this->em->getRepository('Zikula\Component\HookDispatcher\Storage\Doctrine\Entity\HookAreaEntity')
                        ->findOneByAreaname($subscriberArea)
                        ->getId();

        $pareaId = $this->em->getRepository('Zikula\Component\HookDispatcher\Storage\Doctrine\Entity\HookAreaEntity')
                        ->findOneByAreaname($providerArea)
                        ->getId();

        return $this->em->createQueryBuilder()->select('t')
                    ->from('Zikula\Component\HookDispatcher\Storage\Doctrine\Entity\HookBindingEntity', 't')
                    ->where('t.sareaid = ? AND t.pareaid = ?')
                    ->getQuery()->setParameters(array(1 => $sareaId, 2 => $pareaId))
                    ->getOneOrNullResult();
    }

    public function isAllowedBindingBetweenAreas($subscriberArea, $providerArea)
    {
        $sareaId = $this->em->getRepository('Zikula\Component\HookDispatcher\Storage\Doctrine\Entity\HookAreaEntity')
                        ->findOneByAreaname($subscriberArea)
                        ->getId();

        $subscribers =
            $this->em->getRepository('Zikula\Component\HookDispatcher\Storage\Doctrine\Entity\HookSubscriberEntity')
                 ->findBy(array('sareaid' => $sareaId));

        if (!$subscribers) {
            return false;
        }

        $allow = false;
        foreach ($subscribers as $subscriber) {
            $pareaId =
                $this->em->getRepository('Zikula\Component\HookDispatcher\Storage\Doctrine\Entity\HookAreaEntity')
                     ->findOneByAreaname($providerArea)
                     ->getId();

            $hookprovider =
                $this->em->createQueryBuilder()->select('t')
                     ->from('Zikula\Component\HookDispatcher\Storage\Doctrine\Entity\HookProviderEntity', 't')
                     ->where('pareaid = ? AND hooktype = ? AND category = ?')
                     ->getQuery()->setParameters(array(1 => $pareaId,
                                                       2 => $subscriber['hooktype'],
                                                       3 => $subscriber['category']))
                     ->getArrayResult();

            if ($hookprovider) {
                $allow = true;
                break;
            }
        }

        return $allow;
    }

    public function getBindingsBetweenOwners($subscriberOwner, $providerOwner)
    {
        return $this->em->createQueryBuilder()->select('t')
                    ->from('Zikula\Component\HookDispatcher\Storage\Doctrine\Entity\HookBindingEntity', 't')
                    ->where('sowner = ? AND powner = ?')
                    ->getQuery()->setParameters(array(1 => $subscriberOwner, 2 => $providerOwner))
                    ->getArrayResult();
    }

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

    public function getAreaId($areaName)
    {
        $hookArea = $this->em->getRepository('Zikula\Component\HookDispatcher\Storage\Doctrine\Entity\HookAreaEntity')
                   ->findOneByAreaname($areaName);

        if (!$hookArea) {
            return false;
        }
        return $hookArea->getId();
    }
}
