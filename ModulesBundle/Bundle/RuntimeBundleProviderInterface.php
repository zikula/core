<?php

namespace Zikula\ModulesBundle\Bundle;

/**
 *
 */
interface RuntimeBundleProviderInterface
{
    /**
     * @return boolean
     */
    public function hasBundle($name);
    
    /**
     * @return RuntimeBundle
     */
    public function getBundle($name);
}
