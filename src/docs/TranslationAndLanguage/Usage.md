# Translator usage

## Translator service

The translator service can be obtained from container.
Service is pre-configured to automatically detect current locale, domain is by default set to `'messages'`.

## AbstractController

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

## Twig

For translations in Twig Zikula uses native Symfony Twig trans functionality ([docs](https://symfony.com/doc/current/translation/templates.html)).

```twig
{% trans %}Error! That cannot be done'.{% endtrans %}
```
