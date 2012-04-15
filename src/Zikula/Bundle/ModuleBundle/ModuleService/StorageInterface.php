<?php

namespace Zikula\ModulesBundle\ModuleService;

/**
 * Interface of module metadata storages.
 */
interface StorageInterface
{
    /**
     * @return \Zikula\ModulesBundle\Entity\Module[]
     */
    public function getAll();
    
    /**
     * @return \Zikula\ModulesBundle\Entity\Module
     */
    public function get($id);
    
    public function insert(\Zikula\ModulesBundle\Entity\Module $module);
    
    public function update(\Zikula\ModulesBundle\Entity\Module $module);
}

