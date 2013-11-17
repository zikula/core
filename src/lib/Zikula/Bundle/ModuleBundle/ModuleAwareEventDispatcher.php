<?php

namespace Zikula\Bundle\ModuleBundle;

use Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ModuleAwareEventDispatcher extends ContainerAwareEventDispatcher
{
    /**
     * @var ZikulaKernel
     */
    private $kernel;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->kernel = $container->get('kernel');
    }

    public function addListenerService($eventName, $callback, $priority = 0)
    {
        if (!is_array($callback) || 2 !== count($callback)) {
            throw new \InvalidArgumentException('Expected an array("service", "method") argument');
        }

        $bundle = $this->kernel->getBundleByServiceId($eventName[0]);

        // skip inactive module bundles
        if (!$bundle
            || !$bundle instanceof AbstractModule
            || ($bundle instanceof AbstractModule && $this->kernel->isModuleBundleActive($bundle))) {
            parent::addListenerService($eventName, $callback, $priority);
        }
    }
}
