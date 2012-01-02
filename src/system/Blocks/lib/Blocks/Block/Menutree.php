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

class Blocks_Block_Menutree extends Zikula_Controller_AbstractBlock
{
    /**
     * initialise block
     */
    public function init()
    {
        SecurityUtil::registerPermissionSchema('Menutree:menutreeblock:', 'Block ID:Link Name:Link ID');
    }

    /**
     * get information on block
     */
    public function info()
    {
        return array('module'          => $this->name,
                     'text_type'       => $this->__('Menutree'),
                     'text_type_long'  => $this->__('Tree-like menu (menutree)'),
                     'allow_multiple'  => true,
                     'form_content'    => false,
                     'form_refresh'    => false,
                     'show_preview'    => true,
                     'admin_tableless' => true);
    }

    /**
     * display block
     */
    public function display($blockinfo)
    {
        // Security check
        if (!Securityutil::checkPermission('Menutree:menutreeblock:', "{$blockinfo['bid']}::", ACCESS_READ)) {
            return false;
        }

        // Get variables from content block
        $vars = BlockUtil::varsFromContent($blockinfo['content']);

        // stylesheet
        if (file_exists($vars['menutree_stylesheet'])) {
            PageUtil::addVar('stylesheet', $vars['menutree_stylesheet']);
        }

        // template to use
        if (!isset($vars['menutree_tpl']) || empty($vars['menutree_tpl']) || !$this->view->template_exists($vars['menutree_tpl'])) {
            $vars['menutree_tpl'] = 'menutree/blocks_block_menutree_default.tpl';
        }

        // if cache is enabled, checks for a cached output
        if ($this->view->getCaching()) {
            // set the cache id
            $this->view->setCacheId($blockinfo['bkey'].'/bid'.$blockinfo['bid'].'/'.UserUtil::getGidCacheString());

            // check out if the contents are cached
            if ($this->view->is_cached($vars['menutree_tpl'])) {
                $blockinfo['content'] = $this->view->fetch($vars['menutree_tpl']);
                return BlockUtil::themeBlock($blockinfo);
            }
        }

        // set default block vars
        $vars['menutree_content']    = isset($vars['menutree_content']) ? $vars['menutree_content'] : array();
        $vars['menutree_titles']     = isset($vars['menutree_titles']) ? $vars['menutree_titles'] : array();
        $vars['menutree_stylesheet'] = isset($vars['menutree_stylesheet']) ? $vars['menutree_stylesheet'] : '';
        $vars['menutree_editlinks']  = isset($vars['menutree_editlinks']) ? $vars['menutree_editlinks'] : false;

        // set current user lang
        $lang = ZLanguage::getLanguageCode();
        $deflang = 'en';

        if (!in_array($lang, array_keys(current($vars['menutree_content'])))) {
            $lang = $deflang;
        }

        if (!empty($vars['menutree_content'])) {
            // select current lang, check permissions for each item and exclude unactive nodes
            $newTree = array();
            $blocked = array();
            foreach ($vars['menutree_content'] as $id => $item) {
                $item = $item[$lang];
                // check the permission access to the current link
                $hasperms = Securityutil::checkPermission('Menutree:menutreeblock:',"$blockinfo[bid]:$item[name]:$item[id]", ACCESS_READ);
                // checks if has no access to it or the link is not active
                if (!$hasperms || in_array($item['parent'], $blocked) || $item['state'] != 1) {
                    $blocked[] = $item['id'];
                } else {
                    // dynamic components
                    if (strpos($item['href'],'{ext:') === 0) {
                        $dynamic = explode(':', substr($item['href'], 1,  - 1));
                        $modname = $dynamic[1];
                        $func = $dynamic[2]; // plugin
                        $extrainfo = (isset($dynamic[3]) && !empty($dynamic[3])) ? $dynamic[3] : null;
                        if (!empty($modname) && !empty($func)) {
                            $args = array(
                                    'item' => $item,
                                    'lang' => $lang,
                                    'bid' => $blockinfo['bid'],
                                    'extrainfo' => $extrainfo,
                            );
                            $node = ModUtil::apiFunc($modname, 'menutree', $func, $args);
                            if (!is_array($node)) {
                                $node = array(array($lang => $item));
                            }
                        }
                    } else {
                        $node = array(array($lang => $item));
                    }
                    $newTree = array_merge($newTree,(array)$node);
                }
            }

            // bulid structured array
            $langs = array('ref' => $lang,
                           'list' => $lang,
                           'flat' => true);

            $tree = new Blocks_MenutreeTree();
            $tree->setOption('langs',(array)$langs['list']);
            $tree->setOption('flat',$langs['flat']);
            $tree->setOption('parseURL',true);
            $tree->loadArrayData($newTree);

            $newTree = $tree->getData();
        } else {
            $newTree = array();
        }

        // block title
        if (!empty($vars['menutree_titles'][$lang])) {
            $blockinfo['title'] = $vars['menutree_titles'][$lang];
        }

        $this->view->assign('menutree_editlinks', $vars['menutree_editlinks'] && Securityutil::checkPermission('Blocks::', "$blockinfo[bkey]:$blockinfo[title]:$blockinfo[bid]", ACCESS_EDIT))
                   ->assign('menutree_content', $newTree)
                   ->assign('blockinfo', $blockinfo);

        $blockinfo['content'] = $this->view->fetch($vars['menutree_tpl']);

        return BlockUtil::themeBlock($blockinfo);
    }

    /**
     * block configuration
     */
    public function modify($blockinfo)
    {
        $vars = BlockUtil::varsFromContent($blockinfo['content']);

        // set some default vars
        $vars['isnew'] =                    empty($vars);
        $vars['menutree_content'] =         isset($vars['menutree_content']) ? $vars['menutree_content'] : array();
        $vars['menutree_tpl'] =             isset($vars['menutree_tpl']) ? $vars['menutree_tpl'] : '';
        $vars['menutree_stylesheet'] =      isset($vars['menutree_stylesheet']) ? $vars['menutree_stylesheet'] : '';
        $vars['menutree_linkclass'] =       isset($vars['menutree_linkclass']) ? $vars['menutree_linkclass'] : false;
        $vars['menutree_linkclasses'] =     isset($vars['menutree_linkclasses']) ? $vars['menutree_linkclasses'] : array();
        $vars['menutree_titles'] =          isset($vars['menutree_titles']) ? $vars['menutree_titles'] : array();
        $vars['menutree_editlinks'] =       isset($vars['menutree_editlinks']) ? $vars['menutree_editlinks'] : false;
        $vars['menutree_stripbaseurl'] =    isset($vars['menutree_stripbaseurl']) ? $vars['menutree_stripbaseurl'] : true;
        $vars['menutree_maxdepth'] =        isset($vars['menutree_maxdepth']) ? $vars['menutree_maxdepth'] : 0;
        $vars['oldlanguages'] =             isset($vars['oldlanguages']) ? $vars['oldlanguages'] : array();
        $vars['olddefaultanguage'] =        isset($vars['olddefaultanguage']) ? $vars['olddefaultanguage'] :'';

        // get list of languages
        $vars['languages'] = ZLanguage::getInstalledLanguageNames();
        $userlanguage = ZLanguage::getLanguageCode();

        // get default langs
        $vars['defaultanguage'] = !empty($blockinfo['language']) ? $blockinfo['language'] : $userlanguage;

        // rebuild langs array - default lang has to be first
        if (isset($vars['languages']) && count($vars['languages']) > 1) {
            $deflang[$vars['defaultanguage']] = $vars['languages'][$vars['defaultanguage']];
            unset($vars['languages'][$vars['defaultanguage']]);
            $vars['languages'] = array_merge($deflang,$vars['languages']);
            $vars['multilingual'] = true;
        } else {
            $vars['multilingual'] = false;
        }

        $langs = array('list' => array_keys($vars['languages']),
                       'flat' => false);

        // check if there is allredy content
        if (empty($vars['menutree_content'])) {
            // no content - get list of menus to allow import
            $vars['menutree_menus'] = $this->_get_current_menus($blockinfo['bid']);
        } else {
            // are there new langs not present in current menu?
            // check if there are new languages not present in current menu
            // if so - need to set reference lang to copy initial menu items data
            if (count(array_diff($vars['languages'],$vars['oldlanguages'])) > 1) {
                // fisrt try current default lang
                if (in_array($vars['defaultanguage'],$vars['oldlanguages'])) {
                    $langs['ref'] = $vars['defaultanguage'];
                    // or user lang
                } elseif (in_array($userlanguage,$vars['oldlanguages'])) {
                    $langs['ref'] = $userlanguage;
                    // or old default lang
                } elseif (in_array($vars['olddefaultanguage'],$vars['languages'])) {
                    $langs['ref'] = $vars['olddefaultanguage'];
                    // it must be any language present in old and new lang list
                } else {
                    $langs['ref'] = current(array_intersect($vars['languages'], $vars['oldlanguages']));
                }
            }
        }
        // decode tree array
        $tree = new Blocks_MenutreeTree();
        $tree->setOption('id', 'adm-menutree'.$blockinfo['bid']);
        $tree->setOption('sortable', true);
        if (isset($langs)) {
            $tree->setOption('langs', $langs['list']);
        }
        $tree->setOption('stripbaseurl', $vars['menutree_stripbaseurl']);
        $tree->setOption('maxDepth', $vars['menutree_maxdepth']);
        $tree->loadArrayData($vars['menutree_content']);
        $vars['menutree_content'] = $tree->getHTML();

        // get all templates and stylesheets.
        $vars['tpls'] = Blocks_MenutreeUtil::getTemplates();
        $vars['styles'] =  Blocks_MenutreeUtil::getStylesheets();
        $someThemes = $this->__('Only in some themes');
        $vars['somethemes'] = isset($vars['tpls'][$someThemes]) || isset($vars['styles'][$someThemes]) ? true : false;

        // template to use
        if (empty($vars['menutree_tpl']) || !$this->view->template_exists($vars['menutree_tpl'])) {
            $vars['menutree_tpl'] = 'menutree/blocks_block_menutree_default.tpl';
        }

        // prepare block titles array
        foreach (array_keys($vars['languages']) as $lang) {
            if (!array_key_exists($lang, $vars['menutree_titles'])) {
                $vars['menutree_titles'][$lang] = '';
            }
        }

        // for permissions settings get first supported permlevels
        $vars['permlevels']  = $this->_permlevels();

        // check if saved permlevels are correct
        $vars['menutree_titlesperms']   = !empty($vars['menutree_titlesperms']) ? $vars['menutree_titlesperms'] : 'ACCESS_EDIT';
        $vars['menutree_displayperms']  = !empty($vars['menutree_displayperms']) ? $vars['menutree_displayperms'] : 'ACCESS_EDIT';
        $vars['menutree_settingsperms'] = !empty($vars['menutree_settingsperms']) ? $vars['menutree_settingsperms'] : 'ACCESS_EDIT';

        // check user permissions for settings sections
        $useraccess = SecurityUtil::getSecurityLevel(SecurityUtil::getAuthInfo(), 'Blocks::', "$blockinfo[bkey]:$blockinfo[title]:$blockinfo[bid]");
        $vars['menutree_titlesaccess']      = $useraccess >= constant($vars['menutree_titlesperms']);
        $vars['menutree_displayaccess']     = $useraccess >= constant($vars['menutree_displayperms']);
        $vars['menutree_settingsaccess']    = $useraccess >= constant($vars['menutree_settingsperms']);
        $vars['menutree_adminaccess']       = $useraccess >= ACCESS_ADMIN;
        $vars['menutree_anysettingsaccess'] = $vars['menutree_adminaccess'] || $vars['menutree_titlesaccess'] || $vars['menutree_displayaccess'] || $vars['menutree_settingsaccess'];

        // check if the users wants to add a new link via the "Add current url" link in the block
        $addurl = FormUtil::getPassedValue('addurl', 0, 'GET');

        // or if we come from the normal "edit this block" link
        $fromblock = FormUtil::getPassedValue('fromblock', null, 'GET');

        $vars['redirect'] = '';
        $vars['menutree_newurl'] = '';
        if ($addurl == 1) {
            // set a marker for redirection later on
            $newurl = System::serverGetVar('HTTP_REFERER');
            $vars['redirect'] = urlencode($newurl);
            $newurl = str_replace(System::getBaseUrl(), '', $newurl);
            if (empty($newurl)) {
                $newurl = System::getHomepageUrl();
            }
            $vars['menutree_newurl'] = $newurl;
        } elseif (isset($fromblock)) {
            $vars['redirect'] = urlencode(System::serverGetVar('HTTP_REFERER'));
        }

        // Create output object
        $this->view->setCaching(Zikula_View::CACHE_DISABLED);

        // assign all block variables
        $this->view->assign($vars)
                   ->assign('blockinfo', $blockinfo);

        // Return the output that has been generated by this function
        return $this->view->fetch('menutree/blocks_block_menutree_modify.tpl');
    }

    /**
     * update block configuration
     */
    public function update($blockinfo)
    {
        // Get current content
        $vars = BlockUtil::varsFromContent($blockinfo['content']);

        // check if import old menu
        $menutree_menus = FormUtil::getPassedValue('menutree_menus', 'null');

        if ($menutree_menus != 'null') {
            $vars['menutree_content'] = $this->_import_menu($menutree_menus);
        } else {
            $vars['menutree_content'] = FormUtil::getPassedValue('menutree_content', '', 'POST');
            $vars['menutree_content'] = DataUtil::urlsafeJsonDecode($vars['menutree_content']);
        }

        if (!$this->validate_menu($vars['menutree_content'])) {
            return LogUtil::registerError($this->__('Error! Could not save your changes.'));
        }

        // sort tree array according to lineno key
        uasort($vars['menutree_content'], array('Blocks_Block_Menutree','sort_menu'));

        // get other form data
        $menutree_data = FormUtil::getPassedValue('menutree');

        $vars['menutree_tpl'] = isset($menutree_data['tpl']) ? $menutree_data['tpl'] : '';
        if (empty($vars['menutree_tpl']) || !$this->view->template_exists($vars['menutree_tpl'])) {
            $vars['menutree_tpl'] = 'menutree/blocks_block_menutree_default.tpl';
        }

        $vars['menutree_stylesheet'] = isset($menutree_data['stylesheet']) ? $menutree_data['stylesheet'] : '';
        if (empty($vars['menutree_stylesheet']) || $vars['menutree_stylesheet'] == 'null' || !file_exists($vars['menutree_stylesheet'])) {
            $vars['menutree_stylesheet'] = '';
        }

        $vars['menutree_titles'] = isset($menutree_data['titles']) ? $menutree_data['titles'] : array();

        $vars['menutree_linkclass'] = isset($menutree_data['linkclass']) ? (bool)$menutree_data['linkclass'] : false;
        // if class list is provided - rebuild array and fill empty entries
        if ($vars['menutree_linkclass'] && isset($menutree_data['linkclasses'])) {
            foreach ((array)$menutree_data['linkclasses'] as $k => $class) {
                if (empty($class['name'])) {
                    unset($menutree_data['linkclasses'][$k]);
                } elseif (empty($class['title'])) {
                    $menutree_data['linkclasses'][$k]['title'] = $class['name'];
                }
            }
            $vars['menutree_linkclasses'] = $menutree_data['linkclasses'];
            if (count($vars['menutree_linkclasses']) < 1) {
                $vars['menutree_linkclass'] = false;
            }
        }

        $vars['menutree_maxdepth']     = isset($menutree_data['maxdepth']) ? (int)$menutree_data['maxdepth'] : 0;
        $vars['menutree_editlinks']    = isset($menutree_data['editlinks']) ? (bool)$menutree_data['editlinks'] : false;
        $vars['menutree_stripbaseurl'] = isset($menutree_data['stripbaseurl']) ? (bool)$menutree_data['stripbaseurl'] : false;

        $vars['menutree_titlesperms']   = isset($menutree_data['titlesperms']) && array_key_exists($menutree_data['titlesperms'],$this->_permlevels()) ? $menutree_data['titlesperms'] : 'ACCESS_EDIT';
        $vars['menutree_displayperms']  = isset($menutree_data['displayperms']) && array_key_exists($menutree_data['displayperms'],$this->_permlevels()) ? $menutree_data['displayperms'] : 'ACCESS_EDIT';
        $vars['menutree_settingsperms'] = isset($menutree_data['settingsperms']) && array_key_exists($menutree_data['settingsperms'],$this->_permlevels()) ? $menutree_data['settingsperms'] : 'ACCESS_EDIT';

        if (empty($vars['menutree_content'])) {
            unset($vars['menutree_content']);
        } else {
            // check langs and save current langs list and current default lang
            $tmp = current($vars['menutree_content']);
            $vars['oldlanguages'] = array_keys($tmp);
            $vars['olddefaultanguage'] = $vars['oldlanguages'][0];

            // strip base url - if needed
            if ($vars['menutree_stripbaseurl'] === true) {
                $baseurl = System::getBaseUrl();
                foreach ($vars['menutree_content'] as $itemid => $item) {
                    foreach ($item as $lang => $_item) {
                        // strip base url only when it occurs at the beginning of url and only once
                        if (strpos($_item['href'], $baseurl) === 0) {
                            $vars['menutree_content'][$itemid][$lang]['href'] = substr_replace($_item['href'], '', 0, strlen($baseurl));
                        }
                    }
                }
            }
        }

        // write back the new contents
        $blockinfo['content'] = BlockUtil::varsToContent($vars);

        // clear the block cache
        $this->view->clear_cache(null, $blockinfo['bkey'].'/bid'.$blockinfo['bid']);

        // and clear the theme cache
        Zikula_View_Theme::getInstance()->clear_cache();

        return $blockinfo;
    }

    private function _permlevels()
    {
        return array('ACCESS_EDIT'   => $this->__('Edit access'),
                     'ACCESS_ADD'    => $this->__('Add access'),
                     'ACCESS_DELETE' => $this->__('Delete access'),
                     'ACCESS_ADMIN'  => $this->__('Admin access'));
    }

    /**
     * Get list of menus with type supported to import
     */
    private function _get_current_menus($bid)
    {
        $supported = array('Menu', 'Extmenu', 'Menutree');

        $_menus = BlockUtil::getBlocksInfo();

        $menus = array();
        foreach ($_menus as $menu) {
            if (in_array($menu['bkey'],$supported) && $menu['bid'] != $bid) {
                $menus[$menu['bid']] = $menu['title'];
            }
        }

        return $menus;
    }

    /**
     * Convert data of selected menu to menutree style
     * Used to import menus
     */
    private function _import_menu($bid)
    {
        if ((!isset($bid)) || (isset($bid) && !is_numeric($bid))) {
            return;
        }

        $menu = BlockUtil::getBlockInfo($bid);
        $menuVars = BlockUtil::varsFromContent($menu['content']);

        $userlanguage = ZLanguage::getLanguageCode();

        $menuType = strtolower($menu['bkey']);
        switch ($menuType)
        {
            case 'menutree':
                $data = isset($menuVars['menutree_content']) ? $menuVars['menutree_content'] : array();
                break;

            case 'menu':
                if (isset($menuVars['content']) && !empty($menuVars['content'])) {
                    $reflang = $userlanguage;
                    $pid = 1;
                    $data = array();
                    $contentlines = explode('LINESPLIT', $menuVars['content']);
                    foreach ($contentlines as $lineno => $contentline) {
                        list($href, $name, $title) = explode('|', $contentline);
                        if (!empty($name)) {
                            $className = '';
                            $parent = 0;
                            $state = 1;
                            $lang = $reflang;
                            $id = $pid;
                            $data[$lineno][$reflang] = compact('href','name','title','className','parent','state','lang','lineno','id');
                            $pid++;
                        }
                    }
                    $langs = (array)$reflang;
                    $lineno++;
                }
                break;

            case 'extmenu':
                if (isset($menuVars['links']) && !empty($menuVars['links'])) {
                    $langs = array_keys($menuVars['links']);
                    $data = array();
                    foreach ($langs as $lang) {
                        foreach ($menuVars['links'][$lang] as $id => $link) {
                            $data[$id][$lang] = array(
                                    'id'        => $id + 1,
                                    'name'      => isset($link['name']) && !empty($link['name']) ? $link['name'] : $this->__('no name'),
                                    'href'      => isset($link['url']) ? $link['url'] : '',
                                    'title'     => isset($link['title']) ? $link['title'] : '',
                                    'className' => '',
                                    'state'     => isset($link['active']) && $link['active'] && $link['name'] ? 1 : 0,
                                    'lang'      => $lang,
                                    'lineno'    => $id,
                                    'parent'    => 0
                            );
                        }
                    }
                    ksort($data);
                    $pid = $id + 2;
                    $lineno = count($data);
                }
                break;
        }

        if (!empty($menuVars['displaymodules'])) {
            $mods = ModUtil::getUserMods();

            if (is_array($mods) && count($mods)>0) {
                foreach ($mods as $mod) {
                    $tmp = array('name'  => $mod['displayname'],
                                 'href'  => DataUtil::formatForDisplay(ModUtil::url($mod['name'], 'user', 'main')),
                                 'title' => $mod['description']);

                    foreach ($langs as $lang) {
                        $tmp = array_merge($tmp, array('className' => '',
                                                       'parent' => 0,
                                                       'lang' => $lang,
                                                       'state' => 1,
                                                       'lineno' => $lineno,
                                                       'id' => $pid));
                        $tmparray[$lang] = $tmp;
                    }

                    $data[] = $tmparray;
                    $pid++;
                    $lineno++;
                }
            }
        }

        return $data;
    }

    private function validate_menu($array)
    {
        /*
         * Menu should be an array of arrays:
         * [id] = array(
         *     [lang] = array (
         *         [data][lang] = [lang]
         *         [data][parent] = exist
         *     )
         * )
         */
        if (!is_array($array)) {
            return false;
        }
        $ids = array_keys($array);
        $ids[] = 0;
        foreach ($array as $id => $node) {
            if (!is_numeric($id) || !is_array($node)) {
                return false;
            }
            foreach ($node as $lang => $data) {
                if (!ZLanguage::isLangParam($lang)
                        || !is_array($data)
                        || empty($data['name'])
                        || !ZLanguage::isLangParam($data['lang'])
                        || !in_array($data['parent'],$ids)){
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Callback function for uasort() which allows a MenuTree array to be sorted by line number.
     *
     * @param array $a The first element to be compared, an array containing a MenuTree item definition (indexed by language).
     * @param array $b The second element to be compared, an array containing a MenuTree item definition (indexed by language).
     * 
     * @return int 0 if the two operands are equal, -1 if $a's line number is less than $b's, 1 if $a's line number is greater than $b's.
     */
    private function sort_menu($a, $b)
    {
        $aLang = key($a);
        $aLineNo = $a[$aLang]['lineno'] ? $a[$aLang]['lineno'] : 0;
        $bLang = key($b);
        $bLineNo = $b[$bLang]['lineno'] ? $b[$bLang]['lineno'] : 0;
        if ($aLineNo == $bLineNo) {
            return 0;
        }
        return ($aLineNo < $bLineNo) ? -1 : 1;
    }
}
