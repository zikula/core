<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2004, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_System_Modules
 * @subpackage Blocks
 */

/**
 * initialise block
 */
function Search_searchblock_init()
{
    // Security
    pnSecAddSchema('Searchblock::', 'Block title::');
}

/**
 * get information on block
 *
 * @return       array       The block information
 */
function Search_searchblock_info()
{
    return array('module'          => 'Search',
                 'text_type'       => __('Search'),
                 'text_type_long'  => __('Search box'),
                 'allow_multiple'  => true,
                 'form_content'    => false,
                 'form_refresh'    => false,
                 'show_preview'    => true,
                 'admin_tableless' => true);
}

/**
 * display block
 *
 * @param        array       $blockinfo     a blockinfo structure
 * @return       output      the rendered bock
 */
function Search_searchblock_display($blockinfo)
{
    // Security check
    if (!SecurityUtil::checkPermission('Searchblock::', "$blockinfo[title]::", ACCESS_READ)) {
        return;
    }

    // Get current content
    $vars = pnBlockVarsFromContent($blockinfo['content']);

    // Create output object
    $pnRender = & pnRender::getInstance('Search');

    // set some defaults
    if (!isset($vars['displaySearchBtn'])) {
        $vars['displaySearchBtn'] = 0;
    }

    // add the module vars
    $vars['modvar'] = ModUtil::getVar('Search');

    // assign the block vars array
    $pnRender->assign('vars',$vars);

    // set a title if one isn't present
    if (empty($blockinfo['title'])) {
        $blockinfo['title'] = __('Search');
    }

    // return the rendered block
    $blockinfo['content'] = $pnRender->fetch('search_block_search.htm');
    return pnBlockThemeBlock($blockinfo);
}

/**
 * modify block settings
 *
 * @param        array       $blockinfo     a blockinfo structure
 * @return       output      the bock form
 */
function Search_searchblock_modify($blockinfo)
{
    // Get current content
    $vars = pnBlockVarsFromContent($blockinfo['content']);

    // get all the search plugins
    $search_modules = pnModAPIFunc('Search', 'user', 'getallplugins');

    // set some defaults
    if (!isset($vars['displaySearchBtn'])) {
        $vars['displaySearchBtn'] = 0;
    }

    $searchmodules = array();
    if (is_array($search_modules)) {
        foreach($search_modules as $mods) {
            $searchmodules[] = array('module' => pnModAPIFunc($mods['title'], 'Search', 'options', $vars));
        }
    }

    // Create output object
    $pnRender = & pnRender::getInstance('Search');

    // assign the block vars array
    $pnRender->assign('searchvars', $vars);
    $pnRender->assign('searchmodules', $searchmodules);

    return $pnRender->fetch('search_block_search_modify.htm');
}

/**
 * update block settings
 *
 * @param        array       $blockinfo     a blockinfo structure
 * @return       $blockinfo  the modified blockinfo structure
 */
function Search_searchblock_update($blockinfo)
{
    // list of vars that don't need to be saved
    $search_reserved_vars = array('authid', 'bid', 'title', 'positions', 'language', 'submit',
                                  'refresh', 'filter', 'type', 'functions', 'customargs');

    // Get current content
    $vars = pnBlockVarsFromContent($blockinfo['content']);

    foreach($_POST as $key => $value) {
        if (in_array($key, $search_reserved_vars)) {
            continue;
        }
        $vars[$key] = $value;
    }

    // write back the new contents
    $blockinfo['content'] = pnBlockVarsToContent($vars);

    // clear the block cache
    $pnRender = & pnRender::getInstance('Search');
    $pnRender->clear_cache('search_block_search.htm');

    return($blockinfo);
}
