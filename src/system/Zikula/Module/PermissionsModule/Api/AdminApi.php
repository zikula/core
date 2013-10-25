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

namespace Zikula\Module\PermissionsModule\Api;

use LogUtil;
use SecurityUtil;
use DataUtil;
use BlockUtil;
use ModUtil;
use Zikula\Module\PermissionsModule\Entity\PermissionEntity;

/**
 * Permissions_Api_Admin class.
 */
class AdminApi extends \Zikula_AbstractApi
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
        if (!SecurityUtil::checkPermission('ZikulaPermissionsModule::', "group::$args[pid]", ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        // Argument check
        if (!isset($args['pid']) || is_numeric($args['pid'])) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        // get info on current perm
        $permission = $this->entityManager->find('Zikula\Module\PermissionsModule\Entity\PermissionEntity', $args['pid']);
        if (!$permission) {
            return LogUtil::registerError($this->__f('Error! Permission rule ID %s does not exist.', $args['pid']));
        }

        $sequence = $permission['sequence'];

        if ($sequence != 1) {
            $altsequence = $sequence - 1;

            // get info on displaced perm
            $qb = $this->entityManager->createQueryBuilder()
                                      ->select('p')
                                      ->from('Zikula\Module\PermissionsModule\Entity\PermissionEntity', 'p')
                                      ->where('p.sequence = :altsequence')
                                      ->setParameter('altsequence', $altsequence);

            if (!is_null($args['permgrp']) && ($args['permgrp'] != SecurityUtil::PERMS_ALL)) {
                $qb->andWhere('(p.gid = :permsall OR p.gid = :permgrp)')
                   ->setParameter('permsall', SecurityUtil::PERMS_ALL)
                   ->setParameter('permgrp', $args['permgrp']);
                $showpartly = true;
            } else {
                $showpartly = false;
            }

            $d_permission = $qb->getQuery()
                               ->getOneOrNullResult();

            if (!$d_permission) {
                if ($showpartly) {
                    // Changing the sequence by moving while in partial view may only be done if there
                    // are no invisible permissions inbetween that might be affected by the move.
                    LogUtil::registerError($this->__('Error! Permission rule-swapping in partial view can only be done if both affected permission rules are visible. Please switch to full view.'));
                } else {
                    LogUtil::registerError($this->__('Error! No permission rule directly above that one.'));
                }

                return false;
            }

            $altpid = $d_permission['pid'];

            // swap sequence numbers
            $perm1 = $this->entityManager->find('Zikula\Module\PermissionsModule\Entity\Permission', $altpid);
            $perm1['sequence'] = $sequence;

            $perm2 = $this->entityManager->find('Zikula\Module\PermissionsModule\Entity\Permission', $args['pid']);
            $perm2['sequence'] = $altsequence;

            $this->entityManager->flush();
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
        if (!SecurityUtil::checkPermission('ZikulaPermissionsModule::', "group::$args[pid]", ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        // Argument check
        if (!isset($args['pid']) || is_numeric($args['pid'])) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        // get info on current perm
        $permission = $this->entityManager->find('Zikula\Module\PermissionsModule\Entity\PermissionEntity', $args['pid']);
        if (!$permission) {
            return LogUtil::registerError($this->__f('Error! Permission rule ID %s does not exist.', $args['pid']));
        }

        $sequence = $permission['sequence'];

        $maxsequence = $this->maxsequence();
        if ($sequence != $maxsequence) {
            $altsequence = $sequence + 1;

            // get info on displaced perm
            $qb = $this->entityManager->createQueryBuilder()
                                      ->select('p')
                                      ->from('Zikula\Module\PermissionsModule\Entity\PermissionEntity', 'p')
                                      ->where('p.sequence = :altsequence')
                                      ->setParameter('altsequence', $altsequence);

            if (!is_null($args['permgrp']) && ($args['permgrp'] != SecurityUtil::PERMS_ALL)) {
                $qb->andWhere('(p.gid = :permsall OR p.gid = :permgrp)')
                   ->setParameter('permsall', SecurityUtil::PERMS_ALL)
                   ->setParameter('permgrp', $args['permgrp']);
                $showpartly = true;
            } else {
                $showpartly = false;
            }

            $d_permission = $qb->getQuery()
                               ->getOneOrNullResult();

            if (!$d_permission) {
                if ($showpartly) {
                    // Changing the sequence by moving while in partial view may only be done if there
                    // are no invisible permissions inbetween that might be affected by the move.
                    LogUtil::registerError($this->__('Error! Permission rule-swapping in partial view can only be done if both affected permission rules are visible. Please switch to full view.'));
                } else {
                    LogUtil::registerError($this->__('Error! No permission rule directly below that one.'));
                }

                return false;
            }

            $altpid = $d_permission['pid'];

            // swap sequence numbers
            $perm1 = $this->entityManager->find('Zikula\Module\PermissionsModule\Entity\PermissionEntity', $altpid);
            $perm1['sequence'] = $sequence;

            $perm2 = $this->entityManager->find('Zikula\Module\PermissionsModule\Entity\PermissionEntity', $args['pid']);
            $perm2['sequence'] = $altsequence;

            $this->entityManager->flush();
        }

        return true;
    }

    /**
     * Update attributes of a permission.
     *
     * @param int    $args ['pid'] the ID of the permission to update.
     * @param int    $args ['seq'] the order number of the permission.
     * @param int    $args ['oldseq'] the old order number of the permission.
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
        if (!SecurityUtil::checkPermission('ZikulaPermissionsModule::', "group::$args[pid]", ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        // Argument check
        if ((!isset($args['pid']) || !is_numeric($args['pid'])) ||
                (!isset($args['seq']) || !is_numeric($args['seq'])) ||
                (!isset($args['oldseq']) || !is_numeric($args['oldseq'])) ||
                (!isset($args['realm'])) ||
                (!isset($args['id']) || !is_numeric($args['id'])) ||
                (!isset($args['component'])) ||
                (!isset($args['instance'])) ||
                (!isset($args['level']) || !is_numeric($args['level']))) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        // get and update permission
        $permission = $this->entityManager->find('Zikula\Module\PermissionsModule\Entity\PermissionEntity', $args['pid']);
        $permission['gid'] = $args['id'];
        $permission['realm'] = $args['realm'];
        $permission['component'] = $args['component'];
        $permission['instance'] = $args['instance'];
        $permission['level'] = $args['level'];

        $this->entityManager->flush();

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
        if (!SecurityUtil::checkPermission('ZikulaPermissionsModule::', "group::$args[id]", ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        // Argument check
        if ((!isset($args['realm'])) ||
                (!isset($args['id']) || !is_numeric($args['id'])) ||
                (!isset($args['component'])) ||
                (!isset($args['instance'])) ||
                (!isset($args['level']) || !is_numeric($args['level'])) ||
                (!isset($args['insseq']) || !is_numeric($args['insseq']))) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        // Insert Capability
        if ($args['insseq'] == -1) {
            $maxseq = $this->maxsequence();
            $newseq = $maxseq + 1;
        } else {
            // Increase sequence numbers
            $query = $this->entityManager->createQueryBuilder()
                                         ->update('Zikula\Module\PermissionsModule\Entity\PermissionEntity', 'p')
                                         ->set('p.sequence = p.sequence + 1')
                                         ->where('p.sequence >= :insseq')
                                         ->setParameter('insseq', $args['insseq'])
                                         ->getQuery();
            $result = $query->getResult();

            if (!$result) {
                return LogUtil::registerError($this->__('Error! Could not save permission rule sequences.'));
            }

            $newseq = $args['insseq'];
        }

        $obj = new PermissionEntity;
        $obj['gid'] = (int)$args['id'];
        $obj['sequence'] = $newseq;
        $obj['realm'] = (int)$args['realm'];
        $obj['component'] = $args['component'];
        $obj['instance'] = $args['instance'];
        $obj['level'] = (int)$args['level'];

        $this->entityManager->persist($obj);
        $this->entityManager->flush();

        // Clean-up
        $this->resequence();

        return $obj->toArray();
    }

    /**
     * Delete a perm.
     *
     * @param int    $args ['pid'] the ID of the permission to delete.
     *
     * @return boolean true on success, false on failure.
     */
    public function delete($args)
    {
        // Security check
        if (!SecurityUtil::checkPermission('ZikulaPermissionsModule::', "group::$args[pid]", ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        // Argument check
        if (!isset($args['pid']) || !is_numeric($args['pid'])) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        // get and delete permission
        $permission = $this->entityManager->find('Zikula\Module\PermissionsModule\Entity\PermissionEntity', $args['pid']);
        $this->entityManager->remove($permission);
        $this->entityManager->flush();

        $this->resequence();

        return true;
    }

    /**
     * Get the maximum sequence number in permissions table.
     *
     * @return int the maximum sequence number.
     */
    public function maxsequence()
    {
        // Security check
        if (!SecurityUtil::checkPermission('ZikulaPermissionsModule::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        $qb = $this->entityManager->createQueryBuilder();
        $query = $qb->select($qb->expr()->max('p.sequence'))
                    ->from('Zikula\Module\PermissionsModule\Entity\PermissionEntity', 'p')
                    ->getQuery();

        return (int)$query->getSingleScalarResult();;
    }

    /**
     * Resequence a permissions table.
     *
     * @return boolean
     */
    public function resequence()
    {
        // Security check
        if (!SecurityUtil::checkPermission('ZikulaPermissionsModule::', 'group::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        // get all permissions
        $permissions = $this->entityManager->getRepository('Zikula\Module\PermissionsModule\Entity\PermissionEntity')->findBy(array(), array('sequence' => 'ASC'));
        if (!$permissions) {
            return false;
        }

        // fix sequence numbers
        $sequence = 1;

        foreach ($permissions as $permission) {
            $curseq = $permission['sequence'];
            if ($curseq != $sequence) {
                $permission['sequence'] = $sequence;
            }
            $sequence++;
        }

        $this->entityManager->flush();

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
        if (!SecurityUtil::checkPermission('ZikulaPermissionsModule::', "::", ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        // Argument check
        if (!isset($args['newseq']) || !isset($args['oldseq'])) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        $newseq = $args['newseq'];
        $oldseq = $args['oldseq'];
        unset($args);

        //find out the maximum sequence number
        $maxseq = $this->maxsequence();

        // The new sequence is higher in the list
        if ((int)$oldseq > (int)$newseq) {
            if ($newseq < 1) {
                $newseq = 1;
            }

            $query = $this->entityManager->createQueryBuilder()
                                         ->select('p')
                                         ->from('Zikula\Module\PermissionsModule\Entity\PermissionEntity', 'p')
                                         ->where('p.sequence >= :newseq')
                                         ->andWhere('p.sequence <= :oldseq')
                                         ->setParameter('newseq', $newseq)
                                         ->setParameter('oldseq', $oldseq)
                                         ->orderBy('p.sequence', 'DESC')
                                         ->getQuery();

            $permissions = $query->getResult();

            foreach ($permissions as $permission) {
                $curseq = $permission['sequence'];

                if ($curseq == $oldseq) {
                    // we are dealing with the old value so make it the new value
                    $curseq = $newseq;
                } else {
                    $curseq++;
                }

                $permission['sequence'] = (int)$curseq;
            }
        } else {
            // The new sequence is lower in the list
            // if the new requested sequence is bigger than
            // the maximum sequence number then set it to
            // the maximum number.  We don't want any spaces
            // in the sequence.
            if ($newseq > $maxseq) {
                $newseq = (int)$maxseq;
            }

            $query = $this->entityManager->createQueryBuilder()
                                         ->select('p')
                                         ->from('Zikula\Module\PermissionsModule\Entity\PermissionEntity', 'p')
                                         ->where('p.sequence >= :oldseq')
                                         ->andWhere('p.sequence <= :newseq')
                                         ->setParameter('oldseq', $oldseq)
                                         ->setParameter('newseq', $newseq)
                                         ->orderBy('p.sequence', 'ASC')
                                         ->getQuery();

            $permissions = $query->getResult();

            foreach ($permissions as $permission) {
                $curseq = $permission['sequence'];

                if ($curseq == $oldseq) {
                    // we are dealing with the old value so make it the new value
                    $curseq = $newseq;
                } else {
                    $curseq--;
                }

                $permission['sequence'] = (int)$curseq;
            }
        }

        $this->entityManager->flush();

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
        if (!SecurityUtil::checkPermission('ZikulaPermissionsModule::', '::', ACCESS_ADMIN)) {
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
    public function getlinks()
    {
        $links = array();

        if (SecurityUtil::checkPermission('ZikulaPermissionsModule::', '::', ACCESS_READ)) {
            $links[] = array('url' => ModUtil::url('ZikulaPermissionsModule', 'admin', 'view', array()), 'text' => $this->__('Permission rules list'), 'id' => 'permissions_view', 'icon' => 'list');
        }

        if (SecurityUtil::checkPermission('ZikulaPermissionsModule::', '::', ACCESS_ADD)) {
            $links[] = array('url' => ModUtil::url('ZikulaPermissionsModule', 'admin', 'listedit', array('action' => 'add')), 'text' => $this->__('Create new permission rule'), 'id' => 'permissions_new', 'icon' => 'plus');
        }

        if (SecurityUtil::checkPermission('ZikulaPermissionsModule::', '::', ACCESS_ADMIN)) {
            $links[] = array('url' => ModUtil::url('ZikulaPermissionsModule', 'admin', 'modifyconfig'), 'text' => $this->__('Settings'), 'id' => 'permissions_modifyconfig', 'icon' => 'wrench');
        }

        if (ModUtil::getName() == 'ZikulaPermissionsModule') {
            $links[] = array('url' => ModUtil::url('ZikulaPermissionsModule', 'admin', 'viewinstanceinfo'), 'text' => $this->__('Permission rules information'), 'title' => $this->__('Permission rules information'), 'icon' => 'info', 'class' => 'showinstanceinformation');
        }

        return $links;
    }
}
