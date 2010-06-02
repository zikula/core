<?php
/**
 * Zikula Application Framework
 * @copyright (c) 2001, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_System_Modules
 * @subpackage Blocks
 */

/**
 * initialise the blocks module
 *
 * @author       Mark West
 * @return       bool       true on success, false otherwise
 */
function blocks_init()
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
 * @author       Mark West
 * @param        string   $oldVersion   version number string to upgrade from
 * @return       mixed    true on success, last valid version string or false if fails
 */
function blocks_upgrade($oldversion)
{
    // Upgrade dependent on old version number
    switch ($oldversion)
    {
        case '3.2':
            blocks_upgrade_fixSerializedData();
            blocks_upgrade_migrateExtMenu();

        case '3.3':
            blocks_upgrade_updateThelang();

        case '3.4':
            blocks_upgrade_updateBlockLanguages();

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
 * @author       Mark West
 * @return       bool       false
 */
function blocks_delete()
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
function blocks_defaultdata()
{
    // create the default block positions - left, right and center for the traditional 3 column layout
    ModUtil::loadApi('Blocks', 'admin', true);

    // sanity check - truncate existing tables to ensure a clean blocks setup
    DBUtil::truncateTable('blocks');
    DBUtil::truncateTable('block_positions');
    DBUtil::truncateTable('block_placements');

    $left = ModUtil::apiFunc('Blocks', 'admin', 'createposition', array('name' => 'left', 'description' => __('Left blocks')));
    $right = ModUtil::apiFunc('Blocks', 'admin', 'createposition', array('name' => 'right', 'description' => __('Right blocks')));
    $center = ModUtil::apiFunc('Blocks', 'admin', 'createposition', array('name' => 'center', 'description' => __('Center blocks')));

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
        $menucontent['blocktitles'][$lang] = __('Main menu');
        // insert the links
        $menucontent['links'][$lang][] = array('name' => __('Home'), 'url' => '{homepage}', 'title' => __("Go to the site's home page"), 'level' => 0, 'parentid' => null, 'image' => '', 'active' => '1');
        $menucontent['links'][$lang][] = array('name' => __('Administration'), 'url' => '{Admin:adminpanel:admin}', 'title' => __('Go to the administration panel'), 'level' => 0, 'parentid' => null, 'image' => '', 'active' => '1');
        $menucontent['links'][$lang][] = array('name' => __('User account panel'), 'url' => '{Users}', 'title' => __('Go to your user account panel'), 'level' => 0, 'parentid' => null, 'image' => '', 'active' => '1');
        $menucontent['links'][$lang][] = array('name' => __('Log out'), 'url' => '{Users:logout}', 'title' => __('Log out of your user account'), 'level' => 0, 'parentid' => null, 'image' => '', 'active' => '1');
    }

    ZLanguage::setLocale($saveLanguage);

    $menucontent = serialize($menucontent);
    $hellomessage = __('<p><a href="http://www.zikula.org">Zikula</a> is a content management system (CMS) and application framework. It is secure and stable, and is a good choice for sites with a large volume of traffic.</p><p>With Zikula:</p><ul><li>you can customise all aspects of the site\'s appearance through themes, with support for CSS style sheets, JavaScript, Flash and all other modern web development technologies;</li><li>you can mark content as being suitable for either a single language or for all languages, and can control all aspects of localisation and internationalisation of your site and pages;</li><li>you can be sure that your pages will display properly in all browsers, thanks to Zikula\'s full compliance with W3C HTML standards;</li><li>you get a standard application-programming interface (API) that lets you easily augment your site\'s functionality through modules, blocks and other extensions;</li><li>you can get help and support from the Zikula community of webmasters and developers at <a href="http://www.zikula.org">zikula.org</a>.</li></ul><p>Enjoy using Zikula!</p><p><strong>The Zikula team</strong></p><p><em>Note: Zikula is Free Open Source Software (FOSS) licensed under the GNU General Public License.</em></p>');
    $blocks[] = array('bkey' => 'extmenu', 'collapsable' => 1, 'defaultstate' => 1, 'language' => '', 'mid' => ModUtil::getIdFromName('Blocks'), 'title' => __('Main menu'), 'content' => $menucontent, 'positions' => array($left));
    $blocks[] = array('bkey' => 'html', 'collapsable' => 1, 'defaultstate' => 1, 'language' => '', 'mid' => ModUtil::getIdFromName('Blocks'), 'title' => __("This site is powered by Zikula!"), 'content' => $hellomessage, 'positions' => array($center));
    $blocks[] = array('bkey' => 'login', 'collapsable' => 1, 'defaultstate' => 1, 'language' => '', 'mid' => ModUtil::getIdFromName('Users'), 'title' => __('User log-in'), 'positions' => array($right));

    // create each block and then update the block
    // the create creates the initiial block record, the update sets the block placments
    foreach ($blocks as $position => $block)
    {
        $block['bid'] = ModUtil::apiFunc('Blocks', 'admin', 'create', $block);
        ModUtil::apiFunc('Blocks', 'admin', 'update', $block);
    }

    return;
}

function blocks_upgrade_fixSerializedData()
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

function blocks_upgrade_migrateExtMenu()
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

function blocks_upgrade_updateThelang()
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

function blocks_upgrade_updateBlockLanguages()
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
