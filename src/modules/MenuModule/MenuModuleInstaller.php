<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\MenuModule;

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
        'Zikula\MenuModule\Entity\MenuItemEntity'
    ];

    /**
     * Initialise the module.
     *
     * @return boolean True if initialisation successful, false otherwise
     */
    public function install()
    {
        try {
            $this->schemaTool->create($this->entities);
        } catch (\Exception $e) {
            return false;
        }
        $this->createDemoData();

        return true;
    }

    /**
     * upgrade the module from an old version
     *
     * @param string $oldVersion version number string to upgrade from
     *
     * @return bool true as there are no upgrade routines currently
     */
    public function upgrade($oldVersion)
    {
        // Upgrade dependent on old version number
        switch ($oldVersion) {
            case '1.0.0':
                // current version
        }

        return true;
    }

    /**
     * delete the module
     *
     * @return bool true if deletion successful, false otherwise
     */
    public function uninstall()
    {
        $this->schemaTool->drop($this->entities); // @todo remove

        return true; // @todo change to false
    }

    /**
     * Create a demo menu
     */
    private function createDemoData()
    {
        $root = new MenuItemEntity();
        $root->setTitle('home');
        $root->setOptions([
            'childrenAttributes' => [
                'class' => 'nav navbar-nav'
            ]]);

        $modules = new MenuItemEntity();
        $modules->setTitle('Modules');
        $modules->setParent($root);
        $modules->setOptions([
            'attributes' => [
                'icon' => 'fa fa-list',
                'dropdown' => true
            ]]);

        $users = new MenuItemEntity();
        $users->setParent($modules);
        $users->setTitle('UsersModule');
        $users->setOptions([
            'route' => 'zikulausersmodule_useradministration_list',
            'attributes' => [
                'icon' => 'fa fa-users'
            ]
        ]);

        $blocks = new MenuItemEntity();
        $blocks->setParent($modules);
        $blocks->setTitle('BlocksModule');
        $blocks->setOptions([
            'route' => 'zikulablocksmodule_admin_view',
            'attributes' => [
                'icon' => 'fa fa-cubes'
            ]
        ]);

        $zAuth = new MenuItemEntity();
        $zAuth->setParent($modules);
        $zAuth->setTitle('ZAuthModule');
        $zAuth->setOptions([
            'route' => 'zikulazauthmodule_useradministration_list',
            'attributes' => [
                'icon' => 'fa fa-user'
            ]
        ]);

        $this->entityManager->persist($root);
        $this->entityManager->persist($modules);
        $this->entityManager->persist($users);
        $this->entityManager->persist($blocks);
        $this->entityManager->persist($zAuth);
        $this->entityManager->flush();
    }
}
