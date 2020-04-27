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

namespace Zikula\PermissionsModule;

use Zikula\ExtensionsModule\Installer\AbstractExtensionInstaller;
use Zikula\PermissionsModule\Entity\PermissionEntity;

class PermissionsModuleInstaller extends AbstractExtensionInstaller
{
    public function install(): bool
    {
        $this->schemaTool->create([
            PermissionEntity::class
        ]);

        $this->createDefaultData();

        return true;
    }

    public function upgrade(string $oldVersion): bool
    {
        // Upgrade dependent on old version number
        switch ($oldVersion) {
            case '1.2.0': // shipped with Core-1.4.3 through Core-2.0.15
                $this->delVar('rowview');
                $this->delVar('rowedit');
        }

        return true;
    }

    public function uninstall(): bool
    {
        // Deletion not allowed
        return false;
    }

    /**
     * Create the default data for the Permissions module.
     */
    public function createDefaultData(): void
    {
        // give administrator group full access to everything as top priority
        $record = new PermissionEntity();
        $record['gid']       = 2;
        $record['sequence']  = 1;
        $record['component'] = '.*';
        $record['instance']  = '.*';
        $record['level']     = ACCESS_ADMIN; // 800
        $this->entityManager->persist($record);

        // give user group comment access to everything as second priority
        $record = new PermissionEntity();
        $record['gid']       = 1;
        $record['sequence']  = 2;
        $record['component'] = '.*';
        $record['instance']  = '.*';
        $record['level']     = ACCESS_COMMENT; // 300
        $this->entityManager->persist($record);

        // allow unregistered users only read access to everything as lowest priority
        $record = new PermissionEntity();
        $record['gid']       = 0;
        $record['sequence']  = 3;
        $record['component'] = '.*';
        $record['instance']  = '.*';
        $record['level']     = ACCESS_READ; // 200
        $this->entityManager->persist($record);

        $this->entityManager->flush();

        $this->setVar('lockadmin', 1);
        $this->setVar('adminid', 1);
        $this->setVar('filter', 1);
    }
}
