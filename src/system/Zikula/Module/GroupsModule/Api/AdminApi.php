<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Module\GroupsModule\Api;

use Zikula\Core\Event\GenericEvent;
use Zikula\Module\GroupsModule\Entity\GroupEntity;
use Zikula\Module\GroupsModule\Entity\GroupMembershipEntity;
use Zikula\Module\GroupsModule\Helper\CommonHelper;
use SecurityUtil;
use Zikula;
use ModUtil;
use UserUtil;
use System;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Adminstrative API functions for the groups module
 */
class AdminApi extends \Zikula_AbstractApi
{
    /**
     * Create a new group item.
     *
     * @param string[] $args {
     *      @type string $name name of the group
     *                       }
     *
     * @return int the id of the new group
     *
     * @throws \InvalidArgumentException Thrown if the name parameter is provided
     * @throws AccessDeniedException Thrown if the current user does not have add access.
     */
    public function create($args)
    {
        // Argument check
        if (!isset($args['name'])) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        // Setting defaults
        if (!isset($args['gtype'])) {
            $args['gtype'] = CommonHelper::GTYPE_CORE;
        }
        if (!isset($args['state'])) {
            $args['state'] = CommonHelper::STATE_CLOSED;
        }

        // Security check
        if (!SecurityUtil::checkPermission('ZikulaGroupsModule::', '::', ACCESS_ADD)) {
            throw new AccessDeniedException();
        }

        // Add item
        $obj = new GroupEntity;
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
        $this->getDispatcher()->dispatch('group.create', $createEvent);

        // Return the id of the newly created item to the calling process
        return $gid;
    }

    /**
     * Delete a group item.
     *
     * @param int[] $args {
     *      @type int $gid ID of the item
     *                    }
     *
     * @todo call permissions API to remove group permissions associated with the group
     *
     * @return boolean true if successful
     *
     * @throws \InvalidArgumentException Thrown if the gid parameter isn't provided
     * @throws AccessDeniedException Thrown if the current user does not have delete access for the group.
     * @throws NotFoundHttpException Thrown if the group cannot be found
     * @throws \RuntimeException Thrown if the requested group is either the default users group or primary admins group
     */
    public function delete($args)
    {
        // Argument check
        if (!isset($args['gid'])) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        // get item
        $item = $this->entityManager->find('ZikulaGroupsModule:GroupEntity', $args['gid']);

        if (!$item) {
            throw new NotFoundHttpException($this->__('Sorry! No such item found.'));
        }

        // keep item to pass it to dispatcher later
        $deletedItem = $item->toArray();

        // Security check
        if (!SecurityUtil::checkPermission('ZikulaGroupsModule::', $args['gid'] . '::', ACCESS_DELETE)) {
            throw new AccessDeniedException();
        }

        // Special groups check
        $defaultgroupid = $this->getVar('defaultgroup', 0);
        if ($item['gid'] == $defaultgroupid) {
            throw new \RuntimeException($this->__('Sorry! You cannot delete the default users group.'));
        }

        $primaryadmingroupid = $this->getVar('primaryadmingroup', 0);
        if ($item['gid'] == $primaryadmingroupid) {
            throw new \RuntimeException($this->__('Sorry! You cannot delete the primary administrators group.'));
        }

        // Delete the group
        $this->entityManager->remove($item);
        $this->entityManager->flush();

        // remove all memberships of this group
        $query = $this->entityManager->createQueryBuilder()
                                     ->delete()
                                     ->from('ZikulaGroupsModule:GroupMembershipEntity', 'm')
                                     ->where('m.gid = :gid')
                                     ->setParameter('gid', $args['gid'])
                                     ->getQuery();
        $query->getResult();

        // TODO: Is there any reason why we don't delete group applications?
        //

        // Remove any group permissions for this group
        $query = $this->entityManager->createQueryBuilder()
                                     ->delete()
                                     ->from('ZikulaPermissionsModule:PermissionEntity', 'p')
                                     ->where('p.gid = :gid')
                                     ->setParameter('gid', $args['gid'])
                                     ->getQuery();
        $query->getResult();

        // Let other modules know that we have deleted a group.
        $deleteEvent = new GenericEvent($deletedItem);
        $this->getDispatcher()->dispatch('group.delete', $deleteEvent);

        // Let the calling process know that we have finished successfully
        return true;
    }

    /**
     * Update a group item.
     *
     * @param mixed[] $args {
     *      @type int    $gid  the ID of the item
     *      @type string $name the new name of the item
     *                      }
     *
     * @return bool true if successful
     *
     * @throws \InvalidArgumentException Thrown if either the gid or name parameters are not provided
     * @throws NotFoundHttpException Thrown if the group cannot be found
     * @throws AccessDeniedException Thrown if the current user does not have edit access to the group.
     */
    public function update($args)
    {
        // Argument check
        if (!isset($args['gid']) || !isset($args['name'])) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        // get item
        $item = $this->entityManager->find('ZikulaGroupsModule:GroupEntity', $args['gid']);

        if (!$item) {
            throw new NotFoundHttpException($this->__('Sorry! No such item found.'));
        }

        // Security check
        if (!SecurityUtil::checkPermission('ZikulaGroupsModule::', $args['gid'] . '::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        // Other check
        $checkname = ModUtil::apiFunc('ZikulaGroupsModule', 'admin', 'getgidbyname',
                        array('name' => $args['name'],
                              'checkgid' => $args['gid']));

        if ($checkname != false) {
            throw new \RuntimeException($this->__('Error! There is already a group with that name.'));
        }

        // Setting defaults
        if (!isset($args['gtype'])) {
            $args['gtype'] = CommonHelper::GTYPE_CORE;
        }
        if (!isset($args['state'])) {
            $args['state'] = CommonHelper::STATE_CLOSED;
        }

        // Update the item
        $item->merge($args);
        $this->entityManager->flush();

        // Let other modules know that we have updated a group.
        $updateEvent = new GenericEvent($item);
        $this->getDispatcher()->dispatch('group.update', $updateEvent);

        // Let the calling process know that we have finished successfully
        return true;
    }

    /**
     * Add a user to a group item.
     *
     * @param int[] $args {
     *      @type int $gid the ID of the item
     *      @type int $uid the ID of the user
     *                    }
     *
     * @return bool true if successful
     *
     * @throws \InvalidArgumentException Thrown if either gid or uid are not set or not numeric
     * @throws NotFoundHttpException Thrown if the group cannot be found
     * @throws AccessDeniedException Thrown if the current user does not have edit access to the group.
     */
    public function adduser($args)
    {
        // Argument check
        if ((!isset($args['gid'])) || (!isset($args['uid']))) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        // get group
        $group = ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'get', array('gid' => $args['gid']));

        if (!$group) {
            throw new NotFoundHttpException($this->__('Sorry! No such item found.'));
        }

        // Security check
        if (!SecurityUtil::checkPermission('ZikulaGroupsModule::', $args['gid'] . '::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        // Add user to group
        $membership = new GroupMembershipEntity;
        $membership['gid'] = $args['gid'];
        $membership['uid'] = $args['uid'];
        $this->entityManager->persist($membership);
        $this->entityManager->flush();

        // Let other modules know that we have updated a group.
        $adduserEvent = new GenericEvent($membership);
        $this->getDispatcher()->dispatch('group.adduser', $adduserEvent);

        // Let the calling process know that we have finished successfully
        return true;
    }

    /**
     * Remove a user from a group item.
     *
     * @param int[] $args {
     *      @type int $gid the ID of the item
     *      @type int $uid the ID of the user
     *                    }
     *
     * @return bool true if successful
     *
     * @throws \InvalidArgumentException Thrown if either gid or uid are not set or not numeric
     * @throws NotFoundHttpException Thrown if the group cannot be found
     * @throws AccessDeniedException Thrown if the current user does not have edit access to the group.
     */
    public function removeuser($args)
    {
        // Argument check
        if ((!isset($args['gid'])) || (!isset($args['uid']))) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        // get group
        $group = ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'get', array('gid' => $args['gid']));

        if (!$group) {
            throw new NotFoundHttpException($this->__('Sorry! No such item found.'));
        }

        // Security check
        if (!SecurityUtil::checkPermission('ZikulaGroupsModule::', $args['gid'] . '::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        // delete user from group
        $membership = $this->entityManager->getRepository('ZikulaGroupsModule:GroupMembershipEntity')->findOneBy(array('gid' => $args['gid'], 'uid' => $args['uid']));
        if (null !== $membership) {
            $this->entityManager->remove($membership);
            $this->entityManager->flush();
        } else {
            return false;
        }

        // Let other modules know we have updated a group
        $removeuserEvent = new GenericEvent(array('gid' => $args['gid'], 'uid' => $args['uid']));
        $this->getDispatcher()->dispatch('group.removeuser', $removeuserEvent);

        // Let the calling process know that we have finished successfully
        return true;
    }

    /**
     * Get a specific group id from a group name.
     *
     * @param mixed[] $args {
     *      @type string $name name of group item to get
     *      @type int    $checkgid optional gid of the group
     *                      }
     *
     * @return int|bool item, or false on failure.
     *
     * @throws \InvalidArgumentException Thrown if the name parameter isn't provided
     */
    public function getgidbyname($args)
    {
        // Argument check
        if (!isset($args['name'])) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        // create a QueryBuilder instance
        $qb = $this->entityManager->createQueryBuilder();

        // add select and from params
        $qb->select('g')
           ->from('ZikulaGroupsModule:GroupEntity', 'g');

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
     * Get a specific group name from a group id.
     *
     * @param mixed[] $args {
     *      @type int $gid id of group item to get
     *                      }
     *
     * @return string|bool item, or false on failure.
     *
     * @throws \InvalidArgumentException Thrown if the id parameter isn't provided.
     */
    public function getnamebygid($args)
    {
        // Argument check
        if (!isset($args['gid'])) {
            throw new \InvalidArgumentException($this->__('Error! Invalid arguments array received.'));
        }

        // create a QueryBuilder instance
        $qb = $this->entityManager->createQueryBuilder();

        // add select and from params
        $qb->select('g')
           ->from('Zikula\Module\GroupsModule\Entity\GroupEntity', 'g');

        // add clause for filtering name
        $qb->andWhere($qb->expr()->eq('g.gid', $qb->expr()->literal($args['gid'])));

        // convert querybuilder instance into a Query object
        $query = $qb->getQuery();

        // execute query
        $result = $query->getOneOrNullResult();

        // error message and return
        if (!$result) {
            return false;
        }

        // Return the gid
        return $result['name'];
    }

    /**
     * Get applications.
     *
     * @return array array of group applications
     *
     * @throws NotFoundHttpException Thrown if no group applications are found
     */
    public function getapplications()
    {
        $objArray = $this->entityManager->getRepository('ZikulaGroupsModule:GroupApplicationEntity')->findBy(array(), array('app_id' => 'ASC'));

        if ($objArray === false) {
            throw new NotFoundHttpException($this->__('Error! Could not load data.'));
        }

        $items = array();

        foreach ($objArray as $obj) {
            $group = ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'get', array('gid' => $obj['gid']));
            if ($group) {
                if (SecurityUtil::checkPermission('ZikulaGroupsModule::', $group['gid'] . '::', ACCESS_EDIT) && $group <> false) {
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
     * @param int[] $args {
     *      @type int $gid group id
     *      @type int $userid user id
     *                    }
     *
     * @return array
     *
     * @throws \InvalidArgumentException Thrown if invalid parameters are received in $args
     * @throws NotFoundHttpException Thrown if the group application cannot be found
     */
    public function getapplicationinfo($args)
    {
        if (!isset($args['gid']) || !isset($args['userid'])) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        $appInfo = $this->entityManager->getRepository('ZikulaGroupsModule:GroupApplicationEntity')->findOneBy(array('gid' => $args['gid'], 'uid' => $args['userid']));

        if (!$appInfo) {
            throw new NotFoundHttpException($this->__('Error! Could not load data.'));
        }

        return $appInfo->toArray();
    }

    /**
     * Pending action.
     *
     * @param mixed[] $args {
     *      @type int    $gid    group id
     *      @type int    $userid user id
     *      @type string $action action to take ('accept'|'reject')
     *                      }
     *
     * @return boolean true if the pending action was successfully processed
     *
     * @throws \InvalidArgumentException Thrown if invalid parameters are received in $args
     */
    public function pendingaction($args)
    {
        if (!isset($args['gid']) || !isset($args['userid']) || !isset($args['action'])) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        // delete group application
        $application = $this->entityManager->getRepository('ZikulaGroupsModule:GroupApplicationEntity')->findOneBy(array('gid' => $args['gid'], 'uid' => $args['userid']));
        $this->entityManager->remove($application);
        $this->entityManager->flush();

        if ($args['action'] == 'accept') {
            $adduser = ModUtil::apiFunc('ZikulaGroupsModule', 'admin', 'adduser', array('gid' => $args['gid'], 'uid' => $args['userid']));
        }

        // Send message part
        switch ($args['sendtag']) {
            case 1:
                if (ModUtil::available('ZikulaMailerModule')) {
                    $send = ModUtil::apiFunc('ZikulaMailerModule', 'user', 'sendmessage',
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
        $query = $this->entityManager->createQueryBuilder()
                                     ->select('count(g.gid)')
                                     ->from('ZikulaGroupsModule:GroupEntity', 'g')
                                     ->getQuery();

        return (int)$query->getSingleScalarResult();;
    }

    /**
     * Get available admin panel links.
     *
     * @return array array of admin links.
     */
    public function getlinks()
    {
        $links = array();

        if (SecurityUtil::checkPermission('ZikulaGroupsModule::', '::', ACCESS_READ)) {
            $links[] = array('url' => ModUtil::url('ZikulaGroupsModule', 'admin', 'view'), 'text' => $this->__('Groups list'), 'id' => 'groups_view', 'icon' => 'list');
        }
        if (SecurityUtil::checkPermission('ZikulaGroupsModule::', '::', ACCESS_ADMIN)) {
            $links[] = array('url' => ModUtil::url('ZikulaGroupsModule', 'admin', 'modifyconfig'), 'text' => $this->__('Settings'), 'id' => 'groups_modifyconfig', 'icon' => 'wrench');
        }

        return $links;
    }
}
