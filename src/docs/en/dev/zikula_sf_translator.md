Zikula SF Translator
=================

##Overview 

Symfony comes with translation component http://symfony.com/doc/current/book/translation.html Symfony Translations can be in various formats:
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

...add more informations about symfony translator basic usage

Zikula SF Translator base on SF translator and extend it to fit our translation conventions.
## Usage:

### AbstractController
```
        $translated = $this->translator->trans('Hello World');
        $translated = $this->translator->__('Page');
```
### Twig
http://symfony.com/doc/current/book/translation.html#translations-in-templates
```
{% trans from "zikula" %}Error! Cannot determine valid 'cid' for edit mode in 'ZikulaCategoriesModule_admin_edit'.{% endtrans %}
{% trans %}Error! Cannot determine valid 'cid' for edit mode in 'ZikulaCategoriesModule_admin_edit'.{% endtrans %}
    {{ __('Done!') }}
    {{ __('Done!', 'zikula') }}
    {{ __f('Done! Saved the %s category.',{'%s':'test'}) }}
    {{ __f('Done! Saved the %s category.',{'%s':'test'}, 'zikula') }}
    {{ __f('Done! Saved the %s category.',{'%s':'test'}, 'zikula') }}
    {{ _fn('Done! Deleted %1$d user account.', 'Done! Deleted %1$d user accounts.', 1, {'%1$d' : 1}, 'zikula', 'pl')  }}
```
#### Context functions - core gettext twig
https://github.com/zikula/core/blob/1.4/src/lib/Zikula/Bundle/CoreBundle/Twig/Extension/GettextExtension.php#L61 I have idea what they might be but that is not tested so I will think about it later...

## Important 
From Symfony translator documentation
>Each time you create a new translation resource (or install a bundle that includes a translation resource), be sure to clear your cache so that Symfony can discover the new translation resources

## Fallback locale
There is something new in Symfony translator that was not available with legacy mode. Lets say we have a website with 3 languages en (strings are in en) de and pl. All languages are enabled but some translations are missing for some strings. Normally it would show english as default because strings are in english. Falback locale is a feature that reads translator 'fallback' setting from config.yml and set this as locale to show instead the one that is missing. So when viewing german site in polish language and polish translations are not complete while the german are it will show german string but that will happen only when translator fallback setting locale is set to de. At the end there is always english string.
