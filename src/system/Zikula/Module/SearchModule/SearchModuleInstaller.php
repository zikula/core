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

namespace Zikula\Module\SearchModule;

use EventUtil;
use DoctrineHelper;

/**
 * installation routines for the search module
 */
class SearchModuleInstaller extends \Zikula_AbstractInstaller
{
    /**
     * initialise the Search module
     *
     * This function is only ever called once during the lifetime of a particular
     * module instance
     *
     * @return boolean True if initialisation successful, false otherwise.
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
            case '1.5.2':
                $this->setVar('opensearch_enabled', true);
                $this->setVar('opensearch_adult_content', false);
            case '1.5.3':
            // future upgrade routines
        }

        // Update successful
        return true;
    }

    /**
     * Delete the Search module
     *
     * This function is only ever called once during the lifetime of a particular
     * module instance
     *
     * @return bool true if deletion successful, false otherwise
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

        // Deletion successful
        return true;
    }
}
