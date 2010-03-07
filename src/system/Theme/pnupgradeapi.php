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
 * Create a palette in the db
 * @access private
 */
function CreateTheme($skinname)
{
    $GLOBALS['upgradethemename'] = $skinname;
}

/**
 * Create a palette in the db
 * @access private
 */
function CreatePalette($skinname, $skinid, $default, $name, $background, $color1, $color2, $color3, $color4,
                       $color5, $color6, $color7, $color8, $sepcolor, $text1, $text2, $link, $vlink, $hover)
{
    static $default = false;

    if (!$default) {
        $GLOBALS['defaultpalette'] = $name;
        $default = true;
    }
    $GLOBALS['palettes'][$name] = array('bgcolor' => $background, 'color1' => $color1, 'color2' => $color2, 'color3' => $color3,
                                        'color4' => $color4, 'color5' => $color5, 'color6' => $color6, 'color7' => $color7,
                                        'color8' => $color8, 'sepcolor' => $sepcolor, 'link' => $link, 'vlink' => $vlink,
                                        'hover' => $hover, 'text1' => $text1, 'text2' => $text2);
}

/**
 * Create a theme configuration item
 * @access private
 */
function CreateThemeVar($skinid, $name, $description, $value)
{
    $GLOBALS['variables'][$name] = $value;
}

/**
 * Create a theme configuration item
 * @access private
 */
function CreateThemeTemplate($skinid, $label, $file, $type)
{
    // no use for the dsblock template under the new engine
    if ($label == 'dsblock') return;

    // move any 'module' page templates to the right place
    if ($type == 'module') {
        $themename = DataUtil::formatForOS($GLOBALS['upgradethemename']);
        $filename = DataUtil::formatForOS($file);
        rename("themes/$themename/templates/modules/$file", "themes/$themename/templates/$file");
        $type = 'theme';
    }

    // change the standard left, right and center labels
    switch ($label) {
        case 'lsblock' :
            $label = 'left';
            break;
        case 'rsblock' :
            $label = 'right';
            break;
        case 'ccblock' :
            $label = 'center';
            break;
    }

    if (!stristr($label, 'news') && !stristr($label, 'table')) {
        $label = str_replace('*', '', $label);
        $GLOBALS['templates'][$type][$label] = $file;
    }

    pnModAPIFunc('Theme', 'upgrade', 'rewritepagetemplate',
                 array('themename' => $GLOBALS['upgradethemename'], 'filename' => $file, 'type' => $type));
}

/**
 * Create a theme configuration item
 * @access private
 */
function CreateThemeZone($skinid, $zonename, $zonelabel, $type, $active, $skintype)
{
    if (($skintype == 'theme' || $skintype == 'module') && !stristr($zonelabel, 'news') && !stristr($zonelabel, 'table')) {
        $zonelabel = str_replace('*', '', $zonelabel);
        $GLOBALS['pageconfigurations'][] = $zonelabel;
    }
}

/**
 * write version.php
 */
function theme_upgradeapi_writeversion($args)
{
    // Security check
    if (!SecurityUtil::checkPermission('Theme::', '::', ACCESS_ADMIN)) {
        return LogUtil::registerPermissionError();
    }

    // Check our input arguments
    if (!isset($args['themename'])) {
        return LogUtil::registerArgsError();
    }

    $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($args['themename']));

    // fix some characters in the theme name
    $themeinfo['name'] = preg_replace("/[^a-z0-9_]/i", '', $themeinfo['name']);

    $pnRender = Renderer::getInstance('Theme', false);
    $pnRender->assign($themeinfo);
    $versionfile = $pnRender->fetch('upgrade/version.htm');
    $versionlangfile = $pnRender->fetch('upgrade/version_lang.htm');

    // write the main version file
    $handle = fopen("themes/$themeinfo[directory]/version.php", 'w');
    if (!$handle) {
        return LogUtil::registerError(__f('Error! Could not open file so that it could be written to: %s', "themes/$themeinfo[directory]/version.php"));
    }
    if (!fwrite($handle, $versionfile)) {
        fclose($handle);
        return LogUtil::registerError(__f('Error! could not write to file: %s', "themes/$themeinfo[directory]/version.php"));
    }
    fclose($handle);

    // write the version language file
    mkdir("themes/$themeinfo[directory]/lang/");
    mkdir("themes/$themeinfo[directory]/lang/eng");
    $handle = fopen("themes/$themeinfo[directory]/lang/eng/version.php", 'w');
    if (!$handle) {
        return LogUtil::registerError(__f('Error! Could not open file so that it could be written to: %s', "themes/$themeinfo[directory]/lang/eng/version.php"));
    }
    if (!fwrite($handle, $versionlangfile)) {
        fclose($handle);
        return LogUtil::registerError(__f('Error! could not write to file: %s', "themes/$themeinfo[directory]/lang/eng/version.php"));
    }
    fclose($handle);

    return true;
}

/**
 * write themepalettes.ini
 */
function theme_upgradeapi_writepalettes($args)
{
    // Security check
    if (!SecurityUtil::checkPermission('Theme::', '::', ACCESS_ADMIN)) {
        return LogUtil::registerPermissionError();
    }

    // Check our input arguments
    if (!isset($args['themename'])) {
        return LogUtil::registerArgsError();
    }

    $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($args['themename']));

    $pnRender = Renderer::getInstance('Theme', false);
    $pnRender->assign('palettes', $GLOBALS['palettes']);
    $file = $pnRender->fetch('upgrade/themepalettes.htm');
    if (strlen(trim($file)) == 0) {
        return true;
    }

    $handle = fopen("themes/$themeinfo[directory]/templates/config/themepalettes.ini", 'w');
    if (!$handle) {
        return LogUtil::registerError(__f('Error! Could not open file so that it could be written to: %s', "themes/$themeinfo[directory]/templates/config/themepalettes.ini"));
    }
    if (!fwrite($handle, $file)) {
        fclose($handle);
        return LogUtil::registerError(__f('Error! could not write to file: %s', "themes/$themeinfo[directory]/templates/config/themepalettes.ini"));
    }
    fclose($handle);

    return true;
}

/**
 * write themepalettes.ini
 */
function theme_upgradeapi_writevariables($args)
{
    // Security check
    if (!SecurityUtil::checkPermission('Theme::', '::', ACCESS_ADMIN)) {
        return LogUtil::registerPermissionError();
    }

    // Check our input arguments
    if (!isset($args['themename'])) {
        return LogUtil::registerArgsError();
    }

    $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($args['themename']));

    $pnRender = Renderer::getInstance('Theme', false);
    $pnRender->assign('variables', $GLOBALS['variables']);
    $file = $pnRender->fetch('upgrade/themevariables.htm');
    $handle = fopen("themes/$themeinfo[directory]/templates/config/themevariables.ini", 'w');
    if (!$handle) {
        return LogUtil::registerError(__f('Error! Could not open file so that it could be written to: %s', "themes/$themeinfo[directory]/templates/config/themevariables.ini"));
    }
    if (!fwrite($handle, $file)) {
        fclose($handle);
        return LogUtil::registerError(__f('Error! could not write to file: %s', "themes/$themeinfo[directory]/templates/config/themevariables.ini"));
    }
    fclose($handle);

    return true;
}

/**
 * write pageconfigurations.ini
 */
function theme_upgradeapi_writepageconfigurations($args)
{
    // Security check
    if (!SecurityUtil::checkPermission('Theme::', '::', ACCESS_ADMIN)) {
        return LogUtil::registerPermissionError();
    }

    // Check our input arguments
    if (!isset($args['themename'])) {
        return LogUtil::registerArgsError();
    }

    $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($args['themename']));

    $pnRender = Renderer::getInstance('Theme', false);
    $pnRender->assign('pageconfigurations', $GLOBALS['pageconfigurations']);
    $file = $pnRender->fetch('upgrade/pageconfigurations.htm');
    $handle = fopen("themes/$themeinfo[directory]/templates/config/pageconfigurations.ini", 'w');
    if (!$handle) {
        return LogUtil::registerError(__f('Error! Could not open file so that it could be written to: %s', "themes/$themeinfo[directory]/templates/config/pageconfigurations.ini"));
    }
    if (!fwrite($handle, $file)) {
        fclose($handle);
        return LogUtil::registerError(__f('Error! could not write to file: %s', "themes/$themeinfo[directory]/templates/config/pageconfigurations.ini"));
    }
    fclose($handle);

    return true;
}

/**
 * write page configuration files
 */
function theme_upgradeapi_writepageconfiguration($args)
{
    // Security check
    if (!SecurityUtil::checkPermission('Theme::', '::', ACCESS_ADMIN)) {
        return LogUtil::registerPermissionError();
    }

    // Check our input arguments
    if (!isset($args['themename']) || !isset($args['pageconfiguration'])) {
        return LogUtil::registerArgsError();
    }

    $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($args['themename']));

    $pnRender = Renderer::getInstance('Theme', false);
    $pnRender->assign('pagetemplate', $GLOBALS['templates']['theme'][$args['pageconfiguration']]);
    if (!isset($GLOBALS['defaultpalette'])) {
        $GLOBALS['defaultpalette'] = '';
    }
    $pnRender->assign('defaultpalette', $GLOBALS['defaultpalette']);
    $pnRender->assign('templates', $GLOBALS['templates']['block']);
    $file = $pnRender->fetch('upgrade/pageconfiguration.htm');
    $handle = fopen("themes/$themeinfo[directory]/templates/config/{$args['pageconfiguration']}.ini", 'w');
    if (!$handle) {
        return LogUtil::registerError(__f('Error! Could not open file so that it could be written to: %s', "themes/$themeinfo[directory]/templates/config/{$args[pageconfiguration]}.ini"));
    }
    if (!fwrite($handle, $file)) {
        fclose($handle);
        return LogUtil::registerError(__f('Error! could not write to file: %s', "themes/$themeinfo[directory]/templates/config/{$args[pageconfiguration]}.ini"));
    }
    fclose($handle);

    return true;
}

/**
 * write page configuration files
 */
function theme_upgradeapi_rewritepagetemplate($args)
{
    // Security check
    if (!SecurityUtil::checkPermission('Theme::', '::', ACCESS_ADMIN)) {
        return LogUtil::registerPermissionError();
    }

    // Check our input arguments
    if (!isset($args['themename'])) {
        return LogUtil::registerArgsError();
    }
    if (!isset($args['filename']) || !isset($args['filename'])) {
        return LogUtil::registerArgsError();
    }

    // get the theme info
    $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($args['themename']));

    // read the template
    switch ($args['type']) {
        case 'block':
            $typepath = 'blocks/';
            break;
        case 'module':
            $typepath = 'modules/';
            break;
        default:
            // if we get here the page template may still be in the modules directory which means we need to move it...
            if (file_exists($file = "themes/$themeinfo[directory]/templates/modules/$args[filename]")) {
                rename($file, "themes/$themeinfo[directory]/templates/$typepath$args[filename]");
            }
            $typepath = '';
    }

    $filepath = "themes/$themeinfo[directory]/templates/$typepath$args[filename]";
    if (!file_exists($filepath)) {
        return LogUtil::registerError(__f('Error! Could not open file so that it could be written to: %s', $filepath));
    }

    $filesource = file_get_contents($filepath);

    // define the strings to be replaced
    $xanthia2 = array('<!--[$leftblocks]-->', '<!--[$rightblocks]-->', '<!--[$centerblocks]-->',
                      '<!--[$text1color]-->', '<!--[$text2color]-->',
                      '<!--[$linkcolor]-->', '<!--[$vlinkcolor]-->', '<!--[$hovercolor]-->',
                      '<!--[typetoolv80]-->', '<!--[footmsg]-->');
    $xanthia3 = array('<!--[blockposition name=left]-->', '<!--[blockposition name=right]-->', '<!--[blockposition name=center]-->',
                      '<!--[$text1]-->', '<!--[$text2]-->',
                      '<!--[$link]-->', '<!--[$vlink]-->', '<!--[$hover]-->',
                      '', '');

    // fix the template
    $filesource = str_replace($xanthia2, $xanthia3, $filesource);

    $handle = fopen($filepath, 'w');
    if (!$handle) {
        return LogUtil::registerError(__f('Error! Could not open file so that it could be written to: %s', "themes/$themeinfo[directory]/templates/$typepath$args[filename]"));
    }
    if (!fwrite($handle, $filesource)) {
        fclose($handle);
        return LogUtil::registerError(__f('Error! could not write to file: %s', "themes/$themeinfo[directory]/templates/$typepath$args[filename]"));
    }
    fclose($handle);

    return true;
}

/**
 * write news module templates
 */
function theme_upgradeapi_rewritenewstemplates($args)
{
    // Security check
    if (!SecurityUtil::checkPermission('Theme::', '::', ACCESS_ADMIN)) {
        return LogUtil::registerPermissionError();
    }

    // Check our input arguments
    if (!isset($args['themename'])) {
        return LogUtil::registerArgsError();
    }

    // get the theme info
    $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($args['themename']));

    // make the news directory
    mkdir("themes/$themeinfo[directory]/templates/modules/News/");

    // list of filenames to work with
    $files = array('News-index.htm' => 'news_user_index.htm', 'News-article.htm' => 'news_user_articlecontent.htm');

    // rewrite each file
    foreach ($files as $oldfile => $newfile) {
        $oldfilepath = "themes/$themeinfo[directory]/templates/$oldfile";
        if (!file_exists($oldfilepath)) {
            continue;
        }
        $newfilepath = "themes/$themeinfo[directory]/templates/modules/News/$newfile";
        rename($oldfilepath, $newfilepath);
        $filesource = file_get_contents($newfilepath);

        // define the strings to be replaced
        $xanthia2 = array('<!--[$text1color]-->', '<!--[$text2color]-->', '<!--[$linkcolor]-->', '<!--[$vlinkcolor]-->', '<!--[$hovercolor]-->');
        $xanthia3 = array( '<!--[$text1]-->', '<!--[$text2]-->', '<!--[$link]-->', '<!--[$vlink]-->', '<!--[$hover]-->');

        // fix the template
        $filesource = str_replace($xanthia2, $xanthia3, $filesource);

        // for the display template we need to add pager and hook support.
        if ($newfilename == 'news_user_articlecontent.htm') {
            $pnRender = Renderer::getInstance('Theme', false);
            $pnRender->assign('filesource', $filesource);
            $filesource = $pnRender->fetch('upgrade/newsarticlecontent.htm');
        }
        $handle = fopen($newfilepath, 'w');
        if (!$handle) {
            return LogUtil::registerError(__f('Error! Could not open file so that it could be written to: %s', "themes/$themeinfo[directory]/templates/$typepath$args[filename]"));
        }
        if (!fwrite($handle, $filesource)) {
            fclose($handle);
            return LogUtil::registerError(__f('Error! could not write to file: %s', "themes/$themeinfo[directory]/templates/$typepath$args[filename]"));
        }
        fclose($handle);
    }
}
