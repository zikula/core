<?php
/**
 * Copyright Zikula Foundation 2016 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\BlocksModule\Block;

use Zikula\Common\Collection\Collectible\PendingContentCollectible;
use Zikula\Common\Collection\Container;
use Zikula\Core\AbstractBlockHandler;
use Zikula\Core\Event\GenericEvent;

/**
 * Class PendingContentBlock
 * @package Zikula\BlocksModule\Block
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
            $module = $collection->getName();
            foreach ($collection as $item) {
                if ($item instanceof \Zikula_Provider_AggregateItem) { // @todo remove at Core-2.0
                    $link = \ModUtil::url($module, $item->getController(), $item->getMethod(), $item->getArgs());
                } elseif ($item instanceof PendingContentCollectible) {
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
