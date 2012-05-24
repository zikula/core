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

/**
 * Blocks_Controller_User class.
 */
class Blocks_Controller_User extends Zikula_AbstractController
{

    /**
     * The main blocks user function.
     *
     * @return HTML String.
     */
    public function main()
    {
        return LogUtil::registerError(__('Sorry! This module is not designed or is not currently configured to be accessed in the way you attempted.'), 403);
    }

    /**
     * Change the status of a block.
     *
     * Invert the status of a given block id (collapsed/uncollapsed).
     *
     * @return void
     */
    public function changestatus()
    {
        $bid = FormUtil::getPassedValue('bid');
        $uid = UserUtil::getVar('uid');

        $entity = $this->name . '_Entity_UserBlock';
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
