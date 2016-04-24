<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\AdminModule\Block;

use Zikula_View;
use SecurityUtil;
use BlockUtil;
use ModUtil;
use DataUtil;

/**
 * Administrative navigation block
 */
class AdminnavBlock extends \Zikula_Controller_AbstractBlock
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
     *
     * @return void
     */
    public function init()
    {
        SecurityUtil::registerPermissionSchema('ZikulaAdminModule:adminnavblock:', 'Block title::Block ID');
    }

    /**
     * get information on block
     *
     * @return array array of meta information on the block
     */
    public function info()
    {
        // Values
        return [
            'module'         => 'ZikulaAdminModule',
            'text_type'      => $this->__('Administration panel manager'),
            'text_type_long' => $this->__('Display administration categories and modules'),
            'allow_multiple' => false,
            'form_content'   => false,
            'form_refresh'   => false,
            'show_preview'   => true
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
     * @return string html of the rendered blcok
     */
    public function display($blockinfo)
    {
        // Security check
        if (!SecurityUtil::checkPermission('ZikulaAdminModule:adminnavblock', "$blockinfo[title]::$blockinfo[bid]", ACCESS_ADMIN)) {
            return;
        }

        // Get variables from content block
        $vars = BlockUtil::varsFromContent($blockinfo['content']);

        // Call the modules API to get the items
        if (!ModUtil::available('ZikulaAdminModule')) {
            return;
        }

        $items = ModUtil::apiFunc('ZikulaAdminModule', 'admin', 'getall');

        // Check for no items returned
        if (empty($items)) {
            return;
        }

        // get admin capable modules
        $adminModules = ModUtil::getAdminMods();

        // Display each item, permissions permitting
        $adminCategories = [];
        foreach ($items as $item) {
            if (!SecurityUtil::checkPermission('ZikulaAdminModule::', "$item[name]::$item[cid]", ACCESS_READ)) {
                continue;
            }

            $adminLinks = [];
            foreach ($adminModules as $adminModule) {
                // Get all modules in the category
                $catid = ModUtil::apiFunc('ZikulaAdminModule', 'admin', 'getmodcategory',
                                            ['mid' => ModUtil::getIdFromName($adminModule['name'])]);

                if ($catid == $item['cid'] || (false == $catid && $item['cid'] == $this->getVar('defaultcategory'))) {
                    $moduleInfo = ModUtil::getInfoFromName($adminModule['name']);
                    $adminLinks[] = [
                        'menutexturl' => ModUtil::url($moduleInfo['name'], 'admin'),
                        'menutexttitle' => $moduleInfo['displayname']
                    ];
                }
            }
            $adminCategories[] = [
                'url' => $this->get('router')->generate('zikulaadminmodule_admin_adminpanel', array('cid' => $item['cid'])),
                'title' => DataUtil::formatForDisplay($item['name']),
                'modules' => $adminLinks
            ];
        }

        $this->view->assign('admincategories', $adminCategories);

        // Populate block info and pass to theme
        $blockinfo['content'] = $this->view->fetch('Block/adminnav.tpl');

        return BlockUtil::themeBlock($blockinfo);
    }
}
