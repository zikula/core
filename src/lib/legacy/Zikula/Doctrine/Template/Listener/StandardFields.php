<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * This listener takes care for setting the standard fields properly.
 *
 * @deprecated since 1.4.0
 */
class Zikula_Doctrine_Template_Listener_StandardFields extends Doctrine_Record_Listener
{
    /**
     * Current user's id.
     *
     * @var integer
     */
    private $_uid = 0;

    /**
     * Setup this listener.
     */
    public function __construct()
    {
        @trigger_error('Doctrine 1 is deprecated, please use Doctrine 2 instead.', E_USER_DEPRECATED);

        $this->_uid = UserUtil::getVar('uid');
    }

    /**
     * Updates the standard fields before inserting new record.
     *
     * @param Doctrine_Event $event Event
     *
     * @return void
     */
    public function preInsert(Doctrine_Event $event)
    {
        @trigger_error('Doctrine 1 is deprecated, please use Doctrine 2 instead.', E_USER_DEPRECATED);

        $event->getInvoker()->cr_date = DateUtil::getDatetime();
        $event->getInvoker()->cr_uid = $this->_uid;
        $event->getInvoker()->lu_date = DateUtil::getDatetime();
        $event->getInvoker()->lu_uid = $this->_uid;
    }

    /**
     * Updates the standard fields before updating a record.
     *
     * @param Doctrine_Event $event Event
     *
     * @return void
     */
    public function preUpdate(Doctrine_Event $event)
    {
        @trigger_error('Doctrine 1 is deprecated, please use Doctrine 2 instead.', E_USER_DEPRECATED);

        $event->getInvoker()->lu_date = DateUtil::getDatetime();
        $event->getInvoker()->lu_uid = $this->_uid;
    }
}
