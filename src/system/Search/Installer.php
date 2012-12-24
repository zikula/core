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

class Search_Installer extends Zikula_AbstractInstaller
{
    /**
     * initialise the Search module
     * This function is only ever called once during the lifetime of a particular
     * module instance
     */
    public function install()
    {
        // Creating the table
        if (!DBUtil::createTable('search_stat')) {
            return false;
        }

        // Creating the table
        if (!DBUtil::createTable('search_result')) {
            return false;
        }

        // create module vars
        $this->setVar('itemsperpage', 10);
        $this->setVar('limitsummary', 255);

        // register event handler to activate new modules in the search block
        EventUtil::registerPersistentModuleHandler('Search', 'installer.module.installed', array('Search_EventHandlers', 'moduleInstall'));

        // Initialisation successful
        return true;
    }

    /**
     * upgrade the Search module from an old version
     *
     * This function must consider all the released versions of the module!
     * If the upgrade fails at some point, it returns the last upgraded version.
     *
     * @param  string $oldVersion version number string to upgrade from
     * @return mixed  true on success, last valid version string or false if fails
     */
    public function upgrade($oldversion)
    {
        // Upgrade dependent on old version number
        switch ($oldversion) {
            case '1.5.1':
                // register event handler to activate new modules in the search block
                EventUtil::registerPersistentModuleHandler('Search', 'installer.module.installed', array('Search_EventHandlers', 'moduleInstall'));

            case '1.5.2':
            // future upgrade routines
        }

        // Update successful
        return true;
    }

    /**
     * Delete the Search module
     * This function is only ever called once during the lifetime of a particular
     * module instance
     */
    public function uninstall()
    {
        if (DBUtil::dropTable('search_stat') != 2) {
            return false;
        }

        if (DBUtil::dropTable('search_result') != 2) {
            return false;
        }

        // Delete any module variables
        $this->delVars();

        // unregister event handlers
        EventUtil::unregisterPersistentModuleHandlers('Search');

        // Deletion successful
        return true;
    }
}
