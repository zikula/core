<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\BlocksModule;

use FileUtil;
use ThemeUtil;

/**
 * Supporting functions for the menutree block
 */
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

        return $item * 10000;
    }

    /**
     * Get a list of valid templates for the menu
     *
     * @return array array of templates
     */
    public static function getTemplates()
    {
        $tpls = array();

        // restricted templates, array for possible future changes
        $sysTpls = array(
            'modify.tpl',
            'help.tpl'
        );

        // module templates
        $modulesTpls = FileUtil::getFiles('system/BlocksModule/Resources/views/Block/Menutree', false, true, 'tpl', null, false);
        $configTpls = FileUtil::getFiles('config/templates/ZikulaBlocksModule/menutree', false, true, 'tpl', null, false);
        $tpls['modules'] = array_merge($modulesTpls, $configTpls);

        // themes templates - get user and admin themes
        $userThemes = ThemeUtil::getAllThemes(ThemeUtil::FILTER_USER);
        $adminThemes = ThemeUtil::getAllThemes(ThemeUtil::FILTER_ADMIN);
        $mergedThemes = $userThemes + $adminThemes;
        $themesTpls = array();
        foreach ($mergedThemes as $ut) {
            $themeBundle = ThemeUtil::getTheme($ut['name']);
            if (null !== $themeBundle) {
                $files = FileUtil::getFiles($themeBundle->getRelativePath() . '/Resources/views/modules/ZikulaBlocksModule/menutree', false, false, 'tpl', null, false);
                if (count($files > 0)) {
                    $themesTpls[$ut['name']] = $files;
                }
            } elseif (is_readable('themes/' . $ut['name'] . '/templates/modules/ZikulaBlocksModule/menutree')) {
                $themesTpls[$ut['name']] = FileUtil::getFiles('themes/' . $ut['name'] . '/templates/modules/ZikulaBlocksModule/menutree', false, true, 'tpl', null, false, null, false);
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
            $templatesValues[] = 'Block/Menutree/'.$t;
        }
        // fill array keys using values
        $templates = array_combine($templatesValues, $templates);

        $someThemes = __('Only in some themes');
        if (!empty($tpls['themes']['some'])) {
            sort($tpls['themes']['some']);
            foreach ($tpls['themes']['some'] as $k => $t) {
                $tpls['themes']['some'][$k] = 'Block/Menutree/'.$t;
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
        $styles = array();

        // restricted stylesheets, array for possible future changes
        $sysStyles = array(
            'system/BlocksModule/Resources/public/css/menutree/adminstyle.css',
            'system/BlocksModule/Resources/public/css/menutree/contextmenu.css',
            'system/BlocksModule/Resources/public/css/menutree/tree.css'
        );

        // module stylesheets
        $modulesStyles = FileUtil::getFiles('system/BlocksModule/Resources/public/css/menutree', false, false, 'css', null, false);
        $configStyles = FileUtil::getFiles('config/style/ZikulaBlocksModule/menutree', false, false, 'css', null, false);
        $styles['modules'] = array_merge($modulesStyles, $configStyles);

        // themes stylesheets - get user and admin themes
        $userThemes = ThemeUtil::getAllThemes(ThemeUtil::FILTER_USER);
        $adminThemes = ThemeUtil::getAllThemes(ThemeUtil::FILTER_ADMIN);
        $mergedThemes = $userThemes + $adminThemes;
        $themesStyles = array();
        foreach ($mergedThemes as $ut) {
            $themeBundle = ThemeUtil::getTheme($ut['name']);
            if (null !== $themeBundle) {
                $files = FileUtil::getFiles($themeBundle->getRelativePath() . '/Resources/public/css/ZikulaBlocksModule/menutree', false, false, 'css', null, false);
                if (count($files > 0)) {
                    $themesStyles[$ut['name']] = $files;
                }
            } elseif (is_readable('themes/' . $ut['name'] . '/style/ZikulaBlocksModule/menutree')) {
                $themesStyles[$ut['name']] = FileUtil::getFiles('themes/' . $ut['name'] . '/style/ZikulaBlocksModule/menutree', false, false, 'css', null, false);
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

        $stylesheets = array_unique(array_merge($styles['modules'], $styles['themes']['all']));
        $stylesheets = array_diff($stylesheets, $sysStyles);
        sort($stylesheets);

        // fill array keys using values
        $stylesheets = array_combine($stylesheets, $stylesheets);

        $someThemes = __('Only in some themes');
        if (!empty($styles['themes']['some'])) {
            sort($styles['themes']['some']);
            $stylesheets[$someThemes] = array_combine($styles['themes']['some'], $styles['themes']['some']);
        }

        return self::normalize($stylesheets);
    }

    /**
     * Helper function to standard file paths
     *
     * @param array $array of paths to normalise
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
