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
                $root = new MenuItemEntity();
                $root->setTitle('home');
                $users = new MenuItemEntity();
                $users->setParent($root);
                $users->setTitle('UsersModule');
                $users->setOptions([
                    'route' => 'zikulausersmodule_useradministration_list',
                    'icon' => 'fa fa-user'
                ]);
                $blocks = new MenuItemEntity();
                $blocks->setParent($root);
                $blocks->setTitle('BlocksModule');
                $blocks->setOptions([
                    'route' => 'zikulablocksmodule_admin_view'
                ]);
                $zAuth = new MenuItemEntity();
                $zAuth->setParent($users);
                $zAuth->setTitle('ZAuthModule');
                $zAuth->setOptions([
                    'route' => 'zikulazauthmodule_useradministration_list'
                ]);
                $this->entityManager->persist($root);
                $this->entityManager->persist($users);
                $this->entityManager->persist($blocks);
                $this->entityManager->persist($zAuth);
                $this->entityManager->flush();
            case '1.0.1':
                $this->schemaTool->update($this->entities);
                $root = $this->entityManager->getRepository('ZikulaMenuModule:MenuItemEntity')->findOneBy(['title' => 'home']);
                $root->setAttributes([
                    'class' => 'nav navbar-nav'
                ]);
                $users = $this->entityManager->getRepository('ZikulaMenuModule:MenuItemEntity')->findOneBy(['title' => 'UsersModule']);
                $users->removeOption('icon');
                $users->setAttribute('icon', 'fa fa-users');
                $modules = new MenuItemEntity();
                $modules->setTitle('Modules');
                $modules->setParent($root);
                $modules->setAttributes([
                    'icon' => 'fa fa-list',
                    'dropdown' => true
                ]);
                $this->entityManager->persist($modules);
                $this->entityManager->flush();
                $users->setParent($modules);
                $blocks = $this->entityManager->getRepository('ZikulaMenuModule:MenuItemEntity')->findOneBy(['title' => 'BlocksModule']);
                $blocks->setAttribute('icon', 'fa fa-cubes');
                $blocks->setParent($modules);
                $this->entityManager->flush();
            case '1.0.2':
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
        return true; // @todo change to false
    }
}
