Javascript Translation
======================

Zikula includes `willdurand/js-translation-bundle` also known as "BazingaJsTranslationBundle"

refs: https://github.com/willdurand/BazingaJsTranslationBundle

Bazinga adds native symfony translation support like so:

    var myText = Translator.trans('Foo bar baz');
    var myChoiceText = Translator.transChoice('%count% foo|%count% foos', 5);

The methods are defined like so:

    Translator.trans(key, params, domain, locale);
    Translator.transChoice(key, count, params, domain, locale);

Additionally, Zikula adds standard zikula translation functions:

    var myText = Translator.__('Foo bar baz');
    var myStrReplaceText = Translator.__f('Free %stuff%', {stuff: 'beer'});
    var myPluralText = Translator._n('%count% apple', '%count% apples', count);
    var myPluralReplaceText = Translator._fn('%count% %desc% apple', '%count% %desc% apples', 5, {desc: 'fresh'});

The methods are defined like so:

    Translator.__(key, domain, locale);
    Translator.__f(key, params, domain, locale);
    Translator._n(singular, plural, count, domain, locale);
    Translator._fn(singular, plural, count, params, domain, locale);


Extraction from Javascript Files
--------------------------------

Zikula also provides an Extractor for both native Symfony and Zikula translation functions. This is run automatically
when translations are extracted. By default, **all javascript translations** are added to the `zikula_javascript` domain. 
**This catalog is automatically added to every page**.

If an extension _chooses_, it can set the domain manually in each method call, e.g.:

    var myText = Translator.__('Foo bar baz', 'my_special_domain');

In this case, the extractor will export these strings to its own translation file:

    /MyModule/Resources/translatiosn/my_special_domain.js

Then, your extension **must** manually include each of these files in the required template like so:

    {{ pageAddAsset('javascript', url('bazinga_jstranslation_js', {domain:my_special_domain}), constant('Zikula\\ThemeModule\\Engine\\AssetBag::WEIGHT_JS_TRANSLATIONS')) }}


Javascripts in Twig Templates
-----------------------------

Translation usage in scripts that are created *within* a twig template (not a standalone file) should utilize standard
Twig template translation and not utilize javascript translation.