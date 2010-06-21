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

class Theme_Admin extends Zikula_Controller
{
    /**
     * the main admin function
     */
    public function main()
    {
        // Security check will be done in view()
        return $this->view();
    }

    /**
     * display form to create a theme
     */
    public function newtheme()
    {
        // Security check
        if (!SecurityUtil::checkPermission('Theme::', '::', ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }

        $this->renderer->setCaching(false);

        // Return the output that has been generated to the template
        return $this->renderer->fetch('theme_admin_newtheme.htm');
    }

    /**
     * create the theme
     */
    public function create($args)
    {
        // Security check
        if (!SecurityUtil::checkPermission('Theme::', '::', ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }

        // get our input
        $themeinfo = FormUtil::getPassedValue('themeinfo', isset($args['themeinfo']) ? $args['themeinfo'] : null, 'POST');

        // check our input
        if (!isset($themeinfo) || !isset($themeinfo['name']) || empty($themeinfo) || empty($themeinfo['name'])) {
            $url = ModUtil::url('Theme', 'admin', 'new');
            return LogUtil::registerError($this->__("Error! You must enter at least the theme name."), null, $url);
        }

        // Security check
        if (!SecurityUtil::checkPermission('Theme::', "$themeinfo[name]::", ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }

        // create theme
        if (!ModUtil::apiFunc('Theme', 'admin', 'create', array('themeinfo' => $themeinfo))) {
            LogUtil::registerStatus($this->__f('Done! Theme %s created.', $themeinfo['name']));
        }

        // regenerate theme list
        ModUtil::apiFunc('Theme', 'admin', 'regenerate');

        // redirect back to the variables page
        return System::redirect(ModUtil::url('Theme', 'admin', 'view'));
    }

    /**
     * view all themes
     */
    public function view($args = array())
    {
        // Security check
        if (!SecurityUtil::checkPermission('Theme::', '::', ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }

        $this->renderer->setCaching(false);
        
        if(isset($GLOBALS['ZConfig']['multisites']['multi']) && $GLOBALS['ZConfig']['multisites']['multi'] == 1){
            // only the main site can regenerate the themes list
            if($GLOBALS['ZConfig']['multisites']['mainSiteURL'] == FormUtil::getPassedValue('siteDNS', null, 'GET')){
                //return true but any action has been made
                ModUtil::apiFunc('Theme', 'admin', 'regenerate');
            }
        } else {
            ModUtil::apiFunc('Theme', 'admin', 'regenerate');
        }

        // get our input
        $startnum = FormUtil::getPassedValue('startnum', isset($args['startnum']) ? $args['startnum'] : 1, 'GET');
        $startlet = FormUtil::getPassedValue('startlet', isset($args['startlet']) ? $args['startlet'] : null, 'GET');

        // we need this value multiple times, so we keep it
        $itemsperpage = ModUtil::getVar('Theme', 'itemsperpage');

        // call the API to get a list of all themes in the themes dir
        $allthemes = ThemeUtil::getAllThemes(ThemeUtil::FILTER_ALL, ThemeUtil::STATE_ALL);

        // filter by letter if required
        if (isset($startlet) && !empty($startlet)) {
            $allthemes = array_filter($allthemes, '$this->_filterbyletter');
        }
        $themes = array_slice($allthemes, $startnum-1, $itemsperpage);
        $this->renderer->assign('themes', $themes);

        // assign default theme
        $this->renderer->assign('currenttheme', System::getVar('Default_Theme'));

        // assign the values for the smarty plugin to produce a pager
        $this->renderer->assign('pager', array('numitems' => sizeof($allthemes),
                                               'itemsperpage' => $itemsperpage));

        // Return the output that has been generated to the template
        return $this->renderer->fetch('theme_admin_view.htm');
    }

    /**
     * filter theme array by letter
     *
     * @access private
     */
    private function _filterbyletter($theme)
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
    public function modify($args)
    {
        // get our input
        $themename = FormUtil::getPassedValue('themename', isset($args['themename']) ? $args['themename'] : null, 'GET');

        // check our input
        if (!isset($themename) || empty($themename)) {
            return LogUtil::registerArgsError(ModUtil::url('Theme', 'admin', 'view'));
        }

        // Security check
        if (!SecurityUtil::checkPermission('Theme::', "$themename::", ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }

        // get the theme info
        $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($themename));

        // check if we can edit the theme and, if not, create the running config
        if (!is_writable('themes/' . DataUtil::formatForOS($themeinfo['directory']) . '/templates/config') && $themeinfo['type'] == ThemeUtil::TYPE_XANTHIA3) {
            ModUtil::apiFunc('Theme', 'admin', 'createrunningconfig', array('themename' => $themename));
        }

        $this->renderer->setCaching(false);
        
        // assign theme name, theme info and return output
        return $this->renderer->assign('themename', $themename)
                              ->assign('themeinfo', $themeinfo)
                              ->fetch('theme_admin_modify.htm');
    }

    /**
     * update the theme variables
     *
     */
    public function updatesettings($args)
    {
        // get our input
        $themeinfo = FormUtil::getPassedValue('themeinfo', isset($args['themeinfo']) ? $args['themeinfo'] : null, 'POST');
        $themename = FormUtil::getPassedValue('themename', isset($args['themename']) ? $args['themename'] : null, 'POST');

        // check our input
        if (!isset($themename) || empty($themename)) {
            LogUtil::registerArgsError();
            return System::redirect(ModUtil::url('Theme', 'admin', 'view'));
        }

        // Security check
        if (!SecurityUtil::checkPermission('Theme::', "$themename::settings", ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }

        // Confirm authorisation code
        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError(ModUtil::url('Theme','admin','view'));
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
        if (ModUtil::apiFunc('Theme', 'admin', 'updatesettings', array('theme' => $themename, 'themeinfo' => $newthemeinfo))) {
            LogUtil::registerStatus($this->__('Done! Saved module configuration.'));
        }

        // redirect back to the variables page
        return System::redirect(ModUtil::url('Theme', 'admin', 'view'));
    }

    /**
     * display the theme variables
     *
     */
    public function variables($args)
    {
        // get our input
        $themename = FormUtil::getPassedValue('themename', isset($args['themename']) ? $args['themename'] : null, 'GET');

        // check our input
        if (!isset($themename) || empty($themename)) {
            return LogUtil::registerArgsError(ModUtil::url('Theme', 'admin', 'view'));
        }

        $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($themename));
        if (!file_exists('themes/'.DataUtil::formatForOS($themeinfo['directory']).'/version.php')) {
            return LogUtil::registerArgsError(ModUtil::url('Theme', 'admin', 'view'));
        }

        // Security check
        if (!SecurityUtil::checkPermission('Theme::', "$themename::variables", ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }

        // load the language file
        ZLanguage::bindThemeDomain($themename);

        $this->renderer->setCaching(false);
        
        // assign variables, themename, themeinfo and return output
        return $this->renderer->assign('variables', ModUtil::apiFunc('Theme', 'user', 'getvariables', array('theme' => $themename, 'formatting' => true)))
                              ->assign('themename', $themename)
                              ->assign('themeinfo', $themeinfo)
                              ->fetch('theme_admin_variables.htm');
    }

    /**
     * update the theme variables
     *
     */
    public function updatevariables($args)
    {
        // get our input
        $variablesnames = FormUtil::getPassedValue('variablesnames', isset($args['variablesnames']) ? $args['variablesnames'] : null, 'POST');
        $variablesvalues = FormUtil::getPassedValue('variablesvalues', isset($args['variablesvalues']) ? $args['variablesvalues'] : null, 'POST');
        $newvariablename = FormUtil::getPassedValue('newvariablename', isset($args['newvariablename']) ? $args['newvariablename'] : null, 'POST');
        $newvariablevalue = FormUtil::getPassedValue('newvariablevalue', isset($args['newvariablevalue']) ? $args['newvariablevalue'] : null, 'POST');
        $themename = FormUtil::getPassedValue('themename', isset($args['themename']) ? $args['themename'] : null, 'POST');

        // check our input
        if (!isset($themename) || empty($themename)) {
            return LogUtil::registerArgsError(ModUtil::url('Theme', 'admin', 'view'));
        }

        $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($themename));
        if (!file_exists('themes/'.DataUtil::formatForOS($themeinfo['directory']).'/version.php')) {
            return LogUtil::registerArgsError(ModUtil::url('Theme', 'admin', 'view'));
        }

        // Security check
        if (!SecurityUtil::checkPermission('Theme::', "$themename::variables", ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }

        // Confirm authorisation code
        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError(ModUtil::url('Theme','admin','view'));
        }

        // get the original file source
        $variables = ModUtil::apiFunc('Theme', 'user', 'getvariables', array('theme' => $themename, 'formatting' => true, 'explode' => false));

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
        ModUtil::apiFunc('Theme', 'user', 'writeinifile', array('theme' => $themename, 'assoc_arr' => $variables, 'has_sections' => true, 'file' => 'themevariables.ini'));

        // set a status message
        LogUtil::registerStatus($this->__('Done! Saved your changes.'));

        // redirect back to the variables page
        return System::redirect(ModUtil::url('Theme', 'admin', 'variables', array('themename' => $themename)));
    }

    /**
     * display the themes block positions
     *
     */
    public function palettes($args)
    {
        // get our input
        $themename = FormUtil::getPassedValue('themename', isset($args['themename']) ? $args['themename'] : null, 'GET');

        // check our input
        if (!isset($themename) || empty($themename)) {
            return LogUtil::registerArgsError(ModUtil::url('Theme', 'admin', 'view'));
        }

        $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($themename));
        if (!file_exists('themes/'.DataUtil::formatForOS($themeinfo['directory']).'/version.php')) {
            return LogUtil::registerArgsError(ModUtil::url('Theme', 'admin', 'view'));
        }

        // Security check
        if (!SecurityUtil::checkPermission('Theme::', "$themename::colors", ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }

        $this->renderer->setCaching(false);

        // assign palettes, themename, themeinfo and return output
        return $this->renderer->assign('palettes', ModUtil::apiFunc('Theme', 'user', 'getpalettes', array('theme' => $themename)))
                              ->assign('themename', $themename)
                              ->assign('themeinfo', $themeinfo)
                              ->fetch('theme_admin_palettes.htm');
    }

    /**
     * update the theme settings
     *
     */
    public function updatepalettes($args)
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
            return LogUtil::registerArgsError(ModUtil::url('Theme', 'admin', 'view'));
        }

        // Security check
        if (!SecurityUtil::checkPermission('Theme::', "$themename::palettes", ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }

        // Confirm authorisation code
        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError(ModUtil::url('Theme','admin','view'));
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
            LogUtil::registerError($this->__('Notice: Please make sure you type an entry in every field. Your palette cannot be saved if you do not.'));
            return System::redirect(ModUtil::url('Theme', 'admin', 'view'));
        }

        // rewrite the settings to the running config
        ModUtil::apiFunc('Theme', 'user', 'writeinifile', array('theme' => $themename, 'assoc_arr' => $palettes, 'has_sections' => true, 'file' => 'themepalettes.ini'));

        // set a status message
        LogUtil::registerStatus($this->__('Done! Saved your changes.'));

        // redirect back to the settings page
        return System::redirect(ModUtil::url('Theme', 'admin', 'palettes', array('themename' => $themename)));
    }

    /**
     * display the content wrappers for the theme
     *
     */
    public function pageconfigurations($args)
    {
        // get our input
        $themename = FormUtil::getPassedValue('themename', isset($args['themename']) ? $args['themename'] : null, 'GET');

        // check our input
        if (!isset($themename) || empty($themename)) {
            return LogUtil::registerArgsError(ModUtil::url('Theme', 'admin', 'view'));
        }

        $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($themename));
        if (!file_exists('themes/'.DataUtil::formatForOS($themeinfo['directory']).'/version.php')) {
            return LogUtil::registerArgsError(ModUtil::url('Theme', 'admin', 'view'));
        }

        // Security check
        if (!SecurityUtil::checkPermission('Theme::', "$themename::pageconfigurations", ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }

        $this->renderer->setCaching(false);
        
        // assign the theme name and theme info
        $this->renderer->assign('themename', $themename)
                       ->assign('themeinfo', $themeinfo);

        // assign an array to populate the modules dropdown
        $allmods = ModUtil::getAllMods();
        $mods = array();
        foreach ($allmods as $mod) {
            $mods[$mod['name']] = $mod['displayname'];
        }
        $this->renderer->assign('modules', $mods);

        // assign the page configuration assignments
        $pageconfigurations = ModUtil::apiFunc('Theme', 'user', 'getpageconfigurations', array('theme' => $themename));
        ksort($pageconfigurations);
        $this->renderer->assign('pageconfigurations', $pageconfigurations);

        // identify unique page configuration files
        $pageconfigfiles = array();
        foreach ($pageconfigurations as $pageconfiguration) {
            $pageconfigfiles[$pageconfiguration['file']] = file_exists("themes/$themeinfo[directory]/templates/config/$pageconfiguration[file]");
        }
        $this->renderer->assign('pageconfigs', $pageconfigfiles);

        // Return the output that has been generated by this function
        return $this->renderer->fetch('theme_admin_pageconfigurations.htm');
    }

    /**
     * modify a theme page configuration
     *
     */
    public function modifypageconfigtemplates($args)
    {
        // get our input
        $themename = FormUtil::getPassedValue('themename', isset($args['themename']) ? $args['themename'] : null, 'GET');
        $filename = FormUtil::getPassedValue('filename', isset($args['filename']) ? $args['filename'] : null, 'GET');

        // check our input
        if (!isset($themename) || empty($themename)) {
            return LogUtil::registerArgsError(ModUtil::url('Theme', 'admin', 'view'));
        }

        $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($themename));
        if (!file_exists('themes/'.DataUtil::formatForOS($themeinfo['directory']).'/version.php')) {
            return LogUtil::registerArgsError(ModUtil::url('Theme', 'admin', 'view'));
        }

        // Security check
        if (!SecurityUtil::checkPermission('Theme::', "$themename::pageconfigurations", ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }

        // read our configuration file
        $pageconfiguration = ModUtil::apiFunc('Theme', 'user', 'getpageconfiguration', array('theme' => $themename, 'filename' => $filename));
        if (!isset($pageconfiguration) || empty($pageconfiguration)) {
            return LogUtil::registerArgsError(ModUtil::url('Theme', 'admin', 'view'));
        }

        $this->renderer->setCaching(false);
        
        // assign the base filename, themename, theme info, moduletemplates, blocktemplates and palettes
        $this->renderer->assign('filename', $filename)
                       ->assign('themename', $themename)
                       ->assign('themeinfo', $themeinfo)
                       ->assign('moduletemplates', ModUtil::apiFunc('Theme', 'user', 'gettemplates', array('theme' => $themename)))
                       ->assign('blocktemplates', ModUtil::apiFunc('Theme', 'user', 'gettemplates', array('theme' => $themename, 'type' => 'blocks')))
                       ->assign('palettes', ModUtil::apiFunc('Theme', 'user', 'getpalettenames', array('theme' => $themename)));

        // get all block positions
        $blockpositions = ModUtil::apiFunc('Blocks', 'user', 'getallpositions');
        $positions = array();
        foreach ($blockpositions as $blockposition) {
            // check the page configuration
            if (!isset($pageconfiguration['blockpositions'][$blockposition['name']])) {
                $pageconfiguration['blockpositions'][$blockposition['name']] = '';
            }
            $positions[$blockposition['name']] = $blockposition['name'];
        }
        $this->renderer->assign('blockpositions', $positions);

        // call the block API to get a list of all available blocks
        $this->renderer->assign('allblocks', $allblocks = BlockUtil::loadAll());
        foreach ($allblocks as $key => $blocks) {
            foreach ($blocks as $block) {
                // check the page configuration
                if (!isset($pageconfiguration['blocktypes'][$block['bkey']])) {
                    $pageconfiguration['blocktypes'][$block['bkey']] = '';
                }
            }
        }

        // call the block API to get a list of all defined block instances
        $this->renderer->assign('blocks', $blocks = ModUtil::apiFunc('Blocks', 'user', 'getall'));
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
        $this->renderer->assign('pageconfiguration', $pageconfiguration);

        // Return the output that has been generated by this function
        return $this->renderer->fetch('theme_admin_modifypageconfigtemplates.htm');
    }

    /**
     * modify a theme page configuration
     *
     */
    public function updatepageconfigtemplates($args)
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
            return LogUtil::registerArgsError(ModUtil::url('Theme', 'admin', 'view'));
        }

        $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($themename));
        if (!file_exists('themes/'.DataUtil::formatForOS($themeinfo['directory']).'/version.php')) {
            return LogUtil::registerArgsError(ModUtil::url('Theme', 'admin', 'view'));
        }

        // Security check
        if (!SecurityUtil::checkPermission('Theme::', "$themename::pageconfigurations", ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }

        // Confirm authorisation code
        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError(ModUtil::url('Theme','admin','view'));
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
        $filters['outputfilters'] = $this->_checkfilters('outputfilter', $filters['outputfilters']);
        $filters['prefilters']    = $this->_checkfilters('prefilter', $filters['prefilters']);
        $filters['postfilters']   = $this->_checkfilters('postfilter', $filters['postfilters']);
        $pageconfiguration['filters'] = $filters;

        // write the page configuration
        ModUtil::apiFunc('Theme', 'user', 'writeinifile', array('theme' => $themename, 'assoc_arr' => $pageconfiguration, 'has_sections' => true, 'file' => $filename));

        // set a status message
        LogUtil::registerStatus($this->__('Done! Saved your changes.'));

        // return the user to the correct place
        return System::redirect(ModUtil::url('Theme', 'admin', 'pageconfigurations', array('themename' => $themename)));
    }

    /**
     * check if the given filter exists
     *
     */
    private function _checkfilters($type, $filters)
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
            LogUtil::registerError($this->__f('Error! Removed unknown \'%1$s\': \'%2$s\'.', array(DataUtil::formatForDisplay($type), DataUtil::formatForDisplay(implode(',', $leftover)))));
        }
        return implode(',', $newfilters);
    }

    /**
     * modify a theme page configuration
     *
     */
    public function modifypageconfigurationassignment($args)
    {
        // get our input
        $themename = FormUtil::getPassedValue('themename', isset($args['themename']) ? $args['themename'] : null, 'GET');
        $pcname = FormUtil::getPassedValue('pcname', isset($args['pcname']) ? $args['pcname'] : null, 'GET');

        // check our input
        if (!isset($themename) || empty($themename)) {
            return LogUtil::registerArgsError(ModUtil::url('Theme', 'admin', 'view'));
        }

        $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($themename));
        if (!file_exists('themes/'.DataUtil::formatForOS($themeinfo['directory']).'/version.php')) {
            return LogUtil::registerArgsError(ModUtil::url('Theme', 'admin', 'view'));
        }

        // Security check
        if (!SecurityUtil::checkPermission('Theme::', "$themename::pageconfigurations", ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }

        $this->renderer->setCaching(false);
        
        // assign the page config assignment name, theme name and theme info
        $this->renderer->assign('pcname', $pcname)
                       ->assign('themename', $themename)
                       ->assign('themeinfo', $themeinfo);

        // assign all modules
        $allmods = ModUtil::getAllMods();
        $mods = array();
        foreach ($allmods as $mod) {
            $mods[$mod['name']] = $mod['name'];
        }
        $this->renderer->assign('modules', $mods);

        // get all pageconfigurations
        $pageconfigurations = ModUtil::apiFunc('Theme', 'user', 'getpageconfigurations', array('theme' => $themename));
        if (!isset($pageconfigurations[$pcname])) {
            LogUtil::registerError($this->__('Error! No such page configuration assignment found.'));
            return System::redirect(ModUtil::url('Theme', 'admin', 'view'));
        }
        $pageconfigparts = explode('/', $pcname);

        // assign the config filename
        $this->renderer->assign('filename', $pageconfigurations[$pcname]['file']);

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
        $this->renderer->assign($pageconfigassignment);

        // Return the output that has been generated by this function
        return $this->renderer->fetch('theme_admin_modifypageconfigurationassignment.htm');
    }

    /**
     * modify a theme page configuration
     *
     */
    public function updatepageconfigurationassignment($args)
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
            return LogUtil::registerArgsError(ModUtil::url('Theme', 'admin', 'view'));
        }

        $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($themename));
        if (!file_exists('themes/'.DataUtil::formatForOS($themeinfo['directory']).'/version.php')) {
            return LogUtil::registerArgsError(ModUtil::url('Theme', 'admin', 'view'));
        }

        // Security check
        if (!SecurityUtil::checkPermission('Theme::', "$themename::pageconfigurations", ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }

        // Confirm authorisation code
        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError(ModUtil::url('Theme','admin','view'));
        }

        // read the list of existing page config assignments
        $pageconfigurations = ModUtil::apiFunc('Theme', 'user', 'getpageconfigurations', array('theme' => $themename));

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
        ModUtil::apiFunc('Theme', 'user', 'writeinifile', array('theme' => $themename, 'assoc_arr' => $pageconfigurations, 'has_sections' => true, 'file' => 'pageconfigurations.ini'));

        // set a status message
        LogUtil::registerStatus($this->__('Done! Saved your changes.'));

        // return the user to the correct place
        return System::redirect(ModUtil::url('Theme', 'admin', 'pageconfigurations', array('themename' => $themename)));
    }

    /**
     * delete a theme page configuration assignment
     *
     */
    public function deletepageconfigurationassignment($args)
    {
        $themename = FormUtil::getPassedValue('themename', isset($args['themename']) ? $args['themename'] : null, 'REQUEST');
        $pcname = FormUtil::getPassedValue('pcname', isset($args['pcname']) ? $args['pcname'] : null, 'REQUEST');
        $confirmation = FormUtil::getPassedValue('confirmation', null, 'POST');

        // Get the theme info
        $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($themename));

        if ($themeinfo == false) {
            return LogUtil::registerError($this->__('Sorry! No such item found.'));
        }

        // Security check
        if (!SecurityUtil::checkPermission('Theme::', "$themename::pageconfigurations", ACCESS_DELETE)) {
            return LogUtil::registerPermissionError();
        }

        // Check for confirmation.
        if (empty($confirmation)) {
            // No confirmation yet
            $this->renderer->setCaching(false);

            // Assign the theme info
            $this->renderer->assign($themeinfo);

            // Assign the page configuration name
            $this->renderer->assign('pcname', $pcname);

            // Return the output that has been generated by this function
            return $this->renderer->fetch('theme_admin_deletepageconfigurationassignment.htm');
        }

        // If we get here it means that the user has confirmed the action

        // Confirm authorisation code
        if (!SecurityUtil::confirmAuthKey()) {
            LogUtil::registerAuthidError(ModUtil::url('Theme', 'admin', 'pageconfigurations', array('themename' => $themename)));
        }

        // Delete the admin message
        // The return value of the function is checked
        if (ModUtil::apiFunc('Theme', 'admin', 'deletepageconfigurationassignment', array('themename' => $themename, 'pcname' => $pcname))) {
            // Success
            LogUtil::registerStatus($this->__('Done! Deleted it.'));
        }

        // return the user to the correct place
        return System::redirect(ModUtil::url('Theme', 'admin', 'pageconfigurations', array('themename' => $themename)));
    }

    /**
     * display the theme credits
     *
     *
     */
    public function credits($args)
    {
        // get our input
        $themename = FormUtil::getPassedValue('themename', isset($args['themename']) ? $args['themename'] : null, 'GET');

        // check our input
        if (!isset($themename) || empty($themename)) {
            return LogUtil::registerArgsError(ModUtil::url('Theme', 'admin', 'view'));
        }

        // Security check
        if (!SecurityUtil::checkPermission('Theme::', "$themename::credits", ACCESS_EDIT)) {
            return LogUtil::registerArgsError(ModUtil::url('Theme', 'admin', 'view'));
        }

        $this->renderer->setCaching(false);
        
        // assign the theme info and return output
        return $this->renderer->assign('themeinfo', ThemeUtil::getInfo(ThemeUtil::getIDFromName($themename)))
                              ->fetch('theme_admin_credits.htm');
    }


    /**
     * set theme as default for site
     *
     */
    public function setasdefault($args)
    {
        // get our input
        $themename = FormUtil::getPassedValue('themename', isset($args['themename']) ? $args['themename'] : null, 'REQUEST');
        $confirmation = (int)FormUtil::getPassedValue ('confirmation', false, 'REQUEST');
        $resetuserselected = FormUtil::getPassedValue('resetuserselected', isset($args['resetuserselected']) ? $args['resetuserselected'] : null, 'POST');

        // check our input
        if (!isset($themename) || empty($themename)) {
            return LogUtil::registerArgsError(ModUtil::url('Theme', 'admin', 'view'));
        }

        // Security check
        if (!SecurityUtil::checkPermission('Theme::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        // Check for confirmation.
        if (empty($confirmation)) {
            // No confirmation yet
            $this->renderer->setCaching(false);
            
            // Add a hidden field for the item ID to the output
            $this->renderer->assign('themename', $themename);

            // assign the var defining if users can change themes
            $this->renderer->assign('theme_change', System::getVar('theme_change'));

            // Return the output that has been generated by this function
            return $this->renderer->fetch('theme_admin_setasdefault.htm');
        }

        // If we get here it means that the user has confirmed the action

        // Confirm authorisation code
        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError(ModUtil::url('Theme','admin','view'));
        }

        // Set the default theme
        if (ModUtil::apiFunc('Theme', 'admin', 'setasdefault', array('themename' => $themename, 'resetuserselected' => $resetuserselected))) {
            // Success
            LogUtil::registerStatus($this->__('Done! Changed default theme.'));
        }

        return System::redirect(ModUtil::url('Theme', 'admin', 'view'));

    }

    /**
     * upgrade theme
     *
     */
    public function upgrade($args)
    {
        // get our input
        $themename = FormUtil::getPassedValue('themename', isset($args['themename']) ? $args['themename'] : null, 'REQUEST');

        // get the theme info
        $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($themename));

        // check the permissions required to upgrade the theme
        if (!is_writable("themes/$themeinfo[directory]") || !is_writable("themes/$themeinfo[directory]/templates")) {
            LogUtil::registerError($this->__('Notice: Permissions for the theme directory must be set so that it can be written to.'));
            return System::redirect(ModUtil::url('Theme', 'admin', 'view'));
        }

        if (!file_exists("themes/$themeinfo[directory]/xaninit.php") || $themeinfo['type'] != 2) {
            LogUtil::registerError($this->__("Error! This theme cannot be upgraded because it is not a Xanthia 2 theme."));
            return System::redirect(ModUtil::url('Theme', 'admin', 'view'));
        }

        // make the config directory
        if (!is_dir("themes/$themeinfo[directory]/templates/config") && !mkdir("themes/$themeinfo[directory]/templates/config")) {
            LogUtil::registerError($this->__('Error! Could not create theme configuration directory.'));
            return System::redirect(ModUtil::url('Theme', 'admin', 'view'));
        }

        // initialise the globals used in the upgrade
        $GLOBALS['palettes'] = $GLOBALS['variables'] = $GLOBALS['templates'] = $GLOBALS['pageconfigurations'] = array();

        // load the xanthia 2.0 init script
        ModUtil::loadApi('Theme', 'upgrade');
        require_once "themes/$themeinfo[directory]/xaninit.php";
        $currentlang = ZLanguage::getLanguageCodeLegacy();
        if (file_exists($file = "themes/$themeinfo[directory]/lang/$currentlang/xaninit.php")) {
            require_once $file;
        }
        if (function_exists('xanthia_skins_install')) {
            xanthia_skins_install(array('id' => $themeinfo['name']));
        }

        // write the upgraded version file
        ModUtil::apiFunc('Theme', 'upgrade', 'writeversion', array('themename' => $themename));

        // write the upgraded palettes file
        ModUtil::apiFunc('Theme', 'upgrade', 'writepalettes', array('themename' => $themename));

        // write the upgraded palettes file
        ModUtil::apiFunc('Theme', 'upgrade', 'writevariables', array('themename' => $themename));

        // write the upgraded page configurations file
        ModUtil::apiFunc('Theme', 'upgrade', 'writepageconfigurations', array('themename' => $themename));
        foreach($GLOBALS['pageconfigurations'] as $pageconfiguration) {
            ModUtil::apiFunc('Theme', 'upgrade', 'writepageconfiguration', array('themename' => $themename, 'pageconfiguration' => $pageconfiguration));
        }

        // write the upgraded news templates file
        ModUtil::apiFunc('Theme', 'upgrade', 'rewritenewstemplates', array('themename' => $themename));

        // delete a module var that will have been created
        ModUtil::delVar('Xanthia', $themename.'newzone');

        // delete old files
        $files = array("themes/$themeinfo[directory]/xaninit.php", "themes/$themeinfo[directory]/theme.php",
                "themes/$themeinfo[directory]/xaninfo.php", "themes/$themeinfo[directory]/lang/$currentlang/xaninit.php");
        foreach ($files as $file) {
            unlink($file);
        }

        // now regenerate the theme list to detect the change in type
        ModUtil::apiFunc('Theme', 'admin', 'regenerate');

        LogUtil::registerStatus($this->__('Done! Upgraded theme.'));
        return System::redirect(ModUtil::url('Theme', 'admin', 'view'));
    }

    /**
     * delete a theme
     *
     */
    public function delete($args)
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
            return LogUtil::registerError($this->__('Sorry! No such theme found.'), 404);
        }

        // Security check
        if (!SecurityUtil::checkPermission('Theme::', "$themename::", ACCESS_DELETE)) {
            return LogUtil::registerPermissionError();
        }

        // Check for confirmation.
        if (empty($confirmation)) {
            // No confirmation yet

            $this->renderer->setCaching(false);
            
            // Add the message id
            $this->renderer->assign($themeinfo);

            // Return the output that has been generated by this function
            return $this->renderer->fetch('theme_admin_delete.htm');
        }

        // If we get here it means that the user has confirmed the action

        // Confirm authorisation code
        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError(ModUtil::url('Theme','admin','view'));
        }
        $deletefiles = FormUtil::getPassedValue('deletefiles', 0, 'POST');

        // Delete the admin message
        // The return value of the function is checked
        if (ModUtil::apiFunc('Theme', 'admin', 'delete', array('themename' => $themename, 'deletefiles' => $deletefiles))) {
            // Success
            LogUtil::registerStatus($this->__('Done! Deleted it.'));
        }

        // This function generated no output, and so now it is complete we redirect
        // the user to an appropriate page for them to carry on their work
        return System::redirect(ModUtil::url('Theme', 'admin', 'view'));
    }

    /**
     * modify theme settings
     *
     */
    public function modifyconfig()
    {
        // Security check
        if (!SecurityUtil::checkPermission('Theme::', '::', ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }

        $this->renderer->setCaching(false);

        // assign all module vars
        $this->renderer->assign(ModUtil::getVar('Theme'));

        // assign an authid for the clear cache/compile links
        $this->renderer->assign('authid', SecurityUtil::generateAuthKey('Theme'));

        // assign the core config var
        $this->renderer->assign('theme_change', System::getVar('theme_change'));

        // assign a list of modules suitable for html_options
        $usermods = ModUtil::getUserMods();
        $mods = array();
        foreach ($usermods as $usermod) {
            $mods[$usermod['name']] = $usermod['displayname'];
        }
        $this->renderer->assign('mods', $mods);

        // assign an extracted list of non-cached mods
        $this->renderer->assign('modulesnocache', array_flip(explode(',', ModUtil::getVar('Theme', 'modulesnocache'))));

        // check for a .htaccess file
        if (file_exists('.htaccess')){
            $this->renderer->assign('htaccess', 1);
        } else {
            $this->renderer->assign('htaccess', 0);
        }

        // register the renderer object allow access to various smarty values
        $this->renderer->register_object('render', $this->renderer);

        // Return the output that has been generated by this function
        return $this->renderer->fetch('theme_admin_modifyconfig.htm');
    }

    /**
     * Update configuration
     *
     */
    public function updateconfig($args)
    {
        // security check
        if (!SecurityUtil::checkPermission('Theme::', '::', ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }

        // Confirm authorisation code
        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError(ModUtil::url('Theme','admin','view'));
        }

        // set our module variables
        $modulesnocache = FormUtil::getPassedValue('modulesnocache', isset($args['modulesnocache']) ? $args['modulesnocache'] : array(), 'POST');
        $modulesnocache = implode(',', $modulesnocache);
        ModUtil::setVar('Theme', 'modulesnocache', $modulesnocache);

        $enablecache = (bool)FormUtil::getPassedValue('enablecache', isset($args['enablecache']) ? $args['enablecache'] : false, 'POST');
        ModUtil::setVar('Theme', 'enablecache', $enablecache);

        $compile_check = (bool)FormUtil::getPassedValue('compile_check', isset($args['compile_check']) ? $args['compile_check'] : false, 'POST');
        ModUtil::setVar('Theme', 'compile_check', $compile_check);

        $cache_lifetime = (int)FormUtil::getPassedValue('cache_lifetime', isset($args['cache_lifetime']) ? $args['cache_lifetime'] : 3600, 'POST');
        if ($cache_lifetime < -1) $cache_lifetime = 3600;
        ModUtil::setVar('Theme', 'cache_lifetime', $cache_lifetime);

        $force_compile = (bool)FormUtil::getPassedValue('force_compile', isset($args['force_compile']) ? $args['force_compile'] : false, 'POST');
        ModUtil::setVar('Theme', 'force_compile', $force_compile);

        $trimwhitespace = (bool)FormUtil::getPassedValue('trimwhitespace', isset($args['trimwhitespace']) ? $args['trimwhitespace'] : false, 'POST');
        ModUtil::setVar('Theme', 'trimwhitespace', $trimwhitespace);

        $maxsizeforlinks = (int)FormUtil::getPassedValue('maxsizeforlinks', isset($args['maxsizeforlinks']) ? $args['maxsizeforlinks'] : 30, 'POST');
        ModUtil::setVar('Theme', 'maxsizeforlinks', $maxsizeforlinks);

        $theme_change = (bool)FormUtil::getPassedValue('theme_change', isset($args['theme_change']) ? $args['theme_change'] : false, 'POST');
        System::setVar('theme_change', $theme_change);

        $itemsperpage = (int)FormUtil::getPassedValue('itemsperpage', isset($args['itemsperpage']) ? $args['itemsperpage'] : 25, 'POST');
        if ($itemsperpage < 1) $itemsperpage = 25;
        ModUtil::setVar('Theme', 'itemsperpage', $itemsperpage);

        $cssjscombine = (bool)FormUtil::getPassedValue('cssjscombine', isset($args['cssjscombine']) ? $args['cssjscombine'] : false, 'POST');
        ModUtil::setVar('Theme', 'cssjscombine', $cssjscombine);

        $cssjsminify = (bool)FormUtil::getPassedValue('cssjsminify', isset($args['cssjsminify']) ? $args['cssjsminify'] : false, 'POST');
        ModUtil::setVar('Theme', 'cssjsminify', $cssjsminify);

        $cssjscompress = (bool)FormUtil::getPassedValue('cssjscompress', isset($args['cssjscompress']) ? $args['cssjscompress'] : false, 'POST');
        ModUtil::setVar('Theme', 'cssjscompress', $cssjscompress);

        $cssjscombine_lifetime = (int)FormUtil::getPassedValue('cssjscombine_lifetime', isset($args['cssjscombine_lifetime']) ? $args['cssjscombine_lifetime'] : 3600, 'POST');
        if ($cssjscombine_lifetime < -1) $cssjscombine_lifetime = 3600;
        ModUtil::setVar('Theme', 'cssjscombine_lifetime', $cssjscombine_lifetime);


        // render
        $render_compile_check = (bool)FormUtil::getPassedValue('render_compile_check', isset($args['render_compile_check']) ? $args['render_compile_check'] : false, 'POST');
        ModUtil::setVar('Theme', 'render_compile_check', $render_compile_check);

        $render_force_compile = (bool)FormUtil::getPassedValue('render_force_compile', isset($args['render_force_compile']) ? $args['render_force_compile'] : false, 'POST');
        ModUtil::setVar('Theme', 'render_force_compile', $render_force_compile);

        $render_cache = (bool)FormUtil::getPassedValue('render_cache', isset($args['render_cache']) ? $args['render_cache'] : false, 'POST');
        ModUtil::setVar('Theme', 'render_cache', $render_cache);

        $render_lifetime = (int)FormUtil::getPassedValue('render_lifetime', isset($args['render_lifetime']) ? $args['render_lifetime'] : 3600, 'POST');
        if ($render_lifetime < -1) $render_lifetime = 3600;


        // The configuration has been changed, so we clear all caches for this module.
        $this->renderer->clear_all_cache();

        // the module configuration has been updated successfuly
        LogUtil::registerStatus($this->__('Done! Saved module configuration.'));

        return System::redirect(ModUtil::url('Theme', 'admin', 'modifyconfig'));
    }

    /**
     * Clear theme engine compiled templates
     *
     * Using this function, the admin can clear all theme engine compiled
     * templates for the system.
     */
    public function clear_compiled()
    {
        // Security check
        if (!SecurityUtil::checkPermission('Theme::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        // Confirm authorisation code
        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError(ModUtil::url('Theme','admin','view'));
        }

        $Theme = Theme::getInstance('Theme');
        $res   = $Theme->clear_compiled();

        if ($res) {
            LogUtil::registerStatus($this->__('Done! Deleted theme engine compiled templates.'));
        } else {
            LogUtil::registerError($this->__('Error! Failed to clear theme engine compiled templates.'));
        }

        return System::redirect(ModUtil::url('Theme', 'admin', 'modifyconfig'));
    }

    /**
     * Clear theme engine cached templates
     *
     * Using this function, the admin can clear all theme engine cached
     * templates for the system.
     */
    public function clear_cache()
    {
        // Security check
        if (!SecurityUtil::checkPermission('Theme::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        // Confirm authorisation code
        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError(ModUtil::url('Theme','admin','main'));
        }

        $Theme = Theme::getInstance('Theme');
        $res   = $Theme->clear_all_cache();

        if ($res) {
            LogUtil::registerStatus($this->__('Done! Deleted theme engine cached templates.'));
        } else {
            LogUtil::registerError($this->__('Error! Failed to clear theme engine cached templates.'));
        }

        return System::redirect(ModUtil::url('Theme', 'admin', 'modifyconfig'));
    }

    /**
     * Clear CSS/JS combination cached files
     *
     * Using this function, the admin can clear all CSS/JS combination cached
     * files for the system.
     */
    public function clear_cssjscombinecache()
    {
        // Security check
        if (!SecurityUtil::checkPermission('Theme::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        // Confirm authorisation code
        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError(ModUtil::url('Theme','admin','view'));
        }

        $Theme = Theme::getInstance('Theme');
        $Theme->clear_cssjscombinecache();

        LogUtil::registerStatus($this->__('Done! Deleted CSS/JS combination cached files.'));
        return System::redirect(ModUtil::url('Theme', 'admin', 'modifyconfig'));
    }

    /**
     * Clear render compiled templates
     *
     * Using this function, the admin can clear all render compiled templates
     * for the system.
     */
    public function render_clear_compiled()
    {
        // Security check
        if (!SecurityUtil::checkPermission('Theme::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        // Confirm authorisation code
        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError(ModUtil::url('Theme','admin','view'));
        }

        $Renderer = Renderer::getInstance();
        $res      = $Renderer->clear_compiled();

        if ($res) {
            LogUtil::registerStatus($this->__('Done! Deleted rendering engine compiled templates.'));
        } else {
            LogUtil::registerError($this->__('Error! Failed to clear rendering engine compiled templates.'));
        }

        return System::redirect(ModUtil::url('Theme', 'admin', 'modifyconfig'));
    }

    /**
     * Clear render cached templates
     *
     * Using this function, the admin can clear all render cached templates
     * for the system.
     */
    public function render_clear_cache()
    {
        // Security check
        if (!SecurityUtil::checkPermission('Theme::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        // Confirm authorisation code
        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError(ModUtil::url('Theme','admin','view'));
        }

        $Renderer = Renderer::getInstance();
        $res      = $Renderer->clear_all_cache();

        if ($res) {
            LogUtil::registerStatus($this->__('Done! Deleted rendering engine cached pages.'));
        } else {
            LogUtil::registerError($this->__('Error! Failed to clear rendering engine cached pages.'));
        }

        return System::redirect(ModUtil::url('Theme', 'admin', 'modifyconfig'));
    }

    /**
     * Clear all cache and compile directories
     *
     * Using this function, the admin can clear all theme and render cached,
     * compiled and combined files for the system.
     */
    public function clearallcompiledcaches()
    {
        // Security check
        if (!SecurityUtil::checkPermission('Theme::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        ModUtil::apiFunc('Settings', 'admin', 'clearallcompiledcaches');

        LogUtil::registerStatus($this->__('Done! Cleared all cache and compile directories.'));
        return System::redirect(ModUtil::url('Theme', 'admin', 'modifyconfig'));
    }
}