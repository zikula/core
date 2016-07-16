<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Zikula page variables functions.
 * @deprecated remove at Core-2.0
 *
 * A <em>page variable</em> is an entity identified by a name that stores a value for the currently
 * rendered page. They are used to set for example the title of the page, the stylesheets used etc.
 * from the module.
 *
 * Page variables can be <em>single valued</em> or <em>multi valued</em>. In the first case, only
 * one single value can be set; each new setting will overwrite the old one. The title is an example
 * for a single values page variable (each page can have exactly one title). Multi valued variables
 * can contain more than one value, and new values can be added to the variable. An example of a multi
 * valued variable is stylesheet (a page can use more than one style sheet).
 *
 * Zikula offers a set of API functions to manipulate page variables.
 *
 * A module can register a new page variable by providing its metadata using the RegisterVar
 * function.
 *
 * Zikula doesn't impose any restriction on the page variable's name except for duplicate
 * and reserved names. As of this writing, the list of reserved names consists of
 * <ul>
 * <li>title</li>
 * <li>stylesheet</li>
 * <li>javascript</li>
 * <li>jsgettext</li>
 * <li>body</li>
 * <li>header</li>
 * <li>footer</li>
 * </ul>
 *
 * In addition, if your system is operating in legacy compatibility mode, then
 * the variable 'rawtext' is reserved, and maps to 'header'. (When not operating in
 * legacy compatibility mode, 'rawtext' is not reserved and will not be rendered
 * to the page output by the page variable output filter.)
 */
class PageUtil
{
    /**
     * Register Var.
     *
     * Registers a new page variable.
     * Zikula doesn't impose any restriction on the page variable's name except for duplicate
     * and reserved names. As of this writing, the list of reserved names consists of
     * <ul>
     * <li>title</li>
     * <li>stylesheet</li>
     * <li>javascript</li>
     * <li>jsgettext</li>
     * <li>body</li>
     * <li>header</li>
     * <li>footer</li>
     * </ul>
     *
     * In addition, if your system is operating in legacy compatibility mode, then
     * the variable 'rawtext' is reserved, and maps to 'header'. (When not operating in
     * legacy compatibility mode, 'rawtext' is not reserved and will not be rendered
     * to the page output by the page variable output filter.)
     *
     * @param string  $varname    The name of the new page variable
     * @param boolean $multivalue To define a single or a multi valued variable
     * @param string  $default    To set the default value. This value is assigned to the variable at registration time
     *
     * @return boolean success or not
     */
    public static function registerVar($varname, $multivalue = false, $default = null)
    {
        global $_pageVars;

        if (System::isLegacyMode()) {
            switch ($varname) {
                case 'description':
                case 'keywords':
                    return true;
                    break;
                case 'rawtext':
                    LogUtil::log(__f('Warning! The page variable %1$s is deprecated. Please use %2$s instead.', ['rawtext', 'header']), E_USER_DEPRECATED);
                    $varname = 'header';
                    break;
            }
        }

        // check for $_pageVars sanity
        if (!isset($_pageVars)) {
            $_pageVars = [];
        } elseif (!is_array($_pageVars)) {
            return false;
        }

        // if already registered, stop
        if (isset($_pageVars[$varname])) {
            return false;
        }

        // define the page variable and it's default value
        $_pageVars[$varname] = compact('multivalue', 'default');

        // always make the default value the contents (even if it's null - that will be filtered away)
        self::resetVar($varname);

        return true;
    }

    /**
     * Reset Var.
     *
     * Resets the pge variable back to its default value.
     * All values assigned by addVar() or setVar()
     * will get lost.
     *
     * @param string $varname The name of the page variable
     *
     * @return boolean true On success, false of the page variable is not registered
     */
    public static function resetVar($varname)
    {
        global $_pageVars;

        if (System::isLegacyMode()) {
            switch ($varname) {
                case 'description':
                case 'keywords':
                    return true;
                    break;
                case 'rawtext':
                    LogUtil::log(__f('Warning! The page variable %1$s is deprecated. Please use %2$s instead.', ['rawtext', 'header']), E_USER_DEPRECATED);
                    $varname = 'header';
                    break;
            }
        }

        // check for $_pageVars sanity
        if (!isset($_pageVars)) {
            $_pageVars = [];
        } elseif (!is_array($_pageVars)) {
            return false;
        }

        if (!isset($_pageVars[$varname])) {
            return false;
        }

        if ($_pageVars[$varname]['multivalue']) {
            if (empty($_pageVars[$varname]['default'])) {
                $_pageVars[$varname]['contents'] = [];
            } else {
                $_pageVars[$varname]['contents'] = [$_pageVars[$varname]['default']];
            }
        } else {
            if (empty($_pageVars[$varname]['default'])) {
                $_pageVars[$varname]['contents'] = null;
            } else {
                $_pageVars[$varname]['contents'] = $_pageVars[$varname]['default'];
            }
        }

        return true;
    }

    /**
     * GetVar.
     *
     * Returns the value(s) of a page variable. In the case of
     * a mulit valued variable, this is an array containing all assigned
     * values.
     *
     * @param string $varname The name of the page variable
     * @param mixed  $default Default return value
     *
     * @return mixed Contents of the variable
     */
    public static function getVar($varname, $default = null)
    {
        global $_pageVars;

        if (System::isLegacyMode() && in_array($varname, ['description', 'keywords', 'rawtext'])) {
            $sm = ServiceUtil::getManager();
            $metaTags = $sm->getParameter('zikula_view.metatags');
            switch ($varname) {
                case 'description':
                    return $metaTags['description'];
                    break;
                case 'keywords':
                    return $metaTags['keywords'];
                    break;
                case 'rawtext':
                    LogUtil::log(__f('Warning! The page variable %1$s is deprecated. Please use %2$s instead.', ['rawtext', 'header']), E_USER_DEPRECATED);
                    $varname = 'header';
                    break;
            }
        }

        // check for $_pageVars sanity
        if (!isset($_pageVars)) {
            $_pageVars = [];
        } elseif (!is_array($_pageVars)) {
            return false;
        }

        if (isset($_pageVars[$varname]) && isset($_pageVars[$varname]['contents'])) {
            if ($varname == 'title') {
                $title = System::getVar('pagetitle', '');
                if (!empty($title) && $title != '%pagetitle%') {
                    $title = str_replace('%pagetitle%', $_pageVars[$varname]['contents'], $title);
                    $title = str_replace('%sitename%', System::getVar('sitename', ''), $title);
                    $moduleInfo = ModUtil::getInfoFromName(ModUtil::getName());
                    $moduleDisplayName = $moduleInfo['displayname'];
                    $title = str_replace('%modulename%', $moduleDisplayName, $title);

                    return $title;
                }
            }

            return $_pageVars[$varname]['contents'];
        } elseif (isset($_pageVars[$varname]['default'])) {
            return $_pageVars[$varname]['default'];
        }

        return $default;
    }

    /**
     * Set var.
     *
     * Sets the page variable to a new value. In the case of
     * a multi valued page variable, all previously added values
     * will get lost. If you want to add a value to a multi valued
     * page variable, use PageUtil::addVar.
     *
     * @param string $varname The name of the page variable
     * @param mixed  $value   The new value
     *
     * @see    PageUtil::addVar
     * @return boolean true On success, false of the page variable is not registered
     */
    public static function setVar($varname, $value)
    {
        global $_pageVars;

        if (System::isLegacyMode()) {
            $sm = ServiceUtil::getManager();
            $metaTags = $sm->hasParameter('zikula_view.metatags') ? $sm->getParameter('zikula_view.metatags') : [];
            switch ($varname) {
                case 'description':
                    $metaTags['description'] = $value;
                    $sm->setParameter('zikula_view.metatags', $metaTags);

                    return true;
                    break;
                case 'keywords':
                    $metaTags['keywords'] = $value;
                    $sm->setParameter('zikula_view.metatags', $metaTags);

                    return true;
                    break;
                case 'rawtext':
                    LogUtil::log(__f('Warning! The page variable %1$s is deprecated. Please use %2$s instead.', ['rawtext', 'header']), E_USER_DEPRECATED);
                    $varname = 'header';
                    break;
            }
        }

        // check for $_pageVars sanity
        if (!isset($_pageVars)) {
            $_pageVars = [];
        } elseif (!is_array($_pageVars)) {
            return false;
        }

        if (!isset($_pageVars[$varname])) {
            return false;
        }

        if ($_pageVars[$varname]['multivalue']) {
            $_pageVars[$varname]['contents'] = [$value];
        } else {
            $_pageVars[$varname]['contents'] = $value;
        }

        return true;
    }

    /**
     * Converts the <1.3.5 location of jQuery themes to the new one as of 1.4.0.
     *
     * @param array|string $path The path to check for a replacement. This is recursive array-safe
     *
     * @return string The changed path
     *
     * @todo Remove in 1.5.0
     */
    private static function fixJQueryThemesPath($path)
    {
        if (is_array($path)) {
            $return = [];
            foreach ($path as $key => $value) {
                $return[$key] = self::fixJQueryThemesPath($value);
            }

            return $return;
        }

        return str_replace('javascript/jquery-ui/themes/', 'web/jquery-ui/themes/', $path);
    }

    /**
     * Add var.
     *
     * Adds a new vaule to a page variable. In the case of a single
     * page variable, this functions acts exactly like PageUtil::setVar.
     *
     * @param string $varname  The name of the page variable
     * @param mixed  $value    The new value
     * @param string $features The feature(s) to load via polyfill
     *
     * @see    PageUtil::setVar
     * @return boolean true On success, false of the page variable is not registered
     */
    public static function addVar($varname, $value, $features = 'forms')
    {
        if (empty($varname) || empty($value)) {
            throw new \InvalidArgumentException('empty parameter!');
        }
        global $_pageVars;

        if (System::isLegacyMode()) {
            switch ($varname) {
                case 'rawtext':
                    LogUtil::log(__f('Warning! The page variable %1$s is deprecated. Please use %2$s instead.', ['rawtext', 'header']), E_USER_DEPRECATED);
                    $varname = 'header';
                    break;
            }
        }

        // check for $_pageVars sanity
        if (!isset($_pageVars)) {
            $_pageVars = [];
        } elseif (!is_array($_pageVars)) {
            return false;
        }

        if (!isset($_pageVars[$varname])) {
            return false;
        }

        if (is_array($value)) {
            $value = array_unique($value);
        }

        $value = self::resolveSymfonyAsset($value);

        // @todo Remove in 1.5.0.
        $value = self::fixJQueryThemesPath($value);

        $event = new \Zikula\Core\Event\GenericEvent($varname, [], $value);
        $value = EventUtil::getManager()->dispatch('pageutil.addvar_filter', $event)->getData();

        if ($_pageVars[$varname]['multivalue']) {
            if (is_array($value)) {
                if (in_array('polyfill', $value)) {
                    $features = explode(' ', $features);

                    foreach ($features as $feature) {
                        self::addVar('polyfill_features', $feature);
                    }

                    $_pageVars[$varname]['contents'] = array_merge($_pageVars[$varname]['contents'], $value);
                } else {
                    $_pageVars[$varname]['contents'] = array_merge($_pageVars[$varname]['contents'], $value);
                }
            } else {
                $_pageVars[$varname]['contents'][] = $value;
            }
            // make values unique
            $_pageVars[$varname]['contents'] = array_unique($_pageVars[$varname]['contents']);
        } else {
            $_pageVars[$varname]['contents'] = $value;
        }

        return true;
    }

    /**
     * Check if the current page is the homepage.
     *
     * @return boolean true if it is the homepage, false if it is not the homepage
     */
    public static function isHomepage()
    {
        $moduleGetName = FormUtil::getPassedValue('module', null, 'GETPOST', FILTER_SANITIZE_STRING);

        return empty($moduleGetName) ? true : false;
    }

    private static function resolveSymfonyAsset($path)
    {
        if (is_array($path)) {
            $return = [];
            foreach ($path as $key => $value) {
                $return[$key] = self::resolveSymfonyAsset($value);
            }

            return $return;
        }

        if (substr($path, 0, 1) != "@") {
            return $path;
        }
        $sm = \ServiceUtil::getManager();
        $kernel = $sm->get('kernel');

        $root = realpath($kernel->getRootDir() . "/../");
        $fullPath = $kernel->locateResource($path);
        $path = str_replace(DIRECTORY_SEPARATOR, '/', substr($fullPath, strlen($root) + 1));

        return $path;
    }
}
