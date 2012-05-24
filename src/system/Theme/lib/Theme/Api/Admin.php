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

class Theme_Api_Admin extends Zikula_AbstractApi
{
    /**
     * Regenerate themes list.
     *
     * @deprecated
     *
     * @return boolean
     */
    public function regenerate()
    {
        return Theme_Util::regenerate();
    }

    /**
     * get available admin panel links
     *
     * @return array array of admin links
     */
    public function getlinks()
    {
        $links = array();

        if (SecurityUtil::checkPermission('Theme::', '::', ACCESS_ADMIN)) {
            $links[] = array('url' => ModUtil::url('Theme', 'admin', 'view'), 'text' => __('Themes list'), 'class' => 'z-icon-es-view');
        }
        if (SecurityUtil::checkPermission('Theme::', '::', ACCESS_ADMIN)) {
            $links[] = array('url' => ModUtil::url('Theme', 'admin', 'modifyconfig'), 'text' => __('Settings'), 'class' => 'z-icon-es-config');
        }

        return $links;
    }

    /**
     * update theme settings
     *
     * @return bool true on success, false otherwise
     */
    public function updatesettings($args)
    {
        // Security check
        if (!SecurityUtil::checkPermission('Theme::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        // Check our input arguments
        if (!isset($args['themeinfo'])) {
            return LogUtil::registerArgsError();
        }

        if (!DBUtil::updateObject($args['themeinfo'], 'themes')) {
            return LogUtil::registerError(__('Error! Could not save your changes.'));
        }

        return true;
    }

    /**
     * set default site theme
     *
     * optionally reset user theme selections
     */
    public function setasdefault($args)
    {
        // Security check
        if (!SecurityUtil::checkPermission('Theme::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        // Check our input arguments
        if (!isset($args['themename'])) {
            return LogUtil::registerArgsError();
        }
        if (!isset($args['resetuserselected'])) {
            $args['resetuserselected'] = false;
        }

        // if chosen reset all user theme selections
        if ($args['resetuserselected']) {
            $dbtables = DBUtil::getTables();
            $sql ="UPDATE $dbtables[users] SET theme = ''";
            if (!DBUtil::executeSQL($sql)) {
                return false;
            }
        }

        // change default theme
        if (!System::setVar('Default_Theme', $args['themename'])) {
            return false;
        }

        return true;
    }

    /**
     * create running configuration
     *
     */
    public function createrunningconfig($args)
    {
        // check our input
        if (!isset($args['themename']) || empty($args['themename'])) {
            LogUtil::registerArgsError();
        } else {
            $themename = $args['themename'];
        }

        $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($args['themename']));
        if (!file_exists('themes/'.DataUtil::formatForOS($themeinfo['directory']).'/version.php')) {
            return LogUtil::registerArgsError();
        }

        // Security check
        if (!SecurityUtil::checkPermission('Theme::', "$themename::", ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        // get the theme settings and write them back to the running config directory
        $variables = ModUtil::apiFunc('Theme', 'user', 'getvariables', array('theme' => $themename));
        if (is_array($variables)) {
            ModUtil::apiFunc('Theme', 'user', 'writeinifile', array('theme' => $themename, 'assoc_arr' => $variables, 'has_sections' => true, 'file' => 'themevariables.ini'));
        }

        // get the theme palettes and write them back to the running config directory
        $palettes = ModUtil::apiFunc('Theme', 'user', 'getpalettes', array('theme' => $themename));
        if (is_array($palettes)) {
            ModUtil::apiFunc('Theme', 'user', 'writeinifile', array('theme' => $themename, 'assoc_arr' => $palettes, 'has_sections' => true, 'file' => 'themepalettes.ini'));
        }

        // get the theme page configurations and write them back to the running config directory
        $pageconfigurations = ModUtil::apiFunc('Theme', 'user', 'getpageconfigurations', array('theme' => $themename));
        ModUtil::apiFunc('Theme', 'user', 'writeinifile', array('theme' => $themename, 'assoc_arr' => $pageconfigurations, 'has_sections' => true, 'file' => 'pageconfigurations.ini'));

        foreach ($pageconfigurations as $pageconfiguration) {
            $fullpageconfiguration = ModUtil::apiFunc('Theme', 'user', 'getpageconfiguration', array('theme' => $themename, 'filename' => $pageconfiguration['file']));
            ModUtil::apiFunc('Theme', 'user', 'writeinifile', array('theme' => $themename, 'assoc_arr' => $fullpageconfiguration, 'has_sections' => true, 'file' => $pageconfiguration['file']));
        }

        return true;
    }

    /**
     * delete a theme
     */
    public function delete($args)
    {
        // Argument check
        if (!isset($args['themename'])) {
            return LogUtil::registerArgsError();
        }

        $themeid = ThemeUtil::getIDFromName($args['themename']);

        // Get the theme info
        $themeinfo = ThemeUtil::getInfo($themeid);

        if ($themeinfo == false) {
            return LogUtil::registerError(__('Sorry! No such item found.'));
        }

        // Security check
        if (!SecurityUtil::checkPermission('Theme::', "$themeinfo[name]::", ACCESS_DELETE)) {
            return LogUtil::registerPermissionError();
        }

        // reset the theme for any users utilising this theme.
        $dbtables = DBUtil::getTables();
        $sql ="UPDATE $dbtables[users] SET theme = '' WHERE theme = '".DataUtil::formatForStore($themeinfo['name']) ."'";
        if (!DBUtil::executeSQL($sql)) {
            return false;
        }

        if (!DBUtil::deleteObjectByID('themes', $themeid, 'id')) {
            return LogUtil::registerError(__('Error! Could not perform the deletion.'));
        }

        // delete the running config
        ModUtil::apiFunc('Theme', 'admin', 'deleterunningconfig', array('themename' => $themeinfo['name']));

        // clear the compiled and cached templates
        // Note: This actually clears ALL compiled and cached templates but there doesn't seem to be
        // a way to clear out only files associated with a theme without supplying all the template
        // names used by that theme.
        // see http://smarty.php.net/manual/en/api.clear.cache.php
        // and http://smarty.php.net/manual/en/api.clear.compiled.tpl.php
        ModUtil::apiFunc('Theme', 'user', 'clear_compiled');
        ModUtil::apiFunc('Theme', 'user', 'clear_cached');

        // try to delete the files
        if ($args['deletefiles'] == 1) {
            ModUtil::apiFunc('Theme', 'admin', 'deletefiles', array('themename' => $themeinfo['name'], 'themedirectory' => $themeinfo['directory']));
        }

        // Let the calling process know that we have finished successfully
        return true;
    }

    /**
     * delete theme files from the file system if possible
     */
    public function deletefiles($args)
    {
        // check our input
        if (!isset($args['themename']) || empty($args['themename'])) {
            return LogUtil::registerArgsError();
        } else {
            $themename = $args['themename'];
        }

        if (!isset($args['themedirectory']) || empty($args['themedirectory'])) {
            return LogUtil::registerArgsError();
        } else {
            $osthemedirectory = DataUtil::formatForOS($args['themedirectory']);
        }

        // Security check
        if (!SecurityUtil::checkPermission('Theme::', $themename .'::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        if (is_writable('themes') && is_writable('themes/' . $osthemedirectory)) {
            $res = FileUtil::deldir('themes/' .$osthemedirectory);
            if ($res == true) {
                return LogUtil::registerStatus(__('Done! Removed theme files from the file system.'));
            }

            return LogUtil::registerError(__('Error! Could not delete theme files from the file system. Please remove them by another means (FTP, SSH, ...).'));
        }

        LogUtil::registerStatus(__f('Notice: Theme files cannot be deleted because Zikula does not have write permissions for the themes folder and/or themes/%s folder.', DataUtil::formatForDisplay($args['themedirectory'])));

        return false;
    }

    /**
     * delete a running configuration
     */
    public function deleterunningconfig($args)
    {
        // check our input
        if (!isset($args['themename']) || empty($args['themename'])) {
            return LogUtil::registerArgsError();
        } else {
            $themename = $args['themename'];
        }

        // Security check
        if (!SecurityUtil::checkPermission('Theme::', "$themename::", ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        // define the base files
        $files = array('pageconfigurations.ini', 'themepalettes.ini', 'themevariables.ini');

        // get the theme info to identify further files to delete
        $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName($themename));
        if ($themeinfo) {
            $pageconfigurations = ModUtil::apiFunc('Theme', 'user', 'getpageconfigurations', array('theme' => $themename));
            if (is_array($pageconfigurations)) {
                foreach ($pageconfigurations as $pageconfiguration) {
                    $files[] = $pageconfiguration['file'];
                }
            }
        }

        // delete each file
        foreach ($files as $file) {
            ModUtil::apiFunc('Theme', 'admin', 'deleteinifile', array('file' => $file, 'themename' => $themename));
        }

        return true;
    }

    /**
     * delete ini file
     */
    public function deleteinifile($args)
    {
        if (!isset($args['themename']) || empty($args['themename'])) {
            return LogUtil::registerArgsError();
        } else {
            $themename = $args['themename'];
        }

        // Security check
        if (!SecurityUtil::checkPermission('Theme::', "$themename", ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        if (!isset($args['file']) || empty($args['file'])) {
            return LogUtil::registerArgsError();
        }

        $ostemp  = CacheUtil::getLocalDir();
        $ostheme = DataUtil::formatForOS($themename);
        $osfile  = $ostemp.'/Theme_Config/'.$ostheme.'/'.DataUtil::formatForOS($args['file']);

        if (file_exists($osfile) && is_writable($osfile)) {
            unlink($osfile);
        }
    }

    /**
     * delete a page configuration assignment
     */
    public function deletepageconfigurationassignment($args)
    {
        // Argument check
        if (!isset($args['themename']) && !isset($args['pcname'])) {
            return LogUtil::registerArgsError();
        }

        $themeid = ThemeUtil::getIDFromName($args['themename']);

        // Get the theme info
        $themeinfo = ThemeUtil::getInfo($themeid);

        if ($themeinfo == false) {
            return LogUtil::registerError(__('Sorry! No such item found.'));
        }

        // Security check
        if (!SecurityUtil::checkPermission('Theme::', "$themeinfo[name]::pageconfigurations", ACCESS_DELETE)) {
            return LogUtil::registerPermissionError();
        }

        // read the list of existing page config assignments
        $pageconfigurations = ModUtil::apiFunc('Theme', 'user', 'getpageconfigurations', array('theme' => $args['themename']));

        // remove the requested page configuration
        unset($pageconfigurations[$args['pcname']]);

        // write the page configurations back to the running config
        ModUtil::apiFunc('Theme', 'user', 'writeinifile', array('theme' => $args['themename'], 'assoc_arr' => $pageconfigurations, 'has_sections' => true, 'file' => 'pageconfigurations.ini'));

        return true;
    }

    /**
     * create theme
     */
    public function create($args)
    {
        // Argument check
        if (!isset($args['themeinfo']) || !isset($args['themeinfo']['name']) || empty($args['themeinfo']) || empty($args['themeinfo']['name'])) {
            $url = ModUtil::url('Theme', 'admin', 'new');

            return LogUtil::registerError(__("Error: You must enter at least the theme name."), null, $url);
        }

        $themeinfo = DataUtil::formatForOS($args['themeinfo']);

        // check for some invalid input
        if (!isset($themeinfo['displayname']) || empty($themeinfo['displayname'])) {
            $themeinfo['displayname'] = $themeinfo['name'];
        }
        if (!isset($themeinfo['description']) || empty($themeinfo['description'])) {
            $themeinfo['description'] = $themeinfo['name'];
        }

        // strip the theme name of any unwanted characters
        $themeinfo['name'] = preg_replace('/[^a-z0-9_]/i', '_', $themeinfo['name']);

        // check if theme already exists
        if (ThemeUtil::getIDFromName($themeinfo['name'])) {
            return LogUtil::registerError(__('Error! Could not create the new item.'));
        }

        // create the directory structure
        $dirs = array(
                '',
                '/docs',
                '/images',
                '/plugins',
                '/locale',
                '/locale/en',
                '/locale/en/LC_MESSAGES',
                '/style',
                '/templates',
                '/templates/blocks',
                '/templates/config',
                '/templates/modules'
        );

        foreach ($dirs as $dir) {
            if (!mkdir("themes/{$themeinfo['name']}/{$dir}") || !touch("themes/{$themeinfo['name']}/{$dir}/index.html")) {
                return LogUtil::registerError(__('Error! Could not create the new item.'));
            }
        }

        $versionfile = $args['versionfile'];
        $potfile = $args['potfile'];
        $palettesfile = $args['palettesfile'];
        $variablesfile = $args['variablesfile'];
        $pageconfigurationsfile = $args['pageconfigurationsfile'];
        $pageconfigurationfile = $args['pageconfigurationfile'];
        $pagetemplatefile = $args['pagetemplatefile'];
        $cssfile = $args['cssfile'];
        $blockfile = $args['blockfile'];

        $files = array(
                "themes/$themeinfo[name]/version.php" => 'versionfile',
                "themes/$themeinfo[name]/locale/theme_".$themeinfo['name'].".pot" => 'potfile',
                "themes/$themeinfo[name]/templates/config/themepalettes.ini" => 'palettesfile',
                "themes/$themeinfo[name]/templates/config/themevariables.ini" => 'variablesfile',
                "themes/$themeinfo[name]/templates/config/pageconfigurations.ini" => 'pageconfigurationsfile',
                "themes/$themeinfo[name]/templates/config/master.ini" => 'pageconfigurationfile',
                "themes/$themeinfo[name]/templates/master.tpl" => 'pagetemplatefile',
                "themes/$themeinfo[name]/templates/blocks/block.tpl" => 'blockfile',
                "themes/$themeinfo[name]/style/style.css" => 'cssfile'
        );

        // write the files
        foreach ($files as $filename => $filevar) {
            $handle = fopen($filename, 'w');
            if (!$handle) {
                return LogUtil::registerError(__f('Error! Could not open file so that it could be written to: %s', $filename));
            }
            if (!fwrite($handle, $$filevar)) {
                fclose($handle);

                return LogUtil::registerError(__f('Error! could not write to file: %s', $filename));
            }
            fclose($handle);
        }

        return true;
    }
}
