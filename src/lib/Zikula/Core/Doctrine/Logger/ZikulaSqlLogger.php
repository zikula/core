<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Core\Doctrine\Logger;

use Doctrine\DBAL\Logging\SQLLogger;
use EventUtil;
use Zikula\Core\Event\GenericEvent;

/**
 * Doctrine2 SQLLogger that sends sql queries to the zikula event manager.
 */
class ZikulaSqlLogger implements SQLLogger
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

        $zevent = new GenericEvent(null, $query);
        EventUtil::dispatch('log.sql', $zevent);
    }
}
