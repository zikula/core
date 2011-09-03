<?php

namespace Zikula\ThemesBundle\RuntimeBundle;

use Zikula\ModulesBundle\Bundle\RuntimeBundle;

/**
 *
 */
class ThemeRuntimeBundle extends RuntimeBundle
{
    private $themeName;
    private $themePath;
    
    public function __construct($themeName, $kernelRootDir)
    {
        $this->themeName = $themeName;
        $this->themePath = $kernelRootDir . '/../themes/' . $themeName;
        $this->name = 'Themes' . $themeName;
    }
    
    public function getNamespace()
    {
        return $this->themeName;
    }
    
    public function getPath()
    {
        return $this->themePath;
    }
}
