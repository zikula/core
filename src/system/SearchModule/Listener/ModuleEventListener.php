<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SearchModule\Listener;

use BlockUtil;
use ModUtil;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Zikula\Core\Event\GenericEvent;

class ModuleEventListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            'installer.module.installed' => ['moduleInstall'],
        ];
    }

    /**
     * Handle module install event "installer.module.installed".
     * Receives $modinfo as $args
     *
     * @param GenericEvent $event
     *
     * @return void
     */
    public function moduleInstall(GenericEvent $event)
    {
        $mod = $event->getName();

        // determine search capability
        if (!ModUtil::apiFunc($mod, 'search', 'info')) {
            return;
        }

        // get all search blocks
        $blocks = BlockUtil::getBlocksInfo();

        foreach ($blocks as $block) {
            $block = $block->toArray();

            if ($block['bkey'] != 'ZikulaSearchModule') {
                continue;
            }

            $content = BlockUtil::varsFromContent($block['content']);

            if (!isset($content['active'])) {
                $content['active'] = [];
            }
            $content['active'][$mod] = 1;

            $block['content'] = BlockUtil::varsToContent($content);
            ModUtil::apiFunc('ZikulaBlocksModule', 'admin', 'update', $block);
        }
    }
}
