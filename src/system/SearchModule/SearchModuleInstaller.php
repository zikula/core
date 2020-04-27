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

namespace Zikula\SearchModule;

use Zikula\ExtensionsModule\Installer\AbstractExtensionInstaller;
use Zikula\SearchModule\Entity\SearchResultEntity;
use Zikula\SearchModule\Entity\SearchStatEntity;

class SearchModuleInstaller extends AbstractExtensionInstaller
{
    private $entities = [
        SearchResultEntity::class,
        SearchStatEntity::class
    ];

    public function install(): bool
    {
        $this->schemaTool->create($this->entities);

        // create module vars
        $this->setVar('itemsperpage', 10);
        $this->setVar('limitsummary', 255);
        $this->setVar('opensearch_enabled', true);
        $this->setVar('opensearch_adult_content', false);

        return true;
    }

    public function upgrade(string $oldVersion): bool
    {
        switch ($oldVersion) {
            case '1.6.0': // shipped with Core 1.4.3 through Core-2.0.15
                // update schema since extra field has been changed from text to array
                $this->entityManager->getRepository('ZikulaSearchModule:SearchResultEntity')->truncateTable();
                $this->schemaTool->update([
                    SearchResultEntity::class
                ]);
        }

        return true;
    }

    public function uninstall(): bool
    {
        // Deletion not allowed
        return false;
    }
}
