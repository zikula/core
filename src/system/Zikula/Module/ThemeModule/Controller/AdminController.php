<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Module\ThemeModule\Controller;

use Zikula_View;
use LogUtil;
use Modutil;
use SecurityUtil;
use ThemeUtil;
use System;
use CacheUtil;
use DataUtil;
use ZLanguage;
use BlockUtil;
use Zikula_View_Theme;
use Zikula\Module\ThemeModule\Util;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * administrative controllers for the theme module
 */
class AdminController extends \Zikula_AbstractController
{
    /**
     * the theme container
     */
    protected $container;

    /**
     * Post initialise.
     *
     * @return void
     */
    protected function postInitialize()
    {
        // In this controller we do not want caching.
        $this->view->setCaching(Zikula_View::CACHE_DISABLED);
        $this->container = $this->getContainer();
    }

    /**
     * the main admin function
     *
     * @return RedirectResponse
     */
    public function indexAction()
    {
        // Security check will be done in view()
        return new RedirectResponse(System::normalizeUrl(ModUtil::url($this->name, 'admin', 'view')));
    }

    /**
     * the main admin function
     *
     * @return RedirectResponse
     */
    public function mainAction()
    {
        // Security check will be done in view()
        return new RedirectResponse(System::normalizeUrl(ModUtil::url($this->name, 'admin', 'view')));
    }

    /**
     * view all themes
     *
     *      int    $startnum item number to start the pager from
     *      string $startlet starting letter for the alpha pager
     *                      }
     *
     * @return Response symfony response object
     *
     * @throws AccessDeniedException Thrown if the user doesn't have edit permissions to the module
     */
    public function viewAction()
    {
        // Security check
        if (!SecurityUtil::checkPermission('ZikulaThemeModule::', '::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        if (isset($this->container['multisites.enabled']) && $this->container['multisites.enabled'] == 1) {
            // only the main site can regenerate the themes list
            if ($this->container['multisites.mainsiteurl'] == $this->request->query->get('sitedns', null)) {
                //return true but any action has been made
                Util::regenerate();
            }
        } else {
            Util::regenerate();
        }

        // get our input
        $startnum = $this->request->query->get('startnum', 1);
        $startlet = $this->request->query->get('startlet', null);

        // we need this value multiple times, so we keep it
        $itemsperpage = $this->getVar('itemsperpage');

        // call the API to get a list of all themes in the themes dir
        $allthemes = ThemeUtil::getAllThemes(ThemeUtil::FILTER_ALL, ThemeUtil::STATE_ALL);

        // filter by letter if required
        if (isset($startlet) && !empty($startlet)) {
            $allthemes = $this->_filterbyletter($allthemes, $startlet);
        }

        $themes = array_slice($allthemes, $startnum - 1, $itemsperpage);

        $this->view->assign('themes', $themes);

        // assign default theme
        $this->view->assign('currenttheme', System::getVar('Default_Theme'));

        // assign the values for the pager plugin
        $this->view->assign('pager', array('numitems' => sizeof($allthemes),
            'itemsperpage' => $itemsperpage));

        // return the output that has been generated to the template
        return $this->response($this->view->fetch('Admin/view.tpl'));
    }

    /**
     * filter theme array by letter
     *
     * @param array $allthemes the list of themes to filter
     * @param string $startlet the starting letter for the filter
     *
     * @return array filtered themes array
     */
    private function _filterbyletter($allthemes, $startlet)
    {
        $themes = array();

        $startlet = strtolower($startlet);

        foreach ($allthemes as $key => $theme) {
            if (strtolower($theme['displayname'][0]) == $startlet) {
                $themes[$key] = $theme;
            }
        }

        return $themes;
    }

    /**
     * Check the running configuration of a theme
     *
     * @param array $themeinfo theme information array
     *
     * @return void
     */
    private function checkRunningConfig($themeinfo)
    {
        $theme = ThemeUtil::getTheme($themeinfo['name']);
        $ostemp = CacheUtil::getLocalDir();
        if ($theme) {
            $themePath = $theme->getRelativePath().'/Resources/config';
            $zpath  = $ostemp.'/Theme_Config/'.DataUtil::formatForOS($themeinfo['directory']);
            $tpath  = $themePath;
        } else {
            $zpath  = $ostemp.'/Theme_Config/'.DataUtil::formatForOS($themeinfo['directory']);
            $tpath  = 'themes/'.DataUtil::formatForOS($themeinfo['directory']).'/templates/config';
        }
        // check if we can edit the theme and, if not, create the running config
        if (!is_writable($tpath.'/pageconfigurations.ini')) {
            if (!file_exists($zpath) || is_writable($zpath)) {
                ModUtil::apiFunc('ZikulaThemeModule', 'admin', 'createrunningconfig', array('themename' => $themeinfo['name']));

                $this->request->getSession()->getFlashBag()->add('status', $this->__f('Notice: The changes made via Admin Panel will be saved on \'%1$s\' because it seems that the .ini files on \'%2$s\' are not writable.', array($zpath, $tpath)));
            } else {
                $this->request->getSession()->getFlashBag()->add('error', $this->__f('Error! Cannot write any configuration changes. Make sure that the .ini files on \'%1$s\' or \'%2$s\', and the folder itself, are writable.', array($tpath, $zpath)));
            }
        } else {
            $this->request->getSession()->getFlashBag()->add('status', $this->__f('Notice: Seems that your %1$s\'s .ini files are writable. Be sure that there are no .ini files on \'%2$s\' because if so, the Theme Engine will consider them and not your %1$s\'s ones.', array($themeinfo['name'], $zpath)));
        }

        $this->request->getSession()->getFlashBag()->add('status', $this->__f("If the system cannot write on any .ini file, the changes will be saved on '%s' and the Theme Engine will use it.", $zpath));
    }

    /**
     * modify a theme
     *
     *      string $themename name of the theme
     *
     * @return Response symfony response object
     *
     * @throws \InvalidArgumentException Thrown if themename isn't provided
     * @throws AccessDeniedException Thrown if the user doesn't have edit permissions over the theme
     */
    public function modifyAction()
    {
        // get our input
        $themename = $this->request->query->get('themename', null);

        // check our input
        if (!isset($themename) || empty($themename)) {
            throw new \InvalidArgumentException();
        }

        // Security check
        if (!SecurityUtil::checkPermission('ZikulaThemeModule::', "$themename::", ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        // get the theme info
        $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($themename));

        // check that we have writable files
        $this->checkRunningConfig($themeinfo);

        // assign theme name, theme info and return output
        return $this->response(
                $this->view->assign('themename', $themename)
                    ->assign('themeinfo', $themeinfo)
                    ->fetch('Admin/modify.tpl'));
    }

    /**
     * update the theme variables
     *
     *      string $themename name of the theme
     *      array  $themeinfo updated theme information
     *
     * @return RedirectResponse
     *
     * @throws \InvalidArgumentException Thrown if themename isn't provided
     * @throws AccessDeniedException Thrown if the user doesn't have edit permissions over the theme
     */
    public function updatesettingsAction()
    {
        $this->checkCsrfToken();

        // get our input
        $themeinfo = $this->request->request->get('themeinfo', null);
        $themename = $this->request->request->get('themename', null);

        // check our input
        if (!isset($themename) || empty($themename)) {
            throw new \InvalidArgumentException();
        }

        // Security check
        if (!SecurityUtil::checkPermission('ZikulaThemeModule::', "$themename::settings", ACCESS_EDIT)) {
            throw new AccessDeniedException();
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
        $updatesettings = ModUtil::apiFunc('ZikulaThemeModule', 'admin', 'updatesettings', array('theme' => $themename, 'themeinfo' => $newthemeinfo));
        if ($updatesettings) {
            $this->request->getSession()->getFlashBag()->add('status', $this->__('Done! Updated theme settings.'));
        }

        // redirect back to the variables page
        return new RedirectResponse(System::normalizeUrl(ModUtil::url($this->name, 'admin', 'view')));
    }

    /**
     * display the theme variables
     *
     *      string $themename name of the theme
     *      string $filename  name of the file to edit
     *
     * @return Response symfony response object
     *
     * @throws \InvalidArgumentException Thrown if themename isn't provided or doesn't exit
     * @throws AccessDeniedException Thrown if the user doesn't have edit permissions over the theme
     */
    public function variablesAction()
    {
        // get our input
        $themename = $this->request->query->get('themename', null);
        $filename = $this->request->query->get('filename', null);

        // check our input
        if (empty($themename)) {
            throw new \InvalidArgumentException();
        }

        $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($themename));
        $this->checkIfMainThemeFileExists($themeinfo);

        // Security check
        if (!SecurityUtil::checkPermission('ZikulaThemeModule::', "$themename::variables", ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        if ($filename) {
            $variables = ModUtil::apiFunc('ZikulaThemeModule', 'user', 'getpageconfiguration', array('theme' => $themename, 'filename' => $filename));
            $variables = ModUtil::apiFunc('ZikulaThemeModule', 'user', 'formatvariables', array('theme' => $themename, 'variables' => $variables, 'formatting' => true));
        } else {
            $variables = ModUtil::apiFunc('ZikulaThemeModule', 'user', 'getvariables', array('theme' => $themename, 'formatting' => true));
        }

        // load the language file
        ZLanguage::bindThemeDomain($themename);

        // check that we have writable files
        $this->checkRunningConfig($themeinfo);

        // assign variables, themename, themeinfo and return output
        return $this->response($this->view->assign('variables', $variables)
                ->assign('themename', $themename)
                ->assign('themeinfo', $themeinfo)
                ->assign('filename', $filename)
                ->fetch('Admin/variables.tpl'));
    }

    /**
     * update the theme variables
     *
     *      string $themename        name of the theme
     *      string $filename         name of the file to update
     *      array  $variablenames    names of existing variables
     *      array  $variablevalues   values for existing variables
     *      string $newvariablename  name of the new variable
     *      string $newvariablevalue value for the new variable
     *
     * @return RedirectResponse
     *
     * @throws \InvalidArgumentException Thrown if themename isn't provided or doesn't exist
     * @throws AccessDeniedException Thrown if the user doesn't have edit permissions over the theme
     */
    public function updatevariablesAction()
    {
        $this->checkCsrfToken();

        // get our input
        $variablesnames = $this->request->request->get('variablesnames', null);
        $variablesvalues = $this->request->request->get('variablesvalues', null);
        $newvariablename = $this->request->request->get('newvariablename', null);
        $newvariablevalue = $this->request->request->get('newvariablevalue', null);

        $themename = $this->request->request->get('themename', null);
        $filename = $this->request->request->get('filename', null);

        // check our input
        if (!isset($themename) || empty($themename)) {
            throw new \InvalidArgumentException();
        }

        $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($themename));
        if (!file_exists('themes/' . DataUtil::formatForOS($themeinfo['directory']). '/' . $themeinfo['name'] . '.php')) {
            throw new \InvalidArgumentException();
        }

        // Security check
        if (!SecurityUtil::checkPermission('ZikulaThemeModule::', "$themename::variables", ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        // get the original file source
        if ($filename) {
            $variables = ModUtil::apiFunc('ZikulaThemeModule', 'user', 'getpageconfiguration', array('theme' => $themename, 'filename' => $filename));
            $returnurl = ModUtil::url('ZikulaThemeModule', 'admin', 'variables', array('themename' => $themename, 'filename' => $filename));
        } else {
            $filename = 'themevariables.ini';
            $variables = ModUtil::apiFunc('ZikulaThemeModule', 'user', 'getvariables', array('theme' => $themename));
            $returnurl = ModUtil::url('ZikulaThemeModule', 'admin', 'variables', array('themename' => $themename));
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
        ModUtil::apiFunc('ZikulaThemeModule', 'user', 'writeinifile', array('theme' => $themename, 'assoc_arr' => $variables, 'has_sections' => true, 'file' => $filename));

        // set a status message
        $this->request->getSession()->getFlashBag()->add('status', $this->__('Done! Saved your changes.'));

        // redirect back to the variables page
        return new RedirectResponse(System::normalizeUrl($returnurl));
    }

    /**
     * display the themes palettes
     *
     *      string $themename name of the theme
     *
     * @return Response symfony response object
     *
     * @throws AccessDeniedException Thrown if the user doesn't have edit permissions over the theme
     */
    public function palettesAction()
    {
        // get our input
        $themename = $this->request->query->get('themename', null);

        // check our input
        if (!isset($themename) || empty($themename)) {
            throw new \InvalidArgumentException();
        }

        $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($themename));
        $this->checkIfMainThemeFileExists($themeinfo);

        // Security check
        if (!SecurityUtil::checkPermission('ZikulaThemeModule::', "$themename::colors", ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        // check that we have writable files
        $this->checkRunningConfig($themeinfo);

        // assign palettes, themename, themeinfo and return output
        return $this->response($this->view->assign('palettes', ModUtil::apiFunc('ZikulaThemeModule', 'user', 'getpalettes',
            array('theme' => $themename)))
                ->assign('themename', $themename)
                ->assign('themeinfo', $themeinfo)
                ->fetch('Admin/palettes.tpl'));
    }

    /**
     * update the theme palettes
     *
     *      string $themename name of the theme
     *      string $bgcolor   backgroud colour
     *      string $color1    colour 1
     *      string $color1    colour 2
     *      string $color1    colour 3
     *      string $color1    colour 4
     *      string $color1    colour 5
     *      string $color1    colour 6
     *      string $color1    colour 7
     *      string $color1    colour 8
     *      string $sepcolor  seperator colour
     *      string $link      link colour
     *      string $vlink     visited link colour
     *      string $hover     link hover colour
     *
     * @return RedirectResponse
     *
     * @throws \InvalidArgumentException Thrown if themename isn't provided or doesn't exist
     * @throws AccessDeniedException Thrown if the user doesn't have edit permissions over the theme
     * @throws \RuntimeException Thrown if the palette colours are incomplete
     */
    public function updatepalettesAction()
    {
        $this->checkCsrfToken();

        // get our input
        $palettes = $this->request->request->get('palettes', null);
        $palettename = $this->request->request->get('palettename', null);
        $bgcolor = $this->request->request->get('bgcolor', null);
        $color1 = $this->request->request->get('color1', null);
        $color2 = $this->request->request->get('color2', null);
        $color3 = $this->request->request->get('color3', null);
        $color4 = $this->request->request->get('color4', null);
        $color5 = $this->request->request->get('color5', null);
        $color6 = $this->request->request->get('color6', null);
        $color7 = $this->request->request->get('color7', null);
        $color8 = $this->request->request->get('color8', null);
        $sepcolor = $this->request->request->get('sepcolor', null);
        $link = $this->request->request->get('link', null);
        $vlink = $this->request->request->get('vlink', null);
        $hover = $this->request->request->get('hover', null);
        $themename = $this->request->request->get('themename', null);

        // check if this is a valid theme
        $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($themename));
        if (!file_exists('themes/' . DataUtil::formatForOS($themeinfo['directory']). '/' . $themeinfo['name'] . '.php')) {
            throw new \InvalidArgumentException();
        }

        // Security check
        if (!SecurityUtil::checkPermission('ZikulaThemeModule::', "$themename::palettes", ACCESS_EDIT)) {
            throw new AccessDeniedException();
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
                'sepcolor' => $sepcolor, 'link' => $link, 'vlink' => $vlink, 'hover' => $hover);
        } else {
            $this->request->getSession()->getFlashBag()->add('error', $this->__('Notice: Please make sure you type an entry in every field. Your palette cannot be saved if you do not.'));
            return new RedirectResponse(System::normalizeUrl(ModUtil::url($this->name, 'admin', 'palettes', array('themename' => $themename))));
        }

        // rewrite the settings to the running config
        ModUtil::apiFunc('ZikulaThemeModule', 'user', 'writeinifile', array('theme' => $themename, 'assoc_arr' => $palettes, 'has_sections' => true, 'file' => 'themepalettes.ini'));

        // set a status message
        $this->request->getSession()->getFlashBag()->add('status', $this->__('Done! Saved your changes.'));

        // redirect back to the settings page
        return new RedirectResponse(System::normalizeUrl(ModUtil::url($this->name, 'admin', 'palettes', array('themename' => $themename))));
    }

    /**
     * display the content wrappers for the theme
     *
     *      string $themename name of the theme
     *
     * @return Response symfony response object
     *
     * @throws AccessDeniedException Thrown if the user doesn't have edit permissions over the theme
     */
    public function pageconfigurationsAction()
    {
        // get our input
        $themename = $this->request->query->get('themename', null);

        // check our input
        if (!isset($themename) || empty($themename)) {
            throw new \InvalidArgumentException();
        }

        $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($themename));
        $this->checkIfMainThemeFileExists($themeinfo);

        // Security check
        if (!SecurityUtil::checkPermission('ZikulaThemeModule::', "$themename::pageconfigurations", ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        // assign an array to populate the modules dropdown
        $allmods = ModUtil::getAllMods();
        $mods = array();
        foreach ($allmods as $mod) {
            $mods[$mod['name']] = $mod['displayname'];
        }

        // assign the page configuration assignments
        $pageconfigurations = ModUtil::apiFunc('ZikulaThemeModule', 'user', 'getpageconfigurations', array('theme' => $themename));

        // defines the default types and master
        $pagetypes = array(
            'master' => $this->__('Master'),
            '*home' => $this->__('Homepage'),
            '*admin' => $this->__('Admin panel pages'),
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
        $existingconfigs = ModUtil::apiFunc('ZikulaThemeModule', 'user', 'getconfigurations', array('theme' => $themename));

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
        return $this->response($this->view->fetch('Admin/pageconfigurations.tpl'));
    }

    /**
     * modify a theme page configuration
     *
     *      string $themename name of the theme
     *      string $filename  name of the file to edit
     *
     * @return Response symfony response object
     *
     * @throws \InvalidArgumentException Thrown if themename isn't provided or doesn't exist
     * @throws AccessDeniedException Thrown if the user doesn't have edit permissions over the theme
     */
    public function modifypageconfigtemplatesAction()
    {
        // get our input
        $themename = $this->request->query->get('themename', null);
        $filename = $this->request->query->get('filename', null);

        // check our input
        if (empty($themename)) {
            throw new \InvalidArgumentException();
        }

        $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($themename));
        if (!file_exists('themes/' . DataUtil::formatForOS($themeinfo['directory']). '/' . $themeinfo['name'] . '.php')) {
            throw new \InvalidArgumentException();
        }

        // Security check
        if (!SecurityUtil::checkPermission('ZikulaThemeModule::', "$themename::pageconfigurations", ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        // read our configuration file
        $pageconfiguration = ModUtil::apiFunc('ZikulaThemeModule', 'user', 'getpageconfiguration', array('theme' => $themename, 'filename' => $filename));
        if (empty($pageconfiguration)) {
            throw new \InvalidArgumentException();
        }

        // get all block positions
        $blockpositions = ModUtil::apiFunc('ZikulaBlocksModule', 'user', 'getallpositions');
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
        $blocks = ModUtil::apiFunc('ZikulaBlocksModule', 'user', 'getall');
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
        return $this->response($this->view->assign('filename', $filename)
                ->assign('themename', $themename)
                ->assign('themeinfo', $themeinfo)
                ->assign('moduletemplates', ModUtil::apiFunc('ZikulaThemeModule', 'user', 'gettemplates', array('theme' => $themename)))
                ->assign('blocktemplates', ModUtil::apiFunc('ZikulaThemeModule', 'user', 'gettemplates', array('theme' => $themename, 'type' => 'blocks')))
                ->assign('palettes', ModUtil::apiFunc('ZikulaThemeModule', 'user', 'getpalettenames', array('theme' => $themename)))
                ->assign('blockpositions', $blockpositions)
                ->assign('allblocks', $allblocks)
                ->assign('blocks', $blocks)
                ->assign('pageconfiguration', $pageconfiguration)
                ->fetch('Admin/modifypageconfigtemplates.tpl'));
    }

    /**
     * modify a theme page configuration
     *
     *      string $themename              name of the theme
     *      string $filename               name of the file to update
     *      string $pagetemplate           file for the page template
     *      string $blocktemplate          file for the block template
     *      string $pagepalette            palette to apply to the page
     *      string $modulewrapper          wrapper to apply to modules
     *      string $blockwrapper           wrapper to apply to blocks
     *      array  $blockinstancetemplates templates for specific block instances
     *      array  $blocktypetemplates     templates for specific block types
     *      array  $blockpositiontemplates templates for specific block postions
     *
     * @return RedirectResponse
     *
     * @throws \InvalidArgumentException Thrown if themename isn't provided or doesn't exist or
     *                                          if pagetemplate isn't provided or
     *                                          if the requested page configuration doesn't exist
     * @throws AccessDeniedException Thrown if the user doesn't have edit permissions over the theme
     */
    public function updatepageconfigtemplatesAction()
    {
        $this->checkCsrfToken();

        // get our input
        $themename = $this->request->request->get('themename', null);
        $filename = $this->request->request->get('filename', null);
        $pagetemplate = $this->request->request->get('pagetemplate', '');
        $blocktemplate = $this->request->request->get('blocktemplate', '');
        $pagepalette = $this->request->request->get('pagepalette', '');
        $modulewrapper = $this->request->request->get('modulewrapper', 1);
        $blockwrapper = $this->request->request->get('blockwrapper', 1);

        $blockinstancetemplates = $this->request->request->get('blockinstancetemplates', null);
        $blocktypetemplates = $this->request->request->get('blocktypetemplates', null);
        $blockpositiontemplates = $this->request->request->get('blockpositiontemplates', null);

        $filters = $this->request->request->get('filters', null);

        // check our input
        if (empty($themename) || empty($pagetemplate)) {
            throw new \InvalidArgumentException();
        }

        $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($themename));
         if (!file_exists('themes/' . DataUtil::formatForOS($themeinfo['directory']). '/' . $themeinfo['name'] . '.php')) {
            throw new \InvalidArgumentException();
        }

        // Security check
        if (!SecurityUtil::checkPermission('ZikulaThemeModule::', "$themename::pageconfigurations", ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        // read our configuration file
        $pageconfiguration = ModUtil::apiFunc('ZikulaThemeModule', 'user', 'getpageconfiguration', array('theme' => $themename, 'filename' => $filename));
        if (empty($pageconfiguration)) {
            throw new \InvalidArgumentException();
        }

        // form the new page configuration
        $pageconfiguration['page'] = $pagetemplate;
        $pageconfiguration['block'] = $blocktemplate;
        $pageconfiguration['palette'] = $pagepalette;
        $pageconfiguration['modulewrapper'] = $modulewrapper;
        $pageconfiguration['blockwrapper'] = $blockwrapper;
        $pageconfiguration['blockinstances'] = array_filter($blockinstancetemplates);
        $pageconfiguration['blocktypes'] = array_filter($blocktypetemplates);
        $pageconfiguration['blockpositions'] = array_filter($blockpositiontemplates);

        // check if the filters exists. We do this now and not when using them to increase performance
        $filters['outputfilters'] = $this->_checkfilters('outputfilter', $filters['outputfilters']);
        $filters['prefilters'] = $this->_checkfilters('prefilter', $filters['prefilters']);
        $filters['postfilters'] = $this->_checkfilters('postfilter', $filters['postfilters']);
        $pageconfiguration['filters'] = $filters;

        // write the page configuration
        ModUtil::apiFunc('ZikulaThemeModule', 'user', 'writeinifile', array('theme' => $themename, 'assoc_arr' => $pageconfiguration, 'has_sections' => true, 'file' => $filename));

        // set a status message
        $this->request->getSession()->getFlashBag()->add('status', $this->__('Done! Saved your changes.'));

        // return the user to the correct place
        return new RedirectResponse(System::normalizeUrl(ModUtil::url($this->name, 'admin', 'pageconfigurations', array('themename' => $themename))));
    }

    /**
     * Check if the given filter exists
     *
     * @param string $type    type of filter
     * @param array  $filters array of filters
     *
     * @return string comma seperated list of filters
     * 
     * @throws \RuntimeException if filter has leftovers
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
            throw new \RuntimeException($this->__f('Error! Removed unknown \'%1$s\': \'%2$s\'.', array(DataUtil::formatForDisplay($type), DataUtil::formatForDisplay(implode(',', $leftover)))));
        }

        return implode(',', $newfilters);
    }

    /**
     * Modify a theme page configuration
     *
     *      string $themename name of the theme
     *      string $pcname    name of the page configuration to edit
     *
     * @return Response symfony response object
     *
     * @throws AccessDeniedException Thrown if the user doesn't have edit permissions over the theme
     */
    public function modifypageconfigurationassignmentAction()
    {
        // get our input
        $themename = $this->request->query->get('themename', null);
        $pcname = $this->request->query->get('pcname', null);

        // check our input
        if (!isset($themename) || empty($themename)) {
            throw new \InvalidArgumentException();
        }

        $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($themename));
        if (!file_exists('themes/' . DataUtil::formatForOS($themeinfo['directory']). '/' . $themeinfo['name'] . '.php')) {
            throw new \InvalidArgumentException();
        }

        // Security check
        if (!SecurityUtil::checkPermission('ZikulaThemeModule::', "$themename::pageconfigurations", ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        // assign all modules
        $allmods = ModUtil::getAllMods();
        $mods = array();
        foreach ($allmods as $mod) {
            $mods[$mod['name']] = $mod['name'];
        }

        // get all pageconfigurations
        $pageconfigurations = ModUtil::apiFunc('ZikulaThemeModule', 'user', 'getpageconfigurations', array('theme' => $themename));
        if (!isset($pageconfigurations[$pcname])) {
            throw new \InvalidArgumentException();
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
        $existingconfigs = ModUtil::apiFunc('ZikulaThemeModule', 'user', 'getconfigurations', array('theme' => $themename));

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
        return $this->response($this->view->fetch('Admin/modifypageconfigurationassignment.tpl'));
    }

    /**
     * modify a theme page configuration
     *
     *      string $themename      name of the theme
     *      string $pcname         name of the page configuration to update
     *      string $pagemodule     module to identify the page
     *      string $pagetype       type to identify the page
     *      string $pagefunc       function to identify the page
     *      string $pagecustomargs custom arugments to identify the page
     *      bool   $pageimportat   flag to override other matches
     *
     * @return RedirectResponse
     *
     * @throws \InvalidArgumentException Thrown if themename isn't provided or doesn't exist
     * @throws AccessDeniedException Thrown if the user doesn't have edit permissions over the theme
     */
    public function updatepageconfigurationassignmentAction()
    {
        $this->checkCsrfToken();

        // get our input
        $themename = $this->request->request->get('themename', null);
        $pcname = $this->request->request->get('pcname', null);
        $pagemodule = $this->request->request->get('pagemodule', null);
        $pagetype = $this->request->request->get('pagetype', 'user');
        $pagefunc = $this->request->request->get('pagefunc', null);
        $pagecustomargs = $this->request->request->get('pagecustomargs', null);
        $pageimportant = $this->request->request->get('pageimportant', null);
        $filename = $this->request->request->get('filename', null);

        // check our input
        if (!isset($themename) || empty($themename)) {
            throw new \InvalidArgumentException();
        }

        $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($themename));
        if (!file_exists('themes/' . DataUtil::formatForOS($themeinfo['directory']). '/' . $themeinfo['name'] . '.php')) {
            throw new \InvalidArgumentException();
        }

        // Security check
        if (!SecurityUtil::checkPermission('ZikulaThemeModule::', "$themename::pageconfigurations", ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        // read the list of existing page config assignments
        $pageconfigurations = ModUtil::apiFunc('ZikulaThemeModule', 'user', 'getpageconfigurations', array('theme' => $themename));

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
            $pos = array_search($pcname, $keys);
            $keys[$pos] = $newpageconfiguration;
            $pageconfigurations = array_combine($keys, array_values($pageconfigurations));
        }

        // fill the pageconfiguration info
        $pageconfigurations[$newpageconfiguration] = array('file' => $filename);

        if (isset($pageimportant)) {
            $pageconfigurations[$newpageconfiguration]['important'] = 1;
        }

        // write the page configurations back to the running config
        ModUtil::apiFunc('ZikulaThemeModule', 'user', 'writeinifile', array('theme' => $themename, 'assoc_arr' => $pageconfigurations, 'has_sections' => true, 'file' => 'pageconfigurations.ini'));

        // set a status message
        $this->request->getSession()->getFlashBag()->add('status', $this->__('Done! Saved your changes.'));

        // return the user to the correct place
        return new RedirectResponse(System::normalizeUrl(ModUtil::url($this->name, 'admin', 'pageconfigurations', array('themename' => $themename))));
    }

    /**
     * delete a theme page configuration assignment
     *
     *      string $themename    name of the theme
     *      string $pcname       name of the page configuration to edit
     *      bool   $confirmation conformation to delete the page configuration
     *
     * @return Response symfony response object if confirmation isn't provided
     *
     * @throws \InvalidArgumentException Thrown if themename isn't provided or doesn't exist
     * @throws AccessDeniedException Thrown if the user doesn't have delete permissions over the theme
     */
    public function deletepageconfigurationassignmentAction()
    {
        $themename = $this->request->query->get('themename', null);
        $pcname = $this->request->query->get('pcname', null);
        $confirmation = $this->request->request->get('confirmation', null);

        // Get the theme info
        $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($themename));

        if ($themeinfo == false) {
            throw new \InvalidArgumentException();
        }

        // Security check
        if (!SecurityUtil::checkPermission('ZikulaThemeModule::', "$themename::pageconfigurations", ACCESS_DELETE)) {
            throw new AccessDeniedException();
        }

        // Check for confirmation.
        if (empty($confirmation)) {
            // No confirmation yet
            // Assign the theme info
            $this->view->assign($themeinfo);

            // Assign the page configuration name
            $this->view->assign('pcname', $pcname);

            // Return the output that has been generated by this function
            return $this->response($this->view->fetch('Admin/deletepageconfigurationassignment.tpl'));
        }

        // If we get here it means that the user has confirmed the action
        $this->checkCsrfToken();

        // Delete the admin message
        // The return value of the function is checked
        if (ModUtil::apiFunc('ZikulaThemeModule', 'admin', 'deletepageconfigurationassignment', array('themename' => $themename, 'pcname' => $pcname))) {
            // Success
            $this->request->getSession()->getFlashBag()->add('status', $this->__('Done! Deleted it.'));
        }

        // return the user to the correct place
        return new RedirectResponse(System::normalizeUrl(ModUtil::url($this->name, 'admin', 'pageconfigurations', array('themename' => $themename))));
    }

    /**
     * display the theme credits
     *
     *      string $themename name of the theme
     *
     * @return Response symfony response object
     *
     * @throws \InvalidArgumentException Thrown if themename isn't provided or doesn't exist
     * @throws AccessDeniedException Thrown if the user doesn't have edit permissions over the theme
     */
    public function creditsAction()
    {
        // get our input
        $themename = $this->request->query->get('themename', null);

        // check our input
        if (!isset($themename) || empty($themename)) {
            throw new \InvalidArgumentException();
        }

        // Security check
        if (!SecurityUtil::checkPermission('ZikulaThemeModule::', "$themename::credits", ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        // assign the theme info and return output
        return $this->response($this->view->assign('themeinfo', ThemeUtil::getInfo(ThemeUtil::getIDFromName($themename)))
                ->fetch('Admin/credits.tpl'));
    }

    /**
     * set theme as default for site
     *
     *      string $themename         name of the theme
     *      string $confirmation      confirmation to set theme as default
     *      bool   $resetuserselected reset any user chosen themes back to site default
     *
     * @return Response symfony response object if confirmation isn't provided
     *
     * @throws \InvalidArgumentException Thrown if themename isn't provided or doesn't exist
     * @throws AccessDeniedException Thrown if the user doesn't have admin permissions over the module
     */
    public function setasdefaultAction()
    {
        // get our input
        $themename = $this->request->query->get('themename', null);
        $confirmation = (boolean)$this->request->request->get('confirmation', false);
        $resetuserselected = $this->request->request->get('resetuserselected', null);

        // check our input
        if (!isset($themename) || empty($themename)) {
            throw new \InvalidArgumentException();
        }

        // Security check
        if (!SecurityUtil::checkPermission('ZikulaThemeModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        // Check for confirmation.
        if (empty($confirmation)) {
            // No confirmation yet
            // Add a hidden field for the item ID to the output
            $this->view->assign('themename', $themename);

            // assign the var defining if users can change themes
            $this->view->assign('theme_change', System::getVar('theme_change'));

            // Return the output that has been generated by this function
            return $this->response($this->view->fetch('Admin/setasdefault.tpl'));
        }

        // If we get here it means that the user has confirmed the action
        $this->checkCsrfToken();

        // Set the default theme
        if (ModUtil::apiFunc('ZikulaThemeModule', 'admin', 'setasdefault', array('themename' => $themename, 'resetuserselected' => $resetuserselected))) {
            // Success
            $this->request->getSession()->getFlashBag()->add('status', $this->__('Done! Changed default theme.'));
        }

        return new RedirectResponse(System::normalizeUrl(ModUtil::url($this->name, 'admin', 'view')));
    }

    /**
     * delete a theme
     *
     *      string $themename         name of the theme
     *      string $confirmation      confirmation to set theme as default
     *
     * @return Response symfony response object if confirmation isn't provided
     *
     * @throws \InvalidArgumentException Thrown if themename isn't provided or doesn't exist
     * @throws AccessDeniedException Thrown if the user doesn't have delete permissions over the module
     */
    public function deleteAction()
    {
        $themename = $this->request->query->get('themename', null);
        $confirmation = $this->request->request->get('confirmation', null);

        // Get the theme info
        $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($themename));

        if ($themeinfo == false) {
            throw new NotFoundHttpException($this->__('Sorry! No such theme found.'), null, 404);
        }

        // Security check
        if (!SecurityUtil::checkPermission('ZikulaThemeModule::', "$themename::", ACCESS_DELETE)) {
            throw new AccessDeniedException();
        }

        // Check for confirmation.
        if (empty($confirmation)) {
            // No confirmation yet
            // Add the message id
            $this->view->assign($themeinfo);

            // Return the output that has been generated by this function
            return $this->response($this->view->fetch('Admin/delete.tpl'));
        }

        // If we get here it means that the user has confirmed the action
        $this->checkCsrfToken();

        $deletefiles = $this->request->request->get('deletefiles', 0);

        // Delete the admin message
        // The return value of the function is checked
        if (ModUtil::apiFunc('ZikulaThemeModule', 'admin', 'delete', array('themename' => $themename, 'deletefiles' => $deletefiles))) {
            // Success
            $this->request->getSession()->getFlashBag()->add('status', $this->__('Done! Deleted it.'));
        }

        // This function generated no output, and so now it is complete we redirect
        // the user to an appropriate page for them to carry on their work
        return new RedirectResponse(System::normalizeUrl(ModUtil::url($this->name, 'admin', 'view')));
    }

    /**
     * Modify Theme module settings
     *
     * @return Response symfony response object if confirmation isn't provided
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin permissions over the module
     */
    public function modifyconfigAction()
    {
        // Security check
        if (!SecurityUtil::checkPermission('ZikulaThemeModule::', '::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        // assign a list of modules suitable for html_options
        $usermods = ModUtil::getModulesCapableOf('user');
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
        return $this->response($this->view->assign('mods', $mods)
                // assign all module vars
                ->assign($this->getVars())
                // assign admintheme var
                ->assign('admintheme', ModUtil::getVar('Admin', 'admintheme', ''))
                // assign an csrftoken for the clear cache/compile links
                ->assign('csrftoken', SecurityUtil::generateCsrfToken($this->container, true))
                // assign the core config var
                ->assign('theme_change', System::getVar('theme_change'))
                // extracted list of non-cached mods
                ->assign('modulesnocache', array_flip(explode(',', $this->getVar('modulesnocache'))))
                ->fetch('Admin/modifyconfig.tpl'));
    }

    /**
     * Update configuration
     *
     *      bool   $enablecache            name of the theme
     *      string $modulesnocache         confirmation to set theme as default
     *      bool   $compile_check          reset any user chosen themes back to site default
     *      int    $cache_lifetime         time to cache theme elements
     *      string $cache_lifetime_mods    modules to override caching for
     *      bool   $force_compile          force compilation of theme templates
     *      bool   $trimwhitespace         trimwhitespace from templates
     *      int    $maxsizeforlinks        maxmimum size for link text
     *      bool   $theme_change           allow users to change themes
     *      string $admintheme             admin theme for site
     *      string $alt_theme_name         name of alternate theme
     *      string $alt_theme_domain       domain to use when forcing alternate themes
     *      int    $itemsperpage           items per page in admin view
     *      bool   $cssjsscombine          enable combination of all css and js files
     *      bool   $cssjssminify           minify css files
     *      bool   $cssjsscompress         compress combined files
     *      int    $cssjsscombine_lifetime lifetime to cache combined files
     *      bool   $render_compile_check   check for new render templates
     *      bool   $render_force_compile   force compile render templates
     *      bool   $render_cache           enable render caching
     *      int    $render_lifetime        lifetime to cache render templates
     *      bool   $render_expose_template expose template filenames in source
     *
     * @return RedirectResponse
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin permissions over the module
     */
    public function updateconfigAction()
    {
        $this->checkCsrfToken();

        // security check
        if (!SecurityUtil::checkPermission('ZikulaThemeModule::', '::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        // check if the theme cache was disabled and clean it if so
        $enablecache = (bool)$this->request->request->get('enablecache', false);

        if ($this->getVar('enablecache') && !$enablecache) {
            $theme = Zikula_View_Theme::getInstance();
            $theme->clear_all_cache();
        }

        // set our module variables
        $this->setVar('enablecache', $enablecache);

        $modulesnocache = $this->request->request->get('modulesnocache', array());
        $modulesnocache = implode(',', $modulesnocache);
        $this->setVar('modulesnocache', $modulesnocache);

        $compile_check = (bool)$this->request->request->get('compile_check', false);
        $this->setVar('compile_check', $compile_check);

        $cache_lifetime = (int)$this->request->request->get('cache_lifetime', 3600);
        if ($cache_lifetime < -1) {
            $cache_lifetime = 3600;
        }
        $this->setVar('cache_lifetime', $cache_lifetime);

        $cache_lifetime_mods = (int)$this->request->request->get('cache_lifetime_mods', 3600);
        if ($cache_lifetime_mods < -1) $cache_lifetime_mods = 3600;
        $this->setVar('cache_lifetime_mods', $cache_lifetime_mods);

        $force_compile = (bool)$this->request->request->get('force_compile', false);
        $this->setVar('force_compile', $force_compile);

        $trimwhitespace = (bool)$this->request->request->get('trimwhitespace', false);
        $this->setVar('trimwhitespace', $trimwhitespace);

        $maxsizeforlinks = (int)$this->request->request->get('maxsizeforlinks', 30);
        $this->setVar('maxsizeforlinks', $maxsizeforlinks);

        $theme_change = (bool)$this->request->request->get('theme_change', false);
        System::setVar('theme_change', $theme_change);

        $admintheme = (string)$this->request->request->get('admintheme', '');
        ModUtil::setVar('Admin', 'admintheme', $admintheme);

        $alt_theme_name = (string)$this->request->request->get('alt_theme_name', '');
        $this->setVar('alt_theme_name', $alt_theme_name);

        $alt_theme_domain = (string)$this->request->request->get('alt_theme_domain', '');
        $this->setVar('alt_theme_domain', $alt_theme_domain);

        $itemsperpage = (int)$this->request->request->get('itemsperpage', 25);
        if ($itemsperpage < 1) {
            $itemsperpage = 25;
        }
        $this->setVar('itemsperpage', $itemsperpage);

        $cssjscombine = (bool)$this->request->request->get('cssjscombine', false);
        $this->setVar('cssjscombine', $cssjscombine);

        $cssjsminify = (bool)$this->request->request->get('cssjsminify', false);
        $this->setVar('cssjsminify', $cssjsminify);

        $cssjscompress = (bool)$this->request->request->get('cssjscompress', false);
        $this->setVar('cssjscompress', $cssjscompress);

        $cssjscombine_lifetime = (int)$this->request->request->get('cssjscombine_lifetime', 3600);
        if ($cssjscombine_lifetime < -1) {
            $cssjscombine_lifetime = 3600;
        }
        $this->setVar('cssjscombine_lifetime', $cssjscombine_lifetime);


        // render
        $render_compile_check = (bool)$this->request->request->get('render_compile_check', false);
        $this->setVar('render_compile_check', $render_compile_check);

        $render_force_compile = (bool)$this->request->request->get('render_force_compile', false);
        $this->setVar('render_force_compile', $render_force_compile);

        $render_cache = (int)$this->request->request->get('render_cache', false);
        $this->setVar('render_cache', $render_cache);

        $render_lifetime = (int)$this->request->request->get('render_lifetime', 3600);
        if ($render_lifetime < -1) {
            $render_lifetime = 3600;
        }
        $this->setVar('render_lifetime', $render_lifetime);

        $render_expose_template = (bool)$this->request->request->get('render_expose_template', false);
        $this->setVar('render_expose_template', $render_expose_template);

        // The configuration has been changed, so we clear all caches for this module.
        $this->view->clear_compiled();
        $this->view->clear_all_cache();

        // the module configuration has been updated successfuly
        $this->request->getSession()->getFlashBag()->add('status', $this->__('Done! Saved module configuration.'));

        return new RedirectResponse(System::normalizeUrl(ModUtil::url($this->name, 'admin', 'modifyconfig')));
    }

    /**
     * Clear theme engine compiled templates
     *
     * Using this function, the admin can clear all theme engine compiled
     * templates for the system.
     *
     * @return RedirectResponse
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin access over the module
     */
    public function clear_compiledAction()
    {
        $csrftoken = $this->request->query->get('csrftoken');
        $this->checkCsrfToken($csrftoken);

        // Security check
        if (!SecurityUtil::checkPermission('ZikulaThemeModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $theme = Zikula_View_Theme::getInstance();
        $res = $theme->clear_compiled();

        if ($res) {
            $this->request->getSession()->getFlashBag()->add('status', $this->__('Done! Deleted theme engine compiled templates.'));
        } else {
            $this->request->getSession()->getFlashBag()->add('error', $this->__('Error! Failed to clear theme engine compiled templates.'));
        }

        return new RedirectResponse(System::normalizeUrl(ModUtil::url($this->name, 'admin', 'modifyconfig')));
    }

    /**
     * Clear theme engine cached templates
     *
     * Using this function, the admin can clear all theme engine cached
     * templates for the system.
     *
     * @return RedirectResponse
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin access over the module
     */
    public function clear_cacheAction()
    {
        $csrftoken = $this->request->query->get('csrftoken');
        $this->checkCsrfToken($csrftoken);

        // Security check
        if (!SecurityUtil::checkPermission('ZikulaThemeModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $cacheid = $this->request->get('cacheid');

        $theme = Zikula_View_Theme::getInstance();
        $res = $theme->clear_all_cache();

        if ($cacheid) {
            // clear cache for all active themes
            $themesarr = ThemeUtil::getAllThemes();
            foreach ($themesarr as $themearr) {
                $themedir = $themearr['directory'];
                $res = $theme->clear_cache(null, $cacheid, null, null, $themedir);
                if ($res) {
                    $this->request->getSession()->getFlashBag()->add('status', $this->__('Done! Deleted theme engine cached templates.').' '.$cacheid.', '.$themedir);
                } else {
                    $this->request->getSession()->getFlashBag()->add('error', $this->__('Error! Failed to clear theme engine cached templates.').' '.$cacheid.', '.$themedir);
                }
            }
        } else {
            // this clear all cache for all themes
            $res = $theme->clear_all_cache();
            if ($res) {
                $this->request->getSession()->getFlashBag()->add('status', $this->__('Done! Deleted theme engine cached templates.'));
            } else {
                $this->request->getSession()->getFlashBag()->add('error', $this->__('Error! Failed to clear theme engine cached templates.'));
            }
        }

        return new RedirectResponse(System::normalizeUrl(ModUtil::url($this->name, 'admin', 'modifyconfig')));
    }

    /**
     * Clear CSS/JS combination cached files
     *
     * Using this function, the admin can clear all CSS/JS combination cached
     * files for the system.
     *
     * @return RedirectResponse
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin access over the module
     */
    public function clear_cssjscombinecacheAction()
    {
        $csrftoken = $this->request->query->get('csrftoken');
        $this->checkCsrfToken($csrftoken);

        // Security check
        if (!SecurityUtil::checkPermission('ZikulaThemeModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $theme = Zikula_View_Theme::getInstance();
        $theme->clear_cssjscombinecache();

        $this->request->getSession()->getFlashBag()->add('status', $this->__('Done! Deleted CSS/JS combination cached files.'));
        return new RedirectResponse(System::normalizeUrl(ModUtil::url($this->name, 'admin', 'modifyconfig')));
    }

    /**
     * Clear theme engine configurations
     *
     * Using this function, the admin can clear all theme engine configuration
     * copies created inside the temporary directory.
     *
     * @return RedirectResponse
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin access over the module
     */
    public function clear_configAction()
    {
        $csrftoken = $this->request->query->get('csrftoken');
        $this->checkCsrfToken($csrftoken);

        // Security check
        if (!SecurityUtil::checkPermission('ZikulaThemeModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $theme = Zikula_View_Theme::getInstance();
        $res = $theme->clear_theme_config();

        if ($res) {
            $this->request->getSession()->getFlashBag()->add('status', $this->__('Done! Deleted theme engine configurations.'));
        } else {
            $this->request->getSession()->getFlashBag()->add('error', $this->__('Error! Failed to clear theme engine configurations.'));
        }

        return new RedirectResponse(System::normalizeUrl(ModUtil::url($this->name, 'admin', 'modifyconfig')));
    }

    /**
     * Clear render compiled templates
     *
     * Using this function, the admin can clear all render compiled templates
     * for the system.
     *
     * @return RedirectResponse
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin access over the module
     */
    public function render_clear_compiledAction()
    {
        $csrftoken = $this->request->query->get('csrftoken');
        $this->checkCsrfToken($csrftoken);

        // Security check
        if (!SecurityUtil::checkPermission('ZikulaThemeModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $res = $this->view->clear_compiled();

        if ($res) {
            $this->request->getSession()->getFlashBag()->add('status', $this->__('Done! Deleted rendering engine compiled templates.'));
        } else {
            $this->request->getSession()->getFlashBag()->add('error', $this->__('Error! Failed to clear rendering engine compiled templates.'));
        }

        return new RedirectResponse(System::normalizeUrl(ModUtil::url($this->name, 'admin', 'modifyconfig')));
    }

    /**
     * Clear render cached templates
     *
     * Using this function, the admin can clear all render cached templates
     * for the system.
     *
     * @return RedirectResponse
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin access over the module
     */
    public function render_clear_cacheAction()
    {
        $csrftoken = $this->request->query->get('csrftoken');
        $this->checkCsrfToken($csrftoken);

        // Security check
        if (!SecurityUtil::checkPermission('ZikulaThemeModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $res = $this->view->clear_all_cache();

        if ($res) {
            $this->request->getSession()->getFlashBag()->add('status', $this->__('Done! Deleted rendering engine cached pages.'));
        } else {
            $this->request->getSession()->getFlashBag()->add('error', $this->__('Error! Failed to clear rendering engine cached pages.'));
        }

        return new RedirectResponse(System::normalizeUrl(ModUtil::url($this->name, 'admin', 'modifyconfig')));
    }

    /**
     * Clear all cache and compile directories
     *
     * Using this function, the admin can clear all theme and render cached,
     * compiled and combined files for the system.
     *
     * @return RedirectResponse
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin access over the module
     */
    public function clearallcompiledcachesAction()
    {
        // Security check
        if (!SecurityUtil::checkPermission('ZikulaThemeModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        ModUtil::apiFunc('ZikulaSettingsModule', 'admin', 'clearallcompiledcaches');

        $this->request->getSession()->getFlashBag()->add('status', $this->__('Done! Cleared all cache and compile directories.'));
        return new RedirectResponse(System::normalizeUrl(ModUtil::url($this->name, 'admin', 'modifyconfig')));
    }

    /**
     * Check if theme name isn't provided or doesn't exist
     *
     * @param $themeinfo
     *
     * @throws \InvalidArgumentException Thrown if theme name isn't provided or doesn't exist
     */
    private function checkIfMainThemeFileExists($themeinfo)
    {
        $mainThemeFile = 'themes/' . DataUtil::formatForOS($themeinfo['directory']). '/' . $themeinfo['name'] . '.php';
        $mainThemeFileLegacy = 'themes/'.DataUtil::formatForOS($themeinfo['directory']).'/version.php';
        if (!file_exists($mainThemeFile) && !file_exists($mainThemeFileLegacy)) {
            throw new \InvalidArgumentException();
        }
    }

}
