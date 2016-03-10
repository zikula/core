<?php
/**
 * Copyright Zikula Foundation 2016 - Zikula Application Framework
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
