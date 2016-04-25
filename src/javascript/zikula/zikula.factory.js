// Copyright Zikula Foundation, licensed MIT.

/**
 * @fileOverview jQuery factory plugins
 * @requires jQuery, core.js, class.js
 */
(function($) {
    /**
     * Create simple jQuery plugin.
     *
     * @example
     * var Zikula.foo = function() {};
     * $.zPluginFactory('zFoo', Zikula.foo);
     * $('selector').zFoo(); // simple call
     * $('selector').zFoo(1, 'bar', [1,2,3]); // call with arguments for plugin
     *
     * @param {String}   pluginName Name for the plugin
     * @param {Function} plugin     Plugin function
     *
     * @return {Function} The plugin
     */
    jQuery.zPluginFactory = function(pluginName, plugin) {
        if (!plugin || !pluginName) {
            throw new Error('Invalid arguments');
        }
        $.fn[pluginName] = function() {
            var globalArgs = Array.prototype.slice.call(arguments);
            return this.map(function() { // use $.map not $.each, this let the plugin to control the return value
                var localArgs = $.extend(true, [], globalArgs);
                localArgs.unshift(this);
                return plugin.apply(this, localArgs);
            });
        };
        $.extend($.fn[pluginName], plugin); // copy plugin properties

        return $.fn[pluginName];
    };

    /**
     * Create stateful plugin (widget) with Zikula.Class.
     *
     * @example
     * var Zikula.Foo = Zikula.Class.create({});
     * $.zClassFactory('zFoo', Zikula.Foo);
     * $('selector').zFoo(); // calls class constructor
     * $('selector').zFoo('destroy'); // calls destroy method
     * $('selector').zFoo('foo', 1, 2); // calls foo method and passes some arguments
     *
     * @param  {String} klassName   Name for the class
     * @param  {Object} klass       Class object
     *
     * @return {Function} The plugin
     * todo - try to extend widget with class statics
     */
    jQuery.zClassFactory = function(klassName, klass) {
        if (!klass || !klassName) {
            throw new Error('Invalid arguments');
        }
        // define selector for plugin; use as $(':klassName')
        $.expr[":"][klassName] = function(elem) {
            return !!$.data(elem, klassName);
        };
        // factory function
        $.fn[klassName] =  function() {
            var globalArgs = Array.prototype.slice.call(arguments);
            return this.map(function() { // use $.map not $.each, this let the class to control the return value
                var localArgs = $.extend(true, [], globalArgs), // make a deep copy of globalArgs
                    result = this, // set default return value to keep chainability
                    instance = $(this).data(klassName); // try to get already instanced class
                if (instance instanceof klass) { // if instance exists - call its method
                    // get first arg - it should be method name - then call it and pass the rest of args
                    var method = localArgs.shift(),
                        localResult;
                    if (klass.hasMethod(method)) {
                        localResult = instance[method].apply(instance, localArgs);
                        // here we make assumption that destroy method return value should evaluate to true on success
                        // if so - remove stored class data
                        if (method === 'destroy' && result) {
                            $(this).removeData(klassName);
                        }
                    } else if (klass.hasMethod('__call')) {
                        // allow klass to handle calls to no existing methods
                        // to do so klass have to have '__call' method
                        localResult = instance['__call'].apply(instance, [method, localArgs]);
                    }
                    // if method returned value other then itself - return this value instead of default
                    if (typeof localResult !== 'undefined' && localResult !== instance) {
                        result = localResult;
                    }
                } else { // instance does not exist - call class constructor
                    // add $this as the first arg and send it to class constructor
                    if (!klass.hasMethod(localArgs[0])) { // skip calls to methods of uninstanced class
                        localArgs.unshift($(this));
                        $(this).data(klassName, Zikula.Class.construct(klass, localArgs));
                    }
                }
                return result;
            });
        };

        return $.fn[klassName];
    };

})(jQuery);