<?php
/**
 * Copyright Zikula Foundation 2015 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage Response
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
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

    public function hasContainer($containerName)
    {
        return isset($this->linkContainers[$containerName]);
    }
}