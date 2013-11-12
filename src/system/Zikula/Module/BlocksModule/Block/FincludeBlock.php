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
use DataUtil;
use Zikula_View;
use FormUtil;

/**
 * Block to display the contents of a file
 */
class FincludeBlock extends \Zikula_Controller_AbstractBlock
{
    /**
     * initialise block
     *
     * @return void
     */
    public function init()
    {
        SecurityUtil::registerPermissionSchema('fincludeblock::', 'Block title::');
    }

    /**
     * get information on block
     *
     * @return array The block information
     */
    public function info()
    {
        return array('module'          => 'ZikulaBlocksModule',
                     'text_type'       => $this->__('Include'),
                     'text_type_long'  => $this->__('Simple file include'),
                     'allow_multiple'  => true,
                     'form_content'    => false,
                     'form_refresh'    => false,
                     'show_preview'    => true,
                     'admin_tableless' => true);
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
        if (!SecurityUtil::checkPermission('fincludeblock::', "$blockinfo[title]::", ACCESS_READ)) {
            return;
        }

        // Get current content
        $vars = BlockUtil::varsFromContent($blockinfo['content']);

        // Defaults
        if (empty($vars['filo'])) {
            $vars['filo'] = 'relative/path/to/file.txt';
        }
        if (empty($vars['typo'])) {
            $vars['typo'] = 0;
        }

        if (!file_exists($vars['filo'])) {
            if (SecurityUtil::checkPermission('fincludeblock::', "$blockinfo[title]::", ACCESS_EDIT)) {
                $blockinfo['content'] = $this->__f("Error! The file '%s' was not found.", $vars['filo']);

                return BlockUtil::themeBlock($blockinfo);
            } else {
                return;
            }
        }

        $blockinfo['content'] = '';
        switch ($vars['typo']) {
            case 0:
                $blockinfo['content'] = /*nl2br(*/file_get_contents($vars['filo'])/*)*/;    // #155 (Blocktype finclude creates not needed line breaks)
                break;
            case 1:
                $blockinfo['content'] = DataUtil::formatForDisplay(file_get_contents($vars['filo']));
                break;
            case 2:
                ob_start();
                include DataUtil::formatForOS($vars['filo']);
                $blockinfo['content'] = ob_get_clean();
                break;
            default:
                return;
        }

        return BlockUtil::themeBlock($blockinfo);
    }

    /**
     * modify block settings
     *
     * @param mixed[] $blockinfo {
     *      @type string $title   the title of the block
     *      @type int    $bid     the id of the block
     *      @type string $content the seralized block content array
     *                            }
     *
     * @return string the bock form
     */
    public function modify($blockinfo)
    {
        // Get current content
        $vars = BlockUtil::varsFromContent($blockinfo['content']);

        // Defaults
        if (empty($vars['filo'])) {
            $vars['filo'] = '/path/to/file.txt';
        }
        if (empty($vars['typo'])) {
            $vars['typo'] = 0;
        }

        $this->view->setCaching(Zikula_View::CACHE_DISABLED);

        // assign the vars
        $this->view->assign($vars);

        // return the output
        return $this->view->fetch('blocks_block_finclude_modify.tpl');
    }

    /**
     * update block settings
     *
     * @param mixed[] $blockinfo {
     *      @type string $title   the title of the block
     *      @type int    $bid     the id of the block
     *      @type string $content the seralized block content array
     *                            }
     *
     * @return array $blockinfo  the modified blockinfo structure
     */
    public function update($blockinfo)
    {
        // Get current content
        $vars = BlockUtil::varsFromContent($blockinfo['content']);

        // alter the corresponding variable
        $vars['filo'] = FormUtil::getPassedValue('filo');
        $vars['typo'] = FormUtil::getPassedValue('typo');

        // write back the new contents
        $blockinfo['content'] = BlockUtil::varsToContent($vars);

        return $blockinfo;
    }
}