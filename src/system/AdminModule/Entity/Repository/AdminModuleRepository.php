<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\AdminModule\Entity\Repository;

use Doctrine\Common\Collections\Selectable;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityRepository;
use Zikula\AdminModule\Entity\AdminCategoryEntity;
use Zikula\AdminModule\Entity\AdminModuleEntity;

class AdminModuleRepository extends EntityRepository implements ObjectRepository, Selectable
{
    public function persistAndFlush($entity)
    {
        $this->_em->persist($entity);
        $this->_em->flush($entity);
    }

    public function countModulesByCategory($cid)
    {
        $query = $this->createQueryBuilder('m')
            ->select('count(m.amid)')
            ->where('m.cid = :cid')
            ->setParameter('cid', $cid)
            ->getQuery();

        return (int)$query->getSingleScalarResult();
    }

    public function setModuleCategory($moduleName, AdminCategoryEntity $adminCategoryEntity)
    {
        $moduleEntity = $this->_em->getRepository('ZikulaExtensionsModule:ExtensionEntity')->findOneBy(['name' => $moduleName]);
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
}
