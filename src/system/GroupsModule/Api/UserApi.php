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

use ServiceUtil;
use UserUtil;
use Swift_Message;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula;
use Zikula\Core\Event\GenericEvent;
use Zikula\GroupsModule\Helper\CommonHelper;
use Zikula\GroupsModule\Entity\GroupApplicationEntity;
use Zikula\UsersModule\Entity\UserEntity;

/**
 * User API functions for the groups module
 *
 * @deprecated remove at Core-2.0
 */
class UserApi
{
    /**
     * Get all group items.
     *
     * @param int[] $args {
     *      @type int $startnum record number to start get from
     *      @type int $numitems  number of items to get
     *                     }
     *
     * @return array|bool array of group items, false if none are found
     */
    public function getall($args)
    {
        $items = [];

        // Security check
        $permissionApi = ServiceUtil::get('zikula_permissions_module.api.permission');
        if (!$permissionApi->hasPermission('ZikulaGroupsModule::', '::', ACCESS_READ)) {
            return $items;
        }

        $amountOfItems = (!isset($args['numitems']) || !is_numeric($args['numitems'])) ? 0 : (int)$args['numitems'];
        $startOffset = (!isset($args['startnum']) || !is_numeric($args['startnum'])) ? 0 : (int)$args['startnum'];

        $entityManager = ServiceUtil::get('doctrine')->getManager();

        return $entityManager->getRepository('ZikulaGroupsModule:GroupEntity')->getGroups([], [], ['name' => 'ASC'], $amountOfItems, $startOffset);
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
     * @return array|bool item array, or false on failure
     *
     * @throws \InvalidArgumentException Thrown if the gid parameter isn't provided
     */
    public function get($args)
    {
        // Argument check
        if (!isset($args['gid'])) {
            $translator = ServiceUtil::get('translator.default');
            throw new \InvalidArgumentException($translator->__('Invalid arguments array received'));
        }

        // Security check
        $permissionApi = ServiceUtil::get('zikula_permissions_module.api.permission');
        if (!$permissionApi->hasPermission('ZikulaGroupsModule::', $args['gid'] . '::', ACCESS_READ)) {
            return false;
        }

        // get item
        $entityManager = ServiceUtil::get('doctrine')->getManager();
        $group = $entityManager->find('ZikulaGroupsModule:GroupEntity', $args['gid']);
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

        $uidsArray = [];
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

        // Create the item array
        $result['nbuser'] = count($uidsArray);
        $result['members'] = $uidsArray;

        if (!is_null($args['uid'])) {
            $result['status'] = $this->isuserpending([
                'gid' => $args['gid'],
                'uid' => $args['uid']
            ]);
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
        $variableApi = ServiceUtil::get('zikula_extensions_module.api.variable');
        $entityManager = ServiceUtil::get('doctrine')->getManager();

        $excludedState = null;
        if ($variableApi->get('ZikulaGroupsModule', 'hideclosed')) {
            $excludedState = CommonHelper::STATE_CLOSED;
        }

        return $entityManager->getRepository('ZikulaGroupsModule:GroupEntity')->countGroups(CommonHelper::GTYPE_CORE, $excludedState);
    }

    /**
     * Utility function to count the number of items held by this module.
     *
     * @param int[] $args {
     *      @type int $gid id of group item to get
     *                     }
     *
     * @return int number of items held by this module
     *
     * @throws \InvalidArgumentException Thrown if the gid parameter isn't provided or isn't numeric
     */
    public function countgroupmembers($args)
    {
        // Argument check
        if ((!isset($args['gid']) && !is_numeric($args['gid']))) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        $entityManager = ServiceUtil::get('doctrine')->getManager();
        $group = $entityManager->find('ZikulaGroupsModule:GroupEntity', $args['gid']);

        if (null === $group) {
            return 0;
        }

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
     * @return array|bool array of group items, false if no group memberships are found for the input user id
     *
     * @throws \InvalidArgumentException Thrown if the gid parameter isn't provided or isn't numeric
     */
    public function getusergroups($args)
    {
        // Optional arguments.
        if (!isset($args['uid'])) {
            $args['uid'] = UserUtil::getVar('uid');
        }
        if (!is_numeric($args['uid'])) {
            $translator = ServiceUtil::get('translator.default');
            throw new \InvalidArgumentException($translator->__('Invalid arguments array received'));
        }

        // Security check
        $permissionApi = ServiceUtil::get('zikula_permissions_module.api.permission');
        if (!$permissionApi->hasPermission('ZikulaGroupsModule::', '::', ACCESS_READ)) {
            return [];
        }

        $entityManager = ServiceUtil::get('doctrine')->getManager();
        $userGroups = $entityManager->find('ZikulaUsersModule:UserEntity', $args['uid'])->getGroups();

        $groupsArray = [];
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
     * @return array|bool array of groups, false if no groups are found
     */
    public function getallgroups($args)
    {
        $items = [];

        $permissionApi = ServiceUtil::get('zikula_permissions_module.api.permission');
        if (!$permissionApi->hasPermission('ZikulaGroupsModule::', 'ANY', ACCESS_OVERVIEW)) {
            return $items;
        }

        $amountOfItems = (!isset($args['numitems']) || !is_numeric($args['numitems'])) ? 0 : (int)$args['numitems'];
        $startOffset = (!isset($args['startnum']) || !is_numeric($args['startnum'])) ? 0 : (int)$args['startnum'];

        $variableApi = ServiceUtil::get('zikula_extensions_module.api.variable');
        $exclusions = [
            'gtype' => CommonHelper::GTYPE_CORE
        ];
        if ($variableApi->get('ZikulaGroupsModule', 'hideclosed')) {
            $exclusions['state'] = CommonHelper::STATE_CLOSED;
        }

        $entityManager = ServiceUtil::get('doctrine')->getManager();
        $groups = $entityManager->getRepository('ZikulaGroupsModule:GroupEntity')->getGroups([], $exclusions, ['name' => 'ASC'], $amountOfItems, $startOffset);
        if (false === $groups) {
            return false;
        }

        $uid = UserUtil::getVar('uid');

        $memberships = false;
        if (0 != $uid) {
            $memberships = $this->getusergroups([
                'uid' => $uid,
                'clean' => true
            ]);
        }

        $row = 1;
        $permissionApi = ServiceUtil::get('zikula_permissions_module.api.permission');

        foreach ($groups as $obj) {
            $obj = $obj->toArray();
            $gid = $obj['gid'];

            if (!$permissionApi->hasPermission('ZikulaGroupsModule::', $gid . '::', ACCESS_OVERVIEW)) {
                continue;
            }

            $name = $obj['name'];
            $gtype = $obj['gtype'];
            $description = $obj['description'];
            $state = $obj['state'];
            $nbumax = $obj['nbumax'];

            if (!isset($gtype) || is_null($gtype)) {
                $gtype = CommonHelper::GTYPE_CORE;
            }
            if (is_null($state)) {
                $state = CommonHelper::STATE_CLOSED;
            }

            $ismember = is_array($memberships) && in_array($gid, $memberships) ? true : false;

            $status = false;
            if (0 != $uid) {
                $status = $this->isuserpending([
                    'gid' => $gid,
                    'uid' => $uid
                ]);
            }

            $nbuser = $this->countgroupmembers(['gid' => $gid]);

            $canview = false;
            $canapply = false;
            if ($permissionApi->hasPermission('ZikulaGroupsModule::', $gid . '::', ACCESS_READ)) {
                $canview = true;
                $canapply = true;
            }

            // Anon users or non-members should not be able to see private groups.
            if (CommonHelper::GTYPE_PRIVATE == $gtype) {
                if (!$uid || !$this->isgroupmember([
                    'uid' => $uid,
                    'gid' => $gid
                ])) {
                    continue;
                }
            }

            $items[] = [
                'gid' => $gid,
                'name' => $name,
                'gtype' => $gtype,
                'description' => $description,
                'state' => $state,
                'nbuser' => ((false != $nbuser) ? $nbuser : 0),
                'nbumax' => $nbumax,
                'ismember' => $ismember,
                'status' => $status,
                'canview' => $canview,
                'canapply' => $canapply,
                'islogged' => UserUtil::isLoggedIn(),
                'row' => $row
            ];

            if (1 == $row) {
                $row = 2;
            } else {
                $row = 1;
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
     * @return bool true if successful, false if the group isn't found
     *
     * @throws \InvalidArgumentException Thrown if either gid or uid are not set or not numeric
     * @throws AccessDeniedException Thrown if the current user does not have read access to the group
     * @throws \RuntimeException Thrown if the user has already applied for this group
     */
    public function saveapplication($args)
    {
        $translator = ServiceUtil::get('translator.default');
        if ((!isset($args['gid']) && !is_numeric($args['gid'])) ||
            (!isset($args['uid']) && !is_numeric($args['uid']))) {
            throw new \InvalidArgumentException($translator->__('Invalid arguments array received'));
        }

        $permissionApi = ServiceUtil::get('zikula_permissions_module.api.permission');
        if (!$permissionApi->hasPermission('ZikulaGroupsModule::', $args['gid'] . '::', ACCESS_READ)) {
            throw new AccessDeniedException();
        }

        $item = $this->get([
            'gid' => $args['gid'],
            'group_membership' => false
        ]);
        if (!$item) {
            return false;
        }

        // Check in case the user already applied
        $pending = $this->isuserpending([
            'gid' => $args['gid'],
            'uid' => $args['uid']
        ]);
        if ($pending) {
            throw new \RuntimeException($translator->__('Error! You have already applied for membership of this group.'));
        }

        $application = new GroupApplicationEntity();
        $application['uid'] = $args['uid'];
        $application['gid'] = $args['gid'];
        $application['application'] = $args['applytext'];
        $application['status'] = 1;

        $entityManager = ServiceUtil::get('doctrine')->getManager();
        $entityManager->persist($application);
        $entityManager->flush();

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
            $translator = ServiceUtil::get('translator.default');
            throw new \InvalidArgumentException($translator->__('Invalid arguments array received'));
        }

        $appArgs = [
            'gid' => $args['gid'],
            'uid' => $args['uid']
        ];

        // Checking first if this user is really pending.
        $isPending = $this->isuserpending($appArgs);
        if (true === $isPending) {
            $entityManager = ServiceUtil::get('doctrine')->getManager();
            $application = $entityManager->getRepository('ZikulaGroupsModule:GroupApplicationEntity')->findOneBy($appArgs);
            $entityManager->remove($application);
            $entityManager->flush();
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
            $translator = ServiceUtil::get('translator.default');
            throw new \InvalidArgumentException($translator->__('Invalid arguments array received'));
        }

        $entityManager = ServiceUtil::get('doctrine')->getManager();
        $applications = $entityManager->getRepository('ZikulaGroupsModule:GroupApplicationEntity')
            ->findBy(['group' => $args['gid'], 'user' => $args['uid']]);

        return count($applications) > 0;
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
     * @throws AccessDeniedException Thrown if the user is not logged in
     * @throws \RuntimeException Thrown if the user couldn't be added to the group,
     *                                  if the application to the group couldn't be cancelled, or
     *                                  if the user couldn't be removed from the group
     */
    public function userupdate($args)
    {
        $translator = ServiceUtil::get('translator.default');
        if (!isset($args['gtype']) && !is_numeric($args['gtype']) ||
            (!isset($args['gid']) && !is_numeric($args['gid'])) ||
            !isset($args['action'])) {
            throw new \InvalidArgumentException($translator->__('Invalid arguments array received'));
        }

        if (!in_array($args['action'], ['subscribe', 'unsubscribe', 'cancel'])) {
            throw new \InvalidArgumentException($translator->__('Invalid arguments array received'));
        }

        if (!UserUtil::isLoggedIn()) {
            throw new AccessDeniedException($translator->__('Error! You must register for a user account on this site before you can apply for membership of a group.'));
        }

        $userid = UserUtil::getVar('uid');

        if ('subscribe' == $args['action']) {
            if (CommonHelper::GTYPE_PRIVATE == $args['gtype']) {
                if (!isset($args['applytext'])) {
                    throw new \InvalidArgumentException($translator->__('Invalid arguments array received'));
                }

                // We save the user in the application table
                $save = $this->saveapplication([
                    'gid' => $args['gid'],
                    'uid' => $userid,
                    'applytext' => $args['applytext']
                ]);
                if (false === $save) {
                    return false;
                }

                $variableApi = ServiceUtil::get('zikula_extensions_module.api.variable');
                if ($variableApi->get('ZikulaGroupsModule', 'mailwarning')) {
                    $siteName = $variableApi->getSystemVar('sitename', $variableApi->getSystemVar('sitename_en'));
                    $adminMail = $variableApi->getSystemVar('adminmail');
                    $uname = UserUtil::getVar('uname', $userid);

                    // create new message instance
                    /** @var Swift_Message */
                    $message = Swift_Message::newInstance();

                    $message->setFrom([$adminMail => $siteName]);
                    $message->setTo([$adminMail => $translator->__('Administrator')]);

                    $subject = $translator->__('Group membership application registered');
                    $body = $translator->__f('The registered user %1$s has applied for membership of a group. The details of the application are as follows: %2$s', ['%1$s' => $uname, '%2$s' => $args['applytext']]);

                    $mailer = ServiceUtil::get('zikula_mailer_module.api.mailer');
                    $send = $mailer->sendMessage($message, $subject, $body);
                }
            } else {
                // We save the user into the groups
                $save = $this->adduser(['gid' => $args['gid'], 'uid' => $userid]);
                if (false === $save) {
                    throw new \RuntimeException($translator->__('Error! Could not add the user to the group.'));
                }
            }
        } elseif ('cancel' == $args['action']) {
            $save = $this->cancelapp(['gid' => $args['gid'], 'uid' => $userid]);
            if (false === $save) {
                throw new \RuntimeException($translator->__('Error! Could not remove the user from the group.'));
            }
        } else {
            $save = $this->removeuser(['gid' => $args['gid'], 'uid' => $userid]);
            if (false === $save) {
                throw new \RuntimeException($translator->__('Error! Could not remove the user from the group.'));
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
     * @return bool true if successful, false otherwise
     *
     * @throws \InvalidArgumentException Thrown if either gid or uid are not set or not numeric
     * @throws AccessDeniedException Thrown if the current user does not have read access to the group
     */
    public function adduser($args)
    {
        $translator = ServiceUtil::get('translator.default');
        // Argument check
        if ((!isset($args['gid']) && !is_numeric($args['gid'])) ||
            (!isset($args['uid']) && !is_numeric($args['uid']))) {
            throw new \InvalidArgumentException($translator->__('Invalid arguments array received'));
        }

        // Security check
        $permissionApi = ServiceUtil::get('zikula_permissions_module.api.permission');
        if (!$permissionApi->hasPermission('ZikulaGroupsModule::', $args['gid'] . '::', ACCESS_READ)) {
            throw new AccessDeniedException();
        }

        // get group
        $entityManager = ServiceUtil::get('doctrine')->getManager();
        $group = $entityManager->find('ZikulaGroupsModule:GroupEntity', $args['gid']);
        if (!$group) {
            return false;
        }

        // verify if the user is alredy a member of this group
        $user = $entityManager->find('ZikulaUsersModule:UserEntity', $args['uid']);
        $isMember = $group->getUsers()->contains($user);

        // Add item
        if (!$isMember) {
            $user->addGroup($group);
            $entityManager->flush();

            // Let other modules know that we have updated a group.
            $adduserEvent = new GenericEvent(['gid' => $args['gid'], 'uid' => $args['uid']]);
            ServiceUtil::get('event_dispatcher')->dispatch('group.adduser', $adduserEvent);
        } else {
            if (isset($args['verbose']) && !$args['verbose']) {
                return false;
            }

            throw new \RuntimeException($translator->__('Error! You are already a member of this group.'));
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
     * @return bool true if successful, false otherwise
     *
     * @throws \InvalidArgumentException Thrown if either gid or uid are not set or not numeric
     * @throws AccessDeniedException Thrown if the current user does not have read access tp the group
     */
    public function removeuser($args)
    {
        if ((!isset($args['gid']) && !is_numeric($args['gid'])) ||
            (!isset($args['uid']) && !is_numeric($args['uid']))) {
            $translator = ServiceUtil::get('translator.default');
            throw new \InvalidArgumentException($translator->__('Invalid arguments array received'));
        }

        // Security check
        $permissionApi = ServiceUtil::get('zikula_permissions_module.api.permission');
        if (!$permissionApi->hasPermission('ZikulaGroupsModule::', $args['gid'] . '::', ACCESS_READ)) {
            throw new AccessDeniedException();
        }

        // get group
        $entityManager = ServiceUtil::get('doctrine')->getManager();
        $group = $entityManager->find('ZikulaGroupsModule:GroupEntity', $args['gid']);
        if (!$group) {
            return false;
        }

        // delete user from group
        $user = $entityManager->find('ZikulaUsersModule:UserEntity', $args['uid']);
        $user->removeGroup($group);
        $entityManager->flush();

        // Let other modules know we have updated a group
        $removeuserEvent = new GenericEvent(null, ['gid' => $args['gid'], 'uid' => $args['uid']]);
        ServiceUtil::get('event_dispatcher')->dispatch('group.removeuser', $removeuserEvent);

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
        $variableApi = ServiceUtil::get('zikula_extensions_module.api.variable');
        $inactiveLimit = $variableApi->getSystemVar('secinactivemins');
        $dateTime = new \DateTime();
        $dateTime->modify('-' . $inactiveLimit . 'minutes');

        $entityManager = ServiceUtil::get('doctrine')->getManager();

        return $entityManager->getRepository('ZikulaUsersModule:UserSessionEntity')->getUsersSince($dateTime);
    }

    /**
     * Check if a user is a member of a group.
     *
     * @param int[] $args {
     *      @type int $uid user id
     *      @type int $gid group id
     *                     }
     *
     * @return boolean true if member of a group, false otherwise
     *
     * @throws \InvalidArgumentException Thrown if either gid or uid are not set or not numeric
     */
    public function isgroupmember($args)
    {
        if ((!isset($args['uid']) && !is_numeric($args['uid'])) ||
            (!isset($args['gid']) && !is_numeric($args['gid']))) {
            $translator = ServiceUtil::get('translator.default');
            throw new \InvalidArgumentException($translator->__('Invalid arguments array received'));
        }

        // Security check
        $permissionApi = ServiceUtil::get('zikula_permissions_module.api.permission');
        if (!$permissionApi->hasPermission('ZikulaGroupsModule::', '::', ACCESS_READ)) {
            return false;
        }

        // Get group and check if the user exists in this group.
        $group = $this->get([
            'gid' => $args['gid'],
            'group_membership' => true,
            'uid' => $args['uid']
        ]);

        if (!$group || !array_key_exists($args['uid'], $group['members'])) {
            // either group does not exist or the requested uid is not a member of the group
            return false;
        }

        // report the user is a member of the group
        return true;
    }
}
