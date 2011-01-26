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

class Settings_Installer extends Zikula_Installer
{
    /**
     * initialise the settings module
     * This function is only ever called once during the lifetime of a particular
     * module instance
     * @return bool true if successful, false otherwise
     */
    public function install()
    {
        // Set up an initial value for a module variable.  Note that all module
        // variables should be initialised with some value in this way rather
        // than just left blank, this helps the user-side code and means that
        // there doesn't need to be a check to see if the variable is set in
        // the rest of the code as it always will be
        $this->setVar('debug', '0');
        $this->setVar('sitename', $this->__('Site name'));
        $this->setVar('slogan', $this->__('Site description'));
        $this->setVar('metakeywords', $this->__('zikula, portal, portal web, open source, web site, website, weblog, blog, content management, content management system, web content management, web content management system, enterprise web content management, cms, application framework'));
        $this->setVar('defaultpagetitle', $this->__('Site name'));
        $this->setVar('defaultmetadescription', $this->__('Site description'));
        $this->setVar('startdate', date('m/Y', time()));
        $this->setVar('adminmail', 'example@example.com');
        $this->setVar('Default_Theme', 'Andreas08');
        $this->setVar('anonymous', $this->__('Guest'));
        $this->setVar('timezone_offset', '0');
        $this->setVar('timezone_server', '0');
        $this->setVar('funtext', '1');
        $this->setVar('reportlevel', '0');
        $this->setVar('startpage', '');
        $this->setVar('Version_Num', System::VERSION_NUM);
        $this->setVar('Version_ID', System::VERSION_ID);
        $this->setVar('Version_Sub', System::VERSION_SUB);
        $this->setVar('debug_sql', '0');
        $this->setVar('multilingual', '1');
        $this->setVar('useflags', '0');
        $this->setVar('theme_change', '0');
        $this->setVar('UseCompression', '0');
        $this->setVar('siteoff', 0);
        $this->setVar('siteoffreason', '');
        $this->setVar('starttype', '');
        $this->setVar('startfunc', '');
        $this->setVar('startargs', '');
        $this->setVar('entrypoint', 'index.php');
        $this->setVar('language_detect', 0);
        $this->setVar('shorturls', false);
        $this->setVar('shorturlstype', '0');
        $this->setVar('shorturlsseparator', '-');
        $this->setVar('shorturlsstripentrypoint', false);
        $this->setVar('shorturlsdefaultmodule', '');
        $this->setVar('profilemodule', ModUtil::available('Profile') ? 'Profile' : '');
        $this->setVar('messagemodule', '');
        $this->setVar('languageurl', 0);
        $this->setVar('ajaxtimeout', 5000);
        //! this is a comma-separated list of special characters to search for in permalinks
        $this->setVar('permasearch',  $this->__('À,Á,Â,Ã,Å,à,á,â,ã,å,Ò,Ó,Ô,Õ,Ø,ò,ó,ô,õ,ø,È,É,Ê,Ë,è,é,ê,ë,Ç,ç,Ì,Í,Î,Ï,ì,í,î,ï,Ù,Ú,Û,ù,ú,û,ÿ,Ñ,ñ,ß,ä,Ä,ö,Ö,ü,Ü'));
        //! this is a comma-separated list of special characters to replace in permalinks
        $this->setVar('permareplace', $this->__('A,A,A,A,A,a,a,a,a,a,O,O,O,O,O,o,o,o,o,o,E,E,E,E,e,e,e,e,C,c,I,I,I,I,i,i,i,i,U,U,U,u,u,u,y,N,n,s,a,A,o,O,u,U'));

        $this->setVar('language',ZLanguage::getLanguageCodeLegacy());
        $this->setVar('locale', ZLanguage::getLocale());
        $this->setVar('language_i18n', ZLanguage::getlanguageCode());

        $this->setVar('idnnames', 1);

        if (!DBUtil::createTable('workflows')) {
            return false;
        }

        if (!DBUtil::createTable('objectdata_attributes')) {
            return false;
        }

        if (!DBUtil::createTable('objectdata_log')) {
            return false;
        }

        if (!DBUtil::createTable('objectdata_meta')) {
            return false;
        }

        // Initialisation successful
        return true;
    }

    /**
     * Upgrade the settings module from an old version.
     *
     * This function must consider all the released versions of the module!
     * If the upgrade fails at some point, it returns the last upgraded version.
     *
     * @param  string   $oldVersion   version number string to upgrade from.
     *
     * @return boolean|string True on success, last valid version string or false if fails.
     */
    public function upgrade($oldversion)
    {
        // always ensure that the version info is upgraded
        $this->setVar('Version_Num', System::VERSION_NUM);
        $this->setVar('Version_ID', System::VERSION_ID);
        $this->setVar('Version_Sub', System::VERSION_SUB);

        // Upgrade dependent on old version number
        switch ($oldversion)
        {
            case '2.5':
                $this->delVar('jsquicktags');
                $this->delVar('backend_title');
                $this->delVar('refereronprint');
                $this->delVar('storyorder');
                $this->delVar('backend_language');
                $this->delVar('site_logo');

            case '2.6':
                $this->setVar('updatelastchecked', 0);
                $this->setVar('updatefrequency', 7);
                $this->setVar('updateversion', System::VERSION_NUM);
                $this->setVar('updatecheck', true);

            case '2.7':
                $this->setVar('language_i18n', 'en');
                $this->setVar('language_bc', 1);
                $this->setVar('languageurl', 0);
                $this->setVar('ajaxtimeout', 5000);
                //! this is a comma-separated list of special characters to search for in permalinks
                $this->setVar('permasearch',  $this->$this->__('À,Á,Â,Ã,Å,à,á,â,ã,å,Ò,Ó,Ô,Õ,Ø,ò,ó,ô,õ,ø,È,É,Ê,Ë,è,é,ê,ë,Ç,ç,Ì,Í,Î,Ï,ì,í,î,ï,Ù,Ú,Û,ù,ú,û,ÿ,Ñ,ñ,ß,ä,Ä,ö,Ö,ü,Ü'));
                //! this is a comma-separated list of special characters to replace in permalinks
                $this->setVar('permareplace', $this->$this->__('A,A,A,A,A,a,a,a,a,a,O,O,O,O,O,o,o,o,o,o,E,E,E,E,e,e,e,e,C,c,I,I,I,I,i,i,i,i,U,U,U,u,u,u,y,N,n,s,a,A,o,O,u,U'));

            case '2.8':
                $this->delVar('dyn_keywords');
            case '2.9':
            case '2.9.1':
                $this->delVar('timezone_info');
            case '2.9.2':
                $tables = DBUtil::getTables();
                $modulesTable = $tables['modules'];
                $name = $tables['modules_column']['name'];
                $sql = "DELETE FROM $modulesTable WHERE $name = 'ObjectData' OR $name = 'Workflow'";
                DBUtil::executeSQL($sql);
            case '2.9.3':
                // This may have been set by the Users module upgrade already, so only set it if it does not exist.
                $systemIdnSetting = $this->getVar('idnnames', null);
                if (isset($systemIdnSetting)) {
                    if (ModUtil::available('Users')) {
                        $usersIdnSetting = ModUtil::getVar('Users', 'idnnames', null);
                    }
                    $this->setVar('idnnames', isset($usersIdnSetting) ? (bool)$usersIdnSetting : true);
                }
                $this->delVar('language_bc');
            case '2.9.4':
                $this->setVar('defaultpagetitle', $this->__('Site name'));
                $this->setVar('defaultmetadescription', $this->__('Site description'));
            case '2.9.5':
                $this->delVar('shorturlsext');
            case '2.9.6':
                // future upgrade routines
        }

        // Update successful
        return true;
    }

    /**
     * Delete the settings module.
     *
     * @return boolean false
     */
    public function uninstall()
    {
        // This module cannot be uninstalled.
        return false;
    }
}