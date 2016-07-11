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

use DataUtil;
use Doctrine\ORM\EntityManager;
use LogUtil;
use System;
use Zikula\Bundle\HookBundle\Dispatcher\Exception\InvalidArgumentException;
use Zikula\Bundle\HookBundle\Dispatcher\StorageInterface;

/**
 * Doctrine class.
 */
class DoctrineStorage implements StorageInterface
{
    const PROVIDER = 'p';
    const SUBSCRIBER = 's';

    private $runtimeHandlers = [];

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

        // Now we have an areaId we can register a subscriber, but first test if the subscriber is already registered.
        $existingSubscriber = $this->getSubscriberByEventName($eventName);
        if (!empty($existingSubscriber)) {
            if (System::isDevelopmentMode()) {
                LogUtil::registerWarning(__f('The hook subscriber "%1$s" could not be registered for "%2$s" because it is registered already.', [$eventName, $owner]));
            } else {
                $warns = LogUtil::getWarningMessages(false);
                $msg = __f('Hook subscribers could not be registered for "%1$s" because they are registered already.', [$owner]);
                if (!in_array(DataUtil::formatForDisplayHTML($msg), $warns)) {
                    LogUtil::registerWarning($msg);
                }
            }

            return;
        }

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
                 ->from('Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookSubscriberEntity', 't')
                 ->where('t.eventname = ?1')
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
                 ->delete('Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookSubscriberEntity', 't')
                 ->where('t.sareaid = ?1')
                 ->getQuery()->setParameter(1, $areaId)
                 ->execute();

        // remove bindings
        $this->em->createQueryBuilder()
                 ->delete('Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookBindingEntity', 't')
                 ->where('t.sareaid = ?1')
                 ->getQuery()->setParameter(1, $areaId)
                 ->execute();

        // clean areas
        $this->em->createQueryBuilder()
                 ->delete('Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookAreaEntity', 't')
                 ->where('t.id = ?1')
                 ->getQuery()->setParameter(1, $areaId)
                 ->execute();

        $this->generateRuntimeHandlers();
    }

    public function registerProvider($owner, $subOwner, $areaName, $hookType, $category, $className, $method, $serviceId = null)
    {
        $pareaId = $this->registerArea($areaName, self::PROVIDER, $owner, $subOwner, $category);

        $existingProvider = $this->getProviderByAreaAndType($pareaId, $hookType);
        if (!empty($existingProvider)) {
            if (System::isDevelopmentMode()) {
                LogUtil::registerWarning(__f('The hook provider for area "%1$s" of type "%2$s" could not be registered for "%3$s" because it already exists.', [$pareaId, $hookType, $owner]));
            } else {
                $warns = LogUtil::getWarningMessages(false);
                $msg = __f('Hook providers could not be registered for "%1$s" because they already exist.', [$owner]);
                if (!in_array(DataUtil::formatForDisplayHTML($msg), $warns)) {
                    LogUtil::registerWarning($msg);
                }
            }

            return;
        }

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

    public function getProviderByAreaAndType($areaId, $type)
    {
        return $this->em->createQueryBuilder()->select('t')
            ->from('Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookProviderEntity', 't')
            ->where('t.pareaid = ?1')
            ->andWhere('t.hooktype = ?2')
            ->getQuery()
            ->setParameter(1, $areaId)
            ->setParameter(2, $type)
            ->getArrayResult();
    }

    public function unregisterProviderByArea($areaName)
    {
        $areaId = $this->getAreaId($areaName);

        if (!$areaId) {
            return;
        }

        // delete provider entry
        $this->em->createQueryBuilder()
                 ->delete('Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookProviderEntity', 't')
                 ->where('t.pareaid = ?1')
                 ->getQuery()->setParameter(1, $areaId)
                 ->execute();

        // remove bindings
        $this->em->createQueryBuilder()
                 ->delete('Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookBindingEntity', 't')
                 ->where('t.pareaid = ?1')
                 ->getQuery()->setParameter(1, $areaId)
                 ->execute();

        // clean area
        $this->em->createQueryBuilder()
                 ->delete('Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookAreaEntity', 't')
                 ->where('t.id = ?1')
                 ->getQuery()->setParameter(1, $areaId)
                 ->execute();

        $this->generateRuntimeHandlers();
    }

    public function getSubscribersByOwner($owner)
    {
        return $this->em->createQueryBuilder()->select('t')
                    ->from('Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookSubscriberEntity', 't')
                    ->where('t.owner = ?1')
                    ->getQuery()->setParameter(1, $owner)
                    ->getArrayResult();
    }

    public function getSubscriberAreasByOwner($owner)
    {
        return $this->getAreasByOwner($owner, self::SUBSCRIBER);
    }

    public function getProviderAreasByOwner($owner)
    {
        return $this->getAreasByOwner($owner, self::PROVIDER);
    }

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

    public function getOwnerByArea($areaName)
    {
        $hookarea = $this->em->createQueryBuilder()->select('t')
                    ->from('Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookAreaEntity', 't')
                    ->where('t.areaname = ?1')
                    ->getQuery()->setParameter(1, $areaName)
                    ->getSingleResult();

        return $hookarea->getOwner();
    }

    private function generateRuntimeHandlers()
    {
        // truncate runtime
        $this->em->createQueryBuilder()
             ->delete('Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookRuntimeEntity')
             ->getQuery()
             ->execute();

        foreach ($this->getBindings() as $binding) {
            $this->addRuntimeHandlers($binding['sareaid'], $binding['pareaid']);
        }
    }

    private function addRuntimeHandlers($subscriberAreaId, $providerAreaId)
    {
        $sa = $this->em->find('Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookAreaEntity',
                              $subscriberAreaId);
        $pa = $this->em->find('Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookAreaEntity',
                              $providerAreaId);

        $subscribers = $this->em->createQueryBuilder()->select('t')
                            ->from('Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookSubscriberEntity', 't')
                            ->where('t.sareaid = ?1')
                            ->getQuery()->setParameter(1, $subscriberAreaId)
                            ->getArrayResult();

        if (!$subscribers) {
            return false;
        }

        foreach ($subscribers as $subscriber) {
            $provider = $this->em->createQueryBuilder()->select('t')
                             ->from('Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookProviderEntity', 't')
                             ->where('t.pareaid = ?1 AND t.hooktype = ?2')
                             ->getQuery()->setParameters([1 => $providerAreaId, 2 => $subscriber['hooktype']])
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
                 ->from('Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookRuntimeEntity', 't')
                 ->getQuery()
                 ->getArrayResult();

        return $this->runtimeHandlers;
    }

    public function bindSubscriber($subscriberArea, $providerArea)
    {
        $sa = $this->em->getRepository('Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookAreaEntity')
                   ->findOneBy(['areaname' => $subscriberArea]);
        $pa = $this->em->getRepository('Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookAreaEntity')
                   ->findOneBy(['areaname' => $providerArea]);

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
        $sa = $this->em->getRepository('Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookAreaEntity')
                   ->findOneBy(['areaname' => $subscriberArea]);
        $pa = $this->em->getRepository('Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookAreaEntity')
                   ->findOneBy(['areaname' => $providerArea]);

        $subscriberAreaId = $sa->getId();
        $providerAreaId = $pa->getId();

        $this->em->createQueryBuilder()
                 ->delete('Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookBindingEntity', 't')
                 ->where('t.pareaid = ?1 AND t.sareaid = ?2')
                 ->getQuery()->setParameters([1 => $providerAreaId, 2 => $subscriberAreaId])
                 ->execute();

        $this->generateRuntimeHandlers();
    }

    private function getBindings()
    {
        $order = new \Doctrine\ORM\Query\Expr\OrderBy();
        $order->add('t.sareaid', 'ASC');
        $order->add('t.sortorder', 'ASC');

        return $this->em->createQueryBuilder()->select('t')
                 ->from('Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookBindingEntity', 't')
                 ->orderBy($order)
                 ->getQuery()
                 ->getArrayResult();
    }

    public function getBindingsFor($areaName)
    {
        $area = $this->em->getRepository('Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookAreaEntity')
                     ->findOneBy(['areaname' => $areaName]);

        if (!$area) {
            return [];
        }

        if ($area->getAreatype() == self::PROVIDER) {
            $areaIdField = 'pareaid';
        } else { // $area->getAreatype() == self::SUBSCRIBER
            $areaIdField = 'sareaid';
        }

        $order = new \Doctrine\ORM\Query\Expr\OrderBy();
        $order->add('t.sortorder', 'ASC');
        $order->add('t.sareaid', 'ASC');
        $results = $this->em->createQueryBuilder()->select('t')
                         ->from('Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookBindingEntity', 't')
                         ->where("t.$areaIdField = ?1")
                         ->orderBy($order)
                         ->getQuery()->setParameter(1, $area->getId())
                         ->getArrayResult();

        // this could be an area where related entities would help CAH - 23 Oct 2013
        $areas = [];
        foreach ($results as $result) {
            $area = $this->em->find('Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookAreaEntity',
                                    $result['pareaid']);
            $areas[] = [
                'areaname' => $area->getAreaname(),
                'category' => $area->getCategory()
            ];
        }

        return $areas;
    }

    public function setBindOrder($subscriberAreaName, array $providerAreaNames)
    {
        $sareaId = $this->em->getRepository('Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookAreaEntity')
                        ->findOneBy(['areaname' => $subscriberAreaName])
                        ->getId();

        // convert provider areanames to ids
        $providerAreaIds = [];
        foreach ($providerAreaNames as $name) {
            $providerAreaIds[] =
                $this->em->getRepository('Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookAreaEntity')
                     ->findOneBy(['areaname' => $name])
                     ->getId();
        }

        // sort bindings in order of appearance from $providerAreaIds
        $counter = 1;
        foreach ($providerAreaIds as $id) {
            $this->em->createQueryBuilder()
                 ->update('Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookBindingEntity', 't')
                 ->set('t.sortorder', $counter)
                 ->where('t.sareaid = ?1 AND t.pareaid = ?2')
                 ->getQuery()->setParameters([1 => $sareaId, 2 => $id])
                ->execute();
            $counter++;
        }

        $this->generateRuntimeHandlers();
    }

    public function getRuntimeMetaByEventName($eventName)
    {
        foreach ($this->runtimeHandlers as $handler) {
            if ($handler['eventname'] == $eventName) {
                return [
                    'areaid' => $handler['sareaid'],
                    'owner' => $handler['sowner']
                ];
            }
        }

        return false;
    }

    public function getBindingBetweenAreas($subscriberArea, $providerArea)
    {
        $sareaId = $this->em->getRepository('Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookAreaEntity')
                        ->findOneBy(['areaname' => $subscriberArea])
                        ->getId();

        $pareaId = $this->em->getRepository('Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookAreaEntity')
                        ->findOneBy(['areaname' => $providerArea])
                        ->getId();

        return $this->em->createQueryBuilder()->select('t')
                    ->from('Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookBindingEntity', 't')
                    ->where('t.sareaid = ?1 AND t.pareaid = ?2')
                    ->getQuery()->setParameters([1 => $sareaId, 2 => $pareaId])
                    ->getOneOrNullResult();
    }

    public function isAllowedBindingBetweenAreas($subscriberArea, $providerArea)
    {
        $sareaId = $this->em->getRepository('Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookAreaEntity')
                        ->findOneBy(['areaname' => $subscriberArea])
                        ->getId();

        $subscribers =
            $this->em->getRepository('Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookSubscriberEntity')
                 ->findBy(['sareaid' => $sareaId]);

        if (!$subscribers) {
            return false;
        }

        $allow = false;
        /** @var $subscriber \Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookSubscriberEntity */
        foreach ($subscribers as $subscriber) {
            $pareaId =
                $this->em->getRepository('Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookAreaEntity')
                     ->findOneBy(['areaname' => $providerArea])
                     ->getId();

            $hookprovider =
                $this->em->createQueryBuilder()->select('t')
                    ->from('Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookProviderEntity', 't')
                    ->where('t.pareaid = ?1 AND t.hooktype = ?2 AND t.category = ?3')
                    ->getQuery()->setParameters([
                        1 => $pareaId,
                        2 => $subscriber->getHooktype(),
                        3 => $subscriber->getCategory()
                    ])
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
                    ->from('Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookBindingEntity', 't')
                    ->where('t.sowner = ?1 AND t.powner = ?2')
                    ->getQuery()->setParameters([1 => $subscriberOwner, 2 => $providerOwner])
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
        $hookArea = $this->em->getRepository('Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookAreaEntity')
                   ->findOneBy(['areaname' => $areaName]);

        if (!$hookArea) {
            return false;
        }

        return $hookArea->getId();
    }
}
