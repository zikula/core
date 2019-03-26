<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SearchModule;

use Zikula\Core\AbstractExtensionInstaller;
use Zikula\SearchModule\Entity\SearchResultEntity;
use Zikula\SearchModule\Entity\SearchStatEntity;

/**
 * Installation routines for the search module.
 */
class SearchModuleInstaller extends AbstractExtensionInstaller
{
    /**
     * @var array
     */
    private $entities = [
        SearchResultEntity::class,
        SearchStatEntity::class
    ];

    /**
     * Initialise the search module.
     *
     * This function is only ever called once during the lifetime of a particular
     * module instance
     *
     * @return boolean True if initialisation successful, false otherwise
     */
    public function install()
    {
        // create schema
        try {
            $this->schemaTool->create($this->entities);
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
     * @param string $oldVersion version number string to upgrade from
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
                        SearchResultEntity::class
                    ]);
                } catch (\Exception $exception) {
                    $this->addFlash('error', $exception->getMessage());

                    return false;
                }
            case '1.5.3':
                // update schema
                try {
                    $this->schemaTool->update([SearchResultEntity::class]);
                } catch (\Exception $exception) {
                    $this->addFlash('error', $exception->getMessage());

                    return false;
                }
            case '1.5.4':
                // nothing
            case '1.6.0':
                // update schema since extra field has been changed from text to array
                $this->entityManager->getRepository('ZikulaSearchModule:SearchResultEntity')->truncateTable();
                try {
                    $this->schemaTool->update([SearchResultEntity::class]);
                } catch (\Exception $exception) {
                    $this->addFlash('error', $exception->getMessage());

                    return false;
                }
            case '1.6.1':
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
            $this->schemaTool->drop($this->entities);
        } catch (\Exception $e) {
            return false;
        }

        // Delete any module variables
        $this->delVars();

        // Deletion successful
        return true;
    }
}
