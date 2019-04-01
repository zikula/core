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

namespace Zikula\MenuModule;

use Exception;
use Zikula\Core\AbstractExtensionInstaller;
use Zikula\MenuModule\Entity\MenuItemEntity;

/**
 * Installation and upgrade routines for the menu module.
 */
class MenuModuleInstaller extends AbstractExtensionInstaller
{
    /**
     * @var array
     */
    private $entities = [
        MenuItemEntity::class
    ];

    public function install(): bool
    {
        try {
            $this->schemaTool->create($this->entities);
        } catch (Exception $exception) {
            return false;
        }
        $this->createMainMenu();

        return true;
    }

    public function upgrade($oldVersion): bool
    {
        // Upgrade dependent on old version number
        switch ($oldVersion) {
            case '1.0.0':
                $menuItems = $this->entityManager->getRepository(MenuItemEntity::class)->findAll();
                foreach ($menuItems as $menuItem) {
                    if ('zikulasearchmodule_user_form' === $menuItem->getOption('route')) {
                        $menuItem->setOption('route', 'zikulasearchmodule_search_execute');
                    }
                }
                $this->entityManager->flush();
            case '1.0.1':
                // current version
        }

        return true;
    }

    public function uninstall(): bool
    {
        // cannot delete core modules
        return false;
    }

    /**
     * Create a demo menu.
     */
    private function createMainMenu(): void
    {
        $root = new MenuItemEntity();
        $root->setTitle('mainMenu');
        $root->setOptions([
            'childrenAttributes' => [
                'class' => 'nav navbar-nav'
            ]]);

        $home = new MenuItemEntity();
        $home->setParent($root);
        $home->setTitle($this->__('Home'));
        $home->setOptions([
            'route' => 'home',
            'attributes' => [
                'icon' => 'fa fa-home'
            ]
        ]);

        $search = new MenuItemEntity();
        $search->setParent($root);
        $search->setTitle($this->__('Site search'));
        $search->setOptions([
            'route' => 'zikulasearchmodule_search_execute',
            'attributes' => [
                'icon' => 'fa fa-search'
            ]
        ]);

        $this->entityManager->persist($root);
        $this->entityManager->persist($home);
        $this->entityManager->persist($search);
        $this->entityManager->flush();
    }
}
