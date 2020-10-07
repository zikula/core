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

namespace Zikula\AdminModule\Entity\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Zikula\AdminModule\Entity\AdminCategoryEntity;
use Zikula\AdminModule\Entity\AdminModuleEntity;
use Zikula\AdminModule\Entity\RepositoryInterface\AdminModuleRepositoryInterface;
use Zikula\ExtensionsModule\Entity\ExtensionEntity;

class AdminModuleRepository extends ServiceEntityRepository implements AdminModuleRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AdminModuleEntity::class);
    }

    public function persistAndFlush(AdminModuleEntity $entity): void
    {
        $this->_em->persist($entity);
        $this->_em->flush();
    }

    public function countModulesByCategory(int $cid): int
    {
        $query = $this->createQueryBuilder('m')
            ->select('COUNT(m.amid)')
            ->where('m.cid = :cid')
            ->setParameter('cid', $cid)
            ->getQuery();

        return (int) $query->getSingleScalarResult();
    }

    public function setModuleCategory(ExtensionEntity $moduleEntity, AdminCategoryEntity $adminCategoryEntity): void
    {
        $adminModuleEntity = $this->findOneBy(['mid' => $moduleEntity->getId()]);
        if (!isset($adminModuleEntity)) {
            $adminModuleEntity = new AdminModuleEntity();
        }
        $adminModuleEntity->setMid($moduleEntity->getId());
        $adminModuleEntity->setCid($adminCategoryEntity->getCid());
        $modulesInCategory = $this->countModulesByCategory($adminCategoryEntity->getCid());
        $adminModuleEntity->setSortorder($modulesInCategory);
        $this->persistAndFlush($adminModuleEntity);
    }

    public function changeCategory(int $oldCategory, int $newCategory): void
    {
        $query = $this->_em->createQueryBuilder()
            ->update('ZikulaAdminModule:AdminModuleEntity', 'm')
            ->set('m.cid', $newCategory)
            ->where('m.cid = :cid')
            ->setParameter('cid', $oldCategory)
            ->getQuery();

        $query->execute();
    }
}
