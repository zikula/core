// Copyright 2012 Zikula Foundation, licensed LGPLv3 or any later version.
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

    var request = _(Zikula.Config).objectGetPath('request', {});
        viewID = [
        _(request).objectGetPath('module', 'home'),
        _(request).objectGetPath('type', ''),
        _(request).objectGetPath('func', '')
    ].join('-').toLowerCase();
    // attaches event named "$module-$type-$func"
    Zikula.Core.attachEvent(viewID, function(event) {
        $(document).ready(function(){
            event.resolve(request);
        });
    });


})(jQuery);
