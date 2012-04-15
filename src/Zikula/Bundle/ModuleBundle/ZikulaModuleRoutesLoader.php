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
        
        foreach($this->zikulaKernel->getZiklaBundlesOfType(ZikulaKernel::TYPE_MODUES) as $moduleBundle) {
            $resource = '@' . $moduleBundle->getName() . '/Controller/';
            $subCollection = $this->resolve($resource)->load($resource, 'annotation');
            $collection->addCollection($subCollection, '/' . str_replace('Module', '', $moduleBundle->getName()) . '/');
        }
        
        return $collection;
    }

    public function supports($resource, $type = null) {
        return $type == 'zikulaModules';
    }
}
