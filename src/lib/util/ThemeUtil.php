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
     * Return a theme variable.
     *
     * @param string $name    Variable name.
     * @param mixed  $default Default return value.
     *
     * @return mixed Theme variable value.
     */
    public static function getVar($name = null, $default = null)
    {
        $themevars = Zikula_View_Theme::getInstance()->get_template_vars();

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
     * Sets a theme variable.
     *
     * @param string $name  Variable name.
     * @param mixed  $value Value to set.
     *
     * @return void
     */
    public static function setVar($name, $value)
    {
        // if no variable name is present does nothing
        if (!$name) {
            return;
        }

        Zikula_View_Theme::getInstance()->assign($name, $value);
    }

    /**
     * List all available themes.
     *
     * Possible values of filter are
     * self::FILTER_ALL - get all themes (default)
     * self::FILTER_USER - get user themes
     * self::FILTER_SYSTEM - get system themes
     * self::FILTER_ADMIN - get admin themes
     *
     * @param constant $filter Filter list of returned themes by type.
     * @param constant $state  Theme state.
     * @param constant $type   Theme type.
     *
     * @return array Available themes.
     */
    public static function getAllThemes($filter = self::FILTER_ALL, $state = self::STATE_ACTIVE, $type = self::TYPE_ALL)
    {
        static $themesarray = array();

        $key = md5((string)$filter . (string)$state . (string)$type);

        if (empty($themesarray[$key])) {
            $filters = array();
            
            if ($state != self::STATE_ALL) {
                $filters['state'] = DataUtil::formatForStore($state);
            }
            
            if ($type != self::TYPE_ALL) {
                $filters['type'] = (int)DataUtil::formatForStore($type);
            }
            
            if ($filter == self::FILTER_USER) {
                $filters['user'] = 1;
            }
            
            if ($filter == self::FILTER_SYSTEM) {
                $filters['system'] = 1;
            }
            
            if ($filter == self::FILTER_ADMIN) {
                $filters['admin'] = 1;
            }
            
            // get entityManager
            $sm = ServiceUtil::getManager();
            $entityManager = $sm->getService('doctrine.entitymanager');
            
            // get themes
            $themes = $entityManager->getRepository('Theme\Entity\Theme')->findBy($filters, array('name' => 'ASC'));
            
            // index themes array using directory field
            $dbthemes = array();
            foreach ($themes as $theme) {
                $theme = $theme->toArray();
                $dbthemes[$theme['directory']] = $theme;
            }
            
            $themesarray[$key] = $dbthemes;
            if (!$themesarray[$key]) {
                return false;
            }
        }

        return $themesarray[$key];
    }

    /**
     * Get themeID given its name.
     *
     * @param string $theme The name of the theme.
     *
     * @return integer Theme ID.
     */
    public static function getIDFromName($theme)
    {
        // define input, all numbers and booleans to strings
        $theme = (isset($theme) ? strtolower((string)$theme) : '');

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
     * Returns information about a theme.
     *
     * @param string $themeid Id of the theme.
     *
     * @return array The theme information.
     * */
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
     * Gets the themes table.
     *
     * Small wrapper function to avoid duplicate sql.
     *
     * @access private
     * @return array Modules table.
     */
    public static function getThemesTable()
    {
        static $themestable;
        if (!isset($themestable) || System::isInstalling()) {
            // get entityManager
            $sm = ServiceUtil::getManager();
            $entityManager = $sm->getService('doctrine.entitymanager');
            
            // get all themes
            $themes = $entityManager->getRepository('Theme\Entity\Theme')->findAll();

            foreach ($themes as $theme) {
                $theme = $theme->toArray();
                $theme['i18n'] = (is_dir("themes/$theme[name]/locale") ? 1 : 0);
                $themestable[$theme['id']] = $theme;
            }
        }

        return $themestable;
    }

    /**
     * Get the modules stylesheet from several possible sources.
     *
     * @param string $modname    The modules name (optional, defaults to top level module).
     * @param string $stylesheet The stylesheet file (optional).
     *
     * @return string Path of the stylesheet file, relative to PN root folder.
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
        $configstyledir = 'config/style';
        $configpath = "$configstyledir/$osmodname";

        // theme directory
        $theme = DataUtil::formatForOS(UserUtil::getTheme());
        $themepath = "themes/$theme/style/$osmodname";

        // module directory
        $modinfo = ModUtil::getInfoFromName($modname);
        $osmoddir = DataUtil::formatForOS($modinfo['directory']);
        $modpath = "modules/$osmoddir/Resources/style";
        $syspath = "system/$osmoddir/Resources/style";
        $modpathOld = "modules/$osmoddir/style";
        $syspathOld = "system/$osmoddir/style";

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
