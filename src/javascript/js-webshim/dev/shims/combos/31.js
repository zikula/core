
//this might was already extended by ES5 shim feature
(function($){
	"use strict";
	var webshims = window.webshims;
	if(webshims.defineProperties){return;}
	var defineProperty = 'defineProperty';
	var has = Object.prototype.hasOwnProperty;
	var descProps = ['configurable', 'enumerable', 'writable'];
	var extendUndefined = function(prop){
		for(var i = 0; i < 3; i++){
			if(prop[descProps[i]] === undefined && (descProps[i] !== 'writable' || prop.value !== undefined)){
				prop[descProps[i]] = true;
			}
		}
	};

	var extendProps = function(props){
		if(props){
			for(var i in props){
				if(has.call(props, i)){
					extendUndefined(props[i]);
				}
			}
		}
	};

	if(Object.create){
		webshims.objectCreate = function(proto, props, opts){
			extendProps(props);
			var o = Object.create(proto, props);
			if(opts){
				o.options = $.extend(true, {}, o.options  || {}, opts);
				opts = o.options;
			}
			if(o._create && $.isFunction(o._create)){
				o._create(opts);
			}
			return o;
		};
	}

	if(Object[defineProperty]){
		webshims[defineProperty] = function(obj, prop, desc){
			extendUndefined(desc);
			return Object[defineProperty](obj, prop, desc);
		};
	}
	if(Object.defineProperties){
		webshims.defineProperties = function(obj, props){
			extendProps(props);
			return Object.defineProperties(obj, props);
		};
	}
	webshims.getOwnPropertyDescriptor = Object.getOwnPropertyDescriptor;

	webshims.getPrototypeOf = Object.getPrototypeOf;
})(window.webshims.$);
//DOM-Extension helper
webshims.register('dom-extend', function($, webshims, window, document, undefined){
	"use strict";
	var supportHrefNormalized = !('hrefNormalized' in $.support) || $.support.hrefNormalized;
	var has = Object.prototype.hasOwnProperty;
	webshims.assumeARIA = true;
	
	if($('<input type="email" />').attr('type') == 'text' || $('<form />').attr('novalidate') === "" || ('required' in $('<input />')[0].attributes)){
		webshims.error("IE browser modes are busted in IE10+. Make sure to run IE in edge mode (X-UA-Compatible). Please test your HTML/CSS/JS with a real IE version or at least IETester or similar tools. ");
	}
	
	if (!webshims.cfg.no$Switch) {
		var switch$ = function(){
			if (window.jQuery && (!window.$ || window.jQuery == window.$) && !window.jQuery.webshims) {
				webshims.error("jQuery was included more than once. Make sure to include it only once or try the $.noConflict(extreme) feature! Webshims and other Plugins might not work properly. Or set webshims.cfg.no$Switch to 'true'.");
				if (window.$) {
					window.$ = webshims.$;
				}
				window.jQuery = webshims.$;
			}
		};
		switch$();
		setTimeout(switch$, 90);
		webshims.ready('DOM', switch$);
		$(switch$);
		webshims.ready('WINDOWLOAD', switch$);
		
	}

	//shortcus
	var listReg = /\s*,\s*/;
		
	//proxying attribute
	var olds = {};
	var havePolyfill = {};
	var hasPolyfillMethod = {};
	var extendedProps = {};
	var extendQ = {};
	var modifyProps = {};
	
	var oldVal = $.fn.val;
	var singleVal = function(elem, name, val, pass, _argless){
		return (_argless) ? oldVal.call($(elem)) : oldVal.call($(elem), val);
	};
	
	//jquery mobile and jquery ui
	if(!$.widget && (!$.pluginFactory || !$.pluginFactory.mixin)){
		(function(){
			var _cleanData = $.cleanData;
			$.cleanData = (function( orig ) {
				return function( elems ) {
					var events, elem, i;
					for ( i = 0; (elem = elems[i]) != null; i++ ) {
						try {
							// Only trigger remove when necessary to save time
							events = $._data( elem, "events" );
							if ( events && events.remove ) {
								$( elem ).triggerHandler( "remove" );
							}
							// http://bugs.jquery.com/ticket/8235
						} catch ( e ) {}
					}
					orig( elems );
				};
			})( $.cleanData );
		})();
	}
	

	$.fn.val = function(val){
		var elem = this[0];
		if(arguments.length && val == null){
			val = '';
		}
		if(!arguments.length){
			if(!elem || elem.nodeType !== 1){return oldVal.call(this);}
			return $.prop(elem, 'value', val, 'val', true);
		}
		if($.isArray(val)){
			return oldVal.apply(this, arguments);
		}
		var isFunction = $.isFunction(val);
		return this.each(function(i){
			elem = this;
			if(elem.nodeType === 1){
				if(isFunction){
					var genVal = val.call( elem, i, $.prop(elem, 'value', undefined, 'val', true));
					if(genVal == null){
						genVal = '';
					}
					$.prop(elem, 'value', genVal, 'val') ;
				} else {
					$.prop(elem, 'value', val, 'val');
				}
			}
		});
	};
	$.fn.onTrigger = function(evt, fn){
		return this.on(evt, fn).each(fn);
	};
	
	$.fn.onWSOff = function(evt, fn, trigger, evtDel){
		if(!evtDel){
			evtDel = document;
		}
		$(evtDel)[trigger ? 'onTrigger' : 'on'](evt, fn);
		this.on('remove', function(e){
			if(!e.originalEvent){
				$(evtDel).off(evt, fn);
			}
		});
		return this;
	};
	var idCount = 0;
	var dataID = '_webshims'+ (Math.round(Math.random() * 1000));
	var elementData = function(elem, key, val){
		elem = elem.jquery ? elem[0] : elem;
		if(!elem){return val || {};}
		var data = $.data(elem, dataID);
		if(val !== undefined){
			if(!data){
				data = $.data(elem, dataID, {});
			}
			if(key){
				data[key] = val;
			}
		}
		
		return key ? data && data[key] : data;
	};


	[{name: 'getNativeElement', prop: 'nativeElement'}, {name: 'getShadowElement', prop: 'shadowElement'}, {name: 'getShadowFocusElement', prop: 'shadowFocusElement'}].forEach(function(data){
		$.fn[data.name] = function(){
			var elems = [];
			this.each(function(){
				var shadowData = elementData(this, 'shadowData');
				var elem = shadowData && shadowData[data.prop] || this;
				if($.inArray(elem, elems) == -1){
					elems.push(elem);
				}
			});
			return this.pushStack(elems);
		};
	});

	function clone(elem, dataAndEvents, uniqueIds){
		var cloned = $.clone( elem, dataAndEvents, false );
		$(cloned.querySelectorAll('.'+webshims.shadowClass)).detach();
		if(uniqueIds){
			idCount++;
			$(cloned.querySelectorAll('[id]')).prop('id', function(i, id){
				return id +idCount;
			});
		} else {
			$(cloned.querySelectorAll('audio[id^="ID-"], video[id^="ID-"], label[id^="ID-"]')).removeAttr('id');
		}
		return cloned;
	}

	$.fn.clonePolyfill = function(dataAndEvents, uniqueIds){
		dataAndEvents = dataAndEvents || false;
		return this
			.map(function() {
				var cloned = clone( this, dataAndEvents, uniqueIds );
				setTimeout(function(){
					if($.contains(document.body, cloned)){
						$(cloned).updatePolyfill();
					}
				});
				return cloned;
			})
		;
	};
	
	//add support for $('video').trigger('play') in case extendNative is set to false
	if(!webshims.cfg.extendNative && !webshims.cfg.noTriggerOverride){
		(function(oldTrigger){
			$.event.trigger = function(event, data, elem, onlyHandlers){
				
				if(!hasPolyfillMethod[event] || onlyHandlers || !elem || elem.nodeType !== 1){
					return oldTrigger.apply(this, arguments);
				}
				var ret, isOrig, origName;
				var origFn = elem[event];
				var polyfilledFn = $.prop(elem, event);
				var changeFn = polyfilledFn && origFn != polyfilledFn;
				if(changeFn){
					origName = '__ws'+event;
					isOrig = (event in elem) && has.call(elem, event);
					elem[event] = polyfilledFn;
					elem[origName] = origFn;
				}
				
				ret = oldTrigger.apply(this, arguments);
				if (changeFn) {
					if(isOrig){
						elem[event] = origFn;
					} else {
						delete elem[event];
					}
					delete elem[origName];
				}
				
				return ret;
			};
		})($.event.trigger);
	}
	
	['removeAttr', 'prop', 'attr'].forEach(function(type){
		olds[type] = $[type];
		$[type] = function(elem, name, value, pass, _argless){
			var isVal = (pass == 'val');
			var oldMethod = !isVal ? olds[type] : singleVal;
			if( !elem || !havePolyfill[name] || elem.nodeType !== 1 || (!isVal && pass && type == 'attr' && $.attrFn[name]) ){
				return oldMethod(elem, name, value, pass, _argless);
			}
			
			var nodeName = (elem.nodeName || '').toLowerCase();
			var desc = extendedProps[nodeName];
			var curType = (type == 'attr' && (value === false || value === null)) ? 'removeAttr' : type;
			var propMethod;
			var oldValMethod;
			var ret;
			
			
			if(!desc){
				desc = extendedProps['*'];
			}
			if(desc){
				desc = desc[name];
			}
			
			if(desc){
				propMethod = desc[curType];
			}
			
			if(propMethod){
				if(name == 'value'){
					oldValMethod = propMethod.isVal;
					propMethod.isVal = isVal;
				}
				if(curType === 'removeAttr'){
					return propMethod.value.call(elem);	
				} else if(value === undefined){
					return (propMethod.get) ? 
						propMethod.get.call(elem) : 
						propMethod.value
					;
				} else if(propMethod.set) {
					if(type == 'attr' && value === true){
						value = name;
					}
					
					ret = propMethod.set.call(elem, value);
				}
				if(name == 'value'){
					propMethod.isVal = oldValMethod;
				}
			} else {
				ret = oldMethod(elem, name, value, pass, _argless);
			}
			if((value !== undefined || curType === 'removeAttr') && modifyProps[nodeName] && modifyProps[nodeName][name]){
				
				var boolValue;
				if(curType == 'removeAttr'){
					boolValue = false;
				} else if(curType == 'prop'){
					boolValue = !!(value);
				} else {
					boolValue = true;
				}
				
				modifyProps[nodeName][name].forEach(function(fn){
					if(!fn.only || (fn.only = 'prop' && type == 'prop') || (fn.only == 'attr' && type != 'prop')){
						fn.call(elem, value, boolValue, (isVal) ? 'val' : curType, type);
					}
				});
			}
			return ret;
		};
		
		extendQ[type] = function(nodeName, prop, desc){
			
			if(!extendedProps[nodeName]){
				extendedProps[nodeName] = {};
			}
			if(!extendedProps[nodeName][prop]){
				extendedProps[nodeName][prop] = {};
			}
			var oldDesc = extendedProps[nodeName][prop][type];
			var getSup = function(propType, descriptor, oDesc){
				var origProp;
				if(descriptor && descriptor[propType]){
					return descriptor[propType];
				}
				if(oDesc && oDesc[propType]){
					return oDesc[propType];
				}
				if(type == 'prop' && prop == 'value'){
					return function(value){
						var elem = this;
						return (desc.isVal) ? 
							singleVal(elem, prop, value, false, (arguments.length === 0)) : 
							olds[type](elem, prop, value)
						;
					};
				}
				if(type == 'prop' && propType == 'value' && desc.value.apply){
					origProp = '__ws'+prop;
					hasPolyfillMethod[prop] = true;
					return  function(value){
						var sup = this[origProp] || olds[type](this, prop);
						if(sup && sup.apply){
							sup = sup.apply(this, arguments);
						} 
						return sup;
					};
				}
				return function(value){
					return olds[type](this, prop, value);
				};
			};
			extendedProps[nodeName][prop][type] = desc;
			if(desc.value === undefined){
				if(!desc.set){
					desc.set = desc.writeable ? 
						getSup('set', desc, oldDesc) : 
						(webshims.cfg.useStrict && prop == 'prop') ? 
							function(){throw(prop +' is readonly on '+ nodeName);} : 
							function(){webshims.info(prop +' is readonly on '+ nodeName);}
					;
				}
				if(!desc.get){
					desc.get = getSup('get', desc, oldDesc);
				}
				
			}
			
			['value', 'get', 'set'].forEach(function(descProp){
				if(desc[descProp]){
					desc['_sup'+descProp] = getSup(descProp, oldDesc);
				}
			});
		};
		
	});
	
	var extendNativeValue = (function(){
		var UNKNOWN = webshims.getPrototypeOf(document.createElement('foobar'));
		
		//see also: https://github.com/lojjic/PIE/issues/40 | https://prototype.lighthouseapp.com/projects/8886/tickets/1107-ie8-fatal-crash-when-prototypejs-is-loaded-with-rounded-cornershtc
		var isExtendNativeSave = webshims.support.advancedObjectProperties && webshims.support.objectAccessor;
		return function(nodeName, prop, desc){
			var elem , elemProto;
			 if( isExtendNativeSave && (elem = document.createElement(nodeName)) && (elemProto = webshims.getPrototypeOf(elem)) && UNKNOWN !== elemProto && ( !elem[prop] || !has.call(elem, prop) ) ){
				var sup = elem[prop];
				desc._supvalue = function(){
					if(sup && sup.apply){
						return sup.apply(this, arguments);
					}
					return sup;
				};
				elemProto[prop] = desc.value;
			} else {
				desc._supvalue = function(){
					var data = elementData(this, 'propValue');
					if(data && data[prop] && data[prop].apply){
						return data[prop].apply(this, arguments);
					}
					return data && data[prop];
				};
				initProp.extendValue(nodeName, prop, desc.value);
			}
			desc.value._supvalue = desc._supvalue;
		};
	})();
		
	var initProp = (function(){
		
		var initProps = {};
		
		webshims.addReady(function(context, contextElem){
			var nodeNameCache = {};
			var getElementsByName = function(name){
				if(!nodeNameCache[name]){
					nodeNameCache[name] = $(context.getElementsByTagName(name));
					if(contextElem[0] && $.nodeName(contextElem[0], name)){
						nodeNameCache[name] = nodeNameCache[name].add(contextElem);
					}
				}
			};
			
			
			$.each(initProps, function(name, fns){
				getElementsByName(name);
				if(!fns || !fns.forEach){
					webshims.warn('Error: with '+ name +'-property. methods: '+ fns);
					return;
				}
				fns.forEach(function(fn){
					nodeNameCache[name].each(fn);
				});
			});
			nodeNameCache = null;
		});
		
		var tempCache;
		var emptyQ = $([]);
		var createNodeNameInit = function(nodeName, fn){
			if(!initProps[nodeName]){
				initProps[nodeName] = [fn];
			} else {
				initProps[nodeName].push(fn);
			}
			if($.isDOMReady){
				(tempCache || $( document.getElementsByTagName(nodeName) )).each(fn);
			}
		};

		return {
			createTmpCache: function(nodeName){
				if($.isDOMReady){
					tempCache = tempCache || $( document.getElementsByTagName(nodeName) );
				}
				return tempCache || emptyQ;
			},
			flushTmpCache: function(){
				tempCache = null;
			},
			content: function(nodeName, prop){
				createNodeNameInit(nodeName, function(){
					var val =  $.attr(this, prop);
					if(val != null){
						$.attr(this, prop, val);
					}
				});
			},
			createElement: function(nodeName, fn){
				createNodeNameInit(nodeName, fn);
			},
			extendValue: function(nodeName, prop, value){
				createNodeNameInit(nodeName, function(){
					$(this).each(function(){
						var data = elementData(this, 'propValue', {});
						data[prop] = this[prop];
						this[prop] = value;
					});
				});
			}
		};
	})();
		
	var createPropDefault = function(descs, removeType){
		if(descs.defaultValue === undefined){
			descs.defaultValue = '';
		}
		if(!descs.removeAttr){
			descs.removeAttr = {
				value: function(){
					descs[removeType || 'prop'].set.call(this, descs.defaultValue);
					descs.removeAttr._supvalue.call(this);
				}
			};
		}
		if(!descs.attr){
			descs.attr = {};
		}
	};
	
	$.extend(webshims, {
		xProps: havePolyfill,
		getID: (function(){
			var ID = new Date().getTime();
			return function(elem){
				elem = $(elem);
				var id = elem.prop('id');
				if(!id){
					ID++;
					id = 'ID-'+ ID;
					elem.eq(0).prop('id', id);
				}
				return id;
			};
		})(),
		domPrefixes: ["webkit", "moz", "ms", "o", "ws"],

		prefixed: function (prop, obj){
			var i, testProp;
			var ret = false;
			if(obj[prop]){
				ret = prop;
			}
			if(!ret){
				prop = prop.charAt(0).toUpperCase() + prop.slice(1);
				for(i = 0; i < webshims.domPrefixes.length; i++){
					testProp = webshims.domPrefixes[i]+prop;
					if(testProp in obj){
						ret = testProp;
						break;
					}
				}
			}
			return ret;
		},
		shadowClass: 'wsshadow-'+(Date.now()),
		implement: function(elem, type){
			var data = elementData(elem, 'implemented') || elementData(elem, 'implemented', {});
			if(data[type]){
				webshims.warn(type +' already implemented for element #'+elem.id);
				return false;
			}

			data[type] = true;
			return !$(elem).hasClass('ws-nopolyfill');
		},
		extendUNDEFProp: function(obj, props){
			$.each(props, function(name, prop){
				if( !(name in obj) ){
					obj[name] = prop;
				}
			});
		},
		getOptions: (function(){
			var normalName = /\-([a-z])/g;
			var regs = {};
			var nameRegs = {};
			var regFn = function(f, upper){
				return upper.toLowerCase();
			};
			var nameFn = function(f, dashed){
				return dashed.toUpperCase();
			};
			return function(elem, name, bases, stringAllowed){
				if(nameRegs[name]){
					name = nameRegs[name];
				} else {
					nameRegs[name] = name.replace(normalName, nameFn);
					name = nameRegs[name];
				}
				var data = elementData(elem, 'cfg'+name);
				var dataName;
				var cfg = {};
				
				if(data){
					return data;
				}
				data = $(elem).data();
				if(data && typeof data[name] == 'string'){
					if(stringAllowed){
						return elementData(elem, 'cfg'+name, data[name]);
					}
					webshims.error('data-'+ name +' attribute has to be a valid JSON, was: '+ data[name]);
				}
				if(!bases){
					bases = [true, {}];
				} else if(!Array.isArray(bases)){
					bases = [true, {}, bases];
				} else {
					bases.unshift(true, {});
				}
				
				if(data && typeof data[name] == 'object'){
					bases.push(data[name]);
				}
				
				if(!regs[name]){
					regs[name] = new RegExp('^'+ name +'([A-Z])');
				}
				
				for(dataName in data){
					if(regs[name].test(dataName)){
						cfg[dataName.replace(regs[name], regFn)] = data[dataName];
					}
				}
				bases.push(cfg);
				return elementData(elem, 'cfg'+name, $.extend.apply($, bases));
			};
		})(),
		//http://www.w3.org/TR/html5/common-dom-interfaces.html#reflect
		createPropDefault: createPropDefault,
		data: elementData,
		moveToFirstEvent: function(elem, eventType, bindType){
			var events = ($._data(elem, 'events') || {})[eventType];
			var fn;
			
			if(events && events.length > 1){
				fn = events.pop();
				if(!bindType){
					bindType = 'bind';
				}
				if(bindType == 'bind' && events.delegateCount){
					events.splice( events.delegateCount, 0, fn);
				} else {
					events.unshift( fn );
				}
				
				
			}
			elem = null;
		},
		addShadowDom: (function(){
			var resizeTimer;
			var lastHeight;
			var lastWidth;
			var $window = $(window);
			var docObserve = {
				init: false,
				runs: 0,
				test: function(){
					var height = docObserve.getHeight();
					var width = docObserve.getWidth();
					
					if(height != docObserve.height || width != docObserve.width){
						docObserve.height = height;
						docObserve.width = width;
						docObserve.handler({type: 'docresize'});
						docObserve.runs++;
						if(docObserve.runs < 9){
							setTimeout(docObserve.test, 90);
						}
					} else {
						docObserve.runs = 0;
					}
				},
				handler: (function(){
					var evt;
					var trigger = function(){
						$(document).triggerHandler('updateshadowdom', [evt]);
					};
					var timed = function(){
						if(evt && evt.type == 'resize'){
							var width = $window.width();
							var height = $window.width();

							if(height == lastHeight && width == lastWidth){
								return;
							}
							lastHeight = height;
							lastWidth = width;
						}

						if(evt && evt.type != 'docresize'){
							docObserve.height = docObserve.getHeight();
							docObserve.width = docObserve.getWidth();
						}

						if(window.requestAnimationFrame){
							requestAnimationFrame(trigger);
						} else {
							setTimeout(trigger, 0);
						}
					};
					return function(e){
						clearTimeout(resizeTimer);
						evt = e;
						resizeTimer = setTimeout(timed, (e.type == 'resize' && !window.requestAnimationFrame) ? 50 : 9);
					};
				})(),
				_create: function(){
					$.each({ Height: "getHeight", Width: "getWidth" }, function(name, type){
						var body = document.body;
						var doc = document.documentElement;
						docObserve[type] = function (){
							return Math.max(
								body[ "scroll" + name ], doc[ "scroll" + name ],
								body[ "offset" + name ], doc[ "offset" + name ],
								doc[ "client" + name ]
							);
						};
					});
				},
				start: function(){
					if(!this.init && document.body){
						this.init = true;
						this._create();
						this.height = docObserve.getHeight();
						this.width = docObserve.getWidth();
						setInterval(this.test, 999);
						$(this.test);
						if($.support.boxSizing == null){
							$(function(){
								if($.support.boxSizing){
									docObserve.handler({type: 'boxsizing'});
								}
							});
						}
						webshims.ready('WINDOWLOAD', this.test);
						$(document).on('updatelayout.webshim pageinit popupafteropen panelbeforeopen tabsactivate collapsibleexpand shown.bs.modal shown.bs.collapse slid.bs.carousel playerdimensionchange', this.handler);
						$(window).on('resize', this.handler);
					}
				}
			};
			
			
			webshims.docObserve = function(){
				webshims.ready('DOM', function(){
					docObserve.start();

				});
			};
			return function(nativeElem, shadowElem, opts){
				if(nativeElem && shadowElem){
					opts = opts || {};
					if(nativeElem.jquery){
						nativeElem = nativeElem[0];
					}
					if(shadowElem.jquery){
						shadowElem = shadowElem[0];
					}
					var nativeData = $.data(nativeElem, dataID) || $.data(nativeElem, dataID, {});
					var shadowData = $.data(shadowElem, dataID) || $.data(shadowElem, dataID, {});
					var shadowFocusElementData = {};
					if(!opts.shadowFocusElement){
						opts.shadowFocusElement = shadowElem;
					} else if(opts.shadowFocusElement){
						if(opts.shadowFocusElement.jquery){
							opts.shadowFocusElement = opts.shadowFocusElement[0];
						}
						shadowFocusElementData = $.data(opts.shadowFocusElement, dataID) || $.data(opts.shadowFocusElement, dataID, shadowFocusElementData);
					}
					
					$(nativeElem).on('remove', function(e){
						if (!e.originalEvent) {
							setTimeout(function(){
								$(shadowElem).remove();
							}, 4);
						}
					});
					
					nativeData.hasShadow = shadowElem;
					shadowFocusElementData.nativeElement = shadowData.nativeElement = nativeElem;
					shadowFocusElementData.shadowData = shadowData.shadowData = nativeData.shadowData = {
						nativeElement: nativeElem,
						shadowElement: shadowElem,
						shadowFocusElement: opts.shadowFocusElement
					};
					if(opts.shadowChilds){
						opts.shadowChilds.each(function(){
							elementData(this, 'shadowData', shadowData.shadowData);
						});
					}
					
					if(opts.data){
						shadowFocusElementData.shadowData.data = shadowData.shadowData.data = nativeData.shadowData.data = opts.data;
					}
					opts = null;
				}
				webshims.docObserve();
			};
		})(),
		propTypes: {
			standard: function(descs, name){
				createPropDefault(descs);
				if(descs.prop){return;}
				descs.prop = {
					set: function(val){
						descs.attr.set.call(this, ''+val);
					},
					get: function(){
						return descs.attr.get.call(this) || descs.defaultValue;
					}
				};
				
			},
			"boolean": function(descs, name){
				
				createPropDefault(descs);
				if(descs.prop){return;}
				descs.prop = {
					set: function(val){
						if(val){
							descs.attr.set.call(this, "");
						} else {
							descs.removeAttr.value.call(this);
						}
					},
					get: function(){
						return descs.attr.get.call(this) != null;
					}
				};
			},
			"src": (function(){
				var anchor = document.createElement('a');
				anchor.style.display = "none";
				return function(descs, name){
					
					createPropDefault(descs);
					if(descs.prop){return;}
					descs.prop = {
						set: function(val){
							descs.attr.set.call(this, val);
						},
						get: function(){
							var href = this.getAttribute(name);
							var ret;
							if(href == null){return '';}
							
							anchor.setAttribute('href', href+'' );
							
							if(!supportHrefNormalized){
								try {
									$(anchor).insertAfter(this);
									ret = anchor.getAttribute('href', 4);
								} catch(er){
									ret = anchor.getAttribute('href', 4);
								}
								$(anchor).detach();
							}
							return ret || anchor.href;
						}
					};
				};
			})(),
			enumarated: function(descs, name){
					
					createPropDefault(descs);
					if(descs.prop){return;}
					descs.prop = {
						set: function(val){
							descs.attr.set.call(this, val);
						},
						get: function(){
							var val = (descs.attr.get.call(this) || '').toLowerCase();
							if(!val || descs.limitedTo.indexOf(val) == -1){
								val = descs.defaultValue;
							}
							return val;
						}
					};
				}
			
//			,unsignedLong: $.noop
//			,"doubble": $.noop
//			,"long": $.noop
//			,tokenlist: $.noop
//			,settableTokenlist: $.noop
		},
		reflectProperties: function(nodeNames, props){
			if(typeof props == 'string'){
				props = props.split(listReg);
			}
			props.forEach(function(prop){
				webshims.defineNodeNamesProperty(nodeNames, prop, {
					prop: {
						set: function(val){
							$.attr(this, prop, val);
						},
						get: function(){
							return $.attr(this, prop) || '';
						}
					}
				});
			});
		},
		defineNodeNameProperty: function(nodeName, prop, descs){
			havePolyfill[prop] = true;
						
			if(descs.reflect){
				if(descs.propType && !webshims.propTypes[descs.propType]){
					webshims.error('could not finde propType '+ descs.propType);
				} else {
					webshims.propTypes[descs.propType || 'standard'](descs, prop);
				}
				
			}
			
			['prop', 'attr', 'removeAttr'].forEach(function(type){
				var desc = descs[type];
				if(desc){
					if(type === 'prop'){
						desc = $.extend({writeable: true}, desc);
					} else {
						desc = $.extend({}, desc, {writeable: true});
					}
						
					extendQ[type](nodeName, prop, desc);
					if(nodeName != '*' && webshims.cfg.extendNative && type == 'prop' && desc.value && $.isFunction(desc.value)){
						extendNativeValue(nodeName, prop, desc);
					}
					descs[type] = desc;
				}
			});
			if(descs.initAttr){
				initProp.content(nodeName, prop);
			}
			return descs;
		},
		
		defineNodeNameProperties: function(name, descs, propType, _noTmpCache){
			var olddesc;
			for(var prop in descs){
				if(!_noTmpCache && descs[prop].initAttr){
					initProp.createTmpCache(name);
				}
				if(propType){
					if(descs[prop][propType]){
						//webshims.log('override: '+ name +'['+prop +'] for '+ propType);
					} else {
						descs[prop][propType] = {};
						['value', 'set', 'get'].forEach(function(copyProp){
							if(copyProp in descs[prop]){
								descs[prop][propType][copyProp] = descs[prop][copyProp];
								delete descs[prop][copyProp];
							}
						});
					}
				}
				descs[prop] = webshims.defineNodeNameProperty(name, prop, descs[prop]);
			}
			if(!_noTmpCache){
				initProp.flushTmpCache();
			}
			return descs;
		},
		
		createElement: function(nodeName, create, descs){
			var ret;
			if($.isFunction(create)){
				create = {
					after: create
				};
			}
			initProp.createTmpCache(nodeName);
			if(create.before){
				initProp.createElement(nodeName, create.before);
			}
			if(descs){
				ret = webshims.defineNodeNameProperties(nodeName, descs, false, true);
			}
			if(create.after){
				initProp.createElement(nodeName, create.after);
			}
			initProp.flushTmpCache();
			return ret;
		},
		onNodeNamesPropertyModify: function(nodeNames, props, desc, only){
			if(typeof nodeNames == 'string'){
				nodeNames = nodeNames.split(listReg);
			}
			if($.isFunction(desc)){
				desc = {set: desc};
			}
			
			nodeNames.forEach(function(name){
				if(!modifyProps[name]){
					modifyProps[name] = {};
				}
				if(typeof props == 'string'){
					props = props.split(listReg);
				}
				if(desc.initAttr){
					initProp.createTmpCache(name);
				}
				props.forEach(function(prop){
					if(!modifyProps[name][prop]){
						modifyProps[name][prop] = [];
						havePolyfill[prop] = true;
					}
					if(desc.set){
						if(only){
							desc.set.only =  only;
						}
						modifyProps[name][prop].push(desc.set);
					}
					
					if(desc.initAttr){
						initProp.content(name, prop);
					}
				});
				initProp.flushTmpCache();
				
			});
		},
		defineNodeNamesBooleanProperty: function(elementNames, prop, descs){
			if(!descs){
				descs = {};
			}
			if($.isFunction(descs)){
				descs.set = descs;
			}
			webshims.defineNodeNamesProperty(elementNames, prop, {
				attr: {
					set: function(val){
						if(descs.useContentAttribute){
							webshims.contentAttr(this, prop, val);
						} else {
							this.setAttribute(prop, val);
						}
						if(descs.set){
							descs.set.call(this, true);
						}
					},
					get: function(){
						var ret = (descs.useContentAttribute) ? webshims.contentAttr(this, prop) : this.getAttribute(prop);
						return (ret == null) ? undefined : prop;
					}
				},
				removeAttr: {
					value: function(){
						this.removeAttribute(prop);
						if(descs.set){
							descs.set.call(this, false);
						}
					}
				},
				reflect: true,
				propType: 'boolean',
				initAttr: descs.initAttr || false
			});
		},
		contentAttr: function(elem, name, val){
			if(!elem.nodeName){return;}
			var attr;
			if(val === undefined){
				attr = (elem.attributes[name] || {});
				val = attr.specified ? attr.value : null;
				return (val == null) ? undefined : val;
			}
			
			if(typeof val == 'boolean'){
				if(!val){
					elem.removeAttribute(name);
				} else {
					elem.setAttribute(name, name);
				}
			} else {
				elem.setAttribute(name, val);
			}
		},
		
		activeLang: (function(){
			var curLang = [];
			var langDatas = [];
			var loading = {};
			var load = function(src, obj, loadingLang){
				obj._isLoading = true;
				if(loading[src]){
					loading[src].push(obj);
				} else {
					loading[src] = [obj];
					webshims.loader.loadScript(src, function(){
						if(loadingLang == curLang.join()){
							$.each(loading[src], function(i, obj){
								select(obj);
							});
						}
						delete loading[src];
					});
				}
			};
			
			var select = function(obj){
				var oldLang = obj.__active;
				var selectLang = function(i, lang){
					obj._isLoading = false;
					if(obj[lang] || obj.availableLangs.indexOf(lang) != -1){
						if(obj[lang]){
							obj.__active = obj[lang];
							obj.__activeName = lang;
						} else {
							load(obj.langSrc+lang, obj, curLang.join());
						}
						return false;
					}
				};
				$.each(curLang, selectLang);
				if(!obj.__active){
					obj.__active = obj[''];
					obj.__activeName = '';
				}
				if(oldLang != obj.__active){
					$(obj).trigger('change');
				}
			};
			return function(lang){
				var shortLang;
				if(typeof lang == 'string'){
					if(curLang[0] != lang){
						curLang = [lang];
						shortLang = curLang[0].split('-')[0];
						if(shortLang && shortLang != lang){
							curLang.push(shortLang);
						}
						langDatas.forEach(select);
					}
				} else if(typeof lang == 'object'){
					if(!lang.__active){
						langDatas.push(lang);
						select(lang);
					}
					return lang.__active;
				}
				return curLang[0];
			};
		})()
	});
	
	$.each({
		defineNodeNamesProperty: 'defineNodeNameProperty',
		defineNodeNamesProperties: 'defineNodeNameProperties',
		createElements: 'createElement'
	}, function(name, baseMethod){
		webshims[name] = function(names, a, b, c){
			if(typeof names == 'string'){
				names = names.split(listReg);
			}
			var retDesc = {};
			names.forEach(function(nodeName){
				retDesc[nodeName] = webshims[baseMethod](nodeName, a, b, c);
			});
			return retDesc;
		};
	});
	
	webshims.isReady('webshimLocalization', true);

//html5a11y + hidden attribute
(function(){
	if(('content' in document.createElement('template'))){return;}
	
	$(function(){
		var main = $('main').attr({role: 'main'});
		if(main.length > 1){
			webshims.error('only one main element allowed in document');
		} else if(main.is('article *, section *')) {
			webshims.error('main not allowed inside of article/section elements');
		}
	});
	
	if(('hidden' in document.createElement('a'))){
		return;
	}
	
	webshims.defineNodeNamesBooleanProperty(['*'], 'hidden');
	
	var elemMappings = {
		article: "article",
		aside: "complementary",
		section: "region",
		nav: "navigation",
		address: "contentinfo"
	};
	var addRole = function(elem, role){
		var hasRole = elem.getAttribute('role');
		if (!hasRole) {
			elem.setAttribute('role', role);
		}
	};
	
	
	$.webshims.addReady(function(context, contextElem){
		$.each(elemMappings, function(name, role){
			var elems = $(name, context).add(contextElem.filter(name));
			for (var i = 0, len = elems.length; i < len; i++) {
				addRole(elems[i], role);
			}
		});
		if (context === document) {
			var header = document.getElementsByTagName('header')[0];
			var footers = document.getElementsByTagName('footer');
			var footerLen = footers.length;
			
			if (header && !$(header).closest('section, article')[0]) {
				addRole(header, 'banner');
			}
			if (!footerLen) {
				return;
			}
			var footer = footers[footerLen - 1];
			if (!$(footer).closest('section, article')[0]) {
				addRole(footer, 'contentinfo');
			}
		}
	});
	
})();
});
;webshims.register('form-core', function($, webshims, window, document, undefined, options){
	"use strict";

	webshims.capturingEventPrevented = function(e){
		if(!e._isPolyfilled){
			var isDefaultPrevented = e.isDefaultPrevented;
			var preventDefault = e.preventDefault;
			e.preventDefault = function(){
				clearTimeout($.data(e.target, e.type + 'DefaultPrevented'));
				$.data(e.target, e.type + 'DefaultPrevented', setTimeout(function(){
					$.removeData(e.target, e.type + 'DefaultPrevented');
				}, 30));
				return preventDefault.apply(this, arguments);
			};
			e.isDefaultPrevented = function(){
				return !!(isDefaultPrevented.apply(this, arguments) || $.data(e.target, e.type + 'DefaultPrevented') || false);
			};
			e._isPolyfilled = true;
		}
	};


	var modules = webshims.modules;
	var support = webshims.support;
	var isValid = function(elem){
		return ($.prop(elem, 'validity') || {valid: 1}).valid;
	};
	var lazyLoad = function(){
		var toLoad = ['form-validation'];

		$(document).off('.lazyloadvalidation');

		if(options.lazyCustomMessages){
			options.customMessages = true;
			toLoad.push('form-message');
		}

		if(webshims._getAutoEnhance(options.customDatalist)){
			options.fD = true;
			toLoad.push('form-datalist');
		}

		if(options.addValidators){
			toLoad.push('form-validators');
		}
		webshims.reTest(toLoad);
	};
	/*
	 * Selectors for all browsers
	 */
	
	var extendSels = function(){
		var matches, matchesOverride;
		var exp = $.expr[":"];
		var rElementsGroup = /^(?:form|fieldset)$/i;
		var hasInvalid = function(elem){
			var ret = false;
			$(elem).jProp('elements').each(function(){
				if(!rElementsGroup.test(this.nodeName || '')){
					ret = exp.invalid(this);
					if(ret){
						return false;
					}
				}

			});
			return ret;
		};
		$.extend(exp, {
			"valid-element": function(elem){
				return rElementsGroup.test(elem.nodeName || '') ? !hasInvalid(elem) : !!($.prop(elem, 'willValidate') && isValid(elem));
			},
			"invalid-element": function(elem){
				return rElementsGroup.test(elem.nodeName || '') ? hasInvalid(elem) : !!($.prop(elem, 'willValidate') && !isValid(elem));
			},
			"required-element": function(elem){
				return !!($.prop(elem, 'willValidate') && $.prop(elem, 'required'));
			},
			"user-error": function(elem){
				return ($.prop(elem, 'willValidate') && $(elem).getShadowElement().hasClass((options.iVal.errorClass || 'user-error')));
			},
			"optional-element": function(elem){
				return !!($.prop(elem, 'willValidate') && $.prop(elem, 'required') === false);
			}
		});
		
		['valid', 'invalid', 'required', 'optional'].forEach(function(name){
			exp[name] = $.expr[":"][name+"-element"];
		});
		
		// sizzle/jQuery has a bug with :disabled/:enabled selectors
		if(support.fieldsetdisabled && !$('<fieldset disabled=""><input /><input /></fieldset>').find(':disabled').filter(':disabled').is(':disabled')){
			matches = $.find.matches;
			matchesOverride = {':disabled': 1, ':enabled': 1};
			$.find.matches = function(expr, elements){
				if(matchesOverride[expr]){
					return matches.call(this, '*'+expr, elements);
				}
				return matches.apply(this, arguments);
			};
			$.extend(exp, {
				"enabled": function( elem ) {
					return 'disabled' in elem && elem.disabled === false && !$.find.matchesSelector(elem, 'fieldset[disabled] *');
				},
		
				"disabled": function( elem ) {
					return elem.disabled === true || ('disabled' in elem && $.find.matchesSelector(elem, 'fieldset[disabled] *'));
				}
			});
		}
		
		
		//bug was partially fixed in 1.10.0 for IE9, but not IE8 (move to es5 as soon as 1.10.2 is used)
		if(typeof document.activeElement == 'unknown'){
			var pseudoFocus = exp.focus;
			exp.focus = function(){
				try {
					return pseudoFocus.apply(this, arguments);
				} catch(e){
					webshims.error(e);
				}
				return false;
			};
		}
	};
	var formExtras = {
		noAutoCallback: true,
		options: options
	};
	var addModule = webshims.loader.addModule;
	var lazyLoadProxy = function(obj, fn, args){
		lazyLoad();
		webshims.ready('form-validation', function(){
			obj[fn].apply(obj, args);
		});
	};

	var transClass = ('transitionDelay' in document.documentElement.style) ?  '' : ' no-transition';
	var poCFG = webshims.cfg.wspopover;

	addModule('form-validation', $.extend({d: ['form-message']}, formExtras));

	addModule('form-validators', $.extend({}, formExtras));


	if(support.formvalidation && !webshims.bugs.bustedValidity){
		//create delegatable events
		webshims.capturingEvents(['invalid'], true);
	}
	
	if($.expr.filters){
		extendSels();
	} else {
		webshims.ready('sizzle', extendSels);
	}

	webshims.triggerInlineForm = function(elem, event){
		$(elem).trigger(event);
	};
	

	if(!poCFG.position && poCFG.position !== false){
		poCFG.position = {
			at: 'left bottom',
			my: 'left top',
			collision: 'fit flip'
		};
	}
	webshims.wsPopover = {
		id: 0,
		_create: function(){
			this.options = $.extend(true, {}, poCFG, this.options);
			this.id = webshims.wsPopover.id++;
			this.eventns = '.wsoverlay' + this.id;
			this.timers = {};
			this.element = $('<div class="ws-popover'+transClass+'" tabindex="-1"><div class="ws-po-outerbox"><div class="ws-po-arrow"><div class="ws-po-arrowbox" /></div><div class="ws-po-box" /></div></div>');
			this.contentElement = $('.ws-po-box', this.element);
			this.lastElement = $([]);
			this.bindElement();
			
			this.element.data('wspopover', this);
			
		},
		options: {},
		content: function(html){
			this.contentElement.html(html);
		},
		bindElement: function(){
			var that = this;
			var stopBlur = function(){
				that.stopBlur = false;
			};
			this.preventBlur = function(e){
				that.stopBlur = true;
				clearTimeout(that.timers.stopBlur);
				that.timers.stopBlur = setTimeout(stopBlur, 9);
			};
			this.element.on({
				'mousedown': this.preventBlur
			});
		},
		show: function(){
			lazyLoadProxy(this, 'show', arguments);
		}
	};
	
	/* some extra validation UI */
	webshims.validityAlert = {
		showFor: function(){
			lazyLoadProxy(this, 'showFor', arguments);
		}
	};
	
	
	webshims.getContentValidationMessage = function(elem, validity, key){
		var customRule;
		if(webshims.errorbox && webshims.errorbox.initIvalContentMessage){
			webshims.errorbox.initIvalContentMessage(elem);
		}
		var message = (webshims.getOptions && webshims.errorbox ? webshims.getOptions(elem, 'errormessage', false, true) : $(elem).data('errormessage')) || elem.getAttribute('x-moz-errormessage') || '';
		if(key && message[key]){
			message = message[key];
		} else if(message) {
			validity = validity || $.prop(elem, 'validity') || {valid: 1};
			if(validity.valid){
				message = '';
			}
		}
		if(typeof message == 'object'){
			validity = validity || $.prop(elem, 'validity') || {valid: 1};
			if(validity.customError && (customRule = $.data(elem, 'customMismatchedRule')) && message[customRule] && typeof message[customRule] == 'string'){
				message = message[customRule];
			} else if(!validity.valid){
				$.each(validity, function(name, prop){
					if(prop && name != 'valid' && message[name]){
						message = message[name];
						return false;
					}
				});
				if(typeof message == 'object'){
					if(validity.typeMismatch && message.badInput){
						message = message.badInput;
					}
					if(validity.badInput && message.typeMismatch){
						message = message.typeMismatch;
					}
				}
			}
		}
		
		if(typeof message == 'object'){
			message = message.defaultMessage;
		}
		if(webshims.replaceValidationplaceholder){
			message = webshims.replaceValidationplaceholder(elem, message);
		}
		return message || '';
	};

	webshims.refreshCustomValidityRules = $.noop;
	
	$.fn.getErrorMessage = function(key){
		var message = '';
		var elem = this[0];
		if(elem){
			message = webshims.getContentValidationMessage(elem, false, key) || $.prop(elem, 'customValidationMessage') || ($.prop(elem, 'validationMessage') || '');
		}
		return message;
	};

	$.event.special.valuevalidation = {
		setup: function(){
			webshims.error('valuevalidation was renamed to validatevalue!');
		}
	};


	$.event.special.validatevalue = {
		setup: function(){
			var data = $(this).data() || $.data(this, {});
			if(!('validatevalue' in data)){
				data.validatevalue = true;
			}
		}
	};


	$(document).on('focusin.lazyloadvalidation mousedown.lazyloadvalidation touchstart.lazyloadvalidation', function(e){
		if('form' in e.target){
			lazyLoad();
		}
	});

	webshims.ready('WINDOWLOAD', lazyLoad);

	if(modules['form-number-date-ui'].loaded && !options.customMessages && (modules['form-number-date-api'].test() || (support.inputtypes.range && support.inputtypes.color))){
		webshims.isReady('form-number-date-ui', true);
	}

	webshims.ready('DOM', function(){
		if(document.querySelector('.ws-custom-file')){
			webshims.reTest(['form-validation']);
		}
	});

	if(options.addValidators && options.fastValidators){
		webshims.reTest(['form-validators', 'form-validation']);
	}

	if(document.readyState == 'complete'){
		webshims.isReady('WINDOWLOAD', true);
	}
});
;webshims.register('form-datalist', function($, webshims, window, document, undefined, options){
	"use strict";
	var lazyLoad = function(name){
		if(!name || typeof name != 'string'){
			name = 'DOM';
		}
		if(!lazyLoad[name+'Loaded']){
			lazyLoad[name+'Loaded'] = true;
			webshims.ready(name, function(){
				webshims.loader.loadList(['form-datalist-lazy']);
			});
		}
	};
	var noDatalistSupport = {
		submit: 1,
		button: 1,
		reset: 1, 
		hidden: 1,
		
		range: 1,
		date: 1,
		month: 1
	};
	if(webshims.modules["form-number-date-ui"].loaded){
		$.extend(noDatalistSupport, {
			number: 1,
			time: 1
		});
	}
	

	/*
	 * implement propType "element" currently only used for list-attribute (will be moved to dom-extend, if needed)
	 */
	webshims.propTypes.element = function(descs, name){
		webshims.createPropDefault(descs, 'attr');
		if(descs.prop){return;}
		descs.prop = {
			get: function(){
				var elem = $.attr(this, name);
				if(elem){
					elem = document.getElementById(elem);
					if(elem && descs.propNodeName && !$.nodeName(elem, descs.propNodeName)){
						elem = null;
					}
				}
				return elem || null;
			},
			writeable: false
		};
	};
	
	
	/*
	 * Implements datalist element and list attribute
	 */
	
	(function(){
		var formsCFG = webshims.cfg.forms;
		var listSupport = webshims.support.datalist;
		if(listSupport && !formsCFG.customDatalist){return;}
		
			var initializeDatalist =  function(){
			
			var updateDatlistAndOptions = function(){
				var id;
				if(!$.data(this, 'datalistWidgetData') && (id = $.prop(this, 'id'))){
					$('input[list="'+ id +'"], input[data-wslist="'+ id +'"]').eq(0).attr('list', id);
				} else {
					$(this).triggerHandler('updateDatalist');
				}
			};
				
			var inputListProto = {
				//override autocomplete
				autocomplete: {
					attr: {
						get: function(){
							var elem = this;
							var data = $.data(elem, 'datalistWidget');
							if(data){
								return data._autocomplete;
							}
							return ('autocomplete' in elem) ? elem.autocomplete : elem.getAttribute('autocomplete');
						},
						set: function(value){
							var elem = this;
							var data = $.data(elem, 'datalistWidget');
							if(data){
								data._autocomplete = value;
								if(value == 'off'){
									data.hideList();
								}
							} else {
								if('autocomplete' in elem){
									elem.autocomplete = value;
								} else {
									elem.setAttribute('autocomplete', value);
								}
							}
						}
					}
				}
			};
			
			if(listSupport){
				//options only return options, if option-elements are rooted: but this makes this part of HTML5 less backwards compatible
				if(!($('<datalist><select><option></option></select></datalist>').prop('options') || []).length ){
					webshims.defineNodeNameProperty('datalist', 'options', {
						prop: {
							writeable: false,
							get: function(){
								var options = this.options || [];
								if(!options.length){
									var elem = this;
									var select = $('select', elem);
									if(select[0] && select[0].options && select[0].options.length){
										options = select[0].options;
									}
								}
								return options;
							}
						}
					});
				}
				inputListProto.list = {
					attr: {
						get: function(){
							var val = webshims.contentAttr(this, 'list');
							if(val != null){
								$.data(this, 'datalistListAttr', val);
								if(!noDatalistSupport[$.prop(this, 'type')] && !noDatalistSupport[$.attr(this, 'type')]){
									this.removeAttribute('list');
								}
							} else {
								val = $.data(this, 'datalistListAttr');
							}
							
							return (val == null) ? undefined : val;
						},
						set: function(value){
							var elem = this;
							$.data(elem, 'datalistListAttr', value);
							if (!noDatalistSupport[$.prop(this, 'type')] && !noDatalistSupport[$.attr(this, 'type')]) {
								webshims.objectCreate(shadowListProto, undefined, {
									input: elem,
									id: value,
									datalist: $.prop(elem, 'list')
								});
								elem.setAttribute('data-wslist', value);
							} else {
								elem.setAttribute('list', value);
							}
							$(elem).triggerHandler('listdatalistchange');
						}
					},
					initAttr: true,
					reflect: true,
					propType: 'element',
					propNodeName: 'datalist'
				};
			} else {
				webshims.defineNodeNameProperties('input', {
					list: {
						attr: {
							get: function(){
								var val = webshims.contentAttr(this, 'list');
								return (val == null) ? undefined : val;
							},
							set: function(value){
								var elem = this;
								webshims.contentAttr(elem, 'list', value);
								webshims.objectCreate(options.shadowListProto, undefined, {input: elem, id: value, datalist: $.prop(elem, 'list')});
								$(elem).triggerHandler('listdatalistchange');
							}
						},
						initAttr: true,
						reflect: true,
						propType: 'element',
						propNodeName: 'datalist'
					}
				});
			}
			
			webshims.defineNodeNameProperties('input', inputListProto);
			
			webshims.addReady(function(context, contextElem){
				contextElem
					.filter('datalist > select, datalist, datalist > option, datalist > select > option')
					.closest('datalist')
					.each(updateDatlistAndOptions)
				;
			});
		};
		
		
		/*
		 * ShadowList
		 */
		
		var shadowListProto = {
			_create: function(opts){
				
				if(noDatalistSupport[$.prop(opts.input, 'type')] || noDatalistSupport[$.attr(opts.input, 'type')]){return;}
				var datalist = opts.datalist;
				var data = $.data(opts.input, 'datalistWidget');
				var that = this;
				if(datalist && data && data.datalist !== datalist){
					data.datalist = datalist;
					data.id = opts.id;
					
					
					$(data.datalist)
						.off('updateDatalist.datalistWidget')
						.on('updateDatalist.datalistWidget', $.proxy(data, '_resetListCached'))
					;
					
					data._resetListCached();
					return;
				} else if(!datalist){
					if(data){
						data.destroy();
					}
					return;
				} else if(data && data.datalist === datalist){
					return;
				}
				
				
				
				this.datalist = datalist;
				this.id = opts.id;
				this.hasViewableData = true;
				this._autocomplete = $.attr(opts.input, 'autocomplete');
				$.data(opts.input, 'datalistWidget', this);
				$.data(datalist, 'datalistWidgetData', this);
				
				lazyLoad('WINDOWLOAD');
				
				if(webshims.isReady('form-datalist-lazy')){
					if(window.QUnit){
						that._lazyCreate(opts);
					} else {
						setTimeout(function(){
							that._lazyCreate(opts);
						}, 9);
					}
				} else {
					$(opts.input).one('focus', lazyLoad);
					webshims.ready('form-datalist-lazy', function(){
						if(!that._destroyed){
							that._lazyCreate(opts);
						}
					});
				}
			},
			destroy: function(e){
				var input;
				var autocomplete = $.attr(this.input, 'autocomplete');
				$(this.input)
					.off('.datalistWidget')
					.removeData('datalistWidget')
				;
				this.shadowList.remove();
				$(document).off('.datalist'+this.id);
				$(window).off('.datalist'+this.id);
				if(this.input.form && this.input.id){
					$(this.input.form).off('submit.datalistWidget'+this.input.id);
				}
				this.input.removeAttribute('aria-haspopup');
				if(autocomplete === undefined){
					this.input.removeAttribute('autocomplete');
				} else {
					$(this.input).attr('autocomplete', autocomplete);
				}
				if(e && e.type == 'beforeunload'){
					input = this.input;
					setTimeout(function(){
						$.attr(input, 'list', $.attr(input, 'list'));
					}, 9);
				}
				this._destroyed = true;
			}
		};
		
		webshims.loader.addModule('form-datalist-lazy', {
			noAutoCallback: true,
			options: $.extend(options, {shadowListProto: shadowListProto})
		});
		if(!options.list){
			options.list = {};
		}
		//init datalist update
		initializeDatalist();
	})();
	
});
