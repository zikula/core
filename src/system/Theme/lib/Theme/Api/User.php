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

class Theme_Api_User extends Zikula_AbstractApi
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

        $args['variables'] = $this->_readinifile(array('theme'=> $args['theme'], 'file' => 'themevariables.ini', 'sections' => true));

        if (isset($args['formatting']) && is_bool($args['formatting']) && $args['formatting']) {
            $args['variables'] = $this->formatvariables($args);
        }

        return $args['variables'];
    }

    /**
     * Format the variables of a theme or pageconfiguration
     * It uses the theme additional variables as a base which should be variables specifications
     */
    public function formatvariables($args)
    {
        // check our input
        if (!isset($args['theme']) || empty($args['theme']) || !isset($args['variables']) || empty($args['variables'])) {
            return LogUtil::registerArgsError();
        }

        $dom = $this->_getthemedomain($args['theme']);

        // take any variables specification from the themevars
        $themevars = $this->_readinifile(array('theme'=> $args['theme'], 'file' => 'themevariables.ini', 'sections' => true));
        unset($themevars['variables']);

        $variables = array_merge($themevars, $args['variables']);

        foreach (array_keys($variables['variables']) as $var) {
            if (is_array($variables['variables'][$var])) {
                // process each array field and insert it in $variables.variables
                foreach ($variables['variables'][$var] as $k => $v) {
                    if (!isset($variables["$var.$k"])) {
                        $variables["{$var}[{$k}]"] = array('editable' => true, 'type' => 'text');
                    } else {
                        $variables["{$var}[{$k}]"] = $variables["$var.$k"];
                        unset($variables["$var.$k"]);
                    }
                    $variables['variables']["{$var}[{$k}]"] = $v;

                    $this->_variable_options($variables["{$var}[{$k}]"], $args, $dom);
                }
                unset($variables['variables'][$var]);

            } else {
                // process the options of the single value
                if (!isset($variables[$var])) {
                    $variables[$var] = array('editable' => true, 'type' => 'text');
                }
                $this->_variable_options($variables[$var], $args, $dom);
            }
        }

        return $variables;
    }

    /**
     * Internal variable options processor
     */
    private function _variable_options(&$options, $args, $dom)
    {
        if (!isset($args['explode']) || $args['explode'] != false) {
            if (isset($options['type']) && $options['type'] == 'select') {
                $options['values'] = explode(',', $options['values']);
                $options['output'] = explode(',', __($options['output'], $dom));
            }
        }
        if (isset($options['language'])) {
            $options['language'] = __($options['language'], $dom);
        }
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
     * Get one palette for a theme
     */
    public function getpalette($args)
    {
        // check our input
        if (!isset($args['theme']) || empty($args['theme']) || !isset($args['palette']) || empty($args['palette'])) {
            return LogUtil::registerArgsError();
        }

        $allpalettes = ModUtil::apiFunc('Theme', 'user', 'getpalettes', array('theme' => $args['theme']));

        return isset($allpalettes[$args['palette']]) ? $allpalettes[$args['palette']] : null;
    }

    /**
     * Get a list of palettes available for a theme
     */
    public function getpalettenames($args)
    {
        // check our input
        if (!isset($args['theme']) || empty($args['theme'])) {
            return LogUtil::registerArgsError();
        }

        $allpalettes = ModUtil::apiFunc('Theme', 'user', 'getpalettes', array('theme' => $args['theme']));
        $palettes = array();
        foreach (array_keys((array)$allpalettes) as $name) {
            $palettes[$name] = $name;
        }

        return $palettes;
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

        $config = $this->_readinifile(array('theme'=> $args['theme'], 'file' => $args['filename'], 'sections' => true));

        $default = array(
                       'page' => '',
                       'block' => '',
                       'palette' => '', // deprecated
                       'modulewrapper' => 1,
                       'blockwrapper' => 1,
                       'blockinstances' => array(),
                       'blocktypes' => array(),
                       'blockpositions' => array(),
                       'filters' => array(),
                       'variables' => array()
                   );

        return array_merge($default, $config);
    }

    /**
     * Get all configurations available for a theme
     */
    public function getconfigurations($args)
    {
        // check our input
        if (!isset($args['theme']) || empty($args['theme'])) {
            return LogUtil::registerArgsError();
        }

        $themeinfo   = ThemeUtil::getInfo(ThemeUtil::getIDFromName($args['theme']));
        $templatedir = 'themes/'.DataUtil::formatForOS($themeinfo['directory']).'/templates/config';

        // get the available .ini files and exclude the core ones
        $inifiles = FileUtil::getFiles($templatedir, false, true, '.ini', 'f');
        $inifiles = array_diff($inifiles, array('admin.ini', 'pageconfigurations.ini', 'themevariables.ini', 'themepalettes.ini'));
        sort($inifiles);

        return $inifiles;
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

        $args['type'] = isset($args['type']) ? DataUtil::formatForOS($args['type']) : 'modules';

        $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($args['theme']));
        $templatedir = 'themes/'.DataUtil::formatForOS($themeinfo['directory']).'/templates';

        if ($args['type'] == 'modules') {
            // for module templates also search on the theme/templates folder
            $templatelist = FileUtil::getFiles($templatedir, false, true, array('.tpl', '.htm'), 'f');
        } else {
            $templatelist = array();
        }

        $templatelist = array_merge($templatelist, FileUtil::getFiles($templatedir.'/'.$args['type'], false, $args['type'], array('.tpl', '.htm'), 'f'));

        return $templatelist;
    }

    /**
     * read an ini file from either the master theme config or running config
     *
     */
    public function _readinifile($args)
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

        $ostemp   = CacheUtil::getLocalDir();
        $ostheme  = DataUtil::formatForOS($themeinfo['directory']);
        $osfile   = DataUtil::formatForOS($args['file']);

        if (file_exists($ostemp.'/Theme_Config/'.$ostheme.'/'.$osfile)) {
            return parse_ini_file($ostemp.'/Theme_Config/'.$ostheme.'/'.$osfile, $args['sections']);
        } elseif (file_exists('themes/'.$ostheme.'/templates/config/'.$osfile)) {
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

        $ostemp  = CacheUtil::getLocalDir();
        $ostheme = DataUtil::formatForOS($themeinfo['directory']);
        $osfile  = DataUtil::formatForOS($args['file']);

        // verify the writable paths
        $tpath = 'themes/'.$ostheme.'/templates/config';

        if (is_writable($tpath.'/'.$osfile)) {
            $handle = fopen($tpath.'/'.$osfile, 'w+');

        } else {
            if (!file_exists($zpath = $ostemp.'/Theme_Config/'.$ostheme)) {
                mkdir($zpath, $this->serviceManager['system.chmod_dir'], true);
            }

            if (!file_exists($zpath.'/'.$osfile) || is_writable($zpath.'/'.$osfile)) {
                $handle = fopen($zpath.'/'.$osfile, 'w+');
            } else {
                return LogUtil::registerError($this->__f("Error! Cannot write in '%1$s' or '%2$s' to store the contents of '%3$s'.", array($tpath, $zpath, $osfile)));
            }
        }

        // validate the resulting handler and the write operation result
        if (!isset($handle) || !is_resource($handle)) {
            return LogUtil::registerError($this->__f('Error! Could not open file so that it could be written to: %s', $osfile));

        } else {
            if (fwrite($handle, $content) === false) {
                fclose($handle);

                return LogUtil::registerError($this->__f('Error! Could not write to file: %s', $osfile));
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
            foreach ($args['assoc_arr'] as $section => $sectionval) {
                // process and write each section value
                if (!is_array($sectionval)) {
                    $content .= "$section = $sectionval\r\n";
                } else {
                    $content .= "\r\n[$section]\r\n";
                    foreach ($sectionval as $var => $value) {
                        if (is_array($value)) {
                            foreach ($value as $k => $v) {
                                $content .= "{$var}[{$k}] = $v\r\n";
                            }
                        } else {
                            $content .= "$var = $value\r\n";
                        }
                    }
                }
            }
        } else {
            foreach ($args['assoc_arr'] as $key => $value) {
                $content .= "$key = $value\r\n";
            }
        }

        return $content;
    }

    /**
     * Reset the current users theme to the site default
     */
    public function resettodefault($args)
    {
        // Security check
        if (!System::getVar('theme_change')) {
            return LogUtil::registerError($this->__('Notice: Theme switching is currently disabled.'));
        }

        if (!SecurityUtil::checkPermission('Theme::', '::', ACCESS_COMMENT)) {
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
    public function _getthemedomain($themename)
    {
        if (in_array($themename, array('Andreas08', 'Atom', 'Printer', 'RSS', 'SeaBreeze'))) {
            return 'zikula';
        }

        return ZLanguage::getThemeDomain($themename);
    }
}
