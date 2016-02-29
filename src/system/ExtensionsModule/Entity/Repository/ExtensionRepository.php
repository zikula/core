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

namespace Zikula\ExtensionsModule\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Zikula\ExtensionsModule\Entity\ExtensionEntity;
use Zikula\ExtensionsModule\Entity\RepositoryInterface\ExtensionRepositoryInterface;

class ExtensionRepository extends EntityRepository implements ExtensionRepositoryInterface
{
    public function findAll()
    {
        return parent::findAll();
    }

    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        return parent::findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * @param $name
     * @return ExtensionEntity
     */
    public function get($name)
    {
        return parent::findOneBy(['name' => $name]);
    }

    public function getPagedCollectionBy(array $criteria, array $orderBy = null, $limit = 0, $offset = 1)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('e')->from($this->_entityName, 'e');
        $i = 1;
        foreach ($criteria as $field => $value) {
            $comparator = is_array($value) ? 'IN' : '=';
            $qb->andWhere("e.$field $comparator param$i")
                ->setParameter("param$i", $value);
            $i++;
        }
        if (isset($orderBy)) {
            foreach ($orderBy as $field => $direction) {
                $qb->orderBy("e.$field", $direction);
            }
        }
        $query = $qb->getQuery();
        if ($limit > 0) {
            $query->setMaxResults($limit)
                ->setFirstResult($offset - 1);
        }
        $paginator = new Paginator($query);

        return $paginator;
    }

    public function getIndexedArrayCollection($indexBy)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('e')->from($this->_entityName, 'e', "e.$indexBy");

        return $qb->getQuery()->getArrayResult();
    }

    public function updateName($oldName, $newName)
    {
        $query = $this->_em->createQueryBuilder()
            ->update($this->_entityName, 'e')
            ->set('e.name', ':newname')
            ->setParameter('newname', $newName)
            ->where('e.name = :oldname')
            ->setParameter('oldname', $oldName)
            ->getQuery();
        $query->execute();
    }

    public function persistAndFlush($entity)
    {
        $this->_em->persist($entity);
        $this->_em->flush($entity);
    }

    public function removeAndFlush($entity)
    {
        $this->_em->remove($entity);
        $this->_em->flush();
    }
}
