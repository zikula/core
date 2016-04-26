<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
     * @return array [$id, $method]
     */
    public function buildService($id, $className, $method)
    {
        if (!$this->container->has($id)) {
            $definition = new Definition($className, [new Reference($this->serviceId)]);
            $this->container->setDefinition($id, $definition);
        }

        return [$id, $method];
    }
}
