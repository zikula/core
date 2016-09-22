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
 * Base doctrine listener with useful helper methods.
 *
 * @deprecated since 1.4.0
 */
abstract class Zikula_Doctrine_Template_Listener_Base extends Doctrine_Record_Listener
{
    /**
     * Extracts the Doctrine_Table instance from an event.
     *
     * @param Doctrine_Event $event Event
     *
     * @return Doctrine_Table Table
     * @throws LogicException If this method is unable to extract the Table instance
     */
    protected function getTableFromEvent(Doctrine_Event $event)
    {
        $treatedRecord = $event->getInvoker();
        if ($treatedRecord instanceof Doctrine_Record) {
            $recordClass = get_class($treatedRecord);

            return Doctrine::getTable($recordClass);
        } elseif ($treatedRecord instanceof Doctrine_Table) {
            return $treatedRecord;
        } else {
            throw new LogicException("Zikula_Doctrine_Template_Listener_Base::getTableFromEvent() unknown invoker: " + get_class($treatedRecord));
        }
    }

    /**
     * Extracts the table name from an event.
     *
     * @param Doctrine_Event $event Event
     *
     * @return string Table name
     */
    protected function getTableNameFromEvent(Doctrine_Event $event)
    {
        $tableRef = $this->getTableFromEvent($event);
        sscanf($tableRef->getTableName(), Doctrine_Manager::getInstance()->getAttribute(Doctrine::ATTR_TBLNAME_FORMAT), $tableName);

        return $tableName;
    }

    /**
     * Returns the column name of the primary key of the table from an event.
     *
     * @param Doctrine_Event $event Event
     *
     * @return string Column name of the primary key
     */
    protected function getIdColumnFromEvent(Doctrine_Event $event)
    {
        $tableRef = $this->getTableFromEvent($event);

        $idColumn = $tableRef->getIdentifier();

        if (is_array($idColumn)) {
            // TODO support multiple columns as primary key?
            $idColumn = $idColumn[0];
        }

        return $idColumn;
    }
}
