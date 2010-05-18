<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv2.1 (or at your option any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 *  Theme filters
 */
define('PNTHEME_FILTER_ALL', 0);
define('PNTHEME_FILTER_USER', 1);
define('PNTHEME_FILTER_SYSTEM', 2);
define('PNTHEME_FILTER_ADMIN', 3);

/**
 *  Theme states
 */
define('PNTHEME_STATE_ALL', 0);
define('PNTHEME_STATE_ACTIVE', 1);
define('PNTHEME_STATE_INACTIVE', 2);

/**
 *  Theme types
 */
define('PNTHEME_TYPE_ALL', 0);
define('PNTHEME_TYPE_XANTHIA3', 3);

/**
 * ThemeUtil
 *
 * @package Zikula_Core
 * @subpackage ThemeUtil
 */
class ThemeUtil
{
    /**
     * return a theme variable
     *
     * @return mixed theme variable value
     */
    public static function getVar($name = null, $default = null)
    {
        static $themevars;

        if (!isset($themevars)) {
            $themevars = Theme::getInstance()->get_template_vars();
        }

        // if no variable name is present then return all theme vars
        if (!isset($name)) {
            return $themevars;
        }

        // if a name is present and the variable exists return its value
        if (isset($themevars[$name])) {
            return $themevars[$name];
        }

        // not found the var so return the default
        return $default;
    }

    /**
     * getAllThemes
     *
     * list all available themes
     *
     * possible values of filter are
     * PNTHEME_FILTER_ALL - get all themes (default)
     * PNTHEME_FILTER_USER - get user themes
     * PNTHEME_FILTER_SYSTEM - get system themes
     * PNTHEME_FILTER_ADMIN - get admin themes
     *
     * @param filter - filter list of returned themes by type
     * @return array of available themes
     **/
    public static function getAllThemes($filter = PNTHEME_FILTER_ALL, $state = PNTHEME_STATE_ACTIVE, $type = PNTHEME_TYPE_ALL)
    {
        static $themesarray = array();

        $key = md5((string) $filter . (string) $state . (string) $type);

        if (empty($themesarray[$key])) {
            $pntable = pnDBGetTables();
            $themescolumn = $pntable['themes_column'];
            $whereargs = array();
            if ($state != PNTHEME_STATE_ALL) {
                $whereargs[] = "$themescolumn[state] = '" . DataUtil::formatForStore($state) . "'";
            }
            if ($type != PNTHEME_TYPE_ALL) {
                $whereargs[] = "$themescolumn[type] = '" . (int) DataUtil::formatForStore($type) . "'";
            }
            if ($filter == PNTHEME_FILTER_USER) {
                $whereargs[] = "$themescolumn[user] = '1'";
            }
            if ($filter == PNTHEME_FILTER_SYSTEM) {
                $whereargs[] = "$themescolumn[system] = '1'";
            }
            if ($filter == PNTHEME_FILTER_ADMIN) {
                $whereargs[] = "$themescolumn[admin] = '1'";
            }

            $where = implode($whereargs, ' AND ');
            $orderBy = "ORDER BY $themescolumn[name]";
            // define the permission filter to apply
            $permFilter = array(
                array('realm' => 0, 'component_left' => 'Theme', 'instance_left' => 'name', 'level' => ACCESS_READ));
            $themesarray[$key] = DBUtil::selectObjectArray('themes', $where, $orderBy, 0, -1, 'directory', $permFilter);
            if (!$themesarray[$key]) {
                return false;
            }
        }

        return $themesarray[$key];
    }


    /**
     * getIDFromName
     *
     * get themeID given its name
     *
     * @author Mark West
     * @link http://www.markwest.me.uk
     * @param 'theme' the name of the theme
     * @return int theme ID
     */
    public static function getIDFromName($theme)
    {
        // define input, all numbers and booleans to strings
        $theme = (isset($theme) ? strtolower((string) $theme) : '');

        // validate
        if (!pnVarValidate($theme, 'theme')) {
            return false;
        }

        static $themeid;

        if (!is_array($themeid) || !isset($themeid[$theme])) {
            $themes = self::getThemesTable();

            if ($themes === false) {
                return;
            }

            foreach ($themes as $themeinfo) {
                $tName = strtolower($themeinfo['name']);
                $themeid[$tName] = $themeinfo['id'];
                if (isset($themeinfo['displayname']) && $themeinfo['displayname']) {
                    $tdName = strtolower($themeinfo['displayname']);
                    $themeid[$tdName] = $themeinfo['id'];
                }
            }

            if (!isset($themeid[$theme])) {
                $themeid[$theme] = false;
                return false;
            }
        }

        if (isset($themeid[$theme])) {
            return $themeid[$theme];
        }

        return false;
    }

    /**
     * getInfo
     *
     * Returns information about a theme.
     *
     * @author Mark West
     * @param string $themeid Id of the theme
     * @return array the theme information
     **/
    public static function getInfo($themeid)
    {
        if ($themeid == 0 || !is_numeric($themeid)) {
            return false;
        }

        static $themeinfo;

        if (!is_array($themeinfo) || !isset($themeinfo[$themeid])) {
            $themeinfo = self::getThemesTable();

            if (!$themeinfo) {
                return;
            }

            if (!isset($themeinfo[$themeid])) {
                $themeinfo[$themeid] = false;
                return $themeinfo[$themeid];
            }
        }

        return $themeinfo[$themeid];
    }

    /**
     * gets the themes table
     *
     * small wrapper function to avoid duplicate sql
     * @access private
     * @return array modules table
     */
    public static function getThemesTable()
    {
        static $themestable;
        if (!isset($themestable) || defined('_ZINSTALLVER')) {
            $array = DBUtil::selectObjectArray('themes', '', '', -1, -1, 'id');
            foreach ($array as $theme) {
                $theme['i18n'] = (is_dir("themes/$theme[name]/locale") ? 1 : 0);
                $themestable[$theme['id']] = $theme;
            }
        }

        return $themestable;
    }

    /**
     * get the modules stylesheet from several possible sources
     *
     *@access public
     *@param string $modname    the modules name (optional, defaults to top level module)
     *@param string $stylesheet the stylesheet file (optional)
     *@return string path of the stylesheet file, relative to PN root folder
     */
    public static function getModuleStylesheet($modname = '', $stylesheet = '')
    {
        // default for the module
        if (empty($modname)) {
            $modname = pnModGetName();
        }

        // default for the style sheet
        if (empty($stylesheet)) {
            $stylesheet = ModUtil::getVar($modname, 'modulestylesheet');
            if (empty($stylesheet)) {
                $stylesheet = 'style.css';
            }
        }

        $osstylesheet = DataUtil::formatForOS($stylesheet);
        $osmodname = DataUtil::formatForOS($modname);

        // config directory
        $configstyledir = 'config/styles';
        $configpath = "$configstyledir/$osmodname";

        // theme directory
        $theme = DataUtil::formatForOS(pnUserGetTheme());
        $themepath = "themes/$theme/style/$osmodname";

        // module directory
        $modinfo = pnModGetInfo(pnModGetIDFromName($modname));
        $osmoddir = DataUtil::formatForOS($modinfo['directory']);
        $modpath = "modules/$osmoddir/pnstyle";
        $syspath = "system/$osmoddir/pnstyle";

        // search for the style sheet
        $csssrc = '';
        foreach (array($configpath, $themepath, $modpath, $syspath) as $path) {
            if (is_readable("$path/$osstylesheet")) {
                $csssrc = "$path/$osstylesheet";
                break;
            }
        }
        return $csssrc;
    }
}
