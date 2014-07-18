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

namespace Zikula\Module\SecurityCenterModule\Api;

use SecurityUtil;
use ModUtil;
use System;

/**
 * API functions used by administrative controllers
 */
class AdminApi extends \Zikula_AbstractApi
{
    /**
     * Get all intrusions.
     *
     * This function gets all intrusions from the database.
     *
     * @param mixed[] $args {
     *      @type array $where   parameters for the where clause
     *      @type array $sorting parameters for the order by clause
     *      @type array $limit   parameters for the limit clause
     *      @type array $offset  parameters for the offset 
     *                      }
     *
     * @return array array of items
     */
    public function getAllIntrusions($args)
    {
        // Security check
        if (!SecurityUtil::checkPermission('ZikulaSecurityCenterModule::', '::', ACCESS_OVERVIEW)) {
            return array();
        }

        // create a QueryBuilder instance
        $qb = $this->entityManager->createQueryBuilder();

        // add select and from params
        $qb->select('i')
           ->from('ZikulaSecurityCenterModule:IntrusionEntity', 'i');

        // add clause for user
        if (isset($args['where']['uid'])) {
            $uid = $args['where']['uid'];
            unset($args['where']['uid']);

            if ($uid > 0) {
                $qb->from('ZikulaUsersModule:UserEntity', 'u');
                $qb->andWhere($qb->expr()->eq('i.user', 'u.uid'));
                $qb->andWhere($qb->expr()->eq('i.user', ':uid'))->setParameter('uid', $uid);
            }
        }

        // add clauses for where
        if (isset($args['where'])) {
            $i = 1;
            foreach ($args['where'] as $w_key => $w_value) {
                $qb->andWhere($qb->expr()->eq('i.' . $w_key, "?$i"))->setParameter($i, $w_value);
                $i++;
            }
        }

        // add clause for ordering
        if (isset($args['sorting'])) {
            if (isset($args['sorting']['username'])) {
                $sortdir = $args['sorting']['username'];
                unset($args['sorting']['username']);

                $qb->from('ZikulaUsersModule:UserEntity', 'u');
                $qb->andWhere($qb->expr()->eq('i.user', 'u.uid'));
                $qb->addOrderBy('u.uname', $sortdir);
            }

            foreach ($args['sorting'] as $sort => $sortdir) {
                $qb->addOrderBy('i.' . $sort, $sortdir);
            }
        }

        // add limit and offset
        if (isset($args['limit']) && $args['limit'] > 0) {
            $qb->setMaxResults($args['limit']);
            if (isset($args['offset']) && $args['offset'] > 0) {
                $qb->setFirstResult($args['offset']);
            }
        }

        // convert querybuilder instance into a Query object
        $query = $qb->getQuery();

        // execute query
        $items = $query->getResult();

        return $items;
    }

    /**
     * Count all intrusions.
     *
     * This function counts all intrusions that exist in the database.
     *
     * @param $args array  arguments passed to function
     *
     * @return integer count of intrusion items in the database.
     */
    public function countAllIntrusions($args)
    {
        // Security check
        if (!SecurityUtil::checkPermission('ZikulaSecurityCenterModule::', '::', ACCESS_OVERVIEW)) {
            return 0;
        }

        // create a QueryBuilder instance
        $qb = $this->entityManager->createQueryBuilder();

        // add select and from params
        $qb->select('count(i.id)')
           ->from('ZikulaSecurityCenterModule:IntrusionEntity', 'i');

        // add clause for user
        if (isset($args['where']['uid'])) {
            $uid = $args['where']['uid'];
            unset($args['where']['uid']);

            if ($uid > 0) {
                $qb->from('ZikulaUsersModule:UserEntity', 'u');
                $qb->andWhere($qb->expr()->eq('i.user', 'u.uid'));
                $qb->andWhere($qb->expr()->eq('i.user', ':uid'))->setParameter('uid', $uid);
            }
        }

        // add clauses for where
        if (isset($args['where'])) {
            $i = 1;
            foreach ($args['where'] as $w_key => $w_value) {
                $qb->andWhere($qb->expr()->eq('i.' . $w_key, "?$i"))->setParameter($i, $w_value);
                $i++;
            }
        }

        // convert querybuilder instance into a Query object
        $query = $qb->getQuery();

        // execute query
        $count = (int)$query->getSingleScalarResult();

        return $count;
    }

    /**
     * Purge IDS Log.
     *
     * @return bool true if successful, false otherwise.
     */
    public function purgeidslog()
    {
        if (!SecurityUtil::checkPermission('ZikulaSecurityCenterModule::', '::', ACCESS_DELETE)) {
            return false;
        }

        // truncate sc_intrusion table
        $connection = $this->entityManager->getConnection();
        $platform = $connection->getDatabasePlatform();
        $connection->executeUpdate($platform->getTruncateTableSQL('sc_intrusion', true));

        return true;
    }

    /**
     * get available admin panel links
     *
     * @return array array of admin links
     */
    public function getLinks()
    {
        $links = array();

        if (SecurityUtil::checkPermission('ZikulaSecurityCenterModule::', '::', ACCESS_ADMIN)) {
            $links[] = array('url' => ModUtil::url('ZikulaSecurityCenterModule', 'admin', 'modifyconfig'), 'text' => $this->__('Settings'), 'icon' => 'wrench');
            $links[] = array('url' => ModUtil::url('ZikulaSecurityCenterModule', 'admin', 'allowedhtml'), 'text' => $this->__('Allowed HTML settings'), 'icon' => 'list');
            $links[] = array('url' => ModUtil::url('ZikulaSecurityCenterModule', 'admin', 'viewidslog'),
                             'text' => $this->__('View IDS Log'),
                             'icon' => 'align-justify',
                             'links' => array(
                                             array('url' => ModUtil::url('ZikulaSecurityCenterModule', 'admin', 'viewidslog'),
                                                   'text' => $this->__('View IDS Log')),
                                             array('url' => ModUtil::url('ZikulaSecurityCenterModule', 'admin', 'exportidslog'),
                                                   'text' => $this->__('Export IDS Log')),
                                             array('url' => ModUtil::url('ZikulaSecurityCenterModule', 'admin', 'purgeidslog'),
                                                   'text' => $this->__('Purge IDS Log'))
                                               ));

            $outputfilter = System::getVar('outputfilter');
            if ($outputfilter == 1) {
                $links[] = array('url' => ModUtil::url('ZikulaSecurityCenterModule', 'admin', 'purifierconfig'), 'text' => $this->__('HTMLPurifier settings'), 'icon' => 'wrench');
            }
        }

        return $links;
    }
}