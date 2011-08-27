<?php

namespace Zikula\ModulesBundle;

/**
 *
 */
class ZikulaModuleRoutesLoader extends \Symfony\Component\Config\Loader\Loader {
    
    private $zikulaKernel;
    
    public function __construct($zikulaKernel) {
        $this->zikulaKernel = $zikulaKernel;
    }
    
    public function load($resource, $type = null) {
        $collection = new \Symfony\Component\Routing\RouteCollection();
        
        foreach($this->zikulaKernel->getModuleBundles() as $moduleBundle) {
            $resource = '@' . $moduleBundle->getName() . '/Controller/';
            $subCollection = $this->resolve($resource)->load($resource, 'annotation');
            $collection->addCollection($subCollection, '/' . str_replace('Bundle', '', $moduleBundle->getName()) . '/');
        }
        
        return $collection;
    }

    public function supports($resource, $type = null) {
        return $type == 'zikulaModules';
    }
}
