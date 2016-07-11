<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ThemeModule\Api;

use CacheUtil;
use DataUtil;
use FileUtil;
use ModUtil;
use SecurityUtil;
use System;
use ThemeUtil;
use UserUtil;
use ZLanguage;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @deprecated remove at Core-2.0
 * API functions used by user controllers
 */
class UserApi extends \Zikula_AbstractApi
{
    /**
     * Get all settings for a theme
     *
     * @param string[] $args {
     *      @type string $theme name of the theme
     *                       }
     *
     * @return array array of defined theme variables
     *
     * @throws \InvalidArgumentException Thrown if the theme parameter isn't provided or is empty
     */
    public function getvariables($args)
    {
        // check our input
        if (!isset($args['theme']) || empty($args['theme'])) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        $args['variables'] = $this->_readinifile(['theme' => $args['theme'], 'file' => 'themevariables.ini', 'sections' => true]);

        if (isset($args['formatting']) && is_bool($args['formatting']) && $args['formatting']) {
            $args['variables'] = $this->formatvariables($args);
        }

        return $args['variables'];
    }

    /**
     * Format the variables of a theme or pageconfiguration
     * It uses the theme additional variables as a base which should be variables specifications
     *
     * @param mixed[] $args {
     *      @type string $theme     name of the theme
     *      @type array  $variables array of theme variables
     *                      }
     *
     * @return array array of defined theme variables
     *
     * @throws \InvalidArgumentException Thrown if either the theme or variables parameters aren't provided or are empty
     */
    public function formatvariables($args)
    {
        // check our input
        if (!isset($args['theme']) || empty($args['theme']) || !isset($args['variables']) || empty($args['variables'])) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        $dom = $this->_getthemedomain($args['theme']);

        // take any variables specification from the themevars
        $themevars = $this->_readinifile(['theme' => $args['theme'], 'file' => 'themevariables.ini', 'sections' => true]);
        unset($themevars['variables']);

        $variables = array_merge($themevars, $args['variables']);

        foreach (array_keys($variables['variables']) as $var) {
            if (is_array($variables['variables'][$var])) {
                // process each array field and insert it in $variables.variables
                foreach ($variables['variables'][$var] as $k => $v) {
                    if (!isset($variables["$var.$k"])) {
                        $variables["{$var}[{$k}]"] = ['editable' => true, 'type' => 'text'];
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
                    $variables[$var] = ['editable' => true, 'type' => 'text'];
                }
                $this->_variable_options($variables[$var], $args, $dom);
            }
        }

        return $variables;
    }

    /**
     * Internal variable options processor
     *
     * @param array  $options the options array of a variable
     * @param array  $args settings to use when processing the variable
     * @param string $dom language domain
     *
     * @return void
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
     *
     * @param string[] $args {
     *      @type string $theme name of the theme
     *                       }
     *
     * @return array array of defined theme palettes
     *
     * @throws \InvalidArgumentException Thrown of the theme parameter isn't provided or is empty
     */
    public function getpalettes($args)
    {
        // check our input
        if (!isset($args['theme']) || empty($args['theme'])) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        return $this->_readinifile(['theme' => $args['theme'], 'file' => 'themepalettes.ini', 'sections' => true]);
    }

    /**
     * Get one palette for a theme
     *
     * @param string[] $args {
     *      @type string $theme   name of the theme
     *      @type string $palette name of the palette
     *                       }
     *
     * @return array array of palette colours
     *
     * @throws \InvalidArgumentException Thrown of the theme parameter isn't provided or is empty
     */
    public function getpalette($args)
    {
        // check our input
        if (!isset($args['theme']) || empty($args['theme']) || !isset($args['palette']) || empty($args['palette'])) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        $allpalettes = ModUtil::apiFunc('ZikulaThemeModule', 'user', 'getpalettes', ['theme' => $args['theme']]);

        return isset($allpalettes[$args['palette']]) ? $allpalettes[$args['palette']] : null;
    }

    /**
     * Get a list of palettes available for a theme
     *
     * @param string[] $args {
     *      @type string $theme name of the theme
     *                       }
     *
     * @return array array of defined palette names
     *
     * @throws \InvalidArgumentException Thrown of the theme parameter isn't provided or is empty
     */
    public function getpalettenames($args)
    {
        // check our input
        if (!isset($args['theme']) || empty($args['theme'])) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        $allpalettes = ModUtil::apiFunc('ZikulaThemeModule', 'user', 'getpalettes', ['theme' => $args['theme']]);
        $palettes = [];
        foreach (array_keys((array)$allpalettes) as $name) {
            $palettes[$name] = $name;
        }

        return $palettes;
    }

    /**
     * Get all page configurations for a theme
     *
     * @param string[] $args {
     *      @type string $theme name of the theme
     *                       }
     *
     * @return array array of defined theme page configurations
     *
     * @throws \InvalidArgumentException Thrown of the theme parameter isn't provided or is empty
     */
    public function getpageconfigurations($args)
    {
        // check our input
        if (!isset($args['theme']) || empty($args['theme'])) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        return $this->_readinifile(['theme' => $args['theme'], 'file' => 'pageconfigurations.ini', 'sections' => true]);
    }

    /**
     * Get a page configuration for a theme
     *
     * @param string[] $args {
     *      @type string $theme    name of the theme
     *      @type string $filename filename of the page configuration
     *                       }
     *
     * @return array array of page configuration parameters
     *
     * @throws \InvalidArgumentException Thrown if either the theme or filename parameters aren't provided or are empty
     */
    public function getpageconfiguration($args)
    {
        // check our input
        if (!isset($args['theme']) || empty($args['theme'])) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }
        if (!isset($args['filename']) || empty($args['filename'])) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        $config = $this->_readinifile(['theme' => $args['theme'], 'file' => $args['filename'], 'sections' => true]);

        $default = [
            'page' => '',
            'block' => '',
            'palette' => '', // deprecated
            'modulewrapper' => 1,
            'blockwrapper' => 1,
            'blockinstances' => [],
            'blocktypes' => [],
            'blockpositions' => [],
            'filters' => [],
            'variables' => []
        ];

        return array_merge($default, $config);
    }

    /**
     * Get all configurations available for a theme
     *
     * @param string[] $args {
     *      @type string $theme name of the theme
     *                       }
     *
     * @return array array of defined page configurations
     *
     * @throws \InvalidArgumentException Thrown of the theme parameter isn't provided or is empty
     */
    public function getconfigurations($args)
    {
        // check our input
        if (!isset($args['theme']) || empty($args['theme'])) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        $themename = $args['theme'];
        $theme = ThemeUtil::getTheme($themename);
        $themeinfo   = ThemeUtil::getInfo(ThemeUtil::getIDFromName($themename));
        if (isset($theme)) {
            $templatedir = $theme->getConfigPath();
        } else {
            $templatedir = 'themes/' . DataUtil::formatForOS($themeinfo['directory']) . '/templates/config';
        }

        // get the available .ini files and exclude the core ones
        $inifiles = FileUtil::getFiles($templatedir, false, true, '.ini', 'f');
        $inifiles = array_diff($inifiles, ['admin.ini', 'pageconfigurations.ini', 'themevariables.ini', 'themepalettes.ini']);
        sort($inifiles);

        return $inifiles;
    }

    /**
     * Get all templates for a theme
     *
     * @param mixed[] $args {
     *      @type string $theme name of the theme
     *                      }
     *
     * @return array array of defined theme templates
     *
     * @throws \InvalidArgumentException Thrown of the theme parameter isn't provided or is empty
     */
    public function gettemplates($args)
    {
        // check our input
        if (!isset($args['theme']) || empty($args['theme'])) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        $args['type'] = isset($args['type']) ? DataUtil::formatForOS($args['type']) : 'modules';

        $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($args['theme']));
        if ($theme = ThemeUtil::getTheme($themeinfo['name'])) {
            $templatedir = $theme->getPath().'/Resources/views';
        } else {
            $templatedir = 'themes/'.DataUtil::formatForOS($themeinfo['directory']).'/templates';
        }

        if ($args['type'] == 'modules') {
            // for module templates also search on the theme/templates folder
            $templatelist = FileUtil::getFiles($templatedir, false, true, ['.tpl', '.htm'], 'f');
        } else {
            $templatelist = [];
        }

        $templatelist = array_merge($templatelist, FileUtil::getFiles($templatedir.'/'.$args['type'], false, $args['type'], ['.tpl', '.htm'], 'f'));

        return $templatelist;
    }

    /**
     * read an ini file from either the master theme config or running config
     *
     * @param string[] $args {
     *      @type string $theme name of the theme
     *      @type string $file  name of the ini file to read
     *                      }
     *
     * @return array the parsed ini file or false if file doesn't exist (Core 1.4.1+)
     *
     * @throws \InvalidArgumentException Thrown if either the theme or file parameters aren't provided or are empty
     */
    public function _readinifile($args)
    {
        // check our input
        if (!isset($args['file']) || empty($args['file'])) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        if (!isset($args['theme']) || empty($args['theme'])) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
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
        } elseif ($theme = ThemeUtil::getTheme($themeinfo['name'])) {
            $file = $theme->getPath().'/Resources/config/'.$osfile;
            if (file_exists($file)) {
                return parse_ini_file($file, $args['sections']);
            } else {
                return false;
            }
        }
    }

    /**
     * write an ini file to the running configuration directory
     *
     * @param string[] $args {
     *      @type string $theme name of the theme
     *      @type string $file  name of the file to write
     *                      }
     *
     * @return bool true if successful
     *
     * @throws \InvalidArgumentException Thrown if either the theme or file parameters aren't provided or are empty
     * @throws \RuntimeException Thrown if the file cannot be opened or written to.
     */
    public function writeinifile($args)
    {
        // check our input
        if (!isset($args['file']) || empty($args['file'])) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        if (!isset($args['theme']) || empty($args['theme'])) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        // get the theme info
        $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($args['theme']));

        $content = ModUtil::apiFunc('ZikulaThemeModule', 'user', 'createinifile', ['has_sections' => $args['has_sections'], 'assoc_arr' => $args['assoc_arr']]);

        $ostemp  = CacheUtil::getLocalDir();
        $ostheme = DataUtil::formatForOS($themeinfo['directory']);
        $osfile  = DataUtil::formatForOS($args['file']);

        // verify the writable paths
        if ($theme = ThemeUtil::getTheme($themeinfo['name'])) {
            $tpath = $theme->getPath().'/Resources/config';
        } else {
            $tpath = 'themes/'.$ostheme.'/templates/config';
        }

        if (is_writable($tpath.'/'.$osfile)) {
            $handle = fopen($tpath.'/'.$osfile, 'w+');
        } else {
            if (!file_exists($zpath = $ostemp.'/Theme_Config/'.$ostheme)) {
                mkdir($zpath, $this->serviceManager['system.chmod_dir'], true);
            }

            if (!file_exists($zpath.'/'.$osfile) || is_writable($zpath.'/'.$osfile)) {
                $handle = fopen($zpath.'/'.$osfile, 'w+');
            } else {
                throw new \RuntimeException($this->__f('Error! Cannot write in "%1$s" or "%2$s" to store the contents of "%3$s"', [$tpath, $zpath, $osfile]));
            }
        }

        // validate the resulting handler and the write operation result
        if (!isset($handle) || !is_resource($handle)) {
            throw new \RuntimeException($this->__f('Error! Could not open file so that it could be written to: %s', $osfile));
        } else {
            if (fwrite($handle, $content) === false) {
                fclose($handle);

                throw new \RuntimeException($this->__f('Error! Could not write to file: %s', $osfile));
            }
            fclose($handle);

            return true;
        }
    }

    /**
     * create an ini file
     *
     * @param mixed[] $args {
     *      @type array $assoc_arr      name of the content array
     &      @type bool  $has_sections   content array has sections
     *                      }
     *
     * @return string file content if successful, false otherwise
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
     *
     * @param mixed[] $args {
     *                      }
     *
     * @return bool true if successful
     *
     * @throws \AccessDeniedException Thrown if the user doesn't have comment permissions over the theme module
     * @throws \RuntimeException Thrown if theme switching is disabled
     */
    public function resettodefault($args)
    {
        // Security check
        if (!System::getVar('theme_change')) {
            throw new \RuntimeException($this->__('Notice: Theme switching is currently disabled.'));
        }

        if (!SecurityUtil::checkPermission('ZikulaThemeModule::', '::', ACCESS_COMMENT)) {
            throw new AccessDeniedException();
        }

        // update the users record to an empty string - if this user var is empty then the site default is used.
        UserUtil::setVar('theme', '');

        return true;
    }

    /**
     * Retrieves the theme domain
     *
     * @param string $themename theme name to get the domain
     *
     * @return string language domain assoicated with the theme
     */
    public function _getthemedomain($themename)
    {
        if (in_array($themename, ['Andreas08', 'Atom', 'Printer', 'RSS', 'SeaBreeze'])) {
            return 'zikula';
        }

        return ZLanguage::getThemeDomain($themename);
    }
}
