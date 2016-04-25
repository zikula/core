<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * This doctrine event listener sends a Zikula event (name: log.sql) for every executed sql query.
 *
 * Zikula_Event args:
 *   time: execution time in secounds
 *   query: sql query
 *
 * @deprecated since 1.4.0
 */
class Zikula_Doctrine_Listener_Profiler implements Doctrine_EventListener_Interface
{
    /**
     * Executed prior to a query.
     *
     * @param Doctrine_Event $event The Doctrine event instance.
     *
     * @return void
     */
    public function preQuery(Doctrine_Event $event)
    {
        $event->start();
    }

    /**
     * Executed following a query.
     *
     * @param Doctrine_Event $event The Doctrine event instance.
     *
     * @return void
     */
    public function postQuery(Doctrine_Event $event)
    {
        $event->end();
        $zevent = new \Zikula\Core\Event\GenericEvent(null, array('time'  => $event->getElapsedSecs(),
                                                         'query' => $event->getQuery()));
        EventUtil::dispatch('log.sql', $zevent);
    }

    /**
     * Executed prior to a Doctrine exec query.
     *
     * @param Doctrine_Event $event The Doctrine event instance.
     *
     * @return void
     */
    public function preExec(Doctrine_Event $event)
    {
        $event->start();
    }

    /**
     * Executed following a Doctrine exec query.
     *
     * @param Doctrine_Event $event The Doctrine event instance.
     *
     * @return void
     */
    public function postExec(Doctrine_Event $event)
    {
        $event->end();
        $zevent = new \Zikula\Core\Event\GenericEvent(null, array('time'  => $event->getElapsedSecs(),
                                                         'query' => $event->getQuery()));
        EventUtil::dispatch('log.sql', $zevent);
    }

    /**
     * Executed prior to a Doctrine statement exec query.
     *
     * @param Doctrine_Event $event The Doctrine event instance.
     *
     * @return void
     */
    public function preStmtExecute(Doctrine_Event $event)
    {
        $event->start();
    }

    /**
     * Executed following a Doctrine statement exec query.
     *
     * @param Doctrine_Event $event The Doctrine event instance.
     *
     * @return void
     */
    public function postStmtExecute(Doctrine_Event $event)
    {
        $event->end();
        $zevent = new \Zikula\Core\Event\GenericEvent(null, array('time'  => $event->getElapsedSecs(),
                                                         'query' => $event->getQuery()));
        EventUtil::dispatch('log.sql', $zevent);
    }

    /**
     * Transactons are not intercepted.
     *
     * @param Doctrine_Event $event The Doctrine event instance.
     *
     * @return void
     */
    public function preTransactionCommit(Doctrine_Event $event)
    {
    }

    /**
     * Transactons are not intercepted.
     *
     * @param Doctrine_Event $event The Doctrine event instance.
     *
     * @return void
     */
    public function postTransactionCommit(Doctrine_Event $event)
    {
    }

    /**
     * Transactons are not intercepted.
     *
     * @param Doctrine_Event $event The Doctrine event instance.
     *
     * @return void
     */
    public function preTransactionRollback(Doctrine_Event $event)
    {
    }

    /**
     * Transactons are not intercepted.
     *
     * @param Doctrine_Event $event The Doctrine event instance.
     *
     * @return void
     */
    public function postTransactionRollback(Doctrine_Event $event)
    {
    }

    /**
     * Transactons are not intercepted.
     *
     * @param Doctrine_Event $event The Doctrine event instance.
     *
     * @return void
     */
    public function preTransactionBegin(Doctrine_Event $event)
    {
    }

    /**
     * Transactons are not intercepted.
     *
     * @param Doctrine_Event $event The Doctrine event instance.
     *
     * @return void
     */
    public function postTransactionBegin(Doctrine_Event $event)
    {
    }

    /**
     * Connections are not intercepted.
     *
     * @param Doctrine_Event $event The Doctrine event instance.
     *
     * @return void
     */
    public function postConnect(Doctrine_Event $event)
    {
    }

    /**
     * Connections are not intercepted.
     *
     * @param Doctrine_Event $event The Doctrine event instance.
     *
     * @return void
     */
    public function preConnect(Doctrine_Event $event)
    {
    }

    /**
     * Doctrine errors are not intercepted.
     *
     * @param Doctrine_Event $event The Doctrine event instance.
     *
     * @return void
     */
    public function preError(Doctrine_Event $event)
    {
    }

    /**
     * Doctrine errors are not intercepted.
     *
     * @param Doctrine_Event $event The Doctrine event instance.
     *
     * @return void
     */
    public function postError(Doctrine_Event $event)
    {
    }

    /**
     * Doctrine fetches are not intercepted.
     *
     * @param Doctrine_Event $event The Doctrine event instance.
     *
     * @return void
     */
    public function preFetch(Doctrine_Event $event)
    {
    }

    /**
     * Doctrine fetches are not intercepted.
     *
     * @param Doctrine_Event $event The Doctrine event instance.
     *
     * @return void
     */
    public function postFetch(Doctrine_Event $event)
    {
    }

    /**
     * Doctrine fetches are not intercepted.
     *
     * @param Doctrine_Event $event The Doctrine event instance.
     *
     * @return void
     */
    public function preFetchAll(Doctrine_Event $event)
    {
    }

    /**
     * Doctrine fetches are not intercepted.
     *
     * @param Doctrine_Event $event The Doctrine event instance.
     *
     * @return void
     */
    public function postFetchAll(Doctrine_Event $event)
    {
    }

    /**
     * Doctrine statement prepares are not intercepted.
     *
     * @param Doctrine_Event $event The Doctrine event instance.
     *
     * @return void
     */
    public function prePrepare(Doctrine_Event $event)
    {
    }

    /**
     * Doctrine statement prepares are not intercepted.
     *
     * @param Doctrine_Event $event The Doctrine event instance.
     *
     * @return void
     */
    public function postPrepare(Doctrine_Event $event)
    {
    }
}
