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

namespace Zikula\BlocksModule;

use Zikula\BlocksModule\Block\HtmlBlock;
use Zikula\BlocksModule\Entity\BlockEntity;
use Zikula\BlocksModule\Entity\BlockPlacementEntity;
use Zikula\BlocksModule\Entity\BlockPositionEntity;
use Zikula\ExtensionsModule\Entity\ExtensionEntity;
use Zikula\ExtensionsModule\Installer\AbstractExtensionInstaller;
use Zikula\SearchModule\Block\SearchBlock;
use Zikula\UsersModule\Block\LoginBlock;

class BlocksModuleInstaller extends AbstractExtensionInstaller
{
    private $entities = [
        BlockEntity::class,
        BlockPositionEntity::class,
        BlockPlacementEntity::class
    ];

    public function install(): bool
    {
        $this->schemaTool->create($this->entities);
        $this->setVar('collapseable', false);

        return true;
    }

    public function upgrade(string $oldVersion): bool
    {
        switch ($oldVersion) {
            // 3.9.6 shipped with Core-1.4.3
            // 3.9.8 shipped with Core-2.0.15
            // version number reset to 3.0.0 at Core 3.0.0
            case '2.9.9':
                $statement = $this->entityManager->getConnection()->executeQuery("SELECT * FROM blocks WHERE blocktype = 'Lang'");
                $blocks = $statement->fetchAll(\PDO::FETCH_ASSOC);
                if (count($blocks) > 0) {
                    $this->entityManager->getConnection()->executeQuery("UPDATE blocks set bkey=?, blocktype=?, properties=? WHERE blocktype = 'Lang'", [
                        'ZikulaSettingsModule:Zikula\SettingsModule\Block\LocaleBlock',
                        'Locale',
                        'a:0:{}'
                    ]);
                    $this->addFlash('success', 'All instances of LangBlock have been converted to LocaleBlock.');
                }
                $this->entityManager->getConnection()->executeQuery("UPDATE group_perms SET component = REPLACE(component, 'Languageblock', 'LocaleBlock') WHERE component LIKE 'Languageblock%'");
                $statement = $this->entityManager->getConnection()->executeQuery("SELECT * FROM blocks");
                $blocks = $statement->fetchAll(\PDO::FETCH_ASSOC);
                foreach ($blocks as $block) {
                    $bKey = $block['bkey'];
                    if (mb_strpos($bKey, ':')) {
                        [/*$moduleName*/, $bKey] = explode(':', $bKey);
                    }
                    $this->entityManager->getConnection()->executeUpdate('UPDATE blocks SET bKey=? WHERE bid=?', [trim($bKey, '\\'), $block['bid']]);
                }
        }

        return true;
    }

    public function uninstall(): bool
    {
        // Deletion not allowed
        return false;
    }

    /**
     * Add default block data for new installations.
     * This is called after a complete installation since the blocks
     * need to be populated with module id's which are only available
     * once the installation has been completed.
     */
    public function createDefaultData(): void
    {
        // create the default block positions - left, right and center for the traditional 3 column layout
        $positions = [
            'left' => $this->trans('Left blocks'),
            'right' => $this->trans('Right blocks'),
            'center' => $this->trans('Center blocks'),
            'search' => $this->trans('Search block'),
            'header' => $this->trans('Header block'),
            'footer' => $this->trans('Footer block'),
            'topnav' => $this->trans('Top navigation block'),
            'bottomnav' => $this->trans('Bottom navigation block')
        ];
        foreach ($positions as $name => $description) {
            $positions[$name] = new BlockPositionEntity();
            $positions[$name]->setName($name);
            $positions[$name]->setDescription($description);
            $this->entityManager->persist($positions[$name]);
        }
        $this->entityManager->flush();

        $hellomessage = $this->trans('<p><a href="https://ziku.la">Zikula</a> is an Open Source Content Application Framework built on top of Symfony.</p><p>With Zikula you get:</p><ul><li><strong>Power:</strong> You get the all the features of <a href="https://symfony.com">Symfony</a> PLUS: </li><li><strong>User Management:</strong> Built in User and Group management with Rights/Roles control</li><li><strong>Front end control:</strong> You can customise all aspects of the site\'s appearance through themes, with support for <a href="http://jquery.com">jQuery</a>, <a href="http://getbootstrap.com">Bootstrap</a> and many other modern technologies</li><li><strong>Internationalization (i18n):</strong> You can mark content as being suitable for either a single language or for all languages, and can control all aspects of localisation of your site</li><li><strong>Extensibility:</strong> you get a standard application-programming interface (API) that lets you easily extend your site\'s functionality through modules</li><li><strong>More:</strong> Admin UI, global categories, site-wide search, content blocks, menu creation, and more!</li><li><strong>Support:</strong> you can get help and support from the Zikula community of webmasters and developers at <a href="https://ziku.la">ziku.la</a>, <a href="https://github.com/zikula/core">Github</a> and <a href="https://zikula.slack.com/">Slack</a>.</li></ul><p>Enjoy using Zikula!</p><p><strong>The Zikula team</strong></p><p><em>Note: Zikula is Free Open Source Software (FOSS) licensed under the GNU General Public License.</em></p>');

        $blocks = [];
        $extensionRepo = $this->entityManager->getRepository(ExtensionEntity::class);
        $blocksModuleEntity = $extensionRepo->findOneBy(['name' => 'ZikulaBlocksModule']);
        $searchModuleEntity = $extensionRepo->findOneBy(['name' => 'ZikulaSearchModule']);
        $usersModuleEntity = $extensionRepo->findOneBy(['name' => 'ZikulaUsersModule']);
        $blocks[] = [
            'bkey' => SearchBlock::class,
            'blocktype' => 'Search',
            'language' => '',
            'module' => $searchModuleEntity,
            'title' => $this->trans('Search box'),
            'description' => $this->trans('Search block'),
            'properties' => [
                'displaySearchBtn' => true,
                'active' => ['ZikulaUsersModule' => 1]
            ],
            'position' => $positions['left']
        ];
        $blocks[] = [
            'bkey' => HtmlBlock::class,
            'blocktype' => 'Html',
            'language' => '',
            'module' => $blocksModuleEntity,
            'title' => $this->trans('This site is powered by Zikula!'),
            'description' => $this->trans('HTML block'),
            'properties' => ['content' => $hellomessage],
            'position' => $positions['center']
        ];
        $blocks[] = [
            'bkey' => LoginBlock::class,
            'blocktype' => 'Login',
            'language' => '',
            'module' => $usersModuleEntity,
            'title' => $this->trans('User log-in'),
            'description' => $this->trans('Login block'),
            'position' => $positions['topnav'],
            'order' => 1,
            'filters' => [[
                'attribute' => '_route',
                'queryParameter' => null,
                'comparator' => '!=',
                'value' => 'zikulausersmodule_access_login'
            ]]
        ];

        foreach ($blocks as $block) {
            $blockEntity = new BlockEntity();
            $position = $block['position'];
            $sortOrder = !empty($block['order']) ? $block['order'] : 0;
            unset($block['position'], $block['order']);
            $blockEntity->merge($block);
            $this->entityManager->persist($blockEntity);
            $placement = new BlockPlacementEntity();
            $placement->setBlock($blockEntity);
            $placement->setPosition($position);
            $placement->setSortorder($sortOrder);
            $this->entityManager->persist($placement);
        }
        $this->entityManager->flush();
    }
}
