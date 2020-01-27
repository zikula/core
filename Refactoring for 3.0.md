# Refactoring for 3.0

## Modules

### Composer file

Add the following capability for defining the (default) admin icon:

```json
    ...
    "extra": {
        "zikula": {
            ...
            "capabilities": {
                ...
                "admin": {
                    ...
                    "icon": "fas fa-star"
                },
                ...
            },
        },
    },

```

You can remove the old `admin.png` file afterwards.

### Interfaces

In general, interfaces and apis implement argument type-hinting in all methods. This can break an implementation of said
interfaces, etc. Extensions must update their implementation of any core/system interface to adhere to the new signature.

### Service registration

Please use `autowire` and `autoconfigure` as this will magically solve most issues.
Further information can be found [in Symfony docs](https://symfony.com/doc/current/service_container/3.3-di-changes.html#step-1-adding-defaults).

Module services should be registered by their classname (automatically as above) and not with old-fashioned
`service.class.dot.notation`.

### Blocks

BlockHandler classes must implement `Zikula\BlocksModule\BlockHandlerInterface` as in Core-2.0 but there is no longer
a need to tag these classes in your services file as they are auto-tagged. Also - as above, the classname should be
used as the service name.

### Extension menus

`Zikula\Core\LinkContainer\LinkContainerCollector` and `Zikula\Core\LinkContainer\LinkContainerInterface` have been
removed. Extension menus are not implemented using Knp Menu instead. See docs and system modules for further
information and examples.

## Translations

All custom Zikula translation mechanisms have been removed in favour of Symfony's native translation system.

For more information please refer to the documents in `docs/Translation/`.

## Twig

### Classes

Use namespaced classes because the non-namespaced classes have been removed.

For example:

| Old | New |
| --- | --- |
| `\Twig_Extension` | `Twig\Extension\AbstractExtension` |
| `\Twig_SimpleFunction` | `Twig\TwigFunction` |
| `\Twig_SimpleFilter` | `Twig\TwigFilter` |
| `\Twig_SimpleTest` | `Twig\TwigTest` |

and so on…

### Template paths

- change all template names from e.g. `Bundle:Controller:Action.html.twig` to `@Bundle/Controller/Action.html.twig`
- Modules and themes retain the `Module` or `Theme` suffix but bundles do not.

### Templates

| Topic | Old | New | Further information |
| ---- | --- | --- | ------- |
| Filtering loops | `{% for item in items if item.active %}` | `{% for item in items\|filter(i => i.active) %}` | [blog post](https://symfony.com/blog/twig-adds-filter-map-and-reduce-features) with more examples |
| Filtering loops (alternative) | `{% for item in items if item.active %}` | `{% for item in items %}{% if item.active %}` | |
| apply tag | `{% filter upper %}…{% endfilter %}` | `{% apply upper %}…{% endapply %}` | [blog post](https://symfony.com/blog/twig-adds-filter-map-and-reduce-features#the-apply-tag) |
| spaceless filter | `{% spaceless %}…{% endspaceless %}` | `{% apply spaceless %}…{% endapply %}` | [blog post](https://symfony.com/blog/better-white-space-control-in-twig-templates#added-a-spaceless-filter) |
| Old array extension | `shuffle` filter | no equivalent |
| Old date extension | `time_diff` filter | no equivalent |
| Old i18n extension | `trans` filter | use the `trans` filter from Symfony | [trans](https://symfony.com/doc/current/reference/twig_reference.html#trans) reference |
| Old intl extension | `localizeddate` | use `format_date`, `format_datetime`, `format_time` | [format_date](https://twig.symfony.com/doc/3.x/filters/format_date.html) reference, [format_datetime](https://twig.symfony.com/format_datetime) reference, [format_time](https://twig.symfony.com/format_time) reference |
| Old intl extension | `{{ myNumber\|localizednumber }}` | `{{ myNumber\|format_number }}` | [format_number](https://twig.symfony.com/doc/3.x/filters/format_number.html) reference |
| Old intl extension | `{{ myAmount\|localizedcurrency('EUR') }}` | `{{ myAmount\|format_currency('EUR') }}` | [format_currency](https://twig.symfony.com/doc/3.x/filters/format_currency.html) reference |
| Old text extension | `{{ title\|truncate(200, true, '…') }}` | `{{ title\|u.truncate(200, '…') }}` | [u filter](https://twig.symfony.com/doc/3.x/filters/u.html) reference |
| Old text extension | `wordwrap` | `u` | [u filter](https://twig.symfony.com/doc/3.x/filters/u.html) reference |
| Country name | unavailable | `{{ myCountry\|country_name }}` | [country_name](https://twig.symfony.com/country_name) reference |
| Currency name | unavailable | `{{ myCurrency\|currency_name }}` | [currency_name](https://twig.symfony.com/currency_name) reference |
| Currency symbol | unavailable | `{{ 'EUR'\|currency_symbol }}` | [currency_symbol](https://twig.symfony.com/currency_symbol) reference |
| Language name | `{{ myLanguage\|languagename }}` | `{{ myLanguage\|language_name }}` | [language_name](https://twig.symfony.com/language_name) reference |
| Locale name | unavailable | `{{ myLocale\|locale_name }}` | [locale_name](https://twig.symfony.com/locale_name) reference |
| Timezone name | unavailable | `{{ myTimezone\|timezone_name }}` | [timezone_name](https://twig.symfony.com/timezone_name) reference |
