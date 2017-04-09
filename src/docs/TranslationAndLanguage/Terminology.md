Translation Terminology
=======================

The technology for translations used in this guide is simple - anywhere in project we use English strings and English descriptions.
These are translated to other language stored in files or database and loaded on demand instead of English strings. 

## Terminology
 * **Symfony Translator** - Symfony gettext technology used for translations 
 * **Zikula legacy translator** - Zikula prior to 1.4.x uses own implementation of gettext translation technology.
 * **Zikula Symfony Translator** - Extends Symfony Translator to support Zikula translation conventions. 
 * **message** -  in basic it is translation array element example: 'English string' => 'Translated string'
 * **translation template** - .pot file for gettext used only in process of creating new translations. Not used in actual translating. 
 * **domain** - An optional way to organize messages into groups (e.g. Symfony admin, navigation or the default messages
   Zikula zikula, theme_themename, module_modulename and 2.0.0 bundlename)
 * **catalogue** - Gettext way to organize messages into groups (LC_MESSAGES, LC_TYPE, LC_ALL) 
 * **locale** - The locale that the translations are for (e.g. en_GB, en, etc);
 * **loader** - How Symfony/Zikula should load and parse the file (e.g. xlf, php, yml, etc Zikula .po .mo). 

For more information please refer to http://symfony.com/doc/current/translation.html
