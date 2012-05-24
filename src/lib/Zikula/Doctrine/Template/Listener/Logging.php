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
 * Doctrine listener for the Logging doctrine template.
 */
class Zikula_Doctrine_Template_Listener_Logging extends Zikula_Doctrine_Template_Listener_Base
{
    /**
     * Creates a new logging instance.
     *
     * @param Doctrine_Event $event  Event.
     * @param int            $opType I/U/D for Insert/Update/Delete.
     *
     * @return void
     */
    private function _createOperationLog(Doctrine_Event $event, $opType = 'I')
    {
        $data = $event->getInvoker();
        $tableName = $this->getTableNameFromEvent($event);
        $idColumn = $this->getIdColumnFromEvent($event);

        $log = array();
        $log['object_type'] = $tableName;
        $log['object_id']   = $data[$idColumn];
        $log['op']          = $opType;

        if ($opType == 'U') {
            $oldValues = $data->getLastModified(true);

            $diff = array();
            foreach ($oldValues as $column => $oldValue) {
                if (empty($oldValue) && isset($data[$column]) && !empty($data[$column])) {
                    $diff[$column] = 'I: '.$data[$column];
                } elseif (!empty($oldValue) && isset($data[$column]) && !empty($data[$column])) {
                    $diff[$column] = 'U: '.$data[$column];
                } elseif (!empty($oldValue) && empty($data[$column])) {
                    $diff[$column] = 'D: '.$oldValue;
                }
            }

            $log['diff'] = serialize($diff);
        } else {
            // Convert object to array (otherwise we serialize the record object)
            $log['diff'] = serialize($data->toArray());
        }

        DBUtil::insertObject($log, 'objectdata_log');
    }


    /**
     * Creates an log for an insert.
     *
     * @param Doctrine_Event $event Event.
     *
     * @return void
     */
    public function postInsert(Doctrine_Event $event)
    {
        $this->_createOperationLog($event, 'I');
    }

    /**
     * Creates an log for an udpate.
     *
     * @param Doctrine_Event $event Event.
     *
     * @return void
     */
    public function postUpdate(Doctrine_Event $event)
    {
        $this->_createOperationLog($event, 'U');
    }

    /**
     * Creates an log for an delete.
     *
     * @param Doctrine_Event $event Event.
     *
     * @return void
     */
    public function postDelete(Doctrine_Event $event)
    {
        $this->_createOperationLog($event, 'D');
    }
}
