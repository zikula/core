<?php
/**
 * Copyright Zikula Foundation 2010 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license MIT
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Doctrine2 SQLLogger that sends sql queries to the zikula event manager.
 */
class SystemPlugin_Doctrine_ZikulaSqlLogger implements Doctrine\DBAL\Logging\SQLLogger
{
    private $currentQuery;
    private $start;
    
    public function startQuery($sql, array $params = null, array $types = null)
    {
        $this->start = microtime(true);
        $this->currentQuery = array('query' => $sql, 'time' => 0);
    }

    public function stopQuery()
    {
        $query = $this->currentQuery;
        $query['time'] = microtime(true) - $this->start;
        
        $zevent = new Zikula_Event('log.sql', null, $query);
        EventUtil::notify($zevent);
    }
}
