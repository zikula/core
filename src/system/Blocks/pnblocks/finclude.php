<?php
/**
 * Zikula Application Framework
 * @copyright (c) 2001, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_System_Modules
 * @subpackage Blocks
 * @author Patric Kellum
 */

/**
 * initialise block
 *
 * @author       The Zikula Development Team
 */
function Blocks_fincludeblock_init()
{
    // Security
    SecurityUtil::registerPermissionSchema('fincludeblock::', 'Block title::');
}

/**
 * get information on block
 *
 * @author       The Zikula Development Team
 * @return       array       The block information
 */
function Blocks_fincludeblock_info()
{
    return array('module'          => 'Blocks',
                 'text_type'       => __('Include'),
                 'text_type_long'  => __('Simple file include'),
                 'allow_multiple'  => true,
                 'form_content'    => false,
                 'form_refresh'    => false,
                 'show_preview'    => true,
                 'admin_tableless' => true);
}

/**
 * display block
 *
 * @author       The Zikula Development Team
 * @param        array       $blockinfo     a blockinfo structure
 * @return       output      the rendered bock
 */
function Blocks_fincludeblock_display($blockinfo)
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
            $blockinfo['content'] = __f("Error! The file '%s' was not found.", $vars['filo']);
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
 * @author       The Zikula Development Team
 * @param        array       $blockinfo     a blockinfo structure
 * @return       output      the bock form
 */
function Blocks_fincludeblock_modify($blockinfo)
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

    // Create output object - this object will store all of our output so that
    // we can return it easily when required
    $pnRender = Renderer::getInstance('Blocks');

    // assign the vars
    $pnRender->assign($vars);

    // return the output
    return $pnRender->fetch('blocks_block_finclude_modify.htm');
}

/**
 * update block settings
 *
 * @author       The Zikula Development Team
 * @param        array       $blockinfo     a blockinfo structure
 * @return       $blockinfo  the modified blockinfo structure
 */
function Blocks_fincludeblock_update($blockinfo)
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
