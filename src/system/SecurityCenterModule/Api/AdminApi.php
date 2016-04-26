<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SecurityCenterModule\Api;

use SecurityUtil;
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
     * @type array $where parameters for the where clause
     * @type array $sorting parameters for the order by clause
     * @type array $limit parameters for the limit clause
     * @type array $offset parameters for the offset
     *                      }
     *
     * @return array array of items
     */
    public function getAllIntrusions($args)
    {
        // Security check
        if (!SecurityUtil::checkPermission('ZikulaSecurityCenterModule::', '::', ACCESS_OVERVIEW)) {
            return [];
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
        $links = [];

        if (SecurityUtil::checkPermission('ZikulaSecurityCenterModule::', '::', ACCESS_ADMIN)) {
            $links[] = [
                'url' => $this->get('router')->generate('zikulasecuritycentermodule_admin_modifyconfig'),
                'text' => $this->__('Settings'),
                'icon' => 'wrench'
            ];
            $links[] = [
                'url' => $this->get('router')->generate('zikulasecuritycentermodule_admin_allowedhtml'),
                'text' => $this->__('Allowed HTML settings'),
                'icon' => 'list'
            ];
            $links[] = [
                'url' => $this->get('router')->generate('zikulasecuritycentermodule_admin_viewidslog'),
                'text' => $this->__('View IDS Log'),
                'icon' => 'align-justify',
                'links' => [
                    [
                        'url' => $this->get('router')->generate('zikulasecuritycentermodule_admin_viewidslog'),
                        'text' => $this->__('View IDS Log')
                    ],
                    [
                        'url' => $this->get('router')->generate('zikulasecuritycentermodule_admin_exportidslog'),
                        'text' => $this->__('Export IDS Log')
                    ],
                    [
                        'url' => $this->get('router')->generate('zikulasecuritycentermodule_admin_purgeidslog'),
                        'text' => $this->__('Purge IDS Log')
                    ]
                ]
            ];

            $outputfilter = System::getVar('outputfilter');
            if ($outputfilter == 1) {
                $links[] = [
                    'url' => $this->get('router')->generate('zikulasecuritycentermodule_admin_purifierconfig'),
                    'text' => $this->__('HTMLPurifier settings'),
                    'icon' => 'wrench'
                ];
            }
        }

        return $links;
    }
}
