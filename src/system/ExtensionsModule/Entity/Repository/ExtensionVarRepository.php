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

namespace Zikula\ExtensionsModule\Entity\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Zikula\ExtensionsModule\Entity\ExtensionVarEntity;
use Zikula\ExtensionsModule\Entity\RepositoryInterface\ExtensionVarRepositoryInterface;

class ExtensionVarRepository extends ServiceEntityRepository implements ExtensionVarRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExtensionVarEntity::class);
    }

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
