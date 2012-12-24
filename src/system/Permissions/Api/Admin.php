<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Permissions_Api_Admin class.
 */
class Permissions_Api_Admin extends Zikula_AbstractApi
{

    /**
     * Increment sequence number of a permission.
     *
     * This function raises a permission higher up in the overall
     * permissions sequence, thus making it more likely to be acted
     * against.
     *
     * @param int $args ['pid'] the ID of the permission to increment.
     *
     * @return bool true on success, false on failure.
     */
    public function inc($args)
    {
        // Security check
        if (!SecurityUtil::checkPermission('Permissions::', "group::$args[pid]", ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        // Argument check
        if (!isset($args['pid'])) {
            return LogUtil::registerArgsError();
        }

        // Work out which tables to operate against, and
        // various other bits and pieces
        $dbtable = DBUtil::getTables();
        $permcolumn = $dbtable['group_perms_column'];
        if (!is_null($args['permgrp']) && ($args['permgrp'] != SecurityUtil::PERMS_ALL)) {
            $where = " AND ($permcolumn[gid]=" . SecurityUtil::PERMS_ALL . " OR $permcolumn[gid]='" . DataUtil::formatForStore($args['permgrp']) . "')";
            $showpartly = true;
        } else {
            $where = '';
            $showpartly = false;
        }

        // Get info on current perm
        $result = DBUtil::selectObjectByID('group_perms', $args['pid'], 'pid');
        if (!$result) {
            return LogUtil::registerError($this->__f('Error! Permission rule ID %s does not exist.', $args['pid']));
        }
        $sequence = $result['sequence'];

        if ($sequence != 1) {
            $altsequence = $sequence - 1;
            // Get info on displaced perm
            $where = "WHERE $permcolumn[sequence] = '" . (int)DataUtil::formatForStore($altsequence) . "' $where";
            $result = DBUtil::selectObject('group_perms', $where);
            if (!$result) {
                if ($showpartly) {
                    // Changing the sequence by moving while in partial view may only be done if there
                    // are no invisible permissions inbetween that might be affected by the move.
                    LogUtil::registerError($this->__('Error! Permission rule-swapping in partial view can only be done if both affected permission rules are visible. Please switch to full view.'));
                } else {
                    LogUtil::registerError($this->__('Error! No permission rule directly above that one.'));
                }

                return false;
            }
            $altpid = $result['pid'];

            // Swap sequence numbers
            $where = "WHERE $permcolumn[pid] = '" . (int)DataUtil::formatForStore($altpid) . "'";
            $obj = array('sequence' => $sequence);
            DBUtil::updateObject($obj, 'group_perms', $where, 'pid');
            $where = "WHERE $permcolumn[pid] = '" . (int)DataUtil::formatForStore($args['pid']) . "'";
            $obj = array('sequence' => $altsequence);
            DBUtil::updateObject($obj, 'group_perms', $where, 'pid');
        }

        return true;
    }

    /**
     * Decrement sequence number of a permission.
     *
     * @param string $args ['type'] the type of the permission to decrement (user or group).
     * @param int    $args ['pid'] the ID of the permission to decrement.
     *
     * @return boolean true on success, false on failure.
     */
    public function dec($args)
    {
        // Security check
        if (!SecurityUtil::checkPermission('Permissions::', "group::$args[pid]", ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        // Argument check
        if (!isset($args['pid'])) {
            return LogUtil::registerArgsError();
        }

        // Work out which tables to operate against
        $dbtable = DBUtil::getTables();
        $permcolumn = $dbtable['group_perms_column'];
        if (!is_null($args['permgrp']) && ($args['permgrp'] != SecurityUtil::PERMS_ALL)) {
            $where = " AND ($permcolumn[gid]=" . SecurityUtil::PERMS_ALL . " OR  $permcolumn[gid]='" . (int)DataUtil::formatForStore($args['permgrp']) . "')";
            $showpartly = true;
        } else {
            $where = '';
            $showpartly = false;
        }

        // Get info on current perm
        $result = DBUtil::selectObjectByID('group_perms', $args['pid'], 'pid');
        if (!$result) {
            return LogUtil::registerError($this->__f('Error! Permission rule ID %s does not exist.', $args['pid']));
        }
        $sequence = $result['sequence'];

        $maxsequence = $this->maxsequence(array('column' => 'sequence'));
        if ($sequence != $maxsequence) {
            $altsequence = $sequence + 1;
            // Get info on displaced perm
            // Filter-view: added extra check to select-query
            $where = "WHERE $permcolumn[sequence] = '" . (int)DataUtil::formatForStore($altsequence) . "' $where";
            $result = DBUtil::selectObject('group_perms', $where);
            if (!$result) {
                if ($showpartly) {
                    // Filter-view
                    // Changing the sequence by moving while in partial view may only be done if there
                    // are no invisible permissions inbetween that might be affected by the move.
                    LogUtil::registerError($this->__('Error! Permission rule-swapping in partial view can only be done if both affected permission rules are visible. Please switch to full view.'));
                } else {
                    LogUtil::registerError($this->__('Error! No permission rule directly below that one.'));
                }

                return false;
            }
            $altpid = $result['pid'];

            // Swap sequence numbers
            $where = "WHERE $permcolumn[pid] = '" . (int)DataUtil::formatForStore($altpid) . "'";
            $obj = array('sequence' => $sequence);
            DBUtil::updateObject($obj, 'group_perms', $where, 'pid');
            $where = "WHERE $permcolumn[pid] = '" . DataUtil::formatForStore($args['pid']) . "'";
            $obj = array('sequence' => $altsequence);
            DBUtil::updateObject($obj, 'group_perms', $where, 'pid');
        }

        return true;
    }

    /**
     * Update attributes of a permission.
     *
     * @param int    $args ['pid'] the ID of the permission to update.
     * @param string $args ['realm'] the new realm of the permission.
     * @param int    $args ['id'] the new group/user id of the permission.
     * @param string $args ['component'] the new component of the permission.
     * @param string $args ['instance'] the new instance of the permission.
     * @param int    $args ['level'] the new level of the permission.
     *
     * @return bool true on success, false on failure.
     */
    public function update($args)
    {
        // Security check
        if (!SecurityUtil::checkPermission('Permissions::', "group::$args[pid]", ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        // Argument check
        if ((!isset($args['pid'])) ||
                (!isset($args['seq'])) ||
                (!isset($args['oldseq'])) ||
                (!isset($args['realm'])) ||
                (!isset($args['id'])) ||
                (!isset($args['component'])) ||
                (!isset($args['instance'])) ||
                (!isset($args['level']))) {
            return LogUtil::registerArgsError();
        }

        // Work out which tables to operate against
        $dbtable = DBUtil::getTables();
        $permcolumn = $dbtable['group_perms_column'];

        $obj = array('realm' => $args['realm'],
                'gid' => $args['id'],
                'component' => $args['component'],
                'instance' => $args['instance'],
                'level' => $args['level']);
        $where = "WHERE $permcolumn[pid] = '" . (int)DataUtil::formatForStore($args['pid']) . "'";
        $result = DBUtil::updateObject($obj, 'group_perms', $where, 'pid');

        if (!$result) {
            return LogUtil::registerError($this->__f('Error! Could not save group permission rule %s.', $args[pid]));
        }

        if ($args['seq'] != $args['oldseq']) {
            $this->resequence(array('type' => 'group', 'newseq' => $args['seq'], 'oldseq' => $args['oldseq']));
        }

        return true;
    }

    /**
     * Create a new perm.
     *
     * @param string $args ['realm'] the new realm of the permission.
     * @param int    $args ['id'] the new group/user id of the permission.
     * @param string $args ['component'] the new component of the permission.
     * @param string $args ['instance'] the new instance of the permission.
     * @param int    $args ['level'] the new level of the permission.
     *
     * @return boolean true on success, false on failure.
     */
    public function create($args)
    {
        // Security check
        if (!SecurityUtil::checkPermission('Permissions::', "group::$args[id]", ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        // Argument check
        if ((!isset($args['realm'])) ||
                (!isset($args['id'])) ||
                (!isset($args['component'])) ||
                (!isset($args['instance'])) ||
                (!isset($args['level'])) ||
                (!isset($args['insseq']))) {
            return LogUtil::registerArgsError();
        }

        // Work out which tables to operate against
        $dbtable = DBUtil::getTables();
        $permtable = $dbtable['group_perms'];
        $permcolumn = $dbtable['group_perms_column'];

        // Insert Capability
        if ($args['insseq'] == -1) {
            $maxseq = $this->maxsequence(array('column' => 'sequence'));
            $newseq = $maxseq + 1;
        } else {
            // Increase sequence numbers
            $query = "UPDATE $permtable
                  SET $permcolumn[sequence] = $permcolumn[sequence] + 1
                  WHERE $permcolumn[sequence] >= '" . (int)DataUtil::formatForStore($args['insseq']) . "'";
            if (!DBUtil::executeSQL($query)) {
                return LogUtil::registerError($this->__('Error! Could not save permission rule sequences.'));
            }
            $newseq = $args['insseq'];
        }

        $obj = array('realm' => (int)$args['realm'],
                'gid' => (int)$args['id'],
                'sequence' => $newseq,
                'component' => $args['component'],
                'instance' => $args['instance'],
                'level' => (int)$args['level']);

        $newobj = DBUtil::insertObject($obj, 'group_perms', 'pid');
        if ($newobj === false) {
            return LogUtil::registerError('Error adding group permission');
        }

        // Clean-up
        $this->resequence();

        return $newobj;
    }

    /**
     * Delete a perm.
     *
     * @param string $args ['type'] the type of the permission to update (user or group).
     * @param int    $args ['pid'] the ID of the permission to delete.
     *
     * @return boolean true on success, false on failure.
     */
    public function delete($args)
    {
        // Security check
        if (!SecurityUtil::checkPermission('Permissions::', "group::$args[pid]", ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        // Argument check
        if (!isset($args['pid'])) {
            return LogUtil::registerArgsError();
        }

        // Work out which tables to operate against
        $dbtable = DBUtil::getTables();
        $permcolumn = $dbtable['group_perms_column'];

        $where = "WHERE $permcolumn[pid] = '" . (int)DataUtil::formatForStore($args['pid']) . "'";
        if (!DBUtil::deleteObjectByID('group_perms', $args['pid'], 'pid')) {
            return LogUtil::registerError($this->__f('Error! Could not delete group permission rule %s.', $args[pid]));
        }

        $this->resequence();

        return true;
    }

    /**
     * Get the maximum sequence number currently in a given table.
     *
     * @param string $args ['column'] the sequence column name.
     *
     * @return int the maximum sequence number.
     */
    public function maxsequence($args)
    {
        // Security check
        if (!SecurityUtil::checkPermission('Permissions::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        // Argument check
        if (!isset($args['column'])) {
            return LogUtil::registerArgsError();
        }

        return DBUtil::selectFieldMax('group_perms', $args['column']);
    }

    /**
     * Resequence a permissions table.
     *
     * @return boolean
     */
    public function resequence()
    {
        // Security check
        if (!SecurityUtil::checkPermission('Permissions::', "group::", ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        $dbtable = DBUtil::getTables();
        $permcolumn = $dbtable['group_perms_column'];

        // Get the information
        $orderBy = "ORDER BY $permcolumn[sequence]";
        $objArray = DBUtil::selectObjectArray('group_perms', '', $orderBy);
        if (!$objArray) {
            return false;
        }

        // Fix sequence numbers
        $sequence = 1;
        $ak = array_keys($objArray);
        foreach ($ak as $v) {
            $pid = $objArray[$v]['pid'];
            $curseq = $objArray[$v]['sequence'];
            if ($curseq != $sequence) {
                $where = "WHERE $permcolumn[pid] = '" . (int)DataUtil::formatForStore($pid) . "'";
                $obj = array('sequence' => $sequence);
                DBUtil::updateObject($obj, 'group_perms', $where, 'pid');
            }
            $sequence++;
        }

        return true;
    }

    /**
     * Resequence permissions.
     *
     * Called when a permission is assigned the same sequence number as an existing permission.
     *
     * @param string $args ['newseq'] the desired sequence.
     * @param string $args ['oldseq'] the original sequence number.
     *
     * @return boolean
     */
    public function full_resequence($args)
    {
        // Security check
        if (!SecurityUtil::checkPermission('Permissions::', "::", ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        // Argument check
        if (!isset($args['newseq']) || !isset($args['oldseq'])) {
            return LogUtil::registerArgsError();
        }

        $newseq = $args['newseq'];
        $oldseq = $args['oldseq'];
        unset($args);

        $dbtable = DBUtil::getTables();
        $permcolumn = $dbtable['group_perms_column'];

        //find out the maximum sequence number
        $maxseq = $this->maxsequence(array('column' => 'sequence'));

        if ((int)$oldseq > (int)$newseq) {
            if ($newseq < 1) {
                $newseq = 1;
            }
            // The new sequence is higher in the list
            // Get the information
            $where = "WHERE $permcolumn[sequence] >= '" . (int)$newseq . "'
                  AND $permcolumn[sequence] <= '" . (int)$oldseq . "'";
            $orderBy = "ORDER BY $permcolumn[sequence] DESC";
            $objArray = DBUtil::selectObjectArray('group_perms', $where, $orderBy, -1, -1, '', null, array('pid', 'sequence'));

            $key = 0;
            while (list($pid, $curseq) = $objArray[$key]) {
                if ($curseq == $oldseq) {
                    // we are dealing with the old value so make it the new value
                    $curseq = $newseq;
                } else {
                    $curseq++;
                }
                $key++;
                $where = "WHERE $permcolumn[pid] = '" . (int)DataUtil::formatForStore($pid) . "'";
                $obj = array('sequence' => (int)$curseq);
                DBUtil::updateObject($obj, 'group_perms', $where, 'pid');
            }
        } else {
            // The new sequence is lower in the list
            //if the new requested sequence is bigger than
            //the maximum sequence number then set it to
            //the maximum number.  We don't want any spaces
            //in the sequence.
            if ($newseq > $maxseq) {
                $newseq = (int)$maxseq;
            }

            $where = "WHERE $permcolumn[sequence] >= '" . (int)$oldseq . "'
                  AND   $permcolumn[sequence] <= '" . (int)$newseq . "'";
            $orderBy = "ORDER BY $permcolumn[sequence] ASC";
            $objArray = DBUtil::selectObjectArray('group_perms', $where, $orderBy, -1, -1, '', null, array('pid', 'sequence'));

            $key = 0;
            while (list($pid, $curseq) = $objArray[$key]) {
                if ($curseq == $oldseq) {
                    // we are dealing with the old value so make it the new value
                    $curseq = $newseq;
                } else {
                    $curseq--;
                }
                $key++;
                $where = "WHERE $permcolumn[pid] = '" . (int)DataUtil::formatForStore($pid) . "'";
                $obj = array('sequence' => (int)$curseq);
                DBUtil::updateObject($obj, 'group_perms', $where, 'pid');
            }
        }

        return true;
    }

    /**
     * Get all security permissions schemas.
     *
     * @return array array if permission schema values.
     */
    public function getallschemas()
    {
        // Security check
        if (!SecurityUtil::checkPermission('Permissions::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        $schemas = SecurityUtil::getSchemas();
        BlockUtil::loadAll();
        $modinfos = ModUtil::getAllMods();
        foreach ($modinfos as $modinfo) {
            if (!empty($modinfo['securityschema'])) {
                $schemas = array_merge($schemas, $modinfo['securityschema']);
            }
        }
        uksort($schemas, 'strnatcasecmp');
        SecurityUtil::setSchemas($schemas);

        return $schemas;
    }

    /**
     * Get available admin panel links.
     *
     * @return array array of admin links.
     */
    public function getlinks($args)
    {
        $permgrp = (isset($args['permgrp']) && !is_numeric($args['permgrp'])) ? $args['permgrp'] : -1;

        $links = array();

        if (SecurityUtil::checkPermission('Permissions::', '::', ACCESS_READ)) {
            $links[] = array('url' => ModUtil::url('Permissions', 'admin', 'view', array()), 'text' => $this->__('Permission rules list'), 'id' => 'permissions_view', 'class' => 'z-icon-es-view');
        }
        if (SecurityUtil::checkPermission('Permissions::', '::', ACCESS_ADD)) {
            $links[] = array('url' => ModUtil::url('Permissions', 'admin', 'listedit', array('action' => 'add')), 'text' => $this->__('Create new permission rule'), 'id' => 'permissions_new', 'class' => 'z-icon-es-new');
        }
        if (SecurityUtil::checkPermission('Permissions::', '::', ACCESS_ADMIN)) {
            $links[] = array('url' => ModUtil::url('Permissions', 'admin', 'modifyconfig'), 'text' => $this->__('Settings'), 'id' => 'permissions_modifyconfig', 'class' => 'z-icon-es-config');
        }
        if (ModUtil::getName() == 'Permissions') {
            $links[] = array('url' => ModUtil::url('Permissions', 'admin', 'viewinstanceinfo'), 'text' => $this->__('Permission rules information'), 'title' => $this->__('Permission rules information'), 'class' => 'z-icon-es-info showinstanceinformation');
        }

        return $links;
    }

}
