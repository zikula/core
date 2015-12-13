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

namespace Zikula\BlocksModule\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Zikula\BlocksModule\Entity\RepositoryInterface\BlockRepositoryInterface;

class BlockRepository extends EntityRepository implements BlockRepositoryInterface
{
    public function getFilteredBlocks(array $filters)
    {
        $qb = $this->_em->createQueryBuilder();
        $query = $qb->select('b')
            ->from($this->_entityName, 'b');
        if (isset($filters['position'])) {
            $subQb = $this->_em->createQueryBuilder();
            $query
                ->join('b.placements', 'p')
                ->where($qb->expr()->in('p.position',
                    $subQb->select('bp')
                        ->from('ZikulaBlocksModule:BlockPositionEntity', 'bp')
                        ->where('bp.pid = ?1')
                        ->getDQL()
                ))
                ->setParameter(1, $filters['position']);
            unset($filters['position']);
        }
        $paramIndex = 2;
        $sortField = isset($filters['sort-field']) ? $filters['sort-field'] : 'bid';
        $sortDirection = isset($filters['sort-direction']) ? $filters['sort-direction'] : 'ASC';
        unset($filters['sort-field'], $filters['sort-direction']);
        foreach ($filters as $key => $value) {
            if (!isset($value)) {
                unset($filters[$key]);
            } else {
                $query->andWhere($qb->expr()->eq('b.' . $key, '?' . $paramIndex))
                    ->setParameter($paramIndex, $value);
            }
        }
        $query->orderBy('b.' . $sortField, $sortDirection);

        return $query->getQuery()->getResult();
    }
}