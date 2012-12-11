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
     * Post initialise.
     *
     * @return void
     */
    protected function postInitialize()
    {
        // In this controller we do not want caching.
        $this->view->setCaching(Zikula_View::CACHE_DISABLED);
    }

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

        if (isset($this->serviceManager['multisites.enabled']) && $this->serviceManager['multisites.enabled'] == 1) {
            // only the main site can regenerate the themes list
            if ($this->serviceManager['multisites.mainsiteurl'] == FormUtil::getPassedValue('sitedns', null, 'GET')) {
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

        // assign the values for the pager plugin
        $this->view->assign('pager', array('numitems' => sizeof($allthemes),
                                           'itemsperpage' => $itemsperpage));

        // return the output that has been generated to the template
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

        foreach ($allthemes as $key => $theme) {
            if (strtolower($key[0]) == $startlet) {
                $themes[$key] = $theme;
            }
        }

        return $themes;
    }

    /**
     * Running config checker
     */
    private function checkRunningConfig($themeinfo)
    {
        $ostemp = CacheUtil::getLocalDir();
        $zpath  = $ostemp.'/Theme_Config/'.DataUtil::formatForOS($themeinfo['directory']);
        $tpath  = 'themes/'.DataUtil::formatForOS($themeinfo['directory']).'/templates/config';

        // check if we can edit the theme and, if not, create the running config
        if (!is_writable($tpath.'/pageconfigurations.ini')) {
            if (!file_exists($zpath) || is_writable($zpath)) {
                ModUtil::apiFunc('Theme', 'admin', 'createrunningconfig', array('themename' => $themeinfo['name']));

                LogUtil::registerStatus($this->__f('Notice: The changes made via Admin Panel will be saved on \'%1$s\' because it seems that the .ini files on \'%2$s\' are not writable.', array($zpath, $tpath)));

            } else {
                LogUtil::registerError($this->__f('Error! Cannot write any configuration changes. Make sure that the .ini files on \'%1$s\' or \'%2$s\', and the folder itself, are writable.', array($tpath, $zpath)));
            }
        } else {
            LogUtil::registerStatus($this->__f('Notice: Seems that your %1$s\'s .ini files are writable. Be sure that there are no .ini files on \'%2$s\' because if so, the Theme Engine will consider them and not your %1$s\'s ones.', array($themeinfo['name'], $zpath)));
        }

        LogUtil::registerStatus($this->__f("If the system cannot write on any .ini file, the changes will be saved on '%s' and the Theme Engine will use it.", $zpath));
    }

    /**
     * modify theme
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

        // check that we have writable files
        $this->checkRunningConfig($themeinfo);

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
        $curthemeinfo['user']   = 0;
        $curthemeinfo['system'] = 0;
        $curthemeinfo['admin']  = 0;

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
        $filename  = FormUtil::getPassedValue('filename', isset($args['filename']) ? $args['filename'] : null, 'GET');

        // check our input
        if (empty($themename)) {
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

        if ($filename) {
            $variables = ModUtil::apiFunc('Theme', 'user', 'getpageconfiguration', array('theme' => $themename, 'filename' => $filename));
            $variables = ModUtil::apiFunc('Theme', 'user', 'formatvariables', array('theme' => $themename, 'variables' => $variables, 'formatting' => true));

        } else {
            $variables = ModUtil::apiFunc('Theme', 'user', 'getvariables', array('theme' => $themename, 'formatting' => true));
        }

        // load the language file
        ZLanguage::bindThemeDomain($themename);

        // check that we have writable files
        $this->checkRunningConfig($themeinfo);

        // assign variables, themename, themeinfo and return output
        return $this->view->assign('variables', $variables)
                          ->assign('themename', $themename)
                          ->assign('themeinfo', $themeinfo)
                          ->assign('filename', $filename)
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
        $variablesnames   = FormUtil::getPassedValue('variablesnames', isset($args['variablesnames']) ? $args['variablesnames'] : null, 'POST');
        $variablesvalues  = FormUtil::getPassedValue('variablesvalues', isset($args['variablesvalues']) ? $args['variablesvalues'] : null, 'POST');
        $newvariablename  = FormUtil::getPassedValue('newvariablename', isset($args['newvariablename']) ? $args['newvariablename'] : null, 'POST');
        $newvariablevalue = FormUtil::getPassedValue('newvariablevalue', isset($args['newvariablevalue']) ? $args['newvariablevalue'] : null, 'POST');

        $themename = FormUtil::getPassedValue('themename', isset($args['themename']) ? $args['themename'] : null, 'POST');
        $filename  = FormUtil::getPassedValue('filename', isset($args['filename']) ? $args['filename'] : null, 'POST');

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
        if ($filename) {
            $variables = ModUtil::apiFunc('Theme', 'user', 'getpageconfiguration', array('theme' => $themename, 'filename' => $filename));
            $returnurl = ModUtil::url('Theme', 'admin', 'variables', array('themename' => $themename, 'filename' => $filename));

        } else {
            $filename  = 'themevariables.ini';
            $variables = ModUtil::apiFunc('Theme', 'user', 'getvariables', array('theme' => $themename));
            $returnurl = ModUtil::url('Theme', 'admin', 'variables', array('themename' => $themename));
        }

        // form our existing variables
        $newvariables = array();
        foreach ($variablesnames as $id => $variablename) {
            preg_match('/^([\d\w_)]+)(\[([\d\w_)]+)\])?$/', $variablename, $matches);
            if (isset($matches[1])) {
                if (isset($matches[3])) {
                    $newvariables[$matches[1]][$matches[3]] = $variablesvalues[$id];
                } else {
                    $newvariables[$matches[1]] = $variablesvalues[$id];
                }
            }
        }
        // add the new theme variable to the existing variables
        if (!empty($newvariablename) && !empty($newvariablevalue)) {
            preg_match('/^([\d\w_)]+)(\[([\d\w_)]+)\])?$/', $newvariablename, $matches);
            if (isset($matches[1])) {
                if (isset($matches[3])) {
                    $newvariables[$matches[1]][$matches[3]] = $newvariablevalue;
                } else {
                    $newvariables[$matches[1]] = $newvariablevalue;
                }
            }
        }

        // re-add the new values
        $variables['variables'] = $newvariables;

        // rewrite the variables to the running config
        ModUtil::apiFunc('Theme', 'user', 'writeinifile', array('theme' => $themename, 'assoc_arr' => $variables, 'has_sections' => true, 'file' => $filename));

        // set a status message
        LogUtil::registerStatus($this->__('Done! Saved your changes.'));

        // redirect back to the variables page
        $this->redirect($returnurl);
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

        // check that we have writable files
        $this->checkRunningConfig($themeinfo);

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

        // assign an array to populate the modules dropdown
        $allmods = ModUtil::getAllMods();
        $mods = array();
        foreach ($allmods as $mod) {
            $mods[$mod['name']] = $mod['displayname'];
        }

        // assign the page configuration assignments
        $pageconfigurations = ModUtil::apiFunc('Theme', 'user', 'getpageconfigurations', array('theme' => $themename));

        // defines the default types and master
        $pagetypes = array(
            'master'  => $this->__('Master'),
            '*home'   => $this->__('Homepage'),
            '*admin'  => $this->__('Admin panel pages'),
            '*editor' => $this->__('Editor panel pages')
        );

        // checks the  page configuration files in use
        $pageconfigfiles = array();
        foreach ($pageconfigurations as $name => $pageconfiguration) {
            // checks for non-standard pagetypes
            if (strpos($name, '*') === 0 && !isset($pagetypes[$name])) {
                //! Pages inside a specific Controller type (editor, moderator, user)
                $pagetypes[$name] = $this->__f('%s type pages', ucfirst(substr($name, 1)));
            }
            // check if the file exists
            if ($exists = file_exists("themes/$themeinfo[directory]/templates/config/$pageconfiguration[file]")) {
                $existingconfigs[] = $pageconfiguration['file'];
            }
            $pageconfigfiles[$pageconfiguration['file']] = $exists;
        }
        ksort($pageconfigfiles);

        // gets the available page configurations on the theme
        $existingconfigs = ModUtil::apiFunc('Theme', 'user', 'getconfigurations', array('theme' => $themename));

        // check that we have writable files
        $this->checkRunningConfig($themeinfo);

        // assign the output vars
        $this->view->assign('themename', $themename)
                   ->assign('themeinfo', $themeinfo)
                   ->assign('pagetypes', $pagetypes)
                   ->assign('modules', $mods)
                   ->assign('pageconfigurations', $pageconfigurations)
                   ->assign('pageconfigs', $pageconfigfiles)
                   ->assign('existingconfigs', $existingconfigs);

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
        $filename  = FormUtil::getPassedValue('filename', isset($args['filename']) ? $args['filename'] : null, 'GET');

        // check our input
        if (empty($themename)) {
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
        if (empty($pageconfiguration)) {
            return LogUtil::registerArgsError(ModUtil::url('Theme', 'admin', 'view'));
        }

        // get all block positions
        $blockpositions = ModUtil::apiFunc('Blocks', 'user', 'getallpositions');
        foreach ($blockpositions as $name => $blockposition) {
            // check the page configuration
            if (!isset($pageconfiguration['blockpositions'][$blockposition['name']])) {
                $pageconfiguration['blockpositions'][$name] = '';
            }
            $blockpositions[$name] = $blockposition['description'];
        }

        // call the block API to get a list of all available blocks
        $allblocks = BlockUtil::loadAll();
        foreach ($allblocks as $key => $blocks) {
            foreach ($blocks as $k => $block) {
                $allblocks[$key][$k]['bkey'] = $bkey = strtolower($block['bkey']);
                // check the page configuration
                if (!isset($pageconfiguration['blocktypes'][$bkey])) {
                    $pageconfiguration['blocktypes'][$bkey] = '';
                }
            }
        }

        // call the block API to get a list of all defined block instances
        $blocks = ModUtil::apiFunc('Blocks', 'user', 'getall');
        foreach ($blocks as $block) {
            // check the page configuration
            if (!isset($pageconfiguration['blockinstances'][$block['bid']])) {
                $pageconfiguration['blockinstances'][$block['bid']] = '';
            }
        }

        // palette default
        if (!isset($pageconfiguration['palette'])) {
            $pageconfiguration['palette'] = '';
        }

        // block  default
        if (!isset($pageconfiguration['block'])) {
            $pageconfiguration['block'] = '';
        }

        // filters defaults
        if (!isset($pageconfiguration['filters']['outputfilters'])) {
            $pageconfiguration['filters']['outputfilters'] = '';
        }
        if (!isset($pageconfiguration['filters']['prefilters'])) {
            $pageconfiguration['filters']['prefilters'] = '';
        }
        if (!isset($pageconfiguration['filters']['postfilters'])) {
            $pageconfiguration['filters']['postfilters'] = '';
        }

        $this->view->setCaching(Zikula_View::CACHE_DISABLED);

        // assign the output variables and fetch the template
        return $this->view->assign('filename', $filename)
                          ->assign('themename', $themename)
                          ->assign('themeinfo', $themeinfo)
                          ->assign('moduletemplates', ModUtil::apiFunc('Theme', 'user', 'gettemplates', array('theme' => $themename)))
                          ->assign('blocktemplates', ModUtil::apiFunc('Theme', 'user', 'gettemplates', array('theme' => $themename, 'type' => 'blocks')))
                          ->assign('palettes', ModUtil::apiFunc('Theme', 'user', 'getpalettenames', array('theme' => $themename)))
                          ->assign('blockpositions', $blockpositions)
                          ->assign('allblocks', $allblocks)
                          ->assign('blocks', $blocks)
                          ->assign('pageconfiguration', $pageconfiguration)
                          ->fetch('theme_admin_modifypageconfigtemplates.tpl');
    }

    /**
     * modify a theme page configuration
     *
     */
    public function updatepageconfigtemplates($args)
    {
        $this->checkCsrfToken();

        // get our input
        $themename     = FormUtil::getPassedValue('themename', isset($args['themename']) ? $args['themename'] : null, 'POST');
        $filename      = FormUtil::getPassedValue('filename', isset($args['filename']) ? $args['filename'] : null, 'POST');
        $pagetemplate  = FormUtil::getPassedValue('pagetemplate', isset($args['pagetemplate']) ? $args['pagetemplate'] : '', 'POST');
        $blocktemplate = FormUtil::getPassedValue('blocktemplate', isset($args['blocktemplate']) ? $args['blocktemplate'] : '', 'POST');
        $pagepalette   = FormUtil::getPassedValue('pagepalette', isset($args['pagepalette']) ? $args['pagepalette'] : '', 'POST');
        $modulewrapper = FormUtil::getPassedValue('modulewrapper', isset($args['modulewrapper']) ? $args['modulewrapper'] : 1, 'POST');
        $blockwrapper  = FormUtil::getPassedValue('blockwrapper', isset($args['blockwrapper']) ? $args['blockwrapper'] : 1, 'POST');

        $blockinstancetemplates = FormUtil::getPassedValue('blockinstancetemplates', isset($args['blockinstancetemplates']) ? $args['blockinstancetemplates'] : null, 'POST');
        $blocktypetemplates     = FormUtil::getPassedValue('blocktypetemplates', isset($args['blocktypetemplates']) ? $args['blocktypetemplates'] : null, 'POST');
        $blockpositiontemplates = FormUtil::getPassedValue('blockpositiontemplates', isset($args['blockpositiontemplates']) ? $args['blockpositiontemplates'] : null, 'POST');

        $filters = FormUtil::getPassedValue('filters', isset($args['filters']) ? $args['filters'] : null, 'POST');

        // check our input
        if (empty($themename) || empty($pagetemplate)) {
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
        if (empty($pageconfiguration)) {
            return LogUtil::registerArgsError(ModUtil::url('Theme', 'admin', 'view'));
        }

        // form the new page configuration
        $pageconfiguration['page']           = $pagetemplate;
        $pageconfiguration['block']          = $blocktemplate;
        $pageconfiguration['palette']        = $pagepalette;
        $pageconfiguration['modulewrapper']  = $modulewrapper;
        $pageconfiguration['blockwrapper']   = $blockwrapper;
        $pageconfiguration['blockinstances'] = array_filter($blockinstancetemplates);
        $pageconfiguration['blocktypes']     = array_filter($blocktypetemplates);
        $pageconfiguration['blockpositions'] = array_filter($blockpositiontemplates);

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
     * Check if the given filter exists
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
        if (count($leftover) > 0) {
            LogUtil::registerError($this->__f('Error! Removed unknown \'%1$s\': \'%2$s\'.', array(DataUtil::formatForDisplay($type), DataUtil::formatForDisplay(implode(',', $leftover)))));
        }

        return implode(',', $newfilters);
    }

    /**
     * Modify a theme page configuration
     *
     */
    public function modifypageconfigurationassignment($args)
    {
        // get our input
        $themename = FormUtil::getPassedValue('themename', isset($args['themename']) ? $args['themename'] : null, 'GET');
        $pcname    = FormUtil::getPassedValue('pcname', isset($args['pcname']) ? $args['pcname'] : null, 'GET');

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

        // assign all modules
        $allmods = ModUtil::getAllMods();
        $mods = array();
        foreach ($allmods as $mod) {
            $mods[$mod['name']] = $mod['name'];
        }

        // get all pageconfigurations
        $pageconfigurations = ModUtil::apiFunc('Theme', 'user', 'getpageconfigurations', array('theme' => $themename));
        if (!isset($pageconfigurations[$pcname])) {
            LogUtil::registerError($this->__('Error! No such page configuration assignment found.'));
            $this->redirect(ModUtil::url('Theme', 'admin', 'view'));
        }

        // defines the default types and master
        $pagetypes = array(
            'master'  => $this->__('Master'),
            '*home'   => $this->__('Homepage'),
            '*admin'  => $this->__('Admin panel pages'),
            '*editor' => $this->__('Editor panel pages')
        );

        // checks for non-standard pagetypes
        foreach ($pageconfigurations as $name => $pageconfiguration) {
            if (strpos($name, '*') === 0 && !isset($pagetypes[$name])) {
                //! Pages inside a specific Controller type (editor, moderator, user)
                $pagetypes[$name] = $this->__f('%s type pages', ucfirst(substr($name, 1)));
            }
        }

        // form the page config assignment array setting some useful key names
        $pageconfigassignment = array('pagemodule' => null, 'pagetype' => null, 'pagefunc' => null, 'pagecustomargs' => null);

        $pageconfigparts = explode('/', $pcname);

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
        if (isset($pageconfigurations[$pcname]['important']) && $pageconfigurations[$pcname]['important']) {
            $pageconfigassignment['important'] = 1;
        }

        // gets the available page configurations on the theme
        $existingconfigs = ModUtil::apiFunc('Theme', 'user', 'getconfigurations', array('theme' => $themename));

        // assign the page config assignment name, theme name and theme info
        $this->view->assign($pageconfigassignment)
                   ->assign('existingconfigs', $existingconfigs)
                   ->assign('pcname', $pcname)
                   ->assign('themename', $themename)
                   ->assign('themeinfo', $themeinfo)
                   ->assign('pagetypes', $pagetypes)
                   ->assign('modules', $mods)
                   ->assign('filename', $pageconfigurations[$pcname]['file']);

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
        $pageimportant = FormUtil::getPassedValue('pageimportant', isset($args['pageimportant']) ? $args['pageimportant'] : null, 'POST');
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

        // form the new page configuration
        $newpageconfiguration = $pagemodule;
        if (strpos($pagemodule, '*') !== 0 && $pagemodule != 'master') {
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

        // remove the config assignment if was changed
        if (isset($pcname) && isset($pageconfigurations[$pcname]) && $pcname != $newpageconfiguration) {
            // need to place the new one in the old position
            $keys = array_keys($pageconfigurations);
            $pos  = array_search($pcname, $keys);
            $keys[$pos] = $newpageconfiguration;
            $pageconfigurations = array_combine($keys, array_values($pageconfigurations));
        }

        // fill the pageconfiguration info
        $pageconfigurations[$newpageconfiguration] = array('file' => $filename);

        if (isset($pageimportant)) {
            $pageconfigurations[$newpageconfiguration]['important'] = 1;
        }

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
     * Modify Theme settings.
     */
    public function modifyconfig()
    {
        // Security check
        if (!SecurityUtil::checkPermission('Theme::', '::', ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }

        // assign a list of modules suitable for html_options
        $usermods = ModUtil::getUserMods();
        $mods = array();
        foreach ($usermods as $usermod) {
            $mods[$usermod['name']] = $usermod['displayname'];
        }

        // register the renderer object allow access to various view values
        $this->view->register_object('render', $this->view);

        // check for a .htaccess file
        if (file_exists('.htaccess')) {
            $this->view->assign('htaccess', 1);
        } else {
            $this->view->assign('htaccess', 0);
        }

        // assign the output variables and fetch the template
        return $this->view->assign('mods', $mods)
                          // assign all module vars
                          ->assign($this->getVars())
                          // assign an csrftoken for the clear cache/compile links
                          ->assign('csrftoken', SecurityUtil::generateCsrfToken($this->serviceManager, true))
                          // assign the core config var
                          ->assign('theme_change', System::getVar('theme_change'))
                          // extracted list of non-cached mods
                          ->assign('modulesnocache', array_flip(explode(',', $this->getVar('modulesnocache'))))
                          ->fetch('theme_admin_modifyconfig.tpl');
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

        // check if the theme cache was disabled and clean it if so
        $enablecache = (bool)FormUtil::getPassedValue('enablecache', isset($args['enablecache']) ? $args['enablecache'] : false, 'POST');

        if ($this->getVar('enablecache') && !$enablecache) {
            $theme = Zikula_View_Theme::getInstance();
            $theme->clear_all_cache();
        }

        // set our module variables
        $this->setVar('enablecache', $enablecache);

        $modulesnocache = FormUtil::getPassedValue('modulesnocache', isset($args['modulesnocache']) ? $args['modulesnocache'] : array(), 'POST');
        $modulesnocache = implode(',', $modulesnocache);
        $this->setVar('modulesnocache', $modulesnocache);

        $compile_check = (bool)FormUtil::getPassedValue('compile_check', isset($args['compile_check']) ? $args['compile_check'] : false, 'POST');
        $this->setVar('compile_check', $compile_check);

        $cache_lifetime = (int)FormUtil::getPassedValue('cache_lifetime', isset($args['cache_lifetime']) ? $args['cache_lifetime'] : 3600, 'POST');
        if ($cache_lifetime < -1) $cache_lifetime = 3600;
        $this->setVar('cache_lifetime', $cache_lifetime);

        $cache_lifetime_mods = (int)FormUtil::getPassedValue('cache_lifetime_mods', isset($args['cache_lifetime_mods']) ? $args['cache_lifetime_mods'] : 3600, 'POST');
        if ($cache_lifetime_mods < -1) $cache_lifetime_mods = 3600;
        $this->setVar('cache_lifetime_mods', $cache_lifetime_mods);

        $force_compile = (bool)FormUtil::getPassedValue('force_compile', isset($args['force_compile']) ? $args['force_compile'] : false, 'POST');
        $this->setVar('force_compile', $force_compile);

        $trimwhitespace = (bool)FormUtil::getPassedValue('trimwhitespace', isset($args['trimwhitespace']) ? $args['trimwhitespace'] : false, 'POST');
        $this->setVar('trimwhitespace', $trimwhitespace);

        $maxsizeforlinks = (int)FormUtil::getPassedValue('maxsizeforlinks', isset($args['maxsizeforlinks']) ? $args['maxsizeforlinks'] : 30, 'POST');
        $this->setVar('maxsizeforlinks', $maxsizeforlinks);

        $theme_change = (bool)FormUtil::getPassedValue('theme_change', isset($args['theme_change']) ? $args['theme_change'] : false, 'POST');
        System::setVar('theme_change', $theme_change);
        
        $enable_mobile_theme = (bool)FormUtil::getPassedValue('enable_mobile_theme', isset($args['enable_mobile_theme']) ? $args['enable_mobile_theme'] : false, 'POST');
        $this->setVar('enable_mobile_theme', $enable_mobile_theme);

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

        $render_cache = (int)FormUtil::getPassedValue('render_cache', isset($args['render_cache']) ? $args['render_cache'] : false, 'POST');
        $this->setVar('render_cache', $render_cache);

        $render_lifetime = (int)FormUtil::getPassedValue('render_lifetime', isset($args['render_lifetime']) ? $args['render_lifetime'] : 3600, 'POST');
        if ($render_lifetime < -1) $render_lifetime = 3600;
        $this->setVar('render_lifetime', $render_lifetime);

        $render_expose_template = (bool)FormUtil::getPassedValue('render_expose_template', isset($args['render_expose_template']) ? $args['render_expose_template'] : false, 'POST');
        $this->setVar('render_expose_template', $render_expose_template);

        // The configuration has been changed, so we clear all caches for this module.
        $this->view->clear_compiled();
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

        $theme = Zikula_View_Theme::getInstance();
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

        $cacheid = FormUtil::getPassedValue('cacheid');

        $theme = Zikula_View_Theme::getInstance();

        if ($cacheid) {
            // clear cache for all active themes
            $themesarr = ThemeUtil::getAllThemes();
            foreach ($themesarr as $themearr) {
                $themedir = $themearr['directory'];
                $res = $theme->clear_cache(null, $cacheid, null, null, $themedir);
                if ($res) {
                    LogUtil::registerStatus($this->__('Done! Deleted theme engine cached templates.').' '.$cacheid.', '.$themedir);
                } else {
                    LogUtil::registerError($this->__('Error! Failed to clear theme engine cached templates.').' '.$cacheid.', '.$themedir);
                }
            }
        } else {
            // this clear all cache for all themes
            $res = $theme->clear_all_cache();
            if ($res) {
                LogUtil::registerStatus($this->__('Done! Deleted theme engine cached templates.'));
            } else {
                LogUtil::registerError($this->__('Error! Failed to clear theme engine cached templates.'));
            }
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

        $theme = Zikula_View_Theme::getInstance();
        $theme->clear_cssjscombinecache();

        LogUtil::registerStatus($this->__('Done! Deleted CSS/JS combination cached files.'));
        $this->redirect(ModUtil::url('Theme', 'admin', 'modifyconfig'));
    }

    /**
     * Clear theme engine configurations
     *
     * Using this function, the admin can clear all theme engine configuration
     * copies created inside the temporary directory.
     */
    public function clear_config()
    {
        $csrftoken = FormUtil::getPassedValue('csrftoken');
        $this->checkCsrfToken($csrftoken);

        // Security check
        if (!SecurityUtil::checkPermission('Theme::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        $theme = Zikula_View_Theme::getInstance();
        $res   = $theme->clear_theme_config();

        if ($res) {
            LogUtil::registerStatus($this->__('Done! Deleted theme engine configurations.'));
        } else {
            LogUtil::registerError($this->__('Error! Failed to clear theme engine configurations.'));
        }

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
