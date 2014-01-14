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

namespace Zikula\Module\SettingsModule;

use System;
use Zikula_Core;
use ModUtil;
use ZLanguage;
use DBUtil;
use DoctrineHelper;
use EventUtil;

/**
 * Installation and upgrade routines for the settings module
 *
 * PLEASE NOTE CAREFULLY.  The use of System::get/set/delVar() is deliberate
 * we cannot use $this->get/set/delVar() because the keys will be incorrectly
 * generated (System instead of ZConfig).
 */
class SettingsModuleInstaller extends \Zikula_AbstractInstaller
{
    /**
     * Initialise the settings module.
     *
     * @return boolean
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
        System::setVar('metakeywords', $this->__('zikula, portal, portal web, open source, web site, website, weblog, blog, content management, content management system, web content management, web content management system, enterprise web content management, cms, application framework'));
        System::setVar('defaultpagetitle', $this->__('Site name'));
        System::setVar('defaultmetadescription', $this->__('Site description'));
        System::setVar('startdate', date('m/Y', time()));
        System::setVar('adminmail', 'example@example.com');
        System::setVar('Default_Theme', 'Andreas08');
        System::setVar('timezone_offset', '0');
        System::setVar('timezone_server', '0');
        System::setVar('funtext', '1');
        System::setVar('reportlevel', '0');
        System::setVar('startpage', '');
        System::setVar('Version_Num', Zikula_Core::VERSION_NUM);
        System::setVar('Version_ID', Zikula_Core::VERSION_ID);
        System::setVar('Version_Sub', Zikula_Core::VERSION_SUB);
        System::setVar('debug_sql', '0');
        System::setVar('multilingual', '1');
        System::setVar('useflags', '0');
        System::setVar('theme_change', '0');
        System::setVar('UseCompression', '0');
        System::setVar('siteoff', 0);
        System::setVar('siteoffreason', '');
        System::setVar('starttype', '');
        System::setVar('startfunc', '');
        System::setVar('startargs', '');
        System::setVar('entrypoint', 'index.php');
        System::setVar('language_detect', 0);
        System::setVar('shorturls', false);
        System::setVar('shorturlstype', '0');
        System::setVar('shorturlsseparator', '-');
        System::setVar('shorturlsstripentrypoint', true);
        System::setVar('shorturlsdefaultmodule', '');
        System::setVar('profilemodule', ModUtil::available('Profile') ? 'Profile' : '');
        System::setVar('messagemodule', '');
        System::setVar('languageurl', 0);
        System::setVar('ajaxtimeout', 5000);
        //! this is a comma-separated list of special characters to search for in permalinks
        System::setVar('permasearch',  $this->__('À,Á,Â,Ã,Å,à,á,â,ã,å,Ò,Ó,Ô,Õ,Ø,ò,ó,ô,õ,ø,È,É,Ê,Ë,è,é,ê,ë,Ç,ç,Ì,Í,Î,Ï,ì,í,î,ï,Ù,Ú,Û,ù,ú,û,ÿ,Ñ,ñ,ß,ä,Ä,ö,Ö,ü,Ü'));
        //! this is a comma-separated list of special characters to replace in permalinks
        System::setVar('permareplace', $this->__('A,A,A,A,A,a,a,a,a,a,O,O,O,O,O,o,o,o,o,o,E,E,E,E,e,e,e,e,C,c,I,I,I,I,i,i,i,i,U,U,U,u,u,u,y,N,n,ss,ae,Ae,oe,Oe,ue,Ue'));

        System::setVar('language',ZLanguage::getLanguageCodeLegacy());
        System::setVar('locale', ZLanguage::getLocale());
        System::setVar('language_i18n', ZLanguage::getlanguageCode());

        System::setVar('idnnames', 1);

        // create schema
        try {
            DoctrineHelper::createSchema($this->entityManager, array(
                'Zikula\Core\Doctrine\Entity\WorkflowEntity',
            ));
        } catch (\Exception $e) {
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
     * upgrade the module from an old version
     *
     * @param  string $oldversion version number string to upgrade from
     *
     * @return bool|string true on success, last valid version string or false if fails
     */
    public function upgrade($oldversion)
    {
        // always ensure that the version info is upgraded
        System::setVar('Version_Num', Zikula_Core::VERSION_NUM);
        System::setVar('Version_ID', Zikula_Core::VERSION_ID);
        System::setVar('Version_Sub', Zikula_Core::VERSION_SUB);

        // Upgrade dependent on old version number
        switch ($oldversion) {
            case '2.9.7':
                EventUtil::registerPersistentModuleHandler($this->name, 'installer.module.deactivated', array('Zikula\Module\SettingsModule\Listener\ModuleListener', 'moduleDeactivated'));
            // future upgrade routines
            case '2.9.8':
                // current version
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
