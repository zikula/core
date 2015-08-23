<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\SearchModule\Listener;

use ModUtil;
use BlockUtil;
use System;
use SecurityUtil;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Zikula\Core\Event\GenericEvent;

class ModuleEventListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'installer.module.installed' => array('moduleInstall'),
        );
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
        if (ModUtil::apiFunc($mod, 'search', 'info')) {

            // get all search blocks
            $blocks = BlockUtil::getBlocksInfo();

            foreach ($blocks as $block) {

                $block = $block->toArray();

                if ($block['bkey'] != 'ZikulaSearchModule') {
                    continue;
                }

                $content = BlockUtil::varsFromContent($block['content']);

                if (!isset($content['active'])) {
                    $content['active'] = array();
                }
                $content['active'][$mod] = 1;

                $block['content'] = BlockUtil::varsToContent($content);
                ModUtil::apiFunc('ZikulaBlocksModule', 'admin', 'update', $block);
            }
        }
    }

}