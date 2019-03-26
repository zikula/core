<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SettingsModule;

use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;
use Zikula\Core\AbstractExtensionInstaller;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\ExtensionsModule\Entity\ExtensionVarEntity;
use Zikula\ExtensionsModule\Entity\Repository\ExtensionVarRepository;
use Zikula\SettingsModule\Api\LocaleApi;

/**
 * Installation and upgrade routines for the settings module.
 *
 * PLEASE NOTE CAREFULLY.  The use of System::get/set/delVar() is deliberate
 * we cannot use $this->get/set/delVar() because the keys will be incorrectly
 * generated (System instead of ZConfig).
 */
class SettingsModuleInstaller extends AbstractExtensionInstaller
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
        $this->setSystemVar('debug', '0');
        $this->setSystemVar('startdate', date('m/Y', time()));
        $this->setSystemVar('adminmail', 'example@example.com');
        $this->setSystemVar('Default_Theme', 'ZikulaBootstrapTheme');
        $this->setSystemVar('timezone', date_default_timezone_get());
        $this->setSystemVar('funtext', '1');
        $this->setSystemVar('reportlevel', '0');
        $this->setSystemVar('Version_Num', ZikulaKernel::VERSION);
        $this->setSystemVar('Version_Sub', ZikulaKernel::VERSION_SUB);
        $this->setSystemVar('debug_sql', '0');
        $this->setSystemVar('multilingual', '1');
        $this->setSystemVar('useflags', '0');
        $this->setSystemVar('theme_change', '0');
        $this->setSystemVar('UseCompression', '0');
        $this->setSystemVar('siteoff', 0);
        $this->setSystemVar('siteoffreason', '');
        $this->setSystemVar('startController', '');
        $this->setSystemVar('startargs', '');
        $this->setSystemVar('language_detect', 0);
        // Multilingual support
        foreach ($this->container->get(LocaleApi::class)->getSupportedLocales() as $lang) {
            $this->setSystemVar('sitename_' . $lang, $this->__('Site name'));
            $this->setSystemVar('slogan_' . $lang, $this->__('Site description'));
            $this->setSystemVar('defaultpagetitle_' . $lang, $this->__('Site name'));
            $this->setSystemVar('defaultmetadescription_' . $lang, $this->__('Site description'));
        }

        $this->setSystemVar(SettingsConstant::SYSTEM_VAR_PROFILE_MODULE, '');
        $this->setSystemVar(SettingsConstant::SYSTEM_VAR_MESSAGE_MODULE, '');
        $this->setSystemVar('languageurl', 0);
        $this->setSystemVar('ajaxtimeout', 5000);
        //! this is a comma-separated list of special characters to search for in permalinks
        $this->setSystemVar('permasearch', $this->__('À,Á,Â,Ã,Å,à,á,â,ã,å,Ò,Ó,Ô,Õ,Ø,ò,ó,ô,õ,ø,È,É,Ê,Ë,è,é,ê,ë,Ç,ç,Ì,Í,Î,Ï,ì,í,î,ï,Ù,Ú,Û,ù,ú,û,ÿ,Ñ,ñ,ß,ä,Ä,ö,Ö,ü,Ü'));
        //! this is a comma-separated list of special characters to replace in permalinks
        $this->setSystemVar('permareplace', $this->__('A,A,A,A,A,a,a,a,a,a,O,O,O,O,O,o,o,o,o,o,E,E,E,E,e,e,e,e,C,c,I,I,I,I,i,i,i,i,U,U,U,u,u,u,y,N,n,ss,ae,Ae,oe,Oe,ue,Ue'));

        $locale = $this->container->getParameter('locale');
        $this->setSystemVar('locale', $locale);
        $this->setSystemVar('language_i18n', $locale);

        $this->setSystemVar('idnnames', 1);

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
        $request = $this->container->get('request_stack')->getMasterRequest();
        // Upgrade dependent on old version number
        switch ($oldversion) {
            case '2.9.7':
            case '2.9.8':
                $permasearch = $this->getSystemVar('permasearch');
                if (empty($permasearch)) {
                    $this->setSystemVar('permasearch', $this->__('À,Á,Â,Ã,Å,à,á,â,ã,å,Ò,Ó,Ô,Õ,Ø,ò,ó,ô,õ,ø,È,É,Ê,Ë,è,é,ê,ë,Ç,ç,Ì,Í,Î,Ï,ì,í,î,ï,Ù,Ú,Û,ù,ú,û,ÿ,Ñ,ñ,ß,ä,Ä,ö,Ö,ü,Ü'));
                }
                $permareplace = $this->getSystemVar('permareplace');
                if (empty($permareplace)) {
                    $this->setSystemVar('permareplace', $this->__('A,A,A,A,A,a,a,a,a,a,O,O,O,O,O,o,o,o,o,o,E,E,E,E,e,e,e,e,C,c,I,I,I,I,i,i,i,i,U,U,U,u,u,u,y,N,n,ss,ae,Ae,oe,Oe,ue,Ue'));
                }
                $locale = $this->getSystemVar('locale');
                if (empty($locale)) {
                    $this->setSystemVar('locale', $request->getLocale());
                }

            case '2.9.9':
                // update certain System vars to multilingual. provide default values for all locales using current value.
                // must directly manipulate System vars at DB level because using $this->getSystemVar() returns empty values
                $varsToChange = ['sitename', 'slogan', 'defaultpagetitle', 'defaultmetadescription'];
                $systemVars = $this->container->get(ExtensionVarRepository::class)->findBy(['modname' => VariableApi::CONFIG]);
                /** @var ExtensionVarEntity $modVar */
                foreach ($systemVars as $modVar) {
                    if (in_array($modVar->getName(), $varsToChange)) {
                        foreach ($this->container->get(LocaleApi::class)->getSupportedLocales() as $langcode) {
                            $newModVar = clone $modVar;
                            $newModVar->setName($modVar->getName() . '_' . $langcode);
                            $this->entityManager->persist($newModVar);
                        }
                        $this->entityManager->remove($modVar);
                    }
                }
                $this->entityManager->flush();

            case '2.9.10':
                $this->setSystemVar('startController', '');
                $newStargArgs = str_replace(',', '&', $this->getSystemVar('startargs')); // replace comma with `&`
                $this->setSystemVar('startargs', $newStargArgs);
            case '2.9.11':
                $this->setSystemVar('useCompression', (bool)$this->getSystemVar('useCompression'));
            case '2.9.12': // ship with Core-1.4.4
                // reconfigure TZ settings
                $this->setGuestTimeZone();
            case '2.9.13':
                $variableApi = $this->container->get(VariableApi::class);
                $variableApi->del(VariableApi::CONFIG, 'entrypoint');
                $variableApi->del(VariableApi::CONFIG, 'shorturlsstripentrypoint');
                $variableApi->del(VariableApi::CONFIG, 'shorturls');
                $variableApi->del(VariableApi::CONFIG, 'shorturlsdefaultmodule');
            case '2.9.14': // ship with Core-1.5.0
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

    private function setSystemVar($name, $value = '')
    {
        return $this->container->get(VariableApi::class)->set(VariableApi::CONFIG, $name, $value);
    }

    /**
     * @param string $name
     */
    private function getSystemVar($name)
    {
        return $this->container->get(VariableApi::class)->getSystemVar($name);
    }

    /**
     * upgrade helper method
     */
    private function setGuestTimeZone()
    {
        $existingOffset = $this->getSystemVar('timezone_offset');
        $actualOffset = (float) $existingOffset * 60; // express in minutes
        $timezoneAbbreviations = \DateTimeZone::listAbbreviations();
        $timeZone = date_default_timezone_get();
        foreach ($timezoneAbbreviations as $abbreviation => $zones) {
            foreach ($zones as $zone) {
                if ($zone['offset'] === $actualOffset) {
                    $timeZone = $zone['timezone_id'];
                    break 2;
                }
            }
        }
        $this->setSystemVar('timezone', $timeZone);
    }
}
