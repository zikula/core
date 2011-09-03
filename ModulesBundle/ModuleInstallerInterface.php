<?php

namespace Zikula\ModulesBundle;

/**
 * 
 */
interface ModuleInstallerInterface
{
    public function entitiesToInstall();
    
    public function install();
    
    public function upgrade();
    
    public function uninstall();
}
