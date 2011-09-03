<?php

namespace Zikula\ThemesBundle\RuntimeBundle;

use Zikula\ModulesBundle\Bundle\RuntimeBundleProviderInterface;

/**
 *
 */
class ThemeRuntimeBundleProvider implements RuntimeBundleProviderInterface
{
    private $kernelRootDir;
    
    public function __construct($kernelRootDir)
    {
        $this->kernelRootDir = $kernelRootDir;
    }
    
    public function getBundle($name)
    {
        return new ThemeRuntimeBundle($this->extractThemeName($name), $this->kernelRootDir);
    }
    
    public function hasBundle($name)
    {
        return preg_match('/^Themes(.*)/', $name) 
                && file_exists($this->kernelRootDir . '/../themes/'. $this->extractThemeName($name));
    }
    
    private function extractThemeName($name) {
        return preg_replace('/^Themes(.*)/', '$1', $name);
    }
}
