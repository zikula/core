<?php
/**
 * Copyright Zikula Foundation 2015 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula_Form
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\ExtensionsModule\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Zikula\ExtensionsModule\Entity\ExtensionVarEntity;
use Zikula\ExtensionsModule\Entity\RepositoryInterface\ExtensionVarRepositoryInterface;

class ExtensionVarRepository extends EntityRepository implements ExtensionVarRepositoryInterface
{
    public function remove(ExtensionVarEntity $entity)
    {
        $this->_em->remove($entity);
    }

    public function persistAndFlush(ExtensionVarEntity $entity)
    {
        $this->_em->persist($entity);
        $this->_em->flush();
    }

    public function deleteByExtensionAndName($extensionName, $variableName)
    {
        $qb = $this->_em->createQueryBuilder()
            ->delete('Zikula\ExtensionsModule\Entity\ExtensionVarEntity', 'v')
            ->where('v.modname = :modname')
            ->setParameter('modname', $extensionName)
            ->andWhere('v.name = :name')
            ->setParameter('name', $variableName);
        $query = $qb->getQuery();
        $result = $query->execute();

        return (bool)$result;
    }

    public function deleteByExtension($extensionName)
    {
        $qb = $this->_em->createQueryBuilder()
            ->delete('Zikula\ExtensionsModule\Entity\ExtensionVarEntity', 'v')
            ->where('v.modname = :modname')
            ->setParameter('modname', $extensionName);
        $query = $qb->getQuery();
        $result = $query->execute();

        return (bool)$result;
    }

    public function findAll()
    {
        return parent::findAll();
    }

    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        return parent::findBy($criteria, $orderBy, $limit, $offset);
    }

    public function updateName($oldName, $newName)
    {
        $query = $this->_em->createQueryBuilder()
            ->update('Zikula\ExtensionsModule\Entity\ExtensionVarEntity', 'v')
            ->set('v.modname', ':newname')
            ->setParameter('newname', $newName)
            ->where('v.modname = :oldname')
            ->setParameter('oldname', $oldName)
            ->getQuery();
        $query->execute();
    }
}
