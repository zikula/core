<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\BlocksModule;

use Zikula\BlocksModule\Entity\BlockEntity;
use Zikula\BlocksModule\Entity\BlockPlacementEntity;
use Zikula\BlocksModule\Entity\BlockPositionEntity;
use Zikula\BlocksModule\Helper\InstallerHelper;
use Zikula\Core\AbstractExtensionInstaller;
use ZLanguage;

/**
 * Installation and upgrade routines for the blocks module.
 */
class BlocksModuleInstaller extends AbstractExtensionInstaller
{
    /**
     * @var array
     */
    private $entities = [
        'Zikula\BlocksModule\Entity\BlockEntity',
        'Zikula\BlocksModule\Entity\BlockPositionEntity',
        'Zikula\BlocksModule\Entity\BlockPlacementEntity',
    ];

    /**
     * initialise the blocks module
     *
     * @return bool true on success, false otherwise
     */
    public function install()
    {
        try {
            $this->schemaTool->create($this->entities);
        } catch (\Exception $e) {
            return false;
        }

        // Set a default value for a module variable
        $this->setVar('collapseable', false);

        $this->hookApi->installSubscriberHooks($this->bundle->getMetaData());

        // Initialisation successful
        return true;
    }

    /**
     * upgrade the blocks module
     *
     * @param string $oldversion version being upgraded
     *
     * @return bool true if successful, false otherwise
     */
    public function upgrade($oldversion)
    {
        // Upgrade dependent on old version number
        switch ($oldversion) {
            case '3.8.1':
                $this->hookApi->installSubscriberHooks($this->bundle->getMetaData());
            case '3.8.2':
            case '3.9.0':
                $blocks = $this->entityManager->getRepository('ZikulaBlocksModule:BlockEntity')->findAll();
                /** @var \Zikula\BlocksModule\Entity\BlockEntity $block */
                foreach ($blocks as $block) {
                    $content = $block->getContent();
                    if (\DataUtil::is_serialized($content)) {
                        $content = unserialize($content);
                        foreach ($content as $k => $item) {
                            if (is_string($item)) {
                                if (strpos($item, 'blocks_block_extmenu_topnav.tpl') !== false) {
                                    $content[$k] = str_replace('blocks_block_extmenu_topnav.tpl', 'Block/Extmenu/topnav.tpl', $item);
                                } elseif (strpos($item, 'blocks_block_extmenu.tpl') !== false) {
                                    $content[$k] = str_replace('blocks_block_extmenu.tpl', 'Block/Extmenu/extmenu.tpl', $item);
                                } elseif (strpos($item, 'menutree/blocks_block_menutree_') !== false) {
                                    $content[$k] = str_replace('menutree/blocks_block_menutree_', 'Block/Menutree/', $item);
                                }
                            }
                        }
                        $block->setContent(serialize($content));
                    }
                }
                $this->entityManager->flush();

                // check if request is available (#2073)
                $templateWarning = $this->__('Warning: Block template locations modified, you may need to fix your template overrides if you have any.');
                if (is_object($this->container->get('request')) && method_exists($this->container->get('request'), 'getSession') && is_object($this->container->get('request')->getSession())) {
                    $this->addFlash(\Zikula_Session::MESSAGE_WARNING, $templateWarning);
                } else {
                    \LogUtil::registerWarning($templateWarning);
                }
            case '3.9.1':
                // make all content fields of blocks serialized.
                $sql = "SELECT * FROM blocks";
                $blocks = $this->entityManager->getConnection()->fetchAll($sql);
                $oldContent = [];
                foreach ($blocks as $block) {
                    $block['content'] = !empty($block['content']) ? $block['content'] : '';
                    $oldContent[$block['bid']] = \DataUtil::is_serialized($block['content']) ? unserialize($block['content']) : ['content' => $block['content']];
                }
                $this->schemaTool->update($this->entities);
                $this->entityManager->getConnection()->executeQuery("UPDATE blocks SET properties='a:0:{}'");

                $blocks = $this->entityManager->getRepository('ZikulaBlocksModule:BlockEntity')->findAll();
                $installerHelper = new InstallerHelper();
                /** @var \Zikula\BlocksModule\Entity\BlockEntity $block */
                foreach ($blocks as $block) {
                    $block->setProperties($oldContent[$block->getBid()]);
                    $block->setFilters($installerHelper->upgradeFilterArray($block->getFilters()));
                    $block->setBlocktype(preg_match('/.*Block$/', $block->getBkey()) ? substr($block->getBkey(), 0, -5) : $block->getBkey());
                    $block->setBkey($installerHelper->upgradeBkeyToFqClassname($this->container->get('kernel'), $block));
                }
                $this->entityManager->flush();

                $collapseable = $this->getVar('collapseable');
                $this->setVar('collapseable', (bool) $collapseable);

            case '3.9.2':
                // convert Text and Html block types so properties is proper array
                $blocks = $this->entityManager->getRepository('ZikulaBlocksModule:BlockEntity')->findBy(['blocktype' => ['Html', 'Text']]);
                foreach ($blocks as $block) {
                    $properties = $block->getProperties();
                    if (!is_array($properties)) {
                        $block->setProperties(['content' => $properties]);
                    }
                }
                $this->entityManager->flush();
            case '3.9.3':
                $this->schemaTool->drop(['Zikula\BlocksModule\Entity\UserBlockEntity']);
            case '3.9.4':
                // future upgrade routines
        }

        // Update successful
        return true;
    }

    /**
     * delete the blocks module
     *
     * Since the blocks module should never be deleted we'all always return false here
     * @return bool false
     */
    public function uninstall()
    {
        // Deletion not allowed
        return false;
    }

    /**
     * Add default block data for new installs
     * This is called after a complete installation since the blocks
     * need to be populated with module id's which are only available
     * once the install has been completed
     */
    public function defaultdata()
    {
        // create the default block positions - left, right and center for the traditional 3 column layout
        $positions = [
            'left' => $this->__('Left blocks'),
            'right' => $this->__('Right blocks'),
            'center' => $this->__('Center blocks'),
            'search' => $this->__('Search block'),
            'header' => $this->__('Header block'),
            'footer' => $this->__('Footer block'),
            'topnav' => $this->__('Top navigation block'),
            'bottomnav' => $this->__('Bottom navigation block'),
            ];
        foreach ($positions as $name => $description) {
            $positions[$name] = new BlockPositionEntity();
            $positions[$name]->setName($name);
            $positions[$name]->setDescription($description);
            $this->entityManager->persist($positions[$name]);
        }
        $this->entityManager->flush();

        // build the menu content
        $languages = ZLanguage::getInstalledLanguages();
        $saveLanguage = ZLanguage::getLanguageCode();
        $menucontent = [];
        foreach ($languages as $lang) {
            ZLanguage::setLocale($lang);
            ZLanguage::bindCoreDomain();

            $menucontent['displaymodules'] = '0';
            $menucontent['stylesheet'] = 'extmenu.css';
            $menucontent['template'] = 'Block/Extmenu/topnav.tpl';
            $menucontent['blocktitles'][$lang] = $this->__('Main menu');

            // insert the links
            $menucontent['links'][$lang][] = [
                'name' => $this->__('Home'),
                'url' => '{homepage}',
                'title' => $this->__('Go to the home page'),
                'level' => 0,
                'parentid' => null,
                'image' => '',
                'active' => '1'
            ];
            $menucontent['links'][$lang][] = [
                'name' => $this->__('Site search'),
                'url' => '{ZikulaSearchModule}',
                'title' => $this->__('Search this site'),
                'level' => 0,
                'parentid' => null,
                'image' => '',
                'active' => '1'
            ];
        }

        ZLanguage::setLocale($saveLanguage);

        $searchcontent = [
            'displaySearchBtn' => 1,
            'active' => ['ZikulaUsersModule' => 1]
        ];

        $hellomessage = $this->__('<p><a href="http://zikula.org/">Zikula</a> is a content management system (CMS) and application framework. It is secure and stable, and is a good choice for sites with a large volume of traffic.</p><p>With Zikula:</p><ul><li>you can customise all aspects of the site\'s appearance through themes, with support for CSS style sheets, JavaScript, Flash and all other modern web development technologies;</li><li>you can mark content as being suitable for either a single language or for all languages, and can control all aspects of localisation and internationalisation of your site;</li><li>you can be sure that your pages will display properly in all browsers, thanks to Zikula\'s full compliance with W3C HTML standards;</li><li>you get a standard application-programming interface (API) that lets you easily augment your site\'s functionality through modules, blocks and other extensions;</li><li>you can get help and support from the Zikula community of webmasters and developers at <a href="http://www.zikula.org">zikula.org</a>.</li></ul><p>Enjoy using Zikula!</p><p><strong>The Zikula team</strong></p><p><em>Note: Zikula is Free Open Source Software (FOSS) licensed under the GNU General Public License.</em></p>');

        $blocks = [];
        $extensionRepo = $this->entityManager->getRepository('\Zikula\ExtensionsModule\Entity\ExtensionEntity');
        $blocksModuleEntity = $extensionRepo->findOneBy(['name' => 'ZikulaBlocksModule']);
        $searchModuleEntity = $extensionRepo->findOneBy(['name' => 'ZikulaSearchModule']);
        $usersModuleEntity = $extensionRepo->findOneBy(['name' => 'ZikulaUsersModule']);
        $blocks[] = [
            'bkey' => 'ZikulaBlocksModule:\Zikula\BlocksModule\Block\ExtmenuBlock',
            'blocktype' => 'Extmenu',
            'language' => '',
            'module' => $blocksModuleEntity,
            'title' => $this->__('Main menu'),
            'description' => $this->__('Main menu'),
            'content' => $menucontent,
            'position' => $positions['topnav'],
            'order' => 0
        ];
        $blocks[] = [
            'bkey' => 'ZikulaSearchModule:\Zikula\SearchModule\Block\SearchBlock',
            'blocktype' => 'Search',
            'language' => '',
            'module' => $searchModuleEntity,
            'title' => $this->__('Search box'),
            'description' => $this->__('Search block'),
            'content' => $searchcontent,
            'position' => $positions['left']
        ];
        $blocks[] = [
            'bkey' => 'ZikulaBlocksModule:\Zikula\BlocksModule\Block\HtmlBlock',
            'blocktype' => 'Html',
            'language' => '',
            'module' => $blocksModuleEntity,
            'title' => $this->__("This site is powered by Zikula!"),
            'description' => $this->__('HTML block'),
            'properties' => ['content' => $hellomessage],
            'position' => $positions['center']
        ];
        $blocks[] = [
            'bkey' => 'ZikulaUsersModule:\Zikula\UsersModule\Block\LoginBlock',
            'blocktype' => 'Login',
            'language' => '',
            'module' => $usersModuleEntity,
            'title' => $this->__('User log-in'),
            'description' => $this->__('Login block'),
            'position' => $positions['topnav'],
            'order' => 1
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

        return;
    }
}
