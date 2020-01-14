# Translator usage

## Translator service

The translator service can be obtained from container.
Service is pre-configured to automatically detect current locale, domain is by default set to `'messages'`.
Example from AbstractController obtaining translator and setting new domain.

```php
//access translator service
$translator = $bundle->getContainer()->get('translator');

// set domain 
$translator->setDomain($bundle->getTranslationDomain());
```

## AbstractController

Zikula Translator is automatically added in AbstractController and you can access it in your module controller using:

```php
$this->translator
```

Translation examples

```php
//Symfony native notation
$translated = $this->translator->trans('Hello World');
//Zikula translation method
$translated = $this->translator->__('Page');
...
//shortcut methods also available for native zikula methods e.g.:
$translated = $this->__('Page');
```

## Twig

For translations in Twig Zikula uses native Symfony Twig trans functionality ([docs](https://symfony.com/doc/current/translation/templates.html)).

```twig
{% trans %}Error! That cannot be done'.{% endtrans %}
```
