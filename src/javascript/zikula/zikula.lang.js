// Copyright Zikula Foundation, licensed MIT.

/**
 * @fileOverview Zikula language extensions.
 * @requires underscore
 */
(function() {
    /**
     * Namespace for Zikula language extensions.
     * All methods are exposed (and documented) as _ (underscore) methods.
     * Zikula utilize base underscore and underscore.string. Visit linked pages to get documentation.
     *
     * @exports Zikula.Lang as _
     * @class
     * @see <a href="http://underscorejs.org/">http://underscorejs.org/</a>
     * @see <a href="https://github.com/epeli/underscore.string">https://github.com/epeli/underscore.string</a>

     */
    Zikula.Lang = {};

    /**
     * Underscore setup - export Underscore.string to base Underscore
     */
    _.mixin(_.str.exports());

    // Rename and add string functions, overwritten by core underscore methods, to allow chaining.
    _.mixin(/** @lends _ */{
        /**
         * Alias for 'include' method from underscore.string, overwritten by underscore.
         *
         * @function
         * @see <a href="https://github.com/epeli/underscore.string#problems">https://github.com/epeli/underscore.string#problems</a>
         */
        stringInclude: _.str.include,
        /**
         * Alias for 'reverse' method from underscore.string, overwritten by underscore.
         *
         * @function
         * @see <a href="https://github.com/epeli/underscore.string#problems">https://github.com/epeli/underscore.string#problems</a>
         */
        stringReverse: _.str.reverse
    });

    /**
     * Checks if string is valid JSON.
     *
     * @example
     * Zikula.Lang.isJSON(string); // base syntax
     * _(string).isJSON(); // underscore syntax (recommended)
     *
     * _('foo').isJSON(); // returns false
     * _("[1, 2]").isJSON(); // returns true
     *
     * @param {String} string String to test
     * @return {Boolean}
     * @link http://api.prototypejs.org/language/String/prototype/isJSON/
     */
    Zikula.Lang.isJSON = function(string) {
        if (!string) {
            return false;
        }
        string = string.replace(/\\(?:["\\\/bfnrt]|u[0-9a-fA-F]{4})/g, '@');
        string = string.replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g, ']');
        string = string.replace(/(?:^|:|,)(?:\s*\[)+/g, '');
        return (/^[\],:{}\s]*$/).test(string);
    };

    /**
     * Encode json data to url safe format.
     *
     * @example
     *  Zikula.Lang.urlSafeJsonEncode(data, json); // base syntax
     * _(data).urlSafeJsonEncode(json); // underscore syntax (recommended)
     *
     * _({foo: 'bar'}).urlSafeJsonEncode(true); // returns "%7B%22foo%22%3A%22bar%22%7D"
     *
     * @param {*}        data       Data to encode
     * @param {Boolean} [json=true] Should data be also encode to json
     *
     * @return {String} Encoded data
     */
    Zikula.Lang.urlSafeJsonEncode = function(data, json) {
        json = _(json).isUndefined() ? true : json;
        if (json) {
            data = JSON.stringify(data);
        }
        data = data.replace(/\+/g, '%20');
        return encodeURIComponent(data);
    };

    /**
     * Decode json data from url safe format.
     *
     * @example
     *  Zikula.Lang.urlSafeJsonDecode(data, json); // base syntax
     * _(data).urlSafeJsonDecode(json); // underscore syntax (recommended)
     *
     * _("%7B%22foo%22%3A%22bar%22%7D").urlSafeJsonDecode(true); // returns {foo:"bar"})
     *
     * @param {String}   data       Data to encode
     * @param {Boolean} [json=true] Should data be also decode from json
     *
     * @return {*} Decoded data
     */
    Zikula.Lang.urlSafeJsonDecode = function(data, json) {
        json = _(json).isUndefined() ? true : json;
        data = data.replace(/\+/g, '%20');
        data = decodeURIComponent(data);
        if (json) {
            data = JSON.parse(data);
        }
        return data;
    };


    /**
     * Merge two objects recursively.
     *
     * Copies all properties from source to destination object and returns new object.
     * If property exists in destination it is extended not overwritten
     *
     * @example
     *  Zikula.Lang.extendRecursive(destination, source); // base syntax
     * _(destination).extendRecursive(source); // underscore syntax (recommended)
     *
     * var a = {x: true, z: {za: true}},
     *     b = {y: false, z: {zb: false}},
     *     c;
     * c =  _(a).extendRecursive(b); // c is now {x:true, z:{za:true, zb:false}, y:false}
     *
     * @param {Object} destination Destination object
     * @param {Object} source      Source object
     *
     * @return {Object} Extended object
     */
    Zikula.Lang.extendRecursive = function(destination, source) {
        destination = destination || {};
        for (var prop in source) {
            if (source.hasOwnProperty(prop)) {
                try {
                    if (source[prop].constructor === Object) {
                        destination[prop] = Zikula.Lang.extendRecursive(destination[prop], source[prop]);
                    } else {
                        destination[prop] = source[prop];
                    }
                } catch (e) {
                    destination[prop] = source[prop];
                }
            }
        }
        return destination;
    };

    /**
     * Returns nested property value.
     * Allows to easy get at once nested path without worrying about TypeError on undefined property in path chain.
     *
     * @example
     *  Zikula.Lang.objectGetPath(object, pathName, defaultValue); // base syntax
     * _(object).objectGetPath(pathName, defaultValue); // underscore syntax (recommended)
     *
     * _(Zikula).objectGetPath(pathName, 'Config.entrypoint'); // returns 'index.php'
     * _(Zikula).objectGetPath(pathName, ['Config', 'entrypoint']); // returns 'index.php'
     * _(Zikula).objectGetPath(pathName, 'this.path.does.not.exists'); // returns undefined
     * _(Zikula).objectGetPath(pathName, 'this.path.does.not.exists', 'default'); // returns 'default'
     *
     * @param {Object}          object          Object to search in
     * @param {String|Array}    pathName        Dot separated path (or array of parts), relative to object
     * @param {*}              [defaultValue]   Default value to return when result is undefined
     *
     * @return {*} Property value, default value or undefined.
     */
    Zikula.Lang.objectGetPath = function(object, pathName, defaultValue) {
        var prop,
            path = _(pathName).isArray() ? _(pathName).clone() : pathName.split('.'),
            last = path.pop();

        while ((prop = path.shift())) {
            object = object[prop];
            if (!_(object).isObject()) {
                return defaultValue;
            }
        }
        return _(object[last]).isUndefined() ? defaultValue : object[last];
    };

    /**
     * Sets nested property value. If properties in path chain does not exists - they are crated.
     *
     * @example
     *  Zikula.Lang.objectSetPath(object, pathName, value); // base syntax
     * _(object).objectSetPath(pathName, value); // underscore syntax (recommended)
     *
     * _(Zikula).objectSetPath('some.long.path.name', true); // {Zikula:{long:{path:{name:true}}}}
     *
     * @param {Object}          object      Base object
     * @param {String|Array}    pathName    Dot separated path (or array of parts), relative to object
     * @param {*}               value       Value to set
     *
     * @return {*} Last property in path chain (the one that has set the value)
     */
    Zikula.Lang.objectSetPath = function(object, pathName, value) {
        var prop,
            path = _(pathName).isArray() ? _(pathName).clone() : pathName.split('.'),
            last = path.pop();

        while ((prop = path.shift())) {
            object[prop] = object[prop] || {};
            object = object[prop];
        }
        return object[last] = value;
    };

    /**
     * Checks if object has property.
     * Allows to easy check at once nested path without worrying about TypeError on undefined property in path chain.
     *
     * @example
     *  Zikula.Lang.objectIssetPath(object, pathName); // base syntax
     * _(object).objectIssetPath(pathName); // underscore syntax (recommended)
     *
     * _(Zikula).objectIssetPath('Util.Cookie'); // returns true
     * _(Zikula).objectIssetPath('Util.Foo'); // returns false
     *
     * @param {Object}          object      Base object
     * @param {String|Array}    pathName    Dot separated path (or array of parts), relative to object
     *
     * @return {Boolean}
     */
    Zikula.Lang.objectIssetPath = function(object, pathName) {
        return !_(Zikula.Lang.objectGetPath(object, pathName)).isUndefined();
    };

    /**
     * Deletes nested property.
     *
     * @example
     *  Zikula.Lang.objectUnsetPath(object, pathName); // base syntax
     * _(object).objectUnsetPath(pathName); // underscore syntax (recommended)
     *
     * _(Zikula).objectUnsetPath('Config.foo'); // returns true
     * _(Zikula).objectUnsetPath('this.one.does.not.exists'); // returns true
     *
     * @param {Object}          object      Base object
     * @param {String|Array}    pathName    Dot separated path (or array of parts), relative to object
     *
     * @return {Boolean} True on success or if given property did not exist
     */
    Zikula.Lang.objectUnsetPath = function(object, pathName) {
        if (Zikula.Lang.objectIssetPath(object, pathName)) {
            var prop,
                path = _(pathName).isArray() ? _(pathName).clone() : pathName.split('.'),
                last = path.pop();

            while ((prop = path.shift())) {
                object = object[prop];
            }
            return (delete object[last]);
        } else {
            return true;
        }
    };

    // Export Zikula.Lang methods to underscore
    _.mixin(Zikula.Lang);

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
})();
