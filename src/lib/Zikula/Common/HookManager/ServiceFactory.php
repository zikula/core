<?php
/**
 * Copyright 2010 Zikula Foundation
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package HookManager
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Common\HookManager;

use Zikula\Common\ServiceManager\ServiceManager;
use Zikula\Common\ServiceManager\Definition;
use Zikula\Common\ServiceManager\Reference;
use Zikula\Common\EventManager\ServiceHandler;

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
     * @var ServiceManager
     */
    private $container;

    /**
     * Constructor.
     *
     * @param ServiceManager $container ServiceManager.
     * @param string         $serviceId      ID of service to inject.
     */
    public function __construct(ServiceManager $container, $serviceId)
    {
        $this->container = $container;
        if (!$container->hasService($serviceId)) {
            throw new Exception\InvalidArgumentException(sprintf('Service %s is not registered in ServiceManager', $serviceId));
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
     * @return ServiceHandler
     */
    public function buildService($id, $className, $method)
    {
        if (!$this->container->hasService($id)) {
            $definition = new Definition($className, array(new Reference($this->serviceId)));
            $this->container->registerService($id, $definition);
        }

        return new ServiceHandler($id, $method);
    }

}
