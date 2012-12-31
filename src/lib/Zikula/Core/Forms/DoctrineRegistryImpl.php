<?php

namespace Zikula\Core\Forms;

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
        if ($name == 'doctrine.entitymanager') {
            return \ServiceUtil::getService($name);
        } else {
            if ($name == 'doctrine.connection') {
                return \ServiceUtil::getService('doctrine.entitymanager')->getConnection();
            } else {
                return null;
            }
        }
    }

    function getAliasNamespace($alias)
    {
    }

    function getDefaultEntityManagerName()
    {
        return $this->getDefaultManagerName();
    }

    function getEntityManager($name = null)
    {
        return $this->getManager($name);
    }

    function getEntityManagers()
    {
        return $this->getManagers();
    }

    function resetEntityManager($name = null)
    {
        $this->resetEntityManager($name);
    }

    function getEntityNamespace($alias)
    {
    }

    function getEntityManagerNames()
    {
        return $this->getManagerNames();
    }

    function getEntityManagerForClass($class)
    {
        return $this->getManagerForClass($class);
    }
}
