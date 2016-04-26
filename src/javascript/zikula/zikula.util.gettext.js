// Copyright Zikula Foundation, licensed MIT.

/**
 * @fileOverview Javascript Gettext implementation for Zikula.
 * @requires underscore, core.js, class.js
 */
(function() {
    Zikula.define('Util');

    Zikula.Util.Gettext = Zikula.Class.create(/** @lends Zikula.Util.Gettext.prototype */{
        /**
         * Regexp used for validating plural forms
         *
         * @private
         * @type RegExp
         */
        pluralsPattern: /^(nplurals=\d+;\s{0,}plural=[\s\d\w\(\)\?:%><=!&\|]+)\s{0,};\s{0,}$/i,
        /**
         * Null char used as delimiter for plural forms
         *
         * @private
         * @type String
         */
        nullChar: '\u0000',
        /**
         * Javascript Gettext implementation for Zikula.
         *
         * Base class for javascript gettext implementation. It runs internal and
         * exports utility methods to global Zikula object.
         * This are {@link Zikula.__}, {@link Zikula.__f}, {@link Zikula._n} and {@link Zikula._fn}.<br />
         * Usage is quite the same as PHP gettext
         *
         * @example
         * Zikula.__('hello','module_foo');
         * Zikula.__f('hello %s',['A'],'module_foo');
         * Zikula._n('hello my friend','hello my friends',2,'module_foo');
         * Zikula._fn('hello my friend %s','hello my friends %s',2,['A','B'],'module_foo')
         *
         * @class Zikula.Util.Gettext
         * @constructs
         *
         * @param {String} [lang] Language for translations
         * @param {Object} [data] Data with translations
         *
         * @return {Zikula.Util.Gettext} New Zikula.Util.Gettext instance
         */
        init: function(lang, data) {
            this.defaults = Zikula.Util.Gettext.options;

            this.data = {};
            this.setup(lang, data);

            this.__ = _(this.getMessage).bind(this);
            this.__f = _(this.getMessageFormatted).bind(this);
            this._n = _(this.getPluralMessage).bind(this);
            this._fn = _(this.getPluralMessageFormatted).bind(this);

            return this;
        },
        /**
         * Allows to re-init already initialized gettext instance
         *
         * @param {String}  lang    Language for translations
         * @param {Object}  data    Data with translations
         * @param {String} [domain] Default domain to use, optional
         *
         * @return void
         */
        setup: function(lang, data, domain) {
            this.setLang(lang);
            this.setDomain(domain);
            this.addTranslations(data || {});
        },
        /**
         * Adds translations to gettext instance
         *
         * @param {Object} obj Data with translations
         *
         * @return void
         */
        addTranslations: function(obj) {
            _(this.data).extendRecursive(obj);
        },
        /**
         * Setup current gettext language
         *
         * @param {String} lang   Language for translations
         *
         * @return void
         */
        setLang: function(lang) {
            this.lang = lang || this.defaults.lang;
        },
        /**
         * Setup current gettext default domain
         *
         * @param {String} domain Default domain to use, optional
         *
         * @return void
         */
        setDomain: function(domain) {
            this.domain = domain || this.defaults.domain;
        },
        /**
         * Reads from translations data
         *
         * @private
         * @param {String} domain The domain in which given key will be searched
         * @param {String} key    Data key to search
         *
         * @return {*} Given data key value or empty object
         */
        getData: function(domain, key) {
            domain = domain || this.domain;
            return _(this.data).objectGetPath([this.lang, domain, key].join('.'), {});
        },
        /**
         * Gettext: translates message.
         *
         * @example
         * Zikula.__('hello','module_foo');
         *
         * @param {String}  message The message to translate
         * @param {String} [domain] Gettext domain, if no domain is given default one is used
         *
         * @return {String} Translated message
         */
        getMessage: function(message, domain) {
            return this.getData(domain, 'translations')[message] || message;
        },
        /**
         * Gettext: translates and format message using sprintf formatting rules.
         *
         * @example
         * Zikula.__f('hello %s',['A'],'module_foo');
         *
         * @param {String}  message The message to translate
         * @param {Number}  params  Array with zero or more replacements to be made in message
         * @param {String} [domain] Gettext domain, if no domain is given deafult one is used
         *
         * @return {String} Translated message
         */
        getMessageFormatted: function(message, params, domain) {
            return _.vsprintf(this.getMessage(message, domain), params);
        },
        /**
         * Gettext: plural translation.
         *
         * @example
         * Zikula._n('hello my friend','hello my friends',2,'module_foo');
         *
         * @param {String}  singular  Singular message
         * @param {String}  plural    Plural message
         * @param {Number}  count     Count
         * @param {String} [domain]   Gettext domain, if no domain is given default one is used
         *
         * @return {String} Translated message
         */
        getPluralMessage: function(singular, plural, count, domain) {
            var offset = this.getPluralOffset(count, domain),
                key = singular + this.nullChar + plural,
                messages = this.getMessage(key, domain);
            if (messages) {
                return messages.split(this.nullChar)[offset];
            } else {
                return key.split(this.nullChar)[offset];
            }
        },
        /**
         * Gettext: plural formatted translation.
         *
         * @example
         * Zikula._fn('hello my friend %s','hello my friends %s',2,['A','B'],'module_foo')
         *
         * @param {String}  singular Singular message
         * @param {String}  plural   Plural message
         * @param {Number}  count    Count
         * @param {Array}   params   Array with zero or more replacements to be made in singular/plural message
         * @param {String} [domain]  Gettext domain, if no domain is given default one is used
         *
         * @return {String} Translated message
         */
        getPluralMessageFormatted: function(singular, plural, count, params, domain) {
            return _.vsprintf(this.getPluralMessage(singular, plural, count, domain), params);
        },
        /**
         * Calculates plural offset depending on plural forms
         *
         * @private
         * @param {Number} count  Count
         * @param {String} domain The domain to be used, if no domain is given default one is used
         *
         * @return {Number} Plural offset
         */
        getPluralOffset: function(count, domain) {
            var eq = null,
                nplurals = 0,
                plural = 0,
                n = count || 0;
            try {
                eq = this.getData(domain, 'plural-forms').match(this.pluralsPattern)[1];
                eval(eq);
            } catch (e) {
                eq = this.defaults.pluralForms;
                eval(eq);
            }
            if (plural >= nplurals) {
                plural = nplurals - 1;
            }
            return plural;
        }
    });

    Zikula.Class.extend(Zikula.Util.Gettext, /** @lends Zikula.Util.Gettext */{
        /**
         * Default options for {@link Zikula.Util.Gettext}.
         * See <a href="#constructor">Zikula.Util.Gettext constructor</a> for details.
         *
         * @static
         */
        options: {
            lang: 'en',
            domain: 'zikula_js',
            pluralForms: 'nplurals=2; plural=n == 1 ? 0 : 1;'
        }
    });
})();
