// Copyright Zikula Foundation, licensed MIT.

/**
 * @fileOverview Zikula Core helper
 * @requires jQuery, underscore, Modernizr, lang.js, class.js
 */

(function($) {
    /**
     * Namespace for Zikula Util classes
     *
     * @namespace
     * @name Zikula.Util
     */
    Zikula.define('Util');

    Zikula.Core = new Zikula.Util.Services('zikula.core');

    // Load polyfills required by core
    Zikula.Core.loadPolyfill('json', {
        test: Modernizr.json,
        nope: 'javascript/polyfills/json2/json2.js'
    }).loadPolyfill(['localstorage', 'sessionstorage'], {
        test: Modernizr.localstorage && Modernizr.sessionstorage,
        nope: 'javascript/polyfills/storage/storage.js'
    });

    var viewId = _(Zikula.Config).objectGetPath('request.view-id', 'homepage'),
        query = _(Zikula.Config).objectGetPath('request.query', {}),
        homepage = _(Zikula.Config).objectGetPath('request.homepage', false);
    // attaches event named "$module-$type-$func"
    Zikula.Core.attachEvent(viewId, function(event) {
        $(document).ready(function() {
            event.resolve(query);
        });
    });
    Zikula.Core.attachEvent('homepage', function(event) {
        $(document).ready(function() {
            if (homepage) {
                event.resolve(query);
            } else {
                event.reject(query);
            }
        });
    });

})(jQuery);
