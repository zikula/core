<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2.1 (or at your option, any later version).
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
        System::setVar('debug', '0');
        System::setVar('sitename', $this->__('Site name'));
        System::setVar('slogan', $this->__('Site description'));
        System::setVar('metakeywords', $this->__('zikula, community, portal, portal web, open source, gpl, web site, website, weblog, blog, content management, content management system, web content management, web content management system, enterprise web content management, cms, application framework'));
        System::setVar('startdate', date('m/Y', time()));
        System::setVar('adminmail', 'me@example.com');
        System::setVar('Default_Theme', 'andreas08');
        System::setVar('anonymous', $this->__('Guest'));
        System::setVar('timezone_offset', '0');
        System::setVar('timezone_server', '0');
        System::setVar('funtext', '1');
        System::setVar('reportlevel', '0');
        System::setVar('startpage', 'blank');
        System::setVar('Version_Num', System::VERSION_NUM);
        System::setVar('Version_ID', System::VERSION_ID);
        System::setVar('Version_Sub', System::VERSION_SUB);
        System::setVar('debug_sql', '0');
        System::setVar('multilingual', '1');
        System::setVar('useflags', '0');
        System::setVar('theme_change', '0');
        System::setVar('UseCompression', '0');
        System::setVar('errordisplay', 1);
        System::setVar('errorlog', 0);
        System::setVar('errorlogtype', 0);
        System::setVar('errormailto', 'me@example.com');
        System::setVar('siteoff', 0);
        System::setVar('siteoffreason', '');
        System::setVar('starttype', '');
        System::setVar('startfunc', '');
        System::setVar('startargs', '');
        System::setVar('entrypoint', 'index.php');
        System::setVar('language_detect', 0);
        System::setVar('shorturls', false);
        System::setVar('shorturlstype', '0');
        System::setVar('shorturlsext', 'html');
        System::setVar('shorturlsseparator', '-');
        System::setVar('shorturlsstripentrypoint', false);
        System::setVar('shorturlsdefaultmodule', '');
        $groupModules = $this->_getDefaultGroupModules();
        System::setVar('profilemodule', $groupModules['profile']);
        System::setVar('messagemodule', $groupModules['message']);
        System::setVar('languageurl', 0);
        System::setVar('ajaxtimeout', 5000);
        //! this is a comma-separated list of special characters to search for in permalinks
        System::setVar('permasearch',  $this->__('À,Á,Â,Ã,Å,à,á,â,ã,å,Ò,Ó,Ô,Õ,Ø,ò,ó,ô,õ,ø,È,É,Ê,Ë,è,é,ê,ë,Ç,ç,Ì,Í,Î,Ï,ì,í,î,ï,Ù,Ú,Û,ù,ú,û,ÿ,Ñ,ñ,ß,ä,Ä,ö,Ö,ü,Ü'));
        //! this is a comma-separated list of special characters to replace in permalinks
        System::setVar('permareplace', $this->__('A,A,A,A,A,a,a,a,a,a,O,O,O,O,O,o,o,o,o,o,E,E,E,E,e,e,e,e,C,c,I,I,I,I,i,i,i,i,U,U,U,u,u,u,y,N,n,s,a,A,o,O,u,U'));

        System::setVar('language',ZLanguage::getLanguageCodeLegacy());
        System::setVar('locale', ZLanguage::getLocale());
        System::setVar('language_i18n', ZLanguage::getlanguageCode());
        System::setVar('language_bc', 1);

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
     * upgrade the settings module from an old version
     *
     * This function must consider all the released versions of the module!
     * If the upgrade fails at some point, it returns the last upgraded version.
     *
     * @param        string   $oldVersion   version number string to upgrade from
     * @return       mixed    true on success, last valid version string or false if fails
     */
    public function upgrade($oldversion)
    {
        // always ensure that the version info is upgraded
        System::setVar('Version_Num', System::VERSION_NUM);
        System::setVar('Version_ID', System::VERSION_ID);
        System::setVar('Version_Sub', System::VERSION_SUB);

        // Upgrade dependent on old version number
        switch ($oldversion)
        {
            case '2.5':
                System::delVar('jsquicktags');
                System::delVar('backend_title');
                System::delVar('refereronprint');
                System::delVar('storyorder');
                System::delVar('backend_language');
                System::delVar('site_logo');

            case '2.6':
                System::setVar('updatelastchecked', 0);
                System::setVar('updatefrequency', 7);
                System::setVar('updateversion', System::VERSION_NUM);
                System::setVar('updatecheck', true);

            case '2.7':
                System::setVar('language_i18n', 'en');
                System::setVar('language_bc', 1);
                System::setVar('languageurl', 0);
                System::setVar('ajaxtimeout', 5000);
                //! this is a comma-separated list of special characters to search for in permalinks
                System::setVar('permasearch',  $this->$this->__('À,Á,Â,Ã,Å,à,á,â,ã,å,Ò,Ó,Ô,Õ,Ø,ò,ó,ô,õ,ø,È,É,Ê,Ë,è,é,ê,ë,Ç,ç,Ì,Í,Î,Ï,ì,í,î,ï,Ù,Ú,Û,ù,ú,û,ÿ,Ñ,ñ,ß,ä,Ä,ö,Ö,ü,Ü'));
                //! this is a comma-separated list of special characters to replace in permalinks
                System::setVar('permareplace', $this->$this->__('A,A,A,A,A,a,a,a,a,a,O,O,O,O,O,o,o,o,o,o,E,E,E,E,e,e,e,e,C,c,I,I,I,I,i,i,i,i,U,U,U,u,u,u,y,N,n,s,a,A,o,O,u,U'));

            case '2.8':
                System::delVar('dyn_keywords');
            case '2.9':
            case '2.9.1':
                System::delVar('timezone_info');
            case '2.9.2':
                $tables = DBUtil::getTables();
                $modulesTable = $tables['modules'];
                $name = $tables['modules_column']['name'];
                $sql = "DELETE FROM $modulesTable WHERE $name = 'ObjectData' OR $name = 'Workflow'";
                DBUtil::executeSQL($sql);
                DBUtil::createTable('workflows');
            case '2.9.3':
            // future upgrade routines
        }

        // Update successful
        return true;
    }

    /**
     * delete the settings module
     * This function is only ever called once during the lifetime of a particular
     * module instance
     * @return bool true if successful, false otherwise
     */
    public function uninstall()
    {
        // Deletion fail - we dont want users disabling this module!
        return false;
    }

    /**
     * utility function to retrieve default values for module group vars
     *
     * @return array with determined module names
     */
    private function _getDefaultGroupModules()
    {
        $groupModules = array();

        $groupModules['profile'] = '';
        if (ModUtil::available('Profile')) {
            $groupModules['profile'] = 'Profile';

        } elseif (ModUtil::available('MyProfile')) {
            $groupModules['profile'] = 'MyProfile';
        }

        $groupModules['message'] = '';
        if (ModUtil::available('InterCom')) {
            $groupModules['message'] = 'InterCom';
        }

        return $groupModules;
    }
}