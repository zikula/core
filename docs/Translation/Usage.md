---
currentMenu: translation
---
# Translator usage

## PHP files

### Translator service

The translator service can be injected using `Symfony\Contracts\Translation\TranslatorInterface`.
Service is pre-configured to automatically detect current locale, domain is by default set to `'messages'`.

Some simple examples for how to use it in PHP files:

```php
// 1. Simple:
$this->translator->trans('Hello')

// 2. With simple substitution parameters
$this->translator->trans('Hello %userName%', ['%userName%' => 'Mark Smith'])

// 3. With explicit domain
$this->translator->trans('Hello', [], 'acmefoobundle')
```

You can also use `Zikula\Bundle\CoreBundle\Translation\TranslatorTrait`, which allows to use `$this->trans()` instead:

```php
public function trans(string $id, array $parameters = [], string $domain = null, string $locale = null): string
```

You can/should remove the `setTranslator` method from your class which uses the trait. It is not needed anymore.

### Automatic translations

You should remove all translation calls from the following elements:

- Strings inside form type classes:
  - Form field labels
  - Choice labels
  - Placeholders
  - String values for `empty_value` attribute
  - Invalid messages
  - Single help messages
  - Input group addons
  - Alert messages
  - Title attributes
- Flash messages (`$this->addFlash()` as well as `getFlashBag()->add()`); except when substitution parameters are used.
- Knp menu entries:
  - Labels (`$menu->addChild('foo')` as well as `$menu['foo']->setLabel('bar')`)
  - Link titles (`setLinkAttribute('title', 'my.title')`)

They will be picked up by the extractor nevertheless.

More information about how translation of form messages work can be found [here](https://symfony.com/blog/new-in-symfony-4-3-improved-form-translation).

For a help array with multiple strings you need to use annotations guiding the extractor (see next section).

### Using the extractor

To extract translations and influence the extractor's behaviour please read [this doc](Extraction.md).

## Twig template files

For translations in Twig Zikula uses native Symfony Twig trans functionality ([docs](https://symfony.com/doc/current/translation/templates.html)).

```twig
{% trans %}Error! That cannot be done.{% endtrans %}
```

### Convert translations in Twig from earlier versions

Some examples for how to use translations in templates:

```twig
1. Simple:
{% trans %}Hello{% endtrans %} or {{ 'Hello'|trans }}

2. With simple substitution parameters
{% trans with {'%userName%': 'Mark Smith'} %}Hello %userName%{% endtrans %}

3. With explicit domain and locale
{% trans with {} from 'acmefoobundle' into 'fr' %}Hello{% endtrans %} or {{ 'Hello'|trans({}, 'acmefoobundle', 'fr' }}
```

See [Symfony docs](https://symfony.com/doc/current/translation/templates.html) for further details and examples of simple translation.

There is also a `desc` filter for specifying a default translation for a key. You can find an example for this in [this doc](Extraction.md).
