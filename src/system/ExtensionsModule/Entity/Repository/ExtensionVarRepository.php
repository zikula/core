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
use Doctrine\Persistence\ManagerRegistry;
use Zikula\ExtensionsModule\Entity\ExtensionVarEntity;
use Zikula\ExtensionsModule\Entity\RepositoryInterface\ExtensionVarRepositoryInterface;

class ExtensionVarRepository extends ServiceEntityRepository implements ExtensionVarRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExtensionVarEntity::class);
    }

    public function remove(ExtensionVarEntity $entity): void
    {
        $this->_em->remove($entity);
    }

    public function persistAndFlush(ExtensionVarEntity $entity): void
    {
        $this->_em->persist($entity);
        $this->_em->flush();
    }

    public function deleteByExtensionAndName(string $extensionName, string $variableName): bool
    {
        $query = $this->_em->createQueryBuilder()
            ->delete(ExtensionVarEntity::class, 'v')
            ->where('v.modname = :modname')
            ->setParameter('modname', $extensionName)
            ->andWhere('v.name = :name')
            ->setParameter('name', $variableName)
            ->getQuery();
        $result = $query->execute();

        return (bool)$result;
    }

    public function deleteByExtension(string $extensionName): bool
    {
        $query = $this->_em->createQueryBuilder()
            ->delete(ExtensionVarEntity::class, 'v')
            ->where('v.modname = :modname')
            ->setParameter('modname', $extensionName)
            ->getQuery();
        $result = $query->execute();

        return (bool)$result;
    }

    public function updateName(string $oldName, string $newName): bool
    {
        $query = $this->_em->createQueryBuilder()
            ->update(ExtensionVarEntity::class, 'v')
            ->set('v.modname', ':newname')
            ->setParameter('newname', $newName)
            ->where('v.modname = :oldname')
            ->setParameter('oldname', $oldName)
            ->getQuery();
        $result = $query->execute();

        return (bool)$result;
    }
}
