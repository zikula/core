<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\BlocksModule\Block;

use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Zikula\BlocksModule\AbstractBlockHandler;
use Zikula\BlocksModule\Event\PendingContentEvent;

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
        $pendingCollection = $this->eventDispatcher->dispatch(new PendingContentEvent('pending_content'))->getContainer();

        return $this->renderView('@ZikulaBlocksModule/Block/pendingcontent.html.twig', [
            'pendingCollection' => $pendingCollection
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
