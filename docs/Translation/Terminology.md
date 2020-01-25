# Translation terminology

The technology for translations used in this guide is simple - anywhere in project we use English strings and English descriptions.
These are translated to other language stored in files or database and loaded on demand instead of English strings. 

## Terminology

- **Symfony Translator** - Symfony gettext technology used for translations 
- **message** -  in basic it is an translation array element; for example: 'English string' => 'Translated string'
- **domain** - An optional way to organize messages into groups (e.g. `admin`, `navigation`, `validators`; default value is `messages`)
- **catalogue** - Gettext way to organize messages into groups (LC_MESSAGES, LC_TYPE, LC_ALL) 
- **locale** - The locale that the translations are for (e.g. `en_GB`, `en`, etc);
- **loader** - How Symfony/Zikula should load and parse the file (e.g. `xlf`, `php`, `yml`, `po`, `mo`, etc.). 

For more information please refer to https://symfony.com/doc/current/translation.html

## Translation domains
Earlier we used the bundle name as translation domain. The new translation system uses different configurations for different bundles though. You are encouraged to use multiple translation domains now. They should cover different semantical topics and act as a context for translators, like for example `mail`, `messages`, `navigation`, `validators` and `admin`).
