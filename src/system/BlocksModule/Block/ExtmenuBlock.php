<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\BlocksModule\Block;

use SecurityUtil;
use BlockUtil;
use ThemeUtil;
use PageUtil;
use UserUtil;
use ZLanguage;
use ModUtil;
use System;
use Zikula_View;
use Zikula_View_Theme;
use DataUtil;

/**
 * Block to display an extended menu
 */
class ExtmenuBlock extends \Zikula_Controller_AbstractBlock
{
    /**
     * initialise block
     *
     * @return void
     */
    public function init()
    {
        SecurityUtil::registerPermissionSchema('ExtendedMenublock::', 'Block ID:Link ID:');
    }

    /**
     * get information on block
     *
     * @return array The block information
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
        // security check
        if (!SecurityUtil::checkPermission('ExtendedMenublock::', $blockinfo['bid'] . '::', ACCESS_READ)) {
            return;
        }

        // Break out options from our content field
        $vars = BlockUtil::varsFromContent($blockinfo['content']);

        // template to use
        if (empty($vars['template'])) {
            $vars['template'] = 'Block/Extmenu/extmenu.tpl';
        }
        // stylesheet to use
        if (empty($vars['stylesheet'])) {
            $vars['stylesheet'] = 'extmenu.css';
        }

        // add the stylesheet to the header
        PageUtil::addVar('stylesheet', ThemeUtil::getModuleStylesheet('ZikulaBlocksModule', $vars['stylesheet']));

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
                    $link['url'] = ModUtil::apiFunc('ZikulaBlocksModule', 'user', 'encodebracketurl', $link['url']);

                    // check for multiple options in image
                    $this->checkImage($link);
                    $menuitems[$link['url']] = $link;
                }
            }
        }

        // Modules
        if (!empty($vars['displaymodules'])) {
            $newmods = ModUtil::getModulesCapableOf('user');
            $mods = array();
            foreach ($newmods as $module) {
                if (!preg_match('#(?:error|blocks)#', strtolower($module['name']))) {
                    $mods[] = $module;
                }
            }

            foreach ($mods as $mod) {
                $url = isset($mod['capabilities']['user']['route'])
                    ? $this->get('router')->generate($mod['capabilities']['user']['route'])
                    : $mod['capabilities']['user']['url'];
                if (SecurityUtil::checkPermission("$mod[name]::", '::', ACCESS_OVERVIEW)
                    && (empty($menuitems[$url]))) {
                    $menuitems[$url] = array('name'   => $mod['displayname'],
                                         'url'    => $url,
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
     * Check to see if the current URL is the menu item
     *
     * @param string $url The url to check
     *
     * @return boolean
     */
    public function is_recent_page($url)
    {
        if (!empty($url)) {
            $uri = System::getCurrentUri();
            if (is_int(strpos($uri, $url))) {
                return true;
            }
        }

        return false;
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
        // Break out options from our content field
        $vars = BlockUtil::varsFromContent($blockinfo['content']);
        $blockinfo['content'] = '';

        // Defaults
        if (empty($vars['displaymodules'])) {
            $vars['displaymodules'] = 0;
        }

        // template to use
        if (empty($vars['template'])) {
            $vars['template'] = 'Block/Extmenu/extmenu.tpl';
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
        $addurl = $this->request->request->get('addurl', 0);
        // or if we come from the normal "edit this block" link
        $fromblock = $this->request->request->get('fromblock', null);

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
        return $this->view->fetch('Block/Extmenu/modify.tpl');
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
        $vars['displaymodules'] = $this->request->request->get('displaymodules');
        $vars['stylesheet']     = $this->request->request->get('stylesheet');
        $vars['template']       = $this->request->request->get('template');
        $vars['blocktitles']    = $this->request->request->get('blocktitles');

        // Defaults
        if (empty($vars['displaymodules'])) {
            $vars['displaymodules'] = 0;
        }

        if (empty($vars['template'])) {
            $vars['template'] = 'Block/Extmenu/extmenu.tpl';
        }

        if (empty($vars['stylesheet'])) {
            $vars['stylesheet'] = 'extmenu.css';
        }

        // User links
        $content = array();

        $vars['links'] = $this->request->request->get('links');
        $vars['blockversion'] = 1;

        // Save links hierarchy
        $linksorder = $this->request->request->get('linksorder');
        $linksorder = json_decode($linksorder, true);
        if (is_array($linksorder) && !empty($linksorder)) {
            foreach ((array)$vars['links'] as $lang => $langlinks) {
                foreach ($langlinks as $linkid => $link) {
                    $vars['links'][$lang][$linkid]['parentid'] = isset($linksorder[$linkid]['parentid']) ? $linksorder[$linkid]['parentid'] : null;
                    $vars['links'][$lang][$linkid]['haschildren'] = isset($linksorder[$linkid]['haschildren']) && is_bool($linksorder[$linkid]['haschildren']) ? $linksorder[$linkid]['haschildren'] : false;
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

    /**
     * helper function validate an image link
     *
     * @param string $link path to image
     *
     * @return void
     */
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
