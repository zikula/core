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

namespace Zikula\BlocksModule;

use Exception;
use Zikula\BlocksModule\Block\HtmlBlock;
use Zikula\BlocksModule\Entity\BlockEntity;
use Zikula\BlocksModule\Entity\BlockPlacementEntity;
use Zikula\BlocksModule\Entity\BlockPositionEntity;
use Zikula\BlocksModule\Helper\InstallerHelper;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\ExtensionsModule\Entity\ExtensionEntity;
use Zikula\ExtensionsModule\Installer\AbstractExtensionInstaller;
use Zikula\SearchModule\Block\SearchBlock;
use Zikula\UsersModule\Block\LoginBlock;

/**
 * Installation and upgrade routines for the blocks module.
 */
class BlocksModuleInstaller extends AbstractExtensionInstaller
{
    /**
     * @var array
     */
    private $entities = [
        BlockEntity::class,
        BlockPositionEntity::class,
        BlockPlacementEntity::class
    ];

    public function install(): bool
    {
        try {
            $this->schemaTool->create($this->entities);
        } catch (Exception $exception) {
            return false;
        }

        // Set a default value for a module variable
        $this->setVar('collapseable', false);

        // Initialisation successful
        return true;
    }

    public function upgrade(string $oldVersion): bool
    {
        $blockRepository = $this->entityManager->getRepository(BlockEntity::class);
        // Upgrade dependent on old version number
        switch ($oldVersion) {
            case '3.8.1':
            case '3.8.2':
            case '3.9.0':
                $sql = 'SELECT * FROM blocks';
                $blocks = $this->entityManager->getConnection()->fetchAll($sql);
                foreach ($blocks as $block) {
                    $content = $block['content'];
                    if ($this->isSerialized($content)) {
                        $content = unserialize($content);
                        foreach ($content as $k => $item) {
                            if (is_string($item)) {
                                if (false !== mb_strpos($item, 'blocks_block_extmenu_topnav.tpl')) {
                                    $content[$k] = str_replace('blocks_block_extmenu_topnav.tpl', 'Block/Extmenu/topnav.tpl', $item);
                                } elseif (false !== mb_strpos($item, 'blocks_block_extmenu.tpl')) {
                                    $content[$k] = str_replace('blocks_block_extmenu.tpl', 'Block/Extmenu/extmenu.tpl', $item);
                                } elseif (false !== mb_strpos($item, 'menutree/blocks_block_menutree_')) {
                                    $content[$k] = str_replace('menutree/blocks_block_menutree_', 'Block/Menutree/', $item);
                                }
                            }
                        }
                        $this->entityManager->getConnection()->executeUpdate('UPDATE blocks SET content=? WHERE bid=?', [serialize($content), $block['bid']]);
                    }
                }

                // check if request is available (#2073)
                $templateWarning = $this->trans('Warning: Block template locations modified, you may need to fix your template overrides if you have any.');
                if (
                    is_object($this->container->get('request'))
                    && method_exists($this->container->get('request'), 'getSession')
                    && is_object($this->container->get('request')->getSession())
                ) {
                    $this->addFlash('warning', $templateWarning);
                }
            case '3.9.1':
                // make all content fields of blocks serialized.
                $sql = 'SELECT * FROM blocks';
                $blocks = $this->entityManager->getConnection()->fetchAll($sql);
                $oldContent = [];
                foreach ($blocks as $block) {
                    $block['content'] = !empty($block['content']) ? $block['content'] : '';
                    $oldContent[$block['bid']] = $this->isSerialized($block['content']) ? unserialize($block['content']) : ['content' => $block['content']];
                }
                $this->schemaTool->update($this->entities);
                $this->entityManager->getConnection()->executeQuery("UPDATE blocks SET properties='a:0:{}'");

                $blocks = $blockRepository->findAll();
                $installerHelper = new InstallerHelper();
                /** @var ZikulaHttpKernelInterface $kernel */
                $kernel = $this->container->get('kernel');
                /** @var BlockEntity $block */
                foreach ($blocks as $block) {
                    $block->setProperties($oldContent[$block->getBid()]);
                    $block->setFilters($installerHelper->upgradeFilterArray($block->getFilters()));
                    $block->setBlocktype(preg_match('/Block$/', $block->getBkey()) ? mb_substr($block->getBkey(), 0, -5) : $block->getBkey());
                    $block->setBkey($installerHelper->upgradeBkeyToFqClassname($kernel, $block));
                }
                $this->entityManager->flush();

                $collapseable = $this->getVar('collapseable');
                $this->setVar('collapseable', (bool)$collapseable);

            case '3.9.2':
                // convert Text and Html block types so properties is proper array
                $blocks = $blockRepository->findBy(['blocktype' => ['Html', 'Text']]);
                foreach ($blocks as $block) {
                    $properties = $block->getProperties();
                    if (!is_array($properties)) {
                        $block->setProperties(['content' => $properties]);
                    }
                }
                $this->entityManager->flush();
            case '3.9.3':
                $this->schemaTool->drop([
                    'Zikula\BlocksModule\Entity\UserBlockEntity'
                ]);
            case '3.9.4':
                // convert integer values to boolean for search block settings
                $searchBlocks = $blockRepository->findBy(['blocktype' => 'Search']);
                foreach ($searchBlocks as $searchBlock) {
                    $properties = $searchBlock->getProperties();
                    $properties['displaySearchBtn'] = (bool)$properties['displaySearchBtn'];
                    if (isset($properties['active'])) {
                        foreach ($properties['active'] as $module => $active) {
                            $properties['active'][$module] = (bool)$active;
                        }
                    }
                    $searchBlock->setProperties($properties);
                }
                $this->entityManager->flush();
            case '3.9.5':
                $loginBlocks = $blockRepository->findBy(['blocktype' => 'Login']);
                foreach ($loginBlocks as $loginBlock) {
                    $filters = $loginBlock->getFilters();
                    $filters[] = [
                        'attribute' => '_route',
                        'queryParameter' => null,
                        'comparator' => '!=',
                        'value' => 'zikulausersmodule_access_login'
                    ];
                    $loginBlock->setFilters($filters);
                }
                $this->entityManager->flush();
            case '3.9.6':
                $blocks = $this->entityManager->getConnection()->executeQuery("SELECT * FROM blocks WHERE blocktype = 'Lang'");
                if (count($blocks) > 0) {
                    $this->entityManager->getConnection()->executeQuery("UPDATE blocks set bkey=?, blocktype=?, properties=? WHERE blocktype = 'Lang'", [
                        'ZikulaSettingsModule:Zikula\SettingsModule\Block\LocaleBlock',
                        'Locale',
                        'a:0:{}'
                    ]);
                    $this->addFlash('success', 'All instances of LangBlock have been converted to LocaleBlock.');
                }
                $this->entityManager->getConnection()->executeQuery("UPDATE group_perms SET component = REPLACE(component, 'Languageblock', 'LocaleBlock') WHERE component LIKE 'Languageblock%'");
            case '3.9.7':
            case '3.9.8':
                $blocks = $this->entityManager->getConnection()->executeQuery("SELECT * FROM blocks");
                foreach ($blocks as $block) {
                    $bKey = $block['bkey'];
                    if (mb_strpos($bKey, ':')) {
                        [/*$moduleName*/, $bKey] = explode(':', $bKey);
                    }
                    $this->entityManager->getConnection()->executeUpdate('UPDATE blocks SET bKey=? WHERE bid=?', [trim($bKey, '\\'), $block['bid']]);
                }
            case '3.9.9':
            // future upgrade routines
        }

        // Update successful
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

    private function isSerialized($string): bool
    {
        return 'b:0;' === $string || false !== @unserialize($string);
    }
}
