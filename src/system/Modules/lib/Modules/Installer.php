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

class Modules_Installer extends Zikula_Installer
{
    /**
     * initialise the Modules module
     *
     * This function is only ever called once during the lifetime of a particular
     * module instance.
     * This function MUST exist in the pninit file for a module
     *
     * @return       bool       true on success, false otherwise
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
        Doctrine_Core::createTablesFromArray(array('Zikula_Doctrine_Model_HookProviders', 'Zikula_Doctrine_Model_HookSubscribers',
                'Zikula_Doctrine_Model_HookBindings'));
        EventUtil::registerPersistentModuleHandler('Modules', 'controller.method_not_found', array('Modules_HookUI', 'hookproviders'));
        EventUtil::registerPersistentModuleHandler('Modules', 'controller.method_not_found', array('Modules_HookUI', 'hooksubscribers'));
        EventUtil::registerPersistentModuleHandler('Modules', 'controller.method_not_found', array('Modules_HookUI', 'moduleservices'));
        EventUtil::registerPersistentModuleHandler('Modules', 'module_dispatch.services.links', array('Modules_HookUI', 'servicelinks'));

        // populate default data
        $this->defaultdata();
        $this->setVar('itemsperpage', 25);

        // Initialisation successful
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
        // Upgrade dependent on old version number
        switch ($oldversion)
        {
            case '3.7':
                // legacy is no longer supported
                System::delVar('loadlegacy');
                DBUtil::changeTable('modules');
            case '3.7.4':
            case '3.7.5':
            case '3.7.6':
            case '3.7.8':
                // create the new hooks tables
                Doctrine_Core::createTablesFromArray(array('Zikula_Doctrine_Model_HookProviders', 'Zikula_Doctrine_Model_HookSubscribers',
                'Zikula_Doctrine_Model_HookBindings'));
                EventUtil::registerPersistentModuleHandler('Modules', 'controller.method_not_found', array('Modules_HookUI', 'hookproviders'));
                EventUtil::registerPersistentModuleHandler('Modules', 'controller.method_not_found', array('Modules_HookUI', 'hooksubscribers'));
            case '3.7.8':
                EventUtil::registerPersistentModuleHandler('Modules', 'controller.method_not_found', array('Modules_HookUI', 'moduleservices'));
                EventUtil::registerPersistentModuleHandler('Modules', 'module_dispatch.services.links', array('Modules_HookUI', 'servicelinks'));
            case '3.7.9':
                // future upgrade routines

        }

        // Update successful
        return true;
    }

    /**
     * delete the modules module
     *
     * This function is only ever called once during the lifetime of a particular
     * module instance
     * This function MUST exist in the pninit file for a module
     *
     * Since the modules module should never be deleted we'all always return false here
     * @return       bool       false
     */
    public function uninstall()
    {
        // Deletion not allowed
        return false;
    }

    /**
     * create the default data for the Modules module
     *
     * This function is only ever called once during the lifetime of a particular
     * module instance
     *
     * @return       bool       false
     */
    public function defaultdata()
    {
        $version = new Modules_Version();
        $meta = $version->toArray();
        $meta['capabilities'] = serialize($meta['capabilities']);
        $meta['securityschema'] = serialize($meta['securityschema']);
        $meta['state'] = ModUtil::STATE_ACTIVE;
        DBUtil::insertObject($meta, 'modules');
    }
}