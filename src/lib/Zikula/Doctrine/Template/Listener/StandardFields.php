<?php
/**
 * Copyright 2010 Zikula Foundation.
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2.1 (or at your option, any later version).
 * @package Zikula
 * @subpackage Zikula_Doctrine
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * This listener takes care for setting the standard fields properly.
 */
class Zikula_Doctrine_Template_Listener_StandardFields extends Doctrine_Record_Listener
{
    /**
     * Current user's id
     *
     * @var int
     */
    private $uid = 0;

    public function __construct()
    {
        $this->uid = UserUtil::getVar('uid');
    }

    public function preInsert(Doctrine_Event $event)
    {
        $event->getInvoker()->cr_date = DateUtil::getDatetime();
        $event->getInvoker()->cr_uid = $this->uid;
        $event->getInvoker()->lu_date = DateUtil::getDatetime();
        $event->getInvoker()->lu_uid = $this->uid;
    }

    public function preUpdate(Doctrine_Event $event)
    {
        $event->getInvoker()->lu_date = DateUtil::getDatetime();
        $event->getInvoker()->lu_uid = $this->uid;
    }
}
