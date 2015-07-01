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
 * This class serves for initialising and managing doctrine extensions.
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

        if ($type == 'uploadable') {
            $this->listeners[$type] = $this->serviceManager->get('stof_doctrine_extensions.' . $type . '.manager');

            return $this->listeners[$type];
        }

        if (in_array($type, array('blameable', 'loggable'))) {
            $this->listeners[$type] = $this->serviceManager->get('stof_doctrine_extensions.listener.' . $type);

            return $this->listeners[$type];
        }

        $service = '';
        if ($type == 'standardfields') {
            $service = 'doctrine_extensions.listener.' . $type;
        }

        // just for legacy, to be removed as soon as no modules perform these getListener() calls anymore
        $service = 'doctrine_extensions.listener.' . $type;

        if (empty($service) || !$this->serviceManager->has($service)) {
            throw new \InvalidArgumentException(sprintf('No such behaviour %s', $type));
        }

        $this->listeners[$type] = $this->serviceManager->get($service);
        $this->listeners[$type]->setAnnotationReader($this->serviceManager->get('annotation_reader'));
        $this->eventManager->addEventSubscriber($this->listeners[$type]);

        return $this->listeners[$type];
    }
}
