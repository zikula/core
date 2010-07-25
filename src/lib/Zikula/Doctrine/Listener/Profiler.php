<?php
/**
 * Copyright 2010 Zikula Foundation.
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage Zikula_Doctrine
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * This doctrine event listener sends a zikula event (name: log.sql) for every executed sql query.
 *
 * Zikula_Event args:
 *   time: execution time in secounds
 *   query: sql query
 */
class Zikula_Doctrine_Listener_Profiler implements Doctrine_EventListener_Interface
{
    public function preQuery(Doctrine_Event $event) {
        $event->start();
    }

    public function postQuery(Doctrine_Event $event) {
        $event->end();
        $zevent = new Zikula_Event('log.sql', null, array('time'  => $event->getElapsedSecs(),
                                                         'query' => $event->getQuery()));
        EventUtil::notify($zevent);
    }

    public function preExec(Doctrine_Event $event) {
        $event->start();
    }
    
    public function postExec(Doctrine_Event $event) {
        $event->end();
        $zevent = new Zikula_Event('log.sql', null, array('time'  => $event->getElapsedSecs(),
                                                         'query' => $event->getQuery()));
        EventUtil::notify($zevent);
    }

    public function preStmtExecute(Doctrine_Event $event) {
        $event->start();
    }

    public function postStmtExecute(Doctrine_Event $event) {
        $event->end();
        $zevent = new Zikula_Event('log.sql', null, array('time'  => $event->getElapsedSecs(),
                                                         'query' => $event->getQuery()));
        EventUtil::notify($zevent);
    }

    public function preTransactionCommit(Doctrine_Event $event) {}
    public function postTransactionCommit(Doctrine_Event $event) {}

    public function preTransactionRollback(Doctrine_Event $event) {}
    public function postTransactionRollback(Doctrine_Event $event) {}

    public function preTransactionBegin(Doctrine_Event $event) {}
    public function postTransactionBegin(Doctrine_Event $event) {}

    public function postConnect(Doctrine_Event $event) {}
    public function preConnect(Doctrine_Event $event) {}

    public function preError(Doctrine_Event $event) {}
    public function postError(Doctrine_Event $event) {}

    public function preFetch(Doctrine_Event $event) {}
    public function postFetch(Doctrine_Event $event) {}

    public function preFetchAll(Doctrine_Event $event) {}
    public function postFetchAll(Doctrine_Event $event) {}

    public function prePrepare(Doctrine_Event $event) {}
    public function postPrepare(Doctrine_Event $event) {}
}


