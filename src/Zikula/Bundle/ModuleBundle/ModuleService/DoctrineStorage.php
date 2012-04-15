<?php

namespace Zikula\ModulesBundle\ModuleService;

use Doctrine\ORM\EntityManager;

/**
 * Doctrine based storage.
 */
class DoctrineStorage implements StorageInterface
{
    /**
     * @var EntityManager 
     */
    private $em;
    
    public function __construct(EntityManager $em) 
    {
        $this->em = $em;
    }

    public function getAll()
    {
        return $this->em->getRepository('ZikulaModulesBundle:Module')->findAll();
    }
    
    public function get($id)
    {
        return $this->em->find('ZikulaModulesBundle', $id);
    }
    
    public function insert(\Zikula\ModulesBundle\Entity\Module $module) 
    {
        $this->em->persist($module);
        $this->em->flush();
    }
    
    public function update(\Zikula\ModulesBundle\Entity\Module $module)
    {
        $this->em->persist($module);
        $this->em->flush();
    }
}
