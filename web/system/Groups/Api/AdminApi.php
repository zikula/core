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

use Zikula\Core\Event\GenericEvent;

/**
 * Groups_Api_Admin class.
 */
class Groups_Api_AdminApi extends Zikula_AbstractApi
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
        $obj = new Groups\Entity\Group;
        $obj['name'] = $args['name'];
        $obj['gtype'] = $args['gtype'];
        $obj['state'] = $args['state'];
        $obj['nbumax'] = $args['nbumax'];
        $obj['description'] = $args['description'];
        
        $this->entityManager->persist($obj);
        $this->entityManager->flush();
        
        // Get the ID of the item that we inserted.
        $gid = $obj['gid'];

        // Let other modules know that we have created a new group.
        $createEvent = new GenericEvent($obj);
        $this->dispatcher->dispatch('group.create', $createEvent);

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

        // get item
        $item = $this->entityManager->find('Groups\Entity\Group', $args['gid']);

        if (!$item) {
            return LogUtil::registerError($this->__('Sorry! No such item found.'));
        }
        
        // keep item to pass it to dispatcher later
        $deletedItem = $item->toArray();
        
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

        // Delete the group
        $this->entityManager->remove($item);
        $this->entityManager->flush();

        // remove all memberships of this group
        $dql = "DELETE FROM Groups\Entity\GroupMembership m WHERE m.gid = {$args['gid']}";
        $query = $this->entityManager->createQuery($dql);
        $query->getResult();

        // TODO: Is there any reason why we don't delete group applications?
        //

        // Remove any group permissions for this group
        $dql = "DELETE FROM Permissions\Entity\Permission p WHERE p.gid = {$args['gid']}";
        $query = $this->entityManager->createQuery($dql);
        $query->getResult();

        // Let other modules know that we have deleted a group.
        $deleteEvent = new GenericEvent($deletedItem);
        $this->dispatcher->dispatch('group.delete', $deleteEvent);

        // Let the calling process know that we have finished successfully
        return true;
    }

    /**
     * Update a group item.
     *
     * @param int $args['gid'] the ID of the item.
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

        // get item
        $item = $this->entityManager->find('Groups\Entity\Group', $args['gid']);

        if (!$item) {
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
        $item->merge($args);
        $this->entityManager->flush();

        // Let other modules know that we have updated a group.
        $updateEvent = new GenericEvent($item);
        $this->dispatcher->dispatch('group.update', $updateEvent);

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

        // get group
        $group = ModUtil::apiFunc('Groups', 'user', 'get', array('gid' => $args['gid']));

        if (!$group) {
            return LogUtil::registerError($this->__('Sorry! No such item found.'));
        }

        // Security check
        if (!SecurityUtil::checkPermission('Groups::', $args['gid'] . '::', ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }

        // Add user to group
        $membership = new Groups\Entity\GroupMembership;
        $membership['gid'] = $args['gid'];
        $membership['uid'] = $args['uid'];
        $this->entityManager->persist($membership);
        $this->entityManager->flush();

        // Let other modules know that we have updated a group.
        $adduserEvent = new GenericEvent($membership);
        $this->dispatcher->dispatch('group.adduser', $adduserEvent);

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
        if ((!isset($args['gid'])) || (!isset($args['uid']))) {
            return LogUtil::registerArgsError();
        }

        // get group
        $group = ModUtil::apiFunc('Groups', 'user', 'get', array('gid' => $args['gid']));

        if (!$group) {
            return LogUtil::registerError($this->__('Sorry! No such item found.'));
        }

        // Security check
        if (!SecurityUtil::checkPermission('Groups::', $args['gid'] . '::', ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }
        
        // delete user from group
        $membership = $this->entityManager->getRepository('Groups\Entity\GroupMembership')->findOneBy(array('gid' => $args['gid'], 'uid' => $args['uid']));
        $this->entityManager->remove($membership);
        $this->entityManager->flush();

        // Let other modules know we have updated a group
        $removeuserEvent = new GenericEvent(array('gid' => $args['gid'], 'uid' => $args['uid']));
        $this->dispatcher->dispatch('group.removeuser', $removeuserEvent);

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
        
        // create a QueryBuilder instance
        $qb = $this->entityManager->createQueryBuilder();
        
        // add select and from params 
        $qb->select('g')
           ->from('Groups\Entity\Group', 'g');
        
        // add clause for filtering name
        $qb->andWhere($qb->expr()->eq('g.name', $qb->expr()->literal($args['name'])));
        
        // Optional Where to use when modifying a group to check if there is
        // already another group by that name.
        if (isset($args['checkgid']) && is_numeric($args['checkgid'])) {
            $qb->andWhere($qb->expr()->neq('g.gid', $qb->expr()->literal($args['checkgid'])));
        }
        
        // convert querybuilder instance into a Query object
        $query = $qb->getQuery();
        
        // execute query
        $result = $query->getOneOrNullResult();

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
     * @return mixed array, false on failure.
     */
    public function getapplications()
    {
        $objArray = $this->entityManager->getRepository('Groups\Entity\GroupApplication')->findBy(array(), array('app_id' => 'ASC'));

        if ($objArray === false) {
            return LogUtil::registerError($this->__('Error! Could not load data.'));
        }

        $items = array();
        
        foreach ($objArray as $obj) {
            $group = ModUtil::apiFunc('Groups', 'user', 'get', array('gid' => $obj['gid']));
            if ($group) {
                if (SecurityUtil::checkPermission('Groups::', $group['gid'] . '::', ACCESS_EDIT) && $group <> false) {
                    $items[] = array(
                        'app_id' => $obj['app_id'],
                        'userid' => $obj['uid'],
                        'username' => UserUtil::getVar('uname', $obj['uid']),
                        'appgid' => $obj['gid'],
                        'gname' => $group['name'],
                        'application' => nl2br($obj['application']),
                        'status' => $obj['status']);
                }
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
        
        $appInfo = $this->entityManager->getRepository('Groups\Entity\GroupApplication')->findOneBy(array('gid' => $args['gid'], 'uid' => $args['userid']));
        
        if (!$appInfo) {
            return LogUtil::registerError($this->__('Error! Could not load data.'));
        }

        return $appInfo->toArray();
    }

    /**
     * Pending action.
     *
     * @param int $args['gid']
     * @param int $args['userid']
     * @param string $args['action']
     *
     * @return boolean
     */
    public function pendingaction($args)
    {
        if (!isset($args['gid']) || !isset($args['userid']) || !isset($args['action'])) {
            return LogUtil::registerArgsError();
        }
        
        // delete group application
        $application = $this->entityManager->getRepository('Groups\Entity\GroupApplication')->findOneBy(array('gid' => $args['gid'], 'uid' => $args['userid']));
        $this->entityManager->remove($application);
        $this->entityManager->flush();

        if ($args['action'] == 'accept') {
            $adduser = ModUtil::apiFunc('Groups', 'admin', 'adduser', array('gid' => $args['gid'], 'uid' => $args['userid']));
        }

        // Send message part
        switch ($args['sendtag']) {
            case 1:
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
        $dql = "SELECT count(g.gid) FROM Groups\Entity\Group g";
        $query = $this->entityManager->createQuery($dql);
        return (int)$query->getSingleScalarResult();
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
