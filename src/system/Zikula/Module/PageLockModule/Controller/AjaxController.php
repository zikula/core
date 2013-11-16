<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Module\PageLockModule\Controller;

use UserUtil;
use ModUtil;
use Zikula\Core\Response\Ajax\AjaxResponse;

/**
 * Ajax controllers for the pagelock module
 */
class AjaxController extends \Zikula_Controller_AbstractAjax
{
    /**
     * refresh a page lock
     *
     * @returns AjaxResponse containing { hasLock: bool, message: string, lockedBy: string, message:string|null }
     */
    public function refreshpagelockAction()
    {
        $this->checkAjaxToken();
        $lockName = $this->request->request->get('lockname');

        $uname = UserUtil::getVar('uname');

        $lockInfo = ModUtil::apiFunc('ZikulaPageLockModule', 'user', 'requireLock',
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
     * @returns AjaxResponse containing { hasLock: bool, message: string, lockedBy: string, message:string|null }
     */
    public function checkpagelockAction()
    {
        $this->checkAjaxToken();
        $lockName = $this->request->request->get('lockname');

        $uname = UserUtil::getVar('uname');

        $lockInfo = ModUtil::apiFunc('ZikulaPageLockModule', 'user', 'requireLock',
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
