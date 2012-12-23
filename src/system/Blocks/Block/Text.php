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

class Blocks_Block_Text extends Zikula_Controller_AbstractBlock
{
    /**
     * initialise block
     */
    public function init()
    {
        SecurityUtil::registerPermissionSchema('Textblock::', 'Block title::');
    }

    /**
     * get information on block
     *
     * @return array The block information
     */
    public function info()
    {
        return array('module'         => 'Blocks',
                     'text_type'      => $this->__('Text'),
                     'text_type_long' => $this->__('Plain text'),
                     'allow_multiple' => true,
                     'form_content'   => true,
                     'form_refresh'   => false,
                     'show_preview'   => true);
    }

    /**
     * display block
     *
     * @param  array  $blockinfo a blockinfo structure
     * @return output the rendered bock
     */
    public function display($blockinfo)
    {
        if (!SecurityUtil::checkPermission('Textblock::', "$blockinfo[title]::", ACCESS_OVERVIEW)) {
            return;
        }

        $blockinfo['content'] = nl2br($blockinfo['content']);

        return BlockUtil::themeBlock($blockinfo);
    }
}
