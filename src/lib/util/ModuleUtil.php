<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv2.1 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * ModuleUtil
 *
 * @package Zikula_Core
 * @subpackage ModuleUtil
 */
class ModuleUtil
{
    /**
     * Generic modules select function. Only modules in the module
     * table are returned which means that new/unscanned modules
     * will not be returned
     *
     * @param where The where clause to use for the select
     * @param sort  The sort to use
     *
     * @return The resulting module object array
     */
    public static function getModules ($where='', $sort='displayname')
    {
        return DBUtil::selectObjectArray ('modules', $where, $sort);
    }


    /**
     * Return an array of modules in the specified state, only modules in
     * the module table are returned which means that new/unscanned modules
     * will not be returned
     *
     * @param state    The module state (optional) (defaults = active state)
     * @param sort  The sort to use
     *
     * @return The resulting module object array
     */
    public static function getModulesByState ($state=3, $sort='displayname')
    {
        $pntables     = pnDBGetTables();
        $moduletable  = $pntables['modules'];
        $modulecolumn = $pntables['modules_column'];

        $where = "$modulecolumn[state] = $state";
        return DBUtil::selectObjectArray ('modules', $where, $sort);
    }

}
