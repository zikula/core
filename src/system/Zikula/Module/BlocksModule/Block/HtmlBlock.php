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

namespace Zikula\Module\BlocksModule\Block;

use SecurityUtil;
use BlockUtil;

/**
 * Block to display html 
 */
class HtmlBlock extends \Zikula_Controller_AbstractBlock
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
        return array('module'         => 'ZikulaBlocksModule',
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
     * @param mixed[] $blockinfo {<ul>
     *      <li>@type string $title   the title of the block</li>
     *      <li>@type int    $bid     the id of the block</li>
     *      <li>@type string $content the seralized block content array</li>
     *                            </ul>}
     *
     * @return string the rendered bock
     */
    public function display($blockinfo)
    {
        if (!SecurityUtil::checkPermission('HTMLblock::', "$blockinfo[title]::", ACCESS_OVERVIEW)) {
            return;
        }

        return BlockUtil::themeBlock($blockinfo);
    }
}