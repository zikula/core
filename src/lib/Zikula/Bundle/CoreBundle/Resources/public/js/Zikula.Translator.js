// Copyright Zikula Foundation, licensed MIT.

// This function extends vendor/willdurand/js-translation-bundle/Resources/js/translator.js
// it MUST be loaded afterwards
(function($) {
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
        Translator._n = function (singular, plural, number, domain, locale) {
            domain = typeof domain !== 'undefined' ? domain : Translator.defaultDomain;
            locale = typeof locale !== 'undefined' ? locale : Translator.locale;

            return Translator.transChoice(singular+'|'+plural, number, {count: number}, domain, locale);
        };
        Translator._fn = function (singular, plural, number, params, domain, locale) {
            domain = typeof domain !== 'undefined' ? domain : Translator.defaultDomain;
            locale = typeof locale !== 'undefined' ? locale : Translator.locale;
            params.count = number;

            return Translator.transChoice(singular+'|'+plural, number, params, domain, locale);
        };
    });
})(jQuery);
