<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

class Blocks_Installer extends Zikula_AbstractInstaller
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
            'Blocks_Entity_Block',
            'Blocks_Entity_BlockPosition',
            'Blocks_Entity_BlockPlacement',
            'Blocks_Entity_UserBlock'
        );

        try {
            DoctrineHelper::createSchema($this->entityManager, $classes);
        } catch (Exception $e) {
            return false;
        }

        // Set a default value for a module variable
        $this->setVar('collapseable', 0);

        // Initialisation successful
        return true;
    }

    /**
     * upgrade the module from an old version
     *
     * This function must consider all the released versions of the module!
     * If the upgrade fails at some point, it returns the last upgraded version.
     *
     * @param  string $oldVersion version number string to upgrade from
     * @return mixed  true on success, last valid version string or false if fails
     */
    public function upgrade($oldversion)
    {
        // Upgrade dependent on old version number
        switch ($oldversion) {
            case '3.6':
                // Rename 'thelang' block.
                $table = 'blocks';
                $sql = "UPDATE $table SET bkey = 'lang' WHERE bkey = 'thelang'";
                DBUtil::executeSQL($sql);
                // Optional upgrade
                if (in_array(DBUtil::getLimitedTablename('message'), DBUtil::metaTables())) {
                    $this->migrateMessages();
                }
                $this->migrateBlockNames();
                $this->migrateExtMenu();

            case '3.7':
            case '3.7.0':
                if (!DBUtil::changeTable('blocks')) {
                    return false;
                }

            case '3.7.1':
                $this->newBlockPositions();

            case '3.8.0':
                // update empty filter fields to an empty array
                $entity = $this->name . '_Entity_Block';
                $dql = "UPDATE $entity p SET p.filter = 'a:0:{}' WHERE p.filter = '' OR p.filter = 's:0:\"\";' OR p.filter = 'a:3:{s:4:\"type\";s:0:\"\";s:9:\"functions\";s:0:\"\";s:10:\"customargs\";s:0:\"\";}'";
                $query = $this->entityManager->createQuery($dql);
                $query->getResult();

            case '3.8.1':
                // register ui_hooks for HTML block editing
                HookUtil::registerSubscriberBundles($this->version->getHookSubscriberBundles());
            case '3.8.2':
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
     * This is called after a complete pn installation since the blocks
     * need to be populated with module id's which are only available
     * once the install has been completed
     */
    public function defaultdata()
    {
        // load block api
        ModUtil::loadApi('Blocks', 'admin', true);

        // sanity check - truncate existing tables to ensure a clean blocks setup
        $connection = $this->entityManager->getConnection();
        $platform = $connection->getDatabasePlatform();
        $connection->executeUpdate($platform->getTruncateTableSQL('blocks', true));
        $connection->executeUpdate($platform->getTruncateTableSQL('block_positions', true));
        $connection->executeUpdate($platform->getTruncateTableSQL('block_placements', true));

        // create the default block positions - left, right and center for the traditional 3 column layout
        $left = ModUtil::apiFunc('Blocks', 'admin', 'createposition', array('name' => 'left', 'description' => $this->__('Left blocks')));
        $right = ModUtil::apiFunc('Blocks', 'admin', 'createposition', array('name' => 'right', 'description' => $this->__('Right blocks')));
        $center = ModUtil::apiFunc('Blocks', 'admin', 'createposition', array('name' => 'center', 'description' => $this->__('Center blocks')));
        $search = ModUtil::apiFunc('Blocks', 'admin', 'createposition', array('name' => 'search', 'description' => $this->__('Search block')));
        $header = ModUtil::apiFunc('Blocks', 'admin', 'createposition', array('name' => 'header', 'description' => $this->__('Header block')));
        $footer = ModUtil::apiFunc('Blocks', 'admin', 'createposition', array('name' => 'footer', 'description' => $this->__('Footer block')));
        $topnav = ModUtil::apiFunc('Blocks', 'admin', 'createposition', array('name' => 'topnav', 'description' => $this->__('Top navigation block')));
        $bottomnav = ModUtil::apiFunc('Blocks', 'admin', 'createposition', array('name' => 'bottomnav', 'description' => $this->__('Bottom navigation block')));

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
            $menucontent['template'] = 'blocks_block_extmenu.tpl';
            $menucontent['blocktitles'][$lang] = $this->__('Main menu');

            // insert the links
            $menucontent['links'][$lang][] = array('name' => $this->__('Home'), 'url' => '{homepage}', 'title' => $this->__("Go to the home page"), 'level' => 0, 'parentid' => null, 'image' => '', 'active' => '1');
            $menucontent['links'][$lang][] = array('name' => $this->__('Administration'), 'url' => '{Admin:admin:adminpanel}', 'title' => $this->__('Go to the site administration'), 'level' => 0, 'parentid' => null, 'image' => '', 'active' => '1');
            $menucontent['links'][$lang][] = array('name' => $this->__('My Account'), 'url' => '{Users}', 'title' => $this->__('Go to your account panel'), 'level' => 0, 'parentid' => null, 'image' => '', 'active' => '1');
            $menucontent['links'][$lang][] = array('name' => $this->__('Log out'), 'url' => '{Users:user:logout}', 'title' => $this->__('Log out of this site'), 'level' => 0, 'parentid' => null, 'image' => '', 'active' => '1');
            $menucontent['links'][$lang][] = array('name' => $this->__('Site search'), 'url' => '{Search}', 'title' => $this->__('Search this site'), 'level' => 0, 'parentid' => null, 'image' => '', 'active' => '1');

            $topnavcontent['displaymodules'] = '0';
            $topnavcontent['stylesheet'] = 'extmenu.css';
            $topnavcontent['template'] = 'blocks_block_extmenu_topnav.tpl';
            $topnavcontent['blocktitles'][$lang] = $this->__('Top navigation');

            // insert the links
            $topnavcontent['links'][$lang][] = array('name' => $this->__('Home'), 'url' => '{homepage}', 'title' => $this->__("Go to the site's home page"), 'level' => 0, 'parentid' => null, 'image' => '', 'active' => '1');
            $topnavcontent['links'][$lang][] = array('name' => $this->__('My Account'), 'url' => '{Users}', 'title' => $this->__('Go to your account panel'), 'level' => 0, 'parentid' => null, 'image' => '', 'active' => '1');
            $topnavcontent['links'][$lang][] = array('name' => $this->__('Site search'), 'url' => '{Search}', 'title' => $this->__('Search this site'), 'level' => 0, 'parentid' => null, 'image' => '', 'active' => '1');
        }

        ZLanguage::setLocale($saveLanguage);

        $menucontent = serialize($menucontent);
        $topnavcontent = serialize($topnavcontent);
        $searchcontent = array('displaySearchBtn' => 1,
                               'active' => array('Users' => 1));
        $searchcontent = serialize($searchcontent);

        $hellomessage = $this->__('<p><a href="http://zikula.org/">Zikula</a> is a content management system (CMS) and application framework. It is secure and stable, and is a good choice for sites with a large volume of traffic.</p><p>With Zikula:</p><ul><li>you can customise all aspects of the site\'s appearance through themes, with support for CSS style sheets, JavaScript, Flash and all other modern web development technologies;</li><li>you can mark content as being suitable for either a single language or for all languages, and can control all aspects of localisation and internationalisation of your site;</li><li>you can be sure that your pages will display properly in all browsers, thanks to Zikula\'s full compliance with W3C HTML standards;</li><li>you get a standard application-programming interface (API) that lets you easily augment your site\'s functionality through modules, blocks and other extensions;</li><li>you can get help and support from the Zikula community of webmasters and developers at <a href="http://www.zikula.org">zikula.org</a>.</li></ul><p>Enjoy using Zikula!</p><p><strong>The Zikula team</strong></p><p><em>Note: Zikula is Free Open Source Software (FOSS) licensed under the GNU General Public License.</em></p>');
        $blocks[] = array('bkey' => 'Extmenu', 'collapsable' => 1, 'defaultstate' => 1, 'language' => '', 'mid' => ModUtil::getIdFromName('Blocks'), 'title' => $this->__('Main menu'), 'description' => $this->__('Main menu'), 'content' => $menucontent, 'positions' => array($left));
        $blocks[] = array('bkey' => 'Search', 'collapsable' => 1, 'defaultstate' => 1, 'language' => '', 'mid' => ModUtil::getIdFromName('Search'), 'title' => $this->__('Search box'), 'description' => $this->__('Search block'), 'content' => $searchcontent, 'positions' => array($search));
        $blocks[] = array('bkey' => 'Html', 'collapsable' => 1, 'defaultstate' => 1, 'language' => '', 'mid' => ModUtil::getIdFromName('Blocks'), 'title' => $this->__("This site is powered by Zikula!"), 'description' => $this->__('HTML block'), 'content' => $hellomessage, 'positions' => array($center));
        $blocks[] = array('bkey' => 'Login', 'collapsable' => 1, 'defaultstate' => 1, 'language' => '', 'mid' => ModUtil::getIdFromName('Users'), 'title' => $this->__('User log-in'), 'description' => $this->__('Login block'), 'positions' => array($right));
        //$blocks[] = array('bkey' => 'Online', 'collapsable' => 1, 'defaultstate' => 1, 'language' => '', 'mid' => ModUtil::getIdFromName('Users'), 'title' => $this->__('Who\'s on-line'), 'description' => $this->__('Online block'), 'positions' => array($right));
        $blocks[] = array('bkey' => 'Extmenu', 'collapsable' => 1, 'defaultstate' => 1, 'language' => '', 'mid' => ModUtil::getIdFromName('Blocks'), 'title' => $this->__('Top navigation'), 'description' => $this->__('Theme navigation'), 'content' => $topnavcontent, 'positions' => array($topnav));

        // create each block and then update the block
        // the create creates the initial block record, the update sets the block placement
        foreach ($blocks as $position => $block) {
            ModUtil::apiFunc('Blocks', 'admin', 'create', $block);
        }

        return;
    }

    protected function migrateMessages()
    {
        // Migrate any Admin_Messages to blocks
        $messageTable = DBUtil::getLimitedTablename('message');
        $blocksTable = 'blocks';
        $messageBlocks = DBUtil::executeSQL("SELECT * FROM $blocksTable WHERE bkey = 'messages'")->fetchAll(Doctrine::FETCH_ASSOC);

        $result = DBUtil::executeSQL("SELECT * FROM $messageTable");
        $data = $result->fetchAll(Doctrine::FETCH_ASSOC);
        if ($data) {
            foreach ($data as $key => $value) {
                foreach ($data[$key] as $k => $v) {
                    unset($data[$key][$k]);
                    $newKey = str_replace('z_', '', $k);
                    $data[$key][$newKey] = $v;
                }
                unset($data[$key]['date']);
                unset($data[$key]['expire']);
                unset($data[$key]['view']);
                unset($data[$key]['mid']);
                if (!$messageBlocks) {
                    $data[$key]['active'] = '0';
                }
                $data[$key]['bkey'] = 'html';
                $data[$key]['position'] = 'center';
                $data[$key]['refresh'] = '3600';
                $data[$key]['mid'] = ModUtil::getIdFromName('Blocks');
                $data[$key] = DBUtil::insertObject($data[$key], 'blocks', 'bid');
                $placement = array('pid' => 3, 'bid' => $data[$key]['bid']);
                DBUtil::insertObject($placement, 'block_placements', 'pid', true);
            }
        }

        // Remove Admin_Message table.
        DBUtil::executeSQL("DROP TABLE $messageTable");
        // Remove any Admin_Message blocks
        $sql = "DELETE FROM $blocksTable WHERE bkey = 'messages'";
        DBUtil::executeSQL($sql);
    }

    protected function migrateExtMenu()
    {
        $blocks = DBUtil::selectObjectArray('blocks');
        foreach ($blocks as $block) {
            if ($block['bkey'] == 'Extmenu') {
                $content = unserialize($block['content']);
                $content['template'] = str_replace('blocks_block_extmenu.htm', 'blocks_block_extmenu.tpl', $content['template']);

                // Update {} style links to new parameter order
                // Module:type:func instead of Module:func:type
                foreach ($content['links'] as &$lang) {    // Loop through all languages
                    foreach ($lang as &$item) {             // And each item in each language
                        if ( preg_match('#\{(.*)\}#', $item['url'], $matches) ) {
                            $parts = explode(':', $matches[1]);
                            $c = count($parts);
                            if ($c > 1) {           // Need to fix if more than a module is given
                                if ($c == 2) {      // Add type if it was left out
                                    $tmp = 'user';
                                } else {
                                    $tmp = $parts[2];
                                }
                                $parts[2] = $parts[1];
                                $parts[1] = $tmp;
                                $item['url'] = '{' . implode(':', $parts) . '}';    // And put it back together
                            }

                        }
                    }
                }

                $block['content'] = serialize($content);
                DBUtil::updateObject($block, 'blocks', '', 'bid');
            }
        }
    }

    protected function migrateBlockNames()
    {
        $blocks = DBUtil::selectObjectArray('blocks');
        foreach ($blocks as $block) {
            $block['bkey'] = ucfirst($block['bkey']);
            DBUtil::updateObject($block, 'blocks', '', 'bid');
        }
    }

    protected function newBlockPositions()
    {
        $positions = ModUtil::apiFunc('Blocks', 'user', 'getallpositions');

        // create the search block position if doesn't exists
        if (!isset($positions['search'])) {
            $searchpid = ModUtil::apiFunc('Blocks', 'admin', 'createposition', array('name' => 'search', 'description' => $this->__('Search block')));
        } else {
            $searchpid = $positions['search']['pid'];
        }

        // restores the search block if not present
        $dbtable      = DBUtil::getTables();
        $blockscolumn = $dbtable['blocks_column'];
        $searchblocks = DBUtil::selectObjectArray('blocks', "$blockscolumn[bkey] = 'Search'");

        if (empty($searchblocks)) {
            $block = array('bkey' => 'Search', 'collapsable' => 1, 'defaultstate' => 1, 'language' => '', 'mid' => ModUtil::getIdFromName('Search'), 'title' => $this->__('Search box'), 'description' => '', 'positions' => array($searchpid));
            $block['bid'] = ModUtil::apiFunc('Blocks', 'admin', 'create', $block);
            ModUtil::apiFunc('Blocks', 'admin', 'update', $block);
        } else {
            // assign the block to the search position
            $blockplacement = array('bid' => $searchblocks[0]['bid'], 'pid' => $searchpid);
            DBUtil::insertObject($blockplacement, 'block_placements');
        }

        // create new block positions if they don't exist
        if (!isset($positions['header'])) {
            $header = ModUtil::apiFunc('Blocks', 'admin', 'createposition', array('name' => 'header', 'description' => $this->__('Header block')));
        }
        if (!isset($positions['footer'])) {
            $footer = ModUtil::apiFunc('Blocks', 'admin', 'createposition', array('name' => 'footer', 'description' => $this->__('Footer block')));
        }
        if (!isset($positions['bottomnav'])) {
            $bottomnav = ModUtil::apiFunc('Blocks', 'admin', 'createposition', array('name' => 'bottomnav', 'description' => $this->__('Bottom navigation block')));
        }
        if (!isset($positions['topnav'])) {
            $topnav = ModUtil::apiFunc('Blocks', 'admin', 'createposition', array('name' => 'topnav', 'description' => $this->__('Top navigation block')));

            // Build content for the top navigation menu
            $languages = ZLanguage::getInstalledLanguages();
            $saveLanguage = ZLanguage::getLanguageCode();
            foreach ($languages as $lang) {
                ZLanguage::setLocale($lang);
                ZLanguage::bindCoreDomain();
                $topnavcontent = array();
                $topnavcontent['displaymodules'] = '0';
                $topnavcontent['stylesheet'] = 'extmenu.css';
                $topnavcontent['template'] = 'blocks_block_extmenu_topnav.tpl';
                $topnavcontent['blocktitles'][$lang] = $this->__('Top navigation');
                $topnavcontent['links'][$lang][] = array('name' => $this->__('Home'), 'url' => '{homepage}', 'title' => $this->__("Go to the site's home page"), 'level' => 0, 'parentid' => null, 'image' => '', 'active' => '1');
                $topnavcontent['links'][$lang][] = array('name' => $this->__('My Account'), 'url' => '{Users}', 'title' => $this->__('Go to your account panel'), 'level' => 0, 'parentid' => null, 'image' => '', 'active' => '1');
                $topnavcontent['links'][$lang][] = array('name' => $this->__('Site search'), 'url' => '{Search}', 'title' => $this->__('Search this site'), 'level' => 0, 'parentid' => null, 'image' => '', 'active' => '1');
            }

            ZLanguage::setLocale($saveLanguage);
            $topnavcontent = serialize($topnavcontent);
            $topnavblock = array('bkey' => 'Extmenu', 'collapsable' => 1, 'defaultstate' => 1, 'language' => '', 'mid' => ModUtil::getIdFromName('Blocks'), 'title' => $this->__('Top navigation'), 'description' => '', 'content' => $topnavcontent, 'positions' => array($topnav));
            $topnavblock['bid'] = ModUtil::apiFunc('Blocks', 'admin', 'create', $topnavblock);
            ModUtil::apiFunc('Blocks', 'admin', 'update', $topnavblock);
        }
    }
}
