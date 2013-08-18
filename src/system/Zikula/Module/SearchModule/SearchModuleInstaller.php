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

namespace Zikula\Module\SearchModule;

use DBUtil;
use EventUtil;
use DoctrineHelper;

class SearchModuleInstaller extends \Zikula_AbstractInstaller
{
    /**
     * initialise the Search module
     * This function is only ever called once during the lifetime of a particular
     * module instance
     */
    public function install()
    {
        // create schema
        try {
            DoctrineHelper::createSchema($this->entityManager, array(
                'Zikula\Module\SearchModule\Entity\SearchResultEntity',
                'Zikula\Module\SearchModule\Entity\SearchStatEntity',
            ));
        } catch (\Exception $e) {
            return false;
        }

        // create module vars
        $this->setVar('itemsperpage', 10);
        $this->setVar('limitsummary', 255);
        $this->setVar('opensearch_enabled', true);
        $this->setVar('opensearch_adult_content', false);


        // register event handler to activate new modules in the search block.
        EventUtil::registerPersistentModuleHandler($this->name, 'installer.module.installed', array('Zikula\Module\SearchModule\Listener\ModuleListener', 'moduleInstall'));

        // register event handler for opensearch.
        EventUtil::registerPersistentModuleHandler($this->name, 'frontcontroller.predispatch', array('Zikula\Module\SearchModule\Listener\PageloadListener', 'pageload'));

        // Initialisation successful
        return true;
    }

    public function upgrade($oldversion)
    {
        // Upgrade dependent on old version number
        switch ($oldversion) {
            case '1.5.2':
                $this->setVar('opensearch_enabled', true);
                $this->setVar('opensearch_adult_content', false);
                EventUtil::registerPersistentModuleHandler($this->name, 'frontcontroller.predispatch', array('Zikula\Module\SearchModule\Listener\PageloadListener', 'pageload'));
            case '1.5.3':
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
        try {
            DoctrineHelper::dropSchema($this->entityManager, array(
                'Zikula\Module\SearchModule\Entity\SearchResultEntity',
                'Zikula\Module\SearchModule\Entity\SearchStatEntity',
            ));
        } catch (\Exception $e) {
            return false;
        }

        // Delete any module variables
        $this->delVars();

        // unregister event handlers
        EventUtil::unregisterPersistentModuleHandlers('ZikulaSearchModule');

        // Deletion successful
        return true;
    }
}
