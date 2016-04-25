<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Core\LinkContainer;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Zikula\Core\Event\GenericEvent;

class LinkContainerCollector
{
    private $linkContainers;
    private $eventDispatcher;

    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->eventDispatcher = $dispatcher;
        $this->linkContainers = array();
    }

    public function addContainer(LinkContainerInterface $linkContainer)
    {
        $this->linkContainers[$linkContainer->getBundleName()] = $linkContainer;
    }

    public function getLinks($containerName, $type = LinkContainerInterface::TYPE_ADMIN)
    {
        $links = array();
        if ($this->hasContainer($containerName)) {
            $links = $this->linkContainers[$containerName]->getLinks($type);
            // fire event here to add more links like hooks, moduleServices, etc
            $event = new GenericEvent($containerName, array('type' => $type), $links);
            $links = $this->eventDispatcher->dispatch(LinkContainerInterface::EVENT_NAME, $event)->getData();
        }

        return $links;
    }

    public function getAllLinksByType($type = LinkContainerInterface::TYPE_ACCOUNT)
    {
        $links = [];
        foreach ($this->linkContainers as $name => $container) {
            $linkArray = $container->getLinks($type);
            if (!empty($linkArray)) {
                $links[$name] = $linkArray;
            }
        }

        return $links;
    }

    public function hasContainer($containerName)
    {
        return isset($this->linkContainers[$containerName]);
    }
}
