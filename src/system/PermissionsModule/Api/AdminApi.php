<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\PermissionsModule\Api;

use SecurityUtil;
use BlockUtil;
use ModUtil;
use Zikula\PermissionsModule\Entity\PermissionEntity;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * API functions used by administrative controllers
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
     * @param int[] $args {
     *      @type int $pid the ID of the permission to increment.
     *                    }
     *
     * @return bool true on success, false if the permission rule doesn't exist.
     *
     * @throws AccessDeniedException Thrown if the user doesn't admin acces over the permission rule
     * @throws \InvalidArgumentException Thrown if the pid parameter is not set or not numeric
     * @throws \RuntimeException Thrown if there is no permission rule above the requested one or
     *                                  if there is both affected permissions, in partial view, are seperated by a hidden rule
     */
    public function inc($args)
    {
        // Security check
        if (!SecurityUtil::checkPermission('ZikulaPermissionsModule::', "group::$args[pid]", ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        // Argument check
        if (!isset($args['pid']) || !is_numeric($args['pid'])) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        // get info on current perm
        $permission = $this->entityManager->find('ZikulaPermissionsModule:PermissionEntity', $args['pid']);
        if (!$permission) {
            return false;
        }

        $sequence = $permission['sequence'];

        if ($sequence != 1) {
            $altsequence = $sequence - 1;

            // get info on displaced perm
            $qb = $this->entityManager->createQueryBuilder()
                                      ->select('p')
                                      ->from('ZikulaPermissionsModule:PermissionEntity', 'p')
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
                    throw new \RuntimeException($this->__('Error! Permission rule-swapping in partial view can only be done if both affected permission rules are visible. Please switch to full view.'));
                } else {
                    throw new \RuntimeException($this->__('Error! No permission rule directly above that one.'));
                }

                return false;
            }

            $altpid = $d_permission['pid'];

            // swap sequence numbers
            $perm1 = $this->entityManager->find('ZikulaPermissionsModule:PermissionEntity', $altpid);
            $perm1['sequence'] = $sequence;

            $perm2 = $this->entityManager->find('ZikulaPermissionsModule:PermissionEntity', $args['pid']);
            $perm2['sequence'] = $altsequence;

            $this->entityManager->flush();
        }

        return true;
    }

    /**
     * Decrement sequence number of a permission.
     *
     * @param int[] $args {
     *       @type int    $pid  the ID of the permission to decrement.
     *                    }
     *
     * @return bool true on success, false if the permission rule doesn't exist
     *
     * @throws AccessDeniedException Thrown if the user doesn't admin acces over the permission rule
     * @throws \InvalidArgumentException Thrown if the pid parameter is not set or not numeric
     * @throws \RuntimeException Thrown if there is no permission rule below the requested one or
     *                                  if there is both affected permissions, in partial view, are seperateed by a hidden rule
     */
    public function dec($args)
    {
        // Security check
        if (!SecurityUtil::checkPermission('ZikulaPermissionsModule::', "group::$args[pid]", ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        // Argument check
        if (!isset($args['pid']) || !is_numeric($args['pid'])) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        // get info on current perm
        $permission = $this->entityManager->find('ZikulaPermissionsModule:PermissionEntity', $args['pid']);
        if (!$permission) {
            return false;
        }

        $sequence = $permission['sequence'];

        $maxsequence = $this->maxsequence();
        if ($sequence != $maxsequence) {
            $altsequence = $sequence + 1;

            // get info on displaced perm
            $qb = $this->entityManager->createQueryBuilder()
                                      ->select('p')
                                      ->from('ZikulaPermissionsModule:PermissionEntity', 'p')
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
                    throw new \RuntimeException($this->__('Error! Permission rule-swapping in partial view can only be done if both affected permission rules are visible. Please switch to full view.'));
                } else {
                    throw new \RuntimeException($this->__('Error! No permission rule directly below that one.'));
                }

                return false;
            }

            $altpid = $d_permission['pid'];

            // swap sequence numbers
            $perm1 = $this->entityManager->find('ZikulaPermissionsModule:PermissionEntity', $altpid);
            $perm1['sequence'] = $sequence;

            $perm2 = $this->entityManager->find('ZikulaPermissionsModule:PermissionEntity', $args['pid']);
            $perm2['sequence'] = $altsequence;

            $this->entityManager->flush();
        }

        return true;
    }

    /**
     * Update attributes of a permission.
     *
     * @param mixed[] $args {
     *       @type int    $pid       the ID of the permission to update
     *       @type int    $seq       the order number of the permission
     *       @type int    $oldseq    the old order number of the permission
     *       @type string $realm     the new realm of the permission
     *       @type int    $id        the new group/user id of the permission
     *       @type string $component the new component of the permission
     *       @type string $instance  the new instance of the permission
     *       @type int    $level     the new level of the permission
     *                      }
     *
     * @return bool true if successful
     *
     * @throws AccessDeniedException Thrown if the user doesn't admin acces over the permission rule
     * @throws \InvalidArgumentException Thrown if any of pid, seq, oldseq, id or level are not set or not numeric or
     *                                          if any of realm, component or instance are not set
     */
    public function update($args)
    {
        // Security check
        if (!SecurityUtil::checkPermission('ZikulaPermissionsModule::', "group::$args[pid]", ACCESS_ADMIN)) {
            throw new AccessDeniedException();
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
        $permission = $this->entityManager->find('ZikulaPermissionsModule:PermissionEntity', $args['pid']);
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
     * @param mixed[] $args {
     *       @type string $realm     the new realm of the permission
     *       @type int    $id        the new group/user id of the permission
     *       @type string $component the new component of the permission
     *       @type string $instance  the new instance of the permission
     *       @type int    $level     the new level of the permission
     *       @type int    $insseq    the place to insert the new permission rule
     *                      }
     *
     * @return bool true if successful
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin access over the permission rule.
     * @throws \InvalidArgumentException Thrown if any of id, insseq or level are not set or not numeric or
     *                                          if any of realm, component or instance are not set.
     * @throws \RuntimeException Thrown if the permission rule couldn't be saved.
     */
    public function create($args)
    {
        // Security check
        if (!SecurityUtil::checkPermission('ZikulaPermissionsModule::', "group::$args[id]", ACCESS_ADMIN)) {
            throw new AccessDeniedException();
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
                                         ->update('ZikulaPermissionsModule:PermissionEntity', 'p')
                                         ->set('p.sequence', 'p.sequence + 1')
                                         ->where('p.sequence >= :insseq')
                                         ->setParameter('insseq', $args['insseq'])
                                         ->getQuery();
            $result = $query->getResult();

            if (!$result) {
                throw new \RuntimeException($this->__('Error! Could not save permission rule sequences.'));
            }

            $newseq = $args['insseq'];
        }

        $obj = new PermissionEntity();
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
     * @param int[] $args {
     *       @type int $pid the ID of the permission to delete.
     *                    }
     *
     * @return boolean true on success
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin permissions over the group
     * @throws \InvalidArgumentException Thrown if the pid parameter isn't set or isn't numeric
     */
    public function delete($args)
    {
        // Security check
        if (!SecurityUtil::checkPermission('ZikulaPermissionsModule::', "group::$args[pid]", ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        // Argument check
        if (!isset($args['pid']) || !is_numeric($args['pid'])) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        // get and delete permission
        $permission = $this->entityManager->find('ZikulaPermissionsModule:PermissionEntity', $args['pid']);
        $this->entityManager->remove($permission);
        $this->entityManager->flush();

        $this->resequence();

        return true;
    }

    /**
     * Get the maximum sequence number in permissions table.
     *
     * @return int the maximum sequence number.
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin permissions over the module
     */
    public function maxsequence()
    {
        // Security check
        if (!SecurityUtil::checkPermission('ZikulaPermissionsModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $qb = $this->entityManager->createQueryBuilder();
        $query = $qb->select($qb->expr()->max('p.sequence'))
                    ->from('ZikulaPermissionsModule:PermissionEntity', 'p')
                    ->getQuery();

        return (int)$query->getSingleScalarResult();
    }

    /**
     * Resequence a permissions table.
     *
     * @return bool true if successful, false if no permissions rules are found
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin permissions over the module
     */
    public function resequence()
    {
        // Security check
        if (!SecurityUtil::checkPermission('ZikulaPermissionsModule::', 'group::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        // get all permissions
        $permissions = $this->entityManager->getRepository('ZikulaPermissionsModule:PermissionEntity')->findBy(array(), array('sequence' => 'ASC'));
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
     * @param string[] $args {
     *       @type string $newseq the desired sequence
     *       @type string $oldseq the original sequence number
     *                       }
     *
     * @return bool true if successful
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin permissions over the module
     * @throws \InvalidArgumentException Thrown if either the newseq or oldseq parameters aren't set
     */
    public function full_resequence($args)
    {
        // Security check
        if (!SecurityUtil::checkPermission('ZikulaPermissionsModule::', "::", ACCESS_ADMIN)) {
            throw new AccessDeniedException();
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
                                         ->from('ZikulaPermissionsModule:PermissionEntity', 'p')
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
                                         ->from('ZikulaPermissionsModule:PermissionEntity', 'p')
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
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin permissions over the module
     */
    public function getallschemas()
    {
        // Security check
        if (!SecurityUtil::checkPermission('ZikulaPermissionsModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        BlockUtil::loadAll();
        $schemas = SecurityUtil::getSchemas();
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
    public function getLinks()
    {
        $links = array();

        if (SecurityUtil::checkPermission('ZikulaPermissionsModule::', '::', ACCESS_READ)) {
            $links[] = array(
                'url' => ModUtil::url('ZikulaPermissionsModule', 'admin', 'view', array()),
                'text' => $this->__('Permission rules list'),
                'id' => 'permissions_view',
                'icon' => 'list');
        }

        if (SecurityUtil::checkPermission('ZikulaPermissionsModule::', '::', ACCESS_ADD)) {
            $links[] = array('url' => ModUtil::url('ZikulaPermissionsModule', 'admin', 'listedit', array('action' => 'add')),
                'text' => $this->__('Create new permission rule'),
                'icon' => 'plus',
                'class' => 'create-new-permission');
        }

        $links[] = array('url' => ModUtil::url('ZikulaPermissionsModule', 'admin', 'viewinstanceinfo'),
            'text' => $this->__('Permission rules information'),
            'title' => $this->__('Permission rules information'),
            'icon' => 'info',
            'id' => 'view-instance-info');

        if (SecurityUtil::checkPermission('ZikulaPermissionsModule::', '::', ACCESS_ADMIN)) {
            $links[] = array('url' => ModUtil::url('ZikulaPermissionsModule', 'admin', 'modifyconfig'),
                'text' => $this->__('Settings'),
                'id' => 'permissions_modifyconfig',
                'icon' => 'wrench');
        }

        return $links;
    }
}
