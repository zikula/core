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
 * Doctrine listener for the MetaData doctrine template.
 */
class Zikula_Doctrine_Template_Listener_MetaData extends Zikula_Doctrine_Template_Listener_Base
{
    /**
     * Load meta data after an select.
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

        ObjectUtil::expandObjectWithMeta($dataForObjectUtil, $tableName, $idColumn);

        if (isset($dataForObjectUtil['__META__'])) {
            $data['__META__'] = $dataForObjectUtil['__META__'];
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
        if (!isset($data['__META__'])) {
            return;
        }

        $tableName = $this->getTableNameFromEvent($event);
        $idColumn = $this->getIdColumnFromEvent($event);

        $dataForObjectUtil = array();
        $dataForObjectUtil[$idColumn] = $data[$idColumn];
        $dataForObjectUtil['__META__'] = $data['__META__'];

        if ($dataForObjectUtil['__META__'] instanceof ArrayObject) {
            $dataForObjectUtil['__META__'] = $dataForObjectUtil['__META__']->getArrayCopy();
        }

        ObjectUtil::insertObjectMetaData($dataForObjectUtil, $tableName, $idColumn);
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
        if (!isset($data['__META__'])) {
            return;
        }

        $tableName = $this->getTableNameFromEvent($event);
        $idColumn = $this->getIdColumnFromEvent($event);

        // determine meta id
        if (isset($data['__META__']['id'])) {
            $metaId = $data['__META__']['id'];
        } else {
            $dataForObjectUtil = array();
            $dataForObjectUtil[$idColumn] = $data[$idColumn];
            $dataForObjectUtil['__META__'] = $data['__META__'];

            if ($dataForObjectUtil['__META__'] instanceof ArrayObject) {
                $dataForObjectUtil['__META__'] = $dataForObjectUtil['__META__']->getArrayCopy();
            }
            $meta = ObjectUtil::retrieveObjectMetaData($dataForObjectUtil, $tableName, $idColumn);
            $metaId = $meta['id'];
        }

        $dataForObjectUtil = array();
        $dataForObjectUtil[$idColumn] = $data[$idColumn];
        $dataForObjectUtil['__META__'] = $data['__META__'];
        $dataForObjectUtil['__META__']['id'] = $metaId;
        $dataForObjectUtil['__META__']['obj_id'] = $data[$idColumn];

        if ($dataForObjectUtil['__META__'] instanceof ArrayObject) {
            $dataForObjectUtil['__META__'] = $dataForObjectUtil['__META__']->getArrayCopy();
        }

        ObjectUtil::updateObjectMetaData($dataForObjectUtil, $tableName, $idColumn);
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
        if (!isset($data['__META__'])) {
            return;
        }

        $tableName = $this->getTableNameFromEvent($event);
        $idColumn = $this->getIdColumnFromEvent($event);

        // determine meta id
        if (isset($data['__META__']['id'])) {
            $metaId = $data['__META__']['id'];
        } else {
            $dataForObjectUtil = array();
            $dataForObjectUtil[$idColumn] = $data[$idColumn];
            $dataForObjectUtil['__META__'] = $data['__META__'];

            if ($dataForObjectUtil['__META__'] instanceof ArrayObject) {
                $dataForObjectUtil['__META__'] = $dataForObjectUtil['__META__']->getArrayCopy();
            }
            $meta = ObjectUtil::retrieveObjectMetaData($dataForObjectUtil, $tableName, $idColumn);
            $metaId = $meta['id'];
        }

        $dataForObjectUtil = array();
        $dataForObjectUtil[$idColumn] = $data[$idColumn];
        $dataForObjectUtil['__META__'] = $data['__META__'];
        $dataForObjectUtil['__META__']['id'] = $metaId;
        $dataForObjectUtil['__META__']['obj_id'] = $data[$idColumn];

        if ($dataForObjectUtil['__META__'] instanceof ArrayObject) {
            $dataForObjectUtil['__META__'] = $dataForObjectUtil['__META__']->getArrayCopy();
        }

        ObjectUtil::deleteObjectMetaData($dataForObjectUtil, $tableName, $idColumn);
    }
}
