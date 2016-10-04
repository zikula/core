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

use Zikula\BlocksModule\Entity\BlockEntity;
use Zikula\BlocksModule\Entity\BlockPlacementEntity;
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
        $this->createMainMenu();

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
        return false; // cannot delete core modules
    }

    /**
     * Create a demo menu
     */
    private function createMainMenu()
    {
        // Create the Main Menu
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
            'route' => 'zikulasearchmodule_user_form',
            'attributes' => [
                'icon' => 'fa fa-search'
            ]
        ]);

        $this->entityManager->persist($root);
        $this->entityManager->persist($home);
        $this->entityManager->persist($search);
        $this->entityManager->flush();

        // Create the Main Menu Block
        $blocksModuleEntity = $this->entityManager->getRepository('ZikulaExtensionsModule:ExtensionEntity')->findOneBy(['name' => 'ZikulaMenuModule']);
        $blockEntity = new BlockEntity();
        $blockEntity->setTitle($this->__('Main menu'));
        $blockEntity->setBkey('ZikulaMenuModule:\Zikula\MenuModule\Block\MenuBlock');
        $blockEntity->setBlocktype('Menu');
        $blockEntity->setDescription($this->__('Main menu'));
        $blockEntity->setModule($blocksModuleEntity);
        $blockEntity->setProperties([
            'name' => 'mainMenu',
            'options' => '{"template": "ZikulaMenuModule:Override:bootstrap_fontawesome.html.twig"}'
        ]);
        $this->entityManager->persist($blockEntity);

        $topNavPosition = $this->entityManager->getRepository('ZikulaBlocksModule:BlockPositionEntity')->findOneBy(['name' => 'topnav']);
        $placement = new BlockPlacementEntity();
        $placement->setBlock($blockEntity);
        $placement->setPosition($topNavPosition);
        $placement->setSortorder(0);
        $this->entityManager->persist($placement);

        $this->entityManager->flush();
    }
}
