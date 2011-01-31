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

/**
 * Show general installation information
 * @return string HTML output string
 */
class SysInfo_Controller_Admin extends Zikula_Controller
{
    public function main()
    {
        if (!SecurityUtil::checkPermission('SysInfo::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        $this->view->setCaching(false);

        $this->view->assign('pnversionnum', Zikula_Core::VERSION_NUM)
                   ->assign('pnversionid', Zikula_Core::VERSION_ID)
                   ->assign('pnversionsub', Zikula_Core::VERSION_SUB);

        $serversig = System::serverGetVar('SERVER_SIGNATURE');
        if (!isset($serversig) || empty($serversig)) {
            $serversig = System::serverGetVar('SERVER_SOFTWARE');
        }
        $this->view->assign('serversig', $serversig);

        $this->view->assign('phpversion', phpversion());

        // Mess around with PHP functions for the various databases
        $serverInfo = DBUtil::serverInfo();
        $connectionInfo = Doctrine_Manager::getInstance()->getCurrentConnection();
        switch (strtolower($connectionInfo->getDriverName())) {
            case 'mysql':
                $dbinfo = 'MySQL ' . $serverInfo['description'];
                break;
            case 'mysqli':
                $dbinfo = 'MySQL (improved driver) ' . $serverInfo['description'];
                break;
            default:
                $dbinfo = $serverInfo['description'];
                break;
        }

        // Extensions checking
        $mysql = array('name' => 'mysql', 'reason' => $this->__('Zikula can operate with a database of the associated type if this extension is loaded.'));
        $mysqli = array('name' => 'mysqli', 'reason' => $this->__('Zikula can operate with a database of the associated type if this extension is loaded.'));
        $suhosin_extension = array('name' => 'suhosin', 'reason' => $this->__('The <a href="http://www.suhosin.org">Suhosin extension</a> is an advanced protection system for PHP installations. It can be used separately from the Suhosin patch or used in association with it.'));
        $suhosin_patch = array('name' => 'SUHOSIN_PATCH', 'text' => $this->__('Suhosin'), 'reason' => $this->__('The <a href="http://www.suhosin.org">Suhosin patch</a> is an advanced protection system for PHP installations. It can be used separately from the Suhosin extension or used in association with it.'));
        $required_extensions = array();
        $optional_extensions = array($mysql, $mysqli, $suhosin_extension);
        $optional_patches = array($suhosin_patch);
        $extensions = array();
        $opt_extensions = array();
        $opt_patches = array();

        foreach ($required_extensions as $ext) {
            if (extension_loaded($ext['name'])) {
                $ext['loaded'] = 'greenled.gif';
                $ext['status'] = $this->__('Loaded');
            } else {
                $ext['loaded'] = 'redled.gif';
                $ext['status'] = $this->__('Not loaded');
            }
            $extensions[] = $ext;
        }

        foreach ($optional_extensions as $ext) {
            if (extension_loaded($ext['name'])) {
                $ext['loaded'] = 'greenled.gif';
                $ext['status'] = $this->__('Loaded');
            } else {
                $ext['loaded'] = 'redled.gif';
                $ext['status'] = $this->__('Not loaded');
            }
            $opt_extensions[] = $ext;
        }

        foreach ($optional_patches as $ext) {
            if (defined($ext['name'])) {
                $ext['loaded'] = 'greenled.gif';
                $ext['status'] = $this->__('Loaded');
            } else {
                $ext['loaded'] = 'redled.gif';
                $ext['status'] = $this->__('Not loaded');
            }
            $opt_patches[] = $ext;
        }

        $mod_security = false;
        if (function_exists('apache_get_modules')) {
            // we have an apache2
            $apache_modules = apache_get_modules();
            if (in_array("mod_security", $apache_modules)) {
                // modsecurity is installed
                $mod_security = true;
            }
        }

        $this->view->assign('extensions', $extensions)
                   ->assign('opt_extensions', $opt_extensions)
                   ->assign('opt_patches', $opt_patches)
                   ->assign('dbinfo', $dbinfo)
                   ->assign('php_display_errors', DataUtil::getBooleanIniValue('display_errors'))
                   ->assign('php_display_startup_errors', DataUtil::getBooleanIniValue('display_startup_errors'))
                   ->assign('php_expose_php', DataUtil::getBooleanIniValue('expose_php'))
                   ->assign('php_register_globals', DataUtil::getBooleanIniValue('register_globals'))
                   ->assign('php_magic_quotes_gpc', DataUtil::getBooleanIniValue('magic_quotes_gpc'))
                   ->assign('php_magic_quotes_runtime', DataUtil::getBooleanIniValue('magic_quotes_runtime'))
                   ->assign('php_allow_url_fopen', DataUtil::getBooleanIniValue('allow_url_fopen'))
                   ->assign('php_allow_url_include', DataUtil::getBooleanIniValue('allow_url_include'))
                   ->assign('php_disable_functions', DataUtil::getBooleanIniValue('disable_functions'))
                   ->assign('mod_security', (bool)$mod_security);

        return $this->view->fetch('sysinfo_admin_main.tpl');
    }

    /**
     * Show PHP information
     * @param int 'info' The part of phpinfo to display
     * @return string HTML output string
     */
    public function phpinfo()
    {
        if (!SecurityUtil::checkPermission('SysInfo::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        $info = FormUtil::getPassedValue('info', empty($info) ? 4 : $info, 'REQUEST');

        // Output buffering appears to be the only way to do this...
        ob_start();
        phpinfo($info);
        $phpinfo = ob_get_contents();
        ob_end_clean();

        // This is all for formatting
        $phpinfo = preg_replace(array('/^.*<body[^>]*>/is', '/<\/body[^>]*>.*$/is'), '', $phpinfo, 1);

        // get rid of hard rules
        $phpinfo = str_replace('<hr />', '', $phpinfo);

        // Remove pixel table widths.
        $phpinfo = preg_replace('/width="[0-9]+"/i', 'width="80%"', $phpinfo);

        // change the table into our standard admin table format
        $phpinfo = str_replace('<table border="0" cellpadding="3" width="80%">', '<table class="z-datatable">', $phpinfo);
        $phpinfo = str_replace('<tr class="h">', '<tr>', $phpinfo);
        $phpinfo = str_replace('</th></tr>', '</th></tr>', $phpinfo);
        $phpinfo = str_replace('</tr></table>', '</tr></table>', $phpinfo);
        $phpinfo = str_replace('<a name=', '<a id=', $phpinfo);
        $phpinfo = str_replace('<font', '<span', $phpinfo);
        $phpinfo = str_replace('</font', '</span', $phpinfo);

        // match class "v" td cells an pass them to callback function
        $phpinfo = preg_replace_callback('%(<td class="v">)(.*?)(</td>)%i', '_sysinfo_phpinfo_v_callback', $phpinfo);

        // add the relevant row classes
        // we have to break the output into an array so that the starting class can be reset each time
        $phpinfo = explode('<tbody>', $phpinfo);
        foreach ($phpinfo as $key => $source) {
            $GLOBALS['class'] = '_sysinfo_phpinfo_class';
            $phpinfo[$key] = preg_replace_callback('/<tr>/', '_sysinfo_phpinfo_callback', $source);
        }
        $phpinfo = implode('', $phpinfo);

        $this->view->setCaching(false);

        $this->view->assign('phpinfo', $phpinfo);

        return $this->view->fetch('sysinfo_admin_phpinfo.tpl');
    }

    /**
     * Show writable files and folders within the filesystem
     * @return string HTML output string
     */
    public function filesystem()
    {
        if (!SecurityUtil::checkPermission('SysInfo::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        $ztemp = DataUtil::formatForOS(CacheUtil::getLocalDir(),true);
        $filelist = ModUtil::apiFunc('SysInfo', 'admin', 'filelist');

        $this->view->setCaching(false);

        $this->view->assign('filelist', $filelist)
                   ->assign('ztemp', $ztemp);

        return $this->view->fetch('sysinfo_admin_filesystem.tpl');
    }

    /**
     * Show writable files and folders within ztemp
     * @return string HTML output string
     */
    public function ztemp()
    {
        if (!SecurityUtil::checkPermission('SysInfo::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        $this->view->setCaching(false);

        $ztemp = DataUtil::formatForOS(CacheUtil::getLocalDir(),true);
        $filelist = ModUtil::apiFunc('SysInfo', 'admin', 'filelist',
                                     array('startdir' => $ztemp . '/',
                                           'ztemp' => 1));

        $this->view->assign('filelist', $filelist);

        $this->view->assign('ztemp', $ztemp);

        return $this->view->fetch('sysinfo_admin_filesystem.tpl');
    }

    /**
     * Show version information for installed Zikula modules
     * @return string HTML output string
     */
    public function extensions()
    {
        if (!SecurityUtil::checkPermission('SysInfo::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        $this->view->setCaching(false);

        return $this->view->assign('mods', ModuleUtil::getModules())
                          ->assign('themes', ThemeUtil::getAllThemes())
                          ->fetch('sysinfo_admin_extensions.tpl');
    }
}

/**
 * callback function to add PN table tow classes to phpinfo report
 *
 */
function _sysinfo_phpinfo_callback()
{
    $GLOBALS['_sysinfo_phpinfo_class'] = (!isset($GLOBALS['_sysinfo_phpinfo_class']) || $GLOBALS['_sysinfo_phpinfo_class'] == 'z-odd') ? 'z-even' : 'z-odd';
    return '<tr class="'.$GLOBALS['_sysinfo_phpinfo_class'].'">';
}

/**
 * callback function to eventually add an extra space in passed <td class="v">...</td>
 * after a ";" or "@" char to let the browser split long lines nicely
 * see patch #5343 - credits go to mrunreal
 */
function _sysinfo_phpinfo_v_callback($matches)
{
    $matches[2] = preg_replace('%(?<!\s)([;@])(?!\s)%',"$1 ",$matches[2]);
    return $matches[1].$matches[2].$matches[3];
}