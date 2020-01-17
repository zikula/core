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
You can/should remove the `setTranslator` method from your class which uses the trait. It is not needed anymore.

#### Automatic translations
You should remove all translation calls from the following elements:

- Strings inside form type classes:
  - Form field labels
  - Choice labels
  - Placeholders
  - String values for `empty_value` attribute
  - Invalid messages
  - Single help messages
- Flash messages (`$this->addFlash()` as well as `getFlashBag()->add()`); except when substitution parameters are used.

They will be picked up by the extractor nevertheless.

More information about how translation of form messages work can be found [here](https://symfony.com/blog/new-in-symfony-4-3-improved-form-translation).

#### Using the extractor

To extract translations use the console command `translation:extract`. To see all of it's option, do this:
```
php bin/console translation:extract -h
# or
php bin/console translation:extract --help
```

Example for Zikula core:
```
# extract for all configured locales
php bin/console translation:extract zikula
# extract only for English
php bin/console translation:extract zikula en
```

Note `zikula` is the name of our configuration.

Examples for a module or a theme:
```
php bin/console translation:extract -b AcmeFooModule extension
php bin/console translation:extract --bundle AcmeFooModule extension en
php bin/console translation:extract -b AcmeFooModule acmefoomodule
php bin/console translation:extract --bundle AcmeFooModule acmefoomodule en

# or with more memory:
php -dmemory_limit=2G bin/console translation:extract --bundle AcmeFooModule extension
php -dmemory_limit=2G bin/console translation:extract --bundle AcmeFooModule acmefoomodule en
```

You can always check the status of your translation using the `translation:status` command.
Check the available options using `-h` or `--help` like shown above.

#### Translation annotations

To influence the extraction behaviour you can utilise some annotations from the `Translation\Extractor\Annotation` namespace.
Import them like any other php class:
```php
use Translation\Extractor\Annotation\Desc;
use Translation\Extractor\Annotation\Ignore;
use Translation\Extractor\Annotation\Translate;
```

##### `@Desc`
The `@Desc` annotation allows specifying a default translation for a key.

Examples:

```php
$builder->add('title', 'text', [
    /** @Desc("Title:") */
    'label' => 'post.form.title',
]);

/** @Desc("We have changed the permalink because the post '%slug%' already exists.") */
$errors[] = $this->translator->trans(
    'post.form.permalink.error.exists', ['%slug%' => $slug]
);
```

##### `@Ignore`
The `@Ignore` annotation allows ignoring extracting translation subjects which are not a string, but a variable.
You can use it for example for `trans()` calls, form labels and form choices.

Examples:

**TODO this needs further tests, see [this issue](https://github.com/php-translation/extractor/issues/146)**
```php
echo $this->translator->trans(/** @Ignore */$description);

$builder->add('modulecategory' . $module['name'], ChoiceType::class, [
    /** @Ignore */
    'label' => $module['displayname'],
    'empty_data' => null,
    'choices' => /** @Ignore */$options['categories']
]);
```

##### `@Translate`
With the `/** @Translate */` you can explicitly add phrases to the dictionary. This helps to extract strings
which would have been skipped otherwise.

Examples:

```php
$placeholder = /** @Translate */'delivery.user.not_chosen';
```

It can be also used to force specific domain:

```php
$errorMessage = /** @Translate(domain="validators") */'error.user_email.not_unique';
```

### JavaScript files
Follows basically the same rules as translations in PHP files shown above. See [BazingaJsTranslation docs](https://github.com/willdurand/BazingaJsTranslationBundle/blob/master/Resources/doc/index.md#the-js-translator) for further details and examples.

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
```

See [Symfony docs](https://symfony.com/doc/current/translation/templates.html) for further details and examples of simple translation.

There is also a `desc` filter for specifying a default translation for a key (same as the `@Desc` annotation shown above). Use it like this:

```twig
{{ 'post.form.title'|trans|desc('Title:') }}
{{ 'welcome.message'|trans({'%userName%': 'John Smith'})|desc('Welcome %userName%!') }}
```

### Translation domains
Earlier we used the bundle name as translation domain. The new translation system uses different configurations for different bundles though. You are encouraged to use multiple translation domains now. They should cover different semantical topics and act as a context for translators, like for example `mail`, `messages`, `navigation`, `validators` and `admin`).

### About plural forms
Here is an example using plural forms, advanced substitution and the `desc` filter:
```twig
Old: {% set amountOfUsers = _fn('%amount% registered user', '%amount% registered users', users|length, {'%amount%': users|length}) %}
New: {% set amountOfUsers = 'plural_n.registered.user'|trans({count: users|length})|desc('{count, plural,\n  one   {one registered user}\n  other {# registered users}\n}') %}
```

The `plural_n` portion of the translation key is simply a convention established to note that this key requires plural translation.

The translation of this would look something like:
```yaml
#messages+intl-icu.en.yaml
plural_n.registered.user: "{count, plural,\n  one   {one registered user}\n  other {# registered users}\n}"
```

More advanced translation like plurals and other substitutions require using the Symfony ICU MessageFormatter. See [How to Translate Messages using the ICU MessageFormat](https://symfony.com/doc/current/translation/message_format.html). This requires a specific name format on the translation file and other adjustments.

### UI-based translations
Zikula 3 introduces two new abilities for creating and changing translations.

Both can be accessed in the Settings module at the localisation settings if the environment is set to `dev`.

**Edit in place functionality**
Allows to edit translations directly in the context of a page ([demo](https://php-translation.readthedocs.io/en/latest/_images/edit-in-place-demo.gif)).

Edit in place has some limitations you should be aware of:

- It always works for the current locale only; so in order to update translation for multiple languages you need to switch your site's language.
- It can only work with one single configuration. By default this is set to `zikula`, so it works for the core. If you want to use it for a module or a theme, you need to lookup the corresponding configuration name (e.g. `zikulabootstraptheme`) in `/app/config/dynamic/generated.yml` and use this in `/app/config/packages/dev/php_translation.yaml` at `translation.edit_in_place.config_name`.

You can utilise HTML formatting options when your translation keys end with the `.html` suffix ([screenshot](https://php-translation.readthedocs.io/en/latest/_images/demo-html-editor.png)).

**Web UI: provides a web interface to add, edit and remove translations.**

It features a dashboard page ([screenshot](https://php-translation.readthedocs.io/en/latest/_images/webui-dashboard.png)) for the overall progress. When you dive into a translation domain you can use a form to change the translation messages ([screenshot](https://php-translation.readthedocs.io/en/latest/_images/webui-page.png)).

The web UI is able to handle multiple configurations and target languages.

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
