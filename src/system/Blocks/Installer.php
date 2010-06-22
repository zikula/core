<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2.1 (or at your option, any later version).
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
        ModUtil::setVar('Blocks', 'collapseable', 0);

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
            case '3.2':
                $this->upgrade_fixSerializedData();
                $this->upgrade_migrateExtMenu();

            case '3.3':
                $this->upgrade_updateThelang();

            case '3.4':
                $this->upgrade_updateBlockLanguages();

            case '3.5':
            case '3.6':
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
            $menucontent['template'] = 'blocks_block_extmenu.htm';
            $menucontent['blocktitles'][$lang] = $this->__('Main menu');
            // insert the links
            $menucontent['links'][$lang][] = array('name' => $this->__('Home'), 'url' => '{homepage}', 'title' => $this->__("Go to the site's home page"), 'level' => 0, 'parentid' => null, 'image' => '', 'active' => '1');
            $menucontent['links'][$lang][] = array('name' => $this->__('Administration'), 'url' => '{Admin:adminpanel:admin}', 'title' => $this->__('Go to the site administration'), 'level' => 0, 'parentid' => null, 'image' => '', 'active' => '1');
            $menucontent['links'][$lang][] = array('name' => $this->__('My Account'), 'url' => '{Users}', 'title' => $this->__('Go to your account panel'), 'level' => 0, 'parentid' => null, 'image' => '', 'active' => '1');
            $menucontent['links'][$lang][] = array('name' => $this->__('Log out'), 'url' => '{Users:logout}', 'title' => $this->__('Log out of this site'), 'level' => 0, 'parentid' => null, 'image' => '', 'active' => '1');
        }

        ZLanguage::setLocale($saveLanguage);

        $menucontent = serialize($menucontent);
        $hellomessage = $this->__('<p><a href="http://www.zikula.org">Zikula</a> is a content management system (CMS) and application framework. It is secure and stable, and is a good choice for sites with a large volume of traffic.</p><p>With Zikula:</p><ul><li>you can customise all aspects of the site\'s appearance through themes, with support for CSS style sheets, JavaScript, Flash and all other modern web development technologies;</li><li>you can mark content as being suitable for either a single language or for all languages, and can control all aspects of localisation and internationalisation of your site and pages;</li><li>you can be sure that your pages will display properly in all browsers, thanks to Zikula\'s full compliance with W3C HTML standards;</li><li>you get a standard application-programming interface (API) that lets you easily augment your site\'s functionality through modules, blocks and other extensions;</li><li>you can get help and support from the Zikula community of webmasters and developers at <a href="http://www.zikula.org">zikula.org</a>.</li></ul><p>Enjoy using Zikula!</p><p><strong>The Zikula team</strong></p><p><em>Note: Zikula is Free Open Source Software (FOSS) licensed under the GNU General Public License.</em></p>');
        $blocks[] = array('bkey' => 'extmenu', 'collapsable' => 1, 'defaultstate' => 1, 'language' => '', 'mid' => ModUtil::getIdFromName('Blocks'), 'title' => $this->__('Main menu'), 'content' => $menucontent, 'positions' => array($left));
        $blocks[] = array('bkey' => 'html', 'collapsable' => 1, 'defaultstate' => 1, 'language' => '', 'mid' => ModUtil::getIdFromName('Blocks'), 'title' => $this->__("This site is powered by Zikula!"), 'content' => $hellomessage, 'positions' => array($center));
        $blocks[] = array('bkey' => 'login', 'collapsable' => 1, 'defaultstate' => 1, 'language' => '', 'mid' => ModUtil::getIdFromName('Users'), 'title' => $this->__('User log-in'), 'positions' => array($right));

        // create each block and then update the block
        // the create creates the initiial block record, the update sets the block placments
        foreach ($blocks as $position => $block)
        {
            $block['bid'] = ModUtil::apiFunc('Blocks', 'admin', 'create', $block);
            ModUtil::apiFunc('Blocks', 'admin', 'update', $block);
        }

        return;
    }

    public function upgrade_fixSerializedData()
    {
        // fix serialised data in blocks
        $obj = DBUtil::selectObjectArray('blocks');
        foreach ($obj as $block)
        {
            if (DataUtil::is_serialized($block['content'])) {
                $block['content'] = serialize(DataUtil::mb_unserialize($block['content']));
            }
            DBUtil::updateObject($block, 'blocks', '', 'bid', true);
        }

        return true;
    }

    public function upgrade_migrateExtMenu()
    {
        $pntable = System::dbGetTables();
        $blockcolumn = $pntable['blocks_column'];
        $where = "WHERE $blockcolumn[bkey] = 'extmenu'";
        $obj = DBUtil::selectObjectArray('blocks', $where);

        if (count($obj) == 0) {
            // nothing to do
            return;
        }

        foreach ($obj as $block)
        {
            // translate display_name l3 -> l2
            $data = unserialize($block['content']);
            foreach ($data['blocktitles'] as $l3 => $v) {
                if ($l2 = ZLanguage::translateLegacyCode($l3)) {
                    unset($data['blocktitles'][$l3]);
                    $data['blocktitles'][$l2] = $v;
                }
            }

            foreach ($data['links'] as $l3 => $v) {
                if ($l2 = ZLanguage::translateLegacyCode($l3)) {
                    unset($data['links'][$l3]);
                    $data['links'][$l2] = $v;
                }
            }

            $block['content'] = serialize($data);
            DBUtil::updateObject($block, 'blocks', '', 'bid', true);
        }

        return;
    }

    public function upgrade_updateThelang()
    {
        $pntable = System::dbGetTables();
        $blockcolumn = $pntable['blocks_column'];
        $where = "WHERE $blockcolumn[bkey] = 'thelang'";
        $obj = DBUtil::selectObjectArray('blocks', $where);

        if (count($obj) == 0) {
            // nothing to do
            return;
        }

        BlockUtil::load('Blocks', 'thelang');
        foreach ($obj as $block)
        {
            // translate display_name l3 -> l2
            $data = DataUtil::mb_unserialize($block['content']);
            $data['languages'] = ZLanguage::getInstalledLanguages();

            $block['content'] = serialize($data);
            DBUtil::updateObject($block, 'blocks', '', 'bid', true);
        }

        return;
    }

    public function upgrade_updateBlockLanguages()
    {
        $pntable = System::dbGetTables();
        $blockcolumn = $pntable['blocks_column'];
        $where = "WHERE $blockcolumn[language] != ''";
        $obj = DBUtil::selectObjectArray('blocks', $where);

        if (count($obj) == 0) {
            // nothing to do
            return;
        }

        foreach ($obj as $block) {
            // translate l3 -> l2
            if ($l2 = ZLanguage::translateLegacyCode($block['language'])) {
                $block['language'] = $l2;
            }
            DBUtil::updateObject($block, 'blocks', '', 'bid', true);
        }

        return;
    }
}