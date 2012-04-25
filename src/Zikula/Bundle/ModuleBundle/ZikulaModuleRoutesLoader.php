<?php

namespace Zikula\Bundle\ModuleBundle;

use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\RouteCollection;

class ZikulaModuleRoutesLoader extends Loader
{
    /**
     * @var ZikulaKernel
     */
    private $zikulaKernel;

    public function __construct($zikulaKernel)
    {
        $this->zikulaKernel = $zikulaKernel;
    }

    public function load($resource, $type = null)
    {
        $collection = new RouteCollection();

        /* @var AbstractModule $moduleBundle */
        foreach ($this->zikulaKernel->getZikulaBundlesOfType(ZikulaKernel::TYPE_MODULES) as $moduleBundle) {
            $resource = '@'.$moduleBundle->getName().'/Controller/';
            $subCollection = $this->resolve($resource)->load($resource, 'annotation');
            $collection->addCollection($subCollection, '/'.str_replace('Module', '', $moduleBundle->getName()).'/');
        }

        return $collection;
    }

    public function supports($resource, $type = null)
    {
        return $type == 'zikulaModules';
    }

}
