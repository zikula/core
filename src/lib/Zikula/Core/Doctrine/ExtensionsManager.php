<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Core\Doctrine;

use Doctrine\Common\EventManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * This class serves for initialising and managing doctrine extensions.
 */
class ExtensionsManager
{
    private $eventManager;
    private $serviceManager;
    private $listeners;

    public function __construct(EventManager $eventManager, ContainerInterface $serviceManager)
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

        if (in_array($type, ['blameable', 'loggable'])) {
            $this->listeners[$type] = $this->serviceManager->get('stof_doctrine_extensions.listener.' . $type);

            return $this->listeners[$type];
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
