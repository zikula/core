<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2004, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id: search.php 27363 2009-11-02 16:40:08Z mateo $
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_System_Modules
 * @subpackage Blocks
 */

class Search_Block_Search extends Zikula_Block
{
    /**
     * initialise block
     *
     * @author       The Zikula Development Team
     */
    public function init()
    {
        // Security
        SecurityUtil::registerPermissionSchema('Searchblock::', 'Block title::');
    }

    /**
     * get information on block
     *
     * @author       The Zikula Development Team
     * @return       array       The block information
     */
    public function info()
    {
        return array('module'          => 'Search',
                'text_type'       => $this->__('Search'),
                'text_type_long'  => $this->__('Search box'),
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
    public function display($blockinfo)
    {
        // Security check
        if (!SecurityUtil::checkPermission('Searchblock::', "$blockinfo[title]::", ACCESS_READ)) {
            return;
        }

        // Get current content
        $vars = BlockUtil::varsFromContent($blockinfo['content']);

        // set some defaults
        if (!isset($vars['displaySearchBtn'])) {
            $vars['displaySearchBtn'] = 0;
        }

        // add the module vars
        $vars['modvar'] = $this->getVars();
        $vars['active'] = FormUtil::getPassedValue('active', SessionUtil::getVar('searchactive'), 'GETPOST');

        // assign the block vars array
        $this->view->assign('vars', $vars);

        // set a title if one isn't present
        if (empty($blockinfo['title'])) {
            $blockinfo['title'] = __('Search');
        }

        // return the rendered block
        $blockinfo['content'] = $this->view->fetch('search_block_search.tpl');
        return BlockUtil::themeBlock($blockinfo);
    }

    /**
     * modify block settings
     *
     * @author       The Zikula Development Team
     * @param        array       $blockinfo     a blockinfo structure
     * @return       output      the bock form
     */
    public function modify($blockinfo)
    {
        // Get current content
        $vars = BlockUtil::varsFromContent($blockinfo['content']);

        // get all the search plugins
        $search_modules = ModUtil::apiFunc('Search', 'user', 'getallplugins');

        // set some defaults
        if (!isset($vars['displaySearchBtn'])) {
            $vars['displaySearchBtn'] = 0;
        }

        $searchmodules = array();
        if (is_array($search_modules)) {
            foreach($search_modules as $mods) {
                $searchmodules[] = array('module' => ModUtil::apiFunc($mods['title'], 'Search', 'options', $vars));
            }
        }

        $this->view->setCaching(false);

        // assign the block vars array
        $this->view->assign('searchvars', $vars)
                       ->assign('searchmodules', $searchmodules);

        return $this->view->fetch('search_block_search_modify.tpl');
    }

    /**
     * update block settings
     *
     * @author       The Zikula Development Team
     * @param        array       $blockinfo     a blockinfo structure
     * @return       $blockinfo  the modified blockinfo structure
     */
    function update($blockinfo)
    {
        // list of vars that don't need to be saved
        $search_reserved_vars = array('authid', 'bid', 'title', 'positions', 'language', 'submit',
                'refresh', 'filter', 'type', 'functions', 'customargs');

        // Get current content
        $vars = BlockUtil::varsFromContent($blockinfo['content']);

        foreach($_POST as $key => $value) {
            if (in_array($key, $search_reserved_vars)) {
                continue;
            }
            $vars[$key] = $value;
        }

        // write back the new contents
        $blockinfo['content'] = BlockUtil::varsToContent($vars);

        // clear the block cache
        $this->view->clear_cache('search_block_search.tpl');

        return($blockinfo);
    }
}