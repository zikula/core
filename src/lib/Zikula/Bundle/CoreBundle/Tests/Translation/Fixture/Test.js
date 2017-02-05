// Copyright Zikula Foundation, licensed MIT.

( function($) {
    $(document).ready(function() {
        alert(Translator.__("Hi there!") + '\n'
            + Translator.__("Hi there! (again)") + '\n'
            + Translator.__('Hi there! "Foo"') + '\n'
            + Translator.__f('My name is %n%', {"n": 'foo'}) + '\n'
            + 'count:' + '\n'
            + Translator._n('%count% apple', '%count% apples', 1) + '\n'
            + Translator._n('%count% more apple', "%count% more apples", 2) + '\n'
            + Translator._fn('%count% %desc% apple', '%count% %desc% apples', 5, {desc: 'ugly'}) + '\n'
            + Translator.defaultDomain + '\n'
            + Translator.locale
        );
        alert(Translator.trans("Original Translator!"));
        var someText = Translator.transChoice("someText|someTexts", 5);
    });
})(jQuery);
