<?php

namespace Zikula\Core\Form;

use Symfony\Component\Form\FormView;

/**
 * Doctrine RegistryInterface implementation required by symfony2 forms.
 */
class DoctrineRegistryImpl extends \Symfony\Bridge\Doctrine\ManagerRegistry implements \Symfony\Bridge\Doctrine\RegistryInterface
{
    public function __construct()
    {
        parent::__construct('ORM', array('main' => 'doctrine.connection'), array('main' => 'doctrine.entitymanager'), 'main', 'main', 'Doctrine\ORM\Proxy\Proxy');
    }

    protected function getService($name)
    {
        if($name == 'doctrine.entitymanager') {
            return \ServiceUtil::get($name);
        } else if($name == 'doctrine.connection') {
            return \ServiceUtil::get('doctrine.entitymanager')->getConnection();
        } else {
            return null;
        }
    }

    public function getAliasNamespace($alias)
    {
    }

    public function getDefaultEntityManagerName()
    {
        return $this->getDefaultManagerName();
    }

    public function getEntityManager($name = null)
    {
        return $this->getManager($name);
    }

    public function getEntityManagers()
    {
        return $this->getManagers();
    }

    public function resetEntityManager($name = null)
    {
        $this->resetEntityManager($name);
    }

    public function getEntityNamespace($alias)
    {
    }

    public function getEntityManagerNames()
    {
        return $this->getManagerNames();
    }

    public function getEntityManagerForClass($class)
    {
        return $this->getManagerForClass($class);
    }
}
