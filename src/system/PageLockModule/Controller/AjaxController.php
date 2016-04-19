<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\PageLockModule\Controller;

use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Zikula\Core\Controller\AbstractController;
use Zikula\Core\Response\Ajax\AjaxResponse;

/**
 * @Route("/ajax")
 *
 * Ajax controllers for the pagelock module
 */
class AjaxController extends AbstractController
{
    /**
     * @Route("/refresh", options={"expose"=true})
     * @Method("POST")
     *
     * refresh a page lock
     *
     * @param Request $request
     *
     * @return AjaxResponse containing { hasLock: bool, message: string, lockedBy: string, message:string|null }
     */
    public function refreshpagelockAction(Request $request)
    {
        $lockInfo = $this->getLockInfo($request);

        return new AjaxResponse($lockInfo);
    }

    /**
     * @Route("/check", options={"expose"=true})
     * @Method("POST")
     *
     * change a page lock
     *
     * @param Request $request
     *
     * @return AjaxResponse containing { hasLock: bool, message: string, lockedBy: string, message:string|null }
     */
    public function checkpagelockAction(Request $request)
    {
        $lockInfo = $this->getLockInfo($request);

        return new AjaxResponse($lockInfo);
    }

    /**
     * Requires a lock and returns it's information.
     *
     * @param Request $request
     *
     * @return array Lock information data.
     */
    private function getLockInfo(Request $request)
    {
        $lockName = $request->request->get('lockname');

        $lockInfo = $this->get('zikula_pagelock_module.api.locking')->requireLock($lockName, \UserUtil::getVar('uname'), $request->getClientIp(), $request->getSession()->getId());

        $lockInfo['message'] = $lockInfo['hasLock'] ? null : $this->__('Error! Lock broken!');

        return $lockInfo;
    }
}
