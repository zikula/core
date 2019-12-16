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

namespace Zikula\BlocksModule\Entity\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Zikula\BlocksModule\Entity\BlockPositionEntity;
use Zikula\BlocksModule\Entity\RepositoryInterface\BlockPositionRepositoryInterface;

class BlockPositionRepository extends ServiceEntityRepository implements BlockPositionRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BlockPositionEntity::class);
    }

    public function findByName(string $name)
    {
        return $this->findOneBy(['name' => $name]);
    }

    public function getPositionChoiceArray(): array
    {
        $positions = $this->getEntityManager()->createQueryBuilder()
            ->select('p.pid, p.name')
            ->from('ZikulaBlocksModule:BlockPositionEntity', 'p', 'p.pid')
            ->getQuery()
            ->getResult()
        ;
        foreach ($positions as $id => $row) {
            $positions[$id] = $row['name'];
        }

        return $positions;
    }
}
