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

namespace Zikula\AdminModule;

use Zikula\AdminModule\Entity\AdminCategoryEntity;
use Zikula\AdminModule\Entity\AdminModuleEntity;
use Zikula\ExtensionsModule\Installer\AbstractExtensionInstaller;

class AdminModuleInstaller extends AbstractExtensionInstaller
{
    private $entities = [
        AdminCategoryEntity::class,
        AdminModuleEntity::class
    ];

    public function install(): bool
    {
        $this->schemaTool->create($this->entities);

        $this->setVar('modulesperrow', 3);
        $this->setVar('itemsperpage', 15);
        $this->setVar('defaultcategory', 5);
        $this->setVar('admingraphic', 1);
        $this->setVar('startcategory', 1);
        // change below to 0 before release - just makes it easier doing development meantime - drak
        // we can now leave this at 0 since the code also checks the development flag (config.php) - markwest
        $this->setVar('ignoreinstallercheck', 0);
        $this->setVar('admintheme');
        $this->setVar('displaynametype', 1);

        $this->createDefaultData();

        return true;
    }

    public function upgrade(string $oldVersion): bool
    {
        switch ($oldVersion) {
            // 2.0.0 shipped with Core-1.4.3 through Core-2.0.15
            case '2.9.9':
                // do nothing
        }

        return true;
    }

    public function uninstall(): bool
    {
        return false;
    }

    /**
     * Create the default data for the Admin module.
     */
    public function createDefaultData(): void
    {
        $records = [
            [
                'name' => $this->trans('System'),
                'description' => $this->trans('Core modules at the heart of operation of the site.'),
                'icon' => 'fas fa-cogs',
                'sortorder' => 0
            ],
            [
                'name' => $this->trans('Layout'),
                'description' => $this->trans("Layout modules for controlling the site's look and feel."),
                'icon' => 'fas fa-palette',
                'sortorder' => 1
            ],
            [
                'name' => $this->trans('Users'),
                'description' => $this->trans('Modules for controlling user membership, access rights and profiles.'),
                'icon' => 'fas fa-users-cog',
                'sortorder' => 2
            ],
            [
                'name' => $this->trans('Content'),
                'description' => $this->trans('Modules for providing content to your users.'),
                'icon' => 'fas fa-file-contract',
                'sortorder' => 3
            ],
            [
                'name' => $this->trans('Uncategorised'),
                'description' => $this->trans('Newly-installed or uncategorized modules.'),
                'icon' => 'fas fa-cubes',
                'sortorder' => 4
            ],
            [
                'name' => $this->trans('Security'),
                'description' => $this->trans('Modules for managing the site\'s security.'),
                'icon' => 'fas fa-shield-alt',
                'sortorder' => 5
            ]
        ];

        foreach ($records as $record) {
            $item = new AdminCategoryEntity();
            $item->merge($record);
            $this->entityManager->persist($item);
        }

        $this->entityManager->flush();
    }
}
