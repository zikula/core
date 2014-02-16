<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Util
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Block util.
 */
class BlockUtil
{
    /**
     * Display all blocks in a block position.
     *
     * @param string  $side    Block position to render.
     * @param boolean $echo    Whether or not to echo output directly.
     * @param boolean $implode Whether or not to implode lines by \n.
     *
     * @return void|string The rendered output.
     */
    public static function displayPosition($side, $echo = true, $implode = true)
    {
        static $blockplacements = array();
        static $positions = array();
        static $modname;
        static $currentlang;
        static $func;
        static $type;
        static $customargs;

        if (!isset($side)) {
            return null;
        }

        // get the block position
        if (empty($positions)) {
            $positions = ModUtil::apiFunc('Blocks', 'user', 'getallpositions');
        }

        if (!isset($positions[$side])) {
            return;
        }

        if (!isset($modname)) {
            if (PageUtil::isHomepage()) {
                $modname = '_homepage_';
            } else {
                $modname = ModUtil::getName();
            }
        }

        // get all block placements
        if (empty($blockplacements)) {
            $blockplacements = ModUtil::apiFunc('Blocks', 'user', 'getallplacements');
        }

        // get variables from input
        if (!isset($func)) {
            $func = FormUtil::getPassedValue('func', 'main', 'GETPOST');
        }
        if (!isset($type)) {
            $type = FormUtil::getPassedValue('type', 'user', 'GETPOST');
        }

        if (!isset($customargs)) {
            $customargs = array();
            $filtervars = array('module', 'name', 'type', 'func', 'theme', 'authid', 'csrftoken');
            foreach ($_GET as $var => $value) {
                if (is_array($value)) {
                    $arguments = explode('&', urldecode(http_build_query(array($var => $value))));
                    foreach ($arguments as $argument) {
                        $args = explode('=', $argument);
                        if (!in_array($args[0], $filtervars)) {
                            $customargs[] = DataUtil::formatForOS(strip_tags($args[0])) . '=' . DataUtil::formatForOS(strip_tags($args[1]));
                        }
                    }
                } else {
                    if (!in_array($var, $filtervars)) {
                        $customargs[] = DataUtil::formatForOS(strip_tags($var)) . '=' . DataUtil::formatForOS(strip_tags($value));
                    }
                }
            }
        }

        // current language
        if (!isset($currentlang)) {
            $currentlang = ZLanguage::getLanguageCode();
        }

        // loop around the blocks and display only the ones we need
        $blockoutput = array();
        foreach ($blockplacements as $blockplacement) {
            // don't display a block if it's not in this block position
            if ($blockplacement['pid'] != $positions[$side]['pid']) {
                continue;
            }

            // get the full block info
            $blockinfo = self::getBlockInfo($blockplacement['bid']);

            // dont display the block if it's not active or not in matching langauge
            if (!$blockinfo['active'] || (!empty($blockinfo['language']) && $blockinfo['language'] != $currentlang)) {
                continue;
            }

            // block filtering
            if (!empty($blockinfo['filter']) && is_array($blockinfo['filter']) && count($blockinfo['filter'])) {

                $showblock = false;

                // loop for each filter
                foreach ($blockinfo['filter'] as $filter) {
                    // filter must be an array of values
                    if (!is_array($filter)) {
                        continue;
                    }

                    $rule1 = strtolower($filter['module']) == strtolower($modname);
                    $rule2 = empty($filter['ftype']) ? true : (strtolower($filter['ftype']) == strtolower($type));
                    $rule3 = empty($filter['fname']) ? true : (strtolower($filter['fname']) == strtolower($func));

                    if (empty($filter['fargs'])) {
                        $rule4 = true;
                    } else {
                        $testargs = explode('&', $filter['fargs']);
                        foreach ($testargs as $test) {
                            $key = array_search($test, $customargs);
                            if ($key === false) {
                                $rule4 = false;
                                break;
                            } else {
                                $rule4 = true;
                            }
                        }
                    }

                    if ($rule1 == true && $rule2 == true && $rule3 == true && $rule4 !== false) {
                        $showblock = true;
                        break;
                    }
                }

                if (!$showblock) {
                    continue;
                }
            }

            $blockinfo['position'] = $positions[$side]['name'];

            // get the module info and display the block
            $modinfo = ModUtil::getInfo($blockinfo['mid']);

            if ($echo) {
                echo self::show($modinfo['name'], $blockinfo['bkey'], $blockinfo);
            } else {
                $blockoutput[$blockinfo['bid']] = self::show($modinfo['name'], $blockinfo['bkey'], $blockinfo);
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
     * Show a block.
     *
     * @param string $modname   Module name.
     * @param string $blockname Name of the block.
     * @param array  $blockinfo Information parameters.
     *
     * @return mixed Blockinfo array or null.
     */
    public static function show($modname, $blockname, $blockinfo = array())
    {
        global $blocks_modules;

        $blockInstance = self::load($modname, $blockname);

        if ($blockInstance instanceof Zikula_Controller_AbstractBlock) {
            $displayfunc = array($blockInstance, 'display');
        } else {
            $displayfunc = "{$modname}_{$blockname}block_display";
        }

        if (is_callable($displayfunc)) {
            if (is_array($displayfunc)) {
                return call_user_func($displayfunc, $blockinfo);
            } else {
                return $displayfunc($blockinfo);
            }
        } else {
            // Old-style blocks
            if (isset($blocks_modules[0][$blockname]['func_display'])) {
                return $blocks_modules[0][$blockname]['func_display']($blockinfo);
            } else {
                if (SecurityUtil::checkPermission('.*', '.*', ACCESS_ADMIN)) {
                    $blockinfo['title'] = __f("Block type '%s' not found", $blockname);
                    $blockinfo['content'] = __f("Error! The '%s' block type was not found. Please check the corresponding blocks directory.", $blockname);

                    return self::themeBlock($blockinfo);
                }
            }
        }
    }

    /**
     * Display a block based on the current theme.
     *
     * @param array $blockinfo Block info.
     *
     * @return string The rendered output.
     */
    public static function themeBlock($blockinfo)
    {
        static $themeinfo, $themedir, $upb, $downb;

        if (!isset($blockinfo['bid'])) {
            $blockinfo['bid'] = '';
        }
        if (!isset($blockinfo['title'])) {
            $blockinfo['title'] = '';
        }

        if (UserUtil::isLoggedIn() && ModUtil::getVar('Blocks', 'collapseable') == 1 && isset($blockinfo['collapsable']) && ($blockinfo['collapsable'] == '1')) {
            if (!isset($themeinfo)) {
                $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName(UserUtil::getTheme()));
                $themedir = DataUtil::formatForOS($themeinfo['directory']);
            }

            // check for collapsable menus being enabled, and setup the collapsable menu image.
            if (!isset($upb)) {
                if (file_exists('themes/' . $themedir . '/images/upb.png')) {
                    $upb = '<img src="themes/' . $themedir . '/images/upb.png" alt="-" />';
                } elseif (file_exists('themes/' . $themedir . '/images/14_layer_raiselayer.png')) {
                    $upb = '<img src="themes/' . $themedir . '/images/14_layer_raiselayer.png" alt="-" />';
                } else {
                    $upb = '<img src="images/icons/extrasmall/14_layer_raiselayer.png" alt="-" />';
                }
            }
            if (!isset($downb)) {
                if (file_exists('themes/' . $themedir . '/images/downb.png')) {
                    $downb = '<img src="themes/' . $themedir . '/images/downb.png" alt="+" />';
                } elseif (file_exists('themes/' . $themedir . '/images/14_layer_lowerlayer.png')) {
                    $downb = '<img src="themes/' . $themedir . '/images/14_layer_lowerlayer.png" alt="+" />';
                } else {
                    $downb = '<img src="images/icons/extrasmall/14_layer_lowerlayer.png" alt="+" />';
                }
            }

            $checkUserBlock = self::checkUserBlock($blockinfo);
            if ($checkUserBlock) {
                if (!empty($blockinfo['title'])) {
                    $blockinfo['minbox'] = '<a href="' . DataUtil::formatForDisplay(ModUtil::url('Blocks', 'user', 'changestatus', array('bid' => $blockinfo['bid']))) . '">' . $upb . '</a>';
                }
            } else {
                $blockinfo['content'] = '';
                if (!empty($blockinfo['title'])) {
                    $blockinfo['minbox'] = '<a href="' . DataUtil::formatForDisplay(ModUtil::url('Blocks', 'user', 'changestatus', array('bid' => $blockinfo['bid']))) . '">' . $downb . '</a>';
                }
            }
            // end collapseable menu config
        } else {
            $blockinfo['minbox'] = '';
        }

        return Zikula_View_Theme::getInstance()->themesidebox($blockinfo);
    }

    /**
     * Load a block.
     *
     * @param string $modname Module name.
     * @param string $block   Name of the block.
     *
     * @throws LogicException Uf OO-Block is not a Zikula_Controller_AbstractBlock object.
     * @return bool           True on successful load, false otherwise.
     */
    public static function load($modname, $block)
    {
        $sm = ServiceUtil::getManager();
        $modinfo = ModUtil::getInfoFromName($modname);

        $serviceId = strtolower('block.' . $modinfo['name'] . '_' . 'Block_' . $block);
        if ($sm->hasService($serviceId)) {
            return $sm->getService($serviceId);
        }

        if ($modinfo['type'] == ModUtil::TYPE_MODULE) {
            ZLanguage::bindModuleDomain($modinfo['name']);
        }

        $basedir = ($modinfo['type'] == ModUtil::TYPE_SYSTEM) ? 'system' : 'modules';
        $moddir = DataUtil::formatForOS($modinfo['directory']);
        $blockdir = "$basedir/$moddir/lib/$moddir/Block";
        $ooblock = "$blockdir/" . ucwords($block) . '.php';
        ModUtil::load($modname);
        $isOO = ModUtil::isOO($modname);

        if (!$isOO) {
            $blockdirOld = $moddir . '/pnblocks';
            $incfile = DataUtil::formatForOS($block . '.php');

            if (file_exists("$basedir/$blockdirOld/$incfile")) {
                include_once "$basedir/$blockdirOld/$incfile";
            } else {
                return false;
            }
        }

        // get the block info
        if ($isOO) {
            $className = ucwords($modinfo['name']) . '_' . 'Block_' . ucwords($block);
            $r = new ReflectionClass($className);
            $blockInstance = $r->newInstanceArgs(array($sm));
            try {
                if (!$blockInstance instanceof Zikula_Controller_AbstractBlock) {
                    throw new LogicException(sprintf('Block %s must inherit from Zikula_Controller_AbstractBlock', $className));
                }
            } catch (LogicException $e) {
                if (System::isDevelopmentMode()) {
                    throw $e;
                } else {
                    LogUtil::registerError('A fatal error has occured which can be viewed only in development mode.', 500);

                    return false;
                }
            }

            $sm->attachService($serviceId, $blockInstance);
        }

        $result = ($isOO ? $blockInstance : true);

        if ($isOO) {
            $blocks_modules[$block] = call_user_func(array($blockInstance, 'info'));
        } else {
            $infofunc = "{$modname}_{$block}block_info";
            $blocks_modules[$block] = $infofunc();
        }

        // set the module and keys for the new block
        $blocks_modules[$block]['bkey'] = $block;
        $blocks_modules[$block]['module'] = $modname;
        $blocks_modules[$block]['mid'] = ModUtil::getIdFromName($modname);

        // merge the blockinfo in the global list of blocks
        if (!isset($GLOBALS['blocks_modules'])) {
            $GLOBALS['blocks_modules'] = array();
        }
        $GLOBALS['blocks_modules'][$blocks_modules[$block]['mid']][$block] = $blocks_modules[$block];

        // Initialise block if required (new-style)
        if ($isOO) {
            call_user_func(array($blockInstance, 'init'));
        } else {
            $initfunc = "{$modname}_{$block}block_init";
            $initfunc();
        }

        // add stylesheet to the page vars, this makes manual loading obsolete
        PageUtil::addVar('stylesheet', ThemeUtil::getModuleStylesheet($modname));

        return $result;
    }

    /**
     * Load all blocks.
     *
     * @return array Array of blocks.
     */
    public static function loadAll()
    {
        static $blockdirs = array();

        // Load new-style blocks from system and modules tree
        $mods = ModUtil::getAllMods();

        foreach ($mods as $mod) {
            $modname = $mod['name'];
            $moddir = DataUtil::formatForOS($mod['directory']);

            if (!isset($blockdirs[$modname])) {
                $blockdirs[$modname] = array();
                $blockdirs[$modname][] = "system/$moddir/lib/$moddir/Block";
                $blockdirs[$modname][] = "modules/$moddir/lib/$moddir/Block";
                $blockdirs[$modname][] = "modules/$moddir/pnblocks";

                foreach ($blockdirs[$modname] as $dir) {
                    if (is_dir($dir) && is_readable($dir)) {
                        $dh = opendir($dir);
                        while (($f = readdir($dh)) !== false) {
                            if (substr($f, -4) == '.php') {
                                $block = substr($f, 0, -4);
                                self::load($modname, $block);
                            }
                        }
                        closedir($dh);
                    }
                }
            }
        }

        // Return information gathered
        return $GLOBALS['blocks_modules'];
    }

    /**
     * Extract an array of config variables out of the content field of a block.
     *
     * @param string $content The content from the db.
     *
     * @return array
     */
    public static function varsFromContent($content)
    {
        // Try to unserialize first
        if (DataUtil::is_serialized($content, false)) {
            $vars = unserialize($content);
            if ($vars !== false && is_array($vars)) {
                return $vars;
            }
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
     * Put an array of config variables in the content field of a block.
     *
     * @param array $vars The config vars array, in key->value form.
     *
     * @return string The config var string.
     */
    public static function varsToContent($vars)
    {
        return (serialize($vars));
    }

    /**
     * Checks if user controlled block state.
     *
     * Checks if the user has a state set for a current block.
     * Sets the default state for that block if not present.
     *
     * @param array $blockinfo Block info.
     *
     * @return boolean
     */
    public static function checkUserBlock($blockinfo)
    {
        if (UserUtil::isLoggedIn()) {
            $uid = UserUtil::getVar('uid');

            $sm = ServiceUtil::getManager();
            $entityManager = $sm->getService('doctrine.entitymanager');

            $entity = 'Blocks_Entity_UserBlock';
            $item = $entityManager->getRepository($entity)->findOneBy(array('uid' => $uid, 'bid' => $blockinfo['bid']));

            if (!$item) {
                $item = new $entity;
                $item['uid'] = (int)$uid;
                $item['bid'] = $blockinfo['bid'];
                $item['active'] = $blockinfo['defaultstate'];

                $entityManager->persist($item);
                $entityManager->flush();
            }

            return (boolean)$item['active'];
        }

        return false;
    }

    /**
     * Get block information.
     *
     * @return array Array of block information.
     */
    public static function getBlocksInfo()
    {
        return ModUtil::apiFunc('Blocks', 'user', 'getall');
    }

    /**
     * Get block information.
     *
     * @param string $value    The value to search for.
     * @param string $assocKey The field in which we look for the value (optional) (default='bid').
     *
     * @return array Array of block information.
     */
    public static function getBlockInfo($value, $assocKey = 'bid')
    {
        static $blockinfo = array();

        if (!isset($blockinfo[$assocKey]) || empty($blockinfo[$assocKey])) {
            $blockinfo[$assocKey] = array();
            $blocks = self::getBlocksInfo();
            foreach ($blocks as $block) {
                $key = $block[$assocKey];
                $blockinfo[$assocKey][$key] = $block->toArray();
            }
        }

        if (isset($blockinfo[$assocKey][$value])) {
            return $blockinfo[$assocKey][$value];
        }

        return false;
    }

    /**
     * Get block information.
     *
     * @param string $title The block title.
     *
     * @return array Array of block information.
     */
    public static function getInfoByTitle($title)
    {
        return self::getBlockInfo($title, 'title');
    }

    /**
     * Alias to pnBlockDisplayPosition.
     *
     * @param string $side Block position to render.
     *
     * @return string The rendered output.
     */
    public static function blocks($side)
    {
        return self::displayPosition($side);
    }

    /**
     * Alias to pnBlockDisplayPosition.
     *
     * @param array $blockinfo Block info.
     *
     * @return string The rendered output.
     */
    public static function themesideblock($blockinfo)
    {
        return self::themeBlock($blockinfo);
    }

}
