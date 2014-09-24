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

namespace Zikula\Module\BlocksModule\Controller;

use UserUtil;
use BlockUtil;
use SecurityUtil;
use System;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route; // used in annotations - do not remove
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method; // used in annotations - do not remove

/**
 * User controllers for the blocks module
 */
class UserController extends \Zikula_AbstractController
{

    /**
     * @Route("")
     *
     * The main blocks user function.
     *
     * @throws NotFoundHttpException Thrown when accessed to indicate this function isn't valid
     * @return void
     */
    public function indexAction()
    {
        throw new NotFoundHttpException(__('Sorry! This module is not designed or is not currently configured to be accessed in the way you attempted.'));
    }

    /**
     * @Route("display")
     *
     * Display a block if is active
     *
     *      int  $bid          The id of the block
     *      bool $showinactive Override active status of block
     *
     * @return Response symfony response object
     *
     * @throws AccessDeniedException Throw if the user doesn't have edit permissions to the module
     */
    public function displayAction()
    {
        // Block Id - if passed - display the block
        // check GET then POST
        $bid = (int)$this->request->get('bid', null);
        $showinactive = (int)$this->request->get('showinactive', null);

        // Security check for $showinactive only
        if ($showinactive && !SecurityUtil::checkPermission('ZikulaBlocksModule::', '::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        if ($bid > 0) {
            // {block} function in template is not checking for active status, so let's check here
            $blockinfo = BlockUtil::getBlockInfo($bid);
            if ($blockinfo['active'] || $showinactive) {
                $this->view->assign('bid', $bid);

                return new Response($this->view->fetch('blocks_user_display.tpl'));
            }
        }

        return new Response();
    }

    /**
     * @Route("/changestatus/{bid}", requirements={"bid" = "^[1-9]\d*$"})
     * @Method("GET")
     *
     * Change the status of a block.
     *
     * @param Request $request
     * @param integer $bid
     *
     * Invert the status of a given block id (collapsed/uncollapsed).
     *
     * @return RedirectResponse
     */
    public function changestatusAction(Request $request, $bid)
    {
        $uid = UserUtil::getVar('uid');

        $entity = 'ZikulaBlocksModule:UserBlockEntity';
        $item = $this->entityManager->getRepository($entity)->findOneBy(array('uid' => $uid, 'bid' => $bid));

        if ($item['active'] == 1) {
            $item['active'] = 0;
        } else {
            $item['active'] = 1;
        }

        $this->entityManager->flush();

        // now lets get back to where we came from
        return new RedirectResponse($request->server->get('HTTP_REFERER'));
    }
}