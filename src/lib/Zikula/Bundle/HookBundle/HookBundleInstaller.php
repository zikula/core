<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\HookBundle;

use Doctrine\ORM\EntityManagerInterface;
use Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookAreaEntity;
use Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookBindingEntity;
use Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookProviderEntity;
use Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookRuntimeEntity;
use Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookSubscriberEntity;
use Zikula\Core\Doctrine\Helper\SchemaHelper;
use Zikula\Core\InstallerInterface;

/**
 * Class HookBundleInstaller
 */
class HookBundleInstaller implements InstallerInterface
{
    /**
     * @var SchemaHelper
     */
    private $schemaTool;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    private static $entities = [
        HookAreaEntity::class, // @deprecated
        HookBindingEntity::class,
        HookProviderEntity::class, // @deprecated
        HookRuntimeEntity::class,
        HookSubscriberEntity::class, // @deprecated
    ];

    /**
     * HookBundleInstaller constructor.
     * @param SchemaHelper $schemaTool
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        SchemaHelper $schemaTool,
        EntityManagerInterface $entityManager
    ) {
        $this->schemaTool = $schemaTool;
        $this->em = $entityManager;
    }

    public function install()
    {
        try {
            $this->schemaTool->create(self::$entities);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    public function uninstall()
    {
        return false;
    }

    public function upgrade($currentCoreVersion)
    {
        // special note, the $currentCoreVersion var will contain the version of the CORE (not this bundle)

        if (version_compare($currentCoreVersion, '1.5.0', '<')) {
            $this->schemaTool->update(self::$entities);
            /** @var HookAreaEntity[] $areas */
            $areas = $this->em->getRepository(HookAreaEntity::class)->findAll();
            foreach ($areas as $area) {
                $qb = $this->em->createQueryBuilder();
                $qb->update(HookRuntimeEntity::class, 'r')
                    ->set('r.sareaid', ':an')
                    ->where('r.sareaid = :aid')
                    ->setParameter('an', $area->getAreaname())
                    ->setParameter('aid', $area->getId())
                    ->getQuery()
                    ->execute();
                $qb = $this->em->createQueryBuilder();
                $qb->update(HookRuntimeEntity::class, 'r')
                    ->set('r.pareaid', ':an')
                    ->where('r.pareaid = :aid')
                    ->setParameter('an', $area->getAreaname())
                    ->setParameter('aid', $area->getId())
                    ->getQuery()
                    ->execute();
                $qb = $this->em->createQueryBuilder();
                $qb->update(HookBindingEntity::class, 'b')
                    ->set('b.sareaid', ':an')
                    ->where('b.sareaid = :aid')
                    ->setParameter('an', $area->getAreaname())
                    ->setParameter('aid', $area->getId())
                    ->getQuery()
                    ->execute();
                $qb = $this->em->createQueryBuilder();
                $qb->update(HookBindingEntity::class, 'b')
                    ->set('b.pareaid', ':an')
                    ->where('b.pareaid = :aid')
                    ->setParameter('an', $area->getAreaname())
                    ->setParameter('aid', $area->getId())
                    ->getQuery()
                    ->execute();
                $qb = $this->em->createQueryBuilder();
                $qb->update(HookSubscriberEntity::class, 's')
                    ->set('s.sareaid', ':an')
                    ->where('s.sareaid = :aid')
                    ->setParameter('an', $area->getAreaname())
                    ->setParameter('aid', $area->getId())
                    ->getQuery()
                    ->execute();
                $qb = $this->em->createQueryBuilder();
                $qb->update(HookProviderEntity::class, 'p')
                    ->set('p.pareaid', ':an')
                    ->where('p.pareaid = :aid')
                    ->setParameter('an', $area->getAreaname())
                    ->setParameter('aid', $area->getId())
                    ->getQuery()
                    ->execute();
            }
        }
        // @todo at Core-2.0 remove deprecated entities

        // Update successful
        return true;
    }
}
