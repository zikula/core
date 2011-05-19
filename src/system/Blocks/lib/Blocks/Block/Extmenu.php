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

class Blocks_Block_Extmenu extends Zikula_Controller_AbstractBlock
{
    /**
     * initialise block
     */
    public function init()
    {
        SecurityUtil::registerPermissionSchema('ExtendedMenublock::', 'Block ID:Link ID:');
    }

    /**
     * get information on block
     *
     * @return       array       The block information
     */
    public function info()
    {
        return array('module'          => $this->name,
                     'text_type'       => $this->__('Extended menu'),
                     'text_type_long'  => $this->__('Extended menu block'),
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
    public function display($blockinfo)
    {
        // security check
        if (!SecurityUtil::checkPermission('ExtendedMenublock::', $blockinfo['bid'] . '::', ACCESS_READ)) {
            return;
        }

        // Break out options from our content field
        $vars = BlockUtil::varsFromContent($blockinfo['content']);

        // template to use
        if (empty($vars['template'])) {
            $vars['template'] = 'blocks_block_extmenu.tpl';
        }
        // stylesheet to use
        if (empty($vars['stylesheet'])) {
            $vars['stylesheet'] = 'extmenu.css';
        }

        // add the stylesheet to the header
        PageUtil::addVar('stylesheet', ThemeUtil::getModuleStylesheet('Blocks', $vars['stylesheet']));

        // if cache is enabled, checks for a cached output
        if ($this->view->getCaching()) {
            // set the cache id
            $this->view->setCacheId($blockinfo['bkey'].'/bid'.$blockinfo['bid'].'/'.UserUtil::getGidCacheString());

            // check out if the contents are cached
            if ($this->view->is_cached($vars['template'])) {
                $blockinfo['content'] = $this->view->fetch($vars['template']);
                return BlockUtil::themeBlock($blockinfo);
            }
        }

        
        // create default block variables
        if (!isset($vars['blocktitles'])) {
            $vars['blocktitles'] = array();
        }
        if (!isset($vars['links'])) {
            $vars['links'] = array();
        }
        if (!isset($vars['stylesheet'])) {
            $vars['stylesheet'] = '';
        }
        if (!isset($vars['menuid'])) {
            $vars['menuid'] = 0;
        }

        // get language and default to en
        $thislang = ZLanguage::getLanguageCode();
        if (!array_key_exists($thislang, $vars['links'])) {
            $thislang = 'en';
        }

        // if specific blocktitle for selected language exists, use it
        if (array_key_exists($thislang, $vars['blocktitles']) && !empty($vars['blocktitles'][$thislang])) {
            $blockinfo['title'] = $vars['blocktitles'][$thislang];
        }

        // Content
        $menuitems = array();
        if (!empty($vars['links'][$thislang])) {
            $blocked = array();
            foreach ($vars['links'][$thislang] as $linkid => $link) {
                $link['parentid'] = isset($link['parentid']) ? $link['parentid'] : null;
                $denied = !SecurityUtil::checkPermission('ExtendedMenublock::', $blockinfo['bid'] . ':' . $linkid . ':', ACCESS_READ);
                if ($denied || (!is_null($link['parentid']) && in_array($link['parentid'], $blocked))) {
                    $blocked[] = $linkid;
                } elseif (!isset($link['active']) || $link['active'] != '1') {
                    $blocked[] = $linkid;
                } else {
                    // pre zk1.2 check
                    if (!isset($link['id'])) {
                        $link['id'] = $linkid;
                    }
                    $link['url'] = ModUtil::apiFunc('Blocks', 'user', 'encodebracketurl', $link['url']);

                    // check for multiple options in image
                    $this->checkImage($link);
                    $menuitems[] = $link;
                }
            }
        }

        // Modules
        if (!empty($vars['displaymodules'])) {
            $newmods = ModUtil::getUserMods();
            $mods = array();
            foreach ($newmods as $module) {
                if (!preg_match('#(?:error|blocks)#', strtolower($module['name']))) {
                    $mods[] = $module;
                }
            }

            // Separate from current content, if any
            if (count($menuitems) > 0) {
                $menuitems[] = array('name'   => '&nbsp;',
                                     'url'    => '',
                                     'title'  => '',
                                     'level'  => 0,
                                     'parentid' => null,
                                     'image'  => '');

                if (SecurityUtil::checkPermission('ExtendedMenublock::', $blockinfo['bid'] . '::', ACCESS_ADMIN)) {
                    $menuitems[] = array('name'   => $this->__('--Installed modules--'),
                                         'url'    => ModUtil::url('Blocks', 'admin', 'modify', array('bid' => $blockinfo['bid'])),
                                         'title'  => '',
                                         'level'  => 0,
                                         'parentid' => null,
                                         'image'  => '');
                }
            }

            foreach($mods as $mod) {
                // prepare image

                if (SecurityUtil::checkPermission("$mod[name]::", '::', ACCESS_OVERVIEW)) {
                    $menuitems[] = array('name'   => $mod['displayname'],
                                         'url'    => ModUtil::url($mod['name'], 'user', 'main'),
                                         'title'  => $mod['description'],
                                         'level'  => 0,
                                         'parentid' => null,
                                         'image'  => '');
                }
            }
        }

        // check for any empty result set
        if (empty($menuitems)) {
            return;
        }

        $currenturi = urlencode(str_replace(System::getBaseUri() . '/', '', System::getCurrentUri()));

        // assign the items
        $this->view->assign('menuitems', $menuitems)
                   ->assign('blockinfo', $blockinfo)
                   ->assign('currenturi', $currenturi)
                   ->assign('access_edit', Securityutil::checkPermission('ExtendedMenublock::', $blockinfo['bid'] . '::', ACCESS_EDIT));

        // get the block content
        $blockinfo['content'] = $this->view->fetch($vars['template']);

        // pass the block array back to the theme for display
        return BlockUtil::themeBlock($blockinfo);
    }

    /**
     * do a simple check .. to see if the current URL is the menu item
     *
     * @param none
     * @return boolean
     */
    function is_recent_page($url)
    {
        if (!empty($url)) {
            $uri = System::getCurrentUri();
            if (is_integer(strpos($uri, $url))) {
                return true;
            }
        }
        return false;
    }

    /**
     * modify block settings
     *
     * @param        array       $blockinfo     a blockinfo structure
     * @return       output      the bock form
     */
    public function modify($blockinfo)
    {
        // Break out options from our content field
        $vars = BlockUtil::varsFromContent($blockinfo['content']);
        $blockinfo['content'] = '';

        // Defaults
        if (empty($vars['displaymodules'])) {
            $vars['displaymodules'] = 0;
        }

        // template to use
        if (empty($vars['template'])) {
            $vars['template'] = 'blocks_block_extmenu.tpl';
        }

        // create default block variables
        if (!isset($vars['blocktitles'])) {
            $vars['blocktitles'] = array();
        }
        if (!isset($vars['links'])) {
            $vars['links'] = array();
        }
        if (!isset($vars['stylesheet'])) {
            $vars['stylesheet'] = '';
        }
        if (!isset($vars['menuid'])) {
            $vars['menuid'] = 0;
        }

        $languages = ZLanguage::getInstalledLanguages();
        $userlanguage = ZLanguage::getLanguageCode();

        // filter out invalid languages
        foreach ($vars['blocktitles'] as $k => $v) {
            if (!in_array($k, $languages)) {
                unset($vars['blocktitles'][$k]);
                unset($vars['links'][$k]);
            }
        }

        // check if the users wants to add a new link via the "Add current url" link in the block
        $addurl = FormUtil::getPassedValue('addurl', 0, 'GET');
        // or if we come from the normal "edit this block" link
        $fromblock = FormUtil::getPassedValue('fromblock', null, 'GET');

        $redirect = '';
        if ($addurl == 1) {
            // set a marker for redirection later on
            $newurl = System::serverGetVar('HTTP_REFERER');
            $redirect = urlencode($newurl);
            $newurl = str_replace(System::getBaseUrl(), '', $newurl);
            if (empty($newurl)) {
                $newurl = System::getHomepageUrl();
            }
            foreach ($languages as $singlelanguage) {
                $vars['links'][$singlelanguage][] = array('name'   => $this->__('--New link--'),
                                                          'url'    => $newurl,
                                                          'title'  => $this->__('--New link--'),
                                                          'level'  => 0,
                                                          'parentid' => null,
                                                          'image'  => '',
                                                          'active' => 1);
            }
        } elseif (isset($fromblock)) {
            $redirect = urlencode(System::serverGetVar('HTTP_REFERER'));
        }

        // add new languages to the blocktitles and link arrays

        // we need to know which language has the most links, this language will be the "master"
        // for new languages to be added. this ensures that all links for the new language
        // are prepared.
        $link_master = array();
        foreach ($languages as $lang) {
            if (isset($vars['links'][$lang]) && count($link_master) < count($vars['links'][$lang])) {
                $link_master = $vars['links'][$lang];
            }
        }

        foreach ($languages as $lang) {
            // create an empty blocktitle string
            if (!array_key_exists($lang, $vars['blocktitles'])) {
                $vars['blocktitles'][$lang] = '';
            }
            if (!array_key_exists($lang, $vars['links'])) {
                $vars['links'][$lang] = $link_master;
            }
        }

        // menuitems are sorted by language per default for easier
        // access when showing them (which is more often necessary than
        // editing them), but for editing them we need them sorted by id
        $menuitems = array();
        foreach ($vars['links'] as $lang => $langlinks) {
            // langlinks now contains an array of links for a certain language
            // sorted by key=id
            foreach ($langlinks as $linkid => $link) {
                // pre zk1.2 check
                if (!isset($link['id'])) {
                    $link['id'] = $linkid;
                }
                $link['errors'] = array();
                $this->checkImage($link);
                $menuitems[$linkid][$lang] = $link;
            }
        }
        $vars['links'] = $menuitems;

        $this->view->setCaching(Zikula_View::CACHE_DISABLED);

        // assign the vars
        $this->view->assign($vars)
                   ->assign('languages', $languages)
                   ->assign('userlanguage', $userlanguage)
                   ->assign('redirect', $redirect)
                   ->assign('blockinfo', $blockinfo);

        // return the output
        return $this->view->fetch('blocks_block_extmenu_modify.tpl');
    }

    /**
     * update block settings
     *
     * @param        array       $blockinfo     a blockinfo structure
     * @return       $blockinfo  the modified blockinfo structure
     */
    public function update($blockinfo)
    {
        $vars['displaymodules'] = FormUtil::getPassedValue('displaymodules');
        $vars['stylesheet']     = FormUtil::getPassedValue('stylesheet');
        $vars['template']       = FormUtil::getPassedValue('template');
        $vars['blocktitles']    = FormUtil::getPassedValue('blocktitles');

        // Defaults
        if (empty($vars['displaymodules'])) {
            $vars['displaymodules'] = 0;
        }

        if (empty($vars['template'])) {
            $vars['template'] = 'blocks_block_extmenu.tpl';
        }

        if (empty($vars['stylesheet'])) {
            $vars['stylesheet'] = 'extmenu.css';
        }

        // User links
        $content = array();

        $vars['links'] = FormUtil::getPassedValue('links');
        $vars['blockversion'] = 1;

        // Save links hierarchy
        $linksorder = FormUtil::getPassedValue('linksorder');
        $linksorder = json_decode($linksorder, true);
        if (is_array($linksorder) && !empty($linksorder)) {
            foreach ((array)$vars['links'] as $lang => $langlinks) {
                foreach ($langlinks as $linkid => $link) {
                    $vars['links'][$lang][$linkid]['parentid'] = $linksorder[$linkid]['parentid'];
                    $vars['links'][$lang][$linkid]['haschildren'] = $linksorder[$linkid]['haschildren'];
                }
            }
        }

        $blockinfo['content'] = BlockUtil::varsToContent($vars);

        // clear the block cache
        $this->view->clear_cache(null, $blockinfo['bkey'].'/bid'.$blockinfo['bid']);

        // and clear the theme cache
        Zikula_View_Theme::getInstance()->clear_cache();

        return $blockinfo;
    }

    protected function checkImage(&$link)
    {
        if (!empty($link['image'])) {
            $osimg = DataUtil::formatForOS($link['image']);
            if (is_readable($osimg)) {
                $link['image'] = $osimg;
                $link['imagedata'] = @getimagesize($osimg);
            } else {
                $link['errors'][] = DataUtil::formatForDisplay($link['image']) . ': invalid image data';
                $link['image'] = '';
                $link['imagedata'] = false;
            }
        }
        return;
    }
}
