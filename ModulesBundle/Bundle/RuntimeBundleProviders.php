<?php

namespace Zikula\ModulesBundle\Bundle;

/**
 *
 */
class RuntimeBundleProviders implements RuntimeBundleProviderInterface
{
    private $providers = array();
    
    public function addProvider(RuntimeBundleProviderInterface $provider)
    {
        $this->providers[] = $provider;
    }
    
    public function getBundle($name)
    {
        foreach($this->providers as $provider) {
            if($provider->hasBundle($name)) {
                return $provider->getBundle($name);
            }
        }
        
        throw new \InvalidArgumentException(sprintf('unknown runtime bundle "%s"', $name));
    }
    
    public function hasBundle($name)
    {
        foreach($this->providers as $provider) {
            if($provider->hasBundle($name)) {
                return true;
            }
        }
        
        return false;
    }
}
