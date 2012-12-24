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

class Blocks_Block_Html extends Zikula_Controller_AbstractBlock
{
    /**
     * initialise block
     */
    public function init()
    {
        SecurityUtil::registerPermissionSchema('HTMLblock::', 'Block title::');
    }

    /**
     * get information on block
     *
     * @return array The block information
     */
    public function info()
    {
        return array('module'         => 'Blocks',
                     'text_type'      => $this->__('HTML'),
                     'text_type_long' => $this->__('HTML'),
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
        if (!SecurityUtil::checkPermission('HTMLblock::', "$blockinfo[title]::", ACCESS_OVERVIEW)) {
            return;
        }

        return BlockUtil::themeBlock($blockinfo);
    }
}
