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

class Settings_Controller_Admin extends Zikula_AbstractController
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
     * entry point for the module
     *
     * @return string html output
     */
    public function main()
    {
        // Security check will be done in modifyconfig()
        $this->redirect(ModUtil::url($this->name, 'admin', 'modifyconfig'));
    }

    /**
     * display the main site settings form
     *
     * @return string html output
     */
    public function modifyconfig()
    {
        // security check
        if (!SecurityUtil::checkPermission('Settings::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        // localise page title
        $pagetitle = System::getVar('pagetitle', '%pagetitle%');
        $pagetitle = str_replace('%pagetitle%', $this->__('%pagetitle%'), $pagetitle);
        $pagetitle = str_replace('%sitename%', $this->__('%sitename%'), $pagetitle);
        $pagetitle = str_replace('%modulename%', $this->__('%modulename%'), $pagetitle);
        $this->view->assign('pagetitle', $pagetitle);

        return $this->view->fetch('settings_admin_modifyconfig.tpl');
    }

    /**
     * update main site settings
     *
     * @return mixed true if successful, false if unsuccessful, error string otherwise
     */
    public function updateconfig()
    {
        $this->checkCsrfToken();

        // security check
        if (!SecurityUtil::checkPermission('Settings::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        // get settings from form
        $settings = FormUtil::getPassedValue('settings', null, 'POST');

        // if this form wasnt posted to redirect back
        if ($settings === null) {
            $this->redirect(ModUtil::url('Settings', 'admin', 'modifyconfig'));
        }

        // validate the entry point
        $falseEntryPoints = array('admin.php', 'ajax.php', 'install.php', 'upgrade.php', 'user.php', 'mo2json.php', 'jcss.php');
        $entryPointExt = pathinfo($settings['entrypoint'], PATHINFO_EXTENSION);

        if (in_array($settings['entrypoint'], $falseEntryPoints) || !file_exists($settings['entrypoint']) || strtolower($entryPointExt) != 'php') {
            LogUtil::registerError($this->__("Error! Either you entered an invalid entry point, or else the file specified as being the entry point was not found in the Zikula root directory."));
            $settings['entrypoint'] = System::getVar('entrypoint');
        }

        $permachecks = true;
        $settings['permasearch'] = mb_ereg_replace(' ', '', $settings['permasearch']);
        $settings['permareplace'] = mb_ereg_replace(' ', '', $settings['permareplace']);
        if (mb_ereg(',$', $settings['permasearch'])) {
            LogUtil::registerError($this->__("Error! In your permalink settings, strings cannot be terminated with a comma."));
            $permachecks = false;
        }

        if (mb_strlen($settings['permasearch']) == 0) {
            $permasearchCount = 0;
        } else {
            $permasearchCount = (!mb_ereg(',', $settings['permasearch']) && mb_strlen($settings['permasearch'] > 0) ? 1 : count(explode(',', $settings['permasearch'])));
        }

        if (mb_strlen($settings['permareplace']) == 0) {
            $permareplaceCount = 0;
        } else {
            $permareplaceCount = (!mb_ereg(',', $settings['permareplace']) && mb_strlen($settings['permareplace'] > 0) ? 1 : count(explode(',', $settings['permareplace'])));
        }

        if ($permareplaceCount !== $permasearchCount) {
            LogUtil::registerError($this->__("Error! In your permalink settings, the search list and the replacement list for permalink cleansing have a different number of comma-separated elements. If you have 3 elements in the search list then there must be 3 elements in the replacement list."));
            $permachecks = false;
        }

        if ($settings['startpage']) {
            if (empty($settings['starttype']) || empty($settings['startfunc'])) {
                LogUtil::registerError($this->__("Error! When setting a startpage, starttype and startfunc are required fields."));
                unset($settings['startpage']);
                unset($settings['starttype']);
                unset($settings['startfunc']);
            }
        }

        if (!$permachecks) {
            unset($settings['permasearch']);
            unset($settings['permareplace']);
        }

        // delocalise page title
        $settings['pagetitle'] = str_replace($this->__('%pagetitle%'), '%pagetitle%', $settings['pagetitle']);
        $settings['pagetitle'] = str_replace($this->__('%sitename%'), '%sitename%', $settings['pagetitle']);
        $settings['pagetitle'] = str_replace($this->__('%modulename%'), '%modulename%', $settings['pagetitle']);

        // Write the vars
        $configvars = ModUtil::getVar(ModUtil::CONFIG_MODULE);
        foreach ($settings as $key => $value) {
            $oldvalue = System::getVar($key);
            if ($value != $oldvalue) {
                System::setVar($key, $value);
            }
        }

        // clear all cache and compile directories
        ModUtil::apiFunc('Settings', 'admin', 'clearallcompiledcaches');

        LogUtil::registerStatus($this->__('Done! Saved module configuration.'));

        $this->redirect(ModUtil::url('Settings', 'admin', 'modifyconfig'));
    }

    /**
     * display the ML settings form
     *
     * @return string html output
     */
    public function multilingual()
    {
        // security check
        if (!SecurityUtil::checkPermission('Settings::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        // get the server timezone and pass it to template - we should not allow to change this
        $this->view->assign('timezone_server', DateUtil::getTimezone());
        $this->view->assign('timezone_server_abbr', DateUtil::getTimezoneAbbr());

        return $this->view->fetch('settings_admin_multilingual.tpl');
    }

    /**
     * update ML settings
     *
     * @return mixed true if successful, false if unsuccessful, error string otherwise
     */
    public function updatemultilingual()
    {
        $this->checkCsrfToken();

        // security check
        if (!SecurityUtil::checkPermission('Settings::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        $url = ModUtil::url('Settings', 'admin', 'multilingual');

        $settings = array('mlsettings_language_i18n' => 'language_i18n',
                'mlsettings_timezone_offset' => 'timezone_offset',
                'mlsettings_timezone_server' => 'timezone_server',
                'mlsettings_multilingual' => 'multilingual',
                'mlsettings_language_detect' => 'language_detect',
                'mlsettings_languageurl' => 'languageurl');

        // we can't detect language if multilingual feature is off so reset this to false
        if (FormUtil::getPassedValue('mlsettings_multilingual', null, 'POST') == 0) {
            if (System::getVar('language_detect')) {
                System::setVar('language_detect', 0);
                unset($settings['mlsettings_language_detect']);
                LogUtil::registerStatus($this->__('Notice: Language detection is automatically disabled when multi-lingual features are disabled.'));
            }

            $deleteLangUrl = true;
        }

        if (isset($deleteLangUrl)) {
            // reset language settings
            SessionUtil::delVar('language');
            $url = preg_replace('#(.*)(&lang=[a-z-]{2,5})(.*)#i', '$1$3', $url);
        }

        // Write the vars
        $configvars = ModUtil::getVar(ModUtil::CONFIG_MODULE);
        foreach ($settings as $formname => $varname) {
            $newvalue = FormUtil::getPassedValue($formname, null, 'POST');
            $oldvalue = System::getVar($varname);
            if ($newvalue != $oldvalue) {
                System::setVar($varname, $newvalue);
            }
        }

        // clear all cache and compile directories
        ModUtil::apiFunc('Settings', 'admin', 'clearallcompiledcaches');

        // all done successfully
        LogUtil::registerStatus($this->__('Done! Saved localisation settings.'));

        $this->redirect($url);
    }

}
