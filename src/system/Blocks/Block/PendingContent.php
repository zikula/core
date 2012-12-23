<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Pending Content block
 */
class Blocks_Block_PendingContent extends Zikula_Controller_AbstractBlock
{
    /**
     * Initialise block.
     *
     * @return void
     */
    public function init()
    {
        SecurityUtil::registerPermissionSchema('PendingContent::', 'Block title::');
    }

    /**
     * Get information on block
     *
     * @return array The block information.
     */
    public function info()
    {
        return array('module'         => 'Blocks',
                     'text_type'      => $this->__('Pending Content'),
                     'text_type_long' => $this->__('Pending Content'),
                     'allow_multiple' => true,
                     'form_content'   => true,
                     'form_refresh'   => false,
                     'show_preview'   => true);
    }

    /**
     * Display block.
     *
     * @param array $blockinfo Blockinfo structure.
     *
     * @return output Rendered block.
     */
    public function display($blockinfo)
    {
        if (!SecurityUtil::checkPermission('PendingContent::', "$blockinfo[title]::", ACCESS_OVERVIEW)) {
            return;
        }

        // trigger event
        $event = new Zikula_Event('get.pending_content', new Zikula_Collection_Container('pending_content'));
        $pendingCollection = EventUtil::getManager()->notify($event)->getSubject();

        $content = array();
        // process results
        foreach ($pendingCollection as $collection) {
            $module = $collection->getName();
            foreach ($collection as $item) {
                $link = ModUtil::url($module, $item->getController(), $item->getMethod(), $item->getArgs());
                $content[] = array(
                    'description' => $item->getDescription(),
                    'link' => $link,
                    'number' => $item->getNumber(),
                );
            }
        }

        if (!empty($content)) {
            $this->view->assign('content', $content);
            $blockinfo['content'] = $this->view->fetch('blocks_block_pendingcontent.tpl');
        } else {
            $blockinfo['content'] = '';
        }

        return BlockUtil::themeBlock($blockinfo);
    }
}
