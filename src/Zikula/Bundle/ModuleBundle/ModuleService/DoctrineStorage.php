<?php

namespace Zikula\Bundle\ModuleBundle\ModuleService;

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
        return $this->em->getRepository('ZikulaModuleBundle:Module')->findAll();
    }

    public function get($id)
    {
        return $this->em->find('ZikulaModuleBundle', $id);
    }

    public function insert(\Zikula\Bundle\ModuleBundle\Entity\Module $module)
    {
        $this->em->persist($module);
        $this->em->flush();
    }

    public function update(\Zikula\Bundle\ModuleBundle\Entity\Module $module)
    {
        $this->em->persist($module);
        $this->em->flush();
    }
}
