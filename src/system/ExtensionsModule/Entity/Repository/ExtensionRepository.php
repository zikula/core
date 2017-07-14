<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ExtensionsModule\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Zikula\ExtensionsModule\Entity\ExtensionEntity;
use Zikula\ExtensionsModule\Entity\RepositoryInterface\ExtensionRepositoryInterface;

class ExtensionRepository extends EntityRepository implements ExtensionRepositoryInterface
{
    /**
     * @param $name
     * @return ExtensionEntity
     */
    public function get($name)
    {
        return $this->findOneBy(['name' => $name]);
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
        $qb = $this->createQueryBuilder('e')->indexBy('e', 'e.' . $indexBy);

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
