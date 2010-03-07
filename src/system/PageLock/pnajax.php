<?php
/**
 * Zikula Application Framework
 * @copyright (c) 2007, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @author Jorn Wildt
 * @package Zikula_System_Modules
 * @subpackage PageLock
 */


/**
 * refresh a page lock
 *
 * @returns { hasLock: bool, message: string, lockedBy: string }
 */
function PageLock_ajax_refreshpagelock($args)
{
    $lockName = FormUtil::getPassedValue('lockname', null, 'post');

    $uname = pnUserGetVar('uname');

    $lockInfo = pnModAPIFunc('PageLock', 'user', 'requireLock',
                             array('lockName'      => $lockName,
                                   'sessionId'     => session_id(),
                                   'lockedByTitle' => $uname,
                                   'lockedByIPNo'  => $_SERVER['REMOTE_ADDR']));

    if (!$lockInfo['hasLock'])
        $lockInfo['message'] = __('Error! Lock broken!');
    else
        $lockInfo['message'] = null;
    return $lockInfo;
}

/**
 * change a page lock
 *
 */
function PageLock_ajax_checkpagelock($args)
{
    $lockName = FormUtil::getPassedValue('lockname', null, 'post');

    $uname = pnUserGetVar('uname');

    $lockInfo = pnModAPIFunc('PageLock', 'user', 'requireLock',
                             array('lockName'      => $lockName,
                                   'sessionId'     => session_id(),
                                   'lockedByTitle' => $uname,
                                   'lockedByIPNo'  => $_SERVER['REMOTE_ADDR']));

    if (!$lockInfo['hasLock'])
        $lockInfo['message'] = __('Error! Lock broken!');
    else
        $lockInfo['message'] = null;
    return $lockInfo;
}

