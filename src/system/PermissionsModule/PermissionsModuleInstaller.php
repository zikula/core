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

namespace Zikula\PermissionsModule;

use Zikula\PermissionsModule\Entity\PermissionEntity;

/**
 * Installation and upgrade routines for the permissions module
 */
class PermissionsModuleInstaller extends \Zikula_AbstractInstaller
{
    /**
     * Initialise the Permissions module.
     *
     * This function is only ever called once during the lifetime of a particular
     * module instance
     *
     * @return boolean True if initialisation successful, false otherwise.
     */
    public function install()
    {
        // create the table
        try {
            \DoctrineHelper::createSchema($this->entityManager, array('Zikula\PermissionsModule\Entity\PermissionEntity'));
        } catch (\Exception $e) {
            return false;
        }

        // Create any default for this module
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
            case '1.1':
            // future upgrade routines
        }

        // Update successful
        return true;
    }

    /**
     * delete the permissions module
     *
     * This function is only ever called once during the lifetime of a particular
     * module instance
     *
     * Since the permissions module should never be deleted we'all always return false here
     *
     * @return bool false
     */
    public function uninstall()
    {
        // Deletion not allowed
        return false;
    }

    /**
     * create the default data for the permissions module
     *
     * This function is only ever called once during the lifetime of a particular
     * module instance
     *
     * @return void
     */
    public function defaultdata()
    {
        // give administrator group full access to everything as top priority
        $record = new PermissionEntity();
        $record['gid']       = 2;
        $record['sequence']  = 1;
        $record['realm']     = 0;
        $record['component'] = '.*';
        $record['instance']  = '.*';
        $record['level']     = ACCESS_ADMIN; // 800
        $record['bond']      = 0;
        $this->entityManager->persist($record);

        // give user group comment access to everything as second priority
        $record = new PermissionEntity();
        $record['gid']       = 1;
        $record['sequence']  = 2;
        $record['realm']     = 0;
        $record['component'] = '.*';
        $record['instance']  = '.*';
        $record['level']     = ACCESS_COMMENT; // 300
        $record['bond']      = 0;
        $this->entityManager->persist($record);

        // allow unregistered users only read access to everything as lowest priority
        $record = new PermissionEntity();
        $record['gid']       = 0;
        $record['sequence']  = 3;
        $record['realm']     = 0;
        $record['component'] = '.*';
        $record['instance']  = '.*';
        $record['level']     = ACCESS_READ; // 200
        $record['bond']      = 0;
        $this->entityManager->persist($record);

        $this->entityManager->flush();

        $this->setVar('filter', 1);
        $this->setVar('warnbar', 1);
        $this->setVar('rowview', 20);
        $this->setVar('rowedit', 20);
        $this->setVar('lockadmin', 1);
        $this->setVar('adminid', 1);
    }
}
