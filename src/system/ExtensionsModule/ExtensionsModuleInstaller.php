<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ExtensionsModule;

use Zikula\Bundle\CoreBundle\Composer\MetaData;
use Zikula\Bundle\CoreBundle\Composer\Scanner;
use Zikula\ExtensionsModule\Entity\ExtensionDependencyEntity;
use Zikula\ExtensionsModule\Entity\ExtensionEntity;
use Zikula\ExtensionsModule\Entity\ExtensionVarEntity;
use Zikula\ExtensionsModule\Installer\AbstractExtensionInstaller;

class ExtensionsModuleInstaller extends AbstractExtensionInstaller
{
    private $entities = [
        ExtensionEntity::class,
        ExtensionDependencyEntity::class,
        ExtensionVarEntity::class,
    ];

    public function install(): bool
    {
        $this->schemaTool->create($this->entities);

        // populate default data
        $this->createDefaultData();
        $this->setVar('itemsperpage', 40);
        $this->setVar('helpUiMode', 'modal');

        return true;
    }

    public function upgrade(string $oldVersion): bool
    {
        switch ($oldVersion) {
            // 3.7.13 shipped with Core-1.4.3
            // 3.7.15 shipped with Core-2.0.15
            // version number reset to 3.0.0 at Core 3.0.0
            case '2.9.9':
                $this->schemaTool->update([ExtensionEntity::class]);
        }

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
