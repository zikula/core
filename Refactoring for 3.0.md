# Refactoring for 3.0

## Modules

### Interfaces

In general, interfaces and apis implement argument type-hinting in all methods. This can break an implementation of said
interfaces, etc. Extensions must update their implementation of any core/system interface to adhere to the new signature.

### Service registration

Please use `autowire` and `autoconfigure` as this will magically solve most issues.
refs: https://symfony.com/doc/current/service_container/3.3-di-changes.html#step-1-adding-defaults

Module services should be registered by their classname (automatically as above) and not with old-fashioned
`service.class.dot.notation`.

### Blocks

BlockHandler classes must implement `Zikula\BlocksModule\BlockHandlerInterface` as in Core-2.0 but there is no longer
a need to tag these classes in your services file as they are auto-tagged. Also - as above, the classname should be
used as the service name.

## Translations
All custom Zikula translation mechanisms have been removed in favour of Symfony's native translation system.

### PHP files
Some examples for how to convert translations in PHP files:

```php
// import
use Zikula\Common\Translator\TranslatorInterface;       // old
use Symfony\Contracts\Translation\TranslatorInterface;  // new

// 1. Simple:
$this->__('Hello')      // old
$this->trans('Hello')   // new

// 2. With simple substitution parameters
$this->__f('Hello %userName%', ['%userName%' => 'Mark Smith'])      // old
$this->trans('Hello %userName%', ['%userName%' => 'Mark Smith'])    // new

// 3. With explicit domain
$this->__('Hello', 'acmefoomodule')             // old
$this->trans('Hello', [], 'acmefoomodule')      // new

// 4. With plural forms and advanced substitution (see note below)
$this->_fn('User deleted!', '%n users deleted!', count($deletedUsers), ['%n' => count($deletedUsers)]);
$this->getTranslator()->trans('plural_n.users.deleted'/* User deleted!|n users deleted!*/, ['%count%' => count($deletedUsers)]);
```

You can still use `Zikula\Common\Translator\TranslatorTrait`, but it has only one method left now:
```php
public function trans(string $id, array $parameters = [], string $domain = null, string $locale = null): string
```

### Twig template files
Some examples for how to convert translations in templates:

```twig
1. Simple:
Old: {{ __('Hello') }}
New: {% trans %}Hello{% endtrans %} or {{ 'Hello'|trans }}

2. With simple substitution parameters
Old: {{ __f('Hello %userName%', {'%userName%': 'Mark Smith'}) }}
New: {% trans with {'%userName%': 'Mark Smith'} %}Hello %userName%{% endtrans %}

3. With explicit domain and locale
Old: {{ __('Hello', 'acmefoomodule', 'fr') }}
New: {% trans with {} from 'acmefoomodule' into 'fr' %}Hello{% endtrans %} or {{ 'Hello'|trans({}, 'acmefoomodule', 'fr' }}

4. With plural forms and advanced substitution (see note below)
Old: {% set amountOfMembers = _fn('%amount% registered user', '%amount% registered users', users|length, {'%amount%': users|length}) %}
New: {% trans count users|length %}plural_n.registered.user{# one registered user|n registered users #}{% endtrans %}
```

See [Symfony docs](https://symfony.com/doc/current/translation/templates.html) for further details and examples of simple translation.

### About plural forms
The `plural_n` portion of the translation key is simply a convention established to note that this key requires plural translation.
The comments `{# ... #}` are examples of what the translation should appear like in English. Unfortunately, we don't know how to communicate
this comment in the translation file at this time.

The translation of this would look something like:
```yaml
#messages+intl-icu.en.yaml
plural_n.registered.user: "{count, plural,\n  one   {one registered user}\n  other {# registered users}\n}"
```

More advanced translation like plurals and other substitutions require using the Symfony ICU MessageFormatter. See [How to Translate Messages using the ICU MessageFormat](https://symfony.com/doc/current/translation/message_format.html). This requires a specific name format on the translation file and other adjustments.

### JavaScript files

**TBD**

See [BazingaJsTranslation docs](https://github.com/willdurand/BazingaJsTranslationBundle/blob/master/Resources/doc/index.md#the-js-translator) for further details and examples.

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
