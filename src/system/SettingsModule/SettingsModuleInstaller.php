<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SettingsModule;

use DateTimeZone;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Bundle\CoreBundle\Doctrine\Helper\SchemaHelper;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaKernel;
use Zikula\ExtensionsModule\AbstractExtension;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\ExtensionsModule\Installer\AbstractExtensionInstaller;
use Zikula\SettingsModule\Api\ApiInterface\LocaleApiInterface;

class SettingsModuleInstaller extends AbstractExtensionInstaller
{
    /**
     * @var LocaleApiInterface
     */
    private $localeApi;

    /**
     * @var string
     */
    private $locale;

    public function __construct(
        LocaleApiInterface $localeApi,
        $locale,
        AbstractExtension $extension,
        ManagerRegistry $managerRegistry,
        SchemaHelper $schemaTool,
        RequestStack $requestStack,
        TranslatorInterface $translator,
        VariableApiInterface $variableApi
    ) {
        $this->localeApi = $localeApi;
        $this->locale = $locale;
        parent::__construct($extension, $managerRegistry, $schemaTool, $requestStack, $translator, $variableApi);
    }

    public function install(): bool
    {
        $this->setSystemVar('startdate', date('m/Y'));
        $this->setSystemVar('adminmail', 'example@example.com');
        $this->setSystemVar('Default_Theme', 'ZikulaBootstrapTheme');
        $this->setSystemVar('timezone', date_default_timezone_get());
        $this->setSystemVar('Version_Num', ZikulaKernel::VERSION);
        $this->setSystemVar('multilingual', '1');
        $this->setSystemVar('theme_change', '0');
        $this->setSystemVar('UseCompression', '0');
        $this->setSystemVar('siteoff', 0);
        $this->setSystemVar('siteoffreason');
        $this->setSystemVar('language_detect', 0);

        // Multilingual support
        foreach ($this->localeApi->getSupportedLocales() as $lang) {
            $this->setSystemVar('sitename_' . $lang, $this->trans('Site name'));
            $this->setSystemVar('slogan_' . $lang, $this->trans('Site description'));
            $this->setSystemVar('defaultpagetitle_' . $lang, $this->trans('Site name'));
            $this->setSystemVar('defaultmetadescription_' . $lang, $this->trans('Site description'));
            $this->setSystemVar('startController_' . $lang, $this->getDefaultValue('startController'));
        }

        $this->setSystemVar(SettingsConstant::SYSTEM_VAR_PROFILE_MODULE);
        $this->setSystemVar(SettingsConstant::SYSTEM_VAR_MESSAGE_MODULE);
        $this->setSystemVar('languageurl', 0);
        $this->setSystemVar('ajaxtimeout', 5000);
        //! this is a comma-separated list of special characters to search for in permalinks
        $this->setSystemVar('permasearch', $this->getDefaultValue('permasearch'));
        //! this is a comma-separated list of special characters to replace in permalinks
        $this->setSystemVar('permareplace', $this->getDefaultValue('permareplace'));

        $this->setSystemVar('locale', $this->locale);

        // Initialisation successful
        return true;
    }

    public function upgrade(string $oldVersion): bool
    {
        switch ($oldVersion) {
            case '2.9.11': // shipped with Core-1.4.3
                $this->setSystemVar('UseCompression', (bool)$this->getSystemVar('UseCompression'));
            case '2.9.12': // shipped with Core-1.4.4
                // reconfigure TZ settings
                $this->setGuestTimeZone();
            case '2.9.13':
                $this->getVariableApi()->del(VariableApi::CONFIG, 'entrypoint');
                $this->getVariableApi()->del(VariableApi::CONFIG, 'shorturlsstripentrypoint');
                $this->getVariableApi()->del(VariableApi::CONFIG, 'shorturls');
                $this->getVariableApi()->del(VariableApi::CONFIG, 'shorturlsdefaultmodule');
            case '2.9.14': // shipped with Core-1.5.x + Core-2.0.15
                $this->getVariableApi()->del(VariableApi::CONFIG, 'Version_Sub');
                $this->setSystemVar('startController'); // reset to blank because of new format FQCN::method
            case '2.9.15':
                $varsToRemove = [
                    'funtext',
                    'reportlevel',
                    'idnnames',
                    'debug',
                    'debug_sql',
                    'useflags',
                    'language_i18n',
                    'startController',
                    'startargs'
                ];
                foreach ($varsToRemove as $varName) {
                    $this->getVariableApi()->del(VariableApi::CONFIG, $varName);
                }
                foreach ($this->localeApi->getSupportedLocales() as $lang) {
                    $this->setSystemVar('startController_' . $lang, $this->getDefaultValue('startController'));
                }
        }

        return true;
    }

    public function uninstall(): bool
    {
        // This module cannot be uninstalled.
        return false;
    }

    /**
     * @return string|array|null
     */
    private function getDefaultValue(string $name)
    {
        if ('permasearch' === $name) {
            return $this->trans('À,Á,Â,Ã,Å,à,á,â,ã,å,Ò,Ó,Ô,Õ,Ø,ò,ó,ô,õ,ø,È,É,Ê,Ë,è,é,ê,ë,Ç,ç,Ì,Í,Î,Ï,ì,í,î,ï,Ù,Ú,Û,ù,ú,û,ÿ,Ñ,ñ,ß,ä,Ä,ö,Ö,ü,Ü');
        }
        if ('permareplace' === $name) {
            return $this->trans('A,A,A,A,A,a,a,a,a,a,O,O,O,O,O,o,o,o,o,o,E,E,E,E,e,e,e,e,C,c,I,I,I,I,i,i,i,i,U,U,U,u,u,u,y,N,n,ss,ae,Ae,oe,Oe,ue,Ue');
        }
        if ('startController' === $name) {
            return [
                'controller' => '',
                'query' => '',
                'request' => '',
                'attributes' => ''
            ];
        }

        return null;
    }

    private function setSystemVar(string $name, $value = ''): void
    {
        $this->getVariableApi()->set(VariableApi::CONFIG, $name, $value);
    }

    private function getSystemVar(string $name)
    {
        return $this->getVariableApi()->getSystemVar($name);
    }

    private function setGuestTimeZone(): void
    {
        $existingOffset = $this->getSystemVar('timezone_offset');
        $actualOffset = (float) $existingOffset * 60; // express in minutes
        $timezoneAbbreviations = DateTimeZone::listAbbreviations();
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
