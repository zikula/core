Zikula Translation
==================

Zikula translation *is* Symfony translation, with some added bonus features. Please refer to 
http://symfony.com/doc/current/translation.html for more information about Symfony features.

**Paths and file names used by the Symfony Translator**

    `app/Resources/translations/<domain>.<locale>.<loader>`
    
    e.g.
    
    `app/Resources/translations/zikula.en.po`


Note: BOTH are loaded and the legacy overwrites the symfony/Core-2.0 type values if the keys are the same in the same
domain because it is loaded second.
Using a *partial* overwrite is possible if you only define the keys you wish to overwrite.


**Paths and file names used by Symfony Translator - bundles (modules, themes etc.)**

    `.../Resources/translations/<domain>.<locale>.<loader>` where domain is `modulebundlename` or `themebundlename`


### Symfony Translator Loaders

Symfony comes with standard file format loaders:
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

## Important notes
From Symfony translator documentation
> Each time you create a new translation resource (or install a bundle that includes a translation resource), be sure to
clear your cache so that Symfony can discover the new translation resources.

Translations are cached in `app/cache/<env>/translations/catalogue.<locale>.<key>`


### Fallback locale
There is something new in Symfony translator that was not available with legacy mode. A website with 3 languages:
en (strings are in en), de and pl. All languages are enabled but some translations are missing for some strings.
Normally it would show english as default because strings are in english. Fallback locale is a feature that reads
translator 'fallback' setting from config.yml and sets this as locale to show instead the one that is missing. So when
viewing German site in Polish language and Polish translations are not complete while the German is, it will show German
translations instead. This will happen only when translator fallback setting locale is set to `de`. At the end there is
always english string.

## See also sources
 * Gettext documentation https://www.gnu.org/software/gettext/manual/gettext.html#I18n_002c-L10n_002c-and-Such
 * Symfony Translator documentation http://symfony.com/doc/current/translation.html
 * Zikula legacy translations guides https://github.com/zikula/zikula-docs/tree/master/guides/translation
 * https://www.icanlocalize.com/site/tutorials/how-to-translate-with-gettext-po-and-pot-files/
