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
 * Groups_Api_Admin class.
 */
class Groups_Api_Admin extends Zikula_AbstractApi
{

    /**
     * Create a new group item.
     *
     * @param string $args['name'] name of the group.
     *
     * @return mixed group ID on success, false on failure.
     */
    public function create($args)
    {
        // Argument check
        if (!isset($args['name'])) {
            return LogUtil::registerArgsError();
        }

        // Setting defaults
        if (!isset($args['gtype'])) {
            $args['gtype'] = Groups_Helper_Common::GTYPE_CORE;
        }
        if (!isset($args['state'])) {
            $args['state'] = Groups_Helper_Common::STATE_CLOSED;
        }

        // Security check
        if (!SecurityUtil::checkPermission('Groups::', '::', ACCESS_ADD)) {
            return LogUtil::registerPermissionError();
        }

        // Add item
        $obj = array('name' => $args['name'],
                'gtype' => $args['gtype'],
                'state' => $args['state'],
                'nbumax' => $args['nbumax'],
                'description' => $args['description']);
        $result = DBUtil::insertObject($obj, 'groups', 'gid');

        // Check for an error with the database code
        if (!$result) {
            return LogUtil::registerError($this->__('Error! Could not create the new item.'));
        }

        // Get the ID of the item that we inserted.
        $gid = $obj['gid'];

        // Let other modules know that we have created a new group.
        $createEvent = new Zikula_Event('group.create', $obj);
        $this->eventManager->notify($createEvent);

        // Return the id of the newly created item to the calling process
        return $gid;
    }

    /**
     * Delete a group item.
     *
     * @param int $args['gid'] ID of the item.
     *
     * @todo call permissions API to remove group permissions associated with the group
     *
     * @return boolean true on success, false on failure.
     */
    public function delete($args)
    {
        // Argument check
        if (!isset($args['gid'])) {
            return LogUtil::registerArgsError();
        }

        // The user API function is called.
        $item = ModUtil::apiFunc('Groups', 'user', 'get',
                        array('gid' => $args['gid']));

        if ($item == false) {
            return LogUtil::registerError($this->__('Sorry! No such item found.'));
        }

        // Security check
        if (!SecurityUtil::checkPermission('Groups::', $args['gid'] . '::', ACCESS_DELETE)) {
            return LogUtil::registerPermissionError();
        }

        // Special groups check
        $defaultgroupid = $this->getVar('defaultgroup', 0);
        if ($item['gid'] == $defaultgroupid) {
            return LogUtil::registerError($this->__('Sorry! You cannot delete the default users group.'));
        }

        $primaryadmingroupid = $this->getVar('primaryadmingroup', 0);
        if ($item['gid'] == $primaryadmingroupid) {
            return LogUtil::registerError($this->__('Sorry! You cannot delete the primary administrators group.'));
        }

        // Delete the item
        $group_result = DBUtil::deleteObjectByID('groups', $args['gid'], 'gid');

        // remove all memberships of this group
        $groupmembership_result = DBUtil::deleteObjectByID('group_membership', $args['gid'], 'gid');

        // Remove any group permissions for this group
        // TODO: Call the permissions API to do this job
        $groupperm_result = DBUtil::deleteObjectByID('group_perms', $args['gid'], 'gid');

        // Check for an error with the database code
        if (!$group_result || !$groupmembership_result || !$groupperm_result) {
            return LogUtil::registerError($this->__('Error! Could not perform the deletion.'));
        }

        // Let other modules know that we have deleted a group.
        $deleteEvent = new Zikula_Event('group.delete', $item);
        $this->eventManager->notify($deleteEvent);

        // Let the calling process know that we have finished successfully
        return true;
    }

    /**
     * Update a group item.
     *
     * @param int    $args['gid']  the ID of the item.
     * @param string $args['name'] the new name of the item.
     *
     * @todo add missing 'name' to modargs check.
     *
     * @return bool true if successful, false otherwise.
     */
    public function update($args)
    {
        // Argument check
        if (!isset($args['gid'])) {
            return LogUtil::registerArgsError();
        }

        // The user API function is called.
        $item = ModUtil::apiFunc('Groups', 'user', 'get',
                        array('gid' => $args['gid']));

        if ($item == false) {
            return LogUtil::registerError($this->__('Sorry! No such item found.'));
        }

        // Security check
        if (!SecurityUtil::checkPermission('Groups::', $args['gid'] . '::', ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }

        // Other check
        $checkname = ModUtil::apiFunc('Groups', 'admin', 'getgidbyname',
                        array('name' => $args['name'],
                                'checkgid' => $args['gid']));
        if ($checkname != false) {
            return LogUtil::registerError($this->__('Error! There is already a group with that name.'));
        }

        // Setting defaults
        if (!isset($args['gtype'])) {
            $args['gtype'] = Groups_Helper_Common::GTYPE_CORE;
        }
        if (!isset($args['state'])) {
            $args['state'] = Groups_Helper_Common::STATE_CLOSED;
        }

        // Update the item
        $object = array('name' => $args['name'],
                'gtype' => $args['gtype'],
                'state' => $args['state'],
                'nbumax' => (int)$args['nbumax'],
                'description' => $args['description'],
                'gid' => (int)$args['gid']);

        $result = DBUtil::updateObject($object, 'groups', '', 'gid');

        // Check for an error with the database code
        if (!$result) {
            return LogUtil::registerError($this->__('Error! Could not save your changes.'));
        }

        // Let other modules know that we have updated a group.
        $updateEvent = new Zikula_Event('group.update', $object);
        $this->eventManager->notify($updateEvent);

        // Let the calling process know that we have finished successfully
        return true;
    }

    /**
     * Add a user to a group item.
     *
     * @param int $args['gid'] the ID of the item.
     * @param int $args['uid'] the ID of the user.
     *
     * @return bool true if successful, false otherwise.
     */
    public function adduser($args)
    {
        // Argument check
        if ((!isset($args['gid'])) || (!isset($args['uid']))) {
            return LogUtil::registerArgsError();
        }

        // The user API function is called.
        $item = ModUtil::apiFunc('Groups', 'user', 'get',
                        array('gid' => $args['gid']));

        if ($item == false) {
            return LogUtil::registerError($this->__('Sorry! No such item found.'));
        }

        // Security check
        if (!SecurityUtil::checkPermission('Groups::', $args['gid'] . '::', ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }

        // Add item
        $object = array('gid' => $args['gid'],
                'uid' => $args['uid']);
        $result = DBUtil::insertObject($object, 'group_membership');

        // Check for an error with the database code
        if (!$result) {
            return LogUtil::registerError($this->__('Error! Could not create the new item.'));
        }

        // Let other modules know that we have updated a group.
        $adduserEvent = new Zikula_Event('group.adduser', $object);
        $this->eventManager->notify($adduserEvent);

        // Let the calling process know that we have finished successfully
        return true;
    }

    /**
     * Remove a user from a group item.
     *
     * @param int $args['gid'] the ID of the item.
     * @param int $args['uid'] the ID of the user.
     *
     * @return bool true if successful, false otherwise.
     */
    public function removeuser($args)
    {
        // Argument check
        if ((!isset($args['gid'])) ||
                (!isset($args['uid']))) {
            return LogUtil::registerArgsError();
        }

        // The user API function is called.
        $item = ModUtil::apiFunc('Groups', 'user', 'get',
                        array('gid' => $args['gid']));

        if ($item == false) {
            return LogUtil::registerError($this->__('Sorry! No such item found.'));
        }

        // Security check
        if (!SecurityUtil::checkPermission('Groups::', $args['gid'] . '::', ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }

        // Get datbase setup
        $dbtable = DBUtil::getTables();
        $groupmembershipcolumn = $dbtable['group_membership_column'];

        // Add item
        $where = "WHERE       $groupmembershipcolumn[gid] = '" . (int)DataUtil::formatForStore($args['gid']) . "'
              AND         $groupmembershipcolumn[uid] = '" . (int)DataUtil::formatForStore($args['uid']) . "'";
        $result = DBUtil::deleteWhere('group_membership', $where);

        // Check for an error with the database code
        if (!$result) {
            return false;
        }

        // Let other modules know we have updated a group
        $removeuserEvent = new Zikula_Event('group.removeuser', array('gid' => $args['gid'],
                        'uid' => $args['uid']));
        $this->eventManager->notify($removeuserEvent);

        // Let the calling process know that we have finished successfully
        return true;
    }

    /**
     * Get a specific group id from a group name.
     *
     * @param $args['name'] name of group item to get.
     * @param $args['checkgid'] optional gid of the group.
     *
     * @return int item, or false on failure.
     */
    public function getgidbyname($args)
    {
        // Argument check
        if (!isset($args['name'])) {
            return LogUtil::registerArgsError();
        }

        // Get datbase setup
        $dbtable = DBUtil::getTables();
        $groupcolumn = $dbtable['groups_column'];

        // Get item
        $where = "WHERE $groupcolumn[name] = '" . DataUtil::formatForStore($args['name']) . "'";

        // Optional Where to use when modifying a group to check if there is
        // already another group by that name.
        if (isset($args['checkgid']) && is_numeric($args['checkgid'])) {
            $where .= " AND $groupcolumn[gid] != '" . DataUtil::formatForStore($args['checkgid']) . "'";
        }
        $result = DBUtil::selectObject('groups', $where);

        // error message and return
        if (!$result) {
            return false;
        }

        // Return the gid
        return $result['gid'];
    }

    /**
     * Get applications.
     *
     * @param int $args['startnum'].
     * @param int $args['numitems'].
     *
     * @return mixed array, false on failure.
     */
    public function getapplications($args)
    {
        if (!isset($args['startnum']) || !is_numeric($args['startnum'])) {
            $args['startnum'] = 1;
        }
        if (!isset($args['numitems']) || !is_numeric($args['numitems'])) {
            $args['numitems'] = -1;
        }

        $dbtable = DBUtil::getTables();
        $col = $dbtable['group_applications_column'];

        $orderBy = "ORDER BY $col[app_id] ASC";
        $objArray = DBUtil::selectObjectArray('group_applications', '', $orderBy);

        if ($objArray === false) {
            return LogUtil::registerError($this->__('Error! Could not load data.'));
        }

        $items = array();
        foreach ($objArray as $obj) {
            $group = ModUtil::apiFunc('Groups', 'user', 'get', array('gid' => $obj['gid']));
            if (SecurityUtil::checkPermission('Groups::', $group['gid'] . '::', ACCESS_EDIT) && $group <> false) {
                $items[] = array('app_id' => $obj['app_id'],
                        'userid' => $obj['uid'],
                        'username' => UserUtil::getVar('uname', $obj['uid']),
                        'appgid' => $obj['gid'],
                        'gname' => $group['name'],
                        'application' => nl2br($obj['application']),
                        'status' => $obj['status']);
            }
        }

        return $items;
    }

    /**
     * Get application info.
     *
     * @param int $args['gid']
     * @param int $args['userid']
     *
     * @return array
     */
    public function getapplicationinfo($args)
    {
        if (!isset($args['gid']) || !isset($args['userid'])) {
            return LogUtil::registerArgsError();
        }

        $dbtable = DBUtil::getTables();
        $col = $dbtable['group_applications_column'];

        $where = "WHERE  $col[gid] = '" . DataUtil::formatForStore($args['gid']) . "'
              AND    $col[uid] = '" . DataUtil::formatForStore($args['userid']) . "'";

        $result = DBUtil::selectObject('group_applications', $where);

        if ($result === false) {
            return LogUtil::registerError($this->__('Error! Could not load data.'));
        }

        $appinfo = array('app_id' => $result['app_id'],
                'appuid' => $result['uid'],
                'appgid' => $result['gid'],
                'application' => nl2br($result['application']),
                'status' => $result['status']);

        return $appinfo;
    }

    /**
     * Pending action.
     *
     * @param int    $args['gid']
     * @param int    $args['userid']
     * @param string $args['action']
     *
     * @return boolean
     */
    public function pendingaction($args)
    {
        if (!isset($args['gid']) || !isset($args['userid']) || !isset($args['action'])) {
            return LogUtil::registerArgsError();
        }

        $dbtable = DBUtil::getTables();
        $col = $dbtable['group_applications_column'];

        $where = "WHERE $col[gid] = '" . (int)DataUtil::formatForStore($args['gid']) . "'
              AND   $col[uid] = '" . (int)DataUtil::formatForStore($args['userid']) . "'";
        if (!DBUtil::deleteWhere('group_applications', $where)) {
            return LogUtil::registerError($this->__('Error! Could not perform the deletion.'));
        }

        if ($args['action'] == 'accept') {
            $adduser = ModUtil::apiFunc('Groups', 'admin', 'adduser', array('gid' => $args['gid'], 'uid' => $args['userid']));
        }

        // Send message part
        switch ($args['sendtag']) {
            case 1:
                $send = ModUtil::apiFunc('Messages', 'user', 'create',
                                array('to_userid' => $args['userid'],
                                        'subject' => $args['reasontitle'],
                                        'message' => $args['reason']));

                if ($send == false) {
                    LogUtil::registerError($this->__('Error! Could not send the private message to the user.'));
                }

                break;

            case 2:
                if (ModUtil::available('Mailer')) {
                    $send = ModUtil::apiFunc('Mailer', 'user', 'sendmessage',
                                    array('toname' => UserUtil::getVar('uname', $args['userid']),
                                            'toaddress' => UserUtil::getVar('email', $args['userid']),
                                            'subject' => $args['reasontitle'],
                                            'body' => $args['reason']));
                } else {
                    $send = System::mail(UserUtil::getVar('email', $args['userid']), $args['reasontitle'], $args['reason'], "From: " . System::getVar('adminmail') . "\nX-Mailer: PHP/" . phpversion(), 0);
                }
                break;
        }

        return true;
    }

    /**
     * Utility function to count the number of items held by this module.
     *
     * @return int number of items held by this module.
     */
    public function countitems()
    {
        return DBUtil::selectObjectCount('groups');
    }

    /**
     * Get available admin panel links.
     *
     * @return array array of admin links.
     */
    public function getlinks()
    {
        $links = array();

        if (SecurityUtil::checkPermission('Groups::', '::', ACCESS_READ)) {
            $links[] = array('url' => ModUtil::url('Groups', 'admin', 'view'), 'text' => $this->__('Groups list'), 'id' => 'groups_view', 'class' => 'z-icon-es-view');
        }
        if (SecurityUtil::checkPermission('Groups::', '::', ACCESS_ADD)) {
            $links[] = array('url' => ModUtil::url('Groups', 'admin', 'newgroup'), 'text' => $this->__('Create new group'), 'id' => 'groups_new', 'class' => 'z-icon-es-new');
        }
        if (SecurityUtil::checkPermission('Groups::', '::', ACCESS_ADMIN)) {
            $links[] = array('url' => ModUtil::url('Groups', 'admin', 'modifyconfig'), 'text' => $this->__('Settings'), 'id' => 'groups_modifyconfig', 'class' => 'z-icon-es-config');
        }

        return $links;
    }

}
