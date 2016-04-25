<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\GroupsModule\Api;

use Zikula\Core\Event\GenericEvent;
use Zikula\GroupsModule\Helper\CommonHelper;
use Zikula\GroupsModule\Entity\GroupApplicationEntity;
use Zikula\UsersModule\Entity\UserEntity;
use SecurityUtil;
use UserUtil;
use ModUtil;
use System;
use Zikula;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * User API functions for the groups module
 */
class UserApi extends \Zikula_AbstractApi
{
    /**
     * Get all group items.
     *
     * @param int[] $args {
     *      @type int $startnum record number to start get from
     *      @type int $numitems  number of items to get
     *                     }
     *
     * @return array|bool array of group items, false if none are found.
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
           ->from('ZikulaGroupsModule:GroupEntity', 'g');

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
            return false;
        }

        // Return the items
        return $objArray;
    }

    /**
     * Get a specific group item.
     *
     * @param int[] $args {
     *      @type int  $gid              Id of group item to get
     *      @type int  $startnum         Record number to start get from (group membership)
     *      @type int  $numitems         Number of items to get (group membership)
     *      @type bool $group_membership Whether to select group memberships also (defaults to true for BC)
     *      @type int  $uid              ID of user
     *                     }
     *
     * @return array|bool item array, or false on failure.
     *
     * @throws \InvalidArgumentException Thrown if the gid parameter isn't provided
     */
    public function get($args)
    {
        // Argument check
        if (!isset($args['gid'])) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        // get item
        $group = $this->entityManager->find('ZikulaGroupsModule:GroupEntity', $args['gid']);

        if (!$group) {
            return false;
        }

        // convert to array
        $result = $group->toArray();

        // Get group membership
        // Optional arguments.
        $args['group_membership'] = !isset($args['group_membership']) ? true : (bool)$args['group_membership'];
        $args['uid'] = !isset($args['uid']) ? null : (int)$args['uid'];

        if (!isset($args['startnum']) || !is_numeric($args['startnum'])) {
            $args['startnum'] = null;
        }

        if (!isset($args['numitems']) || !is_numeric($args['numitems'])) {
            $args['numitems'] = null;
        }

        $uidsArray = array();
        if ($args['group_membership']) {
            $groupUsers = $group->getUsers();
            /** @var UserEntity $user */
            foreach ($groupUsers as $user) {
                if (!is_null($args['uid']) && ($user->getUid() != $args['uid'])) {
                    continue;
                }
                $uidsArray[$user->getUid()] = $user->toArray();
            }
        }

        // Security check
        if (!SecurityUtil::checkPermission('ZikulaGroupsModule::', $result['gid'] . '::', ACCESS_READ)) {
            return false;
        }

        // Create the item array
        $result['nbuser'] = count($uidsArray);
        $result['members'] = $uidsArray;

        if (!is_null($args['uid'])) {
            $result['status'] = ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'isuserpending', array('gid' => $args['gid'], 'uid' => $args['uid']));
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
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('count(g.gid)')
           ->from('ZikulaGroupsModule:GroupEntity', 'g')
           ->where('g.gtype = :gtype')
           ->setParameter('gtype', CommonHelper::GTYPE_CORE);

        if ($this->getVar('hideclosed')) {
            $qb->andWhere('g.state <> :state')
               ->setParameter('state', CommonHelper::STATE_CLOSED);
        }

        $query = $qb->getQuery();

        return (int)$query->getSingleScalarResult();
    }

    /**
     * Utility function to count the number of items held by this module.
     *
     * @param int[] $args {
     *      @type int $gid id of group item to get
     *                     }
     *
     * @return int number of items held by this module.
     *
     * @throws \InvalidArgumentException Thrown if the gid parameter isn't provided or isn't numeric
     */
    public function countgroupmembers($args)
    {
        // Argument check
        if ((!isset($args['gid']) && !is_numeric($args['gid']))) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        $group = $this->entityManager->find('ZikulaGroupsModule:GroupEntity', $args['gid']);

        return $group->getUsers()->count();
    }

    /**
     * Get all of a user's group memberships.
     *
     * @param int[] $args {
     *      @type int $uid   user id
     *      @type int $clean flag to return an array of GIDs
     *                     }
     *
     * @return array|bool array of group items, false if no group memberships are found for the input user id.
     *
     * @throws \InvalidArgumentException Thrown if the gid parameter isn't provided or isn't numeric
     */
    public function getusergroups($args)
    {
        // Optional arguments.
        if (!isset($args['uid'])) {
            $args['uid'] = UserUtil::getVar('uid');
        }
        if (!isset($args['uid']) && !is_numeric($args['gid'])) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        // Security check
        if (!SecurityUtil::checkPermission('ZikulaGroupsModule::', '::', ACCESS_READ)) {
            return [];
        }

        $userGroups = $this->entityManager->find('ZikulaUsersModule:UserEntity', $args['uid'])->getGroups();

        $groupsArray = array();
        foreach ($userGroups as $gid => $group) {
            $groupsArray[$gid] = $group->toArray();
        }

        // Return the items
        return (isset($args['clean']) && $args['clean']) ? array_keys($groupsArray) : $groupsArray;
    }

    /**
     * Get all groups.
     *
     * @param int[] $args {
     *      @type int $startnum record number to start get from
     *      @type int $numitems  number of items to get
     *                     }
     *
     * @return array|bool array of groups, false if no groups are found.
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
           ->from('ZikulaGroupsModule:GroupEntity', 'g');

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
            return false;
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
                        'nbuser' => (($nbuser != false) ? $nbuser : 0),
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
     * @param int[] $args {
     *      @type int $uid user id
     *      @type int $gid group id
     *                     }
     *
     * @return bool true if successful, false if the group isn't found.
     *
     * @throws \InvalidArgumentException Thrown if either gid or uid are not set or not numeric
     * @throws AccessDeniedException Thrown if the current user does not have read access to the group.
     * @throws \RuntimeException Thrown if the user has already applied for this group
     */
    public function saveapplication($args)
    {
        if ((!isset($args['gid']) && !is_numeric($args['gid'])) ||
            (!isset($args['uid']) && !is_numeric($args['uid']))) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        $item = ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'get', array('gid' => $args['gid'], 'group_membership' => false));

        if (!$item) {
            return false;
        }

        if (!SecurityUtil::checkPermission('ZikulaGroupsModule::', $args['gid'] . '::', ACCESS_READ)) {
            throw new AccessDeniedException();
        }

        // Check in case the user already applied
        $pending = ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'isuserpending',
                        array('gid' => $args['gid'],
                              'uid' => $args['uid']));

        if ($pending) {
            throw new \RuntimeException($this->__('Error! You have already applied for membership of this group.'));
        }

        $application = new GroupApplicationEntity();
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
     * @param int[] $args {
     *      @type int $gid group id
     *      @type int $uid user id
     *                     }
     *
     * @return bool true if successful
     *
     * @throws \InvalidArgumentException Thrown if either gid or uid are not set or not numeric
     */
    public function cancelapp($args)
    {
        if ((!isset($args['gid']) && !is_numeric($args['gid'])) ||
            (!isset($args['uid']) && !is_numeric($args['uid']))) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        // Checking first if this user is really pending.
        $ispending = ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'isuserpending',
                        array('gid' => $args['gid'],
                              'uid' => $args['uid']));

        if ($ispending == true) {
            $application = $this->entityManager->getRepository('ZikulaGroupsModule:GroupApplicationEntity')->findOneBy(array('gid' => $args['gid'], 'uid' => $args['uid']));
            $this->entityManager->remove($application);
            $this->entityManager->flush();
        }

        return true;
    }

    /**
     * Check if user is pending.
     *
     * @param int[] $args {
     *      @type int $uid user id
     *      @type int $gid group id
     *                     }
     *
     * @return bool true if user has a pending application to the group, false otherwise
     *
     * @throws \InvalidArgumentException Thrown if either gid or uid are not set or not numeric
     */
    public function isuserpending($args)
    {
        if ((!isset($args['gid']) && !is_numeric($args['gid'])) ||
            (!isset($args['uid']) && !is_numeric($args['uid']))) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        $applications = $this->entityManager->getRepository('ZikulaGroupsModule:GroupApplicationEntity')->findBy(array('gid' => $args['gid'], 'uid' => $args['uid']));

        if (count($applications) >= 1) {
            return true;
        }

        return false;
    }

    /**
     * Update user.
     *
     * @param mixed[] $args {
     *      @type int    $gid    group id
     *      @type int    $gtype  group type
     *      @type string $action action
     *                       }
     *
     * @return bool true if successful
     *
     * @throws \InvalidArgumentException Thrown if either gtype or gid are not set or not numeric or
     *                                          if action isn't set or one of 'subscribe', 'unsubscribe' or 'cancel'
     * @throws AccessDeniedException Thrown if the user is not logged in.
     * @throws \RuntimeException Thrown if the user couldn't be added to the group,
     *                                  if the application to the group couldn't be cancelled, or
     *                                  if the user couldn't be removed from the group
     */
    public function userupdate($args)
    {
        if (!isset($args['gtype']) && !is_numeric($args['gtype']) ||
            (!isset($args['gid']) && !is_numeric($args['gid'])) ||
            !isset($args['action'])) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        if ($args['action'] != 'subscribe' && $args['action'] != 'unsubscribe' && $args['action'] != 'cancel') {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        if (!UserUtil::isLoggedIn()) {
            throw new AccessDeniedException($this->__('Error! You must register for a user account on this site before you can apply for membership of a group.'));
        }

        $userid = UserUtil::getVar('uid');

        if ($args['action'] == 'subscribe') {
            if ($args['gtype'] == CommonHelper::GTYPE_PRIVATE) {
                if (!isset($args['applytext'])) {
                    throw new \InvalidArgumentException(__('Invalid arguments array received'));
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
                    throw new \RuntimeException($this->__('Error! Could not add the user to the group.'));
                }
            }
        } elseif ($args['action'] == 'cancel') {
            $save = ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'cancelapp',
                            array('gid' => $args['gid'],
                                  'uid' => $userid));

            if ($save == false) {
                throw new \RuntimeException($this->__('Error! Could not remove the user from the group.'));
            }
        } else {
            $save = ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'removeuser',
                            array('gid' => $args['gid'],
                                  'uid' => $userid));

            if ($save == false) {
                throw new \RuntimeException($this->__('Error! Could not remove the user from the group.'));
            }
        }

        return true;
    }

    /**
     * Add a user to a group item.
     *
     * @param int[] $args {
     *      @type int $gid the ID of the item
     *      @type int $uid the ID of the user
     *                     }
     *
     * @return bool true if successful, false otherwise.
     *
     * @throws \InvalidArgumentException Thrown if either gid or uid are not set or not numeric
     * @throws AccessDeniedException Thrown if the current user does not have read access to the group.
     */
    public function adduser($args)
    {
        // Argument check
        if ((!isset($args['gid']) && !is_numeric($args['gid'])) ||
            (!isset($args['uid']) && !is_numeric($args['uid']))) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        // get group
        $group = $this->entityManager->find('ZikulaGroupsModule:GroupEntity', $args['gid']);

        if (!$group) {
            return false;
        }

        // Security check
        if (!SecurityUtil::checkPermission('ZikulaGroupsModule::', $args['gid'] . '::', ACCESS_READ)) {
            throw new AccessDeniedException();
        }

        // verify if the user is alredy a member of this group
        $user = $this->entityManager->find('ZikulaUsersModule:UserEntity', $args['uid']);
        $isMember = $group->getUsers()->contains($user);

        // Add item
        if (!$isMember) {
            $user->addGroup($group);
            $this->entityManager->flush();

            // Let other modules know that we have updated a group.
            $adduserEvent = new GenericEvent(['gid' => $args['gid'], 'uid' => $args['uid']]);
            $this->getDispatcher()->dispatch('group.adduser', $adduserEvent);
        } else {
            if (isset($args['verbose']) && !$args['verbose']) {
                return false;
            }

            throw new \RuntimeException($this->__('Error! You are already a member of this group.'));
        }

        // Let the calling process know that we have finished successfully
        return true;
    }

    /**
     * Remove a user from a group item.
     *
     * @param int[] $args {
     *      @type int $gid the ID of the item
     *      @type int $uid the ID of the user
     *                     }
     *
     * @return bool true if successful, false otherwise.
     *
     * @throws \InvalidArgumentException Thrown if either gid or uid are not set or not numeric
     * @throws AccessDeniedException Thrown if the current user does not have read access tp the group.
     */
    public function removeuser($args)
    {
        if ((!isset($args['gid']) && !is_numeric($args['gid'])) ||
            (!isset($args['uid']) && !is_numeric($args['uid']))) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        // get group
        $group = $this->entityManager->find('ZikulaGroupsModule:GroupEntity', $args['gid']);

        if (!$group) {
            return false;
        }

        // Security check
        if (!SecurityUtil::checkPermission('ZikulaGroupsModule::', $args['gid'] . '::', ACCESS_READ)) {
            throw new AccessDeniedException();
        }

        // delete user from group
        $user = $this->entityManager->find('ZikulaUsersModule:UserEntity', $args['uid']);
        $user->removeGroup($group);
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
     * @return array array of users
     */
    public function whosonline()
    {
        $activetime = time() - (\System::getVar('secinactivemins') * 60);

        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('DISTINCT s.uid')
           ->from('ZikulaUsersModule:UserSessionEntity', 's')
           ->where('s.lastused > :activetime')
           ->setParameter('activetime', $activetime)
           ->andWhere('s.uid <> 0');

        $query = $qb->getQuery();

        $items = $query->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

        return $items;
    }

    /**
     * Check if a user is a member of a group.
     *
     * @param int[] $args {
     *      @type int $uid user id
     *      @type int $gid group id
     *                     }
     *
     * @return boolean true if member of a group, false otherwise.
     *
     * @throws \InvalidArgumentException Thrown if either gid or uid are not set or not numeric
     */
    public function isgroupmember($args)
    {
        if ((!isset($args['uid']) && !is_numeric($args['uid'])) ||
            (!isset($args['gid']) && !is_numeric($args['gid']))) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        // Security check
        if (!SecurityUtil::checkPermission('ZikulaGroupsModule::', '::', ACCESS_READ)) {
            return false;
        }

        // Get group and check if the user exists in this group.
        $group = ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'get', array('gid' => $args['gid'], 'group_membership' => true, 'uid' => $args['uid']));

        if (!$group || !array_key_exists($args['uid'], $group['members'])) {
            // either group does not exist or the requested uid is not a member of the group
            return false;
        }

        // report the user is a member of the group
        return true;
    }
}
