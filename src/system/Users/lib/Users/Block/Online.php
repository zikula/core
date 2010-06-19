<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2.1 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * A block that shows who is currently using the system.
 *
 * @package Zikula
 * @subpackage Users
 */
class Users_Block_Online extends Zikula_Block
{
    /**
     * return the block info
    */
    public function info()
    {
        return array(
        'module'         => 'Users',
        'text_type'      => $this->__("Who's on-line"),
        'text_type_long' => $this->__('On-line block'),
        'allow_multiple' => false,
        'form_content'   => false,
        'form_refresh'   => false,
        'show_preview'   => true
        );
    }

    /**
     * initialise the block
     *
     * Adds the blocks security schema to the PN environment
    */
    public function init()
    {
        // Security
        SecurityUtil::registerPermissionSchema('Onlineblock::', 'Block title::');
    }

    /**
     * Display the block
     *
     * Display the output of the online block
     *
     * @todo move sql queries to calls to relevant API's
    */
    public function display($row)
    {
        if (!SecurityUtil::checkPermission('Onlineblock::', $row['title'].'::', ACCESS_READ)) {
            return;
        }

        // create the output object
        $pnr = Renderer::getInstance('Users');

        // Here we use the user id as the cache id since the block shows user based
        // information; username and number of private messages
        $pnr->cache_id = UserUtil::getVar('uid');

        // check out if the contents are cached.
        // If this is the case, we do not need to make DB queries.
        if ($pnr->is_cached('users_block_online.htm')) {
            $row['content'] = $pnr->fetch('users_block_online.htm');
            return BlockUtil::themeBlock($row);
        }

        $pntable = System::dbGetTables();

        $sessioninfocolumn = $pntable['session_info_column'];
        $activetime = strftime('%Y-%m-%d %H:%M:%S', time() - (System::getVar('secinactivemins') * 60));

        $where = "WHERE $sessioninfocolumn[lastused] > '$activetime' AND $sessioninfocolumn[uid] > 0";
        $numusers = DBUtil::selectObjectCount('session_info', $where, 'uid', true);

        $where = "WHERE $sessioninfocolumn[lastused] > '$activetime' AND $sessioninfocolumn[uid] = '0'";
        $numguests = DBUtil::selectObjectCount('session_info', $where, 'ipaddr', true);

        $pnr->assign('registerallowed', ModUtil::getVar('Users', 'reg_allowreg'));
        $pnr->assign('loggedin', UserUtil::isLoggedIn());
        $pnr->assign('userscount', $numusers );
        $pnr->assign('guestcount', $numguests );

        $pnr->assign('username', UserUtil::getVar('uname'));

        $msgmodule = System::getVar('messagemodule', '');
        if (SecurityUtil::checkPermission($msgmodule.'::', '::', ACCESS_READ) && UserUtil::isLoggedIn()) {
            // check if message module is available and add the necessary info
            $pnr->assign('msgmodule', $msgmodule);
            if (ModUtil::available($msgmodule)) {
                $pnr->assign('messages', ModUtil::apiFunc($msgmodule, 'user', 'getmessagecount'));
            } else {
                $pnr->assign('messages', array());
            }
        }

        $row['content'] = $pnr->fetch('users_block_online.htm');
        return BlockUtil::themeBlock($row);
    }
}
