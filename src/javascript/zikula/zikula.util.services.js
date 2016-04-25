// Copyright Zikula Foundation, licensed MIT.

(function($){
    Zikula.define('Util');

    Zikula.Util.Services = Zikula.Class.create(/** @lends Zikula.Util.Services.prototype */{
        /**
         * Service and event manager utility.<br />
         * For standard usage use {@link Zikula.Core} - already initialized instance
         * of {@link Zikula.Util.Services}
         * todo - extend docs, examples
         *
         * @class Zikula.Util.Services
         * @constructs
         *
         * @param {String}  [namespace='zikula']    Namespace for fired events
         *
         * @return {Zikula.Util.Services} New Zikula.Util.Services instance
         */
        init: function(namespace) {
            this.namespace = namespace || 'Zikula';
            this.services = {};
            this.events = {};

            return this;
        },
        /**
         * Gets existing event object or creates new one.
         *
         * @private
         * @param {String} name Event name
         *
         * @return {Object}
         */
        getEvent: function(name) {
            if (!_(this.events).has(name)) {
                this.events[name] = {
                    name: name,
                    namespaced: _('.').join(name, this.namespace),
                    deferred: new $.Deferred(),
                    loaded: false
                };
            }
            return this.events[name];
        },
        /**
         * Lists registered events names.
         *
         * @return {String[]}
         */
        listEvents: function() {
            return _(this.events).chain().where({loaded: true}).pluck('name').value();
        },
        /**
         * Gets existing service definition or creates new one.
         *
         * @private
         * @param {string} name Service name
         *
         * @return {Object}
         */
        getServiceDefinition: function(name) {
            if (!_(this.services).has(name)) {
                this.services[name] = {
                    name: name,
                    deferred: new $.Deferred(),
                    service: null
                };
            }
            return this.services[name];
        },
        /**
         * Gets registered service.
         *
         * @param {String} name Service name
         *
         * @return {*}
         */
        getService: function(name) {
            return this.getServiceDefinition(name).service;
        },
        /**
         * Lists registered services names.
         *
         * @return {String[]}
         */
        listServices: function() {
            return _(this.services).pluck('name');
        },
        /**
         * Allows to attach new event.
         * todo - example
         *
         * @param {String}      name    Event name
         * @param {Function}    handler Function, which will resolve (notify) event
         *
         * @return {Zikula.Util.Services}
         */
        attachEvent: function(name, handler) {
            var resolver =  new $.Deferred(),
                event = this.getEvent(name);
            event.loaded = true;
            resolver
                .then(event.deferred.resolve, event.deferred.reject)
                .always(_(function(){
                    $(this).trigger(event.namespaced, arguments);
                }).bind(this));

            handler(resolver);

            return this;
        },
        /**
         * Allows to attach new service.
         * todo - example
         *
         * @param {String}  name        Service name
         * @param {*}       definition  Service definition (function, object, klass)
         *
         * @return {Zikula.Util.Services}
         */
        attachService: function(name, definition) {
            var service = this.getServiceDefinition(name),
                eventHandler = function(event) {
                    event.resolve(name, definition);
                };
            service.service = definition;
            this.attachEvent(name, eventHandler);

            return this;
        },
        /**
         * Allows to load and attach new service.
         * Uses yepnope as script/resource loader.
         * todo - example
         *
         * @param {String}      name        Service name
         * @param {Object}      resource    Object for yepnope loader
         * @param {Function}    constructor Function, which will initialize service and return it's definition
         *
         * @return {Zikula.Util.Services}
         */
        loadService: function(name, resource, constructor) {
            var complete = _(resource).objectGetPath('complete', $.noop);
            resource.complete = _(function() {
                complete(); // original complete
                this.attachService(name, constructor())
            }).bind(this);
            yepnope(resource);

            return this;
        },
        /**
         * Loads polyfill
         * Loading task is forwarded to yepnope and:
         * - document ready event is hold till polyfill will be resolved
         * - polyfill is registered into Modernizr under '$name-polyfill'
         * - observable deferred object is created for callbacks
         * todo - example
         *
         * @param {String[]}    names       Polyfill name (or array of names)
         * @param {Object}      resource    Object for yepnope loader
         * @param {Function}   [test]       Optional test for Modernizr to register polyfill
         *
         * @return {Zikula.Util.Services}
         */
        loadPolyfill: function(names, resource, test) {
            names = _(names).isArray() ? names : [names];
            test = _(test).isFunction() ? test : true;

            var resolver =  new $.Deferred(),
                complete = _(resource).objectGetPath('complete', $.noop),
                eventHandler = function(event) {
                    resolver.then(event.resolve, event.reject);
                };

            _(names).each(_(function(name) {
                this.attachEvent(name, eventHandler);
            }).bind(this));

            resource.complete = function() {
                complete(); // original complete
                _(names).each(function(name) {
                    Modernizr.addTest(name+'-polyfill', test);
                });
                resolver.resolve();
                $.holdReady(false);
            };
            $.holdReady(true);
            yepnope(resource);

            return this;
        },
        /**
         * Allows to bind callbacks, which will be called when given events will be resolved (triggered).
         *
         * Exports jQuery Deferred promise for requested polyfill, exposing Deferred methods (such as 'then' or 'done').
         * <a href="http://api.jquery.com/deferred.promise/">Read more about jQuery Deferred</a>
         * todo - examples
         *
         * @param {String[]} names Event names
         *
         * @return {jQuery.Deferred}
         */
        when: function(names) {
            names = _(names).isArray() ? names : [names];
            var callbacks = _(names).map(_(function(name) {
                return this.getEvent(name).deferred;
            }).bind(this));

            return $.when.apply($, callbacks);
        }
    });
})(jQuery);
