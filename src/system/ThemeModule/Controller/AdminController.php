<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ThemeModule\Controller;

use BlockUtil;
use CacheUtil;
use DataUtil;
use Modutil;
use SecurityUtil;
use System;
use ThemeUtil;
use Zikula_View;
use Zikula_View_Theme;
use ZLanguage;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Routing\RouterInterface;
use Zikula\Core\Controller\AbstractController;

/**
 * @deprecated remove at Core-2.0
 * @Route("/admin")
 *
 * administrative controllers for the theme module
 */
class AdminController extends AbstractController
{
    /**
     * @Route("")
     *
     * the main admin function
     *
     * @return RedirectResponse
     */
    public function indexAction()
    {
        // Security check will be done in view()
        return $this->redirectToRoute('zikulathememodule_theme_view');
    }

    /**
     * Route not needed here because method is legacy-only
     *
     * the main admin function
     *
     * @deprecated at 1.4.0 @see indexAction()
     *
     * @return RedirectResponse
     */
    public function mainAction()
    {
        // Security check will be done in view()
        return $this->redirectToRoute('zikulathememodule_theme_view');
    }

    /**
     * Check the running configuration of a theme
     *
     * @param Request $request
     * @param array $themeinfo theme information array
     *
     * @return void
     */
    private function checkRunningConfig(Request $request, $themeinfo)
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
                ModUtil::apiFunc('ZikulaThemeModule', 'admin', 'createrunningconfig', ['themename' => $themeinfo['name']]);

                $this->addFlash('status', $this->__f('Notice: The changes made via Admin Panel will be saved on \'%zpath%\' because it seems that the .ini files on \'%tpath%\' are not writable.', ['%zpath%' => $zpath, '%tpath%' => $tpath]));
            } else {
                $this->addFlash('error', $this->__f('Error! Cannot write any configuration changes. Make sure that the .ini files on \'%tpath%\' or \'%zpath%\', and the folder itself, are writable.', ['%tpath%' => $tpath, '%zpath%' => $zpath]));
            }
        } else {
            $this->addFlash('status', $this->__f('Notice: Seems that your %theme%\'s .ini files are writable. Be sure that there are no .ini files on \'%zpath%\' because if so, the Theme Engine will consider them and not your %theme%\'s ones.', ['%theme%' => $themeinfo['name'], '%zpath%' => $zpath]));
        }

        $this->addFlash('status', $this->__f('If the system cannot write on any .ini file, the changes will be saved on \'%zpath%\' and the Theme Engine will use it.', ['%zpath%' => $zpath]));
    }

    /**
     * @Route("/modify/{themename}")
     * @Method("GET")
     *
     * modify a theme
     *
     * @param Request $request
     *      string $themename name of the theme
     *
     * @return Response symfony response object
     *
     * @throws AccessDeniedException Thrown if the user doesn't have edit permissions over the theme
     */
    public function modifyAction(Request $request, $themename)
    {
        // Security check
        if (!$this->hasPermission('ZikulaThemeModule::', $themename . '::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        // get the theme info
        $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($themename));

        // check that we have writable files
        $this->checkRunningConfig($request, $themeinfo);

        $legacyView = Zikula_View::getInstance('ZikulaThemeModule');

        return new Response(
            $legacyView
                ->assign('themename', $themename)
                ->assign('themeinfo', $themeinfo)
                ->fetch('Admin/modify.tpl')
        );
    }

    /**
     * @Route("/modify")
     * @Method("POST")
     *
     * update the theme variables
     *
     * @param Request $request
     *      string $themename name of the theme
     *      array  $themeinfo updated theme information
     *
     * @return RedirectResponse
     *
     * @throws \InvalidArgumentException Thrown if themename isn't provided
     * @throws AccessDeniedException Thrown if the user doesn't have edit permissions over the theme
     */
    public function updatesettingsAction(Request $request)
    {
        $token = $request->request->get('csrftoken', null);
        $this->get('zikula_core.common.csrf_token_handler')->validate($token);

        // get our input
        $themeinfo = $request->request->get('themeinfo', null);
        $themename = $request->request->get('themename', null);

        // check our input
        if (!isset($themename) || empty($themename)) {
            throw new \InvalidArgumentException();
        }

        // Security check
        if (!$this->hasPermission('ZikulaThemeModule::', $themename . '::settings', ACCESS_EDIT)) {
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
        $updatesettings = ModUtil::apiFunc('ZikulaThemeModule', 'admin', 'updatesettings', ['theme' => $themename, 'themeinfo' => $newthemeinfo]);
        if ($updatesettings) {
            $this->addFlash('status', $this->__('Done! Updated theme settings.'));
        }

        return $this->redirectToRoute('zikulathememodule_theme_view');
    }

    /**
     * @Route("/variables/{themename}/{filename}")
     *
     * display the theme variables
     *
     * @param Request $request
     * @param string $themename name of the theme
     * @param string $filename  name of the file to edit
     *
     * @return Response symfony response object
     *
     * @throws AccessDeniedException Thrown if the user doesn't have edit permissions over the theme
     */
    public function variablesAction(Request $request, $themename, $filename = null)
    {
        $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($themename));
        $this->checkIfMainThemeFileExists($themeinfo);

        // Security check
        if (!$this->hasPermission('ZikulaThemeModule::', $themename . '::variables', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        if ($filename) {
            $variables = ModUtil::apiFunc('ZikulaThemeModule', 'user', 'getpageconfiguration', ['theme' => $themename, 'filename' => $filename]);
            $variables = ModUtil::apiFunc('ZikulaThemeModule', 'user', 'formatvariables', ['theme' => $themename, 'variables' => $variables, 'formatting' => true]);
        } else {
            $variables = ModUtil::apiFunc('ZikulaThemeModule', 'user', 'getvariables', ['theme' => $themename, 'formatting' => true]);
        }

        // load the language file
        ZLanguage::bindThemeDomain($themename);

        // check that we have writable files
        $this->checkRunningConfig($request, $themeinfo);

        $legacyView = Zikula_View::getInstance('ZikulaThemeModule');

        return new Response(
            $legacyView
                ->assign('variables', $variables)
                ->assign('themename', $themename)
                ->assign('themeinfo', $themeinfo)
                ->assign('filename', $filename)
                ->fetch('Admin/variables.tpl')
        );
    }

    /**
     * @Route("/variables")
     * @Method("POST")
     *
     * update the theme variables
     *
     * @param Request $request
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
    public function updatevariablesAction(Request $request)
    {
        $token = $request->request->get('csrftoken', null);
        $this->get('zikula_core.common.csrf_token_handler')->validate($token);

        // get our input
        $variablesnames = $request->request->get('variablesnames', null);
        $variablesvalues = $request->request->get('variablesvalues', null);
        $newvariablename = $request->request->get('newvariablename', null);
        $newvariablevalue = $request->request->get('newvariablevalue', null);
        $themename = $request->request->get('themename', null);
        $filename = $request->request->get('filename', null);

        // check our input
        if (!isset($themename) || empty($themename)) {
            throw new \InvalidArgumentException();
        }

        $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($themename));
        $this->checkIfMainThemeFileExists($themeinfo);

        // Security check
        if (!$this->hasPermission('ZikulaThemeModule::', $themename . '::variables', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        // get the original file source
        if ($filename) {
            $variables = ModUtil::apiFunc('ZikulaThemeModule', 'user', 'getpageconfiguration', ['theme' => $themename, 'filename' => $filename]);
            $returnurl = $this->get('router')->generate('zikulathememodule_admin_variables', ['themename' => $themename, 'filename' => $filename], RouterInterface::ABSOLUTE_URL);
        } else {
            $filename = 'themevariables.ini';
            $variables = ModUtil::apiFunc('ZikulaThemeModule', 'user', 'getvariables', ['theme' => $themename]);
            $returnurl = $this->get('router')->generate('zikulathememodule_admin_variables', ['themename' => $themename], RouterInterface::ABSOLUTE_URL);
        }

        // form our existing variables
        $newvariables = [];
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
        ModUtil::apiFunc('ZikulaThemeModule', 'user', 'writeinifile', ['theme' => $themename, 'assoc_arr' => $variables, 'has_sections' => true, 'file' => $filename]);

        // set a status message
        $this->addFlash('status', $this->__('Done! Saved your changes.'));

        return new RedirectResponse($returnurl);
    }

    /**
     * @Route("/palettes/{themename}")
     * @Method("GET")
     *
     * display the themes palettes
     *
     * @param Request $request
     * @param string $themename name of the theme
     *
     * @return Response symfony response object
     *
     * @throws AccessDeniedException Thrown if the user doesn't have edit permissions over the theme
     */
    public function palettesAction(Request $request, $themename)
    {
        $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($themename));
        $this->checkIfMainThemeFileExists($themeinfo);

        // Security check
        if (!$this->hasPermission('ZikulaThemeModule::', $themename . '::colors', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        // check that we have writable files
        $this->checkRunningConfig($request, $themeinfo);

        $legacyView = Zikula_View::getInstance('ZikulaThemeModule');

        // assign palettes, themename, themeinfo and return output
        return new Response(
            $legacyView
                ->assign('palettes', ModUtil::apiFunc('ZikulaThemeModule', 'user', 'getpalettes', ['theme' => $themename]))
                ->assign('themename', $themename)
                ->assign('themeinfo', $themeinfo)
                ->fetch('Admin/palettes.tpl')
        );
    }

    /**
     * @Route("/palettes")
     * @Method("POST")
     *
     * update the theme palettes
     *
     * @param Request $request
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
    public function updatepalettesAction(Request $request)
    {
        $token = $request->request->get('csrftoken', null);
        $this->get('zikula_core.common.csrf_token_handler')->validate($token);

        // get our input
        $palettes = $request->request->get('palettes', null);
        $palettename = $request->request->get('palettename', null);
        $bgcolor = $request->request->get('bgcolor', null);
        $color1 = $request->request->get('color1', null);
        $color2 = $request->request->get('color2', null);
        $color3 = $request->request->get('color3', null);
        $color4 = $request->request->get('color4', null);
        $color5 = $request->request->get('color5', null);
        $color6 = $request->request->get('color6', null);
        $color7 = $request->request->get('color7', null);
        $color8 = $request->request->get('color8', null);
        $sepcolor = $request->request->get('sepcolor', null);
        $link = $request->request->get('link', null);
        $vlink = $request->request->get('vlink', null);
        $hover = $request->request->get('hover', null);
        $themename = $request->request->get('themename', null);

        // check if this is a valid theme
        $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($themename));
        $this->checkIfMainThemeFileExists($themeinfo);

        // Security check
        if (!$this->hasPermission('ZikulaThemeModule::', $themename . '::palettes', ACCESS_EDIT)) {
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
            $palettes[$palettename] = ['bgcolor' => $bgcolor, 'color1' => $color1, 'color2' => $color2, 'color3' => $color3,
                'color4' => $color4, 'color5' => $color5, 'color6' => $color6, 'color7' => $color7, 'color8' => $color8,
                'sepcolor' => $sepcolor, 'link' => $link, 'vlink' => $vlink, 'hover' => $hover];
        } else {
            $this->addFlash('error', $this->__('Notice: Please make sure you type an entry in every field. Your palette cannot be saved if you do not.'));

            return $this->redirectToRoute('zikulathememodule_admin_palettes', ['themename' => $themename]);
        }

        // rewrite the settings to the running config
        ModUtil::apiFunc('ZikulaThemeModule', 'user', 'writeinifile', ['theme' => $themename, 'assoc_arr' => $palettes, 'has_sections' => true, 'file' => 'themepalettes.ini']);

        // set a status message
        $this->addFlash('status', $this->__('Done! Saved your changes.'));

        return $this->redirectToRoute('zikulathememodule_admin_palettes', ['themename' => $themename]);
    }

    /**
     * @Route("/pageconfig/{themename}")
     * @Method("GET")
     *
     * display the content wrappers for the theme
     *
     * @param Request $request
     * @param string $themename name of the theme
     *
     * @return Response symfony response object
     *
     * @throws AccessDeniedException Thrown if the user doesn't have edit permissions over the theme
     */
    public function pageconfigurationsAction(Request $request, $themename)
    {
        $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($themename));
        $themename = $themeinfo['name'];
        $theme = ThemeUtil::getTheme($themename);

        $this->checkIfMainThemeFileExists($themeinfo);

        // Security check
        if (!$this->hasPermission('ZikulaThemeModule::', $themename . '::pageconfigurations', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        // assign an array to populate the modules dropdown
        $allmods = ModUtil::getAllMods();
        $mods = [];
        foreach ($allmods as $mod) {
            $mods[$mod['name']] = $mod['displayname'];
        }

        // assign the page configuration assignments
        $pageconfigurations = ModUtil::apiFunc('ZikulaThemeModule', 'user', 'getpageconfigurations', ['theme' => $themename]);

        // defines the default types and master
        $pagetypes = [
            'master' => $this->__('Master'),
            '*home' => $this->__('Homepage'),
            '*admin' => $this->__('Admin panel pages'),
            '*editor' => $this->__('Editor panel pages')
        ];

        // checks the  page configuration files in use
        $pageconfigfiles = [];
        $existingconfigs = [];
        foreach ($pageconfigurations as $name => $pageconfiguration) {
            // checks for non-standard pagetypes
            if (strpos($name, '*') === 0 && !isset($pagetypes[$name])) {
                //! Pages inside a specific Controller type (editor, moderator, user)
                $pagetypes[$name] = $this->__f('%s type pages', ['%s' => ucfirst(substr($name, 1))]);
            }
            // check if the file exists
            if (isset($theme) && ($exists = file_exists($theme->getConfigPath() . "/$pageconfiguration[file]"))) {
                $existingconfigs[] = $pageconfiguration['file'];
            } elseif ($exists = file_exists("themes/$themeinfo[directory]/templates/config/$pageconfiguration[file]")) {
                $existingconfigs[] = $pageconfiguration['file'];
            }
            $pageconfigfiles[$pageconfiguration['file']] = $exists;
        }
        ksort($pageconfigfiles);

        // gets the available page configurations on the theme
        $existingconfigs = ModUtil::apiFunc('ZikulaThemeModule', 'user', 'getconfigurations', ['theme' => $themename]);

        // check that we have writable files
        $this->checkRunningConfig($request, $themeinfo);

        $legacyView = Zikula_View::getInstance('ZikulaThemeModule');

        // assign the output vars
        $legacyView
            ->assign('themename', $themename)
            ->assign('themeinfo', $themeinfo)
            ->assign('pagetypes', $pagetypes)
            ->assign('modules', $mods)
            ->assign('pageconfigurations', $pageconfigurations)
            ->assign('pageconfigs', $pageconfigfiles)
            ->assign('existingconfigs', $existingconfigs);

        return new Response($legacyView->fetch('Admin/pageconfigurations.tpl'));
    }

    /**
     * @Route("/modifypageconfigtemplates/{themename}/{filename}")
     * @Method("GET")
     *
     * modify a theme page configuration
     *
     * @param Request $request
     * @param string $themename name of the theme
     * @param string $filename  name of the file to edit
     *
     * @return Response symfony response object
     *
     * @throws \Exception Thrown if required files not found
     * @throws AccessDeniedException Thrown if the user doesn't have edit permissions over the theme
     */
    public function modifypageconfigtemplatesAction(Request $request, $themename, $filename = null)
    {
        $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($themename));
        $this->checkIfMainThemeFileExists($themeinfo);

        // Security check
        if (!$this->hasPermission('ZikulaThemeModule::', $themename . '::pageconfigurations', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        // read our configuration file
        $pageconfiguration = ModUtil::apiFunc('ZikulaThemeModule', 'user', 'getpageconfiguration', ['theme' => $themename, 'filename' => $filename]);
        if (empty($pageconfiguration)) {
            throw new \Exception($this->__("Configuration file not found"));
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
        $blockTypes = BlockUtil::loadAll();
        // returns [[ModuleName:FqBlockClassName => ModuleDisplayName/BlockDisplayName]]
        $allblocks = [];
        foreach ($blockTypes as $bKey => $block) {
            list($moduleDisplayName, $blockDisplayName) = explode('/', $block);
            $allblocks[$bKey] = [];
            $allblocks[$bKey]['module'] = $moduleDisplayName;
            $allblocks[$bKey]['bkey'] = $bKey;
            $allblocks[$bKey]['text_type_long'] = $block;
            $allblocks[$bKey]['text_type'] = $blockDisplayName;
            if (!isset($pageconfiguration['blocktypes'][$bKey])) {
                $pageconfiguration['blocktypes'][$bKey] = '';
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

        $legacyView = Zikula_View::getInstance('ZikulaThemeModule', false);

        return new Response(
            $legacyView
                ->assign('filename', $filename)
                ->assign('themename', $themename)
                ->assign('themeinfo', $themeinfo)
                ->assign('moduletemplates', ModUtil::apiFunc('ZikulaThemeModule', 'user', 'gettemplates', ['theme' => $themename]))
                ->assign('blocktemplates', ModUtil::apiFunc('ZikulaThemeModule', 'user', 'gettemplates', ['theme' => $themename, 'type' => 'blocks']))
                ->assign('palettes', ModUtil::apiFunc('ZikulaThemeModule', 'user', 'getpalettenames', ['theme' => $themename]))
                ->assign('blockpositions', $blockpositions)
                ->assign('allblocks', $allblocks)
                ->assign('blocks', $blocks)
                ->assign('pageconfiguration', $pageconfiguration)
                ->fetch('Admin/modifypageconfigtemplates.tpl')
        );
    }

    /**
     * @Route("/modifypageconfigtemplates")
     * @Method("POST")
     *
     * modify a theme page configuration
     *
     * @param Request $request
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
    public function updatepageconfigtemplatesAction(Request $request)
    {
        $token = $request->request->get('csrftoken', null);
        $this->get('zikula_core.common.csrf_token_handler')->validate($token);

        // get our input
        $themename = $request->request->get('themename', null);
        $filename = $request->request->get('filename', null);
        $pagetemplate = $request->request->get('pagetemplate', '');
        $blocktemplate = $request->request->get('blocktemplate', '');
        $pagepalette = $request->request->get('pagepalette', '');
        $modulewrapper = $request->request->get('modulewrapper', 1);
        $blockwrapper = $request->request->get('blockwrapper', 1);

        $blockinstancetemplates = $request->request->get('blockinstancetemplates', null);
        $blocktypetemplates = $request->request->get('blocktypetemplates', null);
        $blockpositiontemplates = $request->request->get('blockpositiontemplates', null);

        $filters = $request->request->get('filters', null);

        // check our input
        if (empty($themename) || empty($pagetemplate)) {
            throw new \InvalidArgumentException();
        }

        $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($themename));
        $this->checkIfMainThemeFileExists($themeinfo);

        // Security check
        if (!$this->hasPermission('ZikulaThemeModule::', $themename . '::pageconfigurations', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        // read our configuration file
        $pageconfiguration = ModUtil::apiFunc('ZikulaThemeModule', 'user', 'getpageconfiguration', ['theme' => $themename, 'filename' => $filename]);
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
        ModUtil::apiFunc('ZikulaThemeModule', 'user', 'writeinifile', ['theme' => $themename, 'assoc_arr' => $pageconfiguration, 'has_sections' => true, 'file' => $filename]);

        // set a status message
        $this->addFlash('status', $this->__('Done! Saved your changes.'));

        return $this->redirectToRoute('zikulathememodule_admin_pageconfigurations', ['themename' => $themename]);
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

        $legacyView = Zikula_View::getInstance('ZikulaThemeModule');

        $ostype = DataUtil::formatForOS($type);

        $filters = explode(',', $filters);
        $newfilters = [];
        foreach ($filters as $filter) {
            foreach ($legacyView->plugins_dir as $plugindir) {
                if (file_exists($plugindir .'/'. $ostype .'.'. DataUtil::formatForOS($filter) .'.php')) {
                    $newfilters[] = $filter;

                    break;
                }
            }
        }

        $leftover = array_diff($filters, $newfilters);
        if (count($leftover) > 0) {
            throw new \RuntimeException($this->__f('Error! Removed unknown \'%type%\': \'%leftovers%\'.', ['%type%' => DataUtil::formatForDisplay($type), '%leftovers%' => DataUtil::formatForDisplay(implode(',', $leftover))]));
        }

        return implode(',', $newfilters);
    }

    /**
     * @Route("/modifypageconfigurationassignment/{themename}/{pcname}")
     * @Method("GET")
     *
     * Modify a theme page configuration
     *
     * @param Request $request
     * @param string $themename name of the theme
     * @param string $pcname    name of the page configuration to edit
     *
     * @return Response symfony response object
     *
     * @throws \Exception if required files not found
     * @throws AccessDeniedException Thrown if the user doesn't have edit permissions over the theme
     */
    public function modifypageconfigurationassignmentAction(Request $request, $themename, $pcname = null)
    {
        $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($themename));
        $this->checkIfMainThemeFileExists($themeinfo);

        // Security check
        if (!$this->hasPermission('ZikulaThemeModule::', $themename . '::pageconfigurations', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        // assign all modules
        $allmods = ModUtil::getAllMods();
        $mods = [];
        foreach ($allmods as $mod) {
            $mods[$mod['name']] = $mod['name'];
        }

        // get all pageconfigurations
        $pageconfigurations = ModUtil::apiFunc('ZikulaThemeModule', 'user', 'getpageconfigurations', ['theme' => $themename]);
        if (!isset($pageconfigurations[$pcname])) {
            throw new \InvalidArgumentException();
        }

        // defines the default types and master
        $pagetypes = [
            'master'  => $this->__('Master'),
            '*home'   => $this->__('Homepage'),
            '*admin'  => $this->__('Admin panel pages'),
            '*editor' => $this->__('Editor panel pages')
        ];

        // checks for non-standard pagetypes
        foreach ($pageconfigurations as $name => $pageconfiguration) {
            if (strpos($name, '*') === 0 && !isset($pagetypes[$name])) {
                //! Pages inside a specific Controller type (editor, moderator, user)
                $pagetypes[$name] = $this->__f('%s type pages', ['%s' => ucfirst(substr($name, 1))]);
            }
        }

        // form the page config assignment array setting some useful key names
        $pageconfigassignment = ['pagemodule' => null, 'pagetype' => null, 'pagefunc' => null, 'pagecustomargs' => null];

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
        $existingconfigs = ModUtil::apiFunc('ZikulaThemeModule', 'user', 'getconfigurations', ['theme' => $themename]);

        $legacyView = Zikula_View::getInstance('ZikulaThemeModule');

        // assign the page config assignment name, theme name and theme info
        $legacyView
            ->assign($pageconfigassignment)
            ->assign('existingconfigs', $existingconfigs)
            ->assign('pcname', $pcname)
            ->assign('themename', $themename)
            ->assign('themeinfo', $themeinfo)
            ->assign('pagetypes', $pagetypes)
            ->assign('modules', $mods)
            ->assign('filename', $pageconfigurations[$pcname]['file']);

        return new Response($legacyView->fetch('Admin/modifypageconfigurationassignment.tpl'));
    }

    /**
     * @Route("/pageconfig")
     * @Method("POST")
     *
     * modify a theme page configuration
     *
     * @param Request $request
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
    public function updatepageconfigurationassignmentAction(Request $request)
    {
        $token = $request->request->get('csrftoken', null);
        $this->get('zikula_core.common.csrf_token_handler')->validate($token);

        // get our input
        $themename = $request->request->get('themename', null);
        $pcname = $request->request->get('pcname', null);
        $pagemodule = $request->request->get('pagemodule', null);
        $pagetype = $request->request->get('pagetype', 'user');
        $pagefunc = $request->request->get('pagefunc', null);
        $pagecustomargs = $request->request->get('pagecustomargs', null);
        $pageimportant = $request->request->get('pageimportant', null);
        $filename = $request->request->get('filename', null);

        // check our input
        if (!isset($themename) || empty($themename)) {
            throw new \InvalidArgumentException();
        }

        $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($themename));
        $this->checkIfMainThemeFileExists($themeinfo);

        // Security check
        if (!$this->hasPermission('ZikulaThemeModule::', $themename . '::pageconfigurations', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        // read the list of existing page config assignments
        $pageconfigurations = ModUtil::apiFunc('ZikulaThemeModule', 'user', 'getpageconfigurations', ['theme' => $themename]);

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
        $pageconfigurations[$newpageconfiguration] = ['file' => $filename];

        if (isset($pageimportant)) {
            $pageconfigurations[$newpageconfiguration]['important'] = 1;
        }

        // write the page configurations back to the running config
        ModUtil::apiFunc('ZikulaThemeModule', 'user', 'writeinifile', ['theme' => $themename, 'assoc_arr' => $pageconfigurations, 'has_sections' => true, 'file' => 'pageconfigurations.ini']);

        // set a status message
        $this->addFlash('status', $this->__('Done! Saved your changes.'));

        return $this->redirectToRoute('zikulathememodule_admin_pageconfigurations', ['themename' => $themename]);
    }

    /**
     * @Route("/deletepageconfigurationassignment")
     *
     * delete a theme page configuration assignment
     *
     * @param Request $request
     *      string $themename    name of the theme
     *      string $pcname       name of the page configuration to edit
     *      bool   $confirmation conformation to delete the page configuration
     *
     * @return Response symfony response object if confirmation isn't provided
     *
     * @throws \InvalidArgumentException Thrown if themename isn't provided or doesn't exist
     * @throws AccessDeniedException Thrown if the user doesn't have delete permissions over the theme
     */
    public function deletepageconfigurationassignmentAction(Request $request)
    {
        $themename = $request->query->get('themename', null);
        $pcname = $request->query->get('pcname', null);
        $confirmation = $request->request->get('confirmation', null);

        // Get the theme info
        $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($themename));

        if ($themeinfo == false) {
            throw new \InvalidArgumentException();
        }

        // Security check
        if (!$this->hasPermission('ZikulaThemeModule::', $themename . '::pageconfigurations', ACCESS_DELETE)) {
            throw new AccessDeniedException();
        }

        $legacyView = Zikula_View::getInstance('ZikulaThemeModule');

        // Check for confirmation.
        if (empty($confirmation)) {
            // No confirmation yet
            // Assign the theme info
            $legacyView->assign($themeinfo);

            // Assign the page configuration name
            $legacyView->assign('pcname', $pcname);

            return new Response($legacyView->fetch('Admin/deletepageconfigurationassignment.tpl'));
        }

        // If we get here it means that the user has confirmed the action
        $token = $request->request->get('csrftoken', null);
        $this->get('zikula_core.common.csrf_token_handler')->validate($token);

        // Delete the admin message
        // The return value of the function is checked
        if (ModUtil::apiFunc('ZikulaThemeModule', 'admin', 'deletepageconfigurationassignment', ['themename' => $themename, 'pcname' => $pcname])) {
            // Success
            $this->addFlash('status', $this->__('Done! Deleted it.'));
        }

        return $this->redirectToRoute('zikulathememodule_admin_pageconfigurations', ['themename' => $themename]);
    }

    /**
     * @Route("/config")
     * @Method("GET")
     *
     * Modify Theme module settings
     *
     * @return Response symfony response object if confirmation isn't provided
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin permissions over the module
     */
    public function modifyconfigAction()
    {
        // Security check
        if (!$this->hasPermission('ZikulaThemeModule::', '::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        // assign a list of modules suitable for html_options
        $usermods = ModUtil::getModulesCapableOf('user');
        $mods = [];
        foreach ($usermods as $usermod) {
            $mods[$usermod['name']] = $usermod['displayname'];
        }

        // register the renderer object allow access to various view values
        $view = Zikula_View::getInstance('ZikulaThemeModule');
        $view->register_object('render', $view);

        // check for a .htaccess file
        if (file_exists('.htaccess')) {
            $view->assign('htaccess', 1);
        } else {
            $view->assign('htaccess', 0);
        }

        $view->assign('currentTheme', $this->get('zikula_core.common.theme_engine')->getTheme());

        // assign the output variables and fetch the template
        return new Response(
            $view->assign('mods', $mods)
                // assign all module vars
                ->assign($this->getVars())
                // assign admintheme var
                ->assign('admintheme', ModUtil::getVar('ZikulaAdminModule', 'admintheme', ''))
                // assign an csrftoken for the clear cache/compile links
                ->assign('csrftoken', SecurityUtil::generateCsrfToken($this->container, true))
                // assign the core config var
                ->assign('theme_change', System::getVar('theme_change'))
                // extracted list of non-cached mods
                ->assign('modulesnocache', array_flip(explode(',', $this->getVar('modulesnocache'))))
                ->fetch('Admin/modifyconfig.tpl')
        );
    }

    /**
     * @Route("/config")
     * @Method("POST")
     *
     * Update configuration
     *
     * @param Request $request
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
    public function updateconfigAction(Request $request)
    {
        $token = $request->request->get('csrftoken', null);
        $this->get('zikula_core.common.csrf_token_handler')->validate($token);

        // security check
        if (!$this->hasPermission('ZikulaThemeModule::', '::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        // check if the theme cache was disabled and clean it if so
        $enablecache = (bool)$request->request->get('enablecache', false);

        if ($this->getVar('enablecache') && !$enablecache) {
            $theme = Zikula_View_Theme::getInstance();
            $theme->clear_all_cache();
        }

        // set our module variables
        $this->setVar('enablecache', $enablecache);

        $modulesnocache = $request->request->get('modulesnocache', []);
        $modulesnocache = implode(',', $modulesnocache);
        $this->setVar('modulesnocache', $modulesnocache);

        $compile_check = (bool)$request->request->get('compile_check', false);
        $this->setVar('compile_check', $compile_check);

        $cache_lifetime = (int)$request->request->get('cache_lifetime', 3600);
        if ($cache_lifetime < -1) {
            $cache_lifetime = 3600;
        }
        $this->setVar('cache_lifetime', $cache_lifetime);

        $cache_lifetime_mods = (int)$request->request->get('cache_lifetime_mods', 3600);
        if ($cache_lifetime_mods < -1) {
            $cache_lifetime_mods = 3600;
        }
        $this->setVar('cache_lifetime_mods', $cache_lifetime_mods);

        $force_compile = (bool)$request->request->get('force_compile', false);
        $this->setVar('force_compile', $force_compile);

        $trimwhitespace = (bool)$request->request->get('trimwhitespace', false);
        $this->setVar('trimwhitespace', $trimwhitespace);

        $maxsizeforlinks = (int)$request->request->get('maxsizeforlinks', 30);
        $this->setVar('maxsizeforlinks', $maxsizeforlinks);

        $theme_change = (bool)$request->request->get('theme_change', false);
        System::setVar('theme_change', $theme_change);

        $admintheme = (string)$request->request->get('admintheme', '');
        ModUtil::setVar('Admin', 'admintheme', $admintheme);

        $alt_theme_name = (string)$request->request->get('alt_theme_name', '');
        $this->setVar('alt_theme_name', $alt_theme_name);

        $alt_theme_domain = (string)$request->request->get('alt_theme_domain', '');
        $this->setVar('alt_theme_domain', $alt_theme_domain);

        $itemsperpage = (int)$request->request->get('itemsperpage', 25);
        if ($itemsperpage < 1) {
            $itemsperpage = 25;
        }
        $this->setVar('itemsperpage', $itemsperpage);

        $cssjscombine = (bool)$request->request->get('cssjscombine', false);
        $this->setVar('cssjscombine', $cssjscombine);

        $cssjsminify = (bool)$request->request->get('cssjsminify', false);
        $this->setVar('cssjsminify', $cssjsminify);

        $cssjscompress = (bool)$request->request->get('cssjscompress', false);
        $this->setVar('cssjscompress', $cssjscompress);

        $cssjscombine_lifetime = (int)$request->request->get('cssjscombine_lifetime', 3600);
        if ($cssjscombine_lifetime < -1) {
            $cssjscombine_lifetime = 3600;
        }
        $this->setVar('cssjscombine_lifetime', $cssjscombine_lifetime);

        // render
        $render_compile_check = (bool)$request->request->get('render_compile_check', false);
        $this->setVar('render_compile_check', $render_compile_check);

        $render_force_compile = (bool)$request->request->get('render_force_compile', false);
        $this->setVar('render_force_compile', $render_force_compile);

        $render_cache = (int)$request->request->get('render_cache', false);
        $this->setVar('render_cache', $render_cache);

        $render_lifetime = (int)$request->request->get('render_lifetime', 3600);
        if ($render_lifetime < -1) {
            $render_lifetime = 3600;
        }
        $this->setVar('render_lifetime', $render_lifetime);

        $render_expose_template = (bool)$request->request->get('render_expose_template', false);
        $this->setVar('render_expose_template', $render_expose_template);

        // The configuration has been changed, so we clear all caches for this module.
        $view = Zikula_View::getInstance('ZikulaThemeModule');
        $view->clear_compiled();
        $view->clear_all_cache();

        // the module configuration has been updated successfuly
        $this->addFlash('status', $this->__('Done! Saved module configuration.'));

        return $this->redirectToRoute('zikulathememodule_admin_modifyconfig');
    }

    /**
     * @Route("/clearcompiled")
     * @Method("GET")
     *
     * Clear theme engine compiled templates
     *
     * Using this function, the admin can clear all theme engine compiled
     * templates for the system.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin access over the module
     */
    public function clearCompiledAction(Request $request)
    {
        $token = $request->query->get('csrftoken', null);
        $this->get('zikula_core.common.csrf_token_handler')->validate($token);

        // Security check
        if (!$this->hasPermission('ZikulaThemeModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $theme = Zikula_View_Theme::getInstance();
        $res = $theme->clear_compiled();

        if ($res) {
            $this->addFlash('status', $this->__('Done! Deleted theme engine compiled templates.'));
        } else {
            $this->addFlash('error', $this->__('Error! Failed to clear theme engine compiled templates.'));
        }

        return $this->redirectToRoute('zikulathememodule_config_config');
    }

    /**
     * @Route("/clearcache")
     * @Method("GET")
     *
     * Clear theme engine cached templates
     *
     * Using this function, the admin can clear all theme engine cached
     * templates for the system.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin access over the module
     */
    public function clearCacheAction(Request $request)
    {
        $token = $request->query->get('csrftoken', null);
        $this->get('zikula_core.common.csrf_token_handler')->validate($token);

        // Security check
        if (!$this->hasPermission('ZikulaThemeModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $cacheid = $request->get('cacheid');

        $theme = Zikula_View_Theme::getInstance();
        $res = $theme->clear_all_cache();

        if ($cacheid) {
            // clear cache for all active themes
            $themesarr = ThemeUtil::getAllThemes();
            foreach ($themesarr as $themearr) {
                $themedir = $themearr['directory'];
                $res = $theme->clear_cache(null, $cacheid, null, null, $themedir);
                if ($res) {
                    $this->addFlash('status', $this->__('Done! Deleted theme engine cached templates.').' '.$cacheid.', '.$themedir);
                } else {
                    $this->addFlash('error', $this->__('Error! Failed to clear theme engine cached templates.').' '.$cacheid.', '.$themedir);
                }
            }
        } else {
            // this clear all cache for all themes
            $res = $theme->clear_all_cache();
            if ($res) {
                $this->addFlash('status', $this->__('Done! Deleted theme engine cached templates.'));
            } else {
                $this->addFlash('error', $this->__('Error! Failed to clear theme engine cached templates.'));
            }
        }

        return $this->redirectToRoute('zikulathememodule_config_config');
    }

    /**
     * @Route("/clearcombo")
     * @Method("GET")
     *
     * Clear CSS/JS combination cached files
     *
     * Using this function, the admin can clear all CSS/JS combination cached
     * files for the system.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin access over the module
     */
    public function clearCssjscombinecacheAction(Request $request)
    {
        $token = $request->query->get('csrftoken', null);
        $this->get('zikula_core.common.csrf_token_handler')->validate($token);

        // Security check
        if (!$this->hasPermission('ZikulaThemeModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $theme = Zikula_View_Theme::getInstance();
        $theme->clear_cssjscombinecache();

        $this->addFlash('status', $this->__('Done! Deleted CSS/JS combination cached files.'));

        return $this->redirectToRoute('zikulathememodule_config_config');
    }

    /**
     * @Route("/clearconfig")
     * @Method("GET")
     *
     * Clear theme engine configurations
     *
     * Using this function, the admin can clear all theme engine configuration
     * copies created inside the temporary directory.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin access over the module
     */
    public function clearConfigAction(Request $request)
    {
        $token = $request->query->get('csrftoken', null);
        $this->get('zikula_core.common.csrf_token_handler')->validate($token);

        // Security check
        if (!$this->hasPermission('ZikulaThemeModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $theme = Zikula_View_Theme::getInstance();
        $res = $theme->clear_theme_config();

        if ($res) {
            $this->addFlash('status', $this->__('Done! Deleted theme engine configurations.'));
        } else {
            $this->addFlash('error', $this->__('Error! Failed to clear theme engine configurations.'));
        }

        return $this->redirectToRoute('zikulathememodule_config_config');
    }

    /**
     * @Route("/renderclearcompiled")
     * @Method("GET")
     *
     * Clear render compiled templates
     *
     * Using this function, the admin can clear all render compiled templates
     * for the system.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin access over the module
     */
    public function renderClearCompiledAction(Request $request)
    {
        $token = $request->query->get('csrftoken', null);
        $this->get('zikula_core.common.csrf_token_handler')->validate($token);

        // Security check
        if (!$this->hasPermission('ZikulaThemeModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $view = Zikula_View::getInstance('ZikulaThemeModule');
        $res = $view->clear_compiled();

        if ($res) {
            $this->addFlash('status', $this->__('Done! Deleted rendering engine compiled templates.'));
        } else {
            $this->addFlash('error', $this->__('Error! Failed to clear rendering engine compiled templates.'));
        }

        return $this->redirectToRoute('zikulathememodule_config_config');
    }

    /**
     * @Route("/renderclearcache")
     * @Method("GET")
     *
     * Clear render cached templates
     *
     * Using this function, the admin can clear all render cached templates
     * for the system.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin access over the module
     */
    public function renderClearCacheAction(Request $request)
    {
        $token = $request->query->get('csrftoken', null);
        $this->get('zikula_core.common.csrf_token_handler')->validate($token);

        // Security check
        if (!$this->hasPermission('ZikulaThemeModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $view = Zikula_View::getInstance('ZikulaThemeModule');
        $res = $view->clear_all_cache();

        if ($res) {
            $this->addFlash('status', $this->__('Done! Deleted rendering engine cached pages.'));
        } else {
            $this->addFlash('error', $this->__('Error! Failed to clear rendering engine cached pages.'));
        }

        return $this->redirectToRoute('zikulathememodule_config_config');
    }

    /**
     * @Route("/clearall")
     * @Method("GET")
     *
     * Clear all cache and compile directories
     *
     * Using this function, the admin can clear all theme and render cached,
     * compiled and combined files for the system.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin access over the module
     */
    public function clearallcompiledcachesAction(Request $request)
    {
        // Security check
        if (!$this->hasPermission('ZikulaThemeModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        ModUtil::apiFunc('ZikulaSettingsModule', 'admin', 'clearallcompiledcaches');

        $this->addFlash('status', $this->__('Done! Cleared all cache and compile directories.'));

        return $this->redirectToRoute('zikulathememodule_config_config');
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
            throw new \InvalidArgumentException($this->__('Main theme file not found!'));
        }
    }
}
