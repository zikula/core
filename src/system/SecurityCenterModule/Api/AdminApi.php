<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SecurityCenterModule\Api;

use ServiceUtil;

/**
 * API functions used by administrative controllers
 * @deprecated remove at Core-2.0
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
        $permissionApi = ServiceUtil::get('zikula_permissions_module.api.permission');
        if (!$permissionApi->hasPermission('ZikulaSecurityCenterModule::', '::', ACCESS_OVERVIEW)) {
            return [];
        }

        $repository = ServiceUtil::get('zikula_securitycenter_module.intrusion_repository');

        $filters = isset($args['where']) ? $args['where'] : [];
        $sorting = isset($args['sorting']) ? $args['sorting'] : [];
        $limit = isset($args['limit']) ? $args['limit'] : 0;
        $offset = isset($args['offset']) ? $args['offset'] : 0;

        $items = $repository->getIntrusions($filters, $sorting, $limit, $offset);

        return $items;
    }

    /**
     * Count all intrusions.
     *
     * This function counts all intrusions that exist in the database.
     *
     * @param $args array arguments passed to function
     *
     * @return integer count of intrusion items in the database
     */
    public function countAllIntrusions($args)
    {
        // Security check
        $permissionApi = ServiceUtil::get('zikula_permissions_module.api.permission');
        if (!$permissionApi->hasPermission('ZikulaSecurityCenterModule::', '::', ACCESS_OVERVIEW)) {
            return 0;
        }

        $repository = ServiceUtil::get('zikula_securitycenter_module.intrusion_repository');

        $filters = isset($args['where']) ? $args['where'] : [];

        $count = $repository->countIntrusions($filters);

        return $count;
    }

    /**
     * Purge IDS Log.
     *
     * @return bool true if successful, false otherwise
     */
    public function purgeidslog()
    {
        $permissionApi = ServiceUtil::get('zikula_permissions_module.api.permission');
        if (!$permissionApi->hasPermission('ZikulaSecurityCenterModule::', '::', ACCESS_DELETE)) {
            return false;
        }

        $repository = ServiceUtil::get('zikula_securitycenter_module.intrusion_repository');

        $repository->truncateTable();

        return true;
    }
}
