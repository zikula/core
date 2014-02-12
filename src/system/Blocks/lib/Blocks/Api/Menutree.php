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

class Blocks_Api_Menutree extends Zikula_AbstractApi
{
    /**
     * Return list of admin modules
     *
     * Syntax used in menutree
     * {ext:Blocks:adminlinks:[flat,category]}
     * Last param is optional.  It can be flat and/or category separated by a comma.
     * 'flat' will add the admin links in the current menu.  Without 'flat' the links are grouped one level down
     * 'category' additionally groups the admin links by their category.
     * You can combine 'flat' and 'category' to have the category links added in the current menu.
     *
     * @param  array  $args['item']      menu node to be replaced
     * @param  string $args['lang']      current menu language
     * @param  string $args['extrainfo'] additional params - if 'flat' then return links ungrouped. if 'category' module links grouped by category
     * @return mixed  array of links if successful, false otherwise
     */
    public function adminlinks($args)
    {
        $item       = isset($args['item']) && !empty($args['item']) ? $args['item'] : null;
        $lang       = isset($args['lang']) && !empty($args['lang']) ? $args['lang'] : null;
        $extrainfo  = isset($args['extrainfo']) && !empty($args['extrainfo']) ? $args['extrainfo'] : null;
        // $item ang lang params are required
        if (!$item || !$lang) {
            return false;
        }

        // Convert extrainfo into flags
        $extrainfo = ($extrainfo) ? preg_split("/[\s]*,[\s]*/", trim($extrainfo)) : array();
        $flag = array();
        $flag['flat']     = in_array("flat",     $extrainfo);  //now true or false
        $flag['category'] = in_array("category", $extrainfo);  //now true or false

        // Make sure admin API is loaded
        if (!ModUtil::loadApi('Admin','admin',true)) {
            return false;
        }

        if (!SecurityUtil::checkPermission('Admin::', "::", ACCESS_EDIT)) {
            return array(); // Since no permission, return empty links
        }

        // get id for first element, use api func to avoid id conflicts inside menu
        $idoffset = Blocks_MenutreeUtil::getIdOffset($item['id']);
        $lineno = 0;

        $links = array();

        // if not flat, group the links into a single menu entry
        if (!$flag['flat']) {
            $links['adminlinks'] = array(
                    $lang => array(
                            'id' => $idoffset++,
                            'name' => $item['name'],
                            'href' => ModUtil::url('Admin', 'admin', 'adminpanel'),
                            'title' => $item['title'],
                            'className' => $item['className'],
                            'state' => $item['state'],
                            'lang' => $lang,
                            'lineno' => $lineno++,
                            'parent' => $item['parent']
                    )
            );
        }

        // need to set parent node id - if links are grouped - use your_accont item id
        // otherwise parent id of replaced menu node
        $parentNode = (!$flag['flat']) ? $links['adminlinks'][$lang]['id'] : $item['parent'];

        // First work on the Admin module categories
        $catinfo  = array(); // used to store menu information for the categories
        $catlinks = array();

        if ($flag['category']) {
            // Get all the Categories
            $categories = ModUtil::apiFunc('Admin', 'admin', 'getall');

            foreach ($categories as $item) {
                if (SecurityUtil::checkPermission('Admin::', "$item[catname]::$item[cid]", ACCESS_EDIT)) {
                    // Set up the menu information for this category
                    $catinfo[$item['cid']] = array (
                            'id' => $idoffset, // will need this to be a parent
                            'no' => 0          // start with 0 sub menu items
                    );
                    $catlinks[] = array(
                            $lang => array(
                                    'id'        => $idoffset++,
                                    'name'      => $item['catname'],
                                    'href'      => ModUtil::url('Admin','admin','adminpanel', array('acid' => $item['cid'])),
                                    'title'     => $item['description'],
                                    'className' => '',
                                    'state'     => 1,
                                    'lang'      => $lang,
                                    'lineno'    => $lineno++,
                                    'parent'    => $parentNode
                            )
                    );
                }
            }
        }

        // Now work on admin capable modules
        $adminmodules    = ModUtil::getAdminMods();
        $displayNameType = ModUtil::getVar('Admin', 'displaynametype', 1);
        $default_cid     = ModUtil::getVar('Admin', 'startcategory');
        $adminlinks      = array();

        foreach ($adminmodules as $adminmodule) {
            if (SecurityUtil::checkPermission("$adminmodule[name]::", '::', ACCESS_EDIT)) {
                $cid = ModUtil::apiFunc('Admin', 'admin', 'getmodcategory',
                        array('mid' => ModUtil::getIdFromName($adminmodule['name'])));
                $cid = (isset($catinfo[$cid])) ? $cid : $default_cid;  // make sure each module is assigned a category

                $modinfo = ModUtil::getInfo(ModUtil::getIdFromName($adminmodule['name']));

                if ($modinfo['type'] == 2 || $modinfo['type'] == 3) {
                    $menutexturl = ModUtil::url($modinfo['name'], 'admin');
                } else {
                    $menutexturl = 'admin.php?module=' . $modinfo['name'];
                }

                if ($displayNameType == 1) {
                    $menutext = $modinfo['displayname'];
                } elseif ($displayNameType == 2) {
                    $menutext = $modinfo['name'];
                } elseif ($displayNameType == 3) {
                    $menutext = $modinfo['displayname'] . ' (' . $modinfo['name'] . ')';
                }

                $adminlinks[] = array(
                        $lang => array(
                                'id'        => $idoffset++,
                                'name'      => $menutext,
                                'href'      => $menutexturl,
                                'title'     => $modinfo['description'],
                                'className' => '',
                                'state'     => 1,
                                'lang'      => $lang,
                                'lineno'    => ($flag['category']) ? $catinfo[$cid]['no']++ :$lineno++,
                                'parent'    => ($flag['category']) ? $catinfo[$cid]['id']   :$parentNode
                        )
                );
            }
        }

        $links = array_merge($links, $catlinks, $adminlinks);

        return $links;

    }

    /**
     * "Blank", sample plugin
     *
     * @param  array  $args['item']      menu node to be replaced
     * @param  string $args['lang']      current menu language
     * @param  string $args['extrainfo'] additional params - if 'flat' then return links ungrouped
     * @return mixed  array of links if successful, false otherwise
     */
    public function blank($args)
    {
        /**
         * args may look like this:
         * Array
         * (
         *     [item] => Array
         *         (
         *             [id] => 999
         *             [name] => Name given in menutree form
         *             [href] => {ext:blank:foo=1&bar=2}
         *             [title] => Some title
         *             [className] => important
         *             [state] => 1
         *             [lang] => eng
         *             [lineno] => 99
         *             [parent] => 0
         *         )

         *     [lang] => pol
         *     [bid] => 999
         *     [extrainfo] => foo=1&bar=2
         * )
         *
         */
        $dom = ZLanguage::getModuleDomain('menutree');
        $item       = isset($args['item']) && !empty($args['item']) ? $args['item'] : null;
        $lang       = isset($args['lang']) && !empty($args['lang']) ? $args['lang'] : null;
        $bid        = isset($args['bid']) && !empty($args['bid']) ? $args['bid'] : null;
        $extrainfo  = isset($args['extrainfo']) && !empty($args['extrainfo']) ? $args['extrainfo'] : null;

        // $item ang lang params are required
        if (!$item || !$lang) {
            return false;
        }
        // is there is extrainfo - convert it into array, parse_str is quite handy
        if ($extrainfo) {
            parse_str($extrainfo, $extrainfo);
        }

        // get id for first element, use api func to avoid id conflicts inside menu
        $idoffset = Blocks_MenutreeUtil::getIdOffset($item['id']);

        $links = array();
        // build some link
        // you may use associative array keys
        $links['first'] = array(
                $lang => array(
                        'id' => $idoffset++, // always use id returned by api func for first element
                        'name' => $item['name'], // you may use name given by user - but do not have to
                        'href' => ModUtil::url('News'),
                        'title' => $item['title'], // the same as for name - you may use user input
                        'className' => $item['className'],
                        'state' => $item['state'],
                        'lang' => $lang,
                        'lineno' => $item['lineno'],
                        'parent' => $item['parent'] // always use replaced item parent for element at first level
                )
        );

        // build second link - this one will be child of the first element
        $secondLink = isset($extrainfo['foo']) ? ModUtil::url('News','user','display',array('sid' => $extrainfo['foo'])) : ModUtil::url('News');
        $links['second'] = array(
                $lang => array(
                        'id' => $idoffset++, // using this syntax you're always will have proper ids
                        'name' => 'Second blank link',
                        'href' => $secondLink,
                        'title' => __('Title', $dom), // you may also use translated content
                        'className' => '',
                        'state' => 1, // for child nodes set state = 1
                        'lang' => $lang,
                        'lineno' => 0,
                        'parent' => $links['first'][$lang]['id'] // use first element id while we want to set this node as child node
                )
        );

        // build third link - this one will be on the same level as first link
        $thirdLink = isset($extrainfo['bar']) ? ModUtil::url('News','user','display',array('sid' => $extrainfo['bar'])) : ModUtil::url('News');
        $links['third'] = array(
                $lang => array(
                        'id' => $idoffset++,
                        'name' => 'Third blank link',
                        'href' => $thirdLink,
                        'title' => '',
                        'className' => '',
                        'state' => $item['state'], // always use replaced item state for element at first level
                        'lang' => $lang,
                        'lineno' => $item['lineno']+1,
                        'parent' => $item['parent'] // always use replaced item parent for element at first level
                )
        );

        return $links;
    }


    /**
     * Return Clip publications
     *
     * Syntax used in menutree
     * {ext:Blocks:clip:[tid=2&fieldname=title&maxitems=5&flat=1&orderby=somefield]}
     * Params in [] are optional and
     *      tid         = The publication type
     *      fieldname   = The publication field to show as menuitem name
     *      maxitems    = How many items to show in the generated list (default -1 = unlimited)
     *      flat        = [0/1] without hiearchy or with hiearchy and parent publication link included
     *      orderby     = The publication field to order by (default null)
     *
     * @param  array  $args['item']      menu node to be replaced
     * @param  string $args['lang']      current menu language
     * @param  string $args['extrainfo'] additional params
     * @return mixed  array of links if successful, false otherwise
     */
    public function clip($args)
    {
        $item       = isset($args['item']) && !empty($args['item']) ? $args['item'] : null;
        $lang       = isset($args['lang']) && !empty($args['lang']) ? $args['lang'] : null;
        $bid        = isset($args['bid']) && !empty($args['bid']) ? $args['bid'] : null;
        $extrainfo  = isset($args['extrainfo']) && !empty($args['extrainfo']) ? $args['extrainfo'] : null;

        // $item ang lang params are required
        if(!$item || !$lang) {
            return false;
        }
        // is there is extrainfo - convert it into array, parse_str is quite handy
        if($extrainfo) {
            parse_str($extrainfo, $extrainfo);
        }
        $extrainfo['tid'] = (is_numeric($extrainfo['tid'])) ? (int)$extrainfo['tid'] : -1;
        $extrainfo['fieldname'] = isset($extrainfo['fieldname']) ? $extrainfo['fieldname'] : '';
        if ($extrainfo['tid'] < 0 || empty($extrainfo['fieldname'])) {
            return false;
        }
        $extrainfo['maxitems'] = isset($extrainfo['maxitems']) ? (int)$extrainfo['maxitems'] : -1;
        $extrainfo['flat'] = isset($extrainfo['flat']) ? (bool)$extrainfo['flat'] : false;
        $extrainfo['orderby'] = isset($extrainfo['orderby']) ? $extrainfo['orderby'] : null;

        // get id for first element, use api func to avoid id conflicts inside menu
        $idoffset = Blocks_MenutreeUtil::getIdOffset($item['id']);
        $lineno = 0;
        $links = array();

        if(!$extrainfo['flat']) {
            $links['clip'] = array(
                $lang => array(
                    'id' => $idoffset++, // always use id returned by api func for first element
                    'name' => $item['name'], // you may use name given by user - but do not have to
                    'href' => ModUtil::url('Clip', 'user', 'main', array('tid' => $extrainfo['tid'])),
                    'title' => $item['title'], // the same as for name - you may use user input
                    'className' => $item['className'],
                    'state' => $item['state'],
                    'lang' => $lang,
                    'lineno' => $lineno++,
                    'parent' => $item['parent'] // always use replaced item parent for element at first level
                )
            );
        }

        // need to set parent node id - if links are grouped - use item id
        // otherwise parent id of replaced menu node
        $parentNode = (!$extrainfo['flat']) ? $links['clip'][$lang]['id'] : $item['parent'];
    
        // Uses the API to get the list of publications
        // More parameters can be added here if needed, Clip_User_getall has a lot of options
        $result = ModUtil::apiFunc('Clip', 'user', 'getall',
                               array('tid'          => $extrainfo['tid'],
                                     'orderby'      => $extrainfo['orderby'],
                                     'itemsperpage' => $extrainfo['maxitems'],
                                     'checkPerm'    => false,
                                     'array'        => true));
        $publist = $result['publist'];

        foreach((array)$publist as $pub) {
            // skip publications not online
            if ($pub['core_online'] != 1) {
                continue;
            }

            $links[$pub['id']] = array(
                $lang => array(
                    'id' => $idoffset+$pub['id'],
                    'name' => $pub[$extrainfo['fieldname']],
                    'href' => ModUtil::url('Clip', 'user', 'display', array('tid' => $extrainfo['tid'], 'pid' => $pub['core_pid'])),
                    'title' => $pub[$extrainfo['fieldname']],
                    'className' => '',
                    'state' => $pub['core_visible'],
                    'lang' => $lang,
                    'lineno' => $lineno++,
                    'parent' => $parentNode
                )
            );
        }

        return $links;
    }


    /**
     * Return Content pages
     *
     * Syntax used in menutree
     * {ext:Blocks:content:[groupby=page&parent=1]}
     * Params in [] are optional and
     *      groupby = menuitem (default) or page, all other values stands for none
     *      parent - id of parent node - this allows to get specified node of Content pages
     *
     *
     * @param  array  $args['item']      menu node to be replaced
     * @param  string $args['lang']      current menu language
     * @param  string $args['extrainfo'] additional params
     * @return mixed  array of links if successful, false otherwise
     */
    public function content($args)
    {
        $item       = isset($args['item']) && !empty($args['item']) ? $args['item'] : null;
        $lang       = isset($args['lang']) && !empty($args['lang']) ? $args['lang'] : null;
        $extrainfo  = isset($args['extrainfo']) && !empty($args['extrainfo']) ? $args['extrainfo'] : null;
        // $item ang lang params are required
        if (!$item || !$lang) {
            return false;
        }

        // is there is extrainfo - convert it into array, parse_str is quite handy
        if ($extrainfo) {
            parse_str($extrainfo, $extrainfo);
        }
        $extrainfo['parent'] = isset($extrainfo['parent']) ? (int)$extrainfo['parent'] : 0;
        $extrainfo['groupby'] = isset($extrainfo['groupby'])? $extrainfo['groupby'] : 'menuitem';

        // get id for first element, use api func to avoid id conflicts inside menu
        $idoffset = Blocks_MenutreeUtil::getIdOffset($item['id']);
        $lineno = 0;
        $links = array();

        // if $extrainfo['group'] if false - don't group pages
        if ($extrainfo['groupby'] == 'menuitem') {
            $links['content'] = array(
                    $lang => array(
                            'id' => $idoffset++,
                            'name' => $item['name'],
                            'href' => ModUtil::url('Content'),
                            'title' => $item['title'],
                            'className' => $item['className'],
                            'state' => $item['state'],
                            'lang' => $lang,
                            'lineno' => $lineno++,
                            'parent' => $item['parent']
                    )
            );
        }
        // need to set parent node id according to groupby mode
        $parentNode = $extrainfo['groupby'] == 'menuitem' ? $links['content'][$lang]['id'] : $item['parent'];

        // set option and get page list
        $options = array(
                'orderBy' => 'setLeft',
                'makeTree' => false,
                'language' => $lang,
                'includeContent' => false,
                'includeLayout' => false,
                'includeCategories' => false,
                'filter' => array(
                    'superParentId' => $extrainfo['parent']
                )
        );
        $pages = ModUtil::apiFunc('Content', 'page', 'getPages', $options);

        $blocked = array();
        foreach ((array)$pages as $page) {
            // grouping - skip first page if pages are filtered by parent id
            // and grouping is not set to page
            if ($extrainfo['parent'] == $page['id'] && $extrainfo['groupby'] != 'page') {
                continue;
            }
            // skip pages which are disabled for display in menu
            if (in_array($page['parentPageId'], $blocked) || !$page['isInMenu']) {
                $blocked[] = $page['id'];
                continue;
            }
            $links[$page['id']] = array(
                    $lang => array(
                            'id' => $idoffset+$page['id'],
                            'name' => isset($page['translatedTitle']) && !empty($page['translatedTitle']) ? $page['translatedTitle'] : $page['title'],
                            'href' => ModUtil::url('Content', 'user', 'view', array('pid' => $page['id'])),
                            'title' => isset($page['translatedTitle']) && !empty($page['translatedTitle']) ? $page['translatedTitle'] : $page['title'],
                            'className' => '',
                            'state' => $page['isInMenu'],
                            'lang' => $lang,
                            'lineno' => $page['position'],
                            'parent' => isset($links[$page['parentPageId']][$lang]['id']) ? $links[$page['parentPageId']][$lang]['id'] : $parentNode
                    )
            );
        }

        return $links;
    }


    /**
     * Return list of user modules
     *
     * Syntax used in menutree
     * {ext:Blocks:modules:[flat]}
     * Last param is optional
     *
     * @param  array  $args['item']      menu node to be replaced
     * @param  string $args['lang']      current menu language
     * @param  string $args['extrainfo'] additional params - if 'flat' then return links ungrouped
     * @return mixed  array of links if successful, false otherwise
     */
    public function modules($args)
    {
        $item       = isset($args['item']) && !empty($args['item']) ? $args['item'] : null;
        $lang       = isset($args['lang']) && !empty($args['lang']) ? $args['lang'] : null;
        $extrainfo  = isset($args['extrainfo']) && !empty($args['extrainfo']) ? $args['extrainfo'] : null;
        // $item ang lang params are required
        if (!$item || !$lang) {
            return false;
        }

        // get id for first element, use api func to avoid id conflicts inside menu
        $idoffset = Blocks_MenutreeUtil::getIdOffset($item['id']);
        $lineno = 0;

        $links = array();
        // if $extrainfo if 'flat' - don't group links
        if ($extrainfo != 'flat') {
            $links['modules'] = array(
                    $lang => array(
                            'id' => $idoffset++,
                            'name' => $item['name'],
                            'href' => '',
                            'title' => $item['title'],
                            'className' => $item['className'],
                            'state' => $item['state'],
                            'lang' => $lang,
                            'lineno' => $lineno++,
                            'parent' => $item['parent']
                    )
            );
        }
        // need to set parent node id - if links are grouped - use your_accont item id
        // otherwise parent id of replaced menu node
        $parentNode = $extrainfo != 'flat' ? $links['modules'][$lang]['id'] : $item['parent'];

        $mods = ModUtil::getUserMods();

        foreach ($mods as $mod) {
            if (SecurityUtil::checkPermission("$mod[name]::", '::', ACCESS_OVERVIEW)) {
                $links[] = array(
                        $lang => array(
                                'id' => $idoffset++,
                                'name' => $mod['displayname'],
                                'href' => ModUtil::url($mod['name'], 'user', 'main'),
                                'title' => $mod['description'],
                                'className' => '',
                                'state' => 1,
                                'lang' => $lang,
                                'lineno' => $lineno++,
                                'parent' => $parentNode
                        )
                );
            }
        }

        return $links;
    }

    /**
     * Return some useful News links
     *
     * Syntax used in menutree
     * {ext:Blocks:news:[flat=BOOL&links=view,add,cat,arch|ALL]}
     * Params in [] are optional and
     *      flat - true or false, if set to true links are ungrouped (default is false)
     *      links - list of elements, default is ALL, avaiable items:
     *          - view - link to main News view
     *          - add - link do Submit News form
     *          - cat - list of News categories
     *          - arch - link to News archive
     *          Items are displayed in order provided in menutree
     *
     * @param  array  $args['item']      menu node to be replaced
     * @param  string $args['lang']      current menu language
     * @param  string $args['extrainfo'] additional params
     * @return mixed  array of links if successful, false otherwise
     */
    public function news($args)
    {
        $dom = ZLanguage::getModuleDomain('menutree');
        $item       = isset($args['item']) && !empty($args['item']) ? $args['item'] : null;
        $lang       = isset($args['lang']) && !empty($args['lang']) ? $args['lang'] : null;
        $bid        = isset($args['bid']) && !empty($args['bid']) ? $args['bid'] : null;
        $extrainfo  = isset($args['extrainfo']) && !empty($args['extrainfo']) ? $args['extrainfo'] : null;

        // $item ang lang params are required
        if (!$item || !$lang) {
            return false;
        }
        // is there is extrainfo - convert it into array, parse_str is quite handy
        if ($extrainfo) {
            parse_str($extrainfo, $extrainfo);
        }
        $extrainfo['flat'] = isset($extrainfo['flat'])? (bool)$extrainfo['flat'] : false;
        $extrainfo['links'] = isset($extrainfo['links'])? explode(',',$extrainfo['links']) : array('all');

        // get id for first element, use api func to avoid id conflicts inside menu
        $idoffset = Blocks_MenutreeUtil::getIdOffset($item['id']);
        $lineno = 0;

        // load plugin language file
        $modinfo = ModUtil::getInfo(ModUtil::getIdFromName('News'));

        $links = array();
        // build some link
        // you may use associative array keys
        if (!$extrainfo['flat']) {
            $links['news'] = array(
                    $lang => array(
                            'id' => $idoffset++,
                            'name' => $item['name'],
                            'href' => ModUtil::url('News'),
                            'title' => $item['title'],
                            'className' => $item['className'],
                            'state' => $item['state'],
                            'lang' => $lang,
                            'lineno' => $lineno++,
                            'parent' => $item['parent']
                    )
            );
        }
        $parentNode = !$extrainfo['flat'] ? $links['news'][$lang]['id'] : $item['parent'];

        if (in_array('all',$extrainfo['links']) || in_array('view',$extrainfo['links'])) {
            $links['view'] = array(
                    $lang => array(
                            'id' => $idoffset++,
                            'name' => $modinfo['displayname'],
                            'href' => ModUtil::url('News'),
                            'title' => __('View news', $dom),
                            'className' => '',
                            'state' => 1,
                            'lang' => $lang,
                            'lineno' => $lineno++,
                            'parent' => $parentNode
                    )
            );
        }
        if (in_array('all',$extrainfo['links']) || in_array('arch',$extrainfo['links'])) {
            $links['arch'] = array(
                    $lang => array(
                            'id' => $idoffset++,
                            'name' => __('Archive', $dom),
                            'href' => ModUtil::url('News','user','archives'),
                            'title' => __('Archive', $dom),
                            'className' => '',
                            'state' => 1,
                            'lang' => $lang,
                            'lineno' => $lineno++,
                            'parent' => $parentNode
                    )
            );
        }
        if (in_array('all',$extrainfo['links']) || in_array('add',$extrainfo['links'])) {
            $links['add'] = array(
                    $lang => array(
                            'id' => $idoffset++,
                            'name' => __('Submit news', $dom),
                            'href' => ModUtil::url('News','user','new'),
                            'title' => __('Submit news', $dom),
                            'className' => '',
                            'state' => 1,
                            'lang' => $lang,
                            'lineno' => $lineno++,
                            'parent' => $parentNode
                    )
            );
        }
        if (in_array('all',$extrainfo['links']) || in_array('cat',$extrainfo['links'])) {
            if (!$extrainfo['flat']) {
                $links['cat'] = array(
                        $lang => array(
                                'id' => $idoffset++,
                                'name' => __('Categories', $dom),
                                'href' => ModUtil::url('News'),
                                'title' => __('Categories', $dom),
                                'className' => '',
                                'state' => 1,
                                'lang' => $lang,
                                'lineno' => $lineno++,
                                'parent' => $parentNode
                        )
                );
            }
            $catParentNode = !$extrainfo['flat'] ? $links['cat'][$lang]['id'] : $item['parent'];

            Loader::loadClass('CategoryRegistryUtil');
            Loader::loadClass('CategoryUtil');

            $catregistry  = CategoryRegistryUtil::getRegisteredModuleCategories ('News', 'stories');
            if (!empty($catregistry)) {
                $multicategory = count($catregistry) > 1;
                $catLinks = array();
                foreach ($catregistry as $prop => $catid) {
                    if ($multicategory && !$extrainfo['flat']) {
                        $parentCategory = CategoryUtil::getCategoryByID ($catid);
                        $catLinks[$catid] =  array(
                                $lang => array(
                                        'id' => $idoffset++,
                                        'name' => isset($parentCategory['display_name'][$lang]) && !empty($parentCategory['display_name'][$lang]) ? $parentCategory['display_name'][$lang] : $parentCategory['name'],
                                        'href' => '',
                                        'title' => isset($parentCategory['display_name'][$lang]) && !empty($parentCategory['display_name'][$lang]) ? $parentCategory['display_name'][$lang] : $parentCategory['name'],
                                        'className' => '',
                                        'state' => 1,
                                        'lang' => $lang,
                                        'lineno' => $lineno++,
                                        'parent' => $catParentNode
                                )
                        );
                    }
                    $categories = CategoryUtil::getSubCategories($catid);
                    foreach ($categories as $cat) {
                        $catLinks[$cat['id']] = array(
                                $lang => array(
                                        'id' => $idoffset++,
                                        'name' => isset($cat['display_name'][$lang]) && !empty($cat['display_name'][$lang]) ? $cat['display_name'][$lang] : $cat['name'],
                                        'href' => ModUtil::url('News','user','view',array('prop' => $prop, 'cat' => $cat['name'])),
                                        'title' => isset($cat['display_name'][$lang]) && !empty($cat['display_name'][$lang]) ? $cat['display_name'][$lang] : $cat['name'],
                                        'className' => '',
                                        'state' => 1,
                                        'lang' => $lang,
                                        'lineno' => $lineno++,
                                        'parent' => isset($catLinks[$cat['parent_id']]) ? $catLinks[$cat['parent_id']][$lang]['id'] : $catParentNode
                                )
                        );
                    }
                }
            } elseif (!$extrainfo['flat']) {
                unset($links['cat']);
            }
        }

        // sort links in order provided in menutree
        if (!in_array('all',$extrainfo['links'])) {
            $sortedLinks = array();
            if (!$extrainfo['flat']) {
                $sortedLinks[] = $links['news'];
            }
            foreach ($extrainfo['links'] as $l) {
                if (isset($links[$l]) && !empty($links[$l])) {
                    $sortedLinks[] = $links[$l];
                }
                if ($l == 'cat') {
                    $sortedLinks = array_merge((array)$sortedLinks, (array)$catLinks);
                }
            }
            $links = $sortedLinks;
        }

        return $links;
    }


    /**
     * Return some common user links
     *
     * Syntax used in menutree
     * {ext:Blocks:userlinks:[flat]}
     * Last param is optional
     *
     * This plugin generates a list of  some common user links. The list looks as follows:
     * for loggedin users:
     *      Your Account
     *          Profile
     *          Private Messages (if there is some message module)
     *          Logout
     * for anonymous users:
     *      Your Account
     *          Login
     *          Register
     *          Lost Password
     *
     * If you provide an additional param extrainfo = flat, then the links are not grouped within
     * Your Accont element
     *
     * @param  array  $args['item']      menu node to be replaced
     * @param  string $args['lang']      current menu language
     * @param  string $args['extrainfo'] additional params - if 'flat' then return links ungrouped
     * @return mixed  array of links if successful, false otherwise
     */
    public function userlinks($args)
    {
        $dom = ZLanguage::getModuleDomain('menutree');
        $item       = isset($args['item']) && !empty($args['item']) ? $args['item'] : null;
        $lang       = isset($args['lang']) && !empty($args['lang']) ? $args['lang'] : null;
        $extrainfo  = isset($args['extrainfo']) && !empty($args['extrainfo']) ? $args['extrainfo'] : null;
        // $item ang lang params are required
        if (!$item || !$lang) {
            return false;
        }

        // get id for first element, use api func to avoid id conflicts inside menu
        $idoffset = Blocks_MenutreeUtil::getIdOffset($item['id']);
        $lineno = 0;

        // module config
        $profileModule = System::getVar('profilemodule') ? System::getVar('profilemodule') : 'Profile';
        $profileModule = ModUtil::available($profileModule) ? $profileModule : null;

        $messageModule = System::getVar('messagemodule') ? System::getVar('messagemodule') : 'InterCom';
        $messageModule = ModUtil::available($messageModule) ? $messageModule : null;

        $links = array();
        // if $extrainfo if 'flat' - don't group links in your_account node
        if ($extrainfo != 'flat') {
            $links['your_account'] = array(
                    $lang => array(
                            'id' => $idoffset++,
                            'name' => $item['name'],
                            'href' => ModUtil::url($profileModule),
                            'title' => $item['title'],
                            'className' => $item['className'],
                            'state' => $item['state'],
                            'lang' => $lang,
                            'lineno' => $lineno++,
                            'parent' => $item['parent']
                    )
            );
        }
        // need to set parent node id - if links are grouped - use your_accont item id
        // otherwise parent id of replaced menu node
        $parentNode = $extrainfo != 'flat' ? $links['your_account'][$lang]['id'] : $item['parent'];

        if (UserUtil::isLoggedIn()) {
            $links['profile'] = array(
                    $lang => array(
                            'id' => $idoffset++,
                            'name' => __('Profile', $dom),
                            'href' => ModUtil::url($profileModule),
                            'title' => __('Profile', $dom),
                            'className' => '',
                            'state' => 1,
                            'lang' => $lang,
                            'lineno' => $lineno++,
                            'parent' => $parentNode
                    )
            );
            if (!is_null($messageModule)) {
                $links['messages'] = array(
                        $lang => array(
                                'id' => $idoffset++,
                                'name' => __('Private messages', $dom),
                                'href' => ModUtil::url($messageModule),
                                'title' => __('Private messages', $dom),
                                'className' => '',
                                'state' => 1,
                                'lang' => $lang,
                                'lineno' => $lineno++,
                                'parent' => $parentNode
                        )
                );
            }
            $links['logout'] = array(
                    $lang => array(
                            'id' => $idoffset++,
                            'name' => __('Logout', $dom),
                            'href' => ModUtil::url('Users','user','logout'),
                            'title' => __('Logout', $dom),
                            'className' => '',
                            'state' => 1,
                            'lang' => $lang,
                            'lineno' => $lineno++,
                            'parent' => $parentNode
                    )
            );
        } else {
            $serviceManager = ServiceUtil::getManager();
            $request = $this->serviceManager->getService('request');

            $loginArgs = array();
            if ($request->isGet()) {
                $loginArgs['returnpage'] = urlencode(System::getCurrentUri());
            }
            $links['login'] = array(
                    $lang => array(
                            'id' => $idoffset++,
                            'name' => __('Login', $dom),
                            'href' => ModUtil::url('Users', 'user', 'login', $loginArgs),
                            'title' =>__('Login', $dom),
                            'className' => '',
                            'state' => 1,
                            'lang' => $lang,
                            'lineno' => $lineno++,
                            'parent' => $parentNode
                    )
            );
            $links['register'] = array(
                    $lang => array(
                            'id' => $idoffset++,
                            'name' => __('Register', $dom),
                            'href' => ModUtil::url('Users','user','register'),
                            'title' => __('Register', $dom),
                            'className' => '',
                            'state' => 1,
                            'lang' => $lang,
                            'lineno' => $lineno++,
                            'parent' => $parentNode
                    )
            );
            $links['lostpassword'] = array(
                    $lang => array(
                            'id' => $idoffset++,
                            'name' => __('Lost password', $dom),
                            'href' => ModUtil::url('Users','user','lostpassword'),
                            'title' => __('Lost password', $dom),
                            'className' => '',
                            'state' => 1,
                            'lang' => $lang,
                            'lineno' => $lineno++,
                            'parent' => $parentNode
                    )
            );
        }

        return $links;
    }
}
