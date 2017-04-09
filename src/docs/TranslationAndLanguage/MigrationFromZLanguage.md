Replacing ZLanguage
===================

In the examples below, the provided variable (e.g. $container, $kernel, etc) must be adjusted to match the environment
from which it may be obtained. These variables are not defined explicitly in most cases.


ZLanguage::getLanguageCode() becomes $container->getParameter('locale')

ZLanguage::getModuleDomain('AcmeFooModule') becomes $kernel->getModule('AcmeFooModule')->getTranslationDomain()

ZLanguage::getDirection() becomes use `dir=auto` in the template instead

ZLanguage::getLanguageName($code) becomes \Intl::getLanguageBundle()->getLanguageName($locale)

ZLanguage::getInstalledLanguages() becomes $localeApi->getSupportedLocales()

ZLanguage::getInstalledLanguageNames() becomes $localeApi->getSupportedLocaleNames()

ZLanguage::setLocale('en') becomes depends on the need. probably set in the Request $request->setLocale('en')
                              maybe need to set the parameter in the container or in config.yml (or both)

ZLanguage::getLocale() becomes $request->getLocale()

ZLanguage::bind*Domain('AcmeFooModule') becomes no longer needed with symfony translator

ZLanguage::getEncoding() becomes $kernel->getCharset()

ZLanguage::isRequiredLangParam() is handled automatically by the router

ZLanguage::countryMap() becomes \Intl::getRegionBundle()->getCountryNames()
