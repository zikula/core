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
use Zikula\GroupsModule\Entity\GroupEntity;
use Zikula\GroupsModule\Helper\CommonHelper;
use SecurityUtil;
use Zikula;
use ModUtil;
use UserUtil;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

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
        $obj = new GroupEntity();
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
     * @return boolean true if successful, false on failure.
     *
     * @throws \InvalidArgumentException Thrown if the gid parameter isn't provided
     * @throws AccessDeniedException Thrown if the current user does not have delete access for the group.
     * @throws \RuntimeException Thrown if the requested group is either the default users group or primary admins group
     */
    public function delete($args)
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

        // Security check
        if (!SecurityUtil::checkPermission('ZikulaGroupsModule::', $args['gid'] . '::', ACCESS_DELETE)) {
            throw new AccessDeniedException();
        }

        // Special groups check
        $defaultgroupid = $this->getVar('defaultgroup', 0);
        if ($group['gid'] == $defaultgroupid) {
            throw new \RuntimeException($this->__('Sorry! You cannot delete the default users group.'));
        }

        $primaryadmingroupid = $this->getVar('primaryadmingroup', 0);
        if ($group['gid'] == $primaryadmingroupid) {
            throw new \RuntimeException($this->__('Sorry! You cannot delete the primary administrators group.'));
        }

        // Delete the group
        $group->removeAllUsers();
        // @todo Is there any reason why we don't delete group applications?
        $this->entityManager->remove($group);
        // this could be quite memory intensive for large groups managing large collections.
        $this->entityManager->flush();

        // Remove any group permissions for this group
        $query = $this->entityManager->createQueryBuilder()
                                     ->delete()
                                     ->from('ZikulaPermissionsModule:PermissionEntity', 'p')
                                     ->where('p.gid = :gid')
                                     ->setParameter('gid', $args['gid'])
                                     ->getQuery();
        $query->getResult();

        // Let other modules know that we have deleted a group.
        $deleteEvent = new GenericEvent($group->toArray());
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
     * @return bool true if successful, false on failure.
     *
     * @throws \InvalidArgumentException Thrown if either the gid or name parameters are not provided
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
            return false;
        }

        // Security check
        if (!SecurityUtil::checkPermission('ZikulaGroupsModule::', $args['gid'] . '::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        // Other check
        $checkname = ModUtil::apiFunc('ZikulaGroupsModule', 'admin', 'getgidbyname', [
            'name' => $args['name'],
            'checkgid' => $args['gid']
        ]);

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
     * @return bool true if successful, false on failure.
     *
     * @throws \InvalidArgumentException Thrown if either gid or uid are not set or not numeric
     * @throws AccessDeniedException Thrown if the current user does not have edit access to the group.
     */
    public function adduser($args)
    {
        // Argument check
        if ((!isset($args['gid'])) || (!isset($args['uid']))) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        // get group
        $group = ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'get', ['gid' => $args['gid'], 'group_membership' => false]);

        if (!$group) {
            return false;
        }

        // Security check
        if (!SecurityUtil::checkPermission('ZikulaGroupsModule::', $args['gid'] . '::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        $user = $this->entityManager->find('ZikulaUsersModule:UserEntity', $args['uid']);
        // Add user to group
        $user->addGroup($this->entityManager->getReference('ZikulaGroupsModule:GroupEntity', $args['gid']));
        $this->entityManager->flush();

        // Let other modules know that we have updated a group.
        $adduserEvent = new GenericEvent(['gid' => $args['gid'], 'uid' => $args['uid']]);
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
     * @return bool true if successful, false on failure.
     *
     * @throws \InvalidArgumentException Thrown if either gid or uid are not set or not numeric
     * @throws AccessDeniedException Thrown if the current user does not have edit access to the group.
     */
    public function removeuser($args)
    {
        // Argument check
        if ((!isset($args['gid'])) || (!isset($args['uid']))) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        // get group
        $group = $this->entityManager->find('ZikulaGroupsModule:GroupEntity', $args['gid']);

        if (!$group) {
            return false;
        }

        // Security check
        if (!SecurityUtil::checkPermission('ZikulaGroupsModule::', $args['gid'] . '::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        $user = $this->entityManager->find('ZikulaUsersModule:UserEntity', $args['uid']);
        // delete user from group
        $user->removeGroup($group);
        $this->entityManager->flush();

        // Let other modules know we have updated a group
        $removeuserEvent = new GenericEvent(['gid' => $args['gid'], 'uid' => $args['uid']]);
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
        $qb->andWhere($qb->expr()->eq('g.name', ':gname'))->setParameter('gname', $args['name']);

        // Optional Where to use when modifying a group to check if there is
        // already another group by that name.
        if (isset($args['checkgid']) && is_numeric($args['checkgid'])) {
            $qb->andWhere($qb->expr()->neq('g.gid', ':ggid'))->setParameter('ggid', $args['checkgid']);
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
           ->from('Zikula\GroupsModule\Entity\GroupEntity', 'g');

        // add clause for filtering name
        $qb->andWhere($qb->expr()->eq('g.gid', ':ggid'))->setParameter('ggid', $args['gid']);

        // convert querybuilder instance into a Query object
        $query = $qb->getQuery();

        // execute query
        $result = $query->getOneOrNullResult();

        // error message and return
        if (!$result) {
            return false;
        }

        return $result['name'];
    }

    /**
     * Get applications.
     *
     * @return array|bool array of group applications or false if no group applications are found.
     */
    public function getapplications()
    {
        $objArray = $this->entityManager->getRepository('ZikulaGroupsModule:GroupApplicationEntity')->findBy([], ['app_id' => 'ASC']);

        if (false === $objArray) {
            return false;
        }

        $items = [];

        foreach ($objArray as $obj) {
            $group = ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'get', ['gid' => $obj['gid'], 'group_membership' => false]);
            if (!$group) {
                continue;
            }

            if (SecurityUtil::checkPermission('ZikulaGroupsModule::', $group['gid'] . '::', ACCESS_EDIT) && $group != false) {
                $items[] = [
                    'app_id' => $obj['app_id'],
                    'userid' => $obj['uid'],
                    'username' => UserUtil::getVar('uname', $obj['uid']),
                    'appgid' => $obj['gid'],
                    'gname' => $group['name'],
                    'application' => nl2br($obj['application']),
                    'status' => $obj['status']
                ];
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
     * @return array|bool False if no application is found.
     *
     * @throws \InvalidArgumentException Thrown if invalid parameters are received in $args
     */
    public function getapplicationinfo($args)
    {
        if (!isset($args['gid']) || !isset($args['userid'])) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        $appInfo = $this->entityManager->getRepository('ZikulaGroupsModule:GroupApplicationEntity')->findOneBy(['gid' => $args['gid'], 'uid' => $args['userid']]);

        if (!$appInfo) {
            return false;
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
        $application = $this->entityManager->getRepository('ZikulaGroupsModule:GroupApplicationEntity')->findOneBy(['gid' => $args['gid'], 'uid' => $args['userid']]);
        $this->entityManager->remove($application);
        $this->entityManager->flush();

        if ($args['action'] == 'accept') {
            $adduser = ModUtil::apiFunc('ZikulaGroupsModule', 'admin', 'adduser', ['gid' => $args['gid'], 'uid' => $args['userid']]);
        }

        // Send message part
        switch ($args['sendtag']) {
            case 1:
                $send = ModUtil::apiFunc('ZikulaMailerModule', 'user', 'sendmessage', [
                    'toname' => UserUtil::getVar('uname', $args['userid']),
                    'toaddress' => UserUtil::getVar('email', $args['userid']),
                    'subject' => $args['reasontitle'],
                    'body' => $args['reason']
                ]);
                break;
            default:
                $send = true;
        }

        return $send;
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

        return (int)$query->getSingleScalarResult();
    }

    /**
     * Get available admin panel links.
     *
     * @return array array of admin links.
     */
    public function getLinks()
    {
        $links = [];

        if (SecurityUtil::checkPermission('ZikulaGroupsModule::', '::', ACCESS_READ)) {
            $links[] = [
                'url' => ModUtil::url('ZikulaGroupsModule', 'admin', 'view'),
                'text' => $this->__('Groups list'),
                'id' => 'groups_view',
                'icon' => 'list'
            ];
        }
        if (SecurityUtil::checkPermission('ZikulaGroupsModule::', '::', ACCESS_ADMIN)) {
            $links[] = [
                'url' => ModUtil::url('ZikulaGroupsModule', 'admin', 'modifyconfig'),
                'text' => $this->__('Settings'),
                'id' => 'groups_modifyconfig',
                'icon' => 'wrench'
            ];
        }

        return $links;
    }
}
