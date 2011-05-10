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
class Theme_Controller_Admin extends Zikula_AbstractController
{
    /**
     * the main admin function
     */
    public function main()
    {
        // Security check will be done in view()
        $this->redirect(ModUtil::url('Theme', 'admin', 'view'));
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

        $this->view->setCaching(false);

        if(isset($this->serviceManager['multisites.enabled']) && $this->serviceManager['multisites.enabled'] == 1){
            // only the main site can regenerate the themes list
            if($this->serviceManager['multisites.mainsiteurl'] == FormUtil::getPassedValue('sitedns', null, 'GET')){
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
        $itemsperpage = $this->getVar('itemsperpage');

        // call the API to get a list of all themes in the themes dir
        $allthemes = ThemeUtil::getAllThemes(ThemeUtil::FILTER_ALL, ThemeUtil::STATE_ALL);

        // filter by letter if required
        if (isset($startlet) && !empty($startlet)) {
            $allthemes = $this->_filterbyletter($allthemes, $startlet);
        }

        $themes = array_slice($allthemes, $startnum-1, $itemsperpage);

        $this->view->assign('themes', $themes);

        // assign default theme
        $this->view->assign('currenttheme', System::getVar('Default_Theme'));

        // assign the values for the smarty plugin to produce a pager
        $this->view->assign('pager', array('numitems' => sizeof($allthemes),
                                           'itemsperpage' => $itemsperpage));

        // Return the output that has been generated to the template
        return $this->view->fetch('theme_admin_view.tpl');
    }

    /**
     * filter theme array by letter
     *
     * @access private
     */
    private function _filterbyletter($allthemes, $startlet)
    {
        $themes = array();

        $startlet = strtolower($startlet);

        foreach($allthemes as $key => $theme) {
            if (strtolower($key[0]) == $startlet) {
                $themes[$key] = $theme;
            }
        }

        return $themes;
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

        $this->view->setCaching(false);

        // assign theme name, theme info and return output
        return $this->view->assign('themename', $themename)
                          ->assign('themeinfo', $themeinfo)
                          ->fetch('theme_admin_modify.tpl');
    }

    /**
     * update the theme variables
     *
     */
    public function updatesettings($args)
    {
        $this->checkCsrfToken();

        // get our input
        $themeinfo = FormUtil::getPassedValue('themeinfo', isset($args['themeinfo']) ? $args['themeinfo'] : null, 'POST');
        $themename = FormUtil::getPassedValue('themename', isset($args['themename']) ? $args['themename'] : null, 'POST');

        // check our input
        if (!isset($themename) || empty($themename)) {
            LogUtil::registerArgsError();
            $this->redirect(ModUtil::url('Theme', 'admin', 'view'));
        }

        // Security check
        if (!SecurityUtil::checkPermission('Theme::', "$themename::settings", ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
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
        $this->redirect(ModUtil::url('Theme', 'admin', 'view'));
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

        $this->view->setCaching(false);

        // assign variables, themename, themeinfo and return output
        return $this->view->assign('variables', ModUtil::apiFunc('Theme', 'user', 'getvariables', array('theme' => $themename, 'formatting' => true)))
                          ->assign('themename', $themename)
                          ->assign('themeinfo', $themeinfo)
                          ->fetch('theme_admin_variables.tpl');
    }

    /**
     * update the theme variables
     *
     */
    public function updatevariables($args)
    {
        $this->checkCsrfToken();

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
        $this->redirect(ModUtil::url('Theme', 'admin', 'variables', array('themename' => $themename)));
    }

    /**
     * display the themes palettes
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

        $this->view->setCaching(false);

        // assign palettes, themename, themeinfo and return output
        return $this->view->assign('palettes', ModUtil::apiFunc('Theme', 'user', 'getpalettes', array('theme' => $themename)))
                          ->assign('themename', $themename)
                          ->assign('themeinfo', $themeinfo)
                          ->fetch('theme_admin_palettes.tpl');
    }

    /**
     * update the theme palettes
     *
     */
    public function updatepalettes($args)
    {
        $this->checkCsrfToken();

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
        $this->redirect(ModUtil::url('Theme', 'admin', 'palettes', array('themename' => $themename)));
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

        $this->view->setCaching(false);

        // assign the theme name and theme info
        $this->view->assign('themename', $themename)
                   ->assign('themeinfo', $themeinfo);

        // assign an array to populate the modules dropdown
        $allmods = ModUtil::getAllMods();
        $mods = array();
        foreach ($allmods as $mod) {
            $mods[$mod['name']] = $mod['displayname'];
        }
        $this->view->assign('modules', $mods);

        // assign the page configuration assignments
        $pageconfigurations = ModUtil::apiFunc('Theme', 'user', 'getpageconfigurations', array('theme' => $themename));
        ksort($pageconfigurations);
        $this->view->assign('pageconfigurations', $pageconfigurations);

        // identify unique page configuration files
        $pageconfigfiles = array();
        foreach ($pageconfigurations as $pageconfiguration) {
            $pageconfigfiles[$pageconfiguration['file']] = file_exists("themes/$themeinfo[directory]/templates/config/$pageconfiguration[file]");
        }
        $this->view->assign('pageconfigs', $pageconfigfiles);

        // Return the output that has been generated by this function
        return $this->view->fetch('theme_admin_pageconfigurations.tpl');
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

        $this->view->setCaching(Zikula_View::CACHE_DISABLED);

        // assign the base filename, themename, theme info, moduletemplates, blocktemplates and palettes
        $this->view->assign('filename', $filename)
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
        $this->view->assign('blockpositions', $positions);

        // call the block API to get a list of all available blocks
        $this->view->assign('allblocks', $allblocks = BlockUtil::loadAll());
        foreach ($allblocks as $key => $blocks) {
            foreach ($blocks as $block) {
                // check the page configuration
                if (!isset($pageconfiguration['blocktypes'][$block['bkey']])) {
                    $pageconfiguration['blocktypes'][$block['bkey']] = '';
                }
            }
        }

        // call the block API to get a list of all defined block instances
        $this->view->assign('blocks', $blocks = ModUtil::apiFunc('Blocks', 'user', 'getall'));
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

        // filter defaults
        if (!isset($pageconfiguration['filters'])) {
            $pageconfiguration['filters'] = array(
                    'outputfilters' => null,
                    'prefilters' => null,
                    'postfilters' => null
            );
        }

        // assign the page configuration array
        $this->view->assign('pageconfiguration', $pageconfiguration);

        // Return the output that has been generated by this function
        return $this->view->fetch('theme_admin_modifypageconfigtemplates.tpl');
    }

    /**
     * modify a theme page configuration
     *
     */
    public function updatepageconfigtemplates($args)
    {
        $this->checkCsrfToken();

        // get our input
        $themename = FormUtil::getPassedValue('themename', isset($args['themename']) ? $args['themename'] : null, 'POST');
        $filename = FormUtil::getPassedValue('filename', isset($args['filename']) ? $args['filename'] : null, 'POST');
        $pagetemplate = FormUtil::getPassedValue('pagetemplate', isset($args['pagetemplate']) ? $args['pagetemplate'] : null, 'POST');
        $pagepalette = FormUtil::getPassedValue('pagepalette', isset($args['pagepalette']) ? $args['pagepalette'] : null, 'POST');
        $blockpositiontemplates = FormUtil::getPassedValue('blockpositiontemplates', isset($args['blockpositiontemplates']) ? $args['blockpositiontemplates'] : null, 'POST');
        $blocktypetemplates = FormUtil::getPassedValue('blocktypetemplates', isset($args['blocktypetemplates']) ? $args['blocktypetemplates'] : null, 'POST');
        $blockinstancetemplates = FormUtil::getPassedValue('blockinstancetemplates', isset($args['blockinstancetemplates']) ? $args['blockinstancetemplates'] : null, 'POST');
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

        // form the new page configuration
        $pageconfiguration['page'] = $pagetemplate;
        $pageconfiguration['palette'] = $pagepalette;
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
        $this->redirect(ModUtil::url('Theme', 'admin', 'pageconfigurations', array('themename' => $themename)));
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

        $filters = explode(',', $filters);
        $newfilters = array();
        foreach ($filters as $filter) {
            foreach ($this->view->plugins_dir as $plugindir) {
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

        $this->view->setCaching(false);

        // assign the page config assignment name, theme name and theme info
        $this->view->assign('pcname', $pcname)
                   ->assign('themename', $themename)
                   ->assign('themeinfo', $themeinfo);

        // assign all modules
        $allmods = ModUtil::getAllMods();
        $mods = array();
        foreach ($allmods as $mod) {
            $mods[$mod['name']] = $mod['name'];
        }
        $this->view->assign('modules', $mods);

        // get all pageconfigurations
        $pageconfigurations = ModUtil::apiFunc('Theme', 'user', 'getpageconfigurations', array('theme' => $themename));
        if (!isset($pageconfigurations[$pcname])) {
            LogUtil::registerError($this->__('Error! No such page configuration assignment found.'));
            $this->redirect(ModUtil::url('Theme', 'admin', 'view'));
        }
        $pageconfigparts = explode('/', $pcname);

        // assign the config filename
        $this->view->assign('filename', $pageconfigurations[$pcname]['file']);

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
        $this->view->assign($pageconfigassignment);

        // Return the output that has been generated by this function
        return $this->view->fetch('theme_admin_modifypageconfigurationassignment.tpl');
    }

    /**
     * modify a theme page configuration
     *
     */
    public function updatepageconfigurationassignment($args)
    {
        $this->checkCsrfToken();

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
        $this->redirect(ModUtil::url('Theme', 'admin', 'pageconfigurations', array('themename' => $themename)));
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
            $this->view->setCaching(false);

            // Assign the theme info
            $this->view->assign($themeinfo);

            // Assign the page configuration name
            $this->view->assign('pcname', $pcname);

            // Return the output that has been generated by this function
            return $this->view->fetch('theme_admin_deletepageconfigurationassignment.tpl');
        }

        // If we get here it means that the user has confirmed the action
        $this->checkCsrfToken();

        // Delete the admin message
        // The return value of the function is checked
        if (ModUtil::apiFunc('Theme', 'admin', 'deletepageconfigurationassignment', array('themename' => $themename, 'pcname' => $pcname))) {
            // Success
            LogUtil::registerStatus($this->__('Done! Deleted it.'));
        }

        // return the user to the correct place
        $this->redirect(ModUtil::url('Theme', 'admin', 'pageconfigurations', array('themename' => $themename)));
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

        $this->view->setCaching(false);

        // assign the theme info and return output
        return $this->view->assign('themeinfo', ThemeUtil::getInfo(ThemeUtil::getIDFromName($themename)))
                          ->fetch('theme_admin_credits.tpl');
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
            $this->view->setCaching(false);

            // Add a hidden field for the item ID to the output
            $this->view->assign('themename', $themename);

            // assign the var defining if users can change themes
            $this->view->assign('theme_change', System::getVar('theme_change'));

            // Return the output that has been generated by this function
            return $this->view->fetch('theme_admin_setasdefault.tpl');
        }

        // If we get here it means that the user has confirmed the action
        $this->checkCsrfToken();

        // Set the default theme
        if (ModUtil::apiFunc('Theme', 'admin', 'setasdefault', array('themename' => $themename, 'resetuserselected' => $resetuserselected))) {
            // Success
            LogUtil::registerStatus($this->__('Done! Changed default theme.'));
        }

        $this->redirect(ModUtil::url('Theme', 'admin', 'view'));

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

            $this->view->setCaching(false);

            // Add the message id
            $this->view->assign($themeinfo);

            // Return the output that has been generated by this function
            return $this->view->fetch('theme_admin_delete.tpl');
        }

        // If we get here it means that the user has confirmed the action
        $this->checkCsrfToken();

        $deletefiles = FormUtil::getPassedValue('deletefiles', 0, 'POST');

        // Delete the admin message
        // The return value of the function is checked
        if (ModUtil::apiFunc('Theme', 'admin', 'delete', array('themename' => $themename, 'deletefiles' => $deletefiles))) {
            // Success
            LogUtil::registerStatus($this->__('Done! Deleted it.'));
        }

        // This function generated no output, and so now it is complete we redirect
        // the user to an appropriate page for them to carry on their work
        $this->redirect(ModUtil::url('Theme', 'admin', 'view'));
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

        $this->view->setCaching(false);

        // assign all module vars
        $this->view->assign($this->getVars());

        // assign an csrftoken for the clear cache/compile links
        $this->view->assign('csrftoken', SecurityUtil::generateCsrfToken($this->serviceManager, true));

        // assign the core config var
        $this->view->assign('theme_change', System::getVar('theme_change'));

        // assign a list of modules suitable for html_options
        $usermods = ModUtil::getUserMods();
        $mods = array();
        foreach ($usermods as $usermod) {
            $mods[$usermod['name']] = $usermod['displayname'];
        }
        $this->view->assign('mods', $mods);

        // assign an extracted list of non-cached mods
        $this->view->assign('modulesnocache', array_flip(explode(',', $this->getVar('modulesnocache'))));

        // check for a .htaccess file
        if (file_exists('.htaccess')){
            $this->view->assign('htaccess', 1);
        } else {
            $this->view->assign('htaccess', 0);
        }

        // register the renderer object allow access to various smarty values
        $this->view->register_object('render', $this->view);

        // Return the output that has been generated by this function
        return $this->view->fetch('theme_admin_modifyconfig.tpl');
    }

    /**
     * Update configuration
     *
     */
    public function updateconfig($args)
    {
        $this->checkCsrfToken();

        // security check
        if (!SecurityUtil::checkPermission('Theme::', '::', ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }

        // set our module variables
        $modulesnocache = FormUtil::getPassedValue('modulesnocache', isset($args['modulesnocache']) ? $args['modulesnocache'] : array(), 'POST');
        $modulesnocache = implode(',', $modulesnocache);
        $this->setVar('modulesnocache', $modulesnocache);

        $enablecache = (bool)FormUtil::getPassedValue('enablecache', isset($args['enablecache']) ? $args['enablecache'] : false, 'POST');
        $this->setVar('enablecache', $enablecache);

        $compile_check = (bool)FormUtil::getPassedValue('compile_check', isset($args['compile_check']) ? $args['compile_check'] : false, 'POST');
        $this->setVar('compile_check', $compile_check);

        $cache_lifetime = (int)FormUtil::getPassedValue('cache_lifetime', isset($args['cache_lifetime']) ? $args['cache_lifetime'] : 3600, 'POST');
        if ($cache_lifetime < -1) $cache_lifetime = 3600;
        $this->setVar('cache_lifetime', $cache_lifetime);

        $force_compile = (bool)FormUtil::getPassedValue('force_compile', isset($args['force_compile']) ? $args['force_compile'] : false, 'POST');
        $this->setVar('force_compile', $force_compile);

        $trimwhitespace = (bool)FormUtil::getPassedValue('trimwhitespace', isset($args['trimwhitespace']) ? $args['trimwhitespace'] : false, 'POST');
        $this->setVar('trimwhitespace', $trimwhitespace);

        $maxsizeforlinks = (int)FormUtil::getPassedValue('maxsizeforlinks', isset($args['maxsizeforlinks']) ? $args['maxsizeforlinks'] : 30, 'POST');
        $this->setVar('maxsizeforlinks', $maxsizeforlinks);

        $theme_change = (bool)FormUtil::getPassedValue('theme_change', isset($args['theme_change']) ? $args['theme_change'] : false, 'POST');
        System::setVar('theme_change', $theme_change);

        $itemsperpage = (int)FormUtil::getPassedValue('itemsperpage', isset($args['itemsperpage']) ? $args['itemsperpage'] : 25, 'POST');
        if ($itemsperpage < 1) $itemsperpage = 25;
        $this->setVar('itemsperpage', $itemsperpage);

        $cssjscombine = (bool)FormUtil::getPassedValue('cssjscombine', isset($args['cssjscombine']) ? $args['cssjscombine'] : false, 'POST');
        $this->setVar('cssjscombine', $cssjscombine);

        $cssjsminify = (bool)FormUtil::getPassedValue('cssjsminify', isset($args['cssjsminify']) ? $args['cssjsminify'] : false, 'POST');
        $this->setVar('cssjsminify', $cssjsminify);

        $cssjscompress = (bool)FormUtil::getPassedValue('cssjscompress', isset($args['cssjscompress']) ? $args['cssjscompress'] : false, 'POST');
        $this->setVar('cssjscompress', $cssjscompress);

        $cssjscombine_lifetime = (int)FormUtil::getPassedValue('cssjscombine_lifetime', isset($args['cssjscombine_lifetime']) ? $args['cssjscombine_lifetime'] : 3600, 'POST');
        if ($cssjscombine_lifetime < -1) $cssjscombine_lifetime = 3600;
        $this->setVar('cssjscombine_lifetime', $cssjscombine_lifetime);


        // render
        $render_compile_check = (bool)FormUtil::getPassedValue('render_compile_check', isset($args['render_compile_check']) ? $args['render_compile_check'] : false, 'POST');
        $this->setVar('render_compile_check', $render_compile_check);

        $render_force_compile = (bool)FormUtil::getPassedValue('render_force_compile', isset($args['render_force_compile']) ? $args['render_force_compile'] : false, 'POST');
        $this->setVar('render_force_compile', $render_force_compile);

        $render_cache = (bool)FormUtil::getPassedValue('render_cache', isset($args['render_cache']) ? $args['render_cache'] : false, 'POST');
        $this->setVar('render_cache', $render_cache);

        $render_lifetime = (int)FormUtil::getPassedValue('render_lifetime', isset($args['render_lifetime']) ? $args['render_lifetime'] : 3600, 'POST');
        if ($render_lifetime < -1) $render_lifetime = 3600;
        $this->setVar('render_lifetime', $render_lifetime);

        $render_expose_template = (bool)FormUtil::getPassedValue('render_expose_template', isset($args['render_expose_template']) ? $args['render_expose_template'] : false, 'POST');
        $this->setVar('render_expose_template', $render_expose_template);

        // The configuration has been changed, so we clear all caches for this module.
        $this->view->clear_all_cache();

        // the module configuration has been updated successfuly
        LogUtil::registerStatus($this->__('Done! Saved module configuration.'));

        $this->redirect(ModUtil::url('Theme', 'admin', 'modifyconfig'));
    }

    /**
     * Clear theme engine compiled templates
     *
     * Using this function, the admin can clear all theme engine compiled
     * templates for the system.
     */
    public function clear_compiled()
    {
        $csrftoken = FormUtil::getPassedValue('csrftoken');
        $this->checkCsrfToken($csrftoken);

        // Security check
        if (!SecurityUtil::checkPermission('Theme::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        $theme = Zikula_View_Theme::getInstance('Theme');
        $res   = $theme->clear_compiled();

        if ($res) {
            LogUtil::registerStatus($this->__('Done! Deleted theme engine compiled templates.'));
        } else {
            LogUtil::registerError($this->__('Error! Failed to clear theme engine compiled templates.'));
        }

        $this->redirect(ModUtil::url('Theme', 'admin', 'modifyconfig'));
    }

    /**
     * Clear theme engine cached templates
     *
     * Using this function, the admin can clear all theme engine cached
     * templates for the system.
     */
    public function clear_cache()
    {
        $csrftoken = FormUtil::getPassedValue('csrftoken');
        $this->checkCsrfToken($csrftoken);

        // Security check
        if (!SecurityUtil::checkPermission('Theme::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        $theme = Zikula_View_Theme::getInstance('Theme');
        $res   = $theme->clear_all_cache();

        if ($res) {
            LogUtil::registerStatus($this->__('Done! Deleted theme engine cached templates.'));
        } else {
            LogUtil::registerError($this->__('Error! Failed to clear theme engine cached templates.'));
        }

        $this->redirect(ModUtil::url('Theme', 'admin', 'modifyconfig'));
    }

    /**
     * Clear CSS/JS combination cached files
     *
     * Using this function, the admin can clear all CSS/JS combination cached
     * files for the system.
     */
    public function clear_cssjscombinecache()
    {
        $csrftoken = FormUtil::getPassedValue('csrftoken');
        $this->checkCsrfToken($csrftoken);

        // Security check
        if (!SecurityUtil::checkPermission('Theme::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        $theme = Zikula_View_Theme::getInstance('Theme');
        $theme->clear_cssjscombinecache();

        LogUtil::registerStatus($this->__('Done! Deleted CSS/JS combination cached files.'));
        $this->redirect(ModUtil::url('Theme', 'admin', 'modifyconfig'));
    }

    /**
     * Clear render compiled templates
     *
     * Using this function, the admin can clear all render compiled templates
     * for the system.
     */
    public function render_clear_compiled()
    {
        $csrftoken = FormUtil::getPassedValue('csrftoken');
        $this->checkCsrfToken($csrftoken);

        // Security check
        if (!SecurityUtil::checkPermission('Theme::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        $res = $this->view->clear_compiled();

        if ($res) {
            LogUtil::registerStatus($this->__('Done! Deleted rendering engine compiled templates.'));
        } else {
            LogUtil::registerError($this->__('Error! Failed to clear rendering engine compiled templates.'));
        }

        $this->redirect(ModUtil::url('Theme', 'admin', 'modifyconfig'));
    }

    /**
     * Clear render cached templates
     *
     * Using this function, the admin can clear all render cached templates
     * for the system.
     */
    public function render_clear_cache()
    {
        $csrftoken = FormUtil::getPassedValue('csrftoken');
        $this->checkCsrfToken($csrftoken);

        // Security check
        if (!SecurityUtil::checkPermission('Theme::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        $res = $this->view->clear_all_cache();

        if ($res) {
            LogUtil::registerStatus($this->__('Done! Deleted rendering engine cached pages.'));
        } else {
            LogUtil::registerError($this->__('Error! Failed to clear rendering engine cached pages.'));
        }

        $this->redirect(ModUtil::url('Theme', 'admin', 'modifyconfig'));
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
        $this->redirect(ModUtil::url('Theme', 'admin', 'modifyconfig'));
    }
}
