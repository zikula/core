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

namespace Blocks\Controller;

use LogUtil;
use FormUtil;
use UserUtil;
use BlockUtil;
use System;

/**
 * Blocks_Controller_User class.
 */
class UserController extends \Zikula_AbstractController
{

    /**
     * The main blocks user function.
     *
     * @return HTML String.
     */
    public function mainAction()
    {
        return LogUtil::registerError(__('Sorry! This module is not designed or is not currently configured to be accessed in the way you attempted.'), 403);
    }

    /**
     * Display a block if is active
     *
     * @param array $args Arguments.
     */
    public function displayAction($args)
    {
        // Block Id - if passed - display the block
        $bid   = (int)FormUtil::getPassedValue('bid', isset($args['bid']) ? $args['bid'] : null, 'REQUEST');

        if ($bid > 0) {
            // {block} function in template is not checking for active status, so let's check here
            $blockinfo = BlockUtil::getBlockInfo($bid);
            if ($blockinfo['active']) {
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

        $entity = $this->name . '\Entity\UserBlockEntity';
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