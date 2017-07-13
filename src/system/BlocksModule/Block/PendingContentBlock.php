<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\BlocksModule\Block;

use Zikula\BlocksModule\AbstractBlockHandler;
use Zikula\Common\Collection\Collectible\PendingContentCollectible;
use Zikula\Common\Collection\Container;
use Zikula\Core\Event\GenericEvent;

/**
 * Class PendingContentBlock
 */
class PendingContentBlock extends AbstractBlockHandler
{
    public function display(array $properties)
    {
        if (!$this->hasPermission('PendingContent::', "$properties[title]::", ACCESS_OVERVIEW)) {
            return '';
        }

        // trigger event
        $event = new GenericEvent(new Container('pending_content'));
        $pendingCollection = $this->get('event_dispatcher')->dispatch('get.pending_content', $event)->getSubject();

        $content = [];
        foreach ($pendingCollection as $collection) {
            /** @var \Zikula\Common\Collection\Container $collection */
            foreach ($collection as $item) {
                if ($item instanceof PendingContentCollectible) {
                    $link = $this->get('router')->generate($item->getRoute(), $item->getArgs());
                } else {
                    $link = '';
                }
                $content[] = [
                    'description' => $item->getDescription(),
                    'link' => $link,
                    'number' => $item->getNumber(),
                ];
            }
        }

        return $this->renderView('@ZikulaBlocksModule/Block/pendingcontent.html.twig', [
            'content' => $content
        ]);
    }

    public function getType()
    {
        return $this->__("Pending Content");
    }
}
