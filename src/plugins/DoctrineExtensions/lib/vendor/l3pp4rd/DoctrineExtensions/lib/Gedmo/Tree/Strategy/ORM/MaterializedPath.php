<?php

namespace Gedmo\Tree\Strategy\ORM;

use Gedmo\Tree\Strategy\AbstractMaterializedPath;
use Doctrine\Common\Persistence\ObjectManager;
use Gedmo\Mapping\Event\AdapterInterface;

/**
 * This strategy makes tree using materialized path strategy
 *
 * @author Gustavo Falco <comfortablynumb84@gmail.com>
 * @author Gediminas Morkevicius <gediminas.morkevicius@gmail.com>
 * @package Gedmo.Tree.Strategy.ODM.MongoDB
 * @subpackage MaterializedPath
 * @link http://www.gediminasm.org
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
class MaterializedPath extends AbstractMaterializedPath
{
    /**
     * {@inheritdoc}
     */
    public function removeNode($om, $meta, $config, $node)
    {
        $uow = $om->getUnitOfWork();
        $pathProp = $meta->getReflectionProperty($config['path']);
        $pathProp->setAccessible(true);
        $path = addcslashes($pathProp->getValue($node), '%');

        // Remove node's children
        $qb = $om->createQueryBuilder();
        $qb->select('e')
            ->from($meta->name, 'e')
            ->where($qb->expr()->like('e.'.$config['path'], $qb->expr()->literal($path.'%')));
        $results = $qb->getQuery()
            ->execute();
        
        foreach ($results as $node) {
            $uow->scheduleForDelete($node);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getChildren($om, $meta, $config, $path)
    {
        $path = addcslashes($path, '%');
        $qb = $om->createQueryBuilder($meta->name);
        $qb->select('e')
            ->from($meta->name, 'e')
            ->where($qb->expr()->like('e.'.$config['path'], $qb->expr()->literal($path.'%')))
            ->andWhere('e.'.$config['path'].' != :path')
            ->orderBy('e.'.$config['path'], 'asc');      // This may save some calls to updateNode
        $qb->setParameter('path', $path);

        return $qb->getQuery()
            ->execute();
    }
}
