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

namespace Zikula\MenuModule;

use Zikula\ExtensionsModule\Installer\AbstractExtensionInstaller;
use Zikula\MenuModule\Entity\MenuItemEntity;

class MenuModuleInstaller extends AbstractExtensionInstaller
{
    private $entities = [
        MenuItemEntity::class
    ];

    public function install(): bool
    {
        $this->schemaTool->create($this->entities);

        $this->createMainMenu();

        return true;
    }

    public function upgrade(string $oldVersion): bool
    {
        switch ($oldVersion) {
            case '1.0.0':
                $menuItems = $this->entityManager->getRepository(MenuItemEntity::class)->findAll();
                foreach ($menuItems as $menuItem) {
                    if ('zikulasearchmodule_user_form' === $menuItem->getOption('route')) {
                        $menuItem->setOption('route', 'zikulasearchmodule_search_execute');
                    }
                }
                $this->entityManager->flush();
            case '1.0.1': // shipped with Core-2.0.15
                $menuItems = $this->entityManager->getRepository(MenuItemEntity::class)->findAll();
                foreach ($menuItems as $menuItem) {
                    if ($menuItem->hasOption('icon')) {
                        $iconClass = (string) $menuItem->getOption('icon');
                        $menuItem->setOption('icon', 'fas' . mb_substr($iconClass, 3));
                    }
                }
                $this->entityManager->flush();
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
        $home->setTitle($this->trans('Home'));
        $home->setOptions([
            'route' => 'home',
            'attributes' => [
                'icon' => 'fas fa-home'
            ]
        ]);

        $search = new MenuItemEntity();
        $search->setParent($root);
        $search->setTitle($this->trans('Site search'));
        $search->setOptions([
            'route' => 'zikulasearchmodule_search_execute',
            'attributes' => [
                'icon' => 'fas fa-search'
            ]
        ]);

        $this->entityManager->persist($root);
        $this->entityManager->persist($home);
        $this->entityManager->persist($search);
        $this->entityManager->flush();
    }
}
