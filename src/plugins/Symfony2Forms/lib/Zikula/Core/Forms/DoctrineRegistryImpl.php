<?php

namespace Zikula\Core\Forms;

use Symfony\Component\Form\FormView;

/**
 *
 */
class DoctrineRegistryImpl implements \Symfony\Bridge\Doctrine\RegistryInterface
{

    public function getConnection($name = null)
    {
        
    }

    public function getConnectionNames()
    {
        
    }

    public function getConnections()
    {
        
    }

    public function getDefaultConnectionName()
    {
        
    }

    public function getDefaultEntityManagerName()
    {
        
    }

    public function getEntityManager($name = null)
    {
        return \ServiceUtil::getService('doctrine.entitymanager');
    }

    public function getEntityManagerForClass($class)
    {
        return \ServiceUtil::getService('doctrine.entitymanager');
    }

    public function getEntityManagerNames()
    {
        
    }

    public function getEntityManagers()
    {
        return array(\ServiceUtil::getService('doctrine.entitymanager'));
    }

    public function getEntityNamespace($alias)
    {
        
    }

    public function getRepository($entityName, $entityManagerName = null)
    {
        
    }

    public function resetEntityManager($name = null)
    {
        
    }

}
