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

namespace Zikula\Module\SearchModule\Block;

use SecurityUtil;
use BlockUtil;
use ModUtil;
use Zikula_View;
use Zikula_View_Theme;

/**
 * Block to display a search form
 */
class SearchBlock extends \Zikula_Controller_AbstractBlock
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
        $blockinfo['content'] = $this->view->fetch('Block/search.tpl');

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
     * @return string the blocks custom form element
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
        $search_modules = ModUtil::apiFunc('ZikulaSearchModule', 'user', 'getallplugins');

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

        return $this->view->fetch('Block/search_modify.tpl');
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
     * @return array the modified blockinfo structure
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
        $this->view->clear_cache('Block/search.tpl');

        // and clear the theme cache
        Zikula_View_Theme::getInstance()->clear_cache();

        return($blockinfo);
    }
}