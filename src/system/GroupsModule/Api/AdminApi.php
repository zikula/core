<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\GroupsModule\Api;

use ModUtil;
use ServiceUtil;
use Swift_Message;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use UserUtil;
use Zikula\Core\Event\GenericEvent;
use Zikula\GroupsModule\Helper\CommonHelper;
use Zikula\GroupsModule\Entity\GroupEntity;

/**
 * Adminstrative API functions for the groups module
 *
 * @deprecated remove at Core-2.0
 */
class AdminApi
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
     * @throws AccessDeniedException Thrown if the current user does not have add access
     */
    public function create($args)
    {
        // Argument check
        if (!isset($args['name'])) {
            $translator = ServiceUtil::get('translator.default');
            throw new \InvalidArgumentException($translator->__('Invalid arguments array received'));
        }

        // Setting defaults
        if (!isset($args['gtype'])) {
            $args['gtype'] = CommonHelper::GTYPE_CORE;
        }
        if (!isset($args['state'])) {
            $args['state'] = CommonHelper::STATE_CLOSED;
        }

        // Security check
        $permissionApi = ServiceUtil::get('zikula_permissions_module.api.permission');
        if (!$permissionApi->Permission('ZikulaGroupsModule::', '::', ACCESS_ADD)) {
            throw new AccessDeniedException();
        }

        // Add item
        $obj = new GroupEntity();
        $obj['name'] = $args['name'];
        $obj['gtype'] = $args['gtype'];
        $obj['state'] = $args['state'];
        $obj['nbumax'] = $args['nbumax'];
        $obj['description'] = $args['description'];

        $entityManager = ServiceUtil::get('doctrine')->getManager();
        $entityManager->persist($obj);
        $entityManager->flush();

        // Get the ID of the item that we inserted.
        $gid = $obj['gid'];

        // Let other modules know that we have created a new group.
        $createEvent = new GenericEvent($obj);
        ServiceUtil::get('event_dispatcher')->dispatch('group.create', $createEvent);

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
     * @return boolean true if successful, false on failure
     *
     * @throws \InvalidArgumentException Thrown if the gid parameter isn't provided
     * @throws AccessDeniedException Thrown if the current user does not have delete access for the group
     * @throws \RuntimeException Thrown if the requested group is either the default users group or primary admins group
     */
    public function delete($args)
    {
        $translator = ServiceUtil::get('translator.default');

        // Argument check
        if (!isset($args['gid'])) {
            throw new \InvalidArgumentException($translator->__('Invalid arguments array received'));
        }

        // Security check
        $permissionApi = ServiceUtil::get('zikula_permissions_module.api.permission');
        if (!$permissionApi->hasPermission('ZikulaGroupsModule::', $args['gid'] . '::', ACCESS_DELETE)) {
            throw new AccessDeniedException();
        }

        // get item
        $entityManager = ServiceUtil::get('doctrine')->getManager();
        $group = $entityManager->find('ZikulaGroupsModule:GroupEntity', $args['gid']);
        if (!$group) {
            return false;
        }

        $variableApi = ServiceUtil::get('zikula_extensions_module.api.variable');

        // Special groups check
        $defaultgroupid = $variableApi->get('ZikulaGroupsModule', 'defaultgroup', 0);
        if ($group['gid'] == $defaultgroupid) {
            throw new \RuntimeException($translator->__('Sorry! You cannot delete the default users group.'));
        }

        $primaryadmingroupid = $variableApi->get('ZikulaGroupsModule', 'primaryadmingroup', 0);
        if ($group['gid'] == $primaryadmingroupid) {
            throw new \RuntimeException($translator->__('Sorry! You cannot delete the primary administrators group.'));
        }

        // Delete the group
        $group->removeAllUsers();
        // @todo Is there any reason why we don't delete group applications?
        $entityManager->remove($group);
        // this could be quite memory intensive for large groups managing large collections.
        $entityManager->flush();

        // Remove any group permissions for this group
        $permissionsRepository = $entityManager->getRepository('ZikulaPermissionsModule:PermissionEntity');
        $permissionsRepository->deleteGroupPermissions($args['gid']);

        // Let other modules know that we have deleted a group.
        $deleteEvent = new GenericEvent($group->toArray());
        ServiceUtil::get('event_dispatcher')->dispatch('group.delete', $deleteEvent);

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
     * @return bool true if successful, false on failure
     *
     * @throws \InvalidArgumentException Thrown if either the gid or name parameters are not provided
     * @throws AccessDeniedException Thrown if the current user does not have edit access to the group
     */
    public function update($args)
    {
        $translator = ServiceUtil::get('translator.default');

        // Argument check
        if (!isset($args['gid']) || !isset($args['name'])) {
            throw new \InvalidArgumentException($translator->__('Invalid arguments array received'));
        }

        // Security check
        $permissionApi = ServiceUtil::get('zikula_permissions_module.api.permission');
        if (!$permissionApi->hasPermission('ZikulaGroupsModule::', $args['gid'] . '::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        // get item
        $entityManager = ServiceUtil::get('doctrine')->getManager();
        $item = $entityManager->find('ZikulaGroupsModule:GroupEntity', $args['gid']);
        if (!$item) {
            return false;
        }

        // Other check
        $checkname = $this->getgidbyname([
            'name' => $args['name'],
            'checkgid' => $args['gid']
        ]);
        if (false !== $checkname) {
            throw new \RuntimeException($translator->__('Error! There is already a group with that name.'));
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
        $entityManager->flush();

        // Let other modules know that we have updated a group.
        $updateEvent = new GenericEvent($item);
        ServiceUtil::get('event_dispatcher')->dispatch('group.update', $updateEvent);

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
     * @return bool true if successful, false on failure
     *
     * @throws \InvalidArgumentException Thrown if either gid or uid are not set or not numeric
     * @throws AccessDeniedException Thrown if the current user does not have edit access to the group
     */
    public function adduser($args)
    {
        // Argument check
        if (!isset($args['gid']) || !isset($args['uid'])) {
            $translator = ServiceUtil::get('translator.default');
            throw new \InvalidArgumentException($translator->__('Invalid arguments array received'));
        }

        // Security check
        $permissionApi = ServiceUtil::get('zikula_permissions_module.api.permission');
        if (!$permissionApi->hasPermission('ZikulaGroupsModule::', $args['gid'] . '::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        // get group
        $group = ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'get', [
            'gid' => $args['gid'],
            'group_membership' => false
        ]);
        if (!$group) {
            return false;
        }

        $entityManager = ServiceUtil::get('doctrine')->getManager();

        $user = $entityManager->find('ZikulaUsersModule:UserEntity', $args['uid']);
        // Add user to group
        $user->addGroup($entityManager->getReference('ZikulaGroupsModule:GroupEntity', $args['gid']));
        $entityManager->flush();

        // Let other modules know that we have updated a group.
        $adduserEvent = new GenericEvent(['gid' => $args['gid'], 'uid' => $args['uid']]);
        ServiceUtil::get('event_dispatcher')->dispatch('group.adduser', $adduserEvent);

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
     * @return bool true if successful, false on failure
     *
     * @throws \InvalidArgumentException Thrown if either gid or uid are not set or not numeric
     * @throws AccessDeniedException Thrown if the current user does not have edit access to the group
     */
    public function removeuser($args)
    {
        // Argument check
        if (!isset($args['gid']) || !isset($args['uid'])) {
            $translator = ServiceUtil::get('translator.default');
            throw new \InvalidArgumentException($translator->__('Invalid arguments array received'));
        }

        // Security check
        $permissionApi = ServiceUtil::get('zikula_permissions_module.api.permission');
        if (!$permissionApi->hasPermission('ZikulaGroupsModule::', $args['gid'] . '::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        // get group
        $entityManager = ServiceUtil::get('doctrine')->getManager();
        $group = $entityManager->find('ZikulaGroupsModule:GroupEntity', $args['gid']);
        if (!$group) {
            return false;
        }

        $user = $entityManager->find('ZikulaUsersModule:UserEntity', $args['uid']);
        // delete user from group
        $user->removeGroup($group);
        $entityManager->flush();

        // Let other modules know we have updated a group
        $removeuserEvent = new GenericEvent(['gid' => $args['gid'], 'uid' => $args['uid']]);
        ServiceUtil::get('event_dispatcher')->dispatch('group.removeuser', $removeuserEvent);

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
     * @return int|bool item, or false on failure
     *
     * @throws \InvalidArgumentException Thrown if the name parameter isn't provided
     */
    public function getgidbyname($args)
    {
        // Argument check
        if (!isset($args['name'])) {
            $translator = ServiceUtil::get('translator.default');
            throw new \InvalidArgumentException($translator->__('Invalid arguments array received'));
        }

        $excludedGroupId = isset($args['checkgid']) && is_numeric($args['checkgid']) ? $args['checkgid'] : 0;

        $entityManager = ServiceUtil::get('doctrine')->getManager();
        $groupRepository = $entityManager->getRepository('ZikulaGroupsModule:GroupEntity');
        $result = $groupRepository->getGroupByName($args['name'], $excludedGroupId);

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
     * @return string|bool item, or false on failure
     *
     * @throws \InvalidArgumentException Thrown if the id parameter isn't provided
     */
    public function getnamebygid($args)
    {
        // Argument check
        if (!isset($args['gid'])) {
            $translator = ServiceUtil::get('translator.default');
            throw new \InvalidArgumentException($translator->__('Error! Invalid arguments array received.'));
        }

        $entityManager = ServiceUtil::get('doctrine')->getManager();
        $group = $entityManager->find('ZikulaGroupsModule:GroupEntity', $args['gid']);
        if (!$group) {
            return false;
        }

        return $group['name'];
    }

    /**
     * Get applications.
     *
     * @return array|bool array of group applications or false if no group applications are found
     */
    public function getapplications()
    {
        $entityManager = ServiceUtil::get('doctrine')->getManager();
        $objArray = $entityManager->getRepository('ZikulaGroupsModule:GroupApplicationEntity')
            ->findBy([], ['app_id' => 'ASC']);

        if (false === $objArray) {
            return false;
        }

        $permissionApi = ServiceUtil::get('zikula_permissions_module.api.permission');
        $items = [];

        foreach ($objArray as $obj) {
            $group = ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'get', ['gid' => $obj['gid'], 'group_membership' => false]);
            if (!$group) {
                continue;
            }

            if ($permissionApi->hasPermission('ZikulaGroupsModule::', $group['gid'] . '::', ACCESS_EDIT)) {
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
     * @return array|bool False if no application is found
     *
     * @throws \InvalidArgumentException Thrown if invalid parameters are received in $args
     */
    public function getapplicationinfo($args)
    {
        if (!isset($args['gid']) || !isset($args['userid'])) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        $entityManager = ServiceUtil::get('doctrine')->getManager();
        $appInfo = $entityManager->getRepository('ZikulaGroupsModule:GroupApplicationEntity')
            ->findOneBy(['gid' => $args['gid'], 'uid' => $args['userid']]);

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

        $entityManager = ServiceUtil::get('doctrine')->getManager();

        // delete group application
        $application = $entityManager->getRepository('ZikulaGroupsModule:GroupApplicationEntity')->findOneBy([
            'gid' => $args['gid'],
            'uid' => $args['userid']
        ]);
        $entityManager->remove($application);
        $entityManager->flush();

        if ($args['action'] == 'accept') {
            $adduser = $this->adduser([
                'gid' => $args['gid'],
                'uid' => $args['userid']
            ]);
        }

        // Send message part
        switch ($args['sendtag']) {
            case 1:
                $variableApi = ServiceUtil::get('zikula_extensions_module.api.variable');
                $siteName = $variableApi->getSystemVar('sitename', $variableApi->getSystemVar('sitename_en'));
                $adminMail = $variableApi->getSystemVar('adminmail');

                // create new message instance
                /** @var Swift_Message */
                $message = Swift_Message::newInstance();

                $message->setFrom([$adminMail => $siteName]);
                $message->setTo([UserUtil::getVar('email', $args['userid']) => UserUtil::getVar('uname', $args['userid'])]);

                $mailer = ServiceUtil::get('zikula_mailer_module.api.mailer');
                $send = $mailer->sendMessage($message, $args['reasontitle'], $args['reason']);
                break;
            default:
                $send = true;
        }

        return $send;
    }

    /**
     * Utility function to count the number of items held by this module.
     *
     * @return int number of items held by this module
     */
    public function countitems()
    {
        $entityManager = ServiceUtil::get('doctrine')->getManager();

        return $entityManager->getRepository('ZikulaGroupsModule:GroupEntity')->countGroups();
    }
}
