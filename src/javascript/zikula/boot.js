// Copyright 2012 Zikula Foundation, licensed LGPLv3 or any later version.
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
     * This is internal service - unless necessary use shortcuts exposed in Zikula namespace
     *
     * @see Zikula.Util.Gettext
     * @type {Zikula.Util.Gettext}
     */
    Zikula.Services.gettext = new Zikula.Util.Gettext(Zikula.Config.lang, Zikula._translations);

    // Export shortcuts to Zikula global object.
    Zikula.Class.extend(Zikula, {
        __: Zikula.Services.gettext.__,
        __f: Zikula.Services.gettext.__f,
        _n: Zikula.Services.gettext._n,
        _fn: Zikula.Services.gettext._fn
    });

    // make sure json support is assured before using cookie util
    Zikula.Util.Polyfills.when('json').then(function(){
        /**
         * Cookie service.
         * Initialized with default settings Zikula.Util.Cookie class.
         * See Zikula.Util.Cookie for reference.
         *
         * @see Zikula.Util.Cookie
         * @type {Zikula.Util.Cookie}
         */
        Zikula.Services.cookie = new Zikula.Util.Cookie({
            path: Zikula.Config.baseURI
        });

        // Set ajax options
        Zikula.Ajax.defaultOptions = {
            type: 'POST',
            timeout: Zikula.Config.ajaxtimeout || 5000,
            converters: {
                "text json": Zikula.Ajax.Response.convertResponseText
            }
        };
        if (Zikula.Config.sessionName) {
            var sessionId = Zikula.Services.cookie.get(Zikula.Config.sessionName, false);
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


    // Setup dom related stuff
    $(document).ready(function() {
        // Fix buttons for IE 7 and older
        if ($.browser.msie && Number($.browser.version) < 8) {
            $('form').zFixButtons();
        }
    });

})(jQuery);
