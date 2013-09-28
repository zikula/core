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

namespace Zikula\Module\GroupsModule\Api;

use Zikula\Core\Event\GenericEvent;
use Zikula\Module\GroupsModule\Helper\CommonHelper;
use Zikula\Module\GroupsModule\Entity\GroupApplicationEntity;
use Zikula\Module\GroupsModule\Entity\GroupMembershipEntity;
use SecurityUtil;
use DBUtil;
use LogUtil;
use DataUtil;
use UserUtil;
use ModUtil;
use System;
use Zikula;

/**
 * Groups_Api_User class.
 */
class UserApi extends \Zikula_AbstractApi
{
    /**
     * Get all group items.
     *
     * @param int $args['startnum'] record number to start get from.
     * @param int $args['numitems'] number of items to get.
     *
     * @return mixed array of group items, or false on failure.
     */
    public function getall($args)
    {
        $items = array();

        // Security check
        if (!SecurityUtil::checkPermission('ZikulaGroupsModule::', '::', ACCESS_READ)) {
            return $items;
        }

        // create a QueryBuilder instance
        $qb = $this->entityManager->createQueryBuilder();

        // add select and from params
        $qb->select('g')
           ->from('Zikula\Module\GroupsModule\Entity\GroupEntity', 'g');

         // add clause for ordering
        $qb->addOrderBy('g.name', 'ASC');

        // add limit and offset
        $startnum = (!isset($args['startnum']) || !is_numeric($args['startnum'])) ? 0 : (int)$args['startnum'];
        $numitems = (!isset($args['numitems']) || !is_numeric($args['numitems'])) ? 0 : (int)$args['numitems'];
        if ($numitems > 0) {
            $qb->setFirstResult($startnum)
               ->setMaxResults($numitems);
        }

        // convert querybuilder instance into a Query object
        $query = $qb->getQuery();

        // execute query
        $objArray = $query->getResult();

        // Check for an error with the database code
        if ($objArray === false) {
            return LogUtil::registerError($this->__('Error! Could not load data.'));
        }

        // Return the items
        return $objArray;
    }

    /**
     * Get a specific group item.
     *
     * @param int $args['gid'] id of group item to get.
     * @param int $args['startnum'] record number to start get from (group membership).
     * @param int $args['numitems'] number of items to get (group membership).
     *
     * @return mixed item array, or false on failure.
     */
    public function get($args)
    {
        // Argument check
        if (!isset($args['gid'])) {
            return LogUtil::registerArgsError();
        }

        // get item
        $result = $this->entityManager->find('Zikula\Module\GroupsModule\Entity\GroupEntity', $args['gid']);

        if (!$result) {
            return false;
        }

        // convert to array
        $result = $result->toArray();

        // Get group membership
        // Optional arguments.
        if (!isset($args['startnum']) || !is_numeric($args['startnum'])) {
            $args['startnum'] = null;
        }
        if (!isset($args['numitems']) || !is_numeric($args['numitems'])) {
            $args['numitems'] = null;
        }

        $groupmembership = $this->entityManager->getRepository('Zikula\Module\GroupsModule\Entity\GroupMembershipEntity')->findBy(array('gid' => $args['gid']), array(), $args['numitems'], $args['startnum']);

        // Check for an error with the database code
        if ($groupmembership === false) {
            return false;
        }

        $uidsArray = array();
        foreach ($groupmembership as $gm) {
            $gm = $gm->toArray();
            $uidsArray[$gm['uid']] = $gm;
        }

        // Security check
        if (!SecurityUtil::checkPermission('ZikulaGroupsModule::', $result['gid'] . '::', ACCESS_READ)) {
            return false;
        }

        // Create the item array
        $result['nbuser'] = count($uidsArray);
        $result['members'] = $uidsArray;

        $uid = UserUtil::getVar('uid');
        if ($uid != 0) {
            $result['status'] = ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'isuserpending', array('gid' => $args['gid'], 'uid' => $uid));
        } else {
            $result['status'] = false;
        }

        // Return the item array
        return $result;
    }

    /**
     * Utility function to count the number of items held by this module.
     *
     * @return int number of items held by this module
     */
    public function countitems()
    {
        $dql = "SELECT count(g.gid) FROM Zikula\Module\GroupsModule\Entity\GroupEntity g WHERE g.gtype <> " . CommonHelper::GTYPE_CORE;

        if ($this->getVar('hideclosed')) {
            $dql .= " AND g.state <> " . CommonHelper::STATE_CLOSED;
        }

        $query = $this->entityManager->createQuery($dql);
        return (int)$query->getSingleScalarResult();
    }

    /**
     * Utility function to count the number of items held by this module.
     *
     * @param int $args['gid'] id of group item to get.
     *
     * @return int number of items held by this module.
     */
    public function countgroupmembers($args)
    {
        // Argument check
        if (!isset($args['gid'])) {
            return LogUtil::registerArgsError();
        }

        $dql = "SELECT count(m.gid) FROM Zikula\Module\GroupsModule\Entity\GroupMembershipEntity m WHERE m.gid = {$args['gid']}";
        $query = $this->entityManager->createQuery($dql);
        return (int)$query->getSingleScalarResult();
    }

    /**
     * Get all of a user's group memberships.
     *
     * @param int $args['uid'] user id.
     * @param int $args['clean'] flag to return an array of GIDs.
     *
     * @return mixed array of group items, or false on failure.
     */
    public function getusergroups($args)
    {
        // Optional arguments.
        if (!isset($args['uid'])) {
            $args['uid'] = UserUtil::getVar('uid');
        }
        if (!isset($args['uid'])) {
            return LogUtil::registerArgsError();
        }

        $items = array();

        // Security check
        if (!SecurityUtil::checkPermission('ZikulaGroupsModule::', '::', ACCESS_READ)) {
            return $items;
        }

        $groupmembership = $this->entityManager->getRepository('Zikula\Module\GroupsModule\Entity\GroupMembershipEntity')->findBy(array('uid' => $args['uid']));

        // Check for an error with the database code
        if ($groupmembership === false) {
            return LogUtil::registerError($this->__('Error! Could not load data.'));
        }

        $objArray = array();
        foreach ($groupmembership as $gm) {
            $objArray[] = $gm->toArray();
        }

        if (isset($args['clean']) && $args['clean']) {
            $newArray = array();

            foreach ($objArray as $obj) {
                $newArray[] = $obj['gid'];
            }

            $objArray = $newArray;
        }

        // Return the items
        return $objArray;
    }

    /**
     * Get all groups.
     *
     * @param array $args
     *
     * @return array of groups.
     */
    public function getallgroups($args)
    {
        $items = array();

        if (!SecurityUtil::checkPermission('ZikulaGroupsModule::', 'ANY', ACCESS_OVERVIEW)) {
            return $items;
        }

        // create a QueryBuilder instance
        $qb = $this->entityManager->createQueryBuilder();

        // add select and from params
        $qb->select('g')
           ->from('Zikula\Module\GroupsModule\Entity\GroupEntity', 'g');

        // add clause for filtering type
        $qb->andWhere($qb->expr()->neq('g.gtype', $qb->expr()->literal(CommonHelper::GTYPE_CORE)));

        // add clause for filtering state
        if ($this->getVar('hideclosed')) {
            $qb->andWhere($qb->expr()->neq('g.state', $qb->expr()->literal(CommonHelper::STATE_CLOSED)));
        }

        // add clause for ordering
        $qb->addOrderBy('g.name', 'ASC');

        // add limit and offset
        $startnum = (!isset($args['startnum']) || !is_numeric($args['startnum'])) ? 0 : (int)$args['startnum'];
        $numitems = (!isset($args['numitems']) || !is_numeric($args['numitems'])) ? 0 : (int)$args['numitems'];
        if ($numitems > 0) {
            $qb->setFirstResult($startnum)
               ->setMaxResults($numitems);
        }

        // convert querybuilder instance into a Query object
        $query = $qb->getQuery();

        // execute query
        $objArray = $query->getResult();

        if ($objArray === false) {
            return LogUtil::registerError($this->__('Error! Could not load data.'));
        }

        $uid = UserUtil::getVar('uid');

        if ($uid != 0) {
            $memberships = ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'getusergroups',
                            array('uid' => $uid,
                                    'clean' => true));
        } else {
            $memberships = false;
        }

        $row = 1;

        foreach ($objArray as $obj) {
            $obj = $obj->toArray();

            $gid = $obj['gid'];
            $name = $obj['name'];
            $gtype = $obj['gtype'];
            $description = $obj['description'];
            $state = $obj['state'];
            $nbumax = $obj['nbumax'];

            if (SecurityUtil::checkPermission('ZikulaGroupsModule::', $gid . '::', ACCESS_OVERVIEW)) {
                if (!isset($gtype) || is_null($gtype)) {
                    $gtype = CommonHelper::GTYPE_CORE;
                }
                if (is_null($state)) {
                    $state = CommonHelper::STATE_CLOSED;
                }

                $ismember = false;
                if (is_array($memberships) && in_array($gid, $memberships)) {
                    $ismember = true;
                }

                if ($uid != 0) {
                    $status = ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'isuserpending', array('gid' => $gid, 'uid' => $uid));
                } else {
                    $status = false;
                }

                $nbuser = ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'countgroupmembers', array('gid' => $gid));

                if (SecurityUtil::checkPermission('ZikulaGroupsModule::', $gid . '::', ACCESS_READ)) {
                    $canview = true;
                    $canapply = true;
                } else {
                    $canview = false;
                    $canapply = false;
                }

                // Anon users or non-members should not be able to see private groups.
                if ($gtype == CommonHelper::GTYPE_PRIVATE) {
                    if (!$uid || !$this->isgroupmember(array('uid' => $uid, 'gid' => $gid))) {
                        continue;
                    }
                }

                $items[] = array(
                        'gid' => $gid,
                        'name' => $name,
                        'gtype' => $gtype,
                        'description' => $description,
                        'state' => $state,
                        'nbuser' => (($nbuser <> false) ? $nbuser : 0),
                        'nbumax' => $nbumax,
                        'ismember' => $ismember,
                        'status' => $status,
                        'canview' => $canview,
                        'canapply' => $canapply,
                        'islogged' => UserUtil::isLoggedIn(),
                        'row' => $row);

                if ($row == 1) {
                    $row = 2;
                } else {
                    $row = 1;
                }
            }
        }

        return $items;
    }

    /**
     * Save application.
     *
     * @param int $args['uid'] user id.
     * @param int $args['gid'] group id.
     *
     * @return boolean
     */
    public function saveapplication($args)
    {
        if (!isset($args['gid']) || !isset($args['uid'])) {
            return LogUtil::registerArgsError();
        }

        $item = ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'get', array('gid' => $args['gid']));

        if (!$item) {
            return LogUtil::registerError($this->__('Sorry! No such item found.'));
        }

        if (!SecurityUtil::checkPermission('ZikulaGroupsModule::', $args['gid'] . '::', ACCESS_READ)) {
            throw new \Zikula_Exception_Forbidden();
        }

        // Check in case the user already applied
        $pending = ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'isuserpending',
                        array('gid' => $args['gid'],
                              'uid' => $args['uid']));

        if ($pending) {
            return LogUtil::registerError($this->__('Error! You have already applied for membership of this group.'));
        }

        $application = new GroupApplicationEntity;
        $application['uid'] = $args['uid'];
        $application['gid'] = $args['gid'];
        $application['application'] = $args['applytext'];
        $application['status'] = 1;

        $this->entityManager->persist($application);
        $this->entityManager->flush();

        return true;
    }

    /**
     * Delete app from group_applications.
     *
     * @param array $args
     *
     * @return boolean
     */
    public function cancelapp($args)
    {
        if (!isset($args['gid']) || !isset($args['uid'])) {
            return LogUtil::registerArgsError();
        }

        // Checking first if this user is really pending.
        $ispending = ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'isuserpending',
                        array('gid' => $args['gid'],
                              'uid' => $args['uid']));

        if ($ispending == true) {
            $application = $this->entityManager->getRepository('Zikula\Module\GroupsModule\Entity\GroupApplicationEntity')->findOneBy(array('gid' => $args['gid'], 'uid' => $args['uid']));
            $this->entityManager->remove($application);
            $this->entityManager->flush();
        }

        return true;
    }

    /**
     * Check if user is pending.
     *
     * @param int $args['uid'] user id.
     * @param int $args['gid'] group id.
     *
     * @return boolean
     */
    public function isuserpending($args)
    {
        if (!isset($args['gid']) || !isset($args['uid'])) {
            return LogUtil::registerArgsError();
        }

        $applications = $this->entityManager->getRepository('Zikula\Module\GroupsModule\Entity\GroupApplicationEntity')->findBy(array('gid' => $args['gid'], 'uid' => $args['uid']));

        if (count($applications) >= 1) {
            return true;
        }

        return false;
    }

    /**
     * Update user.
     *
     * @param int    $args['uid']     user id.
     * @param int    $args['gtype'].
     * @param string $args['action'].
     *
     * @return boolean
     */
    public function userupdate($args)
    {
        if (!isset($args['gid']) || !isset($args['action']) || !isset($args['gtype'])) {
            return LogUtil::registerArgsError();
        }

        if ($args['action'] != 'subscribe' && $args['action'] != 'unsubscribe' && $args['action'] != 'cancel') {
            return LogUtil::registerArgsError();
        }

        if (!UserUtil::isLoggedIn()) {
            LogUtil::registerError($this->__('Error! You must register for a user account on this site before you can apply for membership of a group.'));
        }

        $userid = UserUtil::getVar('uid');

        if ($args['action'] == 'subscribe') {

            if ($args['gtype'] == CommonHelper::GTYPE_PRIVATE) {
                if (!isset($args['applytext'])) {
                    return LogUtil::registerArgsError();
                }

                // We save the user in the application table
                $save = ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'saveapplication',
                                array('gid' => $args['gid'],
                                      'uid' => $userid,
                                      'applytext' => $args['applytext']));

                if ($save == false) {
                    return false;
                }

                if ($this->getVar('mailwarning')) {
                    $uname = UserUtil::getVar('uname', $userid);
                    $send = ModUtil::apiFunc('ZikulaMailerModule', 'user', 'sendmessage',
                                    array('toname' => $this->__('Administrator'),
                                          'toaddress' => System::getVar('adminmail'),
                                          'subject' => $this->__('Group membership application registered'),
                                          'body' => $this->__f('The registered user %1$s has applied for membership of a group. The details of the application are as follows: %2$s', array($uname, $args['applytext']))));
                }
            } else {
                // We save the user into the groups
                $save = ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'adduser',
                                array('gid' => $args['gid'],
                                      'uid' => $userid));

                if ($save == false) {
                    return LogUtil::registerError($this->__('Error! Could not add the user to the group.'));
                }
            }
        } elseif ($args['action'] == 'cancel') {

            $save = ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'cancelapp',
                            array('gid' => $args['gid'],
                                  'uid' => $userid));

            if ($save == false) {
                return LogUtil::registerError($this->__('Error! Could not remove the user from the group.'));
            }
        } else {

            $save = ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'removeuser',
                            array('gid' => $args['gid'],
                                  'uid' => $userid));

            if ($save == false) {
                return LogUtil::registerError($this->__('Error! Could not remove the user from the group.'));
            }
        }

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
        if (!isset($args['gid']) || !isset($args['uid'])) {
            return LogUtil::registerArgsError();
        }

        // get group
        $group = ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'get', array('gid' => $args['gid']));

        if (!$group) {
            return LogUtil::registerError($this->__('Sorry! No such item found.'));
        }

        // Security check
        if (!SecurityUtil::checkPermission('ZikulaGroupsModule::', $args['gid'] . '::', ACCESS_READ)) {
            throw new \Zikula_Exception_Forbidden();
        }

        // verify if the user is alredy a member of this group
        $is_member = ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'isgroupmember', array('gid' => $args['gid'], 'uid' => $args['uid']));

        // Add item
        if (!$is_member) {
            $membership = new GroupMembershipEntity;
            $membership['gid'] = $args['gid'];
            $membership['uid'] = $args['uid'];
            $this->entityManager->persist($membership);
            $this->entityManager->flush();

            // Let other modules know that we have updated a group.
            $adduserEvent = new GenericEvent($membership);
            $this->getDispatcher()->dispatch('group.adduser', $adduserEvent);
        } else {
            if (isset($args['verbose']) && !$args['verbose']) {
                return false;
            }

            return LogUtil::registerError($this->__('Error! You are already a member of this group.'));
        }

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
        if (!isset($args['gid']) || !isset($args['uid'])) {
            return LogUtil::registerArgsError();
        }

        // get group
        $group = ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'get', array('gid' => $args['gid']));

        if (!$group) {
            return LogUtil::registerError($this->__('Sorry! No such item found.'));
        }

        // Security check
        if (!SecurityUtil::checkPermission('ZikulaGroupsModule::', $args['gid'] . '::', ACCESS_READ)) {
            throw new \Zikula_Exception_Forbidden();
        }

        // delete user from group
        $membership = $this->entityManager->getRepository('Zikula\Module\GroupsModule\Entity\GroupMembershipEntity')->findOneBy(array('gid' => $args['gid'], 'uid' => $args['uid']));
        $this->entityManager->remove($membership);
        $this->entityManager->flush();

        // Let other modules know we have updated a group
        $removeuserEvent = new GenericEvent(null, array('gid' => $args['gid'], 'uid' => $args['uid']));
        $this->getDispatcher()->dispatch('group.removeuser', $removeuserEvent);

        // Let the calling process know that we have finished successfully
        return true;
    }

    /**
     * Find who is online.
     *
     * @param unknown_type $args
     *
     * @return mixed array of users, or false.
     */
    public function whosonline()
    {
        $activetime = time() - (\System::getVar('secinactivemins') * 60);

        $dql = "SELECT DISTINCT s.uid FROM Zikula\Module\UsersModule\Entity\UserSessionEntity s WHERE s.lastused > ' " . $activetime . "' AND s.uid <> 0";
        $query = $this->entityManager->createQuery($dql);
        $items = $query->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);


        return $items;
    }

    /**
     * Check if a user is a member of a group.
     *
     * @param int $args['uid'] user id.
     * @param int $args['gid'] group id.
     *
     * @return boolean true if member of a group, false otherwise.
     */
    public function isgroupmember($args)
    {
        if (!isset($args['uid']) || !is_numeric($args['uid']) ||
            !isset($args['gid']) || !is_numeric($args['gid'])) {
            return LogUtil::registerArgsError();
        }

        // Security check
        if (!SecurityUtil::checkPermission('ZikulaGroupsModule::', '::', ACCESS_READ)) {
            return false;
        }

        // Get the group
        $group = ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'get', array('gid' => $args['gid']));

        // check if group exists
        if (!$group) {
            // report failiure
            return false;
        }

        // check if the user exists in the group
        if (!isset($group['members'][$args['uid']])) {
            // report failiure
            return false;
        }

        // report the user is a member of the group
        return true;
    }

}
