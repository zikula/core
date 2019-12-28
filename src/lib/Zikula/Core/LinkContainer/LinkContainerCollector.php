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

use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\LegacyEventDispatcherProxy;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Zikula\Core\Event\GenericEvent;

class LinkContainerCollector
{
    /**
     * @var array
     */
    private $linkContainers;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher, iterable $linkContainers = [])
    {
        $this->eventDispatcher = LegacyEventDispatcherProxy::decorate($eventDispatcher);
        $this->linkContainers = [];
        foreach ($linkContainers as $linkContainer) {
            $this->addContainer($linkContainer);
        }
    }

    public function addContainer(LinkContainerInterface $linkContainer): void
    {
        $this->linkContainers[$linkContainer->getBundleName()] = $linkContainer;
    }

    public function getLinks(string $containerName, string $type = LinkContainerInterface::TYPE_ADMIN): array
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
            $links = $this->eventDispatcher->dispatch($event, LinkContainerInterface::EVENT_NAME)->getData();
        }

        return $links;
    }

    public function getAllLinksByType(string $type = LinkContainerInterface::TYPE_ACCOUNT): array
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

    public function hasContainer(string $containerName): bool
    {
        return isset($this->linkContainers[$containerName]);
    }
}
