// Copyright Zikula Foundation, licensed MIT.

/**
 * @fileOverview Cookie util
 * @requires underscore, core.js, class.js
 */
(function() {
    Zikula.define('Util');

    Zikula.Util.Cookie = Zikula.Class.create(/** @lends Zikula.Util.Cookie.prototype */{
        /**
         * Base util class for handling cookies.<br />
         * For standard usage use {@link Zikula.cookie} - already initialized instance
         * of {@link Zikula.Util.Cookie}
         *
         * @class Zikula.Util.Cookie
         * @constructs
         *
         * @param {Object}  [options]              Config object
         * @param {String}  [options.path='/']     Default path for cookies, if not set Zikula.Config.baseURI will be used
         * @param {String}  [options.domain='']    Domain for cookies, if not set current domain will be used
         * @param {Boolean} [options.secure=false] Should cookies be secured (transmitted over secure protocol as https)
         * @param {Boolean} [options.json=true]    Should cookies values be encoded to and decoded from json
         *
         * @return {Zikula.Util.Cookie} New Zikula.Util.Cookie instance
         */
        init: function(options) {
            this.options = _.extend(Zikula.Util.Cookie.options, options || { });

            return this;
        },
        /**
         * Create or update cookie.
         *
         * @param {String}       name     Cookie name.
         * @param {*}            value    Cookie value.
         * @param {Number|Date} [expires] Expiration date (Date object) or time in seconds, default is session.
         * @param {String}      [path]    Path for cookie, by default Zikula baseURI is set.
         *
         * @return {Boolean} Returns true on success, false otherwise
         */
        set: function(name, value, expires, path) {
            try {
                value = this.options.json ? this.encode(value) : value;
                var cookieStr = name + '=' + value,
                    cookieArgs = {
                        expires: expires instanceof Date ? expires.toGMTString() : this.secondsFromNow(expires),
                        path: path ? path : this.options.path,
                        domain: this.options.domain,
                        secure: this.options.secure ? 'secure' : ''
                    };
                _(cookieArgs).each(function(value, key, context) {
                    if (value) {
                        cookieStr += ';' + key + '=' + value;
                    }
                });

                document.cookie = cookieStr;
            } catch (e) {
                return false;
            }

            return true;
        },
        /**
         * Get cookie value.
         * Cookie value is returned in original format as it was stored.
         *
         * @param {String}  name Cookie name.
         * @param {Boolean} json Cookie name.
         *
         * @return {*} Returns cookie value or null.
         */
        get: function(name, json) {
            json = _(json).isUndefined() ? this.options.json : json;
            var cookie = new RegExp(name + '=(.*?)(;|$)').exec(document.cookie);

            return cookie ? (json ? this.decode(cookie[1]) : cookie[1]) : null;
        },
        /**
         * Delete cookie
         *
         * @param {String} name Cookie name.
         *
         * @return {Boolean} Returns true on success, false otherwise
         */
        remove: function(name) {
            return this.set(name, '', -1);
        },
        /**
         * Calculates date equal now plus given number of seconds
         *
         * @private
         * @param {Number} seconds Number of seconds
         *
         * @return {String} Date as GMT string
         */
        secondsFromNow: function(seconds) {
            if (!seconds) {
                return null;
            }
            var d = new Date();
            d.setTime(d.getTime() + (seconds * 1000));

            return d.toGMTString();
        },
        /**
         * Encode given value to format safe to store in cookies.
         * Due to PHPIDS original JSON format is encoded using encodeURI
         *
         * @private
         * @param {*} value Value to encode
         *
         * @return {String} Encoded value
         */
        encode: function(value) {
            return encodeURI(encodeURI(JSON.stringify(value)));
        },
        /**
         * Decode given string to original format
         *
         * @private
         * @param {String} value String to decode
         *
         * @return {*} Decoded value
         */
        decode: function(value) {
            return JSON.parse(decodeURI(decodeURI(value)));
        }
    });

    Zikula.Class.extend(Zikula.Util.Cookie, /** @lends Zikula.Util.Cookie */{
        /**
         * Default options for {@link Zikula.Util.Cookie}.
         * See <a href="#constructor">Zikula.Util.Cookie constructor</a> for details.
         *
         * @static
         */
        options: {
            path: '/',
            domain: '',
            secure: false,
            json: true
        }
    });

})();