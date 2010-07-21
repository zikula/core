<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2001, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_System_Modules
 * @subpackage Blocks
 */

class Blocks_Controller_Ajax extends Zikula_Controller
{
    public function _postSetup()
    {
        // no need for a Zikula_View so override it.
    }
    
    /**
     * changeblockorder
     *
     * @author Frank Schummertz
     * @param blockorder array of sorted blocks (value = block id)
     * @param position int zone id
     * @return mixed true or Ajax error
     */
    public function changeblockorder()
    {
        if (!SecurityUtil::checkPermission('Blocks::', '::', ACCESS_ADMIN)) {
            return AjaxUtil::error(LogUtil::registerPermissionError(null,true));
        }

        if (!SecurityUtil::confirmAuthKey()) {
            return AjaxUtil::error(LogUtil::registerAuthidError());
        }

        $blockorder = FormUtil::getPassedValue('blockorder');
        $position = FormUtil::getPassedValue('position');

        // empty block positions for this block zone
        $res = DBUtil::deleteObjectByID('block_placements', $position, 'pid');
        if (!$res) {
            return AjaxUtil::error(LogUtil::registerError($this->__('Error! Could not save your changes.')));
        }

        // add new block positions
        $blockplacements = array();
        foreach ((array)$blockorder as $order => $bid) {
            $blockplacements[] = array('bid' => $bid, 'pid' => $position, 'order' => $order);
        }

        if (!empty($blockplacements)) {
            $res = DBUtil::insertObjectArray($blockplacements, 'block_placements');
            if (!$res) {
                return AjaxUtil::error(LogUtil::registerError($this->__('Error! Could not save your changes.')));
            }
        }

        return array('result' => true);
    }

    /**
     * toggleblock
     * This function toggles active/inactive
     *
     * @author Frank Schummertz
     * @param bid int  id of block to toggle
     * @return mixed true or Ajax error
     */
    public function toggleblock()
    {
        if (!SecurityUtil::checkPermission('Blocks::', '::', ACCESS_ADMIN)) {
            return AjaxUtil::error(LogUtil::registerPermissionError(null,true));
        }

        $bid = FormUtil::getPassedValue('bid', -1, 'GET');
        if ($bid == -1) {
            return AjaxUtil::error(LogUtil::registerError($this->__('No block ID passed.')));
        }

        // read the block information
        $blockinfo = BlockUtil::getBlockInfo($bid);
        if ($blockinfo == false) {
            return AjaxUtil::error(LogUtil::registerError($this->__f('Error! Could not retrieve block information for block ID %s.', DataUtil::formatForDisplay($bid))));
        }

        if ($blockinfo['active'] == 1) {
            ModUtil::apiFunc('Blocks', 'admin', 'deactivate', array('bid' => $bid));
        } else {
            ModUtil::apiFunc('Blocks', 'admin', 'activate', array('bid' => $bid));
        }

        AjaxUtil::output(array('bid' => $bid));
    }
}