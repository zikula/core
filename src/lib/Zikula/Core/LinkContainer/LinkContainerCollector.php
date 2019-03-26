<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Core\LinkContainer;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Zikula\Core\Event\GenericEvent;

class LinkContainerCollector
{
    private $linkContainers;

    private $eventDispatcher;

    public function __construct(EventDispatcherInterface $dispatcher, iterable $linkContainers)
    {
        $this->eventDispatcher = $dispatcher;
        $this->linkContainers = [];
        foreach ($linkContainers as $linkContainer) {
            $this->addContainer($linkContainer);
        }
    }

    private function addContainer(LinkContainerInterface $linkContainer)
    {
        $this->linkContainers[$linkContainer->getBundleName()] = $linkContainer;
    }

    /**
     * @param string $containerName
     * @param string $type
     */
    public function getLinks($containerName, $type = LinkContainerInterface::TYPE_ADMIN)
    {
        $links = [];

        if ($this->hasContainer($containerName)) {
            try {
                $links = $this->linkContainers[$containerName]->getLinks($type);
            } catch (RouteNotFoundException $routeNotFoundException) {
                // do nothing, just skip invalid links
            }

            // fire event here to add more links like hooks, moduleServices, etc
            $event = new GenericEvent($containerName, ['type' => $type], $links);
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
