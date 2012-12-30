<?php

use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel as Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class ZikulaKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            new Zikula\Bundle\CoreBundle\CoreBundle(),
        );

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        //$loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
        //$loader->load(__DIR__.'/config/database.yml');
    }
}
