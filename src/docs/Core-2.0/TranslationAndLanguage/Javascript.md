Javascript Translation
======================

Zikula includes `willdurand/js-translation-bundle` also known as "BazingaJsTranslationBundle"

refs: https://github.com/willdurand/BazingaJsTranslationBundle

Bazinga adds native symfony translation support like so:

    var myText = Translator.trans('Foo bar baz');
    var myChoiceText = Translator.transChoice('%count% foo|%count% foos', 5);

Additionally, Zikula adds standard zikula translation functions:

    var myText = Translator.__('Foo bar baz');
    var myStrReplaceText = Translator.__f('Free %stuff%', {stuff: 'beer'});
    var myPluralText = Translator._n('%count% apple', '%count% apples', count);
    var myPluralReplaceText = Translator._fn('%count% %desc% apple', '%count% %desc% apples', 5, {desc: 'fresh'});

Currently, all translations are added to the `zikula_javascript` domain and this catalog is added to every page.

Zikula also provides an Extractor for both native and Zikula translation functions. This is run automatically
when translations are extracted.
