// Copyright Zikula Foundation, licensed MIT.

// This function extends vendor/willdurand/js-translation-bundle/Bazinga/Bundle/JsTranslationBundle/Resources/js/translator.js
// it MUST be loaded afterwards
( function($) {
    $(document).ready(function() {
        Translator.__ = function (key, domain, locale) {
            domain = typeof domain !== 'undefined' ? domain : Translator.defaultDomain;
            locale = typeof locale !== 'undefined' ? locale : Translator.locale;
            return Translator.trans(key, {}, domain, locale);
        };
        Translator.__f = function (key, params, domain, locale) {
            domain = typeof domain !== 'undefined' ? domain : Translator.defaultDomain;
            locale = typeof locale !== 'undefined' ? locale : Translator.locale;
            return Translator.trans(key, params, domain, locale);
        };
        Translator._n = function (singular, plural, count, domain, locale) {
            domain = typeof domain !== 'undefined' ? domain : Translator.defaultDomain;
            locale = typeof locale !== 'undefined' ? locale : Translator.locale;
            return Translator.transChoice(singular+'|'+plural, count, {count: count}, domain, locale);
        };
        Translator._fn = function (singular, plural, count, params, domain, locale) {
            domain = typeof domain !== 'undefined' ? domain : Translator.defaultDomain;
            locale = typeof locale !== 'undefined' ? locale : Translator.locale;
            params.count = count;
            return Translator.transChoice(singular+'|'+plural, count, params, domain, locale);
        };
    });
})(jQuery);
