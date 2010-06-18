<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2.1 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

class ObjectData_Installer extends Zikula_Installer
{
    /**
     * initialise the ObjectData module
     * This function is only ever called once during the lifetime of a particular
     * module instance
     */
    public function install()
    {
        if (!DBUtil::createTable('objectdata_attributes')) {
            return false;
        }

        if (!DBUtil::createTable('objectdata_log')) {
            return false;
        }

        $this->createTables_101 ();

        return true;
    }

    /**
     * upgrade the module from an old version
     *
     * This function must consider all the released versions of the module!
     * If the upgrade fails at some point, it returns the last upgraded version.
     *
     * @param        string   $oldVersion   version number string to upgrade from
     * @return       mixed    true on success, last valid version string or false if fails
     */
    public function upgrade($oldversion)
    {
        switch ($oldversion)
        {
            case '1.00':
                ObjectData_createTables_101();
                objectdata_upgrade('1.01');

            case '1.01':
            case '1.02':
                if (!DBUtil::changeTable('objectdata_attributes')) {
                    LogUtil::registerError(__('Error! Could not save the attributes table.'));
                    return '1.02';
                }

            case '1.03':
            // future upgrade routines
        }

        return true;
    }

    /**
     * delete the ObjectData module
     * This function is only ever called once during the lifetime of a particular
     * module instance
     */
    public function uninstall()
    {
        // cannot disable this module because it's required for core functions
        return false;
    }

    public function createTables_101()
    {
        if (!DBUtil::createTable('objectdata_meta')) {
            return false;
        }
    }
}