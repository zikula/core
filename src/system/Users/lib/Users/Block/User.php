<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage Users
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * A user-customizable block.
 */
class Users_Block_User extends Zikula_Controller_AbstractBlock
{
    /**
     * Initialise block.
     *
     * @return void
     */
    public function init()
    {
        SecurityUtil::registerPermissionSchema('Userblock::', 'Block title::');
    }

    /**
     * Get information on block.
     *
     * @return array The block information
     */
    public function info()
    {
        return array(
            'module'         => $this->name,
            'text_type'      => $this->__('User'),
            'text_type_long' => $this->__("User's custom box"),
            'allow_multiple' => false,
            'form_content'   => false,
            'form_refresh'   => false,
            'show_preview'   => true,
        );
    }

    /**
     * Display block.
     *
     * @param array $blockInfo A blockinfo structure.
     *
     * @return string|void The rendered block.
     */
    public function display($blockInfo)
    {
        if (!SecurityUtil::checkPermission('Userblock::', $blockInfo['title']."::", ACCESS_READ)) {
            return;
        }

        if (UserUtil::isLoggedIn() && UserUtil::getVar('ublockon') == 1) {
            if (!isset($blockInfo['title']) || empty($blockInfo['title'])) {
                $blockInfo['title'] = $this->__f('Custom block content for %s', UserUtil::getVar('name'));
            }
            $blockInfo['content'] = nl2br(UserUtil::getVar('ublock'));

            return BlockUtil::themeBlock($blockInfo);
        }

        return;
    }
}
