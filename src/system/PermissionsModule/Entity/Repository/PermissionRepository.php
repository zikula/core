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

namespace Zikula\PermissionsModule\Entity\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Zikula\PermissionsModule\Api\PermissionApi;
use Zikula\PermissionsModule\Entity\PermissionEntity;
use Zikula\PermissionsModule\Entity\RepositoryInterface\PermissionRepositoryInterface;

class PermissionRepository extends ServiceEntityRepository implements PermissionRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PermissionEntity::class);
    }

    public function getPermissionsByGroups(array $groups): array
    {
        $qb = $this->createQueryBuilder('p');
        // do not select just 'p' where because this method is used by the upgrader
        // where columns "comment" and "colour" might not exist yet
        $query = $qb->select('p.pid, p.gid, p.sequence, p.component, p.instance, p.level')
            ->where($qb->expr()->in('p.gid', ':groups'))
            ->setParameter('groups', $groups)
            ->orderBy('p.sequence', 'ASC')
            ->getQuery();

        return $query->getArrayResult();
    }

    public function getFilteredPermissions(int $group = PermissionApi::ALL_GROUPS, string $component = null): array
    {
        $qb = $this->createQueryBuilder('p')
            ->select('p')
            ->orderBy('p.sequence', 'ASC');
        if (PermissionApi::ALL_GROUPS !== $group) {
            $qb->where('p.gid = :gid')
                ->setParameter('gid', $group);
        }
        if (isset($component)) {
            $qb->andWhere('p.component LIKE :permgrpparts')
                ->setParameter('permgrpparts', $component . '%');
        }

        return $qb->getQuery()->getResult();
    }

    public function getAllComponents(): array
    {
        $all = $this->findBy([], ['sequence' => 'ASC']);
        $components = [];
        foreach ($all as $perm) {
            // extract components, we keep everything up to the first colon
            $parts = explode(':', $perm->getComponent());
            $components[$parts[0]] = $parts[0];
        }

        return $components;
    }

    public function getAllColours(): array
    {
        $all = $this->findBy([], ['sequence' => 'ASC']);
        $colours = [];
        foreach ($all as $perm) {
            $colour = $perm->getColour() ?: 'default';
            $colours[ucfirst($colour)] = $colour;
        }

        return $colours;
    }

    public function persistAndFlush(PermissionEntity $entity): void
    {
        $this->_em->persist($entity);
        $this->_em->flush();
    }

    public function getMaxSequence(): int
    {
        $qb = $this->createQueryBuilder('p');
        $query = $qb->select($qb->expr()->max('p.sequence'))
            ->getQuery();

        return (int)$query->getSingleScalarResult();
    }

    public function updateSequencesFrom(int $value, int $amount = 1): void
    {
        $query = $this->_em->createQueryBuilder()
            ->update('ZikulaPermissionsModule:PermissionEntity', 'p')
            ->set('p.sequence', 'p.sequence + :amount')
            ->where('p.sequence >= :value')
            ->setParameter('amount', $amount)
            ->setParameter('value', $value)
            ->getQuery();
        $query->execute();
    }

    public function reSequence(): void
    {
        /** @var PermissionEntity[] $permissions */
        $permissions = $this->findBy([], ['sequence' => 'ASC']);
        $sequence = 1;
        foreach ($permissions as $permission) {
            $permission->setSequence($sequence);
            $sequence++;
        }
        $this->_em->flush();
    }

    public function deleteGroupPermissions(int $groupId = 0): void
    {
        if ($groupId < 1) {
            return;
        }

        $qb = $this->_em->createQueryBuilder('p')
            ->delete('ZikulaPermissionsModule:PermissionEntity', 'p')
            ->where('p.gid = :gid')
            ->setParameter('gid', $groupId);

        $query = $qb->getQuery();

        $query->execute();
    }
}
