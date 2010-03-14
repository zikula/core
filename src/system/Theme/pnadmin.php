<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2004, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_System_Modules
 * @subpackage Theme
 */

/**
 * the main admin function
 */
function theme_admin_main()
{
    // Security check will be done in view()
    return theme_admin_view();
}

/**
 * display form to create a theme
 */
function theme_admin_new()
{
    // Security check
    if (!SecurityUtil::checkPermission('Theme::', '::', ACCESS_EDIT)) {
        return LogUtil::registerPermissionError();
    }

    // Create output object
    $pnRender = Renderer::getInstance('Theme', false);

    // Return the output that has been generated to the template
    return $pnRender->fetch('theme_admin_new.htm');
}

/**
 * view all themes
 */
function theme_admin_create($args)
{
    // Security check
    if (!SecurityUtil::checkPermission('Theme::', '::', ACCESS_EDIT)) {
        return LogUtil::registerPermissionError();
    }

    // get our input
    $themeinfo = FormUtil::getPassedValue('themeinfo', isset($args['themeinfo']) ? $args['themeinfo'] : null, 'POST');

    // check our input
    if (!isset($themeinfo) || empty($themeinfo)) {
        return LogUtil::registerArgsError(pnModURL('Theme', 'admin', 'view'));
    }

    // Security check
    if (!SecurityUtil::checkPermission('Theme::', "$themeinfo[name]::", ACCESS_EDIT)) {
        return LogUtil::registerPermissionError();
    }

    // rewrite the variables to the running config
    if (pnModAPIFunc('Theme', 'admin', 'create', array('themeinfo' => $themeinfo))) {
        LogUtil::registerStatus(__f('Done! Created %s.', __('Themes manager')));
    }

    // regenerate theme list
    pnModAPIFunc('Theme', 'admin', 'regenerate');

    // redirect back to the variables page
    return pnRedirect(pnModURL('Theme', 'admin', 'view'));
}

/**
 * view all themes
 */
function theme_admin_view($args = array())
{
    // Security check
    if (!SecurityUtil::checkPermission('Theme::', '::', ACCESS_EDIT)) {
        return LogUtil::registerPermissionError();
    }

    if(isset($GLOBALS['ZConfig']['multisites']['multi']) && $GLOBALS['ZConfig']['multisites']['multi'] == 1){
        // only the main site can regenerate the themes list
        if($GLOBALS['ZConfig']['multisites']['mainSiteURL'] == FormUtil::getPassedValue('siteDNS', null, 'GET')){
            //return true but any action has been made
            pnModAPIFunc('Theme', 'admin', 'regenerate');
        }
    } else {
        pnModAPIFunc('Theme', 'admin', 'regenerate');
    }

    // get our input
    $startnum = FormUtil::getPassedValue('startnum', isset($args['startnum']) ? $args['startnum'] : 1, 'GET');
    $startlet = FormUtil::getPassedValue('startlet', isset($args['startlet']) ? $args['startlet'] : null, 'GET');

    // we need this value multiple times, so we keep it
    $itemsperpage = pnModGetVar('Theme', 'itemsperpage');

    // Create output object
    $pnRender = Renderer::getInstance('Theme', false);

    // call the API to get a list of all themes in the themes dir
    $allthemes = ThemeUtil::getAllThemes(PNTHEME_FILTER_ALL, PNTHEME_STATE_ALL);
    ksort($allthemes);

    // filter by letter if required
    if (isset($startlet) && !empty($startlet)) {
        $allthemes = array_filter($allthemes, '_theme_admin_filterbyletter');
    }
    $themes = array_slice($allthemes, $startnum-1, $itemsperpage);
    $pnRender->assign('themes', $themes);

    // assign default theme
    $pnRender->assign('currenttheme', pnConfigGetVar('Default_Theme'));

    // assign the values for the smarty plugin to produce a pager
    $pnRender->assign('pager', array('numitems'     => sizeof($allthemes),
                                     'itemsperpage' => $itemsperpage));

    // Return the output that has been generated to the template
    return $pnRender->fetch('theme_admin_view.htm');
}

/**
 * filter theme array by letter
 *
 * @access private
 */
function _theme_admin_filterbyletter($theme)
{
    static $startlet;

    if (!isset($startlet)) {
        $startlet = FormUtil::getPassedValue('startlet', isset($args['startlet']) ? $args['startlet'] : null, 'GET');
    }

    if (strcasecmp($theme['name'][0], $startlet)) {
        return false;
    } else {
        return true;
    }
}

/**
 * modify theme
 *
 */
function theme_admin_modify($args)
{
    // get our input
    $themename = FormUtil::getPassedValue('themename', isset($args['themename']) ? $args['themename'] : null, 'GET');

    // check our input
    if (!isset($themename) || empty($themename)) {
        return LogUtil::registerArgsError(pnModURL('Theme', 'admin', 'view'));
    }

    // Security check
    if (!SecurityUtil::checkPermission('Theme::', "$themename::", ACCESS_EDIT)) {
        return LogUtil::registerPermissionError();
    }

    // get the theme info
    $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($themename));

    // check if we can edit the theme and, if not, create the running config
    if (!is_writable('themes/' . DataUtil::formatForOS($themeinfo['directory']) . '/templates/config') && $themeinfo['type'] == PNTHEME_TYPE_XANTHIA3) {
        pnModAPIFunc('Theme', 'admin', 'createrunningconfig', array('themename' => $themename));
    }

    // Create output object
    $pnRender = Renderer::getInstance('Theme', false);

    // assign the theme name
    $pnRender->assign('themename', $themename);

    // assign the theme info
    $pnRender->assign('themeinfo', $themeinfo);

    // Return the output that has been generated to the template
    return $pnRender->fetch('theme_admin_modify.htm');
}

/**
 * update the theme variables
 *
 */
function theme_admin_updatesettings($args)
{
    // get our input
    $themeinfo = FormUtil::getPassedValue('themeinfo', isset($args['themeinfo']) ? $args['themeinfo'] : null, 'POST');
    $themename = FormUtil::getPassedValue('themename', isset($args['themename']) ? $args['themename'] : null, 'POST');

    // check our input
    if (!isset($themename) || empty($themename)) {
        LogUtil::registerArgsError();
        return pnRedirect(pnModURL('Theme', 'admin', 'view'));
    }

    // Security check
    if (!SecurityUtil::checkPermission('Theme::', "$themename::settings", ACCESS_EDIT)) {
        return LogUtil::registerPermissionError();
    }

    // Confirm authorisation code
    if (!SecurityUtil::confirmAuthKey()) {
        return LogUtil::registerAuthidError(pnModURL('Theme','admin','view'));
    }

    // get the existing theme info
    $curthemeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($themename));

    // reset the flag fields so that the form settings always get used
    $curthemeinfo['user'] = 0;
    $curthemeinfo['system'] = 0;
    $curthemeinfo['admin'] = 0;

    // add the new theme variable to the existing variables
    $newthemeinfo = array_merge($curthemeinfo, $themeinfo);

    // rewrite the variables to the running config
    if (pnModAPIFunc('Theme', 'admin', 'updatesettings', array('theme' => $themename, 'themeinfo' => $newthemeinfo))) {
        LogUtil::registerStatus(__('Done! Saved module configuration.'));
    }

    // redirect back to the variables page
    return pnRedirect(pnModURL('Theme', 'admin', 'view'));
}

/**
 * display the theme variables
 *
 */
function theme_admin_variables($args)
{
    // get our input
    $themename = FormUtil::getPassedValue('themename', isset($args['themename']) ? $args['themename'] : null, 'GET');

    // check our input
    if (!isset($themename) || empty($themename)) {
        return LogUtil::registerArgsError(pnModURL('Theme', 'admin', 'view'));
    }

    $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($themename));
    if (!file_exists('themes/'.DataUtil::formatForOS($themeinfo['directory']).'/version.php')) {
        return LogUtil::registerArgsError(pnModURL('Theme', 'admin', 'view'));
    }

    // Security check
    if (!SecurityUtil::checkPermission('Theme::', "$themename::variables", ACCESS_EDIT)) {
        return LogUtil::registerPermissionError();
    }

    // load the language file
    ZLanguage::bindThemeDomain($themename);

    // Create output object
    $pnRender = Renderer::getInstance('Theme', false);

    // assign the variables
    $pnRender->assign('variables', pnModAPIFunc('Theme', 'user', 'getvariables', array('theme' => $themename, 'formatting' => true)));

    // assign the theme name
    $pnRender->assign('themename', $themename);

    // assign the theme info
    $pnRender->assign('themeinfo', $themeinfo);

    // Return the output that has been generated by this function
    return $pnRender->fetch('theme_admin_variables.htm');
}

/**
 * update the theme variables
 *
 */
function theme_admin_updatevariables($args)
{
    // get our input
    $variablesnames = FormUtil::getPassedValue('variablesnames', isset($args['variablesnames']) ? $args['variablesnames'] : null, 'POST');
    $variablesvalues = FormUtil::getPassedValue('variablesvalues', isset($args['variablesvalues']) ? $args['variablesvalues'] : null, 'POST');
    $newvariablename = FormUtil::getPassedValue('newvariablename', isset($args['newvariablename']) ? $args['newvariablename'] : null, 'POST');
    $newvariablevalue = FormUtil::getPassedValue('newvariablevalue', isset($args['newvariablevalue']) ? $args['newvariablevalue'] : null, 'POST');
    $themename = FormUtil::getPassedValue('themename', isset($args['themename']) ? $args['themename'] : null, 'POST');

    // check our input
    if (!isset($themename) || empty($themename)) {
        return LogUtil::registerArgsError(pnModURL('Theme', 'admin', 'view'));
    }

    $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($themename));
    if (!file_exists('themes/'.DataUtil::formatForOS($themeinfo['directory']).'/version.php')) {
        return LogUtil::registerArgsError(pnModURL('Theme', 'admin', 'view'));
    }

    // Security check
    if (!SecurityUtil::checkPermission('Theme::', "$themename::variables", ACCESS_EDIT)) {
        return LogUtil::registerPermissionError();
    }

    // Confirm authorisation code
    if (!SecurityUtil::confirmAuthKey()) {
        return LogUtil::registerAuthidError(pnModURL('Theme','admin','view'));
    }

    // get the original file source
    $variables = pnModAPIFunc('Theme', 'user', 'getvariables', array('theme' => $themename, 'formatting' => true, 'explode' => false));

    // form our existing variables
    $newvariables = array();
    foreach ($variablesnames as $id => $variablename) {
        $newvariables[$variablename] = $variablesvalues[$id];
    }
    // add the new theme variable to the existing variables
    if (!empty($newvariablename) && !empty($newvariablevalue)) {
        $newvariables[$newvariablename] = $newvariablevalue;
    }

    // re-add the new values
    $variables['variables'] = $newvariables;

    // rewrite the variables to the running config
    pnModAPIFunc('Theme', 'user', 'writeinifile', array('theme' => $themename, 'assoc_arr' => $variables, 'has_sections' => true, 'file' => 'themevariables.ini'));

    // set a status message
    LogUtil::registerStatus(__('Done! Saved your changes.'));

    // redirect back to the variables page
    return pnRedirect(pnModURL('Theme', 'admin', 'variables', array('themename' => $themename)));
}

/**
 * display the themes block positions
 *
 */
function theme_admin_palettes($args)
{
    // get our input
    $themename = FormUtil::getPassedValue('themename', isset($args['themename']) ? $args['themename'] : null, 'GET');

    // check our input
    if (!isset($themename) || empty($themename)) {
        return LogUtil::registerArgsError(pnModURL('Theme', 'admin', 'view'));
    }

    $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($themename));
    if (!file_exists('themes/'.DataUtil::formatForOS($themeinfo['directory']).'/version.php')) {
        return LogUtil::registerArgsError(pnModURL('Theme', 'admin', 'view'));
    }

    // Security check
    if (!SecurityUtil::checkPermission('Theme::', "$themename::colors", ACCESS_EDIT)) {
        return LogUtil::registerPermissionError();
    }

    // Create output object
    $pnRender = Renderer::getInstance('Theme', false);

    // assign the theme name
    $pnRender->assign('themename', $themename);

    // assign the theme info
    $pnRender->assign('themeinfo', $themeinfo);

    // assign the palettes
    $pnRender->assign('palettes', pnModAPIFunc('Theme', 'user', 'getpalettes', array('theme' => $themename)));

    // Return the output that has been generated by this function
    return $pnRender->fetch('theme_admin_palettes.htm');
}

/**
 * update the theme settings
 *
 */
function theme_admin_updatepalettes($args)
{
    // get our input
    $palettes = FormUtil::getPassedValue('palettes', isset($args['palettes']) ? $args['palettes'] : null, 'POST');
    $palettename = FormUtil::getPassedValue('palettename', isset($args['palettename']) ? $args['palettename'] : null, 'POST');
    $bgcolor = FormUtil::getPassedValue('bgcolor', isset($args['bgcolor']) ? $args['bgcolor'] : null, 'POST');
    $color1 = FormUtil::getPassedValue('color1', isset($args['color1']) ? $args['color1'] : null, 'POST');
    $color2 = FormUtil::getPassedValue('color2', isset($args['color2']) ? $args['color2'] : null, 'POST');
    $color3 = FormUtil::getPassedValue('color3', isset($args['color3']) ? $args['color3'] : null, 'POST');
    $color4 = FormUtil::getPassedValue('color4', isset($args['color4']) ? $args['color4'] : null, 'POST');
    $color5 = FormUtil::getPassedValue('color5', isset($args['color5']) ? $args['color5'] : null, 'POST');
    $color6 = FormUtil::getPassedValue('color6', isset($args['color6']) ? $args['color6'] : null, 'POST');
    $color7 = FormUtil::getPassedValue('color7', isset($args['color7']) ? $args['color7'] : null, 'POST');
    $color8 = FormUtil::getPassedValue('color8', isset($args['color8']) ? $args['color8'] : null, 'POST');
    $sepcolor = FormUtil::getPassedValue('sepcolor', isset($args['sepcolor']) ? $args['sepcolor'] : null, 'POST');
    $link = FormUtil::getPassedValue('link', isset($args['link']) ? $args['link'] : null, 'POST');
    $vlink = FormUtil::getPassedValue('vlink', isset($args['vlink']) ? $args['vlink'] : null, 'POST');
    $hover = FormUtil::getPassedValue('hover', isset($args['hover']) ? $args['hover'] : null, 'POST');
    $themename = FormUtil::getPassedValue('themename', isset($args['themename']) ? $args['themename'] : null, 'POST');

    // check if this is a valid theme
    $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($themename));
    if (!file_exists('themes/'.DataUtil::formatForOS($themeinfo['directory']).'/version.php')) {
        return LogUtil::registerArgsError(pnModURL('Theme', 'admin', 'view'));
    }

    // Security check
    if (!SecurityUtil::checkPermission('Theme::', "$themename::palettes", ACCESS_EDIT)) {
        return LogUtil::registerPermissionError();
    }

    // Confirm authorisation code
    if (!SecurityUtil::confirmAuthKey()) {
        return LogUtil::registerAuthidError(pnModURL('Theme','admin','view'));
    }

    // check if we've got a new palette being created
    if (isset($themename) && !empty($themename) &&
        isset($palettename) && !empty($palettename) &&
        isset($bgcolor) && !empty($bgcolor) &&
        isset($color1) && !empty($color1) &&
        isset($color2) && !empty($color2) &&
        isset($color3) && !empty($color3) &&
        isset($color4) && !empty($color4) &&
        isset($color5) && !empty($color5) &&
        isset($color6) && !empty($color6) &&
        isset($color7) && !empty($color7) &&
        isset($color8) && !empty($color8) &&
        isset($sepcolor) && !empty($sepcolor) &&
        isset($link) && !empty($link) &&
        isset($vlink) && !empty($vlink) &&
        isset($hover) && !empty($hover)) {
        // add the new theme setting to the existing settings
        $palettes[$palettename] = array('bgcolor' => $bgcolor, 'color1' => $color1, 'color2' => $color2, 'color3' => $color3,
                                        'color4' => $color4, 'color5' => $color5, 'color6' => $color6, 'color7' => $color7, 'color8' => $color8,
                                        'sepcolor' => $sepcolor, 'link' => $link, 'vlink' => $vlink, 'hover' => $hover) ;
    } else {
        LogUtil::registerError(__('Notice: Please make sure you type an entry in every field. Your palette cannot be saved if you do not.'));
        return pnRedirect(pnModURL('Theme', 'admin', 'view'));
    }

    // rewrite the settings to the running config
    pnModAPIFunc('Theme', 'user', 'writeinifile', array('theme' => $themename, 'assoc_arr' => $palettes, 'has_sections' => true, 'file' => 'themepalettes.ini'));

    // set a status message
    LogUtil::registerStatus(__('Done! Saved your changes.'));

    // redirect back to the settings page
    return pnRedirect(pnModURL('Theme', 'admin', 'palettes', array('themename' => $themename)));
}

/**
 * display the content wrappers for the theme
 *
 */
function theme_admin_pageconfigurations($args)
{
    // get our input
    $themename = FormUtil::getPassedValue('themename', isset($args['themename']) ? $args['themename'] : null, 'GET');

    // check our input
    if (!isset($themename) || empty($themename)) {
        return LogUtil::registerArgsError(pnModURL('Theme', 'admin', 'view'));
    }

    $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($themename));
    if (!file_exists('themes/'.DataUtil::formatForOS($themeinfo['directory']).'/version.php')) {
        return LogUtil::registerArgsError(pnModURL('Theme', 'admin', 'view'));
    }

    // Security check
    if (!SecurityUtil::checkPermission('Theme::', "$themename::pageconfigurations", ACCESS_EDIT)) {
        return LogUtil::registerPermissionError();
    }

    // Create output object
    $pnRender = Renderer::getInstance('Theme', false);

    // assign the theme name
    $pnRender->assign('themename', $themename);

    // assign the theme info
    $pnRender->assign('themeinfo', $themeinfo);

    // assign an array to populate the modules dropdown
    $allmods = pnModGetAllMods();
    $mods = array();
    foreach ($allmods as $mod) {
        $mods[$mod['name']] = $mod['displayname'];
    }
    $pnRender->assign('modules', $mods);

    // assign the page configuration assignments
    $pageconfigurations = pnModAPIFunc('Theme', 'user', 'getpageconfigurations', array('theme' => $themename));
    ksort($pageconfigurations);
    $pnRender->assign('pageconfigurations', $pageconfigurations);

    // identify unique page configuration files
    $pageconfigfiles = array();
    foreach ($pageconfigurations as $pageconfiguration) {
        $pageconfigfiles[$pageconfiguration['file']] = file_exists("themes/$themeinfo[directory]/templates/config/$pageconfiguration[file]");
    }
    $pnRender->assign('pageconfigs', $pageconfigfiles);

    // Return the output that has been generated by this function
    return $pnRender->fetch('theme_admin_pageconfigurations.htm');
}

/**
 * modify a theme page configuration
 *
 */
function theme_admin_modifypageconfigtemplates($args)
{
    // get our input
    $themename = FormUtil::getPassedValue('themename', isset($args['themename']) ? $args['themename'] : null, 'GET');
    $filename = FormUtil::getPassedValue('filename', isset($args['filename']) ? $args['filename'] : null, 'GET');

    // check our input
    if (!isset($themename) || empty($themename)) {
        return LogUtil::registerArgsError(pnModURL('Theme', 'admin', 'view'));
    }

    $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($themename));
    if (!file_exists('themes/'.DataUtil::formatForOS($themeinfo['directory']).'/version.php')) {
        return LogUtil::registerArgsError(pnModURL('Theme', 'admin', 'view'));
    }

    // Security check
    if (!SecurityUtil::checkPermission('Theme::', "$themename::pageconfigurations", ACCESS_EDIT)) {
        return LogUtil::registerPermissionError();
    }

    // read our configuration file
    $pageconfiguration = pnModAPIFunc('Theme', 'user', 'getpageconfiguration', array('theme' => $themename, 'filename' => $filename));
    if (!isset($pageconfiguration) || empty($pageconfiguration)) {
        return LogUtil::registerArgsError(pnModURL('Theme', 'admin', 'view'));
    }

    // Create output object
    $pnRender = Renderer::getInstance('Theme', false);

    // assign the base filename
    $pnRender->assign('filename', $filename);
    $pnRender->assign('themename', $themename);

    // assign the theme info
    $pnRender->assign('themeinfo', $themeinfo);

    // get all templates
    $pnRender->assign('moduletemplates', pnModAPIFunc('Theme', 'user', 'gettemplates', array('theme' => $themename)));
    $pnRender->assign('blocktemplates', pnModAPIFunc('Theme', 'user', 'gettemplates', array('theme' => $themename, 'type' => 'blocks')));

    // get all palettes
    $pnRender->assign('palettes', pnModAPIFunc('Theme', 'user', 'getpalettenames', array('theme' => $themename)));

    // get all block positions
    $blockpositions = pnModAPIFunc('Blocks', 'user', 'getallpositions');
    $positions = array();
    foreach ($blockpositions as $blockposition) {
        // check the page configuration
        if (!isset($pageconfiguration['blockpositions'][$blockposition['name']])) {
            $pageconfiguration['blockpositions'][$blockposition['name']] = '';
        }
        $positions[$blockposition['name']] = $blockposition['name'];
    }
    $pnRender->assign('blockpositions', $positions);

    // call the block API to get a list of all available blocks
    $pnRender->assign('allblocks', $allblocks = pnBlockLoadAll());
    foreach ($allblocks as $key => $blocks) {
        foreach ($blocks as $block) {
            // check the page configuration
            if (!isset($pageconfiguration['blocktypes'][$block['bkey']])) {
                $pageconfiguration['blocktypes'][$block['bkey']] = '';
            }
        }
    }

    // call the block API to get a list of all defined block instances
    $pnRender->assign('blocks', $blocks = pnModAPIFunc('Blocks', 'user', 'getall'));
    foreach ($blocks as $block) {
        // check the page configuration
        if (!isset($pageconfiguration['blockinstances'][$block['bid']])) {
            $pageconfiguration['blockinstances'][$block['bid']] = '';
        }
    }

    // palette default
    if (!isset($pageconfiguration['palette'])) {
        $pageconfiguration['palette'] = null;
    }

    // block  default
    if (!isset($pageconfiguration['block'])) {
        $pageconfiguration['block'] = null;
    }

    // wrapper defaults
    if (!isset($pageconfiguration['modulewrapper'])) {
        $pageconfiguration['modulewrapper'] = true;
    }
    if (!isset($pageconfiguration['blockwrapper'])) {
        $pageconfiguration['blockwrapper'] = true;
    }

    // filter defaults
    if (!isset($pageconfiguration['filters'])) {
        $pageconfiguration['filters'] = array(
            'outputfilters' => null,
            'prefilters' => null,
            'postfilters' => null
        );
    }

    // assign the page configuration array
    $pnRender->assign('pageconfiguration', $pageconfiguration);

    // Return the output that has been generated by this function
    return $pnRender->fetch('theme_admin_modifypageconfigtemplates.htm');
}

/**
 * modify a theme page configuration
 *
 */
function theme_admin_updatepageconfigtemplates($args)
{
    // get our input
    $themename = FormUtil::getPassedValue('themename', isset($args['themename']) ? $args['themename'] : null, 'POST');
    $filename = FormUtil::getPassedValue('filename', isset($args['filename']) ? $args['filename'] : null, 'POST');
    $pagetemplate = FormUtil::getPassedValue('pagetemplate', isset($args['pagetemplate']) ? $args['pagetemplate'] : null, 'POST');
    $pagepalette = FormUtil::getPassedValue('pagepalette', isset($args['pagepalette']) ? $args['pagepalette'] : null, 'POST');
    $blockpositiontemplates = FormUtil::getPassedValue('blockpositiontemplates', isset($args['blockpositiontemplates']) ? $args['blockpositiontemplates'] : null, 'POST');
    $blocktypetemplates = FormUtil::getPassedValue('blocktypetemplates', isset($args['blocktypetemplates']) ? $args['blocktypetemplates'] : null, 'POST');
    $blockinstancetemplates = FormUtil::getPassedValue('blockinstancetemplates', isset($args['blockinstancetemplates']) ? $args['blockinstancetemplates'] : null, 'POST');
    $modulewrapper = (int)FormUtil::getPassedValue('modulewrapper', isset($args['modulewrapper']) ? $args['modulewrapper'] : 0, 'POST');
    $blockwrapper = (int)FormUtil::getPassedValue('blockwrapper', isset($args['blockwrapper']) ? $args['blockwrapper'] : 0, 'POST');
    $filters = FormUtil::getPassedValue('filters', isset($args['filters']) ? $args['filters'] : null, 'POST');

    // check our input
    if (!isset($themename) || empty($themename)) {
        return LogUtil::registerArgsError(pnModURL('Theme', 'admin', 'view'));
    }

    $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($themename));
    if (!file_exists('themes/'.DataUtil::formatForOS($themeinfo['directory']).'/version.php')) {
        return LogUtil::registerArgsError(pnModURL('Theme', 'admin', 'view'));
    }

    // Security check
    if (!SecurityUtil::checkPermission('Theme::', "$themename::pageconfigurations", ACCESS_EDIT)) {
        return LogUtil::registerPermissionError();
    }

    // Confirm authorisation code
    if (!SecurityUtil::confirmAuthKey()) {
        return LogUtil::registerAuthidError(pnModURL('Theme','admin','view'));
    }

    // form the new page configuration
    $pageconfiguration['page'] = $pagetemplate;
    $pageconfiguration['palette'] = $pagepalette;
    $pageconfiguration['modulewrapper'] = $modulewrapper;
    $pageconfiguration['blockwrapper'] = $blockwrapper;
    $pageconfiguration['blocktypes'] = $blocktypetemplates;
    $pageconfiguration['blockpositions'] = $blockpositiontemplates;
    $pageconfiguration['blockinstances'] = $blockinstancetemplates;

    // check if the filters exists. We do this now and not when using them to increase performance
    $filters['outputfilters'] = _theme_admin_checkfilters('outputfilter', $filters['outputfilters']);
    $filters['prefilters']    = _theme_admin_checkfilters('prefilter', $filters['prefilters']);
    $filters['postfilters']   = _theme_admin_checkfilters('postfilter', $filters['postfilters']);
    $pageconfiguration['filters'] = $filters;

    // write the page configuration
    pnModAPIFunc('Theme', 'user', 'writeinifile', array('theme' => $themename, 'assoc_arr' => $pageconfiguration, 'has_sections' => true, 'file' => $filename));

    // set a status message
    LogUtil::registerStatus(__('Done! Saved your changes.'));

    // return the user to the correct place
    return pnRedirect(pnModURL('Theme', 'admin', 'pageconfigurations', array('themename' => $themename)));
}

/**
 * check if the given filter exists
 *
 */
function _theme_admin_checkfilters($type, $filters)
{
    $filters = trim($filters);
    if (empty($filters)) {
        return $filters;
    }

    $ostype = DataUtil::formatForOS($type);
    $dummy = Renderer::getInstance('Theme');

    $filters = explode(',', $filters);
    $newfilters = array();
    foreach ($filters as $filter) {
        foreach ($dummy->plugins_dir as $plugindir) {
            if (file_exists($plugindir .'/'. $ostype .'.'. DataUtil::formatForOS($filter) .'.php')) {
                $newfilters[] = $filter;
                break;
            }
        }
    }
    $leftover = array_diff($filters, $newfilters);
    if (count($leftover)>0) {
        LogUtil::registerError(__f('Error! Removed unknown \'%1$s\': \'%2$s\'.', array(DataUtil::formatForDisplay($type), DataUtil::formatForDisplay(implode(',', $leftover)))));
    }
    return implode(',', $newfilters);
}

/**
 * modify a theme page configuration
 *
 */
function theme_admin_modifypageconfigurationassignment($args)
{
    // get our input
    $themename = FormUtil::getPassedValue('themename', isset($args['themename']) ? $args['themename'] : null, 'GET');
    $pcname = FormUtil::getPassedValue('pcname', isset($args['pcname']) ? $args['pcname'] : null, 'GET');

    // check our input
    if (!isset($themename) || empty($themename)) {
        return LogUtil::registerArgsError(pnModURL('Theme', 'admin', 'view'));
    }

    $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($themename));
    if (!file_exists('themes/'.DataUtil::formatForOS($themeinfo['directory']).'/version.php')) {
        return LogUtil::registerArgsError(pnModURL('Theme', 'admin', 'view'));
    }

    // Security check
    if (!SecurityUtil::checkPermission('Theme::', "$themename::pageconfigurations", ACCESS_EDIT)) {
        return LogUtil::registerPermissionError();
    }

    // Create output object
    $pnRender = Renderer::getInstance('Theme', false);

    // assign the page config assignment name and theme name
    $pnRender->assign('themename', $themename);
    $pnRender->assign('pcname', $pcname);

    // assign the theme info
    $pnRender->assign('themeinfo', $themeinfo);

    // assign all modules
    $allmods = pnModGetAllMods();
    $mods = array();
    foreach ($allmods as $mod) {
        $mods[$mod['name']] = $mod['name'];
    }
    $pnRender->assign('modules', $mods);

    // get all pageconfigurations
    $pageconfigurations = pnModAPIFunc('Theme', 'user', 'getpageconfigurations', array('theme' => $themename));
    if (!isset($pageconfigurations[$pcname])) {
        LogUtil::registerError(__('Error! No such page configuration assignment found.'));
        return pnRedirect(pnModURL('Theme', 'admin', 'view'));
    }
    $pageconfigparts = explode('/', $pcname);

    // assign the config filename
    $pnRender->assign('filename', $pageconfigurations[$pcname]['file']);

    // form the page config assignment array setting some useful key names
    $pageconfigassignment = array('pagemodule' => null, 'pagetype' => null, 'pagefunc' => null, 'pagecustomargs' => null);
    $pageconfigassignment['pagemodule'] = $pageconfigparts[0];
    if (isset($pageconfigparts[1])) {
        $pageconfigassignment['pagetype'] = $pageconfigparts[1];
    }
    if (isset($pageconfigparts[2])) {
        $pageconfigassignment['pagefunc'] = $pageconfigparts[2];
    }
    if (isset($pageconfigparts[3])) {
        $pageconfigassignment['pagecustomargs'] = $pageconfigparts[3];
    }
    $pnRender->assign($pageconfigassignment);

    // Return the output that has been generated by this function
    return $pnRender->fetch('theme_admin_modifypageconfigurationassignment.htm');
}

/**
 * modify a theme page configuration
 *
 */
function theme_admin_updatepageconfigurationassignment($args)
{
    // get our input
    $themename = FormUtil::getPassedValue('themename', isset($args['themename']) ? $args['themename'] : null, 'POST');
    $pcname = FormUtil::getPassedValue('pcname', isset($args['pcname']) ? $args['pcname'] : null, 'POST');
    $pagemodule = FormUtil::getPassedValue('pagemodule', isset($args['pagemodule']) ? $args['pagemodule'] : null, 'POST');
    $pagetype = FormUtil::getPassedValue('pagetype', isset($args['pagetype']) ? $args['pagetype'] : 'user', 'POST');
    $pagefunc = FormUtil::getPassedValue('pagefunc', isset($args['pagefunc']) ? $args['pagefunc'] : null, 'POST');
    $pagecustomargs = FormUtil::getPassedValue('pagecustomargs', isset($args['pagecustomargs']) ? $args['pagecustomargs'] : null, 'POST');
    $filename = FormUtil::getPassedValue('filename', isset($args['filename']) ? $args['filename'] : null, 'POST');

    // check our input
    if (!isset($themename) || empty($themename)) {
        return LogUtil::registerArgsError(pnModURL('Theme', 'admin', 'view'));
    }

    $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($themename));
    if (!file_exists('themes/'.DataUtil::formatForOS($themeinfo['directory']).'/version.php')) {
        return LogUtil::registerArgsError(pnModURL('Theme', 'admin', 'view'));
    }

    // Security check
    if (!SecurityUtil::checkPermission('Theme::', "$themename::pageconfigurations", ACCESS_EDIT)) {
        return LogUtil::registerPermissionError();
    }

    // Confirm authorisation code
    if (!SecurityUtil::confirmAuthKey()) {
        return LogUtil::registerAuthidError(pnModURL('Theme','admin','view'));
    }

    // read the list of existing page config assignments
    $pageconfigurations = pnModAPIFunc('Theme', 'user', 'getpageconfigurations', array('theme' => $themename));

    // remove the config assignment being updated
    if (isset($pcname)) {
        unset($pageconfigurations[$pcname]);
    }

    // form the new page configuration
    $newpageconfiguration = $pagemodule;
    if ($pagemodule != '*home' && $pagemodule != '*admin' && $pagemodule != 'master') {
        $newpageconfiguration .= '/';
        if (isset($pagetype)) {
            $newpageconfiguration .= $pagetype;
        }
        $newpageconfiguration .= '/';
        if (isset($pagefunc)) {
            $newpageconfiguration .= $pagefunc;
        }
        $newpageconfiguration .= '/';
        if (isset($pagecustomargs)) {
            $newpageconfiguration .= $pagecustomargs;
        }
    }
    // remove any 'empty' parameters from the string
    $newpageconfiguration = trim($newpageconfiguration, '/');
    $pageconfigurations[$newpageconfiguration] = array('file' => $filename);

    // sort the page configurations
    ksort($pageconfigurations);

    // write the page configurations back to the running config
    pnModAPIFunc('Theme', 'user', 'writeinifile', array('theme' => $themename, 'assoc_arr' => $pageconfigurations, 'has_sections' => true, 'file' => 'pageconfigurations.ini'));

    // set a status message
    LogUtil::registerStatus(__('Done! Saved your changes.'));

    // return the user to the correct place
    return pnRedirect(pnModURL('Theme', 'admin', 'pageconfigurations', array('themename' => $themename)));
}

/**
 * delete a theme page configuration assignment
 *
 */
function theme_admin_deletepageconfigurationassignment($args)
{
    $themename = FormUtil::getPassedValue('themename', isset($args['themename']) ? $args['themename'] : null, 'REQUEST');
    $pcname = FormUtil::getPassedValue('pcname', isset($args['pcname']) ? $args['pcname'] : null, 'REQUEST');
    $confirmation = FormUtil::getPassedValue('confirmation', null, 'POST');

    // Get the theme info
    $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($themename));

    if ($themeinfo == false) {
        return LogUtil::registerError(__('Sorry! No such item found.'));
    }

    // Security check
    if (!SecurityUtil::checkPermission('Theme::', "$themename::pageconfigurations", ACCESS_DELETE)) {
        return LogUtil::registerPermissionError();
    }

    // Check for confirmation.
    if (empty($confirmation)) {
        // No confirmation yet
        // Create output object
        $pnRender = Renderer::getInstance('Theme', false);

        // Assign the theme info
        $pnRender->assign($themeinfo);
        // Assign the page configuration name
        $pnRender->assign('pcname', $pcname);

        // Return the output that has been generated by this function
        return $pnRender->fetch('theme_admin_deletepageconfigurationassignment.htm');
    }

    // If we get here it means that the user has confirmed the action

    // Confirm authorisation code
    if (!SecurityUtil::confirmAuthKey()) {
        LogUtil::registerAuthidError(pnModURL('Theme', 'admin', 'pageconfigurations', array('themename' => $themename)));
    }

    // Delete the admin message
    // The return value of the function is checked
    if (pnModAPIFunc('Theme', 'admin', 'deletepageconfigurationassignment', array('themename' => $themename, 'pcname' => $pcname))) {
        // Success
        LogUtil::registerStatus(__('Done! Deleted it.'));
    }

    // return the user to the correct place
    return pnRedirect(pnModURL('Theme', 'admin', 'pageconfigurations', array('themename' => $themename)));
}

/**
 * display the theme credits
 *
 *
 */
function theme_admin_credits($args)
{
    // get our input
    $themename = FormUtil::getPassedValue('themename', isset($args['themename']) ? $args['themename'] : null, 'GET');

    // check our input
    if (!isset($themename) || empty($themename)) {
        return LogUtil::registerArgsError(pnModURL('Theme', 'admin', 'view'));
    }

    // Security check
    if (!SecurityUtil::checkPermission('Theme::', "$themename::credits", ACCESS_EDIT)) {
        return LogUtil::registerArgsError(pnModURL('Theme', 'admin', 'view'));
    }

    // Create output object
    $pnRender = Renderer::getInstance('Theme', false);

    // assign the theme info
    $pnRender->assign('themeinfo', ThemeUtil::getInfo(ThemeUtil::getIDFromName($themename)));

    // Render the output
    return $pnRender->fetch('theme_admin_credits.htm');
}


/**
 * set theme as default for site
 *
 */
function theme_admin_setasdefault($args)
{
    // get our input
    $themename = FormUtil::getPassedValue('themename', isset($args['themename']) ? $args['themename'] : null, 'REQUEST');
    $confirmation = (int)FormUtil::getPassedValue ('confirmation', false, 'REQUEST');
    $resetuserselected = FormUtil::getPassedValue('resetuserselected', isset($args['resetuserselected']) ? $args['resetuserselected'] : null, 'POST');

    // check our input
    if (!isset($themename) || empty($themename)) {
        return LogUtil::registerArgsError(pnModURL('Theme', 'admin', 'view'));
    }

    // Security check
    if (!SecurityUtil::checkPermission('Theme::', '::', ACCESS_ADMIN)) {
        return LogUtil::registerPermissionError();
    }

    // Check for confirmation.
    if (empty($confirmation)) {
        // No confirmation yet
        $pnRender = Renderer::getInstance('Theme', false);

        // Add a hidden field for the item ID to the output
        $pnRender->assign('themename', $themename);

        // assign the var defining if users can change themes
        $pnRender->assign('theme_change', pnConfigGetVar('theme_change'));

        // Return the output that has been generated by this function
        return $pnRender->fetch('theme_admin_setasdefault.htm');
    }

    // If we get here it means that the user has confirmed the action

    // Confirm authorisation code
    if (!SecurityUtil::confirmAuthKey()) {
        return LogUtil::registerAuthidError(pnModURL('Theme','admin','view'));
    }

    // Set the default theme
    if (pnModAPIFunc('Theme', 'admin', 'setasdefault', array('themename' => $themename, 'resetuserselected' => $resetuserselected))) {
        // Success
        LogUtil::registerStatus(__('Done! Changed default theme.'));
    }

    return pnRedirect(pnModURL('Theme', 'admin', 'view'));

}

/**
 * upgrade theme
 *
 */
function theme_admin_upgrade($args)
{
    // get our input
    $themename = FormUtil::getPassedValue('themename', isset($args['themename']) ? $args['themename'] : null, 'REQUEST');

    // get the theme info
    $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($themename));

    // check the permissions required to upgrade the theme
    if (!is_writable("themes/$themeinfo[directory]") || !is_writable("themes/$themeinfo[directory]/templates")) {
        LogUtil::registerError(__('Notice: Permissions for the theme directory must be set so that it can be written to.'));
        return pnRedirect(pnModURL('Theme', 'admin', 'view'));
    }

    if (!file_exists("themes/$themeinfo[directory]/xaninit.php") || $themeinfo['type'] != 2) {
        LogUtil::registerError(__("Error! This theme cannot be upgraded because it is not a Xanthia 2 theme."));
        return pnRedirect(pnModURL('Theme', 'admin', 'view'));
    }

    // make the config directory
    if (!is_dir("themes/$themeinfo[directory]/templates/config") && !mkdir("themes/$themeinfo[directory]/templates/config")) {
        LogUtil::registerError(__('Error! Could not create theme configuration directory.'));
        return pnRedirect(pnModURL('Theme', 'admin', 'view'));
    }

    // initialise the globals used in the upgrade
    $GLOBALS['palettes'] = $GLOBALS['variables'] = $GLOBALS['templates'] = $GLOBALS['pageconfigurations'] = array();

    // load the xanthia 2.0 init script
    pnModAPILoad('Theme', 'upgrade');
    Loader::requireOnce("themes/$themeinfo[directory]/xaninit.php");
    $currentlang = ZLanguage::getLanguageCodeLegacy();
    if (file_exists($file = "themes/$themeinfo[directory]/lang/$currentlang/xaninit.php")) {
        Loader::requireOnce($file);
    }
    if (function_exists('xanthia_skins_install')) {
        xanthia_skins_install(array('id' => $themeinfo['name']));
    }

    // write the upgraded version file
    pnModAPIFunc('Theme', 'upgrade', 'writeversion', array('themename' => $themename));

    // write the upgraded palettes file
    pnModAPIFunc('Theme', 'upgrade', 'writepalettes', array('themename' => $themename));

    // write the upgraded palettes file
    pnModAPIFunc('Theme', 'upgrade', 'writevariables', array('themename' => $themename));

    // write the upgraded page configurations file
    pnModAPIFunc('Theme', 'upgrade', 'writepageconfigurations', array('themename' => $themename));
    foreach($GLOBALS['pageconfigurations'] as $pageconfiguration) {
        pnModAPIFunc('Theme', 'upgrade', 'writepageconfiguration', array('themename' => $themename, 'pageconfiguration' => $pageconfiguration));
    }

    // write the upgraded news templates file
    pnModAPIFunc('Theme', 'upgrade', 'rewritenewstemplates', array('themename' => $themename));

    // delete a module var that will have been created
    pnModDelVar('Xanthia', $themename.'newzone');

    // delete old files
    $files = array("themes/$themeinfo[directory]/xaninit.php", "themes/$themeinfo[directory]/theme.php",
                   "themes/$themeinfo[directory]/xaninfo.php", "themes/$themeinfo[directory]/lang/$currentlang/xaninit.php");
    foreach ($files as $file) {
        unlink($file);
    }

    // now regenerate the theme list to detect the change in type
    pnModAPIFunc('Theme', 'admin', 'regenerate');

    LogUtil::registerStatus(__('Done! Upgraded theme.'));
    return pnRedirect(pnModURL('Theme', 'admin', 'view'));
}

/**
 * delete a theme
 *
 */
function theme_admin_delete($args)
{
     $themename = FormUtil::getPassedValue('themename', isset($args['themename']) ? $args['themename'] : null, 'REQUEST');
     $objectid = FormUtil::getPassedValue('objectid', isset($args['objectid']) ? $args['objectid'] : null, 'REQUEST');
     $confirmation = FormUtil::getPassedValue('confirmation', null, 'POST');
     if (!empty($objectid)) {
         $mid = $objectid;
     }

    // Get the theme info
    $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($themename));

    if ($themeinfo == false) {
        return LogUtil::registerError(__('Sorry! No such theme found.'), 404);
    }

    // Security check
    if (!SecurityUtil::checkPermission('Theme::', "$themename::", ACCESS_DELETE)) {
        return LogUtil::registerPermissionError();
    }

    // Check for confirmation.
    if (empty($confirmation)) {
        // No confirmation yet
        // Create output object
        $pnRender = Renderer::getInstance('Theme', false);

        // Add the message id
        $pnRender->assign($themeinfo);

        // Return the output that has been generated by this function
        return $pnRender->fetch('theme_admin_delete.htm');
    }

    // If we get here it means that the user has confirmed the action

    // Confirm authorisation code
    if (!SecurityUtil::confirmAuthKey()) {
        return LogUtil::registerAuthidError(pnModURL('Theme','admin','view'));
    }
    $deletefiles = FormUtil::getPassedValue('deletefiles', 0, 'POST');

    // Delete the admin message
    // The return value of the function is checked
    if (pnModAPIFunc('Theme', 'admin', 'delete', array('themename' => $themename, 'deletefiles' => $deletefiles))) {
        // Success
        LogUtil::registerStatus(__('Done! Deleted it.'));
    }

    // This function generated no output, and so now it is complete we redirect
    // the user to an appropriate page for them to carry on their work
    return pnRedirect(pnModURL('Theme', 'admin', 'view'));
}

/**
 * modify theme settings
 *
 */
function theme_admin_modifyconfig()
{
    // Security check
    if (!SecurityUtil::checkPermission('Theme::', '::', ACCESS_EDIT)) {
        return LogUtil::registerPermissionError();
    }

    // Create output object
    $pnRender = Renderer::getInstance('Theme', false);

    // assign all module vars
    $pnRender->assign(pnModGetVar('Theme'));

    // assign an authid for the clear cache/compile links
    $pnRender->assign('authid', SecurityUtil::generateAuthKey('Theme'));

    // assign the core config var
    $pnRender->assign('theme_change', pnConfigGetVar('theme_change'));

    // assign a list of modules suitable for html_options
    $usermods = pnModGetUserMods();
    $mods = array();
    foreach ($usermods as $usermod) {
        $mods[$usermod['name']] = $usermod['displayname'];
    }
    $pnRender->assign('mods', $mods);

    // assign an extracted list of non-cached mods
    $pnRender->assign('modulesnocache', array_flip(explode(',', pnModGetVar('Theme', 'modulesnocache'))));

    // check for a .htaccess file
    if (file_exists('.htaccess')){
        $pnRender->assign('htaccess', 1);
    } else {
        $pnRender->assign('htaccess', 0);
    }

    // register the pnrender object allow access to various smarty values
    $pnRender->register_object('render', $pnRender);

    // Return the output that has been generated by this function
    return $pnRender->fetch('theme_admin_modifyconfig.htm');
}

/**
 * Update configuration
 *
 */
function theme_admin_updateconfig($args)
{
    // security check
    if (!SecurityUtil::checkPermission('Theme::', '::', ACCESS_EDIT)) {
        return LogUtil::registerPermissionError();
    }

    // Confirm authorisation code
    if (!SecurityUtil::confirmAuthKey()) {
        return LogUtil::registerAuthidError(pnModURL('Theme','admin','view'));
    }

    // set our module variables
    $modulesnocache = FormUtil::getPassedValue('modulesnocache', isset($args['modulesnocache']) ? $args['modulesnocache'] : array(), 'POST');
    $modulesnocache = implode(',', $modulesnocache);
    pnModSetVar('Theme', 'modulesnocache', $modulesnocache);

    $enablecache = (bool)FormUtil::getPassedValue('enablecache', isset($args['enablecache']) ? $args['enablecache'] : false, 'POST');
    pnModSetVar('Theme', 'enablecache', $enablecache);

    $compile_check = (bool)FormUtil::getPassedValue('compile_check', isset($args['compile_check']) ? $args['compile_check'] : false, 'POST');
    pnModSetVar('Theme', 'compile_check', $compile_check);

    $cache_lifetime = (int)FormUtil::getPassedValue('cache_lifetime', isset($args['cache_lifetime']) ? $args['cache_lifetime'] : 3600, 'POST');
    if ($cache_lifetime < -1) $cache_lifetime = 3600;
    pnModSetVar('Theme', 'cache_lifetime', $cache_lifetime);

    $force_compile = (bool)FormUtil::getPassedValue('force_compile', isset($args['force_compile']) ? $args['force_compile'] : false, 'POST');
    pnModSetVar('Theme', 'force_compile', $force_compile);

    $trimwhitespace = (bool)FormUtil::getPassedValue('trimwhitespace', isset($args['trimwhitespace']) ? $args['trimwhitespace'] : false, 'POST');
    pnModSetVar('Theme', 'trimwhitespace', $trimwhitespace);

    $maxsizeforlinks = (int)FormUtil::getPassedValue('maxsizeforlinks', isset($args['maxsizeforlinks']) ? $args['maxsizeforlinks'] : 30, 'POST');
    pnModSetVar('Theme', 'maxsizeforlinks', $maxsizeforlinks);

    $theme_change = (bool)FormUtil::getPassedValue('theme_change', isset($args['theme_change']) ? $args['theme_change'] : false, 'POST');
    pnConfigSetVar('theme_change', $theme_change);

    $itemsperpage = (int)FormUtil::getPassedValue('itemsperpage', isset($args['itemsperpage']) ? $args['itemsperpage'] : 25, 'POST');
    if ($itemsperpage < 1) $itemsperpage = 25;
    pnModSetVar('Theme', 'itemsperpage', $itemsperpage);

    $cssjscombine = (bool)FormUtil::getPassedValue('cssjscombine', isset($args['cssjscombine']) ? $args['cssjscombine'] : false, 'POST');
    pnModSetVar('Theme', 'cssjscombine', $cssjscombine);

    $cssjsminify = (bool)FormUtil::getPassedValue('cssjsminify', isset($args['cssjsminify']) ? $args['cssjsminify'] : false, 'POST');
    pnModSetVar('Theme', 'cssjsminify', $cssjsminify);

    $cssjscompress = (bool)FormUtil::getPassedValue('cssjscompress', isset($args['cssjscompress']) ? $args['cssjscompress'] : false, 'POST');
    pnModSetVar('Theme', 'cssjscompress', $cssjscompress);

    $cssjscombine_lifetime = (int)FormUtil::getPassedValue('cssjscombine_lifetime', isset($args['cssjscombine_lifetime']) ? $args['cssjscombine_lifetime'] : 3600, 'POST');
    if ($cssjscombine_lifetime < -1) $cssjscombine_lifetime = 3600;
    pnModSetVar('Theme', 'cssjscombine_lifetime', $cssjscombine_lifetime);


    // render
    $render_compile_check = (bool)FormUtil::getPassedValue('render_compile_check', isset($args['render_compile_check']) ? $args['render_compile_check'] : false, 'POST');
    pnModSetVar('Theme', 'render_compile_check', $render_compile_check);

    $render_force_compile = (bool)FormUtil::getPassedValue('render_force_compile', isset($args['render_force_compile']) ? $args['render_force_compile'] : false, 'POST');
    pnModSetVar('Theme', 'render_force_compile', $render_force_compile);

    $render_cache = (bool)FormUtil::getPassedValue('render_cache', isset($args['render_cache']) ? $args['render_cache'] : false, 'POST');
    pnModSetVar('Theme', 'render_cache', $render_cache);

    $render_lifetime = (int)FormUtil::getPassedValue('render_lifetime', isset($args['render_lifetime']) ? $args['render_lifetime'] : 3600, 'POST');
    if ($render_lifetime < -1) $render_lifetime = 3600;


    // The configuration has been changed, so we clear all caches for this module.
    $pnRender = Renderer::getInstance('Theme', false);
    $pnRender->clear_all_cache();

    // the module configuration has been updated successfuly
    LogUtil::registerStatus(__('Done! Saved module configuration.'));

    return pnRedirect(pnModURL('Theme', 'admin', 'modifyconfig'));
}

/**
 * Clear theme engine compiled templates
 *
 * Using this function, the admin can clear all theme engine compiled
 * templates for the system.
 */
function theme_admin_clear_compiled()
{
    // Security check
    if (!SecurityUtil::checkPermission('Theme::', '::', ACCESS_ADMIN)) {
        return LogUtil::registerPermissionError();
    }

    // Confirm authorisation code
    if (!SecurityUtil::confirmAuthKey()) {
        return LogUtil::registerAuthidError(pnModURL('Theme','admin','view'));
    }

    $Theme = Theme::getInstance('Theme');
    $res   = $Theme->clear_compiled();

    if ($res) {
        LogUtil::registerStatus(__('Done! Deleted theme engine compiled templates.'));
    } else {
        LogUtil::registerError(__('Error: Failed to clear theme engine compiled templates.'));
    }

    return pnRedirect(pnModURL('Theme', 'admin', 'modifyconfig'));
}

/**
 * Clear theme engine cached templates
 *
 * Using this function, the admin can clear all theme engine cached
 * templates for the system.
 */
function theme_admin_clear_cache()
{
    // Security check
    if (!SecurityUtil::checkPermission('Theme::', '::', ACCESS_ADMIN)) {
        return LogUtil::registerPermissionError();
    }

    // Confirm authorisation code
    if (!SecurityUtil::confirmAuthKey()) {
        return LogUtil::registerAuthidError(pnModURL('Theme','admin','main'));
    }

    $Theme = Theme::getInstance('Theme');
    $res   = $Theme->clear_all_cache();

    if ($res) {
        LogUtil::registerStatus(__('Done! Deleted theme engine cached templates.'));
    } else {
        LogUtil::registerError(__('Error: Failed to clear theme engine cached templates.'));
    }

    return pnRedirect(pnModURL('Theme', 'admin', 'modifyconfig'));
}

/**
 * Clear CSS/JS combination cached files
 *
 * Using this function, the admin can clear all CSS/JS combination cached
 * files for the system.
 */
function theme_admin_clear_cssjscombinecache()
{
    // Security check
    if (!SecurityUtil::checkPermission('Theme::', '::', ACCESS_ADMIN)) {
        return LogUtil::registerPermissionError();
    }

    // Confirm authorisation code
    if (!SecurityUtil::confirmAuthKey()) {
        return LogUtil::registerAuthidError(pnModURL('Theme','admin','view'));
    }

    $Theme = Theme::getInstance('Theme');
    $Theme->clear_cssjscombinecache();

    LogUtil::registerStatus(__('Done! Deleted CSS/JS combination cached files.'));
    return pnRedirect(pnModURL('Theme', 'admin', 'modifyconfig'));
}

/**
 * Clear render compiled templates
 *
 * Using this function, the admin can clear all render compiled templates
 * for the system.
 */
function theme_admin_render_clear_compiled()
{
    // Security check
    if (!SecurityUtil::checkPermission('Theme::', '::', ACCESS_ADMIN)) {
        return LogUtil::registerPermissionError();
    }

    // Confirm authorisation code
    if (!SecurityUtil::confirmAuthKey()) {
        return LogUtil::registerAuthidError(pnModURL('Theme','admin','view'));
    }

    $Renderer = Renderer::getInstance();
    $res      = $Renderer->clear_compiled();

    if ($res) {
        LogUtil::registerStatus(__('Done! Deleted rendering engine compiled templates.'));
    } else {
        LogUtil::registerError(__('Error: Failed to clear rendering engine compiled templates.'));
    }

    return pnRedirect(pnModURL('Theme', 'admin', 'modifyconfig'));
}

/**
 * Clear render cached templates
 *
 * Using this function, the admin can clear all render cached templates
 * for the system.
 */
function theme_admin_render_clear_cache()
{
    // Security check
    if (!SecurityUtil::checkPermission('Theme::', '::', ACCESS_ADMIN)) {
        return LogUtil::registerPermissionError();
    }

    // Confirm authorisation code
    if (!SecurityUtil::confirmAuthKey()) {
        return LogUtil::registerAuthidError(pnModURL('Theme','admin','view'));
    }

    $Renderer = Renderer::getInstance();
    $res      = $Renderer->clear_all_cache();

    if ($res) {
        LogUtil::registerStatus(__('Done! Deleted rendering engine cached pages.'));
    } else {
        LogUtil::registerError(__('Error: Failed to clear rendering engine cached pages.'));
    }

    return pnRedirect(pnModURL('Theme', 'admin', 'modifyconfig'));
}
