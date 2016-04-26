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
 * Doctrine listener for the Logging doctrine template.
 *
 * @deprecated since 1.4.0
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

        $log = [
            'object_type' => $tableName,
            'object_id' => $data[$idColumn],
            'op' => $opType
        ];

        if ($opType == 'U') {
            $oldValues = $data->getLastModified(true);

            $diff = [];
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
