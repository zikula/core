<?php
/**
 * Zikula Application Framework
 * @copyright (c) 2001, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_System_Modules
 * @subpackage Blocks
 * @author Brian Lindner
 */

class Blocks_Block_Menu extends AbstractBlock
{
    /**
     * initialise block
     *
     * @author       The Zikula Development Team
     */
    public function init()
    {
        // Security
        SecurityUtil::registerPermissionSchema('Menublock::', 'Block title:Link name:');
    }


    /**
     * get information on block
     *
     * @author       The Zikula Development Team
     * @return       array       The block information
     */
    public function info()
    {
        return array('module' => 'Blocks',
                'text_type' => $this->__('Menu'),
                'text_type_long' => $this->__('Menu block'),
                'allow_multiple' => true,
                'form_content' => false,
                'form_refresh' => false,
                'show_preview' => true,
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
        // security check
        if (!SecurityUtil::checkPermission('Menublock::', "$blockinfo[title]::", ACCESS_READ)) {
            return;
        }

        // setup the renderer for this block
        $pnRender = Renderer::getInstance('Blocks');

        // Set the cache id
        $pnRender->cache_id = $blockinfo['bid'].':'.UserUtil::getVar('uid');

        // Break out options from our content field
        $vars = BlockUtil::varsFromContent($blockinfo['content']);

        // check out if the contents are cached.
        if ($pnRender->is_cached('blocks_block_menu.htm')) {
            // Populate block info and pass to theme
            $blockinfo['content'] = $pnRender->fetch('blocks_block_menu.htm');
            return BlockUtil::themeBlock($blockinfo);
        }

        // Styling - this is deprecated and is only to support old menu for now
        if (empty($vars['style'])) {
            $vars['style'] = 1;
        }

        // Content
        $menuitems = array();
        if (!empty($vars['content'])) {
            $contentlines = explode('LINESPLIT', $vars['content']);
            foreach ($contentlines as $contentline) {
                list($url, $title, $comment) = explode('|', $contentline);
                if (SecurityUtil::checkPermission('Menublock::', "$blockinfo[title]:$title:", ACCESS_READ)) {
                    $menuitems[] = blocks_menu_addMenuItem($title, $url, $comment);
                    $content = true;
                }
            }
        }

        // Modules
        if (!empty($vars['displaymodules'])) {
            $mods = pnModGetUserMods();

            // Separate from current content, if any
            if ($vars['content'] == 1) {
                $menuitems[] = blocks_menu_addMenuItem('', '', '');
            }

            foreach($mods as $mod) {
                if (SecurityUtil::checkPermission("$mod[name]::", '::', ACCESS_OVERVIEW)) {
                    switch($mod['type']) {
                        case 1:
                            $menuitems[] = blocks_menu_addMenuItem($mod['displayname'],
                                    System::getVar('entrypoint', 'index.php') . '?name=' . DataUtil::formatForDisplay($mod['directory']),
                                    $mod['description']);
                            $content = true;
                            break;
                        case 2:
                        case 3:
                            $menuitems[] = blocks_menu_addMenuItem($mod['displayname'],
                                    ModUtil::url($mod['name'], 'user', 'main'),
                                    $mod['description']);
                            $content = true;
                            break;
                    }
                }
            }
        }

        // check for any empty result set
        if (empty($menuitems)) {
            return;
        }

        // assign the items
        $pnRender->assign('menuitems', $menuitems);

        // get the block content
        $blockinfo['content'] = $pnRender->fetch('blocks_block_menu.htm');

        // add the stylesheet to the header
        if (isset($vars['stylesheet'])) {
            PageUtil::addVar('stylesheet', ThemeUtil::getModuleStyleSheet('Blocks', $vars['stylesheet']));
        }

        // pass the block array back to the theme for display
        return BlockUtil::themeBlock($blockinfo);
    }

    /**
     * Prepare a menu item array
     *
     * @param        title           menu item title
     * @param         url             menu item url
     * @param        comment         menu item comment
     */
    public function addMenuItem($title, $url, $comment)
    {
        static $uri;

        if (!isset($uri)) {
            $uri = pnGetCurrentURI();
        }

        if (!isset($title) || $title == '') {
            $title = '&nbsp;';
        }

        $itemselected = false;
        // do a simple check .. to see if the current URL is the menu item
        if (!empty($url)) {
            if (is_integer(strpos($uri, $url))) {
                $itemselected = true;
            }
        }

        // allow a simple portable way to link to the home page of the site
        if ($url == '{homepage}') {
            $url = System::getBaseUrl();
        } elseif (!empty($url)) {
            switch ($url[0]) // Used to allow support for linking to modules with the use of bracket
            {
                case '[': // old style module link
                    {
                        $url = explode(':', substr($url, 1,  - 1));
                        $url = System::getVar('entrypoint', 'index.php') . '?name='.$url[0].(isset($url[1]) ? '&file='.$url[1]:'');
                        break;
                    }
                case '{': // new module link
                    {
                        $url = explode(':', substr($url, 1,  - 1));
                        // url[0] should be the module name
                        if (isset($url[0]) && !empty($url[0])) {
                            $modname = $url[0];
                            // default for params
                            $params = array();
                            // url[1] can be a function or function&param=value
                            if (isset($url[1]) && !empty($url[1])) {
                                $urlparts = explode('&', $url[1]);
                                $func = $urlparts[0];
                                unset($urlparts[0]);
                                if (count($urlparts) > 0) {
                                    foreach ($urlparts as $urlpart) {
                                        $part = explode('=', $urlpart);
                                        $params[trim($part[0])] = trim($part[1]);
                                    }
                                }
                            } else {
                                $func = 'main';
                            }
                            // addon: url[2] can be the type parameter, default 'user'
                            $type = (isset($url[2]) &&!empty($url[2])) ? $url[2] : 'user';
                            //  build the url
                            $url = ModUtil::url($modname, $type, $func, $params);
                        } else {
                            $url = System::getVar('entrypoint', 'index.php');
                        }
                        break;
                    }
            }  // End Bracket Linking
        }

        $item = array('MENUITEMTITLE' => $title,
                'MENUITEMURL' =>  $url,
                'MENUITEMCOMMENT' => DataUtil::formatForDisplay($comment),
                'MENUITEMSELECTED' => $itemselected);
        return $item;
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

        // Create output object - this object will store all of our output so that
        // we can return it easily when required
        $pnRender = Renderer::getInstance('Blocks');

        // assign the vars
        $pnRender->assign($vars);

        $menuitems = array();
        if (!empty($vars['content'])) {
            $contentlines = explode('LINESPLIT', $vars['content']);
            foreach ($contentlines as $contentline) {
                $link = explode('|', $contentline);
                $menuitems[] = $link;
            }
        }
        $pnRender->assign('menuitems', $menuitems);

        // return the output
        return $pnRender->fetch('blocks_block_menu_modify.htm');
    }

    /**
     * update block settings
     *
     * @author       The Zikula Development Team
     * @param        array       $blockinfo     a blockinfo structure
     * @return       $blockinfo  the modified blockinfo structure
     */
    public function update($blockinfo)
    {
        $vars['displaymodules'] = FormUtil::getPassedValue('displaymodules');
        $vars['style']          = FormUtil::getPassedValue('style');
        $vars['stylesheet']     = FormUtil::getPassedValue('stylesheet');

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
        $content = array();
        $c = 1;

        $linkname   = FormUtil::getPassedValue('linkname');
        $linkurl    = FormUtil::getPassedValue('linkurl');
        $linkdesc   = FormUtil::getPassedValue('linkdesc');
        $linkdelete = FormUtil::getPassedValue('linkdelete');
        $linkinsert = FormUtil::getPassedValue('linkinsert');

        foreach ($linkname as $v) {
            if (empty($linkdelete[$c])) {
                $content[] = "$linkurl[$c]|$linkname[$c]|$linkdesc[$c]";
            }
            if (!empty($linkinsert[$c])) {
                $content[] = '||';
            }
            $c++;
        }

        $new_linkname = FormUtil::getPassedValue('new_linkname');
        $new_linkurl  = FormUtil::getPassedValue('new_linkurl');
        $new_linkdesc = FormUtil::getPassedValue('new_linkdesc');
        $new_linkinsert = (bool)FormUtil::getPassedValue('new_linkinsert');

        if ($new_linkname) {
            $content[] = $new_linkurl . '|' . $new_linkname . '|' . $new_linkdesc;
            if ($new_linkinsert) {
                $content[] = '||';
            }
        }
        $vars['content'] = implode('LINESPLIT', $content);

        $blockinfo['content'] = BlockUtil::varsToContent($vars);

        // clear the block cache
        $pnRender = Renderer::getInstance('Blocks');
        $pnRender->clear_cache('blocks_block_menu.htm');

        return($blockinfo);
    }
}