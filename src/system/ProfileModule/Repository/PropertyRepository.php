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

namespace Zikula\ProfileModule\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Zikula\Bundle\FormExtensionBundle\DynamicFieldsContainerInterface;
use Zikula\ProfileModule\Entity\PropertyEntity;

class PropertyRepository extends ServiceEntityRepository implements PropertyRepositoryInterface, DynamicFieldsContainerInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PropertyEntity::class);
    }

    public function getIndexedActive(): array
    {
        $qb = $this->createQueryBuilder('p', 'p.id')
            ->where('p.active = true');

        return $qb->getQuery()->getArrayResult();
    }

    public function getDynamicFieldsSpecification(): array
    {
        return $this->findBy(['active' => true], ['weight' => 'ASC']);
    }
}
