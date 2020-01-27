# JavaScript translation

Zikula includes `willdurand/js-translation-bundle` also known as [BazingaJsTranslationBundle](https://github.com/willdurand/BazingaJsTranslationBundle).

Bazinga adds native symfony translation support like so:

```js
var myText = Translator.trans('Foo bar baz');
var myChoiceText = Translator.transChoice('%count% foo|%count% foos', 5);
```

The methods are defined like so:

```js
Translator.trans(key, params, domain, locale);
Translator.transChoice(key, count, params, domain, locale);
```

See [BazingaJsTranslation docs](https://github.com/willdurand/BazingaJsTranslationBundle/blob/master/Resources/doc/index.md#the-js-translator) for further details and examples.

## Extraction from JavaScript files

Zikula also provides an Extractor for both native Symfony and Zikula translation functions. This is run automatically
when translations are extracted. By default, **all javascript translations** are added to the `zikula_javascript` domain. 
**This catalog is automatically added to every page**.

If an extension _chooses_, it can set the domain manually in each method call, e.g.:

```js
var myText = Translator.__('Foo bar baz', 'my_special_domain');
```

In this case, the extractor will export these strings to its own translation file:

    /MyModule/Resources/translations/my_special_domain.js

Then, your extension **must** manually include each of these files in the required template like so:

```twig
{{ pageAddAsset('javascript', url('bazinga_jstranslation_js', {domain: 'my_special_domain'}), constant('Zikula\\ThemeModule\\Engine\\AssetBag::WEIGHT_JS_TRANSLATIONS')) }}
```

## JavaScripts in Twig templates

Translation usage in scripts that are created *within* a twig template (not a standalone file) should utilize standard
Twig template translation and not utilize javascript translation.
