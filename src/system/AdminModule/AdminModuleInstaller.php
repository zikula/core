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

namespace Zikula\AdminModule;

use DoctrineHelper;
use Zikula\AdminModule\Entity\AdminCategoryEntity;

/**
 * Installation and upgrade routines for the admin module
 */
class AdminModuleInstaller extends \Zikula_AbstractInstaller
{
    /**
     * Initialise the Admin module.
     *
     * This function is only ever called once during the lifetime of a particular
     * module instance
     *
     * @return boolean True if initialisation successful, false otherwise.
     */
    public function install()
    {
        // create tables
        try {
            DoctrineHelper::createSchema($this->entityManager, array(
                'Zikula\AdminModule\Entity\AdminCategoryEntity',
                'Zikula\AdminModule\Entity\AdminModuleEntity',
            ));
        } catch (\Exception $e) {
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
     * @param  string $oldversion version number string to upgrade from
     *
     * @return bool|string true on success, last valid version string or false if fails
     */
    public function upgrade($oldversion)
    {
        // Upgrade dependent on old version number
        switch ($oldversion) {
            case '1.9.1':
                // ensure there is a proper sortorder for modulecategories
                // has the sort order already been set?
                $categories = $this->entityManager->getRepository('ZikulaAdminModule:AdminCategoryEntity')->findBy(['sortorder' => 0]);
                if (count($categories) > 1) {
                    // sort categories by id
                    $dql = "UPDATE Zikula\\AdminModule\\Entity\\AdminCategoryEntity ac SET ac.sortorder = ac.cid - 1";
                    $query = $this->entityManager->createQuery($dql);
                    $query->execute();
                }
            case '1.9.2':
            // future upgrade routines
        }

        // Update successful
        return true;
    }

    /**
     * delete the Admin module
     *
     * This function is only ever called once during the lifetime of a particular
     * module instance
     *
     * @return bool true if deletion successful, false otherwise
     */
    public function uninstall()
    {
        return false;
    }

    /**
     * create the default data for the modules module
     *
     * This function is only ever called once during the lifetime of a particular
     * module instance
     *
     * @return bool false
     */
    public function defaultdata()
    {
        $records = [
            [
                'name' => $this->__('System'),
                'description' => $this->__('Core modules at the heart of operation of the site.'),
                'sortorder' => 0
            ],
            [
                'name' => $this->__('Layout'),
                'description' => $this->__("Layout modules for controlling the site's look and feel."),
                'sortorder' => 1
            ],
            [
                'name' => $this->__('Users'),
                'description' => $this->__('Modules for controlling user membership, access rights and profiles.'),
                'sortorder' => 2
            ],
            [
                'name' => $this->__('Content'),
                'description' => $this->__('Modules for providing content to your users.'),
                'sortorder' => 3
            ],
            [
                'name' => $this->__('Uncategorised'),
                'description' => $this->__('Newly-installed or uncategorized modules.'),
                'sortorder' => 4
            ],
            [
                'name' => $this->__('Security'),
                'description' => $this->__('Modules for managing the site\'s security.'),
                'sortorder' => 5
            ]
        ];

        foreach ($records as $record) {
            $item = new AdminCategoryEntity();
            $item->merge($record);
            $this->entityManager->persist($item);
        }

        $this->entityManager->flush();
    }
}
