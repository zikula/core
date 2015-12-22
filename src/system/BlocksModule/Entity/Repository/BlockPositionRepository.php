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
