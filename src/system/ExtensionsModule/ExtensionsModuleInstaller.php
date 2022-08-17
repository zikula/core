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
use Zikula\ExtensionsModule\Entity\ExtensionVarEntity;
use Zikula\ExtensionsModule\Installer\AbstractExtensionInstaller;

class ExtensionsModuleInstaller extends AbstractExtensionInstaller
{
    private $entities = [
        ExtensionVarEntity::class,
    ];

    public function install(): bool
    {
        $this->schemaTool->create($this->entities);

        return true;
    }

    public function upgrade(string $oldVersion): bool
    {
        switch ($oldVersion) {
            // 3.7.13 shipped with Core-1.4.3
            // 3.7.15 shipped with Core-2.0.15
            // version number reset to 3.0.0 at Core 3.0.0
            case '2.9.9':
                // nothing
        }

        return true;
    }

    public function uninstall(): bool
    {
        // Deletion not allowed
        return false;
    }
}
