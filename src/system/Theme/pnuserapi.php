<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2004, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package     Zikula_System_Modules
 * @subpackage  Theme
 */

/**
 * Get all settings for a theme
 */
function theme_userapi_getvariables($args)
{
    // check our input
    if (!isset($args['theme']) || empty($args['theme'])) {
        return LogUtil::registerArgsError();
    }

    $variables = _theme_userapi_readinifile(array('theme'=> $args['theme'], 'file' => 'themevariables.ini', 'sections' => true));
    if (isset($args['formatting']) && is_bool($args['formatting']) && $args['formatting']) {
        foreach ($variables['variables'] as $key => $value) {
            if (!isset($args['explode']) || $args['explode'] != false) {
                if (isset($variables[$key]) && $variables[$key]['type'] == 'select') {
                    $variables[$key]['values'] = explode(',', $variables[$key]['values']);
                    $variables[$key]['output'] = explode(',', $variables[$key]['output']);
                    foreach ($variables[$key]['output'] as $outputkey => $outputvalue) {
                        if (defined($outputvalue)) {
                            $variables[$key]['output'][$outputkey] = constant($outputvalue);
                        }
                    }
                }
            }
            if (!isset($variables[$key])) {
                $variables[$key] = array('editable' => true, 'type' => 'text');
            }
        }
        return $variables;

    } elseif (isset($variables['variables'])) {
        return $variables['variables'];
    }

    return false;
}

/**
 * Get all paletters for a theme
 */
function theme_userapi_getpalettes($args)
{
    // check our input
    if (!isset($args['theme']) || empty($args['theme'])) {
        return LogUtil::registerArgsError();
    }

    return _theme_userapi_readinifile(array('theme'=> $args['theme'], 'file' => 'themepalettes.ini', 'sections' => true));
}

/**
 * Get all page configurations for a theme
 */
function theme_userapi_getpageconfigurations($args)
{
    // check our input
    if (!isset($args['theme']) || empty($args['theme'])) {
        return LogUtil::registerArgsError();
    }

    return _theme_userapi_readinifile(array('theme'=> $args['theme'], 'file' => 'pageconfigurations.ini', 'sections' => true));
}

/**
 * Get a page configuration for a theme
 */
function theme_userapi_getpageconfiguration($args)
{
    // check our input
    if (!isset($args['theme']) || empty($args['theme'])) {
        return LogUtil::registerArgsError();
    }
    if (!isset($args['filename']) || empty($args['filename'])) {
        return LogUtil::registerArgsError();
    }

    return _theme_userapi_readinifile(array('theme'=> $args['theme'], 'file' => $args['filename'], 'sections' => true));
}

/**
 * Get all templates for a theme
 */
function theme_userapi_gettemplates($args)
{
    // check our input
    if (!isset($args['theme']) || empty($args['theme'])) {
        return LogUtil::registerArgsError();
    }

    $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($args['theme']));
    Loader::loadClass('FileUtil');

    if (!isset($args['type']) || $args['type'] == 'modules') {
        $args['type'] = 'modules';
        $templatelist = FileUtil::getFiles('themes/'.DataUtil::formatForOS($themeinfo['directory']).'/templates/', false, true, null, false);
    } else {
        $templatelist = array();
    }

    $templatelist = array_merge($templatelist, FileUtil::getFiles('themes/'.DataUtil::formatForOS($themeinfo['directory']).'/templates/'.DataUtil::formatForOS($args['type']), false, true, null, false));

    return $templatelist;
}

/**
 * read an ini file from either the master theme config or running config
 *
 */
function _theme_userapi_readinifile($args)
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
        if (ini_get('safe_mode')) {
            return _theme_userapi_parseinifile($ospntemp.'/Theme_Config/'.$ostheme.'_'.$osfile);
        } else {
            return parse_ini_file($ospntemp.'/Theme_Config/'.$ostheme.'_'.$osfile, $args['sections']);
        }
    } else if (file_exists('themes/'.$ostheme.'/templates/config/'.$osfile)) {
        return parse_ini_file('themes/'.$ostheme.'/templates/config/'.$osfile, $args['sections']);
    }
}

/**
 * write an ini file to the running configuration directory
 */
function theme_userapi_writeinifile($args)
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
        return LogUtil::registerError(__f('Error! Could not open file so that it could be written to: %s', $osfile));
    }

    if (!isset($handle) || !is_resource($handle)) {
        return LogUtil::registerError(__f('Error! Could not open file so that it could be written to: %s', $osfile));
    } else {
        if (fwrite($handle, $content) === false) {
            fclose($handle);
            return LogUtil::registerError(__f('Error! could not write to file: %s', $osfile));
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
function theme_userapi_createinifile($args)
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
 *
 */
function theme_userapi_getpalettenames($args)
{
    // check our input
    if (!isset($args['theme']) || empty($args['theme'])) {
        return LogUtil::registerArgsError();
    }

    $allpalettes = ModUtil::apiFunc('Theme', 'user', 'getpalettes', array('theme' => $args['theme']));
    $palettes = array();
    foreach ($allpalettes as $name => $colors) {
        $palettes[$name] = $name;
    }

    return $palettes;
}

/**
 * reset the current users theme to the site default
 *
 */
function theme_userapi_resettodefault($args)
{
    // Security check
    if (!System::getVar('theme_change')) {
        return LogUtil::registerError(__('Notice: Theme switching is currently disabled.'));
    }

    if (!SecurityUtil::checkPermission( 'Theme::', '::', ACCESS_COMMENT)) {
        return LogUtil::registerPermissionError();
    }

    // update the users record to an empty string - if this user var is empty then the site default is used.
    UserUtil::setVar('theme', '');

    return true;
}

/**
 * read an ini file
 *
 * This API is only used if
 * a) a running configuration exists and
 * b) safe mode is on
 *
 * The API is necessary becasue parse_ini_file usage is restricted under safe mode.
 */
function _theme_userapi_parseinifile($filename)
{
    $output = array();
    $contents = file($filename);
    foreach ($contents as $line) {
        $line = trim($line);
        $length = strlen($line);
        if ($length >  0) {
            if (substr($line, 0, 1) == '[' && substr($line, -1, 1) == ']') {
                $section = substr($line, 1, $length-2);
            } else {
                $parts = explode('=', $line);
                if (isset($section) && !empty($section)) {
                    $output[$section][trim($parts[0])] = trim($parts[1]);
                } else {
                    $output[trim($parts[0])] = trim($parts[1]);
                }
            }
        }
    }
    return $output;
}
