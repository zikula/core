<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Zikula\ThemeModule\Entity\ThemeEntity;

/**
 * @deprecated remove at Core-2.0
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
     * @deprecated at Core-1.4.1
     * @see \Zikula\ExtensionsModule\Api\VariableApi::get()
     * @see service zikula_extensions_module.api.variable
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
     * @deprecated at Core-1.4.1
     * @see \Zikula\ExtensionsModule\Api\VariableApi::set()
     * @see service zikula_extensions_module.api.variable
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
     * @param integer $filter Filter list of returned themes by type.
     * @param integer $state  Theme state.
     * @param integer $type   Theme type.
     *
     * @return array Available themes.
     */
    public static function getAllThemes($filter = self::FILTER_ALL, $state = self::STATE_ACTIVE, $type = self::TYPE_ALL)
    {
        static $themesarray = array();

        $key = md5((string)$filter . (string)$state . (string)$type);

        if (empty($themesarray[$key])) {
            /** @var $em Doctrine\ORM\EntityManager */
            $em = ServiceUtil::get('doctrine.entitymanager');
            $qb = $em->createQueryBuilder()
                     ->select('t')
                     ->from('ZikulaThemeModule:ThemeEntity', 't');

            if ($state != self::STATE_ALL) {
                $qb->andWhere('t.state = :state')
                   ->setParameter('state', $state);
            }
            if ($type != self::TYPE_ALL) {
                $qb->andWhere('t.type = :type')
                   ->setParameter('type', $type);
            }
            switch ($filter) {
                case self::FILTER_USER:
                    $qb->andWhere('t.user = 1');
                    break;
                case self::FILTER_SYSTEM:
                    $qb->andWhere('t.system = 1');
                    break;
                case self::FILTER_ADMIN:
                    $qb->andWhere('t.admin = 1');
                    break;
            }

            $qb->orderBy('t.name', 'ASC');
            $query = $qb->getQuery();

            /** @var $array ThemeEntity[] */
            $array = $query->getResult();
            foreach ($array as $value) {
                $themesarray[$key][$value['directory']] = $value->toArray();
            }

            if (!$themesarray[$key]) {
                return false;
            }
        }

        foreach ($themesarray[$key] as $theme => $values) {
            $themesarray[$key][$theme]['structure'] = true;
            $themeBundle = self::getTheme($values['name']);
            $themesarray[$key][$theme]['isTwigBased'] = isset($themeBundle) ? $themeBundle->isTwigBased() : false;
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
     * @return array Modules table.
     */
    public static function getThemesTable()
    {
        static $themestable;
        if (!isset($themestable) || System::isInstalling()) {
            /** @var $em Doctrine\ORM\EntityManager */
            $em = ServiceUtil::get('doctrine.entitymanager');
            /** @var $array ThemeEntity[] */
            $array = $em->getRepository('ZikulaThemeModule:ThemeEntity')->findAll();
            foreach ($array as $theme) {
                $theme = $theme->toArray();
                $theme['i18n'] = (is_dir("themes/$theme[directory]/locale") || is_dir("themes/$theme[directory]/Resources/locale") ? 1 : 0);
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

        $module = ModUtil::getModule($modname);

        $osstylesheet = DataUtil::formatForOS($stylesheet);
        $osmodname = DataUtil::formatForOS($modname);

        $paths = array();

        // config directory
        $configstyledir = 'config/style';
        $paths[] = "$configstyledir/$osmodname";

        // theme directory
        $themeName = DataUtil::formatForOS(UserUtil::getTheme());
        $theme = self::getTheme($themeName);
        if ($theme) {
            $bundleRelativePath = substr($theme->getPath(), strpos($theme->getPath(), 'themes'), strlen($theme->getPath()));
            $bundleRelativePath = str_replace('\\', '/', $bundleRelativePath);
        }
        $paths[] = null === $theme ?
            "themes/$themeName/style/$osmodname" : $bundleRelativePath.'/Resources/css/'.$theme->getName();

        // module directory
        $modinfo = ModUtil::getInfoFromName($modname);
        $osmoddir = DataUtil::formatForOS($modinfo['directory']);

        if ($module) {
            $dir = ModUtil::getModuleBaseDir($modname);
            $bundleRelativePath = substr($module->getPath(), strpos($module->getPath(), $dir), strlen($module->getPath()));
            $bundleRelativePath = str_replace('\\', '/', $bundleRelativePath);
            $paths[] = $bundleRelativePath."/Resources/public/css";
        }
        $paths[] = "modules/$osmoddir/style";
        $paths[] = "system/$osmoddir/style";

        // search for the style sheet
        $csssrc = '';
        foreach ($paths as $path) {
            if (is_readable("$path/$osstylesheet")) {
                $csssrc = "$path/$osstylesheet";
                break;
            }
        }

        return $csssrc;
    }

    /**
     * @param $themeName
     *
     * @return null|\Zikula\ThemeModule\AbstractTheme
     */
    public static function getTheme($themeName)
    {
        try {
            $sm = ServiceUtil::getManager();
            if (null === $sm) {
                // attenpting to `get` from a nullObject below will produce a fatalException which is 'uncatchable'
                // so check for that here and throw exception now.
                throw new \Exception('There is no Service Manager');
            }
            /** @var $kernel Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel */
            $kernel = $sm->get('kernel');

            return $kernel->getTheme($themeName);
        } catch (\Exception $e) {
        }

        return null;
    }

    /**
     * @param $themeName
     *
     * @return bool|mixed False or path
     */
    public static function getThemeRelativePath($themeName)
    {
        $theme = self::getTheme($themeName);
        $path = false;
        if ($theme) {
            $path = substr($theme->getPath(), strpos($theme->getPath(), 'themes'), strlen($theme->getPath()));
            $path = str_replace('\\', '/', $path);
        }

        return $path;
    }
}
