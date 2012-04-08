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

class Admin_Installer extends Zikula_AbstractInstaller
{
    /**
     * Initialise the Admin module.
     * This function is only ever called once during the lifetime of a particular
     * module instance
     *
     * @return boolean True if initialisation succcesful, false otherwise.
     */
    public function install()
    {
        // create tables
        try {
            DoctrineHelper::createSchema($this->entityManager, array('Admin_Entity_AdminCategory', 'Admin_Entity_AdminModule'));
        } catch (Exception $e) {
            return false;
        }

        $this->setVar('modulesperrow', 3);
        $this->setVar('itemsperpage', 15);
        $this->setVar('defaultcategory', 5);
        $this->setVar('admingraphic', 1);
        $this->setVar('startcategory', 1);
        // change below to 0 before release - just makes it easier doing development meantime - drak
        // we can now leave this at 0 since the code also checks the development flag (config.php) - markwest
        $this->setVar('ignoreinstallercheck', 0);
        $this->setVar('admintheme', '');
        $this->setVar('displaynametype', 1);

        $this->defaultdata();

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
            case '1.5':
                if (!DBUtil::changeTable('admin_module')) {
                    return '1.5';
                }

            case '1.6':
                $this->setVar('modulesperrow', 3);
                $this->setVar('itemsperpage', 15);
                $this->setVar('moduledescription', 1);

            case '1.7':
            case '1.8':
                $this->delVar('moduledescription');

            case '1.8.1':
                if (!DBUtil::changeTable('admin_category')) {
                    return '1.8.1';
                }
                if (!DBUtil::changeTable('admin_module')) {
                    return '1.8.1';
                }

            case '1.9.0':
                $this->delVar('modulestylesheet');

            case '1.9.1':
            // future upgrade routines
        }

        // Update successful
        return true;
    }

    /**
     * delete the Admin module
     * This function is only ever called once during the lifetime of a particular
     * module instance
     * @return bool true if deletetion succcesful, false otherwise
     */
    public function uninstall()
    {
        // drop tables
        try {
            DoctrineHelper::dropSchema($this->entityManager, array('Admin_Entity_AdminCategory', 'Admin_Entity_AdminModule'));
        } catch (Exception $e) {
            return false;
        }

        $this->delVars();

        // Deletion successful
        return true;
    }

    /**
     * create the default data for the modules module
     *
     * This function is only ever called once during the lifetime of a particular
     * module instance
     *
     * @return       bool       false
     */
    public function defaultdata()
    {
        $records = array(
                    array('name'     => $this->__('System'),
                          'description' => $this->__('Core modules at the heart of operation of the site.')),
                    array('name'     => $this->__('Layout'),
                          'description' => $this->__("Layout modules for controlling the site's look and feel.")),
                    array('name'     => $this->__('Users'),
                          'description' => $this->__('Modules for controlling user membership, access rights and profiles.')),
                    array('name'     => $this->__('Content'),
                          'description' => $this->__('Modules for providing content to your users.')),
                    array('name'     => $this->__('Uncategorised'),
                          'description' => $this->__('Newly-installed or uncategorized modules.')),
                    array('name'     => $this->__('Security'),
                          'description' => $this->__('Modules for managing the site\'s security.')));
        
        foreach ($records as $record) {
            $item = new Admin_Entity_AdminCategory;
            $item->merge($record);
            $this->entityManager->persist($item);
        }
        
        $this->entityManager->flush();
    }
}
