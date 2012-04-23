<?php

namespace Zikula\Bundle\ModuleBundle\ModuleService;

use Zikula\Bundle\ModuleBundle\Entity\Module;

/**
 * Interface of module metadata storages.
 */
interface StorageInterface
{
    /**
     * @return Module
     */
    public function getAll();

    /**
     * @return Module
     */
    public function get($id);

    public function insert(Module $module);

    public function update(Module $module);
}
