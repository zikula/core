<?php
/**
 * Zikula Application Framework
 * @copyright (c) 2001, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_System_Modules
 * @subpackage Blocks
 */

class Blocks_Block_Text extends Zikula_Block
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
     * @return       array       The block information
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
     * @param        array       $blockinfo     a blockinfo structure
     * @return       output      the rendered bock
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
