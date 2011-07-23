<?php
/**
 * Copyright 2011 Zikula Foundation
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license MIT
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */
class SystemPlugins_DoctrineExtensions_ExtensionsManager
{
    private $eventManager;
    private $serviceManager;
    private $listeners;

    public function __construct(\Doctrine\Common\EventManager $eventManager, Zikula_ServiceManager $serviceManager)
    {
        $this->eventManager = $eventManager;
        $this->serviceManager = $serviceManager;
    }

    public function getListener($type)
    {
        $type = strtolower($type);
        if (isset($this->listeners[$type])) {
            return $this->listeners[$type];
        }
        
        $id = 'doctrine_extensions.listener.' . $type;
        if (!$this->serviceManager->hasService($id)) {
            throw new InvalidArgumentException(sprintf('No such behaviour %s', $type));
        }

        $annotationReader = $this->serviceManager->getService('doctrine.annotationreader');
        $annotationDriver = $this->serviceManager->getService('doctrine.annotationdriver');
        
        $chain = $this->serviceManager->getService('doctrine.driverchain');
        
        // specific behaviour required for certain drivers.
        $entityName = null;
        switch ($type) {
            case 'translatable':
                $entityName = 'Gedmo\\Translatable\\Entity\\Translation';
                break;
            case 'loggable':
                $entityName = 'Loggable\\Entity\\LogEntry';
                break;
        }
        
        if ($entityName) {
            $chain->addDriver($annotationDriver, $entityName);
        }

        $this->listeners[$type] = $this->serviceManager->getService($id);
        $this->listeners[$type]->setAnnotationReader($annotationReader);
        $this->eventManager->addEventSubscriber($this->listeners[$type]);
                
        return $this->listeners[$type];
    }

}