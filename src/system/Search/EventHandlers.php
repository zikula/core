<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Search
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * EventHandlers class.
 */
class Search_EventHandlers
{
    /**
     * Handle module install event "installer.module.installed".
     * Receives $modinfo as $args
     *
     * @param Zikula_Event $event
     *
     * @return void
     */
    public static function moduleInstall(Zikula_Event $event)
    {
        $mod = $event['name'];

        // determine search capability
        if (ModUtil::apiFunc($mod, 'search', 'info')) {

            // get all search blocks
            $blocks = BlockUtil::getBlocksInfo();

            foreach ($blocks as $block) {

                $block = $block->toArray();

                if ($block['bkey'] != 'Search') {
                    continue;
                }

                $content = BlockUtil::varsFromContent($block['content']);

                if (!isset($content['active'])) {
                    $content['active'] = array();
                }
                $content['active'][$mod] = 1;

                $block['content'] = BlockUtil::varsToContent($content);
                ModUtil::apiFunc('Blocks', 'admin', 'update', $block);
            }
        }
    }
}

