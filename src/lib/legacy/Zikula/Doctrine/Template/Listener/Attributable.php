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
 * Doctrine listener for the Attributable doctrine template.
 *
 * @deprecated since 1.4.0
 */
class Zikula_Doctrine_Template_Listener_Attributable extends Zikula_Doctrine_Template_Listener_Base
{
    /**
     * Load all attributes after an select.
     *
     * @param Doctrine_Event $event Event.
     *
     * @return void
     */
    public function postHydrate(Doctrine_Event $event)
    {
        $data = $event->data;
        $tableName = $this->getTableNameFromEvent($event);
        $idColumn = $this->getIdColumnFromEvent($event);

        $dataForObjectUtil = array();
        $dataForObjectUtil[$idColumn] = $data[$idColumn];

        ObjectUtil::expandObjectWithAttributes($dataForObjectUtil, $tableName, $idColumn);

        if (isset($dataForObjectUtil['__ATTRIBUTES__'])) {
            $data['__ATTRIBUTES__'] = $dataForObjectUtil['__ATTRIBUTES__'];
        }
    }

    /**
     * Save all attributes after an insert.
     *
     * @param Doctrine_Event $event Event.
     *
     * @return void
     */
    public function postInsert(Doctrine_Event $event)
    {
        $data = $event->getInvoker();
        if (!isset($data['__ATTRIBUTES__'])) {
            return;
        }

        $tableName = $this->getTableNameFromEvent($event);
        $idColumn = $this->getIdColumnFromEvent($event);

        $dataForObjectUtil = array();
        $dataForObjectUtil[$idColumn] = $data[$idColumn];
        $dataForObjectUtil['__ATTRIBUTES__'] = $data['__ATTRIBUTES__'];

        if ($dataForObjectUtil['__ATTRIBUTES__'] instanceof ArrayObject) {
            $dataForObjectUtil['__ATTRIBUTES__'] = $dataForObjectUtil['__ATTRIBUTES__']->getArrayCopy();
        }

        ObjectUtil::storeObjectAttributes($dataForObjectUtil, $tableName, $idColumn, false);
    }

    /**
     * Update all attributes after an update.
     *
     * @param Doctrine_Event $event Event.
     *
     * @return void
     */
    public function postUpdate(Doctrine_Event $event)
    {
        $data = $event->getInvoker();
        if (!isset($data['__ATTRIBUTES__'])) {
            return;
        }

        $tableName = $this->getTableNameFromEvent($event);
        $idColumn = $this->getIdColumnFromEvent($event);

        $dataForObjectUtil = array();
        $dataForObjectUtil[$idColumn] = $data[$idColumn];
        $dataForObjectUtil['__ATTRIBUTES__'] = $data['__ATTRIBUTES__'];

        if ($dataForObjectUtil['__ATTRIBUTES__'] instanceof ArrayObject) {
            $dataForObjectUtil['__ATTRIBUTES__'] = $dataForObjectUtil['__ATTRIBUTES__']->getArrayCopy();
        }

        ObjectUtil::storeObjectAttributes($dataForObjectUtil, $tableName, $idColumn, true);
    }

    /**
     * Delete all attribtes after an delete.
     *
     * @param Doctrine_Event $event Event.
     *
     * @return void
     */
    public function postDelete(Doctrine_Event $event)
    {
        $data = $event->getInvoker();
        if (!isset($data['__ATTRIBUTES__'])) {
            return;
        }

        $tableName = $this->getTableNameFromEvent($event);
        $idColumn = $this->getIdColumnFromEvent($event);

        $dataForObjectUtil = array();
        $dataForObjectUtil[$idColumn] = $data[$idColumn];

        ObjectUtil::deleteObjectAttributes($dataForObjectUtil, $tableName, $idColumn);
    }
}
