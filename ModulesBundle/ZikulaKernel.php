<?php

namespace Zikula\ModulesBundle;

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\DependencyInjection\ContainerBuilder;

abstract class ZikulaKernel extends Kernel{
    const TYPE_MODUES = 'modules';
    const TYPE_THEMES = 'themes';
    
    private $zikulaBundles;
    private $moduleServiceIds;
    
    public function boot() 
    {
        $modules = array();
        
        $this->loadModuleBundlesFromFilesystem();
        $this->loadThemeBundlesFromFilesystem();
        
        parent::boot();
    }
    
    public function registerModuleBundles(&$bundles) 
    {
        if($this->zikulaBundles) {
            foreach($this->zikulaBundles as $type => $bundleList) {
                foreach($bundleList as $bundle) {
                    $bundles[] = $bundle;
                }
            }
        }
    }
    
    public function getZiklaBundlesOfType($type) 
    {
        return $this->zikulaBundles[$type];
    }
    
    public function isClassInModule($class)
    {
        foreach ($this->getBundles() as $bundle) {
            if (0 === strpos($class, $bundle->getNamespace())) {
                return $bundle instanceof ZikulaModule;
            }
        }

        return false;
    }
    
    public function isClassInActiveModule($class)
    {
        foreach ($this->getBundles() as $bundle) {
            if (0 === strpos($class, $bundle->getNamespace())) {
                $modules = $this->container->get('zikula.modules')->getAllModules();
                $modules = array_filter($modules, function($m) use($bundle) { return $m->getName() == $bundle->getName(); });
                $module = array_shift($modules);
                
                return $module != null && $module->getState() == Entity\Module::STATE_ACTIVE;
            }
        }

        return false;
    }
    
    public function isModuleBundleActive(ZikulaModule $bundle) 
    {
        $modules = $this->container->get('zikula.modules')->getAllModules();
        $modules = array_filter($modules, function($m) use($bundle) { return $m->getName() == $bundle->getName(); });
        $module = array_shift($modules);

        return $module != null && $module->getState() == Entity\Module::STATE_ACTIVE;
    }
    
    public function getBundleByServiceId($id) {
        if(!is_array($this->moduleServiceIds)) {
            $file = $this->getCacheDir().'/'.$this->getContainerClass().'.modules';
            
            if(file_exists($file)) {
                $this->moduleServiceIds = unserialize(file_get_contents($file));
            } else {
                $this->moduleServiceIds = array();
            }
        }
        
        foreach($this->moduleServiceIds as $bundleName => $ids) {
            if(in_array($id, $ids)) {
                return $this->getBundle($bundleName);
            }
        }
        
        return null;
    }
    
    protected function getKernelParameters()
    {
        $base = parent::getKernelParameters();
        $new = array();
        $toClassName = function($object) { return get_class($object); };
        
        foreach($this->zikulaBundles as $type => $bundles) {
            $new['kernel.zikula.' . $type] = array_map($toClassName, $bundles);
        }
        
        return array_merge($base, $new);
    }
    
    protected function dumpContainer(ConfigCache $cache, ContainerBuilder $container, $class, $baseClass)
    {
        parent::dumpContainer($cache, $container, $class, $baseClass);
        
        $data = array();
        
        foreach($this->zikulaBundles as $type => $bundles) {
            foreach($bundles as $bundle) {
                $data[$bundle->getName()] = $bundle->getServiceIds();
            }
        }
        
        $cache = new \Symfony\Component\Config\ConfigCache($this->getCacheDir().'/'.$class.'.modules', $this->debug);
        $cache->write(serialize($data));
    }
    
    private function loadModuleBundlesFromFilesystem() 
    {
        $dirs = \Symfony\Component\Finder\Finder::create()
                    ->directories()->in($this->rootDir . '/../modules')->depth(0);
        
        foreach($dirs as $dir) {
            $class = sprintf('%s\\%sModule', $dir->getFilename(), $dir->getFilename());
            
            if(!is_subclass_of($class, 'Zikula\ModulesBundle\ZikulaModule')) {
                throw new Exception\InvalidModuleStructureException($dir->getFilename(). ' # '. $class);
            }
            
            $this->zikulaBundles[self::TYPE_MODUES][] = new $class();
        }
    }
    
    private function loadThemeBundlesFromFilesystem() 
    {
        $dirs = \Symfony\Component\Finder\Finder::create()
                    ->directories()->in($this->rootDir . '/../themes')->depth(0);
        
        foreach($dirs as $dir) {
            $class = sprintf('%s\\%sTheme', $dir->getFilename(), $dir->getFilename());
            
            if(!is_subclass_of($class, 'Zikula\ThemesBundle\ZikulaTheme')) {
                throw new Exception\InvalidThemeStructureException($dir->getFilename());
            }
            
            $this->zikulaBundles[self::TYPE_THEMES][] = new $class();
        }
    }
}
