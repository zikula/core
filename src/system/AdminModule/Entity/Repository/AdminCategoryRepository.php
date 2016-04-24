<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\AdminModule\Entity\Repository;

use Doctrine\ORM\EntityRepository;

class AdminCategoryRepository extends EntityRepository
{
    public function getIndexedCollection($indexBy)
    {
        $collection = $this->getEntityManager()->createQueryBuilder()
            ->select('ac')
            ->from($this->_entityName, 'ac', 'ac.' . $indexBy)
            ->getQuery()
            ->getResult();

        return $collection;
    }
}
