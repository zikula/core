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

namespace Zikula\BlocksModule\Block;

use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Zikula\BlocksModule\AbstractBlockHandler;
use Zikula\Bundle\CoreBundle\Collection\Collectible\PendingContentCollectible;
use Zikula\Bundle\CoreBundle\Collection\Container;
use Zikula\Bundle\CoreBundle\Event\GenericEvent;

class PendingContentBlock extends AbstractBlockHandler
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function display(array $properties): string
    {
        if (!$this->hasPermission('PendingContent::', $properties['title'] . '::', ACCESS_OVERVIEW)) {
            return '';
        }

        // trigger event
        $event = new GenericEvent(new Container('pending_content'));
        $pendingCollection = $this->eventDispatcher->dispatch($event, 'get.pending_content')->getSubject();

        $content = [];
        foreach ($pendingCollection as $collection) {
            /** @var Container $collection */
            foreach ($collection as $item) {
                $link = '';
                if ($item instanceof PendingContentCollectible) {
                    $link = $this->router->generate($item->getRoute(), $item->getArgs());
                }
                $content[] = [
                    'description' => $item->getDescription(),
                    'link' => $link,
                    'number' => $item->getNumber()
                ];
            }
        }

        return $this->renderView('@ZikulaBlocksModule/Block/pendingcontent.html.twig', [
            'content' => $content
        ]);
    }

    public function getType(): string
    {
        return $this->trans('Pending Content');
    }

    /**
     * @required
     */
    public function setRouter(RouterInterface $router): void
    {
        $this->router = $router;
    }

    /**
     * @required
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher): void
    {
        $this->eventDispatcher = $eventDispatcher;
    }
}
