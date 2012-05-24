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

/**
 * Search_Block_Search class.
 */
class Search_Block_Search extends Zikula_Controller_AbstractBlock
{
    /**
     * initialise block
     */
    public function init()
    {
        SecurityUtil::registerPermissionSchema('Searchblock::', 'Block title::');
    }

    /**
     * get information on block
     *
     * @return array The block information
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
     * @param  array  $blockinfo a blockinfo structure
     * @return output the rendered bock
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

        if (!isset($vars['active'])) {
            $vars['active'] = array();
        }

        // set a title if one isn't present
        if (empty($blockinfo['title'])) {
            $blockinfo['title'] = __('Search');
        }

        $plugin_options = array();

        foreach (array_keys($vars['active']) as $mod) {
            $plugin_options[$mod] = ModUtil::apiFunc($mod, 'search', 'options', $vars);
        }

        // assign the block vars and the plgin options
        $this->view->assign('vars', $vars)
                   ->assign('plugin_options', $plugin_options);

        // return the rendered block
        $blockinfo['content'] = $this->view->fetch('search_block_search.tpl');

        return BlockUtil::themeBlock($blockinfo);
    }

    /**
     * modify block settings
     *
     * @param  array  $blockinfo a blockinfo structure
     * @return output the bock form
     */
    public function modify($blockinfo)
    {
        // Get current content
        $vars = BlockUtil::varsFromContent($blockinfo['content']);

        // set some defaults
        if (!isset($vars['displaySearchBtn'])) {
            $vars['displaySearchBtn'] = 0;
        }

        if (!isset($vars['active'])) {
            $vars['active'] = array();
        }

        // get all the search plugins
        $search_modules = ModUtil::apiFunc('Search', 'user', 'getallplugins');

        $searchmodules = array();
        if (is_array($search_modules)) {
            foreach ($search_modules as $mods) {
                $searchmodules[] = array('module' => ModUtil::apiFunc($mods['title'], 'Search', 'options', $vars));
            }
        }

        $this->view->setCaching(Zikula_View::CACHE_DISABLED);

        // assign the block vars array
        $this->view->assign('searchvars', $vars)
                   ->assign('searchmodules', $searchmodules);

        return $this->view->fetch('search_block_search_modify.tpl');
    }

    /**
     * update block settings
     *
     * @param  array $blockinfo a blockinfo structure
     * @return       $blockinfo  the modified blockinfo structure
     */
    public function update($blockinfo)
    {
        // list of vars that don't need to be saved
        $search_reserved_vars = array('authid', 'csrftoken', 'bid', 'title', 'positions', 'language', 'submit',
                                      'refresh', 'filter', 'type', 'functions', 'customargs');

        // Get current content
        $vars = BlockUtil::varsFromContent($blockinfo['content']);

        foreach ($_POST as $key => $value) {
            if (in_array($key, $search_reserved_vars)) {
                continue;
            }
            $vars[$key] = $value;
        }

        // write back the new contents
        $blockinfo['content'] = BlockUtil::varsToContent($vars);

        // clear the block cache
        $this->view->clear_cache('search_block_search.tpl');

        // and clear the theme cache
        Zikula_View_Theme::getInstance()->clear_cache();

        return($blockinfo);
    }
}
