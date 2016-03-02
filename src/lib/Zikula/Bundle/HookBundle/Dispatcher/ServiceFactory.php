<?php
/**
 * Copyright 2010 Zikula Foundation
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package HookDispatcher
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Bundle\HookBundle\Dispatcher;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Factory for hook handler services.
 */
class ServiceFactory
{
    /**
     * Id a service.
     *
     * @var string
     */
    private $serviceId;

    /**
     * ServiceManager.
     *
     * @var ContainerBuilder
     */
    private $container;

    /**
     * Constructor.
     *
     * @param ContainerBuilder $container ContainerBuilder.
     * @param string           $serviceId ID of service to inject, (usually the event dispatcher).
     */
    public function __construct(ContainerBuilder $container, $serviceId)
    {
        $this->container = $container;
        if (!$container->has($serviceId)) {
            throw new Exception\InvalidArgumentException(sprintf('Service %s is not registered in the DIC', $serviceId));
        }

        $this->serviceId = $serviceId;
    }

    /**
     * Build service.
     *
     * Builds event servicehandlers.  If the service does not exist, it creates it
     * and adds it to the DI container.
     *
     * @param string $id
     * @param string $className
     * @param string $method
     *
     * @return array array($id, $method)
     */
    public function buildService($id, $className, $method)
    {
        if (!$this->container->has($id)) {
            $definition = new Definition($className, array(new Reference($this->serviceId)));
            $this->container->setDefinition($id, $definition);
        }

        return array($id, $method);
    }
}
