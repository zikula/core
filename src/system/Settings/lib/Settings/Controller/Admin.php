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


class Settings_Controller_Admin extends Zikula_Controller
{
    /**
     * entry point for the module
     *
     * @return string html output
     */
    public function main()
    {
        // Security check will be done in modifyconfig()
        return $this->modifyconfig();
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

        $this->view->setCaching(false);

        // get all config vars and assign them to the template
        $configvars = ModUtil::getVar(PN_CONFIG_MODULE);
        // since config vars are serialised and module vars aren't we
        // need to unserialise each config var in turn before assigning
        // them to the template
        foreach ($configvars as $key => $configvar) {
            $configvars[$key] = $configvar;
        }

        $this->view->assign('settings', $configvars);

        return $this->view->fetch('settings_admin_modifyconfig.tpl');
    }

    /**
     * update main site settings
     *
     * @return mixed true if successful, false if unsuccessful, error string otherwise
     */
    public function updateconfig() {

        // security check
        if (!SecurityUtil::checkPermission('Settings::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        // get settings from form - do before authid check
        $settings = FormUtil::getPassedValue('settings', null, 'POST');

        // if this form wasnt posted to redirect back
        if ($settings === null) {
            return System::redirect(ModUtil::url('Settings', 'admin', 'modifyconfig'));
        }

        // confirm the forms auth key
        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError();
        }

        // validate the entry point
        $falseEntryPoints = array('admin.php', 'ajax.php',  'install.php', 'upgrade.php', 'user.php');
        $entryPointExt = pathinfo($settings['entrypoint'], PATHINFO_EXTENSION);

        if (in_array($settings['entrypoint'], $falseEntryPoints) || !file_exists($settings['entrypoint'])
                || strtolower($entryPointExt) != 'php') {
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

        if (!$permachecks) {
            unset($settings['permasearch']);
            unset($settings['permareplace']);
        }

        // Write the vars
        $configvars = ModUtil::getVar(PN_CONFIG_MODULE);
        foreach($settings as $key => $value) {
            $oldvalue = System::getVar($key);
            if ($value != $oldvalue) {
                System::setVar($key, $value);
            }
        }

        // clear all cache and compile directories
        ModUtil::apiFunc('Settings', 'admin', 'clearallcompiledcaches');

        LogUtil::registerStatus($this->__('Done! Saved module configuration.'));

        // Let any other modules know that the modules configuration has been updated
        $this->callHooks('module','updateconfig','Settings', array('module' => 'Settings'));

        return System::redirect(ModUtil::url('Settings', 'admin', 'modifyconfig'));
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

        $this->view->setCaching(false);

        // get all config vars and assign them to the template
        $configvars = ModUtil::getVar(PN_CONFIG_MODULE);
        foreach ($configvars as $key => $configvar) {
            $configvars[$key] = $configvar;
        }

        // get the server timezone - we should not allow to change this
        $configvars['timezone_server'] = DateUtil::getTimezone();
        $configvars['timezone_server_abbr'] = DateUtil::getTimezoneAbbr();
        $this->view->assign($configvars);

        return $this->view->fetch('settings_admin_multilingual.tpl');
    }

    /**
     * update ML settings
     *
     * @return mixed true if successful, false if unsuccessful, error string otherwise
     */
    public function updatemultilingual()
    {
        // security check
        if (!SecurityUtil::checkPermission('Settings::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        $url = ModUtil::url('Settings', 'admin', 'multilingual');

        // confirm the forms auth key
        if (!SecurityUtil::confirmAuthKey()) {
            LogUtil::registerAuthidError();
            return System::redirect($url);
        }

        $settings = array('mlsettings_language_i18n'   => 'language_i18n',
                'mlsettings_timezone_offset' => 'timezone_offset',
                'mlsettings_timezone_server' => 'timezone_server',
                'mlsettings_multilingual'    => 'multilingual',
                'mlsettings_language_detect' => 'language_detect',
                'mlsettings_language_bc'     => 'language_bc',
                'mlsettings_languageurl'     => 'languageurl');

        // we can't detect language if multilingual feature is off so reset this to false
        if (FormUtil::getPassedValue('mlsettings_multilingual', null, 'POST') == 0) {
            if (System::getVar('language_detect')) {
                System::setVar('language_detect', 0);
                unset($settings['mlsettings_language_detect']);
                LogUtil::registerStatus($this->__('Notice: Language detection is automatically disabled when multi-lingual features are disabled.'));
            }

            $deleteLangUrl = true;
        }

        if (FormUtil::getPassedValue('mlsettings_language_bc', null, 'POST') == 0) {
            $lang = System::getVar('language_i18n');
            $newvalue = substr($lang, 0, (strpos($lang, '-') ? strpos($lang, '-') : strlen($lang)));
            if ($lang != $newvalue) {
                System::setVar('language_i18n', $newvalue);
                unset($settings['mlsettings_language_i18n']);
                LogUtil::registerStatus($this->__('Warning! The system language has been changed because language variations have been disabled.'));
                $deleteLangUrl = true;
            }
        }

        if (isset($deleteLangUrl)) {
            // reset language settings
            SessionUtil::delVar('language');
            $url = preg_replace('#(.*)(&lang=[a-z-]{2,5})(.*)#i', '$1$3', $url);
        }

        // Write the vars
        $configvars = ModUtil::getVar(PN_CONFIG_MODULE);
        foreach($settings as $formname => $varname) {
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

        return System::redirect($url);
    }

    /**
     * display the error handling settings form
     *
     * @return string html output
     */
    public function errorhandling()
    {
        // security check
        if (!SecurityUtil::checkPermission('Settings::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        $this->view->setCaching(false);

        // get all config vars and assign them to the template
        $configvars = ModUtil::getVar(PN_CONFIG_MODULE);
        // since config vars are serialised and module vars aren't we
        // need to unserialise each config var in turn before assigning
        // them to the template
        foreach ($configvars as $key => $configvar) {
            $configvars[$key] = $configvar;
        }
        // add the development flag
        $configvars['development'] = System::getVar('development');
        $this->view->assign($configvars);

        return $this->view->fetch('settings_admin_errorhandling.tpl');
    }

    /**
     * update error handling settings
     *
     * @return mixed true if successful, false if unsuccessful, error string otherwise
     */
    public function updateerrorhandling() {

        // security check
        if (!SecurityUtil::checkPermission('Settings::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        // confirm the forms auth key
        if (!SecurityUtil::confirmAuthKey()) {
            return LogUtil::registerAuthidError();
        }

        $settings = array('errorsettings_errordisplay' => 'errordisplay',
                'errorsettings_errorlog'     => 'errorlog',
                'errorsettings_errormailto'  => 'errormailto',
                'errorsettings_errorlogtype' => 'errorlogtype');
        // Write the vars
        $configvars = ModUtil::getVar(PN_CONFIG_MODULE);
        foreach($settings as $formname => $varname) {
            $newvalue = FormUtil::getPassedValue($formname, null, 'POST');
            $oldvalue = System::getVar($varname);
            if ($newvalue != $oldvalue) {
                System::setVar($varname, $newvalue);
            }
        }

        // clear all cache and compile directories
        ModUtil::apiFunc('Settings', 'admin', 'clearallcompiledcaches');

        // all done successfully
        LogUtil::registerStatus($this->__('Done! Saved module configuration.'));

        return System::redirect(ModUtil::url('Settings', 'admin', 'errorhandling'));
    }
}