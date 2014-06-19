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

namespace Zikula\Core\Doctrine;

use Doctrine\Common\EventManager;

/**
 *
 */
class ExtensionsManager
{
    private $eventManager;
    private $serviceManager;
    private $listeners;

    public function __construct(EventManager $eventManager, \Zikula_ServiceManager $serviceManager)
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
/* see #1711
        $service = '';
        $deListenerTypes = array('blameable', 'loggable', 'sluggable', 'softdeleteable', 'sortable', 'timestampable', 'translatable', 'tree', 'uploadable');
        if (in_array($type, $deListenerTypes)) {
            $service = 'stof_doctrine_extensions.listener.' . $type;
            $this->listeners[$type] = $this->serviceManager->get($service);

            return $this->listeners[$type];
        }

        if ($type == 'standardfields') {
            $service = 'doctrine_extensions.listener.' . $type;
        }

        if (empty($service) || !$this->serviceManager->has($service)) {
            throw new \InvalidArgumentException(sprintf('No such behaviour %s', $type));
        }

        $this->listeners[$type] = $this->serviceManager->get($service);
        $this->listeners[$type]->setAnnotationReader($this->serviceManager->get('doctrine.annotation_reader'));
        $this->eventManager->addEventSubscriber($this->listeners[$type]);

        return $this->listeners[$type];
*/

        $id = 'doctrine_extensions.listener.' . $type;
        if (!$this->serviceManager->has($id)) {
            throw new \InvalidArgumentException(sprintf('No such behaviour %s', $type));
        }

        $annotationReader = $this->serviceManager->get('doctrine.annotation_reader');
        $annotationDriver = $this->serviceManager->get('doctrine.annotation_driver');

        $chain = $this->serviceManager->get('doctrine.driver_chain');

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

        $this->listeners[$type] = $this->serviceManager->get($id);
        $this->listeners[$type]->setAnnotationReader($annotationReader);
        $this->eventManager->addEventSubscriber($this->listeners[$type]);

        return $this->listeners[$type];
    }
}
