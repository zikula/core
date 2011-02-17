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
 * Blocks_Controller_Ajax class.
 */
class Blocks_Controller_Ajax extends Zikula_Controller_Ajax
{
    /**
     * Changeblockorder.
     *
     * @param blockorder array of sorted blocks (value = block id)
     * @param position int zone id
     *
     * @return mixed true or Ajax error
     */
    public function changeblockorder()
    {
        if (!SecurityUtil::checkPermission('Blocks::', '::', ACCESS_ADMIN)) {
            LogUtil::registerPermissionError(null, true);
            throw new Zikula_Exception_Forbidden();
        }

        if (!SecurityUtil::confirmAuthKey()) {
            LogUtil::registerAuthidError();
            throw new Zikula_Exception_Fatal();
        }

        $blockorder = FormUtil::getPassedValue('blockorder');
        $position = FormUtil::getPassedValue('position');

        // empty block positions for this block zone
        $res = DBUtil::deleteObjectByID('block_placements', $position, 'pid');
        if (!$res) {
            throw new Zikula_Exception_Fatal($this->__('Error! Could not save your changes.'));
        }

        // add new block positions
        $blockplacements = array();
        foreach ((array)$blockorder as $order => $bid) {
            $blockplacements[] = array('bid' => $bid, 'pid' => $position, 'order' => $order);
        }

        if (!empty($blockplacements)) {
            $res = DBUtil::insertObjectArray($blockplacements, 'block_placements');
            if (!$res) {
                throw new Zikula_Exception_Fatal($this->__('Error! Could not save your changes.'));
            }
        }

        return new Zikula_Response_Ajax(array('result' => true));
    }

    /**
     * Toggleblock.
     *
     * This function toggles active/inactive.
     *
     * @param bid int  id of block to toggle.
     * 
     * @return mixed true or Ajax error
     */
    public function toggleblock()
    {
        if (!SecurityUtil::checkPermission('Blocks::', '::', ACCESS_ADMIN)) {
            LogUtil::registerPermissionError(null, true);
            throw new Zikula_Exception_Forbidden();
        }

        $bid = FormUtil::getPassedValue('bid', -1, 'GET');
        if ($bid == -1) {
            throw new Zikula_Exception_Fatal($this->__('No block ID passed.'));
        }

        // read the block information
        $blockinfo = BlockUtil::getBlockInfo($bid);
        if ($blockinfo == false) {
            throw new Zikula_Exception_Fatal($this->__f('Error! Could not retrieve block information for block ID %s.', DataUtil::formatForDisplay($bid)));
        }

        if ($blockinfo['active'] == 1) {
            ModUtil::apiFunc('Blocks', 'admin', 'deactivate', array('bid' => $bid));
        } else {
            ModUtil::apiFunc('Blocks', 'admin', 'activate', array('bid' => $bid));
        }

        return new Zikula_Response_Ajax(array('bid' => $bid));
    }

}