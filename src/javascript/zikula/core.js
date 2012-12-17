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

    // todo - move to queue, hold dom ready till all dependiencies will be loaded
    Modernizr.load({
        test: window.JSON,
        nope: 'javascript/json2/json2.js'
    });

})(jQuery);
