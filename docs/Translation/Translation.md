---
currentMenu: translation
---
# Translation in Zikula

Zikula uses the native Symfony translation system. Please refer to [Symfony docs](https://symfony.com/doc/current/translation.html) for more information about Symfony features.

## Important notes

From Symfony translator documentation:

> Each time you create a new translation resource (or install a bundle that includes a translation resource), be sure to
clear your cache so that Symfony can discover the new translation resources.

Translations are cached in `var/cache/<env>/translations/catalogue.<locale>.<key>`

### Fallback locale

Let's look at a website with 3 languages: `en` (strings are in `en`), `de` and `pl`.
All languages are enabled but some translations are missing for some strings.

Normally it would show English as default because strings are in English.
The fallback locale is a feature that reads translator 'fallback' setting from config.yaml
and sets this as locale to show instead the one that is missing.
So when viewing a German site in Polish language and Polish translations are not complete
while the German is, it will show German translations instead. This will happen only
when translator fallback setting locale is set to `de`. At the end there is always an English string.

## Further resources

- [Gettext documentation](https://www.gnu.org/software/gettext/manual/gettext.html#I18n_002c-L10n_002c-and-Such)
- [Symfony Translator documentation](https://symfony.com/doc/current/translation.html)
- [How to Translate With GetText PO and POT Files](https://www.icanlocalize.com/site/tutorials/how-to-translate-with-gettext-po-and-pot-files/)
