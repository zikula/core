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
 * A block that shows who is currently using the system.
 */
class Users_Block_Online extends Zikula_Controller_AbstractBlock
{
    /**
     * Initialise the block.
     *
     * Adds the blocks security schema to the PN environment.
     *
     * @return void
     */
    public function init()
    {
        SecurityUtil::registerPermissionSchema('Onlineblock::', 'Block ID::');
    }

    /**
     * Return the block info.
     *
     * @return array The blockinfo structure.
     */
    public function info()
    {
        return array(
            'module'         => $this->name,
            'text_type'      => $this->__("Who's on-line"),
            'text_type_long' => $this->__('On-line block'),
            'allow_multiple' => false,
            'form_content'   => false,
            'form_refresh'   => false,
            'show_preview'   => true,
        );
    }

    /**
     * Display the output of the online block.
     *
     * @param array $blockinfo A blockinfo structure.
     *
     * @todo Move sql queries to calls to relevant API's.
     *
     * @return string|void The output.
     */
    public function display($blockinfo)
    {
        if (!SecurityUtil::checkPermission('Onlineblock::', $blockinfo['bid'].'::', ACCESS_READ)) {
            return;
        }

        if ($this->view->getCaching()) {
            // Here we use the user id as the cache id since the block shows user based
            // information; username and number of private messages.
            $uid = UserUtil::getVar('uid');
            $cacheid = $blockinfo['bkey'].'/bid'.$blockinfo['bid'].'/'.($uid ? $uid : 'guest');
            // We use an individual cache with a lifetime specified on the block configuration.
            $this->view->setCaching(Zikula_View::CACHE_INDIVIDUAL)
                       ->setCacheLifetime($blockinfo['refresh'])
                       ->setCacheId($cacheid);

            // check out if the contents are cached.
            // If this is the case, we do not need to make DB queries.
            if ($this->view->is_cached('users_block_online.tpl')) {
                $blockinfo['content'] = $this->view->fetch('users_block_online.tpl');

                return BlockUtil::themeBlock($blockinfo);
            }
        }

        $table = DBUtil::getTables();

        $sessioninfocolumn = $table['session_info_column'];
        $activetime = strftime('%Y-%m-%d %H:%M:%S', time() - (System::getVar('secinactivemins') * 60));

        $where = "WHERE $sessioninfocolumn[lastused] > '$activetime' AND $sessioninfocolumn[uid] > 0";
        $numusers = DBUtil::selectObjectCount('session_info', $where, 'uid', true);

        $where = "WHERE $sessioninfocolumn[lastused] > '$activetime' AND $sessioninfocolumn[uid] = '0'";
        $numguests = DBUtil::selectObjectCount('session_info', $where, 'ipaddr', true);

        $msgmodule = System::getVar('messagemodule', '');

        if ($msgmodule && SecurityUtil::checkPermission($msgmodule.'::', '::', ACCESS_READ) && UserUtil::isLoggedIn()) {
            // check if message module is available and add the necessary info
            if (ModUtil::available($msgmodule)) {
                $this->view->assign('messages', ModUtil::apiFunc($msgmodule, 'user', 'getmessagecount'));
            } else {
                $this->view->assign('messages', array());
            }
        }

        $this->view->assign('registerallowed', $this->getVar('reg_allowreg'))
                   ->assign('userscount', $numusers)
                   ->assign('guestcount', $numguests)
                   ->assign('msgmodule', $msgmodule);

        $blockinfo['content'] = $this->view->fetch('users_block_online.tpl');

        return BlockUtil::themeBlock($blockinfo);
    }
}
