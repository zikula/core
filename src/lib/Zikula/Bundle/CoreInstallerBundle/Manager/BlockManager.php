<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreInstallerBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Zikula\BlocksModule\BlocksModuleInstaller;
use Zikula\BlocksModule\Entity\BlockEntity;
use Zikula\BlocksModule\Entity\BlockPlacementEntity;
use Zikula\BlocksModule\Entity\BlockPositionEntity;
use Zikula\ExtensionsModule\Entity\ExtensionEntity;

class BlockManager
{
    /**
     * Create the main menu block.
     */
    private function createMainMenuBlock(): void
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->container->get('doctrine')->getManager();

        /** @var ExtensionEntity $menuModuleEntity */
        $menuModuleEntity = $entityManager->getRepository('ZikulaExtensionsModule:ExtensionEntity')
            ->findOneBy(['name' => 'ZikulaMenuModule']);
        $blockEntity = new BlockEntity();
        $mainMenuString = $this->translator->__('Main menu');
        $blockEntity->setTitle($mainMenuString);
        $blockEntity->setBkey('ZikulaMenuModule:\Zikula\MenuModule\Block\MenuBlock');
        $blockEntity->setBlocktype('Menu');
        $blockEntity->setDescription($mainMenuString);
        $blockEntity->setModule($menuModuleEntity);
        $blockEntity->setProperties([
            'name' => 'mainMenu',
            'options' => '{"template": "ZikulaMenuModule:Override:bootstrap_fontawesome.html.twig"}'
        ]);
        $entityManager->persist($blockEntity);

        /** @var BlockPositionEntity $topNavPosition */
        $topNavPosition = $entityManager->getRepository('ZikulaBlocksModule:BlockPositionEntity')
            ->findOneBy(['name' => 'topnav']);
        $placement = new BlockPlacementEntity();
        $placement->setBlock($blockEntity);
        $placement->setPosition($topNavPosition);
        $placement->setSortorder(0);
        $entityManager->persist($placement);

        $entityManager->flush();
    }

    private function createBlocks(): bool
    {
        $installer = new BlocksModuleInstaller();
        $installer->setBundle($this->container->get('kernel')->getModule('ZikulaBlocksModule'));
        $installer->setContainer($this->container);
        // create the default blocks.
        $installer->createDefaultData();
        $this->createMainMenuBlock();

        return true;
    }
}
