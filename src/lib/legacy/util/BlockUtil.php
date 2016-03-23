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

use Zikula\BlocksModule\BlockHandlerInterface;

/**
 * Block util.
 * @deprecated remove at Core-2.0
 */
class BlockUtil
{
    /**
     * Display all blocks in a block position.
     *
     * @param string $side     Block position to render.
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
            $positions = ModUtil::apiFunc('ZikulaBlocksModule', 'user', 'getallpositions');
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
            $blockplacements = ModUtil::apiFunc('ZikulaBlocksModule', 'user', 'getallplacements');
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
            $filtervars = array('module', 'name', 'type', 'func', 'theme', 'csrftoken');
            foreach ($_GET as $var => $value) {
                if (is_array($value)) {
                    $arguments = explode('&', urldecode(http_build_query(array($var => $value))));
                    foreach ($arguments as $argument) {
                        $args = explode('=', $argument);
                        if (!in_array($args[0], $filtervars)) {
                            $customargs[] = DataUtil::formatForOS(strip_tags($args[0])).'='.DataUtil::formatForOS(
                                    strip_tags($args[1])
                                );
                        }
                    }
                } else {
                    if (!in_array($var, $filtervars)) {
                        $customargs[] = DataUtil::formatForOS(strip_tags($var)).'='.DataUtil::formatForOS(
                                strip_tags($value)
                            );
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
                    $rule1 = $filter['module'] == $modname;
                    $rule2 = empty($filter['ftype']) ? true : ($filter['ftype'] == $type);
                    $rule3 = empty($filter['fname']) ? true : ($filter['fname'] == $func);
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
            $blockinfo['mid'] = $blockinfo['module']['id'];
            // get the module info and display the block
            $modinfo = ModUtil::getInfo($blockinfo['mid']);
            $blockOutput = self::show($modinfo['name'], $blockinfo['bkey'], $blockinfo, $blockplacement->getBlock());
            // wrap block if theme requires it.
            $blockWrapper = (bool) Zikula_View_Theme::getInstance()->themeconfig['blockwrapper'];
            if ($blockWrapper) {
                $blockOutput = '<div class="z-block '. 'z-blockposition-'.DataUtil::formatForDisplay($blockinfo['position']).' z-bkey-' . DataUtil::formatForDisplay(strtolower($blockinfo['bkey'])) . ' z-bid-' . DataUtil::formatForDisplay($blockinfo['bid']) . '">' . "\n" . $blockOutput . "</div>\n";
            }
            if ($echo) {
                echo $blockOutput;
            } else {
                $blockoutput[$blockinfo['bid']] = $blockOutput;
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
     * @param string $modname Module name.
     * @param string $blockname Name of the block.
     * @param array $blockinfo Information parameters.
     * @param null $blockEntity
     * @return mixed Blockinfo array or null.
     */
    public static function show($modname, $blockname, $blockinfo = array(), $blockEntity = null)
    {
        global $blocks_modules;
        $content = '';
        $blockInstance = self::load($modname, $blockname);
        $displayfunc = array($blockInstance, 'display');
        $blockEntity = isset($blockEntity) ? $blockEntity : ServiceUtil::get('doctrine.orm.default_entity_manager')->find('Zikula\BlocksModule\Entity\BlockEntity', $blockinfo['bid']);
        $instanceArgs = ($blockInstance instanceof BlockHandlerInterface) ? $blockEntity->getContent() : $blockinfo;
        if (is_callable($displayfunc)) {
            $content =  call_user_func($displayfunc, $instanceArgs);
        }
        if ($blockInstance instanceof BlockHandlerInterface) {
            // FC blocks require wrapping the content in the theme
            $blockinfo['content'] = $content;
            $content = Zikula_View_Theme::getInstance()->themesidebox($blockinfo);
        }

        return $content;
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
        if (UserUtil::isLoggedIn() && ModUtil::getVar(
                'ZikulaBlocksModule',
                'collapseable'
            ) == 1 && isset($blockinfo['collapsable']) && ($blockinfo['collapsable'] == '1')
        ) {
            if (!isset($themeinfo)) {
                $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName(UserUtil::getTheme()));
                $themedir = DataUtil::formatForOS($themeinfo['directory']);
            }
            // check for collapsable menus being enabled, and setup the collapsable menu image.
            if (!isset($upb)) {
                if (file_exists('themes/'.$themedir.'/images/upb.png')) {
                    $upb = '<img src="themes/'.$themedir.'/images/upb.png" alt="-" />';
                } elseif (file_exists('themes/'.$themedir.'/images/14_layer_raiselayer.png')) {
                    $upb = '<img src="themes/'.$themedir.'/images/14_layer_raiselayer.png" alt="-" />';
                } else {
                    $upb = '<img src="images/icons/extrasmall/14_layer_raiselayer.png" alt="-" />';
                }
            }
            if (!isset($downb)) {
                if (file_exists('themes/'.$themedir.'/images/downb.png')) {
                    $downb = '<img src="themes/'.$themedir.'/images/downb.png" alt="+" />';
                } elseif (file_exists('themes/'.$themedir.'/images/14_layer_lowerlayer.png')) {
                    $downb = '<img src="themes/'.$themedir.'/images/14_layer_lowerlayer.png" alt="+" />';
                } else {
                    $downb = '<img src="images/icons/extrasmall/14_layer_lowerlayer.png" alt="+" />';
                }
            }
            $checkUserBlock = self::checkUserBlock($blockinfo);
            if ($checkUserBlock) {
                if (!empty($blockinfo['title'])) {
                    $blockinfo['minbox'] = '<a href="'.DataUtil::formatForDisplay(
                            ModUtil::url(
                                'ZikulaBlocksModule',
                                'user',
                                'changestatus',
                                array('bid' => $blockinfo['bid'])
                            )
                        ).'">'.$upb.'</a>';
                }
            } else {
                $blockinfo['content'] = '';
                if (!empty($blockinfo['title'])) {
                    $blockinfo['minbox'] = '<a href="'.DataUtil::formatForDisplay(
                            ModUtil::url(
                                'ZikulaBlocksModule',
                                'user',
                                'changestatus',
                                array('bid' => $blockinfo['bid'])
                            )
                        ).'">'.$downb.'</a>';
                }
            }
            // end collapseable menu config
        } else {
            $blockinfo['minbox'] = '';
        }

        // try twig theme first (note: theme is already set by this point)
        $container = ServiceUtil::getManager();
        $twigBasedThemeBlock = $container->get('zikula_core.common.theme_engine')->wrapBcBlockInTheme($blockinfo);
        if ($twigBasedThemeBlock) {
            return $twigBasedThemeBlock;
        }
        // theme is not twig based revert to smarty
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
        if ($modinfo['state'] != \ModUtil::STATE_ACTIVE) {
            return false;
        }
        $serviceId = strtolower('block.'.$modinfo['name'].'_'.'Block_'.$block);
        if ($sm->has($serviceId)) {
            return $sm->get($serviceId);
        }
        if ($modinfo['type'] == ModUtil::TYPE_MODULE) {
            ZLanguage::bindModuleDomain($modinfo['name']);
        }
        $basedir = ($modinfo['type'] == ModUtil::TYPE_SYSTEM) ? 'system' : 'modules';
        $moddir = DataUtil::formatForOS($modinfo['directory']);
        $blockdir = "$basedir/$moddir/lib/$moddir/Block";
        $ooblock = "$blockdir/".ucwords($block).'.php';
        ModUtil::load($modname);
        // get the block info
        $kernel = $sm->get('kernel');
        $module = null;
        try {
            /** @var $module \Zikula\Core\AbstractModule */
            $module = $kernel->getModule($modinfo['name']);
            $className = $module->getNamespace().'\\Block\\'.ucwords($block);
            $className = preg_match('/.*Block$/', $className) ? $className : $className.'Block';
        } catch (\InvalidArgumentException $e) {
        }
        if (!isset($className)) {
            $className = ucwords($modinfo['name']).'\\'.'Block\\'.ucwords($block);
            $className = preg_match('/.*Block$/', $className) ? $className : $className.'Block';
            $classNameOld = ucwords($modinfo['name']).'_'.'Block_'.ucwords($block);
            $className = class_exists($className) ? $className : $classNameOld;
        }
        $blockInstance = \ServiceUtil::get('zikula_blocks_module.api.block_factory')->getInstance($className, $module);

        $sm->set($serviceId, $blockInstance);
        if ($blockInstance instanceof \Symfony\Component\DependencyInjection\ContainerAwareInterface) {
            $blockInstance->setContainer($sm);
        }
        $result = $blockInstance;
        if ($blockInstance instanceof Zikula_Controller_AbstractBlock) {
            $blocks_modules[$block] = call_user_func(array($blockInstance, 'info'));
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
        if ($blockInstance instanceof Zikula_Controller_AbstractBlock) {
            call_user_func(array($blockInstance, 'init'));
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
        return \ServiceUtil::get('zikula_blocks_module.api.block')->getAvailableBlockTypes();
        // returns [[ModuleName:FqBlockClassName => ModuleDisplayName/BlockDisplayName]]
        // [[serviceId => ModuleDisplayName/BlockDisplayName]]
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
        // content is now automatically unserialized at the entity level
        return $content;
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
        // content is now automatically serialized at the entity level
        return $vars;
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
            $entityManager = $sm->get('doctrine.orm.default_entity_manager');
            $item = $entityManager->getRepository('ZikulaBlocksModule:UserBlockEntity')->findOneBy(array('uid' => $uid, 'bid' => $blockinfo['bid']));
            if (!$item) {
                $item = new \Zikula\BlocksModule\Entity\UserBlockEntity();
                $item['uid'] = (int) $uid;
                $item['bid'] = $blockinfo['bid'];
                $item['active'] = $blockinfo['defaultstate'];
                $entityManager->persist($item);
                $entityManager->flush();
            }

            return (bool) $item['active'];
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
        return ModUtil::apiFunc('ZikulaBlocksModule', 'user', 'getall');
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
            /** @var \Zikula\BlocksModule\Entity\BlockEntity $block */
            foreach ($blocks as $block) {
                $key = $block[$assocKey];
                $blockinfo[$assocKey][$key] = $block->toArray();
                $blockinfo[$assocKey][$key]['bkey'] = $block->getBlocktype();
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
