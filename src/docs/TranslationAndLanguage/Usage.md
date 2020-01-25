# Translator usage

## PHP files
### Translator service
The translator service can be obtained from container using `translator` or better be injected.
Service is pre-configured to automatically detect current locale, domain is by default set to `'messages'`.

### AbstractController
Zikula Translator is automatically added in AbstractController and you can access it in your module controller using:

```php
$this->translator
```

Translation example

```php
$translated = $this->translator->trans('Hello World');
```

When using `\Zikula\Bundle\CoreBundle\Translation\TranslatorTrait` also a shortcut method becomes available:

```php
$translated = $this->trans('Page');
```

### Convert from earlier versions
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
```

You can still use `Zikula\Bundle\CoreBundle\Translation\TranslatorTrait`, but it has only one method left now:
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

### Convert from earlier versions
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

There is also a `desc` filter for specifying a default translation for a key. You can find an example for this in [this doc](Extraction.md).
