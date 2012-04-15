<?php

namespace Zikula\ModulesBundle;

/**
 * A module service to install, upgrade and remove modules.
 */
interface ModuleServiceInterface {
    public function regenerateModuleList();
    public function getAllModules();
    public function getModule($id);
    public function installModule($id);
}

