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

namespace PageLock\Controller;

use UserUtil;
use ModUtil;
use Zikula\Response\Ajax\AjaxResponse;

class AjaxController extends \Zikula_Controller_AbstractAjax
{
    /**
     * refresh a page lock
     *
     * @returns { hasLock: bool, message: string, lockedBy: string }
     */
    public function refreshpagelockAction(array $args = array())
    {
        $this->checkAjaxToken();
        $lockName = $this->request->request->get('lockname');

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

        return new AjaxResponse($lockInfo);
    }

    /**
     * change a page lock
     *
     */
    public function checkpagelockAction(array $args = array())
    {
        $this->checkAjaxToken();
        $lockName = $this->request->request->get('lockname');

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

        return new AjaxResponse($lockInfo);
    }

}
