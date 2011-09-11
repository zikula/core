<?php

namespace Zikula\ThemesBundle;

/**
 *
 */
abstract class ZikulaTheme extends \Symfony\Component\HttpKernel\Bundle\Bundle 
{
    
    public function __construct() 
    {
        $name = get_class($this);
        $posNamespaceSeperator = strrpos($name, '\\');
        $this->name = substr($name, $posNamespaceSeperator + 1);
    }
    
    public abstract function getVersion();
    
    public function getServiceIds() 
    {
        return array();
    }
}
