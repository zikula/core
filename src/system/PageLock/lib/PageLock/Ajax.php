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

class PageLock_Ajax extends Zikula_Controller
{
    /**
     * refresh a page lock
     *
     * @returns { hasLock: bool, message: string, lockedBy: string }
     */
    public function refreshpagelock($args)
    {
        $lockName = FormUtil::getPassedValue('lockname', null, 'post');

        $uname = UserUtil::getVar('uname');

        $lockInfo = ModUtil::apiFunc('PageLock', 'user', 'requireLock',
                array('lockName'      => $lockName,
                'sessionId'     => session_id(),
                'lockedByTitle' => $uname,
                'lockedByIPNo'  => $_SERVER['REMOTE_ADDR']));

        if (!$lockInfo['hasLock']) {
            $lockInfo['message'] = $this->__('Error! Lock broken!');
        } else {
            $lockInfo['message'] = null;
        }
        return $lockInfo;
    }

    /**
     * change a page lock
     *
     */
    public function checkpagelock($args)
    {
        $lockName = FormUtil::getPassedValue('lockname', null, 'post');

        $uname = UserUtil::getVar('uname');

        $lockInfo = ModUtil::apiFunc('PageLock', 'user', 'requireLock',
                array('lockName'      => $lockName,
                'sessionId'     => session_id(),
                'lockedByTitle' => $uname,
                'lockedByIPNo'  => $_SERVER['REMOTE_ADDR']));

        if (!$lockInfo['hasLock']) {
            $lockInfo['message'] = $this->__('Error! Lock broken!');
        } else {
            $lockInfo['message'] = null;
        }
        return $lockInfo;
    }

}