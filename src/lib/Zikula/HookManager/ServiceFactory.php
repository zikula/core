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

/**
 * Factory for hook handler services.
 */
class Zikula_HookManager_ServiceFactory
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
     * @var Zikula_ServiceManager
     */
    private $serviceManager;

    /**
     * Constructor.
     *
     * @param Zikula_ServiceManager $serviceManager ServiceManager.
     * @param string                $serviceId      ID of service to inject.
     */
    public function __construct(Zikula_ServiceManager $serviceManager, $serviceId)
    {
        $this->serviceManager = $serviceManager;
        if (!$serviceManager->hasService($serviceId)) {
            throw new Zikula_HookManager_Exception_InvalidArgumentException(sprintf('Service %s is not registered in ServiceManager', $serviceId));
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
     * @return Zikula_ServiceHandler
     */
    public function buildService($id, $className, $method)
    {
        if (!$this->serviceManager->hasService($id)) {
            $definition = new Zikula_ServiceManager_Definition($className, array(new Zikula_ServiceManager_Reference($this->serviceId)));
            $this->serviceManager->registerService($id, $definition);
        }

        return new Zikula_ServiceHandler($id, $method);
    }

}
