<?php

namespace Zikula\Bundle\ModuleBundle;

use Symfony\Component\HttpKernel\Debug\ContainerAwareTraceableEventDispatcher;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Log\LoggerInterface;

class ModuleAwareTraceableEventDispatcher extends ContainerAwareTraceableEventDispatcher
{
    /**
     * @var ZikulaKernel
     */
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
        if (!$bundle
            || !$bundle instanceof AbstractModule
            || ($bundle instanceof AbstractModule && $this->kernel->isModuleBundleActive($bundle))) {
            parent::addListenerService($eventName, $callback, $priority);
        }
    }
}
