<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Module\BlocksModule;

use DoctrineHelper;
use ModUtil;
use ZLanguage;
use Doctrine;
use HookUtil;

/**
 * Installation and upgrade routines for the blocks module
 */
class BlocksModuleInstaller extends \Zikula_AbstractInstaller
{
    /**
     * initialise the blocks module
     *
     * @return bool true on success, false otherwise
     */
    public function install()
    {
        // create tables
        $classes = array(
            'Zikula\Module\BlocksModule\Entity\BlockEntity',
            'Zikula\Module\BlocksModule\Entity\BlockPositionEntity',
            'Zikula\Module\BlocksModule\Entity\BlockPlacementEntity',
            'Zikula\Module\BlocksModule\Entity\UserBlockEntity'
        );

        try {
            DoctrineHelper::createSchema($this->entityManager, $classes);
        } catch (\Exception $e) {
            return false;
        }

        // Set a default value for a module variable
        $this->setVar('collapseable', 0);

        HookUtil::registerSubscriberBundles($this->version->getHookSubscriberBundles());

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
                HookUtil::registerSubscriberBundles($this->version->getHookSubscriberBundles());
            case '3.8.2':
            case '3.9.0':
                $blocks = $this->entityManager->getRepository('ZikulaBlocksModule:BlockEntity')->findAll();
                /** @var \Zikula\Module\BlocksModule\Entity\BlockEntity $block */
                foreach ($blocks as $block) {
                    $content = $block->getContent();
                    if(\DataUtil::is_serialized($content)) {
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
                if (is_object($this->request) && method_exists($this->request, 'getSession') && is_object($this->request->getSession())) {
                    $this->request->getSession()->getFlashBag()->add(\Zikula_Session::MESSAGE_WARNING, $templateWarning);
                } else {
                    \LogUtil::registerWarning($templateWarning);
                }
            case '3.9.1':
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
        // load block api
        ModUtil::loadApi('ZikulaBlocksModule', 'admin', true);

        // sanity check - truncate existing tables to ensure a clean blocks setup
        $connection = $this->entityManager->getConnection();
        $platform = $connection->getDatabasePlatform();
        $connection->executeUpdate($platform->getTruncateTableSQL('blocks', true));
        $connection->executeUpdate($platform->getTruncateTableSQL('block_positions', true));
        $connection->executeUpdate($platform->getTruncateTableSQL('block_placements', true));

        // create the default block positions - left, right and center for the traditional 3 column layout
        $left = ModUtil::apiFunc('ZikulaBlocksModule', 'admin', 'createposition', array('name' => 'left', 'description' => $this->__('Left blocks')));
        $right = ModUtil::apiFunc('ZikulaBlocksModule', 'admin', 'createposition', array('name' => 'right', 'description' => $this->__('Right blocks')));
        $center = ModUtil::apiFunc('ZikulaBlocksModule', 'admin', 'createposition', array('name' => 'center', 'description' => $this->__('Center blocks')));
        $search = ModUtil::apiFunc('ZikulaBlocksModule', 'admin', 'createposition', array('name' => 'search', 'description' => $this->__('Search block')));
        $header = ModUtil::apiFunc('ZikulaBlocksModule', 'admin', 'createposition', array('name' => 'header', 'description' => $this->__('Header block')));
        $footer = ModUtil::apiFunc('ZikulaBlocksModule', 'admin', 'createposition', array('name' => 'footer', 'description' => $this->__('Footer block')));
        $topnav = ModUtil::apiFunc('ZikulaBlocksModule', 'admin', 'createposition', array('name' => 'topnav', 'description' => $this->__('Top navigation block')));
        $bottomnav = ModUtil::apiFunc('ZikulaBlocksModule', 'admin', 'createposition', array('name' => 'bottomnav', 'description' => $this->__('Bottom navigation block')));

        // define an array of the default blocks
        $blocks = array();

        // build the menu content
        $languages = ZLanguage::getInstalledLanguages();
        $saveLanguage = ZLanguage::getLanguageCode();
        $menucontent = array();
        $topnavcontent = array();
        foreach ($languages as $lang) {
            ZLanguage::setLocale($lang);
            ZLanguage::bindCoreDomain();

            $menucontent['displaymodules'] = '0';
            $menucontent['stylesheet'] = 'extmenu.css';
            $menucontent['template'] = 'Block/Extmenu/extmenu.tpl';
            $menucontent['blocktitles'][$lang] = $this->__('Main menu');

            // insert the links
            $menucontent['links'][$lang][] = array('name' => $this->__('Home'), 'url' => '{homepage}', 'title' => $this->__("Go to the home page"), 'level' => 0, 'parentid' => null, 'image' => '', 'active' => '1');
            $menucontent['links'][$lang][] = array('name' => $this->__('Administration'), 'url' => '{Admin:admin:adminpanel}', 'title' => $this->__('Go to the site administration'), 'level' => 0, 'parentid' => null, 'image' => '', 'active' => '1');
            $menucontent['links'][$lang][] = array('name' => $this->__('My Account'), 'url' => '{ZikulaUsersModule}', 'title' => $this->__('Go to your account panel'), 'level' => 0, 'parentid' => null, 'image' => '', 'active' => '1');
            $menucontent['links'][$lang][] = array('name' => $this->__('Log out'), 'url' => '{ZikulaUsersModule:user:logout}', 'title' => $this->__('Log out of this site'), 'level' => 0, 'parentid' => null, 'image' => '', 'active' => '1');
            $menucontent['links'][$lang][] = array('name' => $this->__('Site search'), 'url' => '{ZikulaSearchModule}', 'title' => $this->__('Search this site'), 'level' => 0, 'parentid' => null, 'image' => '', 'active' => '1');

            $topnavcontent['displaymodules'] = '0';
            $topnavcontent['stylesheet'] = 'extmenu.css';
            $topnavcontent['template'] = 'Block/Extmenu/topnav.tpl';
            $topnavcontent['blocktitles'][$lang] = $this->__('Top navigation');

            // insert the links
            $topnavcontent['links'][$lang][] = array('name' => $this->__('Home'), 'url' => '{homepage}', 'title' => $this->__("Go to the site's home page"), 'level' => 0, 'parentid' => null, 'image' => '', 'active' => '1');
            $topnavcontent['links'][$lang][] = array('name' => $this->__('My Account'), 'url' => '{ZikulaUsersModule}', 'title' => $this->__('Go to your account panel'), 'level' => 0, 'parentid' => null, 'image' => '', 'active' => '1');
            $topnavcontent['links'][$lang][] = array('name' => $this->__('Site search'), 'url' => '{ZikulaSearchModule}', 'title' => $this->__('Search this site'), 'level' => 0, 'parentid' => null, 'image' => '', 'active' => '1');
        }

        ZLanguage::setLocale($saveLanguage);

        $menucontent = serialize($menucontent);
        $topnavcontent = serialize($topnavcontent);
        $searchcontent = array('displaySearchBtn' => 1,
                               'active' => array('ZikulaUsersModule' => 1));
        $searchcontent = serialize($searchcontent);

        $hellomessage = $this->__('<p><a href="http://zikula.org/">Zikula</a> is a content management system (CMS) and application framework. It is secure and stable, and is a good choice for sites with a large volume of traffic.</p><p>With Zikula:</p><ul><li>you can customise all aspects of the site\'s appearance through themes, with support for CSS style sheets, JavaScript, Flash and all other modern web development technologies;</li><li>you can mark content as being suitable for either a single language or for all languages, and can control all aspects of localisation and internationalisation of your site;</li><li>you can be sure that your pages will display properly in all browsers, thanks to Zikula\'s full compliance with W3C HTML standards;</li><li>you get a standard application-programming interface (API) that lets you easily augment your site\'s functionality through modules, blocks and other extensions;</li><li>you can get help and support from the Zikula community of webmasters and developers at <a href="http://www.zikula.org">zikula.org</a>.</li></ul><p>Enjoy using Zikula!</p><p><strong>The Zikula team</strong></p><p><em>Note: Zikula is Free Open Source Software (FOSS) licensed under the GNU General Public License.</em></p>');
        $blocks[] = array('bkey' => 'Extmenu', 'collapsable' => 1, 'defaultstate' => 1, 'language' => '', 'mid' => ModUtil::getIdFromName('ZikulaBlocksModule'), 'title' => $this->__('Main menu'), 'description' => $this->__('Main menu'), 'content' => $menucontent, 'positions' => array($left));
        $blocks[] = array('bkey' => 'Search', 'collapsable' => 1, 'defaultstate' => 1, 'language' => '', 'mid' => ModUtil::getIdFromName('ZikulaSearchModule'), 'title' => $this->__('Search box'), 'description' => $this->__('Search block'), 'content' => $searchcontent, 'positions' => array($search));
        $blocks[] = array('bkey' => 'Html', 'collapsable' => 1, 'defaultstate' => 1, 'language' => '', 'mid' => ModUtil::getIdFromName('ZikulaBlocksModule'), 'title' => $this->__("This site is powered by Zikula!"), 'description' => $this->__('HTML block'), 'content' => $hellomessage, 'positions' => array($center));
        $blocks[] = array('bkey' => 'Login', 'collapsable' => 1, 'defaultstate' => 1, 'language' => '', 'mid' => ModUtil::getIdFromName('ZikulaUsersModule'), 'title' => $this->__('User log-in'), 'description' => $this->__('Login block'), 'positions' => array($right));
        //$blocks[] = array('bkey' => 'Online', 'collapsable' => 1, 'defaultstate' => 1, 'language' => '', 'mid' => ModUtil::getIdFromName('ZikulaUsersModule'), 'title' => $this->__('Who\'s on-line'), 'description' => $this->__('Online block'), 'positions' => array($right));
        $blocks[] = array('bkey' => 'Extmenu', 'collapsable' => 1, 'defaultstate' => 1, 'language' => '', 'mid' => ModUtil::getIdFromName('ZikulaBlocksModule'), 'title' => $this->__('Top navigation'), 'description' => $this->__('Theme navigation'), 'content' => $topnavcontent, 'positions' => array($topnav));

        // create each block and then update the block
        // the create creates the initial block record, the update sets the block placement
        foreach ($blocks as $position => $block) {
            ModUtil::apiFunc('ZikulaBlocksModule', 'admin', 'create', $block);
        }

        return;
    }
}
