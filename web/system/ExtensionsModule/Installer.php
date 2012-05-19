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

namespace ExtensionsModule;

use Zikula\Core\Event\GenericEvent;
use Zikula_View, SecurityUtil, HookUtil, LogUtil, EventUtil;
use Zikula\Core\Event\GenericEvent;

class Installer extends \Zikula_AbstractInstaller
{
    /**
     * Install the Extensions module.
     *
     * @return boolean
     */
    public function install()
    {
        // create tables
        $tables = array(
            'Zikula\Core\Doctrine\Entity\Extension',
            'Zikula\Core\Doctrine\Entity\ExtensionDependency',
            'Zikula\Core\Doctrine\Entity\ExtensionVar',
            'Zikula\Component\HookDispatcher\Storage\Doctrine\Entity\HookAreaEntity',
            'Zikula\Component\HookDispatcher\Storage\Doctrine\Entity\HookBindingEntity',
            'Zikula\Component\HookDispatcher\Storage\Doctrine\Entity\HookProviderEntity',
            'Zikula\Component\HookDispatcher\Storage\Doctrine\Entity\HookRuntimeEntity',
            'Zikula\Component\HookDispatcher\Storage\Doctrine\Entity\HookSubscriberEntity',
        );
        
        try {
            \DoctrineHelper::createSchema($this->entityManager, $tables);
        } catch (Exception $e) {
            return false;
        }

        // create hook provider table.
        EventUtil::registerPersistentModuleHandler('Extensions', 'controller.method_not_found', array('ExtensionsModule\HookUI', 'hooks'));
        EventUtil::registerPersistentModuleHandler('Extensions', 'controller.method_not_found', array('ExtensionsModule\HookUI', 'moduleservices'));

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
     * @return  boolean|string True on success, last valid version string or false if fails.
     */
    public function upgrade($oldversion)
    {
        // Upgrade dependent on old version number
        switch ($oldversion)
        {
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
        $version = new Version();
        $meta = $version->toArray();
        $meta['state'] = \ModUtil::STATE_ACTIVE;
        
        unset($meta['dependencies']);
        
        $item = new \Zikula\Core\Doctrine\Entity\Extension();
        $item->merge($meta);
        
        $this->entityManager->persist($item);
        $this->entityManager->flush();
    }
}