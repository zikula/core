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

namespace Zikula\ExtensionsModule;

use Exception;
use Zikula\Bundle\CoreBundle\Composer\MetaData;
use Zikula\Bundle\CoreBundle\Composer\Scanner;
use Zikula\ExtensionsModule\Entity\ExtensionDependencyEntity;
use Zikula\ExtensionsModule\Entity\ExtensionEntity;
use Zikula\ExtensionsModule\Entity\ExtensionVarEntity;
use Zikula\ExtensionsModule\Installer\AbstractExtensionInstaller;

/**
 * Installation and upgrade routines for the extensions module.
 */
class ExtensionsModuleInstaller extends AbstractExtensionInstaller
{
    public function install(): bool
    {
        $entities = [
            ExtensionEntity::class,
            ExtensionDependencyEntity::class,
            ExtensionVarEntity::class,
        ];

        try {
            $this->schemaTool->create($entities);
        } catch (Exception $exception) {
            return false;
        }

        // populate default data
        $this->createDefaultData();
        $this->setVar('itemsperpage', 40);
        $this->setVar('helpUiMode', 'modal');

        // Initialisation successful
        return true;
    }

    public function upgrade(string $oldVersion): bool
    {
        // Upgrade dependent on old version number
        switch ($oldVersion) {
            case '3.7.10':
                // Load DB connection
                $connection = $this->entityManager->getConnection();

                // increase length of some hook table fields from 20 to 60
                $commands = [];
                $commands[] = 'ALTER TABLE `hook_provider` CHANGE `method` `method` VARCHAR(60) NOT NULL';
                $commands[] = 'ALTER TABLE `hook_runtime` CHANGE `method` `method` VARCHAR(60) NOT NULL';

                foreach ($commands as $sql) {
                    $connection->executeQuery($sql);
                }
            case '3.7.11':
                $this->schemaTool->update([ExtensionEntity::class]);
            case '3.7.12':
                $this->setVar('itemsperpage', 40);
            case '3.7.13':
            case '3.7.14':
                $this->schemaTool->update([ExtensionEntity::class]);
            case '3.7.15':
                // altering the 'modules' table to rename the core_min to coreCompatibility
                // is done \Zikula\ExtensionsModule\Listener\Core3UpgradeListener::upgrade
                // in the core upgrade process so that it happens before any access to the table
            case '3.7.16':
                // future upgrade routines
        }

        // Update successful
        return true;
    }

    public function uninstall(): bool
    {
        // Deletion not allowed
        return false;
    }

    /**
     * Create the default data for the extensions module.
     */
    private function createDefaultData(): void
    {
        $scanner = new Scanner();
        $jsonPath = realpath(__DIR__ . '/composer.json');
        $jsonContent = $scanner->decode($jsonPath);
        $metaData = new MetaData($jsonContent);
        $metaData->setTranslator($this->getTranslator());
        $meta = $metaData->getFilteredVersionInfoArray();
        $meta['state'] = Constant::STATE_ACTIVE;
        unset($meta['dependencies'], $meta['oldnames']);

        $entity = new ExtensionEntity();
        $entity->merge($meta);

        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }
}
