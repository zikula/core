<?php
/**
 * Zikula Application Framework
 * @copyright (c) 2001, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_System_Modules
 * @subpackage Blocks
 */

/**
 * The main blocks user function
 * @author Mark West
 * @return HTML String
 */
function blocks_user_main()
{
    return LogUtil::registerError(__('Sorry! This module is not designed or is not currently configured to be accessed in the way you attempted.'), 403);
}


/**
 * Change the status of a block
 * Invert the status of a given block id (collapsed/uncollapsed)
 *
 * @author Michael (acm3)
 * @author lophas
 * @return void
 */
function blocks_user_changestatus()
{
    /* Throwing an error under come conditions - commented out temporarily.
    if (!SecurityUtil::confirmAuthKey()) {
        echo __("Sorry! Invalid authorization key ('authkey'). This is probably either because you pressed the \'Back\' button to return to a page which does not allow that, or else because the page\'s authorization key expired due to prolonged inactivity. Please try again.");
        Theme::getInstance()->themefooter();
        System::shutdown();
    }
    */
    $bid = FormUtil::getPassedValue('bid');
    $uid = UserUtil::getVar('uid');

    $pntable = System::dbGetTables();
    $column  = $pntable['userblocks_column'];

    $where  = "WHERE $column[bid]='".DataUtil::formatForStore($bid)."' AND $column[uid]='".DataUtil::formatForStore($uid)."'";
    $active = DBUtil::selectField ('userblocks', 'active', $where);

    $obj = array();
    $obj['active'] = ($active ? 0 : 1);
    $where = "WHERE $column[uid]='".DataUtil::formatForStore($uid)."' AND $column[bid]='".DataUtil::formatForStore($bid)."'";
    $res = DBUtil::updateObject ($obj, 'userblocks', $where);

    if (!$res) {
        return LogUtil::registerError(__('Error! An SQL error occurred.'));
    }

    // now lets get back to where we came from
    return pnRedirect(pnServerGetVar('HTTP_REFERER'));
}
