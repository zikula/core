<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2002, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @author       Mark West
 * @package      Zikula_System_Modules
 * @subpackage   Admin
 */

class Admin_Block_Adminnav extends Zikula_Block
{
    /**
     * initialise block
     */
    public function init()
    {
        // Security
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
        foreach ($items as $item)
        {
            if (SecurityUtil::checkPermission('Admin::', "$item[catname]::$item[cid]", ACCESS_READ)) {
                $adminlinks = array();
                foreach ($adminmodules as $adminmodule) {
                    // Get all modules in the category
                    $catid = ModUtil::apiFunc('Admin', 'admin', 'getmodcategory',
                            array('mid' => ModUtil::getIdFromName($adminmodule['name'])));

                    if (($catid == $item['cid']) || (($catid == false) && ($item['cid'] == $this->getVar('defaultcategory')))) {
                        $modinfo = ModUtil::getInfo(ModUtil::getIdFromName($adminmodule['name']));
                        $menutexturl = ModUtil::url($modinfo['name'], 'admin');
                        $menutexttitle = $modinfo['displayname'];
                        $adminlinks[] = array('menutexturl' => $menutexturl,
                                'menutexttitle' => $menutexttitle);
                    }
                }
                $admincategories[] = array('url' => ModUtil::url('Admin', 'admin', 'adminpanel', array('cid' => $item['cid'])),
                        'title' => DataUtil::formatForDisplay($item['catname']),
                        'modules' => $adminlinks);
            }
        }


        $this->renderer->assign('admincategories', $admincategories);

        // Populate block info and pass to theme
        $blockinfo['content'] = $this->renderer->fetch('admin_block_adminnav.htm');

        return BlockUtil::themeBlock($blockinfo);
    }
}