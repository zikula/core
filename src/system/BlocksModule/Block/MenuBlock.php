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
use ModUtil;
use System;
use DataUtil;
use Zikula_View_Theme;

/**
 * Simple list menu block
 */
class MenuBlock extends \Zikula_Controller_AbstractBlock
{
    /**
     * initialise block
     *
     * @return void
     */
    public function init()
    {
        SecurityUtil::registerPermissionSchema('Menublock::', 'Block title:Link name:');
    }

    /**
     * get information on block
     *
     * @return array The block information
     */
    public function info()
    {
        return [
            'module'          => $this->name,
            'text_type'       => $this->__('Menu'),
            'text_type_long'  => $this->__('Menu block'),
            'allow_multiple'  => true,
            'form_content'    => false,
            'form_refresh'    => false,
            'show_preview'    => true,
            'admin_tableless' => true
        ];
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
        if (!SecurityUtil::checkPermission('Menublock::', "$blockinfo[title]::", ACCESS_READ)) {
            return;
        }

        // Break out options from our content field
        $vars = BlockUtil::varsFromContent($blockinfo['content']);

        // add the stylesheet to the header
        if (isset($vars['stylesheet'])) {
            PageUtil::addVar('stylesheet', ThemeUtil::getModuleStyleSheet('ZikulaBlocksModule', $vars['stylesheet']));
        }

        // if cache is enabled, checks for a cached output
        if ($this->view->getCaching()) {
            // set the cache id
            $this->view->setCacheId($blockinfo['bkey'].'/bid'.$blockinfo['bid'].'/'.UserUtil::getGidCacheString());

            // check out if the contents are cached
            if ($this->view->is_cached('Block/menu.tpl')) {
                $blockinfo['content'] = $this->view->fetch('Block/menu.tpl');

                return BlockUtil::themeBlock($blockinfo);
            }
        }

        // Styling - this is deprecated and is only to support old menu for now
        if (empty($vars['style'])) {
            $vars['style'] = 1;
        }

        // Content
        $menuitems = [];
        if (!empty($vars['content'])) {
            $contentlines = explode('LINESPLIT', $vars['content']);
            foreach ($contentlines as $contentline) {
                list($url, $title, $comment) = explode('|', $contentline);
                if (SecurityUtil::checkPermission('Menublock::', "$blockinfo[title]:$title:", ACCESS_READ)) {
                    $menuitems[] = self::addMenuItem($title, $url, $comment);
                    $content = true;
                }
            }
        }

        // Modules
        if (!empty($vars['displaymodules'])) {
            $mods = ModUtil::getModulesCapableOf('user');

            // Separate from current content, if any
            if ($vars['content'] == 1) {
                $menuitems[] = self::addMenuItem('', '', '');
            }

            foreach ($mods as $mod) {
                if (SecurityUtil::checkPermission("$mod[name]::", '::', ACCESS_OVERVIEW)) {
                    $url = isset($mod['capabilities']['user']['url'])
                        ? $mod['capabilities']['user']['url']
                        : $this->get('router')->generate($mod['capabilities']['user']['route']);
                    $menuitems[] = self::addMenuItem($mod['displayname'], $url, $mod['description']);
                    $content = true;
                }
            }
        }

        // check for any empty result set
        if (empty($menuitems)) {
            return;
        }

        // assign the items
        $this->view->assign('menuitems', $menuitems);

        // get the block content
        $blockinfo['content'] = $this->view->fetch('Block/menu.tpl');

        // pass the block array back to the theme for display
        return BlockUtil::themeBlock($blockinfo);
    }

    /**
     * Prepare a menu item array
     *
     * @param $title   menu item title
     * @param $url     menu item url
     * @param $comment menu item comment
     *
     * @return array the prepared array
     */
    public function addMenuItem($title, $url, $comment)
    {
        static $uri;

        if (!isset($uri)) {
            $uri = System::getCurrentUri();
        }

        if (!isset($title) || $title == '') {
            $title = '&nbsp;';
        }

        $itemselected = false;
        // do a simple check .. to see if the current URL is the menu item
        if (!empty($url)) {
            if (is_int(strpos($uri, $url))) {
                $itemselected = true;
            }
        }

        // allow a simple portable way to link to the home page of the site
        if ($url == '{homepage}') {
            $url = System::getBaseUrl();
        } elseif (!empty($url)) {
            if ($url[0] == '{') {
                $url = explode(':', substr($url, 1, -1));

                // url[0] should be the module name
                if (isset($url[0]) && !empty($url[0])) {
                    $modname = $url[0];

                    // default values
                    $type = 'user';
                    $func = 'index';
                    $params = [];

                    // url[2] can be a function or function&param=value
                    if (isset($url[2]) && !empty($url[2])) {
                        $urlparts = explode('&', $url[2]);
                        $func = $urlparts[0];
                        unset($urlparts[0]);
                        if (count($urlparts) > 0) {
                            foreach ($urlparts as $urlpart) {
                                $part = explode('=', $urlpart);
                                $params[trim($part[0])] = trim($part[1]);
                            }
                        }
                        // addon: url[2] can be the type parameter, default 'user'
                        $type = (isset($url[1]) && !empty($url[1])) ? $url[1] : 'user';
                    } else {
                        $capabilities = ModUtil::getCapabilitiesOf($modname);
                        if (!empty($capabilities) && isset($capabilities['user']['route'])) {
                            $route = $this->get('router')->generate($capabilities['user']['route']);
                        }
                    }

                    //  build the url
                    $url = isset($route) ? $route : ModUtil::url($modname, $type, $func, $params);
                }
            }
        }

        $item = [
            'MENUITEMTITLE'    => $title,
            'MENUITEMURL'      => $url,
            'MENUITEMCOMMENT'  => DataUtil::formatForDisplay($comment),
            'MENUITEMSELECTED' => $itemselected
        ];

        return $item;
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
        if (empty($vars['style'])) {
            $vars['style'] = 1;
        }
        // template to use
        if (empty($vars['template'])) {
            $vars['template'] = 'menu';
        }
        // stylesheet to use
        if (empty($vars['stylesheet'])) {
            $vars['stylesheet'] = '';
        }
        // display modules
        if (empty($vars['displaymodules'])) {
            $vars['displaymodules'] = false;
        }

        // assign the vars
        $this->view->assign($vars);

        $menuitems = [];
        if (!empty($vars['content'])) {
            $contentlines = explode('LINESPLIT', $vars['content']);
            foreach ($contentlines as $contentline) {
                $link = explode('|', $contentline);
                $menuitems[] = $link;
            }
        }
        $this->view->assign('menuitems', $menuitems);

        // return the output
        return $this->view->fetch('Block/menu_modify.tpl');
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
     * @return       $blockinfo  the modified blockinfo structure
     */
    public function update($blockinfo)
    {
        $vars['displaymodules'] = $this->request->request->get('displaymodules');
        $vars['style']          = $this->request->request->get('style');
        $vars['stylesheet']     = $this->request->request->get('stylesheet');

        // Defaults
        if (empty($vars['displaymodules'])) {
            $vars['displaymodules'] = 0;
        }
        if (empty($vars['style'])) {
            $vars['style'] = 1;
        }
        if (empty($vars['template'])) {
            $vars['template'] = 'menu';
        }

        // User links
        $content = [];
        $c = 1;

        $linkname   = $this->request->request->get('linkname');
        $linkurl    = $this->request->request->get('linkurl');
        $linkdesc   = $this->request->request->get('linkdesc');
        $linkdelete = $this->request->request->get('linkdelete');
        $linkinsert = $this->request->request->get('linkinsert');

        if (isset($linkname)) {
            foreach ($linkname as $v) {
                if (empty($linkdelete[$c])) {
                    $content[] = "$linkurl[$c]|$linkname[$c]|$linkdesc[$c]";
                }
                if (!empty($linkinsert[$c])) {
                    $content[] = '||';
                }
                $c++;
            }
        }

        $new_linkname = $this->request->request->get('new_linkname');
        $new_linkurl  = $this->request->request->get('new_linkurl');
        $new_linkdesc = $this->request->request->get('new_linkdesc');
        $new_linkinsert = (bool)$this->request->request->get('new_linkinsert');

        if ($new_linkname) {
            $content[] = $new_linkurl . '|' . $new_linkname . '|' . $new_linkdesc;
            if ($new_linkinsert) {
                $content[] = '||';
            }
        }
        $vars['content'] = implode('LINESPLIT', $content);

        $blockinfo['content'] = BlockUtil::varsToContent($vars);

        // clear the block cache
        $this->view->clear_cache('Block/menu.tpl');

        // and clear the theme cache
        Zikula_View_Theme::getInstance()->clear_cache();

        return $blockinfo;
    }
}
