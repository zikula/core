<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SearchModule;

use Zikula\Core\AbstractExtensionInstaller;

/**
 * installation routines for the search module
 */
class SearchModuleInstaller extends AbstractExtensionInstaller
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
            $this->schemaTool->create([
                'Zikula\SearchModule\Entity\SearchResultEntity',
                'Zikula\SearchModule\Entity\SearchStatEntity',
            ]);
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
     * @param  string $oldVersion version number string to upgrade from
     *
     * @return bool|string true on success, last valid version string or false if fails
     */
    public function upgrade($oldVersion)
    {
        // Upgrade dependent on old version number
        switch ($oldVersion) {
            case '1.5.2':
                $this->setVar('opensearch_enabled', true);
                $this->setVar('opensearch_adult_content', false);

                // update schema
                try {
                    $this->schemaTool->update([
                        'Zikula\SearchModule\Entity\SearchResultEntity',
                    ]);
                } catch (\Exception $e) {
                    $this->addFlash('error', $e->getMessage());

                    return false;
                }
            case '1.5.3':
                // update schema
                try {
                    $this->schemaTool->update([
                        'Zikula\SearchModule\Entity\SearchResultEntity',
                    ]);
                } catch (\Exception $e) {
                    $this->addFlash('error', $e->getMessage());

                    return false;
                }
            case '1.5.4':
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
            $this->schemaTool->drop([
                'Zikula\SearchModule\Entity\SearchResultEntity',
                'Zikula\SearchModule\Entity\SearchStatEntity',
            ]);
        } catch (\Exception $e) {
            return false;
        }

        // Delete any module variables
        $this->delVars();

        // Deletion successful
        return true;
    }
}
