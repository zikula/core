// Copyright 2012 Zikula Foundation, licensed LGPLv3 or any later version.
/**
 * @fileOverview Zikula Core helper
 * @requires jQuery, underscore, Modernizr, lang.js, class.js
 */

(function($) {
    /**
     * Creates namespace in Zikula scope through nested chain of objects,
     * based on the given path.
     * If object in chain already exists it will be extended, not overwritten
     *
     * @example
     * Zikula.define('Module.Component'); //will create object chain: Zikula.Module.Component
     *
     * @param {String}  pathName        Dot separated path to define.
     *
     * @return {Object} Zikula extended object
     */
    Zikula.define = function(pathName) {
        if (Zikula.Lang.objectIssetPath(Zikula, pathName)) {
            return Zikula.Lang.objectGetPath(Zikula, pathName);
        }
        return Zikula.Lang.objectSetPath(Zikula, pathName, {});
    };

    /**
     * Namespace for Zikula Util classes
     *
     * @namespace
     * @name Zikula.Util
     */
    Zikula.define('Util');

    /**
     * Namespace for Zikula Services
     *
     * @name Zikula.Services
     * @namespace Zikula Plugins namespace
     * todo - move this to service manager
     */
    Zikula.define('Services');

    /**
     * Utility for managing polyfills.
     *
     * @namespace Zikula.Util.Polyfills Utility for managing polyfills.
     */
    Zikula.Util.Polyfills = (function(){
        /**
         * Internal storage for registered polyfills
         *
         * @type {Object}
         * @private
         */
        var polyfills = {};

        /**
         * Internal getter for polyfills.
         *
         * @private
         * @param {String}  name    Polufill name
         * @return {Object} Single polyfill object
         */
        function getPolyfill(name) {
            if (!_(polyfills).has(name)) {
                polyfills[name] = {
                    name: name,
                    deferred: new $.Deferred(),
                    loaded: false
                };
            }
            return polyfills[name];
        }

        /**
         * Add new Modernizr test for polyfill.
         * Result is accessed inside Modernizr as polyfill name suffixed with '-polyfill' (eg 'json-polyfill');
         *
         * @private
         * @param {String}              name    Polyfill name
         * @param {Function|Boolean}   [test]   Optional test for Modernizr (by default it's set to true)
         */
        function addTest(name, test) {
            test = _(test).isFunction() ? test : true;
            Modernizr.addTest(name+'-polyfill', test);
        }

        /**
         * Register new polyfill.
         *
         * Loading task is forwarded to yepnope and:
         * - document ready event is hold till polyfill will be resolved
         * - polyfill is registered into Modernizr under '$name-polyfill'
         * - observable deferred object is created for callbacks
         *
         * @example
         *  Zikula.Util.Polyfills.add('json', {
         *      test: Modernizr.json,
         *      nope: 'javascript/polyfills/json2/json2.js'
         *  });
         *
         * @name Zikula.Util.Polyfills.add
         * @function
         *
         * @param {String[]}    names       Polyfill name (or array of names)
         * @param {Object}      resource    Object for yepnope loader
         * @param {Function}   [test]       Optional test for Modernizr to register polyfill
         *
         * @return {Zikula.Util.Polyfills}
         */
        function add(names, resource, test) {
            names = _(names).isArray() ? names : [names];

            var resolver =  new $.Deferred(),
                complete = _(resource).objectGetPath('complete', $.noop);

            resolver.always(function() {
                complete();
                _(names).each(function(name) {
                    addTest(name, test);
                });
                $.holdReady(false);
            });
            resource.complete = resolver.resolve;

            $.holdReady(true);
            yepnope(resource);

            _(names).each(function(name) {
                var polyfill = getPolyfill(name);
                polyfill.loaded = true;
                resolver.done(polyfill.deferred.resolve);
            });

            return methods;
        }

        /**
         * Allows to bind callbacks to polyfill, which will be called when polyfill will be loaded
         * (or it is already loaded).
         *
         * Exports jQuery Deferred promise for requested polyfill, exposing Deferred methods (such as 'then' or 'done').
         * Due to the yepnope nature - returned promise will be always resolved (so using 'fail' method is pointless).
         * <a href="http://api.jquery.com/deferred.promise/">Read more about jQuery Deferred</a>
         *
         * @example
         *  Zikula.Util.Polyfills.when('json').then(doSomething); // doSomething will be executed after 'json' polyfill
         *                                                           will be loaded (or immediately if it's already loaded)
         *
         * @name Zikula.Util.Polyfills.when
         * @function
         *
         * @param {String[]} names Polyfill name or array of names
         * @return {jQuery.Deferred}
         */
        function when(names) {
            names = _(names).isArray() ? names : [names];
            var callbacks = _(names).map(function(name) {
                return getPolyfill(name).deferred;
            });

            return $.when.apply($, callbacks);
        }

        // Export public methods
        var methods = {
            add: add,
            when: when
        };
        return methods;
    })();

    // Load polyfills required by core
    Zikula.Util.Polyfills.add('json', {
        test: Modernizr.json,
        nope: 'javascript/polyfills/json2/json2.js'
    }).add(['localstorage', 'sessionstorage'], {
        test: Modernizr.localstorage && Modernizr.sessionstorage,
        nope: 'javascript/polyfills/storage/storage.js'
    });

})(jQuery);
