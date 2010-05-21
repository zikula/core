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

/**
 * initialise block
 */
function Admin_adminnavblock_init()
{
    // Security
    SecurityUtil::registerPermissionSchema('Admin:adminnavblock:', 'Block title::Block ID');
}

/**
 * get information on block
 */
function Admin_adminnavblock_info()
{
    // Values
    return array('module'         => 'Admin',
                 'text_type'      => __('Administration panel manager'),
                 'text_type_long' => __('Display administration categories and modules'),
                 'allow_multiple' => false,
                 'form_content'   => false,
                 'form_refresh'   => false,
                 'show_preview'   => true);
}

/**
 * display block
 */
function Admin_adminnavblock_display($blockinfo)
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
    $adminmodules = pnModGetAdminMods();
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

                if (($catid == $item['cid']) || (($catid == false) && ($item['cid'] == ModUtil::getVar('Admin', 'defaultcategory')))) {
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

    // Create output object
    $pnRender = Renderer::getInstance('Admin');

    $pnRender->assign('admincategories', $admincategories);

    // Populate block info and pass to theme
    $blockinfo['content'] = $pnRender->fetch('admin_block_adminnav.htm');

    return BlockUtil::themeBlock($blockinfo);
}
