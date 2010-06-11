<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv2.1 (or at your option any later version).
 * @package Util
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */


/**
 * ThemeUtil
 */
class ThemeUtil
{
    const STATE_ALL = 0;
    const STATE_ACTIVE = 1;
    const STATE_INACTIVE = 2;

    const TYPE_ALL = 0;
    const TYPE_XANTHIA3 = 3;

    const FILTER_ALL = 0;
    const FILTER_USER = 1;
    const FILTER_SYSTEM = 2;
    const FILTER_ADMIN = 3;
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
     * self::FILTER_ALL - get all themes (default)
     * self::FILTER_USER - get user themes
     * self::FILTER_SYSTEM - get system themes
     * self::FILTER_ADMIN - get admin themes
     *
     * @param filter - filter list of returned themes by type
     * @return array of available themes
     **/
    public static function getAllThemes($filter = self::FILTER_ALL, $state = self::STATE_ACTIVE, $type = self::TYPE_ALL)
    {
        static $themesarray = array();

        $key = md5((string) $filter . (string) $state . (string) $type);

        if (empty($themesarray[$key])) {
            $pntable = System::dbGetTables();
            $themescolumn = $pntable['themes_column'];
            $whereargs = array();
            if ($state != self::STATE_ALL) {
                $whereargs[] = "$themescolumn[state] = '" . DataUtil::formatForStore($state) . "'";
            }
            if ($type != self::TYPE_ALL) {
                $whereargs[] = "$themescolumn[type] = '" . (int) DataUtil::formatForStore($type) . "'";
            }
            if ($filter == self::FILTER_USER) {
                $whereargs[] = "$themescolumn[user] = '1'";
            }
            if ($filter == self::FILTER_SYSTEM) {
                $whereargs[] = "$themescolumn[system] = '1'";
            }
            if ($filter == self::FILTER_ADMIN) {
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
        if (!System::varValidate($theme, 'theme')) {
            return false;
        }

        static $themeid;

        if (!is_array($themeid) || !isset($themeid[$theme])) {
            $themes = self::getThemesTable();

            if (!$themes) {
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
        if (!isset($themestable) || System::isInstalling()) {
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
            $modname = ModUtil::getName();
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
        $theme = DataUtil::formatForOS(UserUtil::getTheme());
        $themepath = "themes/$theme/style/$osmodname";

        // module directory
        $modinfo = ModUtil::getInfo(ModUtil::getIdFromName($modname));
        $osmoddir = DataUtil::formatForOS($modinfo['directory']);
        $modpath = "modules/$osmoddir/style";
        $syspath = "system/$osmoddir/style";
        $modpathOld = "modules/$osmoddir/pnstyle";
        $syspathOld = "system/$osmoddir/pnstyle";

        // search for the style sheet
        $csssrc = '';
        foreach (array($configpath, $themepath, $modpath, $syspath, $modpathOld, $syspathOld) as $path) {
            if (is_readable("$path/$osstylesheet")) {
                $csssrc = "$path/$osstylesheet";
                break;
            }
        }
        return $csssrc;
    }
}
