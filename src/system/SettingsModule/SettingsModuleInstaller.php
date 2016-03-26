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

namespace Zikula\SettingsModule;

use System;
use Zikula_Core;
use ModUtil;
use ZLanguage;
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
        // Set up an initial value for a module variable. Note that all module
        // variables should be initialised with some value in this way rather
        // than just left blank, this helps the user-side code and means that
        // there doesn't need to be a check to see if the variable is set in
        // the rest of the code as it always will be.
        System::setVar('debug', '0');
        System::setVar('startdate', date('m/Y', time()));
        System::setVar('adminmail', 'example@example.com');
        System::setVar('Default_Theme', 'ZikulaBootstrapTheme');
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
        // Multilingual support
        foreach (ZLanguage::getInstalledLanguages() as $lang) {
            System::setVar('sitename_' . $lang, $this->__('Site name'));
            System::setVar('slogan_' . $lang, $this->__('Site description'));
            System::setVar('metakeywords_' . $lang, $this->__('zikula, portal, open source, web site, website, weblog, blog, content management system, cms, application framework'));
            System::setVar('defaultpagetitle_' . $lang, $this->__('Site name'));
            System::setVar('defaultmetadescription_' . $lang, $this->__('Site description'));
        }

        if (function_exists('apache_get_modules') && in_array('mod_rewrite', apache_get_modules())) {
            // Only strip entry point if "mod_rewrite" is available.
            System::setVar('shorturlsstripentrypoint', true);
        } else {
            System::setVar('shorturlsstripentrypoint', false);
        }

        System::setVar('shorturlsdefaultmodule', '');
        System::setVar('profilemodule', ((ModUtil::available('ZikulaProfileModule')) ? 'ZikulaProfileModule' : ''));
        System::setVar('messagemodule', '');
        System::setVar('languageurl', 0);
        System::setVar('ajaxtimeout', 5000);
        //! this is a comma-separated list of special characters to search for in permalinks
        System::setVar('permasearch', $this->__('À,Á,Â,Ã,Å,à,á,â,ã,å,Ò,Ó,Ô,Õ,Ø,ò,ó,ô,õ,ø,È,É,Ê,Ë,è,é,ê,ë,Ç,ç,Ì,Í,Î,Ï,ì,í,î,ï,Ù,Ú,Û,ù,ú,û,ÿ,Ñ,ñ,ß,ä,Ä,ö,Ö,ü,Ü'));
        //! this is a comma-separated list of special characters to replace in permalinks
        System::setVar('permareplace', $this->__('A,A,A,A,A,a,a,a,a,a,O,O,O,O,O,o,o,o,o,o,E,E,E,E,e,e,e,e,C,c,I,I,I,I,i,i,i,i,U,U,U,u,u,u,y,N,n,ss,ae,Ae,oe,Oe,ue,Ue'));

        System::setVar('language', ZLanguage::getLanguageCodeLegacy());
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

        /**
         * These entities are only used to install the tables and they
         * are @deprecated as of 1.4.0 because the Objectdata paradigm
         * is being removed at 2.0.0
         */
        try {
            DoctrineHelper::createSchema($this->entityManager, array(
                'Zikula\SettingsModule\Entity\ObjectdataAttributes',
                'Zikula\SettingsModule\Entity\ObjectdataLog',
                'Zikula\SettingsModule\Entity\ObjectdataMeta',
            ));
        } catch (\Exception $e) {
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
                EventUtil::registerPersistentModuleHandler($this->name, 'installer.module.deactivated', array('Zikula\SettingsModule\Listener\ModuleListener', 'moduleDeactivated'));

            case '2.9.8':
                $permasearch = System::getVar('permasearch');
                if (empty($permasearch)) {
                    System::setVar('permasearch', $this->__('À,Á,Â,Ã,Å,à,á,â,ã,å,Ò,Ó,Ô,Õ,Ø,ò,ó,ô,õ,ø,È,É,Ê,Ë,è,é,ê,ë,Ç,ç,Ì,Í,Î,Ï,ì,í,î,ï,Ù,Ú,Û,ù,ú,û,ÿ,Ñ,ñ,ß,ä,Ä,ö,Ö,ü,Ü'));
                }
                $permareplace = System::getVar('permareplace');
                if (empty($permareplace)) {
                    System::setVar('permareplace', $this->__('A,A,A,A,A,a,a,a,a,a,O,O,O,O,O,o,o,o,o,o,E,E,E,E,e,e,e,e,C,c,I,I,I,I,i,i,i,i,U,U,U,u,u,u,y,N,n,ss,ae,Ae,oe,Oe,ue,Ue'));
                }
                $locale = System::getVar('locale');
                if (empty($locale)) {
                    System::setVar('locale', ZLanguage::getLocale());
                }

            case '2.9.9':
                // update certain System vars to multilingual. provide default values for all locales using current value.
                // must directly manipulate System vars at DB level because using System::getVar() returns empty values due to ModUtil::setupMultilingual()
                $varsToChange = array('sitename', 'slogan', 'metakeywords', 'defaultpagetitle', 'defaultmetadescription');
                $SystemVars = $this->entityManager->getRepository('Zikula\ExtensionsModule\Entity\ExtensionVarEntity')->findBy(array('modname' => ModUtil::CONFIG_MODULE));
                /** @var \Zikula\ExtensionsModule\Entity\ExtensionVarEntity $modVar */
                foreach ($SystemVars as $modVar) {
                    if (in_array($modVar->getName(), $varsToChange)) {
                        foreach (ZLanguage::getInstalledLanguages() as $langcode) {
                            $newModVar = clone $modVar;
                            $newModVar->setName($modVar->getName() . '_' . $langcode);
                            $this->entityManager->persist($newModVar);
                        }
                        $this->entityManager->remove($modVar);
                    }
                }
                $this->entityManager->flush();

            case '2.9.10':
                System::setVar('startController', '');
                $newStargArgs = str_replace(',', '&', System::getVar('startargs')); // replace comma with `&`
                System::setVar('startargs', $newStargArgs);
            case '2.9.11': // ship with Core-1.4.2
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
