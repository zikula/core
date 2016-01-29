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

namespace Zikula\BlocksModule\Controller;

use UserUtil;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Zikula\Core\Controller\AbstractController;

/**
 * User controllers for the blocks module
 */
class UserController extends AbstractController
{
    /**
     * @Route("/changestatus/{bid}", requirements={"bid" = "^[1-9]\d*$"})
     * @Method("GET")
     *
     * Invert the status of a block.
     *
     * @param Request $request
     * @param integer $bid
     *
     * @return RedirectResponse
     */
    public function changestatusAction(Request $request, $bid)
    {
        $uid = UserUtil::getVar('uid');

        $item = $this->getDoctrine()->getManager()->getRepository('ZikulaBlocksModule:UserBlockEntity')->findOneBy(array('uid' => $uid, 'bid' => $bid));
        $item->setActive($item->getActive() == 1 ? 0 : 1);
        $this->getDoctrine()->getManager()->flush();

        return new RedirectResponse($request->server->get('HTTP_REFERER'));
    }
}
