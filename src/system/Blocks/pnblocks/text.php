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

/**
 * initialise block
 *
 * @author       The Zikula Development Team
 */
function Blocks_textblock_init()
{
    // Security
    SecurityUtil::registerPermissionSchema('Textblock::', 'Block title::');
}

/**
 * get information on block
 *
 * @author       The Zikula Development Team
 * @return       array       The block information
 */
function Blocks_textblock_info()
{
    return array('module'         => 'Blocks',
                 'text_type'      => __('Text'),
                 'text_type_long' => __('Plain text'),
                 'allow_multiple' => true,
                 'form_content'   => true,
                 'form_refresh'   => false,
                 'show_preview'   => true);

}

/**
 * display block
 *
 * @author       The Zikula Development Team
 * @param        array       $blockinfo     a blockinfo structure
 * @return       output      the rendered bock
 */
function Blocks_textblock_display($blockinfo)
{
    if (!SecurityUtil::checkPermission('Textblock::', "$blockinfo[title]::", ACCESS_OVERVIEW)) {
        return;
    }

     // itevo: /Go; line breaks aren't displayed; added a nl2br() to fix this
    $blockinfo['content'] = nl2br($blockinfo['content']);
    // /itevo

    return BlockUtil::themeBlock($blockinfo);
}
