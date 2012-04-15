<?php

namespace Zikula\ModuleBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 *
 */
class SandboxContainerExtension implements ExtensionInterface
{
    /**
     * @var ExtensionInterface 
     */
    private $delegate;
    private $serviceIds = array();
    
    public function __construct($delegate, &$serviceIds)
    {
        $this->delegate = $delegate;
        $this->serviceIds =& $serviceIds;
    }

    public function getAlias()
    {
        return $this->delegate->getAlias();
    }

    public function getNamespace()
    {
        return $this->delegate->getNamespace();
    }

    public function getXsdValidationBasePath()
    {
        return $this->delegate->getXsdValidationBasePath();
    }

    public function load(array $config, ContainerBuilder $container)
    {
        $this->delegate->load($config, new SandboxContainerBuilder($container, $this->serviceIds));
    }

}
