Zikula Translation's Guide
=================

This guide is intended to provide overview of translation technologies, usage, implementations and developement.

## Table of contents

* Introduction
* Terminology
* User's guide 
	* Zikula legacy translator
	* Zikula Symfony Translator
* Translators
* Developers guide
	* Zikula legacy translator
	* Symfony translator service
	* Zikula Symfony Translator
	* Testing
* Important notes
* See also sources


## Introduction  
Technology for translations used in this guide is simple - anywere in project we use English strings and English descriptions.
These are translated to other language stored in files or database and loaded on demand instead of English strings. 

## Terminology
* **Symfony Translator** - Symfony gettext technology used for translations 
* **Zikula legacy translator** - Zikula priror to 1.4.x uses own implementation of gettext translation technology. 
* **Zikula Symfony Translator** - Extends Symfony Translator to support Zikula translation conventions. 
* **message** -  in basic it is translation array element example: 'Englilsh string' => 'Translated string' 
* **translation template** - .pot file for gettext used only in process of creating new translations. Not used in actual translating. 
* **domain** - An optional way to organize messages into groups (e.g. Symfony admin, navigation or the default messages Zikula zikula, theme_themename, module_modulename and 2.0.0 bundlename)
* **catalogue** - Gettext way to organize messages into groups (LC_MESSAGES, LC_TYPE, LC_ALL) 
* **locale** - The locale that the translations are for (e.g. en_GB, en, etc);
* **loader** - How Symfony should load and parse the file (e.g. xlf, php, yml, etc Zikula .po .mo).


## User's guide
Used technologies try to symplify translation process as possible. Installing language for Zikula is as simple as copying translation catalogue to aprioriate directory and enabling it in administration.
You can find your locale translations on https://github.com/zikula-communities If there is no translation for your language and you willing to translate please check 'Translators' part of this guide. 
### Zikula legacy translator

Catalogue is always ``` LC_MESSAGES ```

**Path to install translations on systems versions prior to 1.4.x**  

``` Core translations /locale/catalogue/domain.loader ```

``` Theme translations /themes/themename/locale/catalogue/domain.loader  where domain is 'theme_themename' ```

``` Module translations /modules/modulename/locale/catalogue/domain.loader where domain is 'module_themename'```

** <a name="paths_14"></a> Path to install translations on systems versions 1.4.x UPGRADE**

``` Core app/Resources/locale/catalogue/domain.loader ```

``` Theme from 1.3.x translations /themes/themename/locale/catalogue/domain.loader  where domain is 'theme_themename' ```

``` Module from 1.3.x translations /modules/modulename/locale/catalogue/domain.loader where domain is 'module_themename' ```

``` Theme - bundle - translations /themes/themename/locale/catalogue/domain.loader  where domain is 'themebundlename' ```

``` Module - bundle - translations /modules/modulename/locale/catalogue/domain.loader where domain is 'modulebundlename' ```

> To translate a module in Zikula 1.4.x, the file name must be in the format `modulename.mo`. To translate a module in Zikula 1.3.x, the file name was previously in the format `module_modulename.mo`. These files must be placed into the legacy folder `app/Resources/locale/{lang}/LC_MESSAGES` (where `{lang}` is the standardized abbreviation for your language (e.g. `de` for german). In the future, the files must be placed into the new `app/Resources/translations` folder. In this case, copy all files to `<filename>.<lang>.po` (for example `routes.template.po => routes.de.po`) and translate the `.po` files as usual to generate the `.mo` files.


### Zikula Symfony Translator

**Zikula Symfony translator suports zikula paths for core and modules on systems versions 1.4.x - only bundle type!**

``` Core legacy app/Resources/locale/catalogue/domain.loader ```

**Paths and file names used by Symfony Translator - and standard for zikula 2.0.0 - core**

``` Core 2.0.0 app/Resources/translations/domain.locale.loader ```

**Paths and file names used by Symfony Translator - and standard for zikula 2.0.0 - bundles (modules themes etc.)**

``` Themes and modules bundle type .../Resources/translations/domain.locale.loader where domain is 'modulebundlename' or 'themebundlename' ```

## Translators

https://github.com/zikula/zikula-docs/blob/master/guides/translation/GuideForTranslators.rst 

## Developers guide


Developers should be aware of the gettext specyfication and symfony translator specyfication 
https://www.gnu.org/software/gettext/manual/gettext.html#I18n_002c-L10n_002c-and-Such

### Zikula legacy translator

Add migration examples

### Symfony Translator

Symfony comes with translation component Symfony Translations can be in various formats:
* ArrayLoader - to load catalogs from PHP arrays.
* CsvFileLoader - to load catalogs from CSV files.
* IcuDatFileLoader - to load catalogs from resource bundles.
* IcuResFileLoader - to load catalogs from resource bundles.
* IniFileLoader - to load catalogs from ini files.
* MoFileLoader - to load catalogs from gettext files.
* PhpFileLoader - to load catalogs from PHP files.
* PoFileLoader - to load catalogs from gettext files.
* QtFileLoader - to load catalogs from QT XML files.
* XliffFileLoader - to load catalogs from Xliff files.
* JsonFileLoader - to load catalogs from JSON files.
* YamlFileLoader - to load catalogs from Yaml files (requires the Yaml component). 

For more informations please refer to http://symfony.com/doc/current/book/translation.html 

### Zikula Symfony Translator

* Zikula SF Translator base on SF translator and extend it to fit our translation conventions. Translator is loaded as service and available like other services.
* Zikula translator automatically preload translations from both Symfony and Zikula translation directiories.

##### Translator service:

Translator service can be obtained from container.
Service is preconfigured to automatically detect current locale, domain is by default set to 'zikula'.
Exaple from AbstractController obtaining translator and setting new domain.

```
		//access translator service
		$this->translator = $bundle->getContainer()->get('translator');
		
		// set domain 
		$this->translator->setDomain($bundle->getTranslationDomain());
```

##### AbstractController

Zikula Translator is automatically added in AbstractContraller and you can access it in your module controller using:
 
```
$this->translator
```

Translation examples

```
		//Symfony nativ notation
        $translated = $this->translator->trans('Hello World');
        //Zikula translation method
        $translated = $this->translator->__('Page');
        ...
        //shortcut's
        $translated = $this->__('Page');
        ...
```

##### Twig

For translations in Twig Zikula uses CoreGettext extensions apart from nativ Symfony Twig trans function.
http://symfony.com/doc/current/book/translation.html#translations-in-templates
```
//Symfony nativ notation
{% trans from "zikula" %}Error! Cannot determine valid 'cid' for edit mode in 'ZikulaCategoriesModule_admin_edit'.{% endtrans %}
{% trans %}Error! Cannot determine valid 'cid' for edit mode in 'ZikulaCategoriesModule_admin_edit'.{% endtrans %}
//Zikula gettext notation
    {{ __('Done!') }}
    {{ __('Done!', 'zikula') }}
    {{ __f('Done! Saved the %s category.',{'%s':'test'}) }}
    {{ __f('Done! Saved the %s category.',{'%s':'test'}, 'zikula') }}
    {{ __f('Done! Saved the %s category.',{'%s':'test'}, 'zikula') }}
    {{ _fn('Done! Deleted %1$d user account.', 'Done! Deleted %1$d user accounts.', 1, {'%1$d' : 1}, 'zikula', 'pl')  }}
```

##### Context functions - core gettext twig

todo 
https://github.com/zikula/core/blob/1.4/src/lib/Zikula/Bundle/CoreBundle/Twig/Extension/GettextExtension.php#L61

##### Testing

Symfony comes with ``` app/console translation:debug ``` command line tool to test translations.
**This tool work only with Symfony and Zikula 2.0.0 translation paths.**
Example output for more informations please check http://symfony.com/doc/current/book/translation.html#debugging-translations

```
	php app/console translation:debug pl KaikmediaPagesModule
	+----------+-------------+----------------------+
	| State(s) | Id          | Message Preview (pl) |
	+----------+-------------+----------------------+
	| o        | Pages       | Strony               |
	| o        | Page        | Strona               |
	| o        | pages       | strony               |
	| o        | page        | strona               |
	| o        | read more   | czytaj więcej        |
	| o        | title       | tytuł                |
	| o        | description | opis                 |
	+----------+-------------+----------------------+
	
	Legend:
	 x Missing message
	 o Unused message
	 = Same as the fallback message
```


## Important notes
From Symfony translator documentation
>Each time you create a new translation resource (or install a bundle that includes a translation resource), be sure to clear your cache so that Symfony can discover the new translation resources.

There is something new in Symfony translator that was not available with legacy mode. Lets say we have a website with 3 languages en (strings are in en) de and pl. All languages are enabled but some translations are missing for some strings. Normally it would show english as default because strings are in english. Falback locale is a feature that reads translator 'fallback' setting from config.yml and set this as locale to show instead the one that is missing. So when viewing german site in polish language and polish translations are not complete while the german are it will show german string but that will happen only when translator fallback setting locale is set to de. At the end there is always english string.

## See also sources
* Gettext documentation https://www.gnu.org/software/gettext/manual/gettext.html#I18n_002c-L10n_002c-and-Such
* Symfony Translator documentation http://symfony.com/doc/current/book/translation.html 
* Zikula legacy translations guides https://github.com/zikula/zikula-docs/tree/master/guides/translation
