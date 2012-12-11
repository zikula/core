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

class Admin_Block_Adminnav extends Zikula_Controller_AbstractBlock
{
    /**
     * Post initialise.
     *
     * @return void
     */
    protected function postInitialize()
    {
        // In this block we do not want caching.
        $this->view->setCaching(Zikula_View::CACHE_DISABLED);
    }

    /**
     * initialise block
     */
    public function init()
    {
        SecurityUtil::registerPermissionSchema('Admin:adminnavblock:', 'Block title::Block ID');
    }

    /**
     * get information on block
     */
    public function info()
    {
        // Values
        return array('module'         => 'Admin',
                     'text_type'      => $this->__('Administration panel manager'),
                     'text_type_long' => $this->__('Display administration categories and modules'),
                     'allow_multiple' => false,
                     'form_content'   => false,
                     'form_refresh'   => false,
                     'show_preview'   => true);
    }

    /**
     * display block
     */
    public function display($blockinfo)
    {
        // Security check
        if (!SecurityUtil::checkPermission('Admin:adminnavblock', "$blockinfo[title]::$blockinfo[bid]", ACCESS_ADMIN)) {
            return;
        }

        // Get variables from content block
        $vars = BlockUtil::varsFromContent($blockinfo['content']);

        // Call the modules API to get the items
        if (!ModUtil::available('Admin')) {
            return;
        }

        $items = ModUtil::apiFunc('Admin', 'admin', 'getall');

        // Check for no items returned
        if (empty($items)) {
            return;
        }

        // get admin capable modules
        $adminmodules = ModUtil::getAdminMods();
        $adminmodulescount = count($adminmodules);

        // Display each item, permissions permitting
        $admincategories = array();
        foreach ($items as $item) {
            if (SecurityUtil::checkPermission('Admin::', "$item[name]::$item[cid]", ACCESS_READ)) {
                $adminlinks = array();
                foreach ($adminmodules as $adminmodule) {
                    // Get all modules in the category
                    $catid = ModUtil::apiFunc('Admin', 'admin', 'getmodcategory',
                                              array('mid' => ModUtil::getIdFromName($adminmodule['name'])));

                    if (($catid == $item['cid']) || (($catid == false) && ($item['cid'] == $this->getVar('defaultcategory')))) {
                        $modinfo = ModUtil::getInfoFromName($adminmodule['name']);
                        $menutexturl = ModUtil::url($modinfo['name'], 'admin');
                        $menutexttitle = $modinfo['displayname'];
                        $adminlinks[] = array('menutexturl' => $menutexturl,
                                              'menutexttitle' => $menutexttitle);
                    }
                }
                $admincategories[] = array('url' => ModUtil::url('Admin', 'admin', 'adminpanel', array('cid' => $item['cid'])),
                                           'title' => DataUtil::formatForDisplay($item['name']),
                                           'modules' => $adminlinks);
            }
        }

        $this->view->assign('admincategories', $admincategories);

        // Populate block info and pass to theme
        $blockinfo['content'] = $this->view->fetch('admin_block_adminnav.tpl');

        return BlockUtil::themeBlock($blockinfo);
    }
}
