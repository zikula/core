// Copyright Zikula Foundation, licensed MIT.

/**
 * @fileOverview Bootstrap for Zikula javascript
 * @requires jQuery, underscore, zikula.js, lang.js, core.js, class.js, factory.js, util.cookie.js, util.gettext.js, dom.js, ajax.js
 */
/**
 * Global Zikula config object.
 * Zikula.Config is defined inline in HTML HEAD and is always avaiable.<br >
 * Contains following properties:<br >
 * - entrypoint<br >
 * - baseURL<br >
 * - baseURI<br >
 * - ajaxtimeout<br >
 * - lang
 *
 * @name Zikula.Config
 */

/**
 * jQuery object (dummy doc stub for extensions)
 * @name jQuery
 * @class
 * @see <a href="http://api.jquery.com/">http://api.jquery.com/</a>
 */

/**
 * jQuery object (dummy doc stub for extensions)
 * @name fn
 * @class
 * @memberOf jQuery
 * @see <a href="http://api.jquery.com/">http://api.jquery.com/</a>
 */
(function($) {
    /**
     * Gettext service.
     * This is internal service - use shortcuts exposed in Zikula namespace
     *
     * @see Zikula.Util.Gettext
     * @type {Zikula.Util.Gettext}
     */
    var gettext = new Zikula.Util.Gettext(Zikula.Config.lang, Zikula._translations);
    Zikula.Core.attachService('gettext', gettext);

    // Export shortcuts to Zikula global object.
    Zikula.Class.extend(Zikula, {
        __: gettext.__,
        __f: gettext.__f,
        _n: gettext._n,
        _fn: gettext._fn
    });

    // make sure json support is assured before using cookie util
    Zikula.Core.when('json').then(function(){
        /**
         * Cookie service.
         * Initialized with default settings Zikula.Util.Cookie class.
         * See Zikula.Util.Cookie for reference.
         *
         * @see Zikula.Util.Cookie
         * @type {Zikula.Util.Cookie}
         */
        var cookie = new Zikula.Util.Cookie({
            path: Zikula.Config.baseURI
        });
        Zikula.Core.attachService('cookie', cookie);

        // Set ajax options
        Zikula.Ajax.defaultOptions = {
            type: 'POST',
            timeout: Zikula.Config.ajaxtimeout || 5000,
            converters: {
                "text json": Zikula.Ajax.Response.convertResponseText
            }
        };
        if (Zikula.Config.sessionName) {
            var sessionId = Zikula.Core.getService('cookie').get(Zikula.Config.sessionName, false);
            if (sessionId) {
                Zikula.Ajax.defaultOptions.headers = {
                    'X-ZIKULA-AJAX-TOKEN': sessionId
                };
            }
        }
        $.ajaxSetup(Zikula.Ajax.defaultOptions);

        // Use prefilter to extend jQuery request and response object with Zikula.Ajax.Response
        $.ajaxPrefilter(function(options, originalOptions, jqXHR) {
            var response = new Zikula.Ajax.Response(options, jqXHR);
            $.extend(options, {
                zikula: response
            });
            $.extend(jqXHR, {
                zikula: response
            });
        });
    });
})(jQuery);
