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

class Extensions_Installer extends Zikula_AbstractInstaller
{
    /**
     * Install the Extensions module.
     *
     * @return boolean
     */
    public function install()
    {
        // modules
        if (!DBUtil::createTable('modules')) {
            return false;
        }

        // module_vars
        if (!DBUtil::createTable('module_vars')) {
            return false;
        }

        // hooks
        if (!DBUtil::createTable('hooks')) {
            return false;
        }

        // module_deps
        if (!DBUtil::createTable('module_deps')) {
            return false;
        }

        // create hook provider table.
        Doctrine_Core::createTablesFromArray(array('Zikula_Doctrine_Model_HookArea', 'Zikula_Doctrine_Model_HookProvider', 'Zikula_Doctrine_Model_HookSubscriber', 'Zikula_Doctrine_Model_HookBinding', 'Zikula_Doctrine_Model_HookRuntime'));
        EventUtil::registerPersistentModuleHandler('Extensions', 'controller.method_not_found', array('Extensions_HookUI', 'hooks'));
        EventUtil::registerPersistentModuleHandler('Extensions', 'controller.method_not_found', array('Extensions_HookUI', 'moduleservices'));

        // populate default data
        $this->defaultdata();
        $this->setVar('itemsperpage', 25);

        // Initialisation successful
        return true;
    }

    /**
     * Upgrade the module from an old version.
     *
     * This function must consider all the released versions of the module!
     * If the upgrade fails at some point, it returns the last upgraded version.
     *
     * @param string $oldVersion Version number string to upgrade from.
     *
     * @return boolean|string True on success, last valid version string or false if fails.
     */
    public function upgrade($oldversion)
    {
        // Upgrade dependent on old version number
        switch ($oldversion) {
            case '3.6':
            case '3.7':
                // legacy is no longer supported
                System::delVar('loadlegacy');
                DBUtil::changeTable('modules');
            case '3.7.4':
            case '3.7.5':
            case '3.7.6':
            case '3.7.8':
                // create the new hooks tables
                Doctrine_Core::createTablesFromArray(array('Zikula_Doctrine_Model_HookArea', 'Zikula_Doctrine_Model_HookProvider', 'Zikula_Doctrine_Model_HookSubscriber', 'Zikula_Doctrine_Model_HookBinding', 'Zikula_Doctrine_Model_HookRuntime'));
                EventUtil::registerPersistentModuleHandler('Extensions', 'controller.method_not_found', array('Extensions_HookUI', 'hooks'));
                EventUtil::registerPersistentModuleHandler('Extensions', 'controller.method_not_found', array('Extensions_HookUI', 'moduleservices'));
            case '3.7.9':
                // increase length of some hook table fields from 60 to 100
                $commands = array();
                $commands[] = "ALTER TABLE hook_area CHANGE areaname areaname VARCHAR(100) NOT NULL";
                $commands[] = "ALTER TABLE hook_runtime CHANGE eventname eventname VARCHAR(100) NOT NULL";
                $commands[] = "ALTER TABLE hook_subscriber CHANGE eventname eventname VARCHAR(100) NOT NULL";

                // Load DB connection
                $dbEvent = new Zikula_Event('doctrine.init_connection');
                $connection = $this->eventManager->notify($dbEvent)->getData();

                foreach ($commands as $sql) {
                    $stmt = $connection->prepare($sql);
                    $stmt->execute();
                }

            case '3.7.10':
                // future upgrade routines

        }

        // Update successful
        return true;
    }

    /**
     * delete the modules module
     *
     * This function is only ever called once during the lifetime of a particular
     * module instance.
     *
     * Since the modules module should never be deleted we'all always return false here
     * @return boolean False
     */
    public function uninstall()
    {
        // Deletion not allowed
        return false;
    }

    /**
     * Create the default data for the Extensions module.
     *
     * @return void
     */
    public function defaultdata()
    {
        $version = new Extensions_Version();
        $meta = $version->toArray();
        $meta['capabilities'] = serialize($meta['capabilities']);
        $meta['securityschema'] = serialize($meta['securityschema']);
        $meta['state'] = ModUtil::STATE_ACTIVE;
        DBUtil::insertObject($meta, 'modules');
    }
}
