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

namespace Zikula\ExtensionsModule;

use Doctrine_Core;
use EventUtil;
use System;
use Zikula_Event;
use Zikula\ExtensionsModule\ExtensionsModuleVersion;
use ModUtil;
use Zikula\ExtensionsModule\Entity\ExtensionEntity;

/**
 * Installation and upgrade routines for the extensions module
 */
class ExtensionsModuleInstaller extends \Zikula_AbstractInstaller
{
    /**
     * Install the Extensions module.
     *
     * @return boolean true if installation is successful, false otherwise
     */
    public function install()
    {
        // create tables
        $tables = array(
            'Zikula\ExtensionsModule\Entity\ExtensionEntity',
            'Zikula\ExtensionsModule\Entity\ExtensionDependencyEntity',
            'Zikula\ExtensionsModule\Entity\ExtensionVarEntity',
            'Zikula\Component\HookDispatcher\Storage\Doctrine\Entity\HookAreaEntity',
            'Zikula\Component\HookDispatcher\Storage\Doctrine\Entity\HookBindingEntity',
            'Zikula\Component\HookDispatcher\Storage\Doctrine\Entity\HookProviderEntity',
            'Zikula\Component\HookDispatcher\Storage\Doctrine\Entity\HookRuntimeEntity',
            'Zikula\Component\HookDispatcher\Storage\Doctrine\Entity\HookSubscriberEntity',
        );

        try {
            \DoctrineHelper::createSchema($this->entityManager, $tables);
        } catch (\Exception $e) {
            return false;
        }

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
     * @param string $oldversion Version number string to upgrade from.
     *
     * @return  boolean|string True on success, last valid version string or false if fails.
     */
    public function upgrade($oldversion)
    {
        // Upgrade dependent on old version number
        switch ($oldversion) {
            case '3.7.10':
                // Load DB connection
                $connection = $this->entityManager->getConnection();

                // increase length of some hook table fields from 20 to 60
                $commands = array();
                $commands[] = "ALTER TABLE `hook_provider` CHANGE `method` `method` VARCHAR(60) NOT NULL";
                $commands[] = "ALTER TABLE `hook_runtime` CHANGE `method` `method` VARCHAR(60) NOT NULL";

                foreach ($commands as $sql) {
                    $stmt = $connection->executeQuery($sql);
                }
            case '3.7.11':
                \DoctrineHelper::updateSchema($this->entityManager, array('Zikula\ExtensionsModule\Entity\ExtensionEntity'));
            case '3.7.12':
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
     * @return boolean false this module cannot be deleted
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
        $version = new ExtensionsModuleVersion(new ZikulaExtensionsModule());
        $meta = $version->toArray();
        $meta['state'] = \ModUtil::STATE_ACTIVE;

        unset($meta['dependencies']);
        unset($meta['oldnames']);

        $item = new ExtensionEntity();
        $item->merge($meta);

        $this->entityManager->persist($item);
        $this->entityManager->flush();
    }
}