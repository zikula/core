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

namespace Zikula\BlocksModule\Block;

use SecurityUtil;
use BlockUtil;

/**
 * Block to display simple rendered text
 */
class TextBlock extends \Zikula_Controller_AbstractBlock
{
    /**
     * initialise block
     *
     * @return void
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
        return array('module'         => 'ZikulaBlocksModule',
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
     * @param mixed[] $blockinfo {
     *      @type string $title   the title of the block
     *      @type int    $bid     the id of the block
     *      @type string $content the seralized block content array
     *                            }
     *
     * @return string the rendered bock
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
