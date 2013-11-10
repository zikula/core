<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @copyright Zikula Foundation
 * @package Zikula
 * @subpackage ZikulaBlocksModule
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Module\BlocksModule\Controller;

use FormUtil;
use UserUtil;
use BlockUtil;
use SecurityUtil;
use System;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Blocks_Controller_User class.
 */
class UserController extends \Zikula_AbstractController
{

    /**
     * The main blocks user function.
     *
     * @throws Symfony\Component\HttpKernel\Exception\NotFoundHttpException Thrown when accessed to indicate this function isn't valid
     * @return void
     */
    public function mainAction()
    {
        throw new NotFoundHttpException(__('Sorry! This module is not designed or is not currently configured to be accessed in the way you attempted.'));
    }

    /**
     * Display a block if is active
     *
     * @param mixed[] $args {<ul>
     *      <li>@type int  $bid          The id of the block</li>
     *      <li>@type bool $showinactive Override active status of block</li>
     *                       </ul>}
     *
     * @return Symfony\Component\HttpFoundation\Response symfony response object
     *
     * @throws Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException Throw if the user doesn't have edit permissions to the module
     */
    public function displayAction($args)
    {
        // Block Id - if passed - display the block
        $bid   = (int)FormUtil::getPassedValue('bid', isset($args['bid']) ? $args['bid'] : null, 'REQUEST');
        $showinactive = (bool)FormUtil::getPassedValue('showinactive', isset($args['showinactive']) ? $args['showinactive'] : false, 'REQUEST');

        // Security check for $showinactive only
        if ($showinactive && !SecurityUtil::checkPermission('ZikulaBlocksModule::', '::', ACCESS_EDIT)) {
            throw new AccessDeniedHttpException();
        }

        if ($bid > 0) {
            // {block} function in template is not checking for active status, so let's check here
            $blockinfo = BlockUtil::getBlockInfo($bid);
            if ($blockinfo['active'] || $showinactive) {
                $this->view->assign('args', $args);
                $this->view->assign('bid', $bid);

                return $this->view->fetch('blocks_user_display.tpl');
            }
        }

        return '';
    }

    /**
     * Change the status of a block.
     *
     * Invert the status of a given block id (collapsed/uncollapsed).
     *
     * @return void
     */
    public function changestatusAction()
    {
        $bid = FormUtil::getPassedValue('bid');
        $uid = UserUtil::getVar('uid');

        $entity = 'Zikula\Module\BlocksModule\Entity\UserBlockEntity';
        $item = $this->entityManager->getRepository($entity)->findOneBy(array('uid' => $uid, 'bid' => $bid));

        if ($item['active'] == 1) {
            $item['active'] = 0;
        } else {
            $item['active'] = 1;
        }

        $this->entityManager->flush();

        // now lets get back to where we came from
        $this->redirect(System::serverGetVar('HTTP_REFERER'));
    }
}