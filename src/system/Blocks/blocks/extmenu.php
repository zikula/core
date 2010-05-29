<?php
/**
 * Zikula Application Framework
 * @copyright (c) 2001, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_System_Modules
 * @subpackage Blocks
 * @author Frank Schummertz [landseer]
 */

/**
 * initialise block
 *
 * @author       The Zikula Development Team
 */
function Blocks_extmenublock_init()
{
    // Security
    SecurityUtil::registerPermissionSchema('ExtendedMenublock::', 'Block ID:Link ID:');
}

/**
 * get information on block
 *
 * @author       The Zikula Development Team
 * @return       array       The block information
 */
function Blocks_extmenublock_info()
{
    return array('module' => 'Blocks',
                 'text_type' => __('Extended menu'),
                 'text_type_long' => __('Extended menu block'),
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
function Blocks_extmenublock_display($blockinfo)
{
    // security check
    if (!SecurityUtil::checkPermission('ExtendedMenublock::', $blockinfo['bid'] . '::', ACCESS_READ)) {
        return;
    }

    // Create output object
    $pnRender = Renderer::getInstance('Blocks');

    // Set the cache id
    $pnRender->cache_id = $blockinfo['bid'].':'.UserUtil::getVar('uid');

    // Break out options from our content field
    $vars = BlockUtil::varsFromContent($blockinfo['content']);

    // template to use
    if (empty($vars['template'])) {
        $vars['template'] = 'blocks_block_extmenu.htm';
    }
    // template to use
    if (empty($vars['stylesheet'])) {
        $vars['stylesheet'] = 'extmenu.css';
    }

    // check out if the contents are cached.
    if ($pnRender->is_cached($vars['template'])) {
        // Populate block info and pass to theme
        $blockinfo['content'] = $pnRender->fetch($vars['template']);
        return BlockUtil::themeBlock($blockinfo);
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

    // block title: copy the ml blocktitle to the original block title
    if (array_key_exists($thislang, $vars['blocktitles'])) {
        $blockinfo['title'] = $vars['blocktitles'][$thislang];
    }

    // Content
    $menuitems =array();
    if (!empty($vars['links'][$thislang])) {
        $blocked = array();
        foreach ($vars['links'][$thislang] as $linkid => $link) {
            $link['parentid'] = isset($link['parentid']) ? $link['parentid'] : null;
            $denied = !SecurityUtil::checkPermission('ExtendedMenublock::', $blockinfo['bid'] . ':' . $linkid . ':', ACCESS_READ);
            if($denied || in_array($link['parentid'], $blocked)) {
                $blocked[] = $linkid;
            } elseif ($link['active'] != 1) {
                $blocked[] = $linkid;
            } else {
                // pre zk1.2 check
                if (!isset($link['id'])) {
                    $link['id'] = $linkid;
                }
                $link['url'] = extmenu_buildURL($link['url']);
                // check for multiple options in image
                extmenu_check_image($link);
                $menuitems[] = $link;
            }
        }
    }

    // Modules
    if (!empty($vars['displaymodules'])) {
        $newmods = pnModGetUserMods();
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
                                'image'  => ''
                                );
            if (SecurityUtil::checkPermission('ExtendedMenublock::', $blockinfo['bid'] . '::', ACCESS_ADMIN)) {
                $menuitems[] = array('name'   => __('--Installed modules--'),
                                    'url'    => ModUtil::url('Blocks', 'admin', 'modify', array('bid' => $blockinfo['bid'])),
                                    'title'  => '',
                                    'level'  => 0,
                                    'parentid' => null,
                                    'image'  => ''
                                    );
            }
        }

        foreach($mods as $mod) {
            // prepare image

            if (SecurityUtil::checkPermission("$mod[name]::", '::', ACCESS_OVERVIEW)) {
                switch($mod['type']) {
                    case 1:
                        $menuitems[] = array('name'   => $mod['displayname'],
                                             'url'    => System::getVar('entrypoint', 'index.php') . '?name=' . DataUtil::formatForDisplay($mod['directory']),
                                             'title'  => $mod['description'],
                                             'level'  => 0,
                                             'parentid' => null,
                                             'image'  => ''
                                             );
                        break;
                    case 2:
                    case 3:
                        $menuitems[] = array('name'   => $mod['displayname'],
                                             'url'    => ModUtil::url($mod['name'], 'user', 'main'),
                                             'title'  => $mod['description'],
                                             'level'  => 0,
                                             'parentid' => null,
                                             'image'  => ''
                                             );
                        break;
                }
            }
        }
    }

    // check for any empty result set
    if (empty($menuitems)) {
        return;
    }

    $currenturi = urlencode(str_replace(System::getBaseUri() . '/', '', pnGetCurrentURI()));

    // assign the items
    $pnRender->assign('menuitems', $menuitems);
    $pnRender->assign('blockinfo', $blockinfo);
    $pnRender->assign('currenturi', $currenturi);
    $pnRender->assign('access_edit', Securityutil::checkPermission('ExtendedMenublock::', $blockinfo['bid'] . '::', ACCESS_EDIT));

    // get the block content
    $blockinfo['content'] = $pnRender->fetch($vars['template']);

    // add the stylesheet to the header
    PageUtil::addVar('stylesheet', ThemeUtil::getModuleStylesheet('Blocks', $vars['stylesheet']));

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
        $uri = pnGetCurrentURI();
        if (is_integer(strpos($uri, $url))) {
            return true;
        }
    }
    return false;
}

/**
 * Prepare a menu item url
 *
 * @param         url            menu item url
 */
function extmenu_buildURL($url)
{
    // allow a simple portable way to link to the home page of the site
    if ($url == '{homepage}') {
        $url = htmlspecialchars(pnGetHomepageURL());
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

    return $url;
}

/**
 * modify block settings
 *
 * @author       The Zikula Development Team
 * @param        array       $blockinfo     a blockinfo structure
 * @return       output      the bock form
 */
function Blocks_extmenublock_modify($blockinfo)
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
        $vars['template'] = 'blocks_block_extmenu.htm';
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

    // Create output object - this object will store all of our output so that
    // we can return it easily when required
    $pnRender = Renderer::getInstance('Blocks', false);

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
            $newurl = System::getVar('entrypoint', 'index.php');
        }
        foreach($languages as $singlelanguage) {
            $vars['links'][$singlelanguage][] = array('name'   => __('--New link--'),
                                                      'url'    => $newurl,
                                                      'title'  => __('--New link--'),
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
    foreach($languages as $lang) {
        if(isset($vars['links'][$lang]) && count($link_master) < count($vars['links'][$lang])) {
            $link_master = $vars['links'][$lang];
        }
    }

    foreach($languages as $lang) {
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
            extmenu_check_image($link);
            $menuitems[$linkid][$lang] = $link;
        }
    }
    $vars['links'] = $menuitems;

    // assign the vars
    $pnRender->assign($vars);
    $pnRender->assign('languages', $languages);
    $pnRender->assign('userlanguage', $userlanguage);
    $pnRender->assign('redirect', $redirect);

    $pnRender->assign('blockinfo', $blockinfo);

    // return the output
    return $pnRender->fetch('blocks_block_extmenu_modify.htm');
}

/**
 * update block settings
 *
 * @author       The Zikula Development Team
 * @param        array       $blockinfo     a blockinfo structure
 * @return       $blockinfo  the modified blockinfo structure
 */
function Blocks_extmenublock_update($blockinfo)
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
        $vars['template'] = 'blocks_block_extmenu.htm';
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
    if(is_array($linksorder) && !empty($linksorder)) {
        foreach ((array)$vars['links'] as $lang => $langlinks) {
            foreach ($langlinks as $linkid => $link) {
                $vars['links'][$lang][$linkid]['parentid'] = $linksorder[$linkid]['parentid'];
                $vars['links'][$lang][$linkid]['haschildren'] = $linksorder[$linkid]['haschildren'];
            }
        }
    }

    $blockinfo['content'] = BlockUtil::varsToContent($vars);

    // clear the block cache
    $pnRender = Renderer::getInstance('Blocks', false);
    $pnRender->clear_all_cache();

    return $blockinfo;
}

function extmenu_check_image(&$link)
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
