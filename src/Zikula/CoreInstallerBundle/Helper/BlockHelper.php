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

namespace Zikula\Bundle\CoreInstallerBundle\Helper;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\BlocksModule\BlocksModuleInstaller;
use Zikula\BlocksModule\Entity\BlockEntity;
use Zikula\BlocksModule\Entity\BlockPlacementEntity;
use Zikula\BlocksModule\Entity\BlockPositionEntity;
use Zikula\ExtensionsModule\Collector\InstallerCollector;
use Zikula\ExtensionsModule\Entity\ExtensionEntity;
use Zikula\MenuModule\Block\MenuBlock;

class BlockHelper
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var InstallerCollector
     */
    private $installerCollector;

    public function __construct(
        EntityManagerInterface $entityManager,
        TranslatorInterface $translator,
        InstallerCollector $installerCollector
    ) {
        $this->entityManager = $entityManager;
        $this->translator = $translator;
        $this->installerCollector = $installerCollector;
    }

    public function createBlocks(): bool
    {
        $installer = $this->installerCollector->get(BlocksModuleInstaller::class);
        $installer->createDefaultData();
        $this->createMainMenuBlock();

        return true;
    }

    /**
     * Create the main menu block.
     */
    private function createMainMenuBlock(): void
    {
        /** @var ExtensionEntity $menuModuleEntity */
        $menuModuleEntity = $this->entityManager->getRepository(ExtensionEntity::class)
            ->findOneBy(['name' => 'ZikulaMenuModule']);
        $blockEntity = new BlockEntity();
        $mainMenuString = $this->translator->trans('Main menu');
        $blockEntity->setTitle($mainMenuString);
        $blockEntity->setBkey(MenuBlock::class);
        $blockEntity->setBlocktype('Menu');
        $blockEntity->setDescription($mainMenuString);
        $blockEntity->setModule($menuModuleEntity);
        $blockEntity->setProperties([
            'name' => 'mainMenu',
            'options' => '{"template": "@ZikulaMenuModule/Override/bootstrap_fontawesome.html.twig"}'
        ]);
        $this->entityManager->persist($blockEntity);

        /** @var BlockPositionEntity $topNavPosition */
        $topNavPosition = $this->entityManager->getRepository(BlockPositionEntity::class)
            ->findOneBy(['name' => 'topnav']);
        $placement = new BlockPlacementEntity();
        $placement->setBlock($blockEntity);
        $placement->setPosition($topNavPosition);
        $placement->setSortorder(0);
        $this->entityManager->persist($placement);

        $this->entityManager->flush();
    }
}
