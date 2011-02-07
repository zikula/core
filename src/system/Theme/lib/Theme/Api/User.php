<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

class Theme_Api_User extends Zikula_Api
{
    /**
     * Get all settings for a theme
     */
    public function getvariables($args)
    {
        // check our input
        if (!isset($args['theme']) || empty($args['theme'])) {
            return LogUtil::registerArgsError();
        }

        $variables = $this->_readinifile(array('theme'=> $args['theme'], 'file' => 'themevariables.ini', 'sections' => true));

        if (isset($args['formatting']) && is_bool($args['formatting']) && $args['formatting']) {
            $dom = $this->_getthemedomain($args['theme']);

            foreach (array_keys($variables['variables']) as $var) {
                if (!isset($args['explode']) || $args['explode'] != false) {
                    if (isset($variables[$var]['type']) && $variables[$var]['type'] == 'select') {
                        $variables[$var]['values'] = explode(',', __($variables[$var]['values'], $dom));
                        $variables[$var]['output'] = explode(',', __($variables[$var]['output'], $dom));
                    }
                }
                if (isset($variables[$var]['language'])) {
                    $variables[$var]['language'] = __($variables[$var]['language'], $dom);
                }
                if (!isset($variables[$var])) {
                    $variables[$var] = array('editable' => true, 'type' => 'text');
                }
            }
        }

        return $variables;
    }

    /**
     * Get all paletters for a theme
     */
    public function getpalettes($args)
    {
        // check our input
        if (!isset($args['theme']) || empty($args['theme'])) {
            return LogUtil::registerArgsError();
        }

        return $this->_readinifile(array('theme'=> $args['theme'], 'file' => 'themepalettes.ini', 'sections' => true));
    }

    /**
     * Get all page configurations for a theme
     */
    public function getpageconfigurations($args)
    {
        // check our input
        if (!isset($args['theme']) || empty($args['theme'])) {
            return LogUtil::registerArgsError();
        }

        return $this->_readinifile(array('theme'=> $args['theme'], 'file' => 'pageconfigurations.ini', 'sections' => true));
    }

    /**
     * Get a page configuration for a theme
     */
    public function getpageconfiguration($args)
    {
        // check our input
        if (!isset($args['theme']) || empty($args['theme'])) {
            return LogUtil::registerArgsError();
        }
        if (!isset($args['filename']) || empty($args['filename'])) {
            return LogUtil::registerArgsError();
        }

        return $this->_readinifile(array('theme'=> $args['theme'], 'file' => $args['filename'], 'sections' => true));
    }

    /**
     * Get all templates for a theme
     */
    public function gettemplates($args)
    {
        // check our input
        if (!isset($args['theme']) || empty($args['theme'])) {
            return LogUtil::registerArgsError();
        }

        $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($args['theme']));
        $templatedir = realpath('themes/'.DataUtil::formatForOS($themeinfo['directory']).'/templates');

        if (!isset($args['type']) || $args['type'] == 'modules') {
            $args['type'] = 'modules';
            $templatelist = FileUtil::getFiles($templatedir, false, false, '.tpl', 'f');
        } else {
            $templatelist = array();
        }

        $templatelist = array_merge($templatelist, FileUtil::getFiles($templatedir.'/'.DataUtil::formatForOS($args['type']), false, false, '.tpl', 'f'));

        $templates = array();
        $dirlen = strlen($templatedir . '/');
        foreach ($templatelist as $template) {
            $template = realpath($template);
            $templates[] = substr($template, $dirlen, strlen($template));
        }

        return $templates;
    }

    /**
     * read an ini file from either the master theme config or running config
     *
     */
    function _readinifile($args)
    {
        // check our input
        if (!isset($args['file']) || empty($args['file'])) {
            return LogUtil::registerArgsError();
        }

        if (!isset($args['theme']) || empty($args['theme'])) {
            return LogUtil::registerArgsError();
        }

        // get the theme info
        $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($args['theme']));

        // set the section parse flag
        if (!isset($args['sections']) || !is_bool($args['sections'])) {
            $args['sections'] = false;
        }

        $ostheme = DataUtil::formatForOS($themeinfo['directory']);
        $ospntemp = CacheUtil::getLocalDir();
        $osfile = DataUtil::formatForOS($args['file']);

        if (file_exists($ospntemp.'/Theme_Config/'.$ostheme.'_'.$osfile)) {
            return parse_ini_file($ospntemp.'/Theme_Config/'.$ostheme.'_'.$osfile, $args['sections']);
        } else if (file_exists('themes/'.$ostheme.'/templates/config/'.$osfile)) {
            return parse_ini_file('themes/'.$ostheme.'/templates/config/'.$osfile, $args['sections']);
        }
    }

    /**
     * write an ini file to the running configuration directory
     */
    public function writeinifile($args)
    {
        // check our input
        if (!isset($args['file']) || empty($args['file'])) {
            return LogUtil::registerArgsError();
        }

        if (!isset($args['theme']) || empty($args['theme'])) {
            return LogUtil::registerArgsError();
        }

        // get the theme info
        $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($args['theme']));

        $content = ModUtil::apiFunc('theme', 'user', 'createinifile', array('has_sections' => $args['has_sections'], 'assoc_arr' => $args['assoc_arr']));

        $ostheme = DataUtil::formatForOS($themeinfo['directory']);
        $ospntemp = CacheUtil::getLocalDir();
        $osfile = DataUtil::formatForOS($args['file']);

        if (is_writable($fullfile = 'themes/' . $ostheme . '/templates/config/' .$osfile)) {
            $handle = fopen($fullfile, 'w');
        } elseif (is_writable($fullfile = $ospntemp.'/Theme_Config/'.$ostheme.'_'.$osfile)) {
            $handle = fopen($fullfile, 'w');
        } else {
            return LogUtil::registerError($this->__f('Error! Could not open file so that it could be written to: %s', $osfile));
        }

        if (!isset($handle) || !is_resource($handle)) {
            return LogUtil::registerError($this->__f('Error! Could not open file so that it could be written to: %s', $osfile));
        } else {
            if (fwrite($handle, $content) === false) {
                fclose($handle);
                return LogUtil::registerError($this->__f('Error! could not write to file: %s', $osfile));
            }
            fclose($handle);
            return true;
        }
    }

    /**
     * create an ini file
     *
     * @return mixed string ini file contents if succesful, boolean false otherwise
     */
    public function createinifile($args)
    {
        if (!isset($args['assoc_arr']) || empty($args['assoc_arr'])) {
            return false;
        }
        if (!isset($args['has_sections'])) {
            $args['has_sections'] = true;
        }

        $content = '';
        if ($args['has_sections']) {
            foreach ($args['assoc_arr'] as $key => $elem) {
                if (!is_array($elem)) {
                    $content .= $key.' = '.$elem."\r\n";
                } else {
                    $content .= "\r\n[".$key."]\r\n";
                    foreach ($elem as $key2=>$elem2) {
                        $content .= $key2.' = '.$elem2."\r\n";
                    }
                }
            }
        } else {
            foreach ($args['assoc_arr'] as $key => $elem) {
                $content .= $key.' = '.$elem."\r\n";
            }
        }

        return $content;
    }

    /**
     * get a list of palettes available for a theme
     */
    public function getpalettenames($args)
    {
        // check our input
        if (!isset($args['theme']) || empty($args['theme'])) {
            return LogUtil::registerArgsError();
        }

        $allpalettes = ModUtil::apiFunc('Theme', 'user', 'getpalettes', array('theme' => $args['theme']));
        $palettes = array();
        foreach (array_keys($allpalettes) as $name) {
            $palettes[$name] = $name;
        }

        return $palettes;
    }

    /**
     * reset the current users theme to the site default
     *
     */
    public function resettodefault($args)
    {
        // Security check
        if (!System::getVar('theme_change')) {
            return LogUtil::registerError($this->__('Notice: Theme switching is currently disabled.'));
        }

        if (!SecurityUtil::checkPermission( 'Theme::', '::', ACCESS_COMMENT)) {
            return LogUtil::registerPermissionError();
        }

        // update the users record to an empty string - if this user var is empty then the site default is used.
        UserUtil::setVar('theme', '');

        return true;
    }

    /**
     * Retrieves the theme domain
     *
     * @param string $themename Name of the theme to parse
     */
    function _getthemedomain($themename)
    {
        if (in_array($themename, array('Andreas08', 'Atom', 'Printer', 'RSS', 'SeaBreeze'))) {
            return 'zikula';
        }

        return ZLanguage::getThemeDomain($themename);
    }
}
