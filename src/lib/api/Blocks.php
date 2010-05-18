<?php
/**
 * Zikula Application Framework
 *
 * @version $Id$
 * @license GNU/GPLv2 (or at your option any later version).
 * Please see the NOTICE and LICENSE files distributed with this source
 */


/**
 * display all blocks in a block position
 * @param $side block position to render
 */
function pnBlockDisplayPosition($side, $echo = true, $implode = true)
{
    static $blockplacements = array();
    static $positions = array();
    static $modname;
    static $currentlang;
    static $func;
    static $type;

    if (!isset($side)) {
        return null;
    }

    pnModDBInfoLoad('Blocks', 'Blocks');

    // get the block position
    if (empty($positions)) {
        $positions = DBUtil::selectObjectArray('block_positions', null, null, -1, -1, 'name');
    }
    if (!isset($positions[$side])) {
        return;
    }
    if (!isset($modname)) {
        $modname = pnModGetName();
    }

    // get the blocks in this block position
    if (empty($blockplacements)) {
        $blockplacements = DBUtil::selectObjectArray('block_placements', null, 'pn_order');
    }

    // get variables from input
    if (!isset($func)) {
        $func = FormUtil::getPassedValue('func', 'main', 'GETPOST');
    }
    if (!isset($type)) {
        $type = FormUtil::getPassedValue('type', 'user', 'GETPOST');
    }

    // loop around the blocks display only the ones we need
    if (!isset($currentlang)) {
        $currentlang = ZLanguage::getLanguageCode();
    }

    $blockoutput = array();
    foreach ($blockplacements as $blockplacement) {
        // don't display a block if it's not in this block position
        if ($blockplacement['pid'] != $positions[$side]['pid']) {
            continue;
        }
        // get the full block info
        $blockinfo = pnBlockGetInfo($blockplacement['bid']);
        // block filtering
        if (!empty($blockinfo['filter']['modules']) || !empty($blockinfo['filter']['type']) || !empty($blockinfo['filter']['func']) || !empty($blockinfo['filter']['customargs'])) {
            // check the module name
            if (!empty($blockinfo['filter']['modules']) && !in_array($modname, $blockinfo['filter']['modules'])) {
                continue;
            }
            // check the function type
            if (!empty($blockinfo['filter']['type'])) {
                $blockinfo['filter']['type'] = explode(',', $blockinfo['filter']['type']);
                if (!in_array($type, $blockinfo['filter']['type'])) {
                    continue;
                }
            }
            // check the function name
            if (!empty($blockinfo['filter']['functions'])) {
                $blockinfo['filter']['functions'] = explode(',', $blockinfo['filter']['functions']);
                if (!in_array($func, $blockinfo['filter']['functions'])) {
                    continue;
                }
            }
            if (!empty($blockinfo['filter']['customargs'])) {
                $blockinfo['filter']['customargs'] = explode(',', $blockinfo['filter']['customargs']);
                $customargs = array();
                static $filtervars = array('module', 'name', 'type', 'func', 'theme', 'authid');
                foreach ($_GET as $var => $value) {
                    if (!in_array($var, $filtervars)) {
                        $customargs[] = DataUtil::formatForOS(strip_tags($var)) . '=' . DataUtil::formatForOS(strip_tags($value));
                    }
                }
                if (!array_intersect($customargs, $blockinfo['filter']['customargs'])) {
                    continue;
                }
            }
        }

        // dont display the block if it's not active or not in matching langauge
        if (!$blockinfo['active'] || (!empty($blockinfo['language']) && $blockinfo['language'] != $currentlang)) {
            continue;
        }

        $blockinfo['position'] = $positions[$side]['name'];
        // get the module info and display the block
        $modinfo = pnModGetInfo($blockinfo['mid']);
        if ($echo) {
            echo pnBlockShow($modinfo['name'], $blockinfo['bkey'], $blockinfo);
        } else {
            $blockoutput[] = pnBlockShow($modinfo['name'], $blockinfo['bkey'], $blockinfo);
        }
    }

    if ($echo) {
        return;
    } else {
        if ($implode) {
            return implode("\n", $blockoutput);
        } else {
            return $blockoutput;
        }
    }
}

/**
 * show a block
 *
 * @param string $modname module name
 * @param string $block name of the block
 * @param array $blockinfo information parameters
 * @return mixed blockinfo array or null
 */
function pnBlockShow($modname, $block, $blockinfo = array())
{
    global $blocks_modules;

    pnBlockLoad($modname, $block);

    $displayfunc = "{$modname}_{$block}block_display";

    if (function_exists($displayfunc)) {
        // New-style blocks
        return $displayfunc($blockinfo);
    } else {
        // Old-style blocks
        if (isset($blocks_modules[0][$block]['func_display'])) {
            return $blocks_modules[0][$block]['func_display']($blockinfo);
        } else {
            if (SecurityUtil::checkPermission('.*', '.*', ACCESS_ADMIN)) {
                $blockinfo['title'] = __f("Block type '%s' not found", $block);
                $blockinfo['content'] = __f("Error! The '%s' block type was not found. Please check the corresponding blocks directory.", $block);
                return pnBlockThemeBlock($blockinfo);
            }
        }
    }
}

/**
 * Display a block based on the current theme
 */
function pnBlockThemeBlock($row)
{
    static $themeinfo, $themedir, $upb, $downb;

    if (!isset($row['bid'])) {
        $row['bid'] = '';
    }
    if (!isset($row['title'])) {
        $row['title'] = '';
    }

    if (!isset($themeinfo)) {
        $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName(pnUserGetTheme()));
        $themedir = DataUtil::formatForOS($themeinfo['directory']);
    }

    // check for collapsable menus being enabled, and setup the collapsable menu image.
    if (!isset($upb)) {
        if (file_exists('themes/' . $themedir . '/images/upb.gif')) {
            $upb = '<img src="themes/' . $themedir . '/images/upb.gif" alt="-" />';
        } elseif (file_exists('themes/' . $themedir . '/images/14_layer_raiselayer.gif')) {
            $upb = '<img src="themes/' . $themedir . '/images/14_layer_raiselayer.gif" alt="-" />';
        } else {
            $upb = '<img src="images/icons/extrasmall/14_layer_raiselayer.gif" alt="-" />';
        }
    }
    if (!isset($downb)) {
        if (file_exists('themes/' . $themedir . '/images/downb.gif')) {
            $downb = '<img src="themes/' . $themedir . '/images/downb.gif" alt="+" />';
        } elseif (file_exists('themes/' . $themedir . '/images/14_layer_lowerlayer.gif')) {
            $downb = '<img src="themes/' . $themedir . '/images/14_layer_lowerlayer.gif" alt="+" />';
        } else {
            $downb = '<img src="images/icons/extrasmall/14_layer_lowerlayer.gif" alt="+" />';
        }
    }

    if (pnUserLoggedIn() && ModUtil::getVar('Blocks', 'collapseable') == 1 && isset($row['collapsable']) && ($row['collapsable'] == '1')) {
        if (pnCheckUserBlock($row) == '1') {
            if (!empty($row['title'])) {
                $row['minbox'] = '<a href="' . DataUtil::formatForDisplay(ModUtil::url('Blocks', 'user', 'changestatus', array('bid' => $row['bid'], 'authid' => pnSecGenAuthKey()))) . '">' . $upb . '</a>';
            }
        } else {
            $row['content'] = '';
            if (!empty($row['title'])) {
                $row['minbox'] = '<a href="' . DataUtil::formatForDisplay(ModUtil::url('Blocks', 'user', 'changestatus', array('bid' => $row['bid'], 'authid' => pnSecGenAuthKey()))) . '">' . $downb . '</a>';
            }
        }
    } else {
        $row['minbox'] = '';
    }
    // end collapseable menu config

    return Theme::getInstance()->themesidebox($row);
}

/**
 * load a block
 *
 * @param string $modname module name
 * @param string $block name of the block
 * @return bool true on successful load, false otherwise
 */
function pnBlockLoad($modname, $block)
{
    static $loaded = array();

    if (isset($loaded["$modname/$block"])) {
        return true;
    }

    $modinfo = pnModGetInfo(pnModGetIdFromName($modname));

    if ($modinfo['i18n']) {
        ZLanguage::bindModuleDomain($modinfo['name']);
    }

    $moddir = DataUtil::formatForOS($modinfo['directory']);
    $blockdir = $moddir . '/pnblocks';
    $incfile = DataUtil::formatForOS($block . '.php');

    $files = array();
    if ($modinfo['type'] == 3) {
        $files[] = 'system/' . $blockdir . '/' . $incfile;
        $rc = Loader::loadOneFile($files);
        if (!$rc) {
            return false;
        }
    } elseif ($modinfo['type'] == 2) {
        $files[] = 'modules/' . $blockdir . '/' . $incfile;
        $rc = Loader::loadOneFile($files);
        if (!$rc) {
            return false;
        }
    } else {
        return false;
    }

    $loaded["$modname/$block"] = 1;

    // get the block info
    $infofunc = "{$modname}_{$block}block_info";
    if (function_exists($infofunc)) {
        $blocks_modules[$block] = $infofunc();
    }

    // set the module and keys for the new block
    $blocks_modules[$block]['bkey'] = $block;
    $blocks_modules[$block]['module'] = $modname;
    $blocks_modules[$block]['mid'] = pnModGetIDFromName($modname);

    // merge the blockinfo in the global list of blocks
    if (!isset($GLOBALS['blocks_modules'])) {
        $GLOBALS['blocks_modules'] = array();
    }
    $GLOBALS['blocks_modules'][$blocks_modules[$block]['mid']][$block] = $blocks_modules[$block];

    // Initialise block if required (new-style)
    $initfunc = "{$modname}_{$block}block_init";
    if (function_exists($initfunc)) {
        $initfunc();
    }

    // add stylesheet to the page vars, this makes manual loading obsolete
    PageUtil::addVar('stylesheet', ThemeUtil::getModuleStylesheet($modname));

    return true;
}

/**
 * load all blocks
 * @return array array of blocks
 */
function pnBlockLoadAll()
{
    static $blockdirs = array();

    // Load new-style blocks from system and modules tree
    $mods = pnModGetAllMods();

    foreach ($mods as $mod) {
        $modname = $mod['name'];
        $moddir = DataUtil::formatForOS($mod['directory']);

        if (!isset($blockdirs[$modname])) {
            $blockdirs[$modname] = array();
            $blockdirs[$modname][] = 'system/' . $moddir . '/pnblocks';
            $blockdirs[$modname][] = 'modules/' . $moddir . '/pnblocks';

            foreach ($blockdirs[$modname] as $dir) {
                if (is_dir($dir) && is_readable($dir)) {
                    $dh = opendir($dir);
                    while (($f = readdir($dh)) !== false) {
                        if (substr($f, -4) == '.php') {
                            $block = substr($f, 0, -4);
                            pnBlockLoad($modname, $block);
                        }
                    }
                    closedir($dh);
                }
            }
        }
    }

    /* TODO A - review this code - drak
    $dir = 'config/blocks';
    if (is_dir($dir) && is_readable($dir)) {
        $dh = opendir($dir);
        while (($f = readdir($dh)) !== false) {
            if (substr($f, -4) == '.php') {
                $block = substr($f, 0, -4);
                pnBlockLoad('Legacy', $block);
            }
        }
        closedir($dh);
    }
    */

    // Return information gathered
    return $GLOBALS['blocks_modules'];
}

/**
 * extract an array of config variables out of the content field of a
 * block
 *
 * @param the $ content from the db
 */
function pnBlockVarsFromContent($content)
{
    // Assume serialized content ends in a ";" followed by some curly-end-braces
    if (preg_match('/;}*$/', $content)) {
        // Serialised content
        $vars = unserialize($content);
        return $vars;
    }

    // Unserialised content
    $links = explode("\n", $content);
    $vars = array();
    foreach ($links as $link) {
        $link = trim($link);
        if ($link) {
            $var = explode(':=', $link);
            if (isset($var[1])) {
                $vars[$var[0]] = $var[1];
            }
        }
    }

    return $vars;
}

/**
 * put an array of config variables in the content field of a block
 *
 * @param the $ config vars array, in key->value form
 */
function pnBlockVarsToContent($vars)
{
    return (serialize($vars));
}

/**
 * Checks if user controlled block state
 *
 * Checks if the user has a state set for a current block
 * Sets the default state for that block if not present
 *
 * @access private
 */
function pnCheckUserBlock($row)
{
    if (!isset($row['bid'])) {
        $row['bid'] = '';
    }
    if (pnUserLoggedIn()) {
        $uid = pnUserGetVar('uid');
        $pntable = pnDBGetTables();
        $column = $pntable['userblocks_column'];
        $where = "WHERE $column[bid] = '" . DataUtil::formatForStore($row['bid']) . "'
                  AND $column[uid] = '" . DataUtil::formatForStore($uid) . "'";

        $result = DBUtil::selectObject('userblocks', $where);
        if ($result === false) {
            LogUtil::registerError(__f('Error! A database error occurred: \'%1$s: %2$s\'.', array($dbconn->ErrorNo(), $dbconn->ErrorMsg())));
            return true; // FIXME: should this really return true (RNG)
        }
        if (!$result) {
            $uid = DataUtil::formatForStore($uid);
            $obj = array('uid' => $uid, 'bid' => $row['bid'], 'active' => $row['defaultstate']);
            if (!DBUtil::insertObject($obj, 'userblocks', 'bid', true)) {
                LogUtil::registerError(__f('Error! A database error occurred: \'%1$s: %2$s\'.', array($dbconn->ErrorNo(), $dbconn->ErrorMsg())));
                return true; // FIXME: should this really return true (RNG)
            }
            return true; // FIXME: should this really return true (RNG)
        } else {
            return $result['active'];
        }
    }

    return false;
}

/**
 * get block information
 * @param value the value to search for
 * @param assocKey the field in which we look for the value (optional) (default='bid')
 * @return array array of block information
 */
function pnBlocksGetInfo()
{
    pnModDBInfoLoad('Blocks', 'Blocks');
    return DBUtil::selectObjectArray('blocks');
}

/**
 * get block information
 * @param value the value to search for
 * @param assocKey the field in which we look for the value (optional) (default='bid')
 * @return array array of block information
 */
function pnBlockGetInfo($value, $assocKey = 'bid')
{
    static $blockinfo = array();

    if (!isset($blockinfo[$assocKey]) || empty($blockinfo[$assocKey])) {
        $blockinfo[$assocKey] = array();
        $blocks = pnBlocksGetInfo();
        $ak = array_keys($blocks);
        foreach ($ak as $k) {
            $key = $blocks[$k][$assocKey];
            $blocks[$k]['filter'] = strlen($blocks[$k]['filter']) > 0 ? (array) unserialize($blocks[$k]['filter']) : array();
            $blockinfo[$assocKey][$key] = $blocks[$k];
        }
    }

    if (isset($blockinfo[$assocKey][$value])) {
        return $blockinfo[$assocKey][$value];
    }

    return false;
}

/**
 * get block information
 * @param title the block title
 * @return array array of block information
 */
function pnBlockGetInfoByTitle($title)
{
    return pnBlockGetInfo($title, 'title');
}

/**
 * alias to pnBlockDisplayPosition
 */
function blocks($side)
{
    return pnBlockDisplayPosition($side);
}

/**
 * alias to pnBlockDisplayPosition
 */
function themesideblock($row)
{
    return pnBlockThemeBlock($row);
}
