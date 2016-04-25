// Copyright Zikula Foundation, licensed MIT.

/**
 * @fileOverview Class-based inheritance model for JavaScript
 * @requires core.js
 */
(function() {
    /**
     * Zikula.Class for managing class-based inheritance.<br />
     * It's based on Prototype's Class with some changes (the way of super method calls)
     * and the ability to call the constructor with a arguments list.
     *
     * @namespace Zikula.Class for managing class-based inheritance
     */
    Zikula.Class = (function() {
        var fnTest = /xyz/.test(function() {xyz;}) ? /\b_super\b/ : /.*/;

        /**
         * Creates a class and returns a constructor function for instances of the class.<br />
         * Calling returned constructor with "new" statement will create new class
         * instance and automatically invoke class's "init" method.<br />
         * Accepts two kind of params: <br />
         * - the first one - "superclass" - is optional and it can be other Zikula.Class to extend;
         *   if given - all its methods and properties are inherited by created class;
         *   one can access parent's methods calling "super" method (see examples)<br />
         * - other params are objects, which methods are copied to new class; if there's
         *   more then one object and method names are overlapping, later one take precedence.
         *
         * @example
         * // create base class
         * var Animal = Zikula.Class.create({
         *     init: function(name, sound) {
         *         this.name  = name;
         *         this.sound = sound;
         *     },
         *     speak: function() {
         *         alert(this.name + ' says: ' + this.sound + '!');
         *     }
         * });
         * // extend base class
         * var Snake = Zikula.Class.create(Animal, {
         *     init: function(name) {
         *         this._super('init', name, 'hissssssssss');
         *     }
         * });
         * // create instance
         * var ringneck = new Snake('Ringneck');
         * ringneck.speak(); // alerts "Ringneck says: hissssssss!"
         *
         * @name Zikula.Class.create
         * @static
         * @function
         *
         * @param {Zikula.Class} [superclass] Optional superclass to extend
         * @param {Object} methods     One or more objects with methods for new class
         *
         * @return {Zikula.Class} Zikula.Class constructor
         */
        function create() {
            'klass:nomunge'; // do not obfuscate constructor name
            var parent = null, args = Array.prototype.slice.call(arguments);
            if (typeof args[0] === 'function') {
                parent = args.shift();
            }

            function klass() {
                if (!(this instanceof klass)) {
                    return construct(klass, arguments);
                }
                this.init.apply(this, arguments);
            }

            extend(klass, Methods);
            klass.superclass = parent;
            klass.subclasses = [];

            if (parent) {
                var subclass = function() {};
                subclass.prototype = parent.prototype;
                klass.prototype = new subclass();
                if (parent.subclasses) {
                    parent.subclasses.push(klass);
                }
            }

            for (var i = 0; i < args.length; i++) {
                klass.addMethods(args[i]);
            }

            if (!klass.prototype.init) {
                klass.prototype.init = function() {};
            }

            klass.prototype.constructor = klass;

            return klass;
        }

        /**
         * Extends object by copying all properties from the source object to the destination object.<br />
         * By default source properties override destination properties, if such exists.
         * This can be avoided with safe param set to true.
         *
         * @example
         * // simple usage
         * var dest = {
         *         a: 1
         *     },
         *     source = {
         *         a: 2,
         *         b: true
         *     };
         * Zikula.Class.extend(dest, source, true); // dest is now {a: 1, b: true}
         *
         * // extend Animal class with some static method
         * Zikula.Class.extend(Animal, {
         *     staticProp: true,
         *     staticMethod: function() {
         *         alert('Animal.staticMethod called!');
         *     }
         * });
         * Animal.staticMethod(); // alerts "Animal.staticMethod called!"
         *
         * @name Zikula.Class.extend
         * @static
         * @function
         *
         * @param {Object}   dest        Destination object, where new properties will be copied
         * @param {Object}   source      Source object
         * @param {Boolean} [safe=false] If set to true, the destination object properties won't be overwritten
         *
         * @return {Object} Extended object
         */
        function extend(dest, source, safe) {
            safe = safe || false;
            for (var prop in source) {
                if (!dest[prop] || !safe) {
                    dest[prop] = source[prop];
                }
            }
            return dest;
        }

        /**
         * Calls class constructor with an arbitrary number of arguments.<br />
         * Allows to simulate use of .apply() on class constructor.
         *
         * @example
         * var args = ['some name', 'some sound'];
         * var instance = Zikula.Class.construct(Animal, args); // works the same as new Animal('some name', 'some sound');
         *
         * @name Zikula.Class.construct
         * @static
         * @function
         *
         * @param {Zikula.Class} klass Zikula.Class object
         * @param {*}     args  Arguments to pass to klass constructor
         *
         * @return {Zikula.Class} New instance of given klass
         */
        function construct(klass, args) {
            function F() {
                return klass.apply(this, args);
            }

            F.prototype = klass.prototype;
            return new F();
        }

        var privates = 'superclass subclasses addMethods getMethods hasMethod getStaticProperties hasStaticProperty'.split(' '),
            Methods = {
                /**
                 * Allows to add new (or redefine existing) instance methods.<br />
                 * This method is available on classes created by {@link Zikula.Class.create}.<br />
                 * New methods are added to all subclasses as well as the already instantiated instances.
                 *
                 * @example
                 * var Animal = Zikula.Class.create({
                 *     init: function(name, sound) {
                 *         this.name  = name;
                 *         this.sound = sound;
                 *     },
                 *     speak: function() {
                 *         alert(this.name + ' says: ' + this.sound + '!');
                 *     }
                 * });
                 * var Bird = Zikula.Class.create(Animal, {
                 *     init: function(sound) {
                 *         this._super('init', 'Bird', sound);
                 *     }
                 * });
                 * var littleBird = new Bird('Bird', 'tweet, tweet');
                 * Animal.addMethods({
                 *     speakLoud: function() {
                 *         alert(this.name + ' says: ' + this.sound.toUpperCase() + '!');
                 *     }
                 * });
                 * littleBird.speakLoud(); // alerts "Bird says: TWEET, TWEET!"
                 *
                 * @name Zikula.Class#addMethods
                 * @function
                 *
                 * @param {Object} source Source object containing methods to add
                 *
                 * @return {Zikula.Class}
                 */
                addMethods: function(source) {
                    var ancestor = this.superclass && this.superclass.prototype;

                    for (var name in source) {
                        this.prototype[name] = typeof source[name] === 'function' &&
                            ancestor && typeof ancestor[name] === 'function' && fnTest.test(source[name]) ? (function(name, fn) {
                            return function() {
                                this._super = function(method) {
                                    return ancestor[method].apply(this, Array.prototype.slice.call(arguments, 1));
                                };
                                return fn.apply(this, arguments);
                            };
                        })(name, source[name]) : source[name];
                    }

                    return this;
                },
                /**
                 * Gets the class methods' names.
                 *
                 * @example
                 * var Animal = Zikula.Class.create({
                 *     init: function(name, sound) {
                 *         this.name  = name;
                 *         this.sound = sound;
                 *     },
                 *     speak: function() {
                 *         alert(this.name + ' says: ' + this.sound + '!');
                 *     }
                 * });
                 * Animal.getMethods(); // returns ['init', 'speak']
                 *
                 * @name Zikula.Class#getMethods
                 * @function
                 *
                 * @return {Array} Array of class methods names
                 */
                getMethods: function() {
                    var methods = [];
                    for (var name in this.prototype) {
                        if (name !== 'constructor' && typeof this.prototype[name] === 'function') {
                            methods.push(name);
                        }
                    }
                    return methods;
                },
                /**
                 * Checks if the class method exists.
                 *
                 * @example
                 * var Animal = Zikula.Class.create({
                 *     init: function(name, sound) {
                 *         this.name  = name;
                 *         this.sound = sound;
                 *     },
                 *     speak: function() {
                 *         alert(this.name + ' says: ' + this.sound + '!');
                 *     }
                 * });
                 * Animal.hasMethod('speak'); // returns true
                 * Animal.hasMethod('speakQuietly'); // returns false
                 *
                 * @name Zikula.Class#hasMethod
                 * @function
                 *
                 * @param {String} name Method name to check
                 *
                 * @return {Boolean}
                 */
                hasMethod: function(name) {
                    return typeof this.prototype[name] === 'function';
                },
                /**
                 * Gets the class static properties names.<br />
                 * Internal properties and methods (such as superclass, subclasses etc) are ignored.
                 *
                 * @example
                 * Zikula.Class.extend(Animal, {
                 *     staticProp: true,
                 *     staticMethod: function() {
                 *         alert('Animal.staticMethod called!');
                 *     }
                 * });
                 * Animal.getStaticProperties(); // returns ['staticProp', 'staticMethod']
                 * Animal.hasMethod('speakQuietly'); // returns false
                 *
                 * @name Zikula.Class#getStaticProperties
                 * @function
                 *
                 * @return {Array} Array of class static properties
                 */
                getStaticProperties: function() {
                    var properties = [];
                    for (var name in this) {
                        if (privates.indexOf(name) === -1) {
                            properties.push(name);
                        }
                    }

                    return properties;
                },
                /**
                 * Checks if the class static property exists.
                 * Internal properties and methods (such as superclass, subclasses etc) are ignored.
                 *
                 * @example
                 * Zikula.Class.extend(Animal, {
                 *     staticProp: true,
                 *     staticMethod: function() {
                 *         alert('Animal.staticMethod called!');
                 *     }
                 *  });
                 * Animal.hasStaticProperty('staticMethod'); // returns true
                 * Animal.hasStaticProperty('speakQuietly'); // returns false
                 *
                 * @name Zikula.Class#hasStaticProperty
                 * @function
                 *
                 * @param {String} name Property name to check
                 *
                 * @return {Boolean}
                 */
                hasStaticProperty: function(name) {
                    return typeof this[name] !== 'undefined' && privates.indexOf(name) === -1;
                }
            };

        return {
            create: create,
            extend: extend,
            construct: construct
        };

    })();
})();