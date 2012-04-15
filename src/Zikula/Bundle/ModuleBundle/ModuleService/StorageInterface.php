<?php

namespace Zikula\ModuleBundle\ModuleService;

/**
 * Interface of module metadata storages.
 */
interface StorageInterface
{
    /**
     * @return \Zikula\ModuleBundle\Entity\Module[]
     */
    public function getAll();
    
    /**
     * @return \Zikula\ModuleBundle\Entity\Module
     */
    public function get($id);
    
    public function insert(\Zikula\ModuleBundle\Entity\Module $module);
    
    public function update(\Zikula\ModuleBundle\Entity\Module $module);
}

