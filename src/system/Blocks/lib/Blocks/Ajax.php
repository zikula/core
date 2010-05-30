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

class Blocks_Ajax extends AbstractController
{
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
            AjaxUtil::error($this->__('Sorry! You have not been granted access to this page.'));
        }

        if (!SecurityUtil::confirmAuthKey()) {
            AjaxUtil::error($this->__("Sorry! Invalid authorisation key ('authkey'). This is probably either because you pressed the 'Back' button to return to a page which does not allow that, or else because the page's authorisation key expired due to prolonged inactivity. Please refresh the page and try again."));
        }

        $blockorder = FormUtil::getPassedValue('blockorder');
        $position = FormUtil::getPassedValue('position');

        // empty block positions for this block zone
        $res = DBUtil::deleteObjectByID('block_placements', $position, 'pid');
        if (!$res) {
            AjaxUtil::error($this->__('Error! Could not save your changes.'));
        }

        // add new block positions
        $blockplacements = array();
        foreach ($blockorder as $order => $bid) {
            $blockplacements[] = array('bid' => $bid, 'pid' => $position, 'order' => $order);
        }
        $res = DBUtil::insertObjectArray($blockplacements, 'block_placements');
        if (!$res) {
            AjaxUtil::error($this->__('Error! Could not save your changes.'));
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
            AjaxUtil::error($this->__('Sorry! You have not been granted access to this page.'));
        }

        $bid = FormUtil::getPassedValue('bid', -1, 'GET');
        if ($bid == -1) {
            LogUtil::registerError($this->__('No block ID passed.'));
            AjaxUtil::output();
        }

        // read the block information
        $blockinfo = BlockUtil::getBlockInfo($bid);
        if ($blockinfo == false) {
            LogUtil::registerError($this->__f('Error! Could not retrieve block information for block ID %s.', DataUtil::formatForDisplay($bid)));
            AjaxUtil::output();
        }

        if ($blockinfo['active'] == 1) {
            ModUtil::apiFunc('Blocks', 'admin', 'deactivate', array('bid' => $bid));
        } else {
            ModUtil::apiFunc('Blocks', 'admin', 'activate', array('bid' => $bid));
        }

        AjaxUtil::output(array('bid' => $bid));
    }
}