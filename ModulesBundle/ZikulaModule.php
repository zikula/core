<?php

namespace Zikula\ModulesBundle;

/**
 *
 */
abstract class ZikulaModule extends \Symfony\Component\HttpKernel\Bundle\Bundle {
    
    public function __construct() {
        $name = get_class($this);
        $posNamespaceSeperator = strrpos($name, '\\');
        $this->name = str_replace('Module', '', substr($name, $posNamespaceSeperator + 1));
    }
    
    public abstract function getVersion();
    
    /**
     * @return ModuleInstallerInterface
     */
    public abstract function createInstaller();
}
