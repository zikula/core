<?php

namespace Zikula\ModulesBundle;

use Symfony\Bundle\FrameworkBundle\Debug\TraceableEventDispatcher;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Log\LoggerInterface;

/**
 *
 */
class ModuleAwareTraceableEventDispatcher extends TraceableEventDispatcher
{
    private $kernel;
    
    public function __construct(ContainerInterface $container, LoggerInterface $logger = null)
    {
        parent::__construct($container, $logger);
        
        $this->kernel = $container->get('kernel');
    }

    public function addListenerService($eventName, $callback, $priority = 0)
    {
        if (!is_array($callback) || 2 !== count($callback)) {
            throw new \InvalidArgumentException('Expected an array("service", "method") argument');
        }
        
        $bundle = $this->kernel->getBundleByServiceId($callback[0]);
        
        // skip inactive module bundles
        if(!$bundle 
                || !$bundle instanceof ZikulaModule 
                || ($bundle instanceof ZikulaModule && $this->kernel->isModuleBundleActive($bundle))) {
            parent::addListenerService($eventName, $callback, $priority);
        }
    }
}
