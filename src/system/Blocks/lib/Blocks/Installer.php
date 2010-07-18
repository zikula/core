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

class Blocks_Installer extends Zikula_Installer
{
    /**
     * initialise the blocks module
     *
     * @return       bool       true on success, false otherwise
     */
    public function install()
    {
        // create blocks table
        // appropriate error message and return
        if (!DBUtil::createTable('blocks')) {
            return false;
        }

        // create userblocks table
        if (!DBUtil::createTable('userblocks')) {
            return false;
        }

        // create block positions table
        if (!DBUtil::createTable('block_positions')) {
            return false;
        }

        // create block placements table
        if (!DBUtil::createTable('block_placements')) {
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
     * @param        string   $oldVersion   version number string to upgrade from
     * @return       mixed    true on success, last valid version string or false if fails
     */
    public function upgrade($oldversion)
    {
        // Upgrade dependent on old version number
        switch ($oldversion)
        {
            case '3.6':
                // Rename 'thelang' block.
                $table = DBUtil::getLimitedTablename('blocks');
                $sql = "UPDATE $table SET z_bkey = 'lang' WHERE z_bkey = 'thelang'";
                DBUtil::executeSQL($sql);

                // Optional upgrade
                if (in_array(DBUtil::getLimitedTablename('message'), DBUtil::metaTables())) {
                    $this->migrateMessages();
                }

                $this->migrateExtMenu();

            case '3.7':
            case '3.7.0':
                if (!DBUtil::changeTable('blocks')) {
                    return false;
                }

            case '3.8.0':
            // future upgrade routines
        }

        // Update successful
        return true;
    }

    /**
     * delete the blocks module
     *
     * Since the blocks module should never be deleted we'all always return false here
     * @return       bool       false
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
        // create the default block positions - left, right and center for the traditional 3 column layout
        ModUtil::loadApi('Blocks', 'admin', true);

        // sanity check - truncate existing tables to ensure a clean blocks setup
        DBUtil::truncateTable('blocks');
        DBUtil::truncateTable('block_positions');
        DBUtil::truncateTable('block_placements');

        $left = ModUtil::apiFunc('Blocks', 'admin', 'createposition', array('name' => 'left', 'description' => $this->__('Left blocks')));
        $right = ModUtil::apiFunc('Blocks', 'admin', 'createposition', array('name' => 'right', 'description' => $this->__('Right blocks')));
        $center = ModUtil::apiFunc('Blocks', 'admin', 'createposition', array('name' => 'center', 'description' => $this->__('Center blocks')));

        // define an array of the default blocks
        $blocks = array();
        // build the menu content
        $languages = ZLanguage::getInstalledLanguages();
        $saveLanguage = ZLanguage::getLanguageCode();
        foreach ($languages as $lang)
        {
            ZLanguage::setLocale($lang);
            ZLanguage::bindCoreDomain();
            $menucontent['displaymodules'] = '0';
            $menucontent['stylesheet'] = 'extmenu.css';
            $menucontent['template'] = 'blocks_block_extmenu.tpl';
            $menucontent['blocktitles'][$lang] = $this->__('Main menu');
            // insert the links
            $menucontent['links'][$lang][] = array('name' => $this->__('Home'), 'url' => '{homepage}', 'title' => $this->__("Go to the site's home page"), 'level' => 0, 'parentid' => null, 'image' => '', 'active' => '1');
            $menucontent['links'][$lang][] = array('name' => $this->__('Site search'), 'url' => '{Search}', 'title' => $this->__('Search this site'), 'level' => 0, 'parentid' => null, 'image' => '', 'active' => '1');
            $menucontent['links'][$lang][] = array('name' => $this->__('Administration'), 'url' => '{Admin:adminpanel:admin}', 'title' => $this->__('Go to the site administration'), 'level' => 0, 'parentid' => null, 'image' => '', 'active' => '1');
            $menucontent['links'][$lang][] = array('name' => $this->__('My Account'), 'url' => '{Users}', 'title' => $this->__('Go to your account panel'), 'level' => 0, 'parentid' => null, 'image' => '', 'active' => '1');
            $menucontent['links'][$lang][] = array('name' => $this->__('Log out'), 'url' => '{Users:logout}', 'title' => $this->__('Log out of this site'), 'level' => 0, 'parentid' => null, 'image' => '', 'active' => '1');
        }

        ZLanguage::setLocale($saveLanguage);

        $menucontent = serialize($menucontent);
        $hellomessage = $this->__('<p><a href="http://zikula.org">Zikula</a> is a content management system (CMS) and application framework. It is secure and stable, and is a good choice for sites with a large volume of traffic.</p><p>With Zikula:</p><ul><li>you can customise all aspects of the site\'s appearance through themes, with support for CSS style sheets, JavaScript, Flash and all other modern web development technologies;</li><li>you can mark content as being suitable for either a single language or for all languages, and can control all aspects of localisation and internationalisation of your site and pages;</li><li>you can be sure that your pages will display properly in all browsers, thanks to Zikula\'s full compliance with W3C HTML standards;</li><li>you get a standard application-programming interface (API) that lets you easily augment your site\'s functionality through modules, blocks and other extensions;</li><li>you can get help and support from the Zikula community of webmasters and developers at <a href="http://www.zikula.org">zikula.org</a>.</li></ul><p>Enjoy using Zikula!</p><p><strong>The Zikula team</strong></p><p><em>Note: Zikula is Free Open Source Software (FOSS) licensed under the GNU General Public License.</em></p>');
        $blocks[] = array('bkey' => 'Extmenu', 'collapsable' => 1, 'defaultstate' => 1, 'language' => '', 'mid' => ModUtil::getIdFromName('Blocks'), 'title' => $this->__('Main menu'), 'description' => '', 'content' => $menucontent, 'positions' => array($left));
        $blocks[] = array('bkey' => 'Search', 'collapsable' => 1, 'defaultstate' => 1, 'language' => '', 'mid' => ModUtil::getIdFromName('Search'), 'title' => $this->__('Search box'), 'description' => '', 'positions' => array($left));
        $blocks[] = array('bkey' => 'Html', 'collapsable' => 1, 'defaultstate' => 1, 'language' => '', 'mid' => ModUtil::getIdFromName('Blocks'), 'title' => $this->__("This site is powered by Zikula!"), 'description' => '', 'content' => $hellomessage, 'positions' => array($center));
        $blocks[] = array('bkey' => 'Login', 'collapsable' => 1, 'defaultstate' => 1, 'language' => '', 'mid' => ModUtil::getIdFromName('Users'), 'title' => $this->__('User log-in'), 'description' => '', 'positions' => array($right));
        $blocks[] = array('bkey' => 'Online', 'collapsable' => 1, 'defaultstate' => 1, 'language' => '', 'mid' => ModUtil::getIdFromName('Users'), 'title' => $this->__('Who\'s on-line'), 'description' => '', 'positions' => array($right));

        // create each block and then update the block
        // the create creates the initiial block record, the update sets the block placments
        foreach ($blocks as $position => $block)
        {
            $block['bid'] = ModUtil::apiFunc('Blocks', 'admin', 'create', $block);
            ModUtil::apiFunc('Blocks', 'admin', 'update', $block);
        }

        return;
    }

    protected function migrateMessages()
    {
        // Migrate any Admin_Messages to blocks
        $table = DBUtil::getLimitedTablename('message');
        $result = DBUtil::executeSQL("SELECT * FROM $table");
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
        DBUtil::executeSQL("DROP TABLE $table");

        // Remove any Admin_Message blocks
        $table = DBUtil::getLimitedTablename('blocks');
        $sql = "DELETE FROM $table WHERE z_bkey = 'messages'";
        DBUtil::executeSQL($sql);
    }

    protected function migrateExtMenu()
    {
        $blocks = DBUtil::selectObjectArray('blocks');
        foreach ($blocks as $block) {
            if ($block['bkey'] == 'extmenu') {
                $content = unserialize($block['content']);
                $content['template'] = str_replace('blocks_block_extmenu.htm', 'blocks_block_extmenu.tpl', $content['template']);
                $block['content'] = serialize($content);
                DBUtil::updateObject($block, 'blocks', '', 'bid');
            }
        }
    }
}