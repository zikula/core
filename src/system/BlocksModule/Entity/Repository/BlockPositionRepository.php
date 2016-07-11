<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\BlocksModule\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Zikula\BlocksModule\Entity\RepositoryInterface\BlockPositionRepositoryInterface;

class BlockPositionRepository extends EntityRepository implements BlockPositionRepositoryInterface
{
    public function findByName($name)
    {
        return parent::findOneBy(['name' => $name]);
    }

    /**
     * Get an array of position names indexed by the id
     * @return array
     */
    public function getPositionChoiceArray()
    {
        $positions = $this->getEntityManager()->createQueryBuilder()
            ->select('p.pid, p.name')
            ->from('ZikulaBlocksModule:BlockPositionEntity', 'p', 'p.pid')
            ->getQuery()
            ->getResult();
        foreach ($positions as $id => $row) {
            $positions[$id] = $row['name'];
        }

        return $positions;
    }
}
