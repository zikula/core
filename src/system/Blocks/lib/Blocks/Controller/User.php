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
 * Blocks_Controller_User class.
 */
class Blocks_Controller_User extends Zikula_AbstractController
{

    /**
     * The main blocks user function.
     *
     * @return HTML String.
     */
    public function main()
    {
        return LogUtil::registerError(__('Sorry! This module is not designed or is not currently configured to be accessed in the way you attempted.'), 403);
    }

    /**
     * Change the status of a block.
     *
     * Invert the status of a given block id (collapsed/uncollapsed).
     *
     * @return void
     */
    public function changestatus()
    {
        $bid = $this->request->get('bid');
        $uid = UserUtil::getVar('uid');

        $dbtable = DBUtil::getTables();
        $column = $dbtable['userblocks_column'];

        $where = "WHERE $column[bid]='" . DataUtil::formatForStore($bid) . "' AND $column[uid]='" . DataUtil::formatForStore($uid) . "'";
        $active = DBUtil::selectField('userblocks', 'active', $where);

        $obj = array();
        $obj['active'] = ($active ? 0 : 1);
        $where = "WHERE $column[uid]='" . DataUtil::formatForStore($uid) . "' AND $column[bid]='" . DataUtil::formatForStore($bid) . "'";
        $res = DBUtil::updateObject($obj, 'userblocks', $where);

        if (!$res) {
            return LogUtil::registerError($this->__('Error! An SQL error occurred.'));
        }

        // now lets get back to where we came from
        return $this->redirect(System::serverGetVar('HTTP_REFERER'));
    }

}