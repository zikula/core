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

/**
 * A user-customizable block.
 *
 * @package Zikula
 * @subpackage Users
 */
class Users_Block_Accountlinks extends Zikula_Block
{
    /**
     * initialise block
     */
    public function init()
    {
        SecurityUtil::registerPermissionSchema('Accountlinks::', 'Block title::');
    }

    /**
     * get information on block
     *
     * @return       array       The block information
     */
    public function info()
    {
        return array('module'         => 'Users',
                     'text_type'      => $this->__('User account'),
                     'text_type_long' => $this->__("User account links"),
                     'allow_multiple' => false,
                     'form_content'   => false,
                     'form_refresh'   => false,
                     'show_preview'   => true);
    }

    /**
     * display block
     *
     * @param        array       $blockinfo     a blockinfo structure
     * @return       output      the rendered bock
     */
    public function display($blockinfo)
    {
        if (!SecurityUtil::checkPermission('Accountlinks::', $blockinfo['title']."::", ACCESS_READ)) {
            return;
        }

        // Get variables from content block
        $vars = BlockUtil::varsFromContent($blockinfo['content']);

        // Call the modules API to get the items
        if (!ModUtil::available('Users')) {
            return;
        }

        $accountlinks = ModUtil::apiFunc('Users', 'user', 'accountLinks');

        // Check for no items returned
        if (empty($accountlinks)) {
            return;
        }

        $this->view->setCaching(false);

        $this->view->assign('accountlinks', $accountlinks);

        // Populate block info and pass to theme
        $blockinfo['content'] = $this->view->fetch('users_block_accountlinks.tpl');

        return BlockUtil::themeBlock($blockinfo);
    }
}
