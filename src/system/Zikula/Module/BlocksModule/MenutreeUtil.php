<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @copyright Zikula Foundation
 * @package Zikula
 * @subpackage ZikulaBlocksModule
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Module\BlocksModule;

use FileUtil;
use ThemeUtil;

class MenutreeUtil
{
    /**
     * Generate an offset id to avoid id conflicts
     *
     * @param int $id the input id
     *
     * @return int the input item * 10000
     */
    public static function getIdOffset($id = null)
    {
        $item = !is_null($id) && !empty($id) ? $id : 1;

        return $item*10000;
    }

    /**
     * Get a list of valid templates for the menu
     *
     * @return array array of templates
     */
    public static function getTemplates()
    {
        $templates = array();
        $tpls = array();

        // restricted templates, array for possible future changes
        $sysTpls = array(
            'blocks_block_menutree_modify.tpl',
            'blocks_block_menutree_include_help.tpl'
        );

        // module templates
        $modulesTpls = FileUtil::getFiles('system/Zikula/Module/BlocksModule/Resources/views/menutree', false, true, 'tpl', false);
        $configTpls = FileUtil::getFiles('config/templates/ZikulaBlocksModule/menutree', false, true, 'tpl', false);
        $tpls['modules'] = array_merge($modulesTpls, $configTpls);

        // themes templates - get user and admin themes
        $userThemes = ThemeUtil::getAllThemes(ThemeUtil::FILTER_USER);
        $adminThemes = ThemeUtil::getAllThemes(ThemeUtil::FILTER_ADMIN);
        $themesTpls = array();
        foreach ($userThemes as $ut) {
            $themesTpls[$ut['name']] = FileUtil::getFiles('themes/'.$ut['name'].'/templates/modules/ZikulaBlocksModule/menutree', false, true, 'tpl', false);
        }
        foreach ($adminThemes as $at) {
            if (!array_key_exists($at['name'], $themesTpls)) {
                $themesTpls[$at['name']] = FileUtil::getFiles('themes/'.$at['name'].'/templates/modules/ZikulaBlocksModule/menutree', false, true, 'tpl', false);
            }
        }

        // get tpls which exist in every theme
        if (count($themesTpls) > 1) {
            $tpls['themes']['all'] = call_user_func_array('array_intersect', $themesTpls);
        } else {
            $tpls['themes']['all'] = $themesTpls;
        }

        // get tpls which exist in some themes
        $tpls['themes']['some'] = array_unique(call_user_func_array('array_merge', $themesTpls));
        $tpls['themes']['some'] = array_diff($tpls['themes']['some'], $tpls['themes']['all'], $tpls['modules'], $sysTpls);

        $templates = array_unique(array_merge($tpls['modules'], $tpls['themes']['all']));
        $templates = array_diff($templates, $sysTpls);
        sort($templates);

        // prepare array values
        $templatesValues = array();
        foreach ($templates as $t) {
            $templatesValues[] = 'menutree/'.$t;
        }
        // fill array keys using values
        $templates = array_combine($templatesValues, $templates);

        $someThemes = __('Only in some themes');
        if (!empty($tpls['themes']['some'])) {
            sort($tpls['themes']['some']);
            foreach ($tpls['themes']['some'] as $k => $t) {
                $tpls['themes']['some'][$k] = 'menutree/'.$t;
            }
            $templates[$someThemes] = array_combine($tpls['themes']['some'], $tpls['themes']['some']);
        }

        return self::normalize($templates);
    }

    /**
     * Get a list of valid stylesheets for the menu
     *
     * @return array array of stylesheets
     */
    public static function getStylesheets()
    {
        $stylesheets = array();
        $styles = array();

        // restricted stylesheets, array for possible future changes
        $sysStyles = array(
            'system/Zikula/Module/BlocksModule/Resources/public/css/menutree/adminstyle.css',
            'system/Zikula/Module/BlocksModule/Resources/public/css/menutree/contextmenu.css',
            'system/Zikula/Module/BlocksModule/Resources/public/css/menutree/tree.css'
        );

        // module stylesheets
        $modulesStyles = FileUtil::getFiles('system/Zikula/Module/BlocksModule/Resources/public/css/menutree', false, false, 'css', false);
        $configStyles = FileUtil::getFiles('config/style/ZikulaBlocksModule/menutree', false, false, 'css', false);
        $styles['modules'] = array_merge($modulesStyles, $configStyles);

        // themes stylesheets - get user and admin themes
        $userThemes = ThemeUtil::getAllThemes(ThemeUtil::FILTER_USER);
        $adminThemes = ThemeUtil::getAllThemes(ThemeUtil::FILTER_ADMIN);
        $themesStyles = array();
        foreach ($userThemes as $ut) {
            $themesStyles[$ut['name']] = FileUtil::getFiles('themes/'.$ut['name'].'/style/ZikulaBlocksModule/menutree', false, false, 'css', false);
        }
        foreach ($adminThemes as $at) {
            if (!array_key_exists($at['name'], $themesStyles)) {
                $themesStyles[$at['name']] = FileUtil::getFiles('themes/'.$at['name'].'/style/ZikulaBlocksModule/menutree', false, false, 'css', false);
            }
        }

        // get stylesheets which exist in every theme
        if (count($themesStyles) > 1) {
            $styles['themes']['all'] = call_user_func_array('array_intersect', $themesStyles);
        } else {
            $styles['themes']['all'] = $themesStyles;
        }
        // get stylesheets which exist in some themes
        $styles['themes']['some'] = array_unique(call_user_func_array('array_merge', $themesStyles));
        $styles['themes']['some'] = array_diff($styles['themes']['some'], $styles['themes']['all'], $styles['modules'], $sysStyles);

        $stylesheets = array_unique(array_merge($styles['modules'],$styles['themes']['all']));
        $stylesheets = array_diff($stylesheets,$sysStyles);
        sort($stylesheets);

        // fill array keys using values
        $stylesheets = array_combine($stylesheets, $stylesheets);

        $someThemes = __('Only in some themes');
        if (!empty($styles['themes']['some'])) {
            sort($styles['themes']['some']);
            $stylesheets[$someThemes] = array_combine($styles['themes']['some'],$styles['themes']['some']);
        }

        return self::normalize($stylesheets);
    }

    /**
     * Helper function to standard file paths
     *
     * @param array array of paths to normalise
     *
     * @return array the modified array
     */
    protected static function normalize($array)
    {
        $normalizedArray = array();

        foreach ($array as $k => $v) {
            if (is_array($v)) {
                foreach ($v as $k2 => $v2) {
                    $k2 = str_replace('\\', '/', $k2);
                    $v2 = str_replace('\\', '/', $v2);
                    $normalizedArray[$k][$k2] = $v2;
                }
            } else {
                $k = str_replace('\\', '/', $k);
                $v = str_replace('\\', '/', $v);
                $normalizedArray[$k] = $v;
            }
        }

        return $normalizedArray;
    }
}