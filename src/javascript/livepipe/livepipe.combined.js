/**
 * @author Ryan Johnson <http://syntacticx.com/>
 * @copyright 2008 PersonalGrid Corporation <http://personalgrid.com/>
 * @package LivePipe UI
 * @license MIT
 * @url http://livepipe.net/core
 * @require prototype.js
 */

if(typeof(Control) == 'undefined')
    Control = {};
    
var $proc = function(proc){
    return typeof(proc) == 'function' ? proc : function(){return proc};
};

var $value = function(value){
    return typeof(value) == 'function' ? value() : value;
};

Object.Event = {
    extend: function(object){
        object._objectEventSetup = function(event_name){
            this._observers = this._observers || {};
            this._observers[event_name] = this._observers[event_name] || [];
        };
        object.observe = function(event_name,observer){
            if(typeof(event_name) == 'string' && typeof(observer) != 'undefined'){
                this._objectEventSetup(event_name);
                if(!this._observers[event_name].include(observer))
                    this._observers[event_name].push(observer);
            }else
                for(var e in event_name)
                    this.observe(e,event_name[e]);
        };
        object.stopObserving = function(event_name,observer){
            this._objectEventSetup(event_name);
            if(event_name && observer)
                this._observers[event_name] = this._observers[event_name].without(observer);
            else if(event_name)
                this._observers[event_name] = [];
            else
                this._observers = {};
        };
        object.observeOnce = function(event_name,outer_observer){
            var inner_observer = function(){
                outer_observer.apply(this,arguments);
                this.stopObserving(event_name,inner_observer);
            }.bind(this);
            this._objectEventSetup(event_name);
            this._observers[event_name].push(inner_observer);
        };
        object.notify = function(event_name){
            this._objectEventSetup(event_name);
            var collected_return_values = [];
            var args = $A(arguments).slice(1);
            try{
                for(var i = 0; i < this._observers[event_name].length; ++i)
                    collected_return_values.push(this._observers[event_name][i].apply(this._observers[event_name][i],args) || null);
            }catch(e){
                if(e == $break)
                    return false;
                else
                    throw e;
            }
            return collected_return_values;
        };
        if(object.prototype){
            object.prototype._objectEventSetup = object._objectEventSetup;
            object.prototype.observe = object.observe;
            object.prototype.stopObserving = object.stopObserving;
            object.prototype.observeOnce = object.observeOnce;
            object.prototype.notify = function(event_name){
                if(object.notify){
                    var args = $A(arguments).slice(1);
                    args.unshift(this);
                    args.unshift(event_name);
                    object.notify.apply(object,args);
                }
                this._objectEventSetup(event_name);
                var args = $A(arguments).slice(1);
                var collected_return_values = [];
                try{
                    if(this.options && this.options[event_name] && typeof(this.options[event_name]) == 'function')
                        collected_return_values.push(this.options[event_name].apply(this,args) || null);
                    for(var i = 0; i < this._observers[event_name].length; ++i)
                        collected_return_values.push(this._observers[event_name][i].apply(this._observers[event_name][i],args) || null);
                }catch(e){
                    if(e == $break)
                        return false;
                    else
                        throw e;
                }
                return collected_return_values;
            };
        }
    }
};

/* Begin Core Extensions */

//Element.observeOnce
Element.addMethods({
    observeOnce: function(element,event_name,outer_callback){
        var inner_callback = function(){
            outer_callback.apply(this,arguments);
            Element.stopObserving(element,event_name,inner_callback);
        };
        Element.observe(element,event_name,inner_callback);
    }
});

//mouse:wheel
(function(){
    function wheel(event){
        var delta, element, custom_event;
        // normalize the delta
        if (event.wheelDelta) { // IE & Opera
            delta = event.wheelDelta / 120;
        } else if (event.detail) { // W3C
            delta =- event.detail / 3;
        }
        if (!delta) { return; }
        element = Event.extend(event).target;
        element = Element.extend(element.nodeType === Node.TEXT_NODE ? element.parentNode : element);
        custom_event = element.fire('mouse:wheel',{ delta: delta });
        if (custom_event.stopped) {
            Event.stop(event);
            return false;
        }
    }
    document.observe('mousewheel',wheel);
    document.observe('DOMMouseScroll',wheel);
})();

/* End Core Extensions */

//from PrototypeUI
var IframeShim = Class.create({
    initialize: function() {
        this.element = new Element('iframe',{
            style: 'position:absolute;filter:progid:DXImageTransform.Microsoft.Alpha(opacity=0);display:none',
            src: 'javascript:void(0);',
            frameborder: 0 
        });
        $(document.body).insert(this.element);
    },
    hide: function() {
        this.element.hide();
        return this;
    },
    show: function() {
        this.element.show();
        return this;
    },
    positionUnder: function(element) {
        var element = $(element);
        var offset = element.cumulativeOffset();
        var dimensions = element.getDimensions();
        this.element.setStyle({
            left: offset[0] + 'px',
            top: offset[1] + 'px',
            width: dimensions.width + 'px',
            height: dimensions.height + 'px',
            zIndex: element.getStyle('zIndex') - 1
        }).show();
        return this;
    },
    setBounds: function(bounds) {
        for(prop in bounds)
            bounds[prop] += 'px';
        this.element.setStyle(bounds);
        return this;
    },
    destroy: function() {
        if(this.element)
            this.element.remove();
        return this;
    }
});/**
 * @author Ryan Johnson <http://syntacticx.com/>
 * @copyright 2008 PersonalGrid Corporation <http://personalgrid.com/>
 * @package LivePipe UI
 * @license MIT
 * @url http://livepipe.net/controls/hotkey/
 * @attribution http://www.quirksmode.org/js/cookies.html
 */

/*global document, Prototype, $A */

if(typeof(Prototype) == "undefined") {
  throw "Cookie requires Prototype to be loaded."; }
if(typeof(Object.Event) == "undefined") {
  throw "Cookie requires Object.Event to be loaded."; }

var Cookie = {
  build: function() {
    return $A(arguments).compact().join("; ");
  },
  secondsFromNow: function(seconds) {
    var d = new Date();
    d.setTime(d.getTime() + (seconds * 1000));
    return d.toGMTString();
  },
  set: function(name,value,seconds){
    Cookie.notify('set',name,value);
    var expiry = seconds ? 'expires=' + Cookie.secondsFromNow(seconds) : null;
    document.cookie = Cookie.build(name + "=" + value, expiry, "path=/");
  },
  get: function(name){
    Cookie.notify('get',name);
    var valueMatch = new RegExp(name + "=([^;]+)").exec(document.cookie);
    return valueMatch ? valueMatch[1] : null;
  },
  unset: function(name){
    Cookie.notify('unset',name);
    Cookie.set(name,'',-1);
  }
};
Object.Event.extend(Cookie);
/**
 * @author Ryan Johnson <http://syntacticx.com/>
 * @copyright 2008 PersonalGrid Corporation <http://personalgrid.com/>
 * @package LivePipe UI
 * @license MIT
 * @url http://livepipe.net/control/contextmenu
 * @require prototype.js, livepipe.js
 */

/*global window, document, Prototype, Class, Event, $, $A, $R, Control, $value */

if(typeof(Prototype) == "undefined") {
    throw "Control.ContextMenu requires Prototype to be loaded."; }
if(typeof(Object.Event) == "undefined") {
    throw "Control.ContextMenu requires Object.Event to be loaded."; }

Control.ContextMenu = Class.create({
    initialize: function(container,options){
        Control.ContextMenu.load();
        this.options = Object.extend({
            leftClick: false,
            disableOnShiftKey: true,
            disableOnAltKey: true,
            selectedClassName: 'selected',
            activatedClassName: 'activated',
            animation: true,
            animationCycles: 2,
            animationLength: 300,
            delayCallback: true
        },options || {});
        this.activated = false;
        this.items = this.options.items || [];
        this.container = $(container);
        this.container.observe(this.options.leftClick ? 'click' : (Prototype.Browser.Opera ? 'click' : 'contextmenu'),function(event){
            if(!Control.ContextMenu.enabled || Prototype.Browser.Opera && !event.ctrlKey) {
                return; }
            this.open(event);
        }.bindAsEventListener(this));
    },
    open: function(event){
        if(Control.ContextMenu.current && !Control.ContextMenu.current.close()) {
            return; }
        if(this.notify('beforeOpen',event) === false) {
            return false; }
        this.buildMenu();
        if(this.items.length === 0){
            this.close(event);
            return false;
        }
        this.clicked = Event.element(event);
        Control.ContextMenu.current = this;
        Control.ContextMenu.positionContainer(event);
        Control.ContextMenu.container.show();
        if(this.notify('afterOpen',event) === false) {
            return false; }
        event.stop();
        return true;
    },
    close: function(event){
        if(event) {
            event.stop(); }
        if(this.notify('beforeClose') === false) {
            return false; }
        Control.ContextMenu.current = false;
        this.activated = false;
        Control.ContextMenu.container.removeClassName(this.options.activatedClassName);
        Control.ContextMenu.container.select('li').invoke('stopObserving');
        Control.ContextMenu.container.hide();
        Control.ContextMenu.container.update('');
        if(this.notify('afterClose') === false) {
            return false; }
        return true;
    },
    buildMenu: function(){
        var list = document.createElement('ul');
        Control.ContextMenu.container.appendChild(list);
        this.items.each(function(item){
            if(!(!item.condition || item.condition && item.condition() !== false)) {
                return; }
            var item_container = $(document.createElement('li'));
            item_container.update($value(item.label));
            list.appendChild(item_container);
            item_container[$value(item.enabled) ? 'removeClassName' : 'addClassName']('disabled');
            item_container.observe('mousedown',function(event,item){
                if(!$value(item.enabled)) {
                    return event.stop(); }
                this.activated = $value(item.label);
            }.bindAsEventListener(this,item));
            item_container.observe('click',this.selectMenuItem.bindAsEventListener(this,item,item_container));
            item_container.observe('contextmenu',this.selectMenuItem.bindAsEventListener(this,item,item_container));
        }.bind(this));
    },
    addItem: function(params){
        if (!('enabled' in params)) { params.enabled = true; }
        this.items.push(params);
        return this;
    },
    destroy: function(){
        this.container.stopObserving(Prototype.Browser.Opera || this.options.leftClick ? 'click' : 'contextmenu');
        this.items = [];
    },
    selectMenuItem: function(event,item,item_container){
        if(!$value(item.enabled)) {
            return event.stop(); }
        if(!this.activated || this.activated == $value(item.label)){
            if(this.options.animation){
                Control.ContextMenu.container.addClassName(this.options.activatedClassName);
                $A($R(0,this.options.animationCycles * 2)).each(function(i){
                    window.setTimeout(function(){
                        item_container.toggleClassName(this.options.selectedClassName);
                    }.bind(this),i * parseInt(this.options.animationLength / (this.options.animationCycles * 2), 10));
                }.bind(this));
                window.setTimeout(function(){
                    if(this.close() && this.options.delayCallback) {
                        item.callback(this.clicked); }
                }.bind(this),this.options.animationLength);
                if(!this.options.delayCallback) {
                    item.callback(this.clicked); }
            }else if(this.close()) {
                item.callback(this.clicked); }
        }
        event.stop();
        return false;
    }
});
Object.extend(Control.ContextMenu,{
    loaded: false,
    capture_all: false,
    menus: [],
    current: false,
    enabled: false,
    offset: 4,
    load: function(capture_all){
        if(Control.ContextMenu.loaded) {
            return; }
        Control.ContextMenu.loaded = true;
        if(typeof(capture_all) == 'undefined') {
            capture_all = false; }
        Control.ContextMenu.capture_all = capture_all;
        Control.ContextMenu.container = $(document.createElement('div'));
        Control.ContextMenu.container.id = 'control_contextmenu';
        Control.ContextMenu.container.style.position = 'absolute';
        Control.ContextMenu.container.style.zIndex = 99999;
        Control.ContextMenu.container.hide();
        document.body.appendChild(Control.ContextMenu.container);
        Control.ContextMenu.enable();
    },
    enable: function(){
        Control.ContextMenu.enabled = true;
        Event.observe(document.body,'click',Control.ContextMenu.onClick);
        if(Control.ContextMenu.capture_all) {
            Event.observe(document.body,'contextmenu',Control.ContextMenu.onContextMenu); }
    },
    disable: function(){
        Event.stopObserving(document.body,'click',Control.ContextMenu.onClick);
        if(Control.ContextMenu.capture_all) {
            Event.stopObserving(document.body,'contextmenu',Control.ContextMenu.onContextMenu);    }
    },
    onContextMenu: function(event){
        event.stop();
        return false;
    },
    onClick: function(){
        if(Control.ContextMenu.current) {
            Control.ContextMenu.current.close(); }
    },
    positionContainer: function(event){
        var dimensions = Control.ContextMenu.container.getDimensions();
        var top = Event.pointerY(event);
        var left = Event.pointerX(event);
        var bottom = dimensions.height + top;
        var right = dimensions.width + left;
        var viewport_dimensions = document.viewport.getDimensions();
        var viewport_scroll_offsets = document.viewport.getScrollOffsets();
        if(bottom > viewport_dimensions.height + viewport_scroll_offsets.top) {
            top -= bottom - ((viewport_dimensions.height  + viewport_scroll_offsets.top) - Control.ContextMenu.offset); }
        if(right > viewport_dimensions.width + viewport_scroll_offsets.left) {
            left -= right - ((viewport_dimensions.width + viewport_scroll_offsets.left) - Control.ContextMenu.offset); }
        Control.ContextMenu.container.setStyle({
            top: top + 'px',
            left: left + 'px'
        });
    }
});
Object.Event.extend(Control.ContextMenu);/**
 * @author Ryan Johnson <http://syntacticx.com/>
 * @copyright 2008 PersonalGrid Corporation <http://personalgrid.com/>
 * @package LivePipe UI
 * @license MIT
 * @url http://livepipe.net/extra/event_behavior
 * @require prototype.js, livepipe.js
 * @attribution http://www.adamlogic.com/2007/03/20/3_metaprogramming-javascript-presentation
 */

/*global Prototype, Class, Event, Try, $, $A, $H */

if(typeof(Prototype) == "undefined") {
    throw "Event.Behavior requires Prototype to be loaded."; }
if(typeof(Object.Event) == "undefined") {
    throw "Event.Behavior requires Object.Event to be loaded."; }
    
Event.Behavior = {
    addVerbs: function(verbs){
        var v;
        for (var name in verbs) { if (verbs.hasOwnProperty(name)) {
            v = new Event.Behavior.Verb(verbs[name]);
            Event.Behavior.Verbs[name] = v;
            Event.Behavior[name.underscore()] = Event.Behavior[name] = v.getCallbackForStack.bind(v);
        }}
    },
    addEvents: function(events){
        $H(events).each(function(event_type){
            Event.Behavior.Adjective.prototype[event_type.key.underscore()] = Event.Behavior.Adjective.prototype[event_type.key] = function(){
                this.nextConditionType = 'and';
                this.events.push(event_type.value);
                this.attachObserver(false);
                return this;
            };
        });
    },
    invokeElementMethod: function(element,action,args){
        if(typeof(element) == 'function'){
            return $A(element()).each(function(e){
                if(typeof(args[0]) == 'function'){
                    return $A(args[0]).each(function(a){
                        return $(e)[action].apply($(e),(a ? [a] : []));
                    });
                }else {
                    return $(e)[action].apply($(e),args || []); }
            });
        }else {
            return $(element)[action].apply($(element),args || []); }
    }
};

Event.Behavior.Verbs = $H({});

Event.Behavior.Verb = Class.create();
Object.extend(Event.Behavior.Verb.prototype,{
    originalAction: false,
    execute: false,
    executeOpposite: false,
    target: false,
    initialize: function(action){
        this.originalAction = action;
        this.execute = function(action,target,argument){
            return (argument) ? action(target,argument) : action(target);
        }.bind(this,action);
    },
    setOpposite: function(opposite_verb){
        var opposite_action = opposite_verb.originalAction;
        this.executeOpposite = function(opposite_action,target,argument){
            return (argument) ? opposite_action(target,argument) : 
                opposite_action(target);
        }.bind(this,opposite_action);
    },
    getCallbackForStack: function(argument){
        return new Event.Behavior.Noun(this,argument);
    }
});

Event.Behavior.addVerbs({
    call: function(callback){
        callback();
    },
    show: function(element){
        return Event.Behavior.invokeElementMethod(element,'show');
    },
    hide: function(element){
        return Event.Behavior.invokeElementMethod(element,'hide');
    },
    remove: function(element){
        return Event.Behavior.invokeElementMethod(element,'remove');
    },
    setStyle: function(element,styles){
        return Event.Behavior.invokeElementMethod(element,'setStyle',[(typeof(styles) == 'function' ? styles() : styles)]);
    },
    addClassName: function(element,class_name){
        return Event.Behavior.invokeElementMethod(element,'addClassName',[(typeof(class_name) == 'function' ? class_name() : class_name)]);
    },
    removeClassName: function(element,class_name){
        return Event.Behavior.invokeElementMethod(element,'removeClassName',[(typeof(class_name) == 'function' ? class_name() : class_name)]);
    },
    setClassName: function(element,class_name){
        var c = (typeof(class_name) == 'function') ? class_name() : class_name;
        if(typeof(element) == 'function'){
            return $A(element()).each(function(e){
                $(e).className = c;
            });
        }else {
            c = $(element).className;
            return c;
        }
    },
    update: function(content,element){
        return Event.Behavior.invokeElementMethod(element,'update',[(typeof(content) == 'function' ? content() : content)]);
    },
    replace: function(content,element){
        return Event.Behavior.invokeElementMethod(element,'replace',[(typeof(content) == 'function' ? content() : content)]);
    }
});
Event.Behavior.Verbs.show.setOpposite(Event.Behavior.Verbs.hide);
Event.Behavior.Verbs.hide.setOpposite(Event.Behavior.Verbs.show);
Event.Behavior.Verbs.addClassName.setOpposite(Event.Behavior.Verbs.removeClassName);
Event.Behavior.Verbs.removeClassName.setOpposite(Event.Behavior.Verbs.addClassName);

Event.Behavior.Noun = Class.create();
Object.extend(Event.Behavior.Noun.prototype,{
    verbs: false,
    verb: false,
    argument: false,
    subject: false,
    target: false,
    initialize: function(verb,argument){
        //this.verbs = $A([]);
        this.verb = verb;
        this.argument = argument;
    },
    execute: function(){
        return (this.target) ? this.verb.execute(this.target,this.argument) : 
            this.verb.execute(this.argument);
    },
    executeOpposite: function(){
        return (this.target) ? 
            this.verb.executeOpposite(this.target,this.argument) : 
            this.verb.executeOpposite(this.argument);
    },
    when: function(subject){
        this.subject = subject;
        return new Event.Behavior.Adjective(this);
    },
    getValue: function(){
        return Try.these(
            function(){return $(this.subject).getValue();}.bind(this),
            function(){return $(this.subject).options[$(this.subject).options.selectedIndex].value;}.bind(this),
            function(){return $(this.subject).value;}.bind(this),
            function(){return $(this.subject).innerHTML;}.bind(this)
        );
    },
    containsValue: function(match){
        var value = this.getValue();
        if(typeof(match) == 'function'){
            return $A(match()).include(value);
        }else {
            return value.match(match); }
    },
    setTarget: function(target){
        this.target = target;
        return this;
    },
    and: function(){

    }
});
Event.Behavior.Noun.prototype._with = Event.Behavior.Noun.prototype.setTarget;
Event.Behavior.Noun.prototype.on = Event.Behavior.Noun.prototype.setTarget;
Event.Behavior.Noun.prototype.of = Event.Behavior.Noun.prototype.setTarget;
Event.Behavior.Noun.prototype.to = Event.Behavior.Noun.prototype.setTarget;
Event.Behavior.Noun.prototype.from = Event.Behavior.Noun.prototype.setTarget;

Event.Behavior.Adjective = Class.create();
Object.extend(Event.Behavior.Adjective.prototype,{
    noun: false,
    lastConditionName: '',
    nextConditionType: 'and',
    conditions: $A([]),
    events: $A([]),
    attached: false,
    initialize: function(noun){
        this.conditions = $A([]);
        this.events = $A([]);
        this.noun = noun;
    },
    attachObserver: function(execute_on_load){
        if(this.attached){
            //this may call things multiple times, but is the only way to gaurentee correct state on startup
            if(execute_on_load) {
                this.execute(); }
            return;
        }
        this.attached = true;
        if(typeof(this.noun.subject) == 'function'){
            $A(this.noun.subject()).each(function(subject){
                (this.events.length > 0 ? this.events : $A(['change'])).each(function(event_name){
                    (subject.observe ? subject : $(subject)).observe(event_name,function(){
                        this.execute();
                    }.bind(this));
                }.bind(this));
            }.bind(this));
        }else{
            (this.events.length > 0 ? this.events : $A(['change'])).each(function(event_name){
                $(this.noun.subject).observe(event_name,function(){
                    this.execute();
                }.bind(this));
            }.bind(this));
        }
        if(execute_on_load) { this.execute(); }
    },
    execute: function(){
        if(this.match()) { return this.noun.execute(); }
        else if(this.noun.verb.executeOpposite) { this.noun.executeOpposite(); }
    },
    attachCondition: function(callback){
        this.conditions.push([this.nextConditionType,callback.bind(this)]);
    },
    match: function(){
        if(this.conditions.length === 0) { return true; }
        else {
            return this.conditions.inject(false, function (bool,condition) {
                return (condition[0] === 'or') ? 
                       (bool && condition[1]()) : (bool || condition[1]());
            });
        }
    },
    //conditions
    is: function(item){
        this.lastConditionName = 'is';
        this.attachCondition(function(item){
            return (typeof(item) == 'function' ? item() : item) == this.noun.getValue();
        }.bind(this,item));
        this.attachObserver(true);
        return this;
    },
    isNot: function(item){
        this.lastConditionName = 'isNot';
        this.attachCondition(function(item){
            return (typeof(item) == 'function' ? item() : item) != this.noun.getValue();
        }.bind(this,item));
        this.attachObserver(true);
        return this;
    },
    contains: function(item){
        this.lastConditionName = 'contains';
        this.attachCondition(function(item){
            return this.noun.containsValue(item);
        }.bind(this,item));
        this.attachObserver(true);
        return this;
    },
    within: function(item){
        this.lastConditionName = 'within';
        this.attachCondition(function(item){
            
        }.bind(this,item));
        this.attachObserver(true);
        return this;
    },
    //events
    change: function(){
        this.nextConditionType = 'and';
        this.attachObserver(true);
        return this;
    },
    and: function(condition){
        this.attached = false;
        this.nextConditionType = 'and';
        if(condition) { this[this.lastConditionName](condition); }
        return this;
    },
    or: function(condition){
        this.attached = false;
        this.nextConditionType = 'or';
        if(condition) { this[this.lastConditionName](condition); }
        return this;
    }
});

Event.Behavior.addEvents({
    losesFocus: 'blur',
    gainsFocus: 'focus',
    isClicked: 'click',
    isDoubleClicked: 'dblclick',
    keyPressed: 'keypress'
});

Event.Behavior.Adjective.prototype.is_not = Event.Behavior.Adjective.prototype.isNot;
Event.Behavior.Adjective.prototype.include = Event.Behavior.Adjective.prototype.contains;
Event.Behavior.Adjective.prototype.includes = Event.Behavior.Adjective.prototype.contains;
Event.Behavior.Adjective.prototype.are = Event.Behavior.Adjective.prototype.is;
Event.Behavior.Adjective.prototype.areNot = Event.Behavior.Adjective.prototype.isNot;
Event.Behavior.Adjective.prototype.are_not = Event.Behavior.Adjective.prototype.isNot;
Event.Behavior.Adjective.prototype.changes = Event.Behavior.Adjective.prototype.change;
/**
 * @author Ryan Johnson <http://syntacticx.com/>
 * @copyright 2008 PersonalGrid Corporation <http://personalgrid.com/>
 * @package LivePipe UI
 * @license MIT
 * @url http://livepipe.net/extra/hotkey
 * @require prototype.js, livepipe.js
 */

/*global document, Prototype, Class, Event, $ */

if(typeof(Prototype) == "undefined") {
    throw "HotKey requires Prototype to be loaded."; }
if(typeof(Object.Event) == "undefined") {
    throw "HotKey requires Object.Event to be loaded."; }

var HotKey = Class.create({
    initialize: function(letter,callback,options){
        letter = letter.toUpperCase();
        HotKey.hotkeys.push(this);
        this.options = Object.extend({
            element: false,
            shiftKey: false,
            altKey: false,
            ctrlKey: true,
            bubbleEvent : true,
            fireOnce : false // Keep repeating event while key is pressed?
        },options || {});
        this.letter = letter;

        // All custom hotkey events should stop after their custom actions.
        this.callback = function (event) {
            if (!(this.options.fireOnce && this.fired) && Object.isFunction(callback)) { 
                callback(event); 
            }
            if (!this.options.bubbleEvent) { event.stop(); }
            this.fired = true;
        };

        this.element = $(this.options.element || document);
        this.handler = function(event){
            if(!event || (
                (Event['KEY_' + this.letter] || this.letter.charCodeAt(0)) == event.keyCode &&
                ((!this.options.shiftKey || (this.options.shiftKey && event.shiftKey)) &&
                    (!this.options.altKey || (this.options.altKey && event.altKey)) &&
                    (!this.options.ctrlKey || (this.options.ctrlKey && event.ctrlKey))
                )
            )){
                if(this.notify('beforeCallback',event) === false) {
                    return; }
                this.callback(event);
                this.notify('afterCallback',event);
            }
        }.bind(this);
        this.enable();
    },
    trigger: function(){
        this.handler();
    },
    enable: function(){
        this.element.observe('keydown',this.handler);
    },
    disable: function(){
        this.element.stopObserving('keydown',this.handler);
    },
    destroy: function(){
        this.disable();
        HotKey.hotkeys = HotKey.hotkeys.without(this);
    }
});
Object.extend(HotKey,{
    hotkeys: []
});
Object.Event.extend(HotKey);
/**
 * @author Ryan Johnson <http://syntacticx.com/>
 * @copyright 2008 PersonalGrid Corporation <http://personalgrid.com/>
 * @package LivePipe UI
 * @license MIT
 * @url http://livepipe.net/control/progressbar
 * @require prototype.js, livepipe.js
 */

/*global document, Prototype, Ajax, Class, PeriodicalExecuter, $, $A, Control */

if(typeof(Prototype) == "undefined") {
    throw "Control.ProgressBar requires Prototype to be loaded."; }
if(typeof(Object.Event) == "undefined") {
    throw "Control.ProgressBar requires Object.Event to be loaded."; }

Control.ProgressBar = Class.create({
    initialize: function(container,options){
        this.progress = 0;
        this.executer = false;
        this.active = false;
        this.poller = false;
        this.container = $(container);
        this.containerWidth = this.container.getDimensions().width - (parseInt(this.container.getStyle('border-right-width').replace(/px/,''), 10) + parseInt(this.container.getStyle('border-left-width').replace(/px/,''), 10));
        this.progressContainer = $(document.createElement('div'));
        this.progressContainer.setStyle({
            width: this.containerWidth + 'px',
            height: '100%',
            position: 'absolute',
            top: '0px',
            right: '0px'
        });
        this.container.appendChild(this.progressContainer);
        this.options = {
            afterChange: Prototype.emptyFunction,
            interval: 0.25,
            step: 1,
            classNames: {
                active: 'progress_bar_active',
                inactive: 'progress_bar_inactive'
            }
        };
        Object.extend(this.options,options || {});
        this.container.addClassName(this.options.classNames.inactive);
        this.active = false;
    },
    setProgress: function(value){
        this.progress = value;
        this.draw();
        if(this.progress >= 100) {
            this.stop(false); }
        this.notify('afterChange',this.progress,this.active);
    },
    poll: function (url, interval, ajaxOptions){
        // Extend the passed ajax options and success callback with our own.
        ajaxOptions = ajaxOptions || {};
        var success = ajaxOptions.onSuccess || Prototype.emptyFunction;
        ajaxOptions.onSuccess = success.wrap(function (callOriginal, request) {
            this.setProgress(parseInt(request.responseText, 10));
            if(!this.active) { this.poller.stop(); }
            callOriginal(request);
        }).bind(this);

        this.active = true;
        this.poller = new PeriodicalExecuter(function(){
            var a = new Ajax.Request(url, ajaxOptions);
        }.bind(this),interval || 3);
    },
    start: function(){
        this.active = true;
        this.container.removeClassName(this.options.classNames.inactive);
        this.container.addClassName(this.options.classNames.active);
        this.executer = new PeriodicalExecuter(this.step.bind(this,this.options.step),this.options.interval);
    },
    stop: function(reset){
        this.active = false;
        if(this.executer) {
            this.executer.stop(); }
        this.container.removeClassName(this.options.classNames.active);
        this.container.addClassName(this.options.classNames.inactive);
        if (typeof reset  === 'undefined' || reset === true) {
            this.reset(); }
    },
    step: function(amount){
        this.active = true;
        this.setProgress(Math.min(100,this.progress + amount));
    },
    reset: function(){
        this.active = false;
        this.setProgress(0);
    },
    draw: function(){
        this.progressContainer.setStyle({
            width: (this.containerWidth - Math.floor((parseInt(this.progress, 10) / 100) * this.containerWidth)) + 'px'
        });
    },
    notify: function(event_name){
        if(this.options[event_name]) {
            return [this.options[event_name].apply(this.options[event_name],$A(arguments).slice(1))]; }
    }
});
Object.Event.extend(Control.ProgressBar);
/**
 * @author Ryan Johnson <http://syntacticx.com/>
 * @copyright 2008 PersonalGrid Corporation <http://personalgrid.com/>
 * @package LivePipe UI
 * @license MIT
 * @url http://livepipe.net/control/rating
 * @require prototype.js, livepipe.js
 */

/*global document, Prototype, Ajax, Class, Event, $, $A, $F, $R, $break, Control */

if(typeof(Prototype) == "undefined") {
    throw "Control.Rating requires Prototype to be loaded."; }
if(typeof(Object.Event) == "undefined") {
    throw "Control.Rating requires Object.Event to be loaded."; }

Control.Rating = Class.create({
    initialize: function(container,options){
        Control.Rating.instances.push(this);
        this.value = false;
        this.links = [];
        this.container = $(container);
        this.container.update('');
        this.options = {
            min: 1,
            max: 5,
            rated: false,
            input: false,
            reverse: false,
            capture: true,
            multiple: false,
            classNames: {
                off: 'rating_off',
                half: 'rating_half',
                on: 'rating_on',
                selected: 'rating_selected'
            },
            updateUrl: false,
            updateParameterName: 'value',
            updateOptions : {},
            afterChange: Prototype.emptyFunction
        };
        Object.extend(this.options,options || {});
        if(this.options.value){
            this.value = this.options.value;
            delete this.options.value;
        }
        if(this.options.input){
            this.options.input = $(this.options.input);
            this.options.input.observe('change',function(input){
                this.setValueFromInput(input);
            }.bind(this,this.options.input));
            this.setValueFromInput(this.options.input,true);
        }
        var range = $R(this.options.min,this.options.max);
        (this.options.reverse ? $A(range).reverse() : range).each(function(i){
            var link = this.buildLink(i);
            this.container.appendChild(link);
            this.links.push(link);
        }.bind(this));
        this.setValue(this.value || this.options.min - 1,false,true);
    },
    buildLink: function(rating){
        var link = $(document.createElement('a'));
        link.value = rating;
        if(this.options.multiple || (!this.options.rated && !this.options.multiple)){
            link.href = '';
            link.onmouseover = this.mouseOver.bind(this,link);
            link.onmouseout = this.mouseOut.bind(this,link);
            link.onclick = this.click.bindAsEventListener(this,link);
        }else{
            link.style.cursor = 'default';
            link.observe('click',function(event){
                Event.stop(event);
                return false;
            }.bindAsEventListener(this));
        }
        link.addClassName(this.options.classNames.off);
        return link;
    },
    disable: function(){
        this.links.each(function(link){
            link.onmouseover = Prototype.emptyFunction;
            link.onmouseout = Prototype.emptyFunction;
            link.onclick = Prototype.emptyFunction;
            link.observe('click',function(event){
                Event.stop(event);
                return false;
            }.bindAsEventListener(this));
            link.style.cursor = 'default';
        }.bind(this));
    },
    setValueFromInput: function(input,prevent_callbacks){
        this.setValue($F(input),true,prevent_callbacks);
    },
    setValue: function(value,force_selected,prevent_callbacks){
        this.value = value;
        if(this.options.input){
            if(this.options.input.options){
                $A(this.options.input.options).each(function(option,i){
                    if(option.value == this.value){
                        this.options.input.options.selectedIndex = i;
                        throw $break;
                    }
                }.bind(this));
            }else {
                this.options.input.value = this.value; }
        }
        this.render(this.value,force_selected);
        if(!prevent_callbacks){
            if(this.options.updateUrl){
                var params = {}, a;
                params[this.options.updateParameterName] = this.value;
                a = new Ajax.Request(this.options.updateUrl, Object.extend(
                    this.options.updateOptions, { parameters : params }
                ));
            }
            this.notify('afterChange',this.value);
        }
    },
    render: function(rating,force_selected){
        (this.options.reverse ? this.links.reverse() : this.links).each(function(link,i){
            if(link.value <= Math.ceil(rating)){
                link.className = this.options.classNames[link.value <= rating ? 'on' : 'half'];
                if(this.options.rated || force_selected) {
                    link.addClassName(this.options.classNames.selected); }
            }else {
                link.className = this.options.classNames.off; }
        }.bind(this));
    },
    mouseOver: function(link){
        this.render(link.value,true);
    },
    mouseOut: function(link){
        this.render(this.value);
    },
    click: function(event,link){
        this.options.rated = true;
        this.setValue((link.value ? link.value : link),true);
        if(!this.options.multiple) {
            this.disable(); }
        if(this.options.capture){
            Event.stop(event);
            return false;
        }
    }
});
Object.extend(Control.Rating,{
    instances: [],
    findByElementId: function(id){
        return Control.Rating.instances.find(function(instance){
            return (instance.container.id && instance.container.id == id);
        });
    }
});
Object.Event.extend(Control.Rating);

// script.aculo.us Resizables.js

// Copyright(c) 2007 - Orr Siloni, Comet Information Systems http://www.comet.co.il/en/
//
// Resizable.js is freely distributable under the terms of an MIT-style license.
// For details, see the script.aculo.us web site: http://script.aculo.us/

var Resizables = {
	instances: [],
	observers: [],
	
	register: function(resizable) {
		if(this.instances.length == 0) {
			this.eventMouseUp   = this.endResize.bindAsEventListener(this);
			this.eventMouseMove = this.updateResize.bindAsEventListener(this);
			
			Event.observe(document, "mouseup", this.eventMouseUp);
			Event.observe(document, "mousemove", this.eventMouseMove);
		}
		this.instances.push(resizable);
	},
	
	unregister: function(resizable) {
		this.instances = this.instances.reject(function(d) { return d==resizable });
		if(this.instances.length == 0) {
			Event.stopObserving(document, "mouseup", this.eventMouseUp);
			Event.stopObserving(document, "mousemove", this.eventMouseMove);
		}
	},
	
	activate: function(resizable) {
		if(resizable.options.delay) { 
			this._timeout = setTimeout(function() {
				Resizables._timeout = null; 
				Resizables.activeResizable = resizable; 
			}.bind(this), resizable.options.delay); 
		} else {
			this.activeResizable = resizable;
		}
	},
	
	deactivate: function() {
		this.activeResizable = null;
	},
	
	updateResize: function(event) {
		if(!this.activeResizable) return;
		var pointer = [Event.pointerX(event), Event.pointerY(event)];
		// Mozilla-based browsers fire successive mousemove events with
		// the same coordinates, prevent needless redrawing (moz bug?)
		if(this._lastPointer && (this._lastPointer.inspect() == pointer.inspect())) return;
		this._lastPointer = pointer;
		
		this.activeResizable.updateResize(event, pointer);
	},
	
	endResize: function(event) {
		if(this._timeout) { 
		  clearTimeout(this._timeout); 
		  this._timeout = null; 
		}
		if(!this.activeResizable) return;
		this._lastPointer = null;
		this.activeResizable.endResize(event);
		this.activeResizable = null;
	},
	
	addObserver: function(observer) {
		this.observers.push(observer);
		this._cacheObserverCallbacks();
	},
  
	removeObserver: function(element) {  // element instead of observer fixes mem leaks
		this.observers = this.observers.reject( function(o) { return o.element==element });
		this._cacheObserverCallbacks();
	},
	
	notify: function(eventName, resizable, event) {  // 'onStart', 'onEnd', 'onResize'
		if(this[eventName+'Count'] > 0)
			this.observers.each( function(o) {
				if(o[eventName]) o[eventName](eventName, resizable, event);
			});
		if(resizable.options[eventName]) resizable.options[eventName](resizable, event);
	},
	
	_cacheObserverCallbacks: function() {
		['onStart','onEnd','onResize'].each( function(eventName) {
			Resizables[eventName+'Count'] = Resizables.observers.select(
				function(o) { return o[eventName]; }
			).length;
		});
	}
}

var Resizable = Class.create();
Resizable._resizing = {};

Resizable.prototype = {
	initialize: function(element){
		var defaults = {
			handle: false,
			snap: false,  // false, or xy or [x,y] or function(x,y){ return [x,y] }
			delay: 0,
			minHeight: false,
			minwidth: false,
			maxHeight: false,
			maxWidth: false
		}
		
		this.element = $(element);
		
		var options = Object.extend(defaults, arguments[1] || {});
		if(options.handle && typeof options.handle == 'string')
			this.handle = $(options.handle);
		else if(options.handle)
			this.handle = options.handle;
			
		if(!this.handle) this.handle = this.element;
		
		this.options  = options;
		this.dragging = false;
		
		this.eventMouseDown = this.initResize.bindAsEventListener(this);
		Event.observe(this.handle, "mousedown", this.eventMouseDown);
		
		Resizables.register(this);
	},
	
	destroy: function() {
		Event.stopObserving(this.handle, "mousedown", this.eventMouseDown);
	},
	
	currentDelta: function() {
		return([
			parseInt(Element.getStyle(this.element,'width') || '0'),
			parseInt(Element.getStyle(this.element,'height') || '0')]);
	},
	
	initResize: function(event) {
		if(typeof Resizable._resizing[this.element] != 'undefined' &&
			Resizable._resizing[this.element]) return;
		if(Event.isLeftClick(event)) {
			// abort on form elements, fixes a Firefox issue
			var src = Event.element(event);
			if((tag_name = src.tagName.toUpperCase()) && (
				tag_name=='INPUT' || tag_name=='SELECT' || tag_name=='OPTION' ||
				tag_name=='BUTTON' || tag_name=='TEXTAREA')) return;
			
			this.pointer = [Event.pointerX(event), Event.pointerY(event)];
			this.size = [parseInt(this.element.getStyle('width')) || 0, parseInt(this.element.getStyle('height')) || 0];
			
			Resizables.activate(this);
			Event.stop(event);
		}
	},
	
	startResize: function(event) {
		this.resizing = true;
		if(this.options.zindex) {
			this.originalZ = parseInt(Element.getStyle(this.element,'z-index') || 0);
			this.element.style.zIndex = this.options.zindex;
		}
		Resizables.notify('onStart', this, event);
		Resizable._resizing[this.element] = true;
	},
	
	updateResize: function(event, pointer) {
		if(!this.resizing) this.startResize(event);
		
		Resizables.notify('onResize', this, event);
		
		this.draw(pointer);
		if(this.options.change) this.options.change(this);
		
		// fix AppleWebKit rendering
		if(Prototype.Browser.WebKit) window.scrollBy(0,0);
		Event.stop(event);
	},
	
	finishResize: function(event, success) {
		this.resizing = false;
		Resizables.notify('onEnd', this, event);
		if(this.options.zindex) this.element.style.zIndex = this.originalZ;
		Resizable._resizing[this.element] = false;
		Resizables.deactivate(this);
	},
	
	endResize: function(event) {
		if(!this.resizing) return;
		this.finishResize(event, true);
		Event.stop(event);
	},
	
	draw: function(point) {
		var p = [0,1].map(function(i){ 
			return (this.size[i] + point[i] - this.pointer[i]);
		}.bind(this));
		
		if(this.options.snap) {
			if(typeof this.options.snap == 'function') {
				p = this.options.snap(p[0],p[1],this);
			} else {
				if(this.options.snap instanceof Array) {
				p = p.map( function(v, i) {
				return Math.round(v/this.options.snap[i])*this.options.snap[i] }.bind(this))
			} else {
				p = p.map( function(v) {
				return Math.round(v/this.options.snap)*this.options.snap }.bind(this))
			}
		}}
		
		var minWidth = (typeof(this.options.minWidth) == 'function') ? this.options.minWidth(this.element) : this.options.minWidth;
		var maxWidth = (typeof(this.options.maxWidth) == 'function') ? this.options.maxWidth(this.element) : this.options.maxWidth;
		var minHeight = (typeof(this.options.minHeight) == 'function') ? this.options.minHeight(this.element) : this.options.minHeight;
		var maxHeight = (typeof(this.options.maxHeight) == 'function') ? this.options.maxHeight(this.element) : this.options.maxHeight;

		if (minWidth && p[0] <= minWidth) p[0] = minWidth;
		if (maxWidth && p[0] >= maxWidth) p[0] = maxWidth;
		if (minHeight && p[1] <= minHeight) p[1] = minHeight;
		if (maxHeight && p[1] >= maxHeight) p[1] = maxHeight;
		
		var style = this.element.style;
		if((!this.options.constraint) || (this.options.constraint=='horizontal')){
			style.width = p[0] + "px";
		}
		if((!this.options.constraint) || (this.options.constraint=='vertical')){
			style.height = p[1] + "px";
		}
		
		if(style.visibility=="hidden") style.visibility = ""; // fix gecko rendering
	}
};
/**
 * @author Ryan Johnson <http://syntacticx.com/>
 * @copyright 2008 PersonalGrid Corporation <http://personalgrid.com/>
 * @package LivePipe UI
 * @license MIT
 * @url http://livepipe.net/control/scrollbar
 * @require prototype.js, slider.js, livepipe.js
 */

if(typeof(Prototype) == "undefined")
    throw "Control.ScrollBar requires Prototype to be loaded.";
if(typeof(Control.Slider) == "undefined")
    throw "Control.ScrollBar requires Control.Slider to be loaded.";
if(typeof(Object.Event) == "undefined")
    throw "Control.ScrollBar requires Object.Event to be loaded.";

Control.ScrollBar = Class.create({
    initialize: function(container,track,options){
        this.enabled = false;
        this.notificationTimeout = false;
        this.container = $(container);
        this.boundMouseWheelEvent = this.onMouseWheel.bindAsEventListener(this);
        this.boundResizeObserver = this.onWindowResize.bind(this);
        this.track = $(track);
        this.handle = this.track.firstDescendant();
        this.options = Object.extend({
            active_class_name: 'scrolling',
            apply_active_class_name_to: this.container,
            notification_timeout_length: 125,
            handle_minimum_height: 25,
            scroll_to_smoothing: 0.01,
            scroll_to_steps: 15,
            proportional: true,
            slider_options: {}
        },options || {});
        this.slider = new Control.Slider(this.handle,this.track,Object.extend({
            axis: 'vertical',
            onSlide: this.onChange.bind(this),
            onChange: this.onChange.bind(this)
        },this.options.slider_options));
        this.recalculateLayout();
        Event.observe(window,'resize',this.boundResizeObserver);
        this.handle.observe('mousedown',function(){
            if(this.auto_sliding_executer)
                this.auto_sliding_executer.stop();
        }.bind(this));
    },
    destroy: function(){
        Event.stopObserving(window,'resize',this.boundResizeObserver);
    },
    enable: function(){
        this.enabled = true;
        this.container.observe('mouse:wheel',this.boundMouseWheelEvent);
        this.slider.setEnabled();
        this.track.show();
        if(this.options.active_class_name)
            $(this.options.apply_active_class_name_to).addClassName(this.options.active_class_name);
        this.notify('enabled');
    },
    disable: function(){
        this.enabled = false;
        this.container.stopObserving('mouse:wheel',this.boundMouseWheelEvent);
        this.slider.setDisabled();
        this.track.hide();
        if(this.options.active_class_name)
            $(this.options.apply_active_class_name_to).removeClassName(this.options.active_class_name);
        this.notify('disabled');
        this.reset();
    },
    reset: function(){
        this.slider.setValue(0);
    },
    recalculateLayout: function(){
        if(this.container.scrollHeight <= this.container.offsetHeight)
            this.disable();
        else{
            this.enable();
            this.slider.trackLength = this.slider.maximumOffset() - this.slider.minimumOffset();
            if(this.options.proportional){
                this.handle.style.height = Math.max(this.container.offsetHeight * (this.container.offsetHeight / this.container.scrollHeight),this.options.handle_minimum_height) + 'px';
                this.slider.handleLength = this.handle.style.height.replace(/px/,'');
            }
        }
    },
    onWindowResize: function(){
        this.recalculateLayout();
        this.scrollBy(0);
    },
    onMouseWheel: function(event){
        if(this.auto_sliding_executer)
            this.auto_sliding_executer.stop();
        this.slider.setValueBy(-(event.memo.delta / 20)); //put in math to account for the window height
        event.stop();
        return false;
    },
    onChange: function(value){
        this.container.scrollTop = Math.round(value / this.slider.maximum * (this.container.scrollHeight - this.container.offsetHeight));
        if(this.notification_timeout)
            window.clearTimeout(this.notificationTimeout);
        this.notificationTimeout = window.setTimeout(function(){
            this.notify('change',value);
        }.bind(this),this.options.notification_timeout_length);
    },
    getCurrentMaximumDelta: function(){
        return this.slider.maximum * (this.container.scrollHeight - this.container.offsetHeight);
    },
    getDeltaToElement: function(element){
        return this.slider.maximum * ((element.positionedOffset().top + (element.getHeight() / 2)) - (this.container.getHeight() / 2));
    },
    scrollTo: function(y,animate){
        var current_maximum_delta = this.getCurrentMaximumDelta();
        if(y == 'top')
            y = 0;
        else if(y == 'bottom')
            y = current_maximum_delta;
        else if(typeof(y) != "number")
            y = this.getDeltaToElement($(y));
        if(this.enabled){
            y = Math.max(0,Math.min(y,current_maximum_delta));
            if(this.auto_sliding_executer)
                this.auto_sliding_executer.stop();
            var target_value = y / current_maximum_delta;
            var original_slider_value = this.slider.value;
            var delta = (target_value - original_slider_value) * current_maximum_delta;
            if(animate){
                this.auto_sliding_executer = new PeriodicalExecuter(function(){
                    if(Math.round(this.slider.value * 100) / 100 < Math.round(target_value * 100) / 100 || Math.round(this.slider.value * 100) / 100 > Math.round(target_value * 100) / 100){
                        this.scrollBy(delta / this.options.scroll_to_steps);
                    }else{
                        this.auto_sliding_executer.stop();
                        this.auto_sliding_executer = null;
                        if(typeof(animate) == "function")
                            animate();
                    }            
                }.bind(this),this.options.scroll_to_smoothing);
            }else
                this.scrollBy(delta);
        }else if(typeof(animate) == "function")
            animate();
    },
    scrollBy: function(y){
        if(!this.enabled)
            return false;
        this.slider.setValueBy(y / this.getCurrentMaximumDelta());
    }
});
Object.Event.extend(Control.ScrollBar);
/**
 * @author Ryan Johnson <http://syntacticx.com/>
 * @copyright 2008 PersonalGrid Corporation <http://personalgrid.com/>
 * @package LivePipe UI
 * @license MIT
 * @url http://livepipe.net/control/selection
 * @require prototype.js, effects.js, draggable.js, livepipe.js
 */

/*global window, document, Prototype, Element, Event, $, $$, $break, Control, Draggable */

if(typeof(Prototype) == "undefined") {
    throw "Control.Selection requires Prototype to be loaded."; }
if(typeof(Object.Event) == "undefined") {
    throw "Control.Selection requires Object.Event to be loaded."; }

Control.Selection = {
    options: {
        resize_layout_timeout: 125,
        selected: Prototype.emptyFunction,
        deselected: Prototype.emptyFunction,
        change: Prototype.emptyFunction,
        selection_id: 'control_selection',
        selection_style: {
            zIndex: 999,
            cursor: 'default',
            border: '1px dotted #000'
        },
        filter: function(element){
            return true;
        },
        drag_proxy: false,
        drag_proxy_threshold: 1,
        drag_proxy_options: {}
    },
    selectableElements: [],
    elements: [],
    selectableObjects: [],
    objects: [],
    active: false,
    container: false,
    resizeTimeout: false,
    load: function(options){
        Control.Selection.options = Object.extend(Control.Selection.options,options || {});
        Control.Selection.selection_div = $(document.createElement('div'));
        Control.Selection.selection_div.id = Control.Selection.options.selection_id;
        Control.Selection.selection_div.style.display = 'none';
        Control.Selection.selection_div.setStyle(Control.Selection.options.selection_style);
        Control.Selection.border_width = parseInt(Control.Selection.selection_div.getStyle('border-top-width'), 10) * 2;
        Control.Selection.container = Prototype.Browser.IE ? window.container : window;
        $(document.body).insert(Control.Selection.selection_div);
        Control.Selection.enable();
        if(Control.Selection.options.drag_proxy && typeof(Draggable) != 'undefined') {
            Control.Selection.DragProxy.load(); }
        Event.observe(window,'resize',function(){
            if(Control.Selection.resizeTimeout) {
                window.clearTimeout(Control.Selection.resizeTimeout); }
            Control.Selection.resizeTimeout = window.setTimeout(Control.Selection.recalculateLayout,Control.Selection.options.resize_layout_timeout);
        });
        if(Prototype.Browser.IE){
            var body = $$('body').first();
            body.observe('mouseleave',Control.Selection.stop);
            body.observe('mouseup',Control.Selection.stop);
        }
    },
    enable: function(){
        if(Prototype.Browser.IE){
            document.onselectstart = function(){
                return false;
            };
        }
        Event.observe(Control.Selection.container,'mousedown',Control.Selection.start);
        Event.observe(Control.Selection.container,'mouseup',Control.Selection.stop);
    },
    disable: function(){
        if(Prototype.Browser.IE){
            document.onselectstart = function(){
                return true;
            };
        }
        Event.stopObserving(Control.Selection.container,'mousedown',Control.Selection.start);
        Event.stopObserving(Control.Selection.container,'mouseup',Control.Selection.stop);
    },
    recalculateLayout: function(){
        Control.Selection.selectableElements.each(function(element){
            var dimensions = element.getDimensions();
            var offset = element.cumulativeOffset();
            var scroll_offset = element.cumulativeScrollOffset();
            if(!element._control_selection) {
                element._control_selection = {}; }
            element._control_selection.top = offset[1] - scroll_offset[1];
            element._control_selection.left = offset[0] - scroll_offset[0];
            element._control_selection.width = dimensions.width;
            element._control_selection.height = dimensions.height;
        });
    },
    addSelectable: function(element,object,activation_targets,activation_target_callback){
        element = $(element);
        if(activation_targets) {
            activation_targets = activation_targets.each ? activation_targets : [activation_targets]; }
        var dimensions = element.getDimensions();
        var offset = Element.cumulativeOffset(element);
        element._control_selection = {
            activation_targets: activation_targets,
            is_selected: false,
            top: offset[1],
            left: offset[0],
            width: dimensions.width,
            height: dimensions.height,
            activationTargetMouseMove: function(){
                Control.Selection.notify('activationTargetMouseMove',element);
                if(activation_targets){
                    activation_targets.each(function(activation_target){
                        activation_target.stopObserving('mousemove',element._control_selection.activationTargetMouseMove);
                    });
                }
                Control.Selection.DragProxy.container.stopObserving('mousemove',element._control_selection.activationTargetMouseMove);
            },
            activationTargetMouseDown: function(event){
                if(!Control.Selection.elements.include(element)) {
                    Control.Selection.select(element); }
                Control.Selection.DragProxy.start(event);
                Control.Selection.DragProxy.container.hide();
                if(activation_targets){
                    activation_targets.each(function(activation_target){
                        activation_target.observe('mousemove',element._control_selection.activationTargetMouseMove);
                    });
                }
                Control.Selection.DragProxy.container.observe('mousemove',element._control_selection.activationTargetMouseMove);
            },
            activationTargetClick: function(){
                Control.Selection.select(element);
                if(typeof(activation_target_callback) == "function") {
                    activation_target_callback(); }
                if(activation_targets){
                    activation_targets.each(function(activation_target){
                        activation_target.stopObserving('mousemove',element._control_selection.activationTargetMouseMove);
                    });
                }
                Control.Selection.DragProxy.container.stopObserving('mousemove',element._control_selection.activationTargetMouseMove);
            }
        };
        element.onselectstart = function(){
            return false;
        };
        element.unselectable = 'on';
        element.style.MozUserSelect = 'none';
        if(activation_targets){
            activation_targets.each(function(activation_target){
                activation_target.observe('mousedown',element._control_selection.activationTargetMouseDown);
                activation_target.observe('click',element._control_selection.activationTargetClick);
            });
        }
        Control.Selection.selectableElements.push(element);
        Control.Selection.selectableObjects.push(object);
    },
    removeSelectable: function(element){
        element = $(element);
        if(element._control_selection.activation_targets){
            element._control_selection.activation_targets.each(function(activation_target){
                activation_target.stopObserving('mousedown',element._control_selection.activationTargetMouseDown);
            });
            element._control_selection.activation_targets.each(function(activation_target){
                activation_target.stopObserving('click',element._control_selection.activationTargetClick);
            });
        }
        element._control_selection = null;
        element.onselectstart = function() {
            return true;
        };
        element.unselectable = 'off';
        element.style.MozUserSelect = '';
        var position = 0;
        Control.Selection.selectableElements.each(function(selectable_element,i){
            if(selectable_element == element){
                position = i;
                throw $break;
            }
        });
        Control.Selection.selectableElements = Control.Selection.selectableElements.without(element);
        Control.Selection.selectableObjects = Control.Selection.selectableObjects.slice(0,position).concat(Control.Selection.selectableObjects.slice(position + 1));
    },
    select: function(selected_elements){
        if(typeof(selected_elements) == "undefined" || !selected_elements) {
            selected_elements = []; }
        if(!selected_elements.each && !selected_elements._each) {
            selected_elements = [selected_elements]; }
        //comparing the arrays directly wouldn't equate to true in safari so we need to compare each item
        var selected_items_have_changed = !(Control.Selection.elements.length == selected_elements.length && Control.Selection.elements.all(function(item,i){
            return selected_elements[i] == item;
        }));
        if(!selected_items_have_changed) {
            return; }
        var selected_objects_indexed_by_element = {};
        var selected_objects = selected_elements.collect(function(selected_element){
            var selected_object = Control.Selection.selectableObjects[Control.Selection.selectableElements.indexOf(selected_element)];
            selected_objects_indexed_by_element[selected_element] = selected_object;
            return selected_object;
        });
        if(Control.Selection.elements.length === 0 && selected_elements.length !== 0){
            selected_elements.each(function(element){
                Control.Selection.notify('selected',element,selected_objects_indexed_by_element[element]);
            });
        }else{
            Control.Selection.elements.each(function(element){
                if(!selected_elements.include(element)){
                    Control.Selection.notify('deselected',element,selected_objects_indexed_by_element[element]);
                }
            });
            selected_elements.each(function(element){
                if(!Control.Selection.elements.include(element)){
                    Control.Selection.notify('selected',element,selected_objects_indexed_by_element[element]);
                }
            });
        }
        Control.Selection.elements = selected_elements;
        Control.Selection.objects = selected_objects;
        Control.Selection.notify('change',Control.Selection.elements,Control.Selection.objects);
    },
    deselect: function(){
        if(Control.Selection.notify('deselect') === false) {
            return false; }
        Control.Selection.elements.each(function(element){
            Control.Selection.notify('deselected',element,Control.Selection.selectableObjects[Control.Selection.selectableElements.indexOf(element)]);
        });
        Control.Selection.objects = [];
        Control.Selection.elements = [];
        Control.Selection.notify('change',Control.Selection.objects,Control.Selection.elements);
        return true;
    },
    //private
    start: function(event){
        if(!event.isLeftClick() || Control.Selection.notify('start',event) === false) {
            return false; }
        if(!event.shiftKey && !event.altKey) {
            Control.Selection.deselect(); }
        Event.observe(Control.Selection.container,'mousemove',Control.Selection.onMouseMove);
        Event.stop(event);
        return false;
    },
    stop: function(){
        Event.stopObserving(Control.Selection.container,'mousemove',Control.Selection.onMouseMove);
        Control.Selection.active = false;
        Control.Selection.selection_div.setStyle({
            display: 'none',
            top: null,
            left: null,
            width: null,
            height: null
        });
        Control.Selection.start_mouse_coordinates = {};
        Control.Selection.current_mouse_coordinates = {};
    },
    mouseCoordinatesFromEvent: function(event){
        return {
            x: Event.pointerX(event),
            y: Event.pointerY(event)
        };
    },
    onClick: function(event,element,source){
        var selection = [];
        if(event.shiftKey){
            selection = Control.Selection.elements.clone();
            if(!selection.include(element)) {
                selection.push(element); }
        }else if(event.altKey){
            selection = Control.Selection.elements.clone();
            if(selection.include(element)) {
                selection = selection.without(element); }
        }else{
            selection = [element];
        }
        Control.Selection.select(selection);
        if(source == 'click') {
            Event.stop(event); }
    },
    onMouseMove: function(event){
        if(!Control.Selection.active){
            Control.Selection.active = true;
            Control.Selection.start_mouse_coordinates = Control.Selection.mouseCoordinatesFromEvent(event);
        }else{
            Control.Selection.current_mouse_coordinates = Control.Selection.mouseCoordinatesFromEvent(event);
            Control.Selection.drawSelectionDiv();
            var current_selection = Control.Selection.selectableElements.findAll(function(element){
                return Control.Selection.options.filter(element) && Control.Selection.elementWithinSelection(element);
            });
            if(event.shiftKey && !event.altKey){
                Control.Selection.elements.each(function(element){
                    if(!current_selection.include(element)) {
                        current_selection.push(element); }
                });
            }else if(event.altKey && !event.shiftKey){
                current_selection = Control.Selection.elements.findAll(function(element){
                    return !current_selection.include(element);
                });
            }
            Control.Selection.select(current_selection);
        }
    },
    drawSelectionDiv: function(){
        if(Control.Selection.start_mouse_coordinates == Control.Selection.current_mouse_coordinates){
            Control.Selection.selection_div.style.display = 'none';
        }else{
            Control.Selection.viewport = document.viewport.getDimensions();
            Control.Selection.selection_div.style.position = 'absolute';
            Control.Selection.current_direction = (Control.Selection.start_mouse_coordinates.y > Control.Selection.current_mouse_coordinates.y ? 'N' : 'S') + (Control.Selection.start_mouse_coordinates.x < Control.Selection.current_mouse_coordinates.x ? 'E' : 'W');
            Control.Selection.selection_div.setStyle(Control.Selection['dimensionsFor' + Control.Selection.current_direction]());
            Control.Selection.selection_div.style.display = 'block';
        }
    },
    dimensionsForNW: function(){
        return {
            top: (Control.Selection.start_mouse_coordinates.y - (Control.Selection.start_mouse_coordinates.y - Control.Selection.current_mouse_coordinates.y)) + 'px',
            left: (Control.Selection.start_mouse_coordinates.x - (Control.Selection.start_mouse_coordinates.x - Control.Selection.current_mouse_coordinates.x)) + 'px',
            width: (Control.Selection.start_mouse_coordinates.x - Control.Selection.current_mouse_coordinates.x) + 'px',
            height: (Control.Selection.start_mouse_coordinates.y - Control.Selection.current_mouse_coordinates.y) + 'px'
        };
    },
    dimensionsForNE: function(){
        return {
            top: (Control.Selection.start_mouse_coordinates.y - (Control.Selection.start_mouse_coordinates.y - Control.Selection.current_mouse_coordinates.y)) + 'px',
            left: Control.Selection.start_mouse_coordinates.x + 'px',
            width: Math.min((Control.Selection.viewport.width - Control.Selection.start_mouse_coordinates.x) - Control.Selection.border_width,Control.Selection.current_mouse_coordinates.x - Control.Selection.start_mouse_coordinates.x) + 'px',
            height: (Control.Selection.start_mouse_coordinates.y - Control.Selection.current_mouse_coordinates.y) + 'px'
        };
    },
    dimensionsForSE: function(){
        return {
            top: Control.Selection.start_mouse_coordinates.y + 'px',
            left: Control.Selection.start_mouse_coordinates.x + 'px',
            width: Math.min((Control.Selection.viewport.width - Control.Selection.start_mouse_coordinates.x) - Control.Selection.border_width,Control.Selection.current_mouse_coordinates.x - Control.Selection.start_mouse_coordinates.x) + 'px',
            height: Math.min((Control.Selection.viewport.height - Control.Selection.start_mouse_coordinates.y) - Control.Selection.border_width,Control.Selection.current_mouse_coordinates.y - Control.Selection.start_mouse_coordinates.y) + 'px'
        };
    },
    dimensionsForSW: function(){
        return {
            top: Control.Selection.start_mouse_coordinates.y + 'px',
            left: (Control.Selection.start_mouse_coordinates.x - (Control.Selection.start_mouse_coordinates.x - Control.Selection.current_mouse_coordinates.x)) + 'px',
            width: (Control.Selection.start_mouse_coordinates.x - Control.Selection.current_mouse_coordinates.x) + 'px',
            height: Math.min((Control.Selection.viewport.height - Control.Selection.start_mouse_coordinates.y) - Control.Selection.border_width,Control.Selection.current_mouse_coordinates.y - Control.Selection.start_mouse_coordinates.y) + 'px'
        };
    },
    inBoundsForNW: function(element,selection){
        return (
            ((element.left > selection.left || element.right > selection.left) && selection.right > element.left) &&
            ((element.top > selection.top || element.bottom > selection.top) && selection.bottom > element.top)
        );
    },
    inBoundsForNE: function(element,selection){
        return (
            ((element.left < selection.right || element.left < selection.right) && selection.left < element.right) &&
            ((element.top > selection.top || element.bottom > selection.top) && selection.bottom > element.top)
        );
    },
    inBoundsForSE: function(element,selection){
        return (
            ((element.left < selection.right || element.left < selection.right) && selection.left < element.right) &&
            ((element.bottom < selection.bottom || element.top < selection.bottom) && selection.top < element.bottom)
        );
    },
    inBoundsForSW: function(element,selection){
        return (
            ((element.left > selection.left || element.right > selection.left) && selection.right > element.left) &&
            ((element.bottom < selection.bottom || element.top < selection.bottom) && selection.top < element.bottom)
        );
    },
    elementWithinSelection: function(element){
        if(Control.Selection['inBoundsFor' + Control.Selection.current_direction]({
            top: element._control_selection.top,
            left: element._control_selection.left,
            bottom: element._control_selection.top + element._control_selection.height,
            right: element._control_selection.left + element._control_selection.width
        },{
            top: parseInt(Control.Selection.selection_div.style.top, 10),
            left: parseInt(Control.Selection.selection_div.style.left, 10),
            bottom: parseInt(Control.Selection.selection_div.style.top, 10) + parseInt(Control.Selection.selection_div.style.height, 10),
            right: parseInt(Control.Selection.selection_div.style.left, 10) + parseInt(Control.Selection.selection_div.style.width, 10)
        })){
            element._control_selection.is_selected = true;
            return true;
        }else{
            element._control_selection.is_selected = false;
            return false;
        }
    },
    DragProxy: {
        active: false,
        xorigin: 0,
        yorigin: 0,
        load: function(){
            Control.Selection.DragProxy.container = $(document.createElement('div'));
            Control.Selection.DragProxy.container.id = 'control_selection_drag_proxy';
            Control.Selection.DragProxy.container.setStyle({
                position: 'absolute',
                top: '1px',
                left: '1px',
                zIndex: 99999
            });
            Control.Selection.DragProxy.container.hide();
            document.body.appendChild(Control.Selection.DragProxy.container);
            Control.Selection.observe('selected',Control.Selection.DragProxy.selected);
            Control.Selection.observe('deselected',Control.Selection.DragProxy.deselected);
        },
        start: function(event){            
            if(event.isRightClick()){
                Control.Selection.DragProxy.container.hide();
                return;
            }            
            if(Control.Selection.DragProxy.xorigin == Event.pointerX(event) && Control.Selection.DragProxy.yorigin == Event.pointerY(event)) {
                return; }
            Control.Selection.DragProxy.active = true;
            Control.Selection.DragProxy.container.setStyle({
                position: 'absolute',
                top: Event.pointerY(event) + 'px',
                left: Event.pointerX(event) + 'px'
            });            
            Control.Selection.DragProxy.container.observe('mouseup',Control.Selection.DragProxy.onMouseUp);            
            Control.Selection.DragProxy.container.show();
            Control.Selection.DragProxy.container._draggable = new Draggable(Control.Selection.DragProxy.container,Object.extend({
                onEnd: Control.Selection.DragProxy.stop
            },Control.Selection.options.drag_proxy_options));
            Control.Selection.DragProxy.container._draggable.eventMouseDown(event);            
            Control.Selection.DragProxy.notify('start',Control.Selection.DragProxy.container,Control.Selection.elements);
        },
        stop: function(){
            window.setTimeout(function(){
                Control.Selection.DragProxy.active = false;
                Control.Selection.DragProxy.container.hide();
                if(Control.Selection.DragProxy.container._draggable){
                    Control.Selection.DragProxy.container._draggable.destroy();
                    Control.Selection.DragProxy.container._draggable = null;
                }
                Control.Selection.DragProxy.notify('stop');
            },1);
        },
        onClick: function(event){
            Control.Selection.DragProxy.xorigin = Event.pointerX(event);
            Control.Selection.DragProxy.yorigin = Event.pointerY(event);
            if(event.isRightClick()) {
                Control.Selection.DragProxy.container.hide(); }
            if(Control.Selection.elements.length >= Control.Selection.options.drag_proxy_threshold && !(event.shiftKey || event.altKey) && (Control.Selection.DragProxy.xorigin != Event.pointerX(event) || Control.Selection.DragProxy.yorigin != Event.pointerY(event))){
                Control.Selection.DragProxy.start(event);
                Event.stop(event);
            }
        },
        onMouseUp: function(event){
            Control.Selection.DragProxy.stop();
            Control.Selection.DragProxy.container.stopObserving('mouseup',Control.Selection.DragProxy.onMouseUp);
        },
        selected: function(element){
            element.observe('mousedown',Control.Selection.DragProxy.onClick);
        },
        deselected: function(element){
            element.stopObserving('mousedown',Control.Selection.DragProxy.onClick);
        }
    }
};
Object.Event.extend(Control.Selection);
Object.Event.extend(Control.Selection.DragProxy);
/**
 * @author Ryan Johnson <http://syntacticx.com/>
 * @copyright 2008 PersonalGrid Corporation <http://personalgrid.com/>
 * @package LivePipe UI
 * @license MIT
 * @url http://livepipe.net/control/rating
 * @require prototype.js, livepipe.js
 */

/*global Prototype, Class, Option, $, $A, Control, $break,  */

if(typeof(Prototype) == "undefined") {
    throw "Control.SelectMultiple requires Prototype to be loaded."; }
if(typeof(Object.Event) == "undefined") {
    throw "Control.SelectMultiple requires Object.Event to be loaded."; }

Control.SelectMultiple = Class.create({
    select: false,
    container: false,
    numberOfCheckedBoxes: 0,
    checkboxes: [],
    hasExtraOption: false,
    initialize: function(select,container,options){
        this.options = {
            checkboxSelector: 'input[type=checkbox]',
            nameSelector: 'span.name',
            labelSeparator: ', ',
            valueSeparator: ',',
            afterChange: Prototype.emptyFunction,
            overflowString: function(str){
                return str.truncate();
            },
            overflowLength: 30
        };
        Object.extend(this.options,options || {});
        this.select = $(select);
        this.container =  $(container);
        this.checkboxes = (typeof(this.options.checkboxSelector) == 'function') ? 
            this.options.checkboxSelector.bind(this)() : 
            this.container.getElementsBySelector(this.options.checkboxSelector);
        var value_was_set = false;
        if(this.options.value){
            value_was_set = true;
            this.setValue(this.options.value);
            delete this.options.value;
        }
        this.hasExtraOption = false;
        this.checkboxes.each(function(checkbox){
         checkbox.observe('click',this.checkboxOnClick.bind(this,checkbox));
        }.bind(this));
        this.select.observe('change',this.selectOnChange.bind(this));
        this.countAndCheckCheckBoxes();
        if(!value_was_set) {
         this.scanCheckBoxes(); }
        this.notify('afterChange',this.select.options[this.select.options.selectedIndex].value);
    },
    countAndCheckCheckBoxes: function(){
        this.numberOfCheckedBoxes = this.checkboxes.inject(0,function(number,checkbox){
            checkbox.checked = (this.select.options[this.select.options.selectedIndex].value == checkbox.value);
            var value_string = this.select.options[this.select.options.selectedIndex].value;
            var value_collection = $A(value_string.split ? value_string.split(this.options.valueSeparator) : value_string);
            var should_check = value_collection.any(function(value) {
                if (!should_check && checkbox.value == value) {
                    return true; }
            }.bind(this));
            checkbox.checked = should_check;
            if(checkbox.checked) {
                ++number; }
            return number;
        }.bind(this));
    },
    setValue: function(value_string){
        this.numberOfCheckedBoxes = 0;
        var value_collection = $A(value_string.split ? value_string.split(this.options.valueSeparator) : value_string);
        this.checkboxes.each(function(checkbox){
            checkbox.checked = false;
            value_collection.each(function(value){
                if(checkbox.value == value){
                    ++this.numberOfCheckedBoxes;
                    checkbox.checked = true;
                }
            }.bind(this));
        }.bind(this));
        this.scanCheckBoxes();
    },
    selectOnChange: function(){
        this.removeExtraOption();
        this.countAndCheckCheckBoxes();
        this.notify('afterChange',this.select.options[this.select.options.selectedIndex].value);
    },
    checkboxOnClick: function(checkbox){
        this.numberOfCheckedBoxes = this.checkboxes.findAll(function (c) { 
            return c.checked; 
        }).length;
        this.scanCheckBoxes();
        this.notify('afterChange', this.numberOfCheckedBoxes === 0 ? "" :
            this.select.options[this.select.options.selectedIndex].value);
    },
    scanCheckBoxes: function(){
        switch(this.numberOfCheckedBoxes){
            case 1:
                this.checkboxes.each(function(checkbox){
                    if(checkbox.checked){
                        $A(this.select.options).each(function(option,i){
                            if(option.value == checkbox.value){
                                this.select.options.selectedIndex = i;
                                throw $break;
                            }
                        }.bind(this));
                        throw $break;
                    }
                }.bind(this));
                break;
            case 0:
                this.removeExtraOption();
                break;
            default:
                this.addExtraOption();
                break;
        }
    },
    getLabelForExtraOption: function(){
        var label = (typeof(this.options.nameSelector) == 'function' ? 
            this.options.nameSelector.bind(this)() : 
            this.container.getElementsBySelector(this.options.nameSelector).inject([],function(labels,name_element,i){
                if(this.checkboxes[i].checked) {
                    labels.push(name_element.innerHTML); }
                return labels;
            }.bind(this))
        ).join(this.options.labelSeparator);
        return (label.length >= this.options.overflowLength && this.options.overflowLength > 0) ? 
            (typeof(this.options.overflowString) == 'function' ? this.options.overflowString(label) : this.options.overflowString) : 
            label;
    },
    getValueForExtraOption: function(){
        return this.checkboxes.inject([],function(values,checkbox){
            if(checkbox.checked) {
                values.push(checkbox.value); }
            return values;
        }).join(this.options.valueSeparator);
    },
    addExtraOption: function(){
        this.removeExtraOption();
        this.hasExtraOption = true;
        this.select.options[this.select.options.length] = new Option(this.getLabelForExtraOption(),this.getValueForExtraOption());
        this.select.options.selectedIndex = this.select.options.length - 1;
    },
    removeExtraOption: function(){
        if(this.hasExtraOption){
            this.select.remove(this.select.options.length - 1);
            this.hasExtraOption = false;
        }
    }
});
Object.Event.extend(Control.SelectMultiple);
/**
 * @author Ryan Johnson <http://syntacticx.com/>
 * @copyright 2008 PersonalGrid Corporation <http://personalgrid.com/>
 * @package LivePipe UI
 * @license MIT
 * @url http://livepipe.net/control/tabs
 * @require prototype.js, livepipe.js
 */

/*global window, document, Prototype, $, $A, $H, $break, Class, Element, Event, Control */

if(typeof(Prototype) == "undefined") {
    throw "Control.Tabs requires Prototype to be loaded."; }
if(typeof(Object.Event) == "undefined") {
    throw "Control.Tabs requires Object.Event to be loaded."; }

Control.Tabs = Class.create({
    initialize: function(tab_list_container,options){
        if(!$(tab_list_container)) {
            throw "Control.Tabs could not find the element: " + tab_list_container; }
        this.activeContainer = false;
        this.activeLink = false;
        this.containers = $H({});
        this.links = [];
        Control.Tabs.instances.push(this);
        this.options = {
            beforeChange: Prototype.emptyFunction,
            afterChange: Prototype.emptyFunction,
            hover: false,
            linkSelector: 'li a',
            setClassOnContainer: false,
            activeClassName: 'active',
            defaultTab: 'first',
            autoLinkExternal: true,
            targetRegExp: /#(.+)$/,
            showFunction: Element.show,
            hideFunction: Element.hide
        };
        Object.extend(this.options,options || {});
        (typeof(this.options.linkSelector == 'string') ? 
            $(tab_list_container).select(this.options.linkSelector) : 
            this.options.linkSelector($(tab_list_container))
        ).findAll(function(link){
            return (/^#/).exec((Prototype.Browser.WebKit ? decodeURIComponent(link.href) : link.href).replace(window.location.href.split('#')[0],''));
        }).each(function(link){
            this.addTab(link);
        }.bind(this));
        this.containers.values().each(Element.hide);
        if(this.options.defaultTab == 'first') {
            this.setActiveTab(this.links.first());
        } else if(this.options.defaultTab == 'last') {
            this.setActiveTab(this.links.last());
        } else {
            this.setActiveTab(this.options.defaultTab); }
        var targets = this.options.targetRegExp.exec(window.location);
        if(targets && targets[1]){
            targets[1].split(',').each(function(target){
                this.setActiveTab(this.links.find(function(link){
                    return link.key == target;
                }));
            }.bind(this));
        }
        if(this.options.autoLinkExternal){
            $A(document.getElementsByTagName('a')).each(function(a){
                if(!this.links.include(a)){
                    var clean_href = a.href.replace(window.location.href.split('#')[0],'');
                    if(clean_href.substring(0,1) == '#'){
                        if(this.containers.keys().include(clean_href.substring(1))){
                            $(a).observe('click',function(event,clean_href){
                                this.setActiveTab(clean_href.substring(1));
                            }.bindAsEventListener(this,clean_href));
                        }
                    }
                }
            }.bind(this));
        }
    },
    addTab: function(link){
        this.links.push(link);
        link.key = link.getAttribute('href').replace(window.location.href.split('#')[0],'').split('#').last().replace(/#/,'');
        var container = $(link.key);
        if(!container) {
            throw "Control.Tabs: #" + link.key + " was not found on the page."; }
        this.containers.set(link.key,container);
        link[this.options.hover ? 'onmouseover' : 'onclick'] = function(link){
            if(window.event) {
                Event.stop(window.event); }
            this.setActiveTab(link);
            return false;
        }.bind(this,link);
    },
    setActiveTab: function(link){
        if(!link && typeof(link) == 'undefined') {
            return; }
        if(typeof(link) == 'string'){
            this.setActiveTab(this.links.find(function(_link){
                return _link.key == link;
            }));
        }else if(typeof(link) == 'number'){
            this.setActiveTab(this.links[link]);
        }else{
            if(this.notify('beforeChange',this.activeContainer,this.containers.get(link.key)) === false) {
                return; }
            if(this.activeContainer) {
                this.options.hideFunction(this.activeContainer); }
            this.links.each(function(item){
                (this.options.setClassOnContainer ? $(item.parentNode) : item).removeClassName(this.options.activeClassName);
            }.bind(this));
            (this.options.setClassOnContainer ? $(link.parentNode) : link).addClassName(this.options.activeClassName);
            this.activeContainer = this.containers.get(link.key);
            this.activeLink = link;
            this.options.showFunction(this.containers.get(link.key));
            this.notify('afterChange',this.containers.get(link.key));
        }
    },
    next: function(){
        this.links.each(function(link,i){
            if(this.activeLink == link && this.links[i + 1]){
                this.setActiveTab(this.links[i + 1]);
                throw $break;
            }
        }.bind(this));
    },
    previous: function(){
        this.links.each(function(link,i){
            if(this.activeLink == link && this.links[i - 1]){
                this.setActiveTab(this.links[i - 1]);
                throw $break;
            }
        }.bind(this));
    },
    first: function(){
        this.setActiveTab(this.links.first());
    },
    last: function(){
        this.setActiveTab(this.links.last());
    }
});
Object.extend(Control.Tabs,{
    instances: [],
    findByTabId: function(id){
        return Control.Tabs.instances.find(function(tab){
            return tab.links.find(function(link){
                return link.key == id;
            });
        });
    }
});
Object.Event.extend(Control.Tabs);
/**
 * @author Ryan Johnson <http://syntacticx.com/>
 * @copyright 2008 PersonalGrid Corporation <http://personalgrid.com/>
 * @package LivePipe UI
 * @license MIT
 * @url http://livepipe.net/control/textarea
 * @require prototype.js, livepipe.js
 */

/*global window, document, Prototype, Class, $, $A, Control */

if(typeof(Prototype) == "undefined") {
    throw "Control.TextArea requires Prototype to be loaded."; }
if(typeof(Object.Event) == "undefined") {
    throw "Control.TextArea requires Object.Event to be loaded."; }

Control.TextArea = Class.create({
    initialize: function(textarea){
        this.onChangeTimeout = false;
        this.element = $(textarea);
        $(this.element).observe('keyup',this.doOnChange.bindAsEventListener(this));
        $(this.element).observe('paste',this.doOnChange.bindAsEventListener(this));
        $(this.element).observe('input',this.doOnChange.bindAsEventListener(this));
        if(!!document.selection){
            $(this.element).observe('mouseup',this.saveRange.bindAsEventListener(this));  
            $(this.element).observe('keyup',this.saveRange.bindAsEventListener(this));
        }
    },
    doOnChange: function(event){
        if(this.onChangeTimeout) {
            window.clearTimeout(this.onChangeTimeout); }
        this.onChangeTimeout = window.setTimeout(function(){
            this.notify('change',this.getValue());
        }.bind(this),Control.TextArea.onChangeTimeoutLength);
    },
    saveRange: function(){
        this.range = document.selection.createRange();  
    },
    getValue: function(){
        return this.element.value;
    },
    getSelection: function(){
        if(!!document.selection) {
            return document.selection.createRange().text; }
        else if(!!this.element.setSelectionRange) {
            return this.element.value.substring(this.element.selectionStart,this.element.selectionEnd); }
        else {
            return false; }
    },
    replaceSelection: function(text){
        var scroll_top = this.element.scrollTop;
        if(!!document.selection){
            this.element.focus();
            var range = (this.range) ? this.range : document.selection.createRange();
            range.text = text;
            range.select();
        }else if(!!this.element.setSelectionRange){
            var selection_start = this.element.selectionStart;
            this.element.value = this.element.value.substring(0,selection_start) + text + this.element.value.substring(this.element.selectionEnd);
            this.element.setSelectionRange(selection_start + text.length,selection_start + text.length);
        }
        this.doOnChange();
        this.element.focus();
        this.element.scrollTop = scroll_top;
    },
    wrapSelection: function(before,after){
        var sel = this.getSelection();
        // Remove the wrapping if the selection has the same before/after
        if (sel.indexOf(before) === 0 && 
            sel.lastIndexOf(after) === (sel.length - after.length)) {
            this.replaceSelection(sel.substring(before.length, 
                sel.length - after.length));
        } else { this.replaceSelection(before + sel + after); }
    },
    insertBeforeSelection: function(text){
        this.replaceSelection(text + this.getSelection());
    },
    insertAfterSelection: function(text){
        this.replaceSelection(this.getSelection() + text);
    },
    collectFromEachSelectedLine: function(callback,before,after){
        this.replaceSelection((before || '') + $A(this.getSelection().split("\n")).collect(callback).join("\n") + (after || ''));
    },
    insertBeforeEachSelectedLine: function(text,before,after){
        this.collectFromEachSelectedLine(function(line){
        },before,after);
    }
});
Object.extend(Control.TextArea,{
    onChangeTimeoutLength: 500
});
Object.Event.extend(Control.TextArea);

Control.TextArea.ToolBar = Class.create(    {
    initialize: function(textarea,toolbar){
        this.textarea = textarea;
        if(toolbar) {
            this.container = $(toolbar); }
        else{
            this.container = $(document.createElement('ul'));
            this.textarea.element.parentNode.insertBefore(this.container,this.textarea.element);
        }
    },
    attachButton: function(node,callback){
        node.onclick = function(){return false;};
        $(node).observe('click',callback.bindAsEventListener(this.textarea));
    },
    addButton: function(link_text,callback,attrs){
        var li = document.createElement('li');
        var a = document.createElement('a');
        a.href = '#';
        this.attachButton(a,callback);
        li.appendChild(a);
        Object.extend(a,attrs || {});
        if(link_text){
            var span = document.createElement('span');
            span.innerHTML = link_text;
            a.appendChild(span);
        }
        this.container.appendChild(li);
    }
});
/**
 * @author Ryan Johnson <http://syntacticx.com/>
 * @copyright 2008 PersonalGrid Corporation <http://personalgrid.com/>
 * @package LivePipe UI
 * @license MIT
 * @url http://livepipe.net/control/window
 * @require prototype.js, effects.js, draggable.js, resizable.js, livepipe.js
 */

//adds onDraw and constrainToViewport option to draggable
if(typeof(Draggable) != 'undefined'){
    //allows the point to be modified with an onDraw callback
    Draggable.prototype.draw = function(point) {
        var pos = Position.cumulativeOffset(this.element);
        if(this.options.ghosting) {
            var r = Position.realOffset(this.element);
            pos[0] += r[0] - Position.deltaX; pos[1] += r[1] - Position.deltaY;
        }
        
        var d = this.currentDelta();
        pos[0] -= d[0]; pos[1] -= d[1];
        
        if(this.options.scroll && (this.options.scroll != window && this._isScrollChild)) {
            pos[0] -= this.options.scroll.scrollLeft-this.originalScrollLeft;
            pos[1] -= this.options.scroll.scrollTop-this.originalScrollTop;
        }
        
        var p = [0,1].map(function(i){ 
            return (point[i]-pos[i]-this.offset[i]) 
        }.bind(this));
        
        if(this.options.snap) {
            if(typeof this.options.snap == 'function') {
                p = this.options.snap(p[0],p[1],this);
            } else {
                if(this.options.snap instanceof Array) {
                    p = p.map( function(v, i) {return Math.round(v/this.options.snap[i])*this.options.snap[i] }.bind(this))
                } else {
                    p = p.map( function(v) {return Math.round(v/this.options.snap)*this.options.snap }.bind(this))
                  }
            }
        }
        
        if(this.options.onDraw)
            this.options.onDraw.bind(this)(p);
        else{
            var style = this.element.style;
            if(this.options.constrainToViewport){
                var viewport_dimensions = document.viewport.getDimensions();
                var container_dimensions = this.element.getDimensions();
                var margin_top = parseInt(this.element.getStyle('margin-top'));
                var margin_left = parseInt(this.element.getStyle('margin-left'));
                var boundary = [[
                    0 - margin_left,
                    0 - margin_top
                ],[
                    (viewport_dimensions.width - container_dimensions.width) - margin_left,
                    (viewport_dimensions.height - container_dimensions.height) - margin_top
                ]];
                if((!this.options.constraint) || (this.options.constraint=='horizontal')){ 
                    if((p[0] >= boundary[0][0]) && (p[0] <= boundary[1][0]))
                        this.element.style.left = p[0] + "px";
                    else
                        this.element.style.left = ((p[0] < boundary[0][0]) ? boundary[0][0] : boundary[1][0]) + "px";
                } 
                if((!this.options.constraint) || (this.options.constraint=='vertical')){ 
                    if((p[1] >= boundary[0][1] ) && (p[1] <= boundary[1][1]))
                        this.element.style.top = p[1] + "px";
                  else
                        this.element.style.top = ((p[1] <= boundary[0][1]) ? boundary[0][1] : boundary[1][1]) + "px";               
                }
            }else{
                if((!this.options.constraint) || (this.options.constraint=='horizontal'))
                  style.left = p[0] + "px";
                if((!this.options.constraint) || (this.options.constraint=='vertical'))
                  style.top     = p[1] + "px";
            }
            if(style.visibility=="hidden")
                style.visibility = ""; // fix gecko rendering
        }
    };
}

if(typeof(Prototype) == "undefined")
    throw "Control.Window requires Prototype to be loaded.";
if(typeof(IframeShim) == "undefined")
    throw "Control.Window requires IframeShim to be loaded.";
if(typeof(Object.Event) == "undefined")
    throw "Control.Window requires Object.Event to be loaded.";
/*
    known issues:
        - when iframe is clicked is does not gain focus
        - safari can't open multiple iframes properly
        - constrainToViewport: body must have no margin or padding for this to work properly
        - iframe will be mis positioned during fade in
        - document.viewport does not account for scrollbars (this will eventually be fixed in the prototype core)
    notes
        - setting constrainToViewport only works when the page is not scrollable
        - setting draggable: true will negate the effects of position: center
*/
Control.Window = Class.create({
    initialize: function(container,options){
        Control.Window.windows.push(this);
        
        //attribute initialization
        this.container = false;
        this.isOpen = false;
        this.href = false;
        this.sourceContainer = false; //this is optionally the container that will open the window
        this.ajaxRequest = false;
        this.remoteContentLoaded = false; //this is set when the code to load the remote content is run, onRemoteContentLoaded is fired when the connection is closed
        this.numberInSequence = Control.Window.windows.length + 1; //only useful for the effect scoping
        this.indicator = false;
        this.effects = {
            fade: false,
            appear: false
        };
        this.indicatorEffects = {
            fade: false,
            appear: false
        };
        
        //options
        this.options = Object.extend({
            //lifecycle
            beforeOpen: Prototype.emptyFunction,
            afterOpen: Prototype.emptyFunction,
            beforeClose: Prototype.emptyFunction,
            afterClose: Prototype.emptyFunction,
            //dimensions and modes
            height: null,
            width: null,
            className: false,
            position: 'center', //'center', 'relative', [x,y], [function(){return x;},function(){return y;}]
            offsetLeft: 0, //available only for anchors opening the window, or windows set to position: hover
            offsetTop: 0, //""
            iframe: false, //if the window has an href, this will display the href as an iframe instead of requesting the url as an an Ajax.Request
            hover: false, //element object to hover over, or if "true" only available for windows with sourceContainer (an anchor or any element already on the page with an href attribute)
            indicator: false, //element to show or hide when ajax requests, images and iframes are loading
            closeOnClick: false, //does not work with hover,can be: true (click anywhere), 'container' (will refer to this.container), or element (a specific element)
            iframeshim: true, //whether or not to position an iFrameShim underneath the window 
            //effects
            fade: false,
            fadeDuration: 0.75,
            //draggable
            draggable: false,
            onDrag: Prototype.emptyFunction,
            //resizable
            resizable: false,
            minHeight: false,
            minWidth: false,
            maxHeight: false,
            maxWidth: false,
            onResize: Prototype.emptyFunction,
            //draggable and resizable
            constrainToViewport: false,
            //ajax
            method: 'post',
            parameters: {},
            onComplete: Prototype.emptyFunction,
            onSuccess: Prototype.emptyFunction,
            onFailure: Prototype.emptyFunction,
            onException: Prototype.emptyFunction,
            //any element with an href (image,iframe,ajax) will call this after it is done loading
            onRemoteContentLoaded: Prototype.emptyFunction,
            insertRemoteContentAt: false //false will set this to this.container, can be string selector (first returned will be selected), or an Element that must be a child of this.container
        },options || {});
        
        //container setup
        this.indicator = this.options.indicator ? $(this.options.indicator) : false;
        if(container){
            if(typeof(container) == "string" && container.match(Control.Window.uriRegex))
                this.href = container;
            else{
                this.container = $(container);
                //need to create the container now for tooltips (or hover: element with no container already on the page)
                //second call made below will not create the container since the check is done inside createDefaultContainer()
                this.createDefaultContainer(container);
                //if an element with an href was passed in we use it to activate the window
                if(this.container && ((this.container.readAttribute('href') && this.container.readAttribute('href') != '') || (this.options.hover && this.options.hover !== true))){                        
                    if(this.options.hover && this.options.hover !== true)
                        this.sourceContainer = $(this.options.hover);
                    else{
                        this.sourceContainer = this.container;
                        this.href = this.container.readAttribute('href');
                        var rel = this.href.match(/^#(.+)$/);
                        if(rel && rel[1]){
                            this.container = $(rel[1]);
                            this.href = false;
                        }else
                            this.container = false;
                    }
                    //hover or click handling
                    this.sourceContainerOpenHandler = function(event){
                        this.open(event);
                        event.stop();
                        return false;
                    }.bindAsEventListener(this);
                    this.sourceContainerCloseHandler = function(event){
                        this.close(event);
                    }.bindAsEventListener(this);
                    this.sourceContainerMouseMoveHandler = function(event){
                        this.position(event);
                    }.bindAsEventListener(this);
                    if(this.options.hover){
                        this.sourceContainer.observe('mouseenter',this.sourceContainerOpenHandler);
                        this.sourceContainer.observe('mouseleave',this.sourceContainerCloseHandler);
                        if(this.options.position == 'mouse')
                            this.sourceContainer.observe('mousemove',this.sourceContainerMouseMoveHandler);
                    }else
                        this.sourceContainer.observe('click',this.sourceContainerOpenHandler);
                }
            }
        }
        this.createDefaultContainer(container);
        if(this.options.insertRemoteContentAt === false)
            this.options.insertRemoteContentAt = this.container;
        var styles = {
            margin: 0,
            position: 'absolute',
            zIndex: Control.Window.initialZIndexForWindow()
        };
        if(this.options.width)
            styles.width = $value(this.options.width) + 'px';
        if(this.options.height)
            styles.height = $value(this.options.height) + 'px';
        this.container.setStyle(styles);
        if(this.options.className)
            this.container.addClassName(this.options.className);
        this.positionHandler = this.position.bindAsEventListener(this);
        this.outOfBoundsPositionHandler = this.ensureInBounds.bindAsEventListener(this);
        this.bringToFrontHandler = this.bringToFront.bindAsEventListener(this);
        this.container.observe('mousedown',this.bringToFrontHandler);
        this.container.hide();
        this.closeHandler = this.close.bindAsEventListener(this);
        //iframeshim setup
        if(this.options.iframeshim){
            this.iFrameShim = new IframeShim();
            this.iFrameShim.hide();
        }
        //resizable support
        this.applyResizable();
        //draggable support
        this.applyDraggable();
        
        //makes sure the window can't go out of bounds
        Event.observe(window,'resize',this.outOfBoundsPositionHandler);
        
        this.notify('afterInitialize');
    },
    open: function(event){
        if(this.isOpen){
            this.bringToFront();
            return false;
        }
        if(this.notify('beforeOpen') === false)
            return false;
        //closeOnClick
        if(this.options.closeOnClick){
            if(this.options.closeOnClick === true)
                this.closeOnClickContainer = $(document.body);
            else if(this.options.closeOnClick == 'container')
                this.closeOnClickContainer = this.container;
            else if (this.options.closeOnClick == 'overlay'){
                Control.Overlay.load();
                this.closeOnClickContainer = Control.Overlay.container;
            }else
                this.closeOnClickContainer = $(this.options.closeOnClick);
            this.closeOnClickContainer.observe('click',this.closeHandler);
        }
        if(this.href && !this.options.iframe && !this.remoteContentLoaded){
            //link to image
            this.remoteContentLoaded = true;
            if(this.href.match(/\.(jpe?g|gif|png|tiff?)$/i)){
                var img = new Element('img');
                img.observe('load',function(img){
                    this.getRemoteContentInsertionTarget().insert(img);
                    this.position();
                    if(this.notify('onRemoteContentLoaded') !== false){
                        if(this.options.indicator)
                            this.hideIndicator();
                        this.finishOpen();
                    }
                }.bind(this,img));
                img.writeAttribute('src',this.href);
            }else{
                //if this is an ajax window it will only open if the request is successful
                if(!this.ajaxRequest){
                    if(this.options.indicator)
                        this.showIndicator();
                    this.ajaxRequest = new Ajax.Request(this.href,{
                        method: this.options.method,
                        parameters: this.options.parameters,
                        onComplete: function(request){
                            this.notify('onComplete',request);
                            this.ajaxRequest = false;
                        }.bind(this),
                        onSuccess: function(request){
                            this.getRemoteContentInsertionTarget().insert(request.responseText);
                            this.notify('onSuccess',request);
                            if(this.notify('onRemoteContentLoaded') !== false){
                                if(this.options.indicator)
                                    this.hideIndicator();
                                this.finishOpen();
                            }
                        }.bind(this),
                        onFailure: function(request){
                            this.notify('onFailure',request);
                            if(this.options.indicator)
                                this.hideIndicator();
                        }.bind(this),
                        onException: function(request,e){
                            this.notify('onException',request,e);
                            if(this.options.indicator)
                                this.hideIndicator();
                        }.bind(this)
                    });
                }
            }
            return true;
        }else if(this.options.iframe && !this.remoteContentLoaded){
            //iframe
            this.remoteContentLoaded = true;
            if(this.options.indicator)
                this.showIndicator();
            this.getRemoteContentInsertionTarget().insert(Control.Window.iframeTemplate.evaluate({
                href: this.href
            }));
            var iframe = this.container.down('iframe');
            iframe.onload = function(){
                this.notify('onRemoteContentLoaded');
                if(this.options.indicator)
                    this.hideIndicator();
                iframe.onload = null;
            }.bind(this);
        }
        this.finishOpen(event);
        return true
    },
    close: function(event){ //event may or may not be present
        if(!this.isOpen || this.notify('beforeClose',event) === false)
            return false;
        if(this.options.closeOnClick)
            this.closeOnClickContainer.stopObserving('click',this.closeHandler);
        if(this.options.fade){
            this.effects.fade = new Effect.Fade(this.container,{
                queue: {
                    position: 'front',
                    scope: 'Control.Window' + this.numberInSequence
                },
                from: 1,
                to: 0,
                duration: this.options.fadeDuration / 2,
                afterFinish: function(){
                    if(this.iFrameShim)
                        this.iFrameShim.hide();
                    this.isOpen = false;
                    this.notify('afterClose');
                }.bind(this)
            });
        }else{
            this.container.hide();
            if(this.iFrameShim)
                this.iFrameShim.hide();
        }
        if(this.ajaxRequest)
            this.ajaxRequest.transport.abort();
        if(!(this.options.draggable || this.options.resizable) && this.options.position == 'center')
            Event.stopObserving(window,'resize',this.positionHandler);
        if(!this.options.draggable && this.options.position == 'center')
            Event.stopObserving(window,'scroll',this.positionHandler);
        if(this.options.indicator)
            this.hideIndicator();
        if(!this.options.fade){
            this.isOpen = false;
            this.notify('afterClose');
        }
        return true;
    },
    position: function(event){
        //this is up top for performance reasons
        if(this.options.position == 'mouse'){
            var xy = [Event.pointerX(event),Event.pointerY(event)];
            this.container.setStyle({
                top: xy[1] + $value(this.options.offsetTop) + 'px',
                left: xy[0] + $value(this.options.offsetLeft) + 'px'
            });
            return;
        }
        var container_dimensions = this.container.getDimensions();
        var viewport_dimensions = document.viewport.getDimensions();
        Position.prepare();
        var offset_left = (Position.deltaX + Math.floor((viewport_dimensions.width - container_dimensions.width) / 2));
        var offset_top = (Position.deltaY + ((viewport_dimensions.height > container_dimensions.height) ? Math.floor((viewport_dimensions.height - container_dimensions.height) / 2) : 0));
        if(this.options.position == 'center'){
            this.container.setStyle({
                top: (container_dimensions.height <= viewport_dimensions.height) ? ((offset_top != null && offset_top > 0) ? offset_top : 0) + 'px' : 0,
                left: (container_dimensions.width <= viewport_dimensions.width) ? ((offset_left != null && offset_left > 0) ? offset_left : 0) + 'px' : 0
            });
        }else if(this.options.position == 'relative'){
            var xy = this.sourceContainer.cumulativeOffset();
            var top = xy[1] + $value(this.options.offsetTop);
            var left = xy[0] + $value(this.options.offsetLeft);
            this.container.setStyle({
                top: (container_dimensions.height <= viewport_dimensions.height) ? (this.options.constrainToViewport ? Math.max(0,Math.min(viewport_dimensions.height - (container_dimensions.height),top)) : top) + 'px' : 0,
                left: (container_dimensions.width <= viewport_dimensions.width) ? (this.options.constrainToViewport ? Math.max(0,Math.min(viewport_dimensions.width - (container_dimensions.width),left)) : left) + 'px' : 0
            });
        }else if(this.options.position.length){
            var top = $value(this.options.position[1]) + $value(this.options.offsetTop);
            var left = $value(this.options.position[0]) + $value(this.options.offsetLeft);
            this.container.setStyle({
                top: (container_dimensions.height <= viewport_dimensions.height) ? (this.options.constrainToViewport ? Math.max(0,Math.min(viewport_dimensions.height - (container_dimensions.height),top)) : top) + 'px' : 0,
                left: (container_dimensions.width <= viewport_dimensions.width) ? (this.options.constrainToViewport ? Math.max(0,Math.min(viewport_dimensions.width - (container_dimensions.width),left)) : left) + 'px' : 0
            });
        }
        if(this.iFrameShim)
            this.updateIFrameShimZIndex();
    },
    ensureInBounds: function(){
        if(!this.isOpen)
            return;
        var viewport_dimensions = document.viewport.getDimensions();
        var container_offset = this.container.cumulativeOffset();
        var container_dimensions = this.container.getDimensions();
        if(container_offset.left + container_dimensions.width > viewport_dimensions.width){
            this.container.setStyle({
                left: (Math.max(0,viewport_dimensions.width - container_dimensions.width)) + 'px'
            });
        }
        if(container_offset.top + container_dimensions.height > viewport_dimensions.height){
            this.container.setStyle({
                top: (Math.max(0,viewport_dimensions.height - container_dimensions.height)) + 'px'
            });
        }
    },
    bringToFront: function(){
        Control.Window.bringToFront(this);
        this.notify('bringToFront');
    },
    destroy: function(){
        this.container.stopObserving('mousedown',this.bringToFrontHandler);
        if(this.draggable){
            Draggables.removeObserver(this.container);
            this.draggable.handle.stopObserving('mousedown',this.bringToFrontHandler);
            this.draggable.destroy();
        }
        if(this.resizable){
            Resizables.removeObserver(this.container);
            this.resizable.handle.stopObserving('mousedown',this.bringToFrontHandler);
            this.resizable.destroy();
        }
        if(this.container && !this.sourceContainer)
            this.container.remove();
        if(this.sourceContainer){
            if(this.options.hover){
                this.sourceContainer.stopObserving('mouseenter',this.sourceContainerOpenHandler);
                this.sourceContainer.stopObserving('mouseleave',this.sourceContainerCloseHandler);
                if(this.options.position == 'mouse')
                    this.sourceContainer.stopObserving('mousemove',this.sourceContainerMouseMoveHandler);
            }else
                this.sourceContainer.stopObserving('click',this.sourceContainerOpenHandler);
        }
        if(this.iFrameShim)
            this.iFrameShim.destroy();
        Event.stopObserving(window,'resize',this.outOfBoundsPositionHandler);
        Control.Window.windows = Control.Window.windows.without(this);
        this.notify('afterDestroy');
    },
    //private
    applyResizable: function(){
        if(this.options.resizable){
            if(typeof(Resizable) == "undefined")
                throw "Control.Window requires resizable.js to be loaded.";
            var resizable_handle = null;
            if(this.options.resizable === true){
                resizable_handle = new Element('div',{
                    className: 'resizable_handle'
                });
                this.container.insert(resizable_handle);
            }else
                resizable_handle = $(this.options.resziable);
            this.resizable = new Resizable(this.container,{
                handle: resizable_handle,
                minHeight: this.options.minHeight,
                minWidth: this.options.minWidth,
                maxHeight: this.options.constrainToViewport ? function(element){
                    //viewport height - top - total border height
                    return (document.viewport.getDimensions().height - parseInt(element.style.top || 0)) - (element.getHeight() - parseInt(element.style.height || 0));
                } : this.options.maxHeight,
                maxWidth: this.options.constrainToViewport ? function(element){
                    //viewport width - left - total border width
                    return (document.viewport.getDimensions().width - parseInt(element.style.left || 0)) - (element.getWidth() - parseInt(element.style.width || 0));
                } : this.options.maxWidth
            });
            this.resizable.handle.observe('mousedown',this.bringToFrontHandler);
            Resizables.addObserver(new Control.Window.LayoutUpdateObserver(this,function(){
                if(this.iFrameShim)
                    this.updateIFrameShimZIndex();
                this.notify('onResize');
            }.bind(this)));
        }
    },
    applyDraggable: function(){
        if(this.options.draggable){
            if(typeof(Draggables) == "undefined")
                throw "Control.Window requires dragdrop.js to be loaded.";
            var draggable_handle = null;
            if(this.options.draggable === true){
                draggable_handle = new Element('div',{
                    className: 'draggable_handle'
                });
                this.container.insert(draggable_handle);
            }else
                draggable_handle = $(this.options.draggable);
            this.draggable = new Draggable(this.container,{
                handle: draggable_handle,
                constrainToViewport: this.options.constrainToViewport,
                zindex: this.container.getStyle('z-index'),
                starteffect: function(){
                    if(Prototype.Browser.IE){
                        this.old_onselectstart = document.onselectstart;
                        document.onselectstart = function(){
                            return false;
                        };
                    }
                }.bind(this),
                endeffect: function(){
                    document.onselectstart = this.old_onselectstart;
                }.bind(this)
            });
            this.draggable.handle.observe('mousedown',this.bringToFrontHandler);
            Draggables.addObserver(new Control.Window.LayoutUpdateObserver(this,function(){
                if(this.iFrameShim)
                    this.updateIFrameShimZIndex();
                this.notify('onDrag');
            }.bind(this)));
        }
    },
    createDefaultContainer: function(container){
        if(!this.container){
            //no container passed or found, create it
            this.container = new Element('div',{
                id: 'control_window_' + this.numberInSequence
            });
            $(document.body).insert(this.container);
            if(typeof(container) == "string" && $(container) == null && !container.match(/^#(.+)$/) && !container.match(Control.Window.uriRegex))
                this.container.update(container);
        }
    },
    finishOpen: function(event){
        this.bringToFront();
        if(this.options.fade){
            if(typeof(Effect) == "undefined")
                throw "Control.Window requires effects.js to be loaded."
            if(this.effects.fade)
                this.effects.fade.cancel();
            this.effects.appear = new Effect.Appear(this.container,{
                queue: {
                    position: 'end',
                    scope: 'Control.Window.' + this.numberInSequence
                },
                from: 0,
                to: 1,
                duration: this.options.fadeDuration / 2,
                afterFinish: function(){
                    if(this.iFrameShim)
                        this.updateIFrameShimZIndex();
                    this.isOpen = true;
                    this.notify('afterOpen');
                }.bind(this)
            });
        }else
            this.container.show();
        this.position(event);
        if(!(this.options.draggable || this.options.resizable) && this.options.position == 'center')
            Event.observe(window,'resize',this.positionHandler,false);
        if(!this.options.draggable && this.options.position == 'center')
            Event.observe(window,'scroll',this.positionHandler,false);
        if(!this.options.fade){
            this.isOpen = true;
            this.notify('afterOpen');
        }
        return true;
    },
    showIndicator: function(){
        this.showIndicatorTimeout = window.setTimeout(function(){
            if(this.options.fade){
                this.indicatorEffects.appear = new Effect.Appear(this.indicator,{
                    queue: {
                        position: 'front',
                        scope: 'Control.Window.indicator.' + this.numberInSequence
                    },
                    from: 0,
                    to: 1,
                    duration: this.options.fadeDuration / 2
                });
            }else
                this.indicator.show();
        }.bind(this),Control.Window.indicatorTimeout);
    },
    hideIndicator: function(){
        if(this.showIndicatorTimeout)
            window.clearTimeout(this.showIndicatorTimeout);
        this.indicator.hide();
    },
    getRemoteContentInsertionTarget: function(){
        return typeof(this.options.insertRemoteContentAt) == "string" ? this.container.down(this.options.insertRemoteContentAt) : $(this.options.insertRemoteContentAt);
    },
    updateIFrameShimZIndex: function(){
        if(this.iFrameShim)
            this.iFrameShim.positionUnder(this.container);
    }
});
//class methods
Object.extend(Control.Window,{
    windows: [],
    baseZIndex: 9999,
    indicatorTimeout: 250,
    iframeTemplate: new Template('<iframe src="#{href}" width="100%" height="100%" frameborder="0"></iframe>'),
    uriRegex: /^(\/|\#|https?\:\/\/|[\w]+\/)/,
    bringToFront: function(w){
        Control.Window.windows = Control.Window.windows.without(w);
        Control.Window.windows.push(w);
        Control.Window.windows.each(function(w,i){
            var z_index = Control.Window.baseZIndex + i;
            w.container.setStyle({
                zIndex: z_index
            });
            if(w.isOpen){
                if(w.iFrameShim)
                w.updateIFrameShimZIndex();
            }
            if(w.options.draggable)
                w.draggable.options.zindex = z_index;
        });
    },
    open: function(container,options){
        var w = new Control.Window(container,options);
        w.open();
        return w;
    },
    //protected
    initialZIndexForWindow: function(w){
        return Control.Window.baseZIndex + (Control.Window.windows.length - 1);
    }
});
Object.Event.extend(Control.Window);

//this is the observer for both Resizables and Draggables
Control.Window.LayoutUpdateObserver = Class.create({
    initialize: function(w,observer){
        this.w = w;
        this.element = $(w.container);
        this.observer = observer;
    },
    onStart: Prototype.emptyFunction,
    onEnd: function(event_name,instance){
        if(instance.element == this.element && this.iFrameShim)
            this.w.updateIFrameShimZIndex();
    },
    onResize: function(event_name,instance){
        if(instance.element == this.element)
            this.observer(this.element);
    },
    onDrag: function(event_name,instance){
        if(instance.element == this.element)
            this.observer(this.element);
    }
});

//overlay for Control.Modal
Control.Overlay = {
    id: 'control_overlay',
    loaded: false,
    container: false,
    lastOpacity: 0,
    styles: {
        position: 'fixed',
        top: 0,
        left: 0,
        width: '100%',
        height: '100%',
        zIndex: 9998
    },
    ieStyles: {
        position: 'absolute',
        top: 0,
        left: 0,
        zIndex: 9998
    },
    effects: {
        fade: false,
        appear: false
    },
    load: function(){
        if(Control.Overlay.loaded)
            return false;
        Control.Overlay.loaded = true;
        Control.Overlay.container = new Element('div',{
            id: Control.Overlay.id
        });
        $(document.body).insert(Control.Overlay.container);
        if(Prototype.Browser.IE){
            Control.Overlay.container.setStyle(Control.Overlay.ieStyles);
            Event.observe(window,'scroll',Control.Overlay.positionOverlay);
            Event.observe(window,'resize',Control.Overlay.positionOverlay);
            Control.Overlay.observe('beforeShow',Control.Overlay.positionOverlay);
        }else
            Control.Overlay.container.setStyle(Control.Overlay.styles);
        Control.Overlay.iFrameShim = new IframeShim();
        Control.Overlay.iFrameShim.hide();
        Event.observe(window,'resize',Control.Overlay.positionIFrameShim);
        Control.Overlay.container.hide();
        return true;
    },
    unload: function(){
        if(!Control.Overlay.loaded)
            return false;
        Event.stopObserving(window,'resize',Control.Overlay.positionOverlay);
        Control.Overlay.stopObserving('beforeShow',Control.Overlay.positionOverlay);
        Event.stopObserving(window,'resize',Control.Overlay.positionIFrameShim);
        Control.Overlay.iFrameShim.destroy();
        Control.Overlay.container.remove();
        Control.Overlay.loaded = false;
        return true;
    },
    show: function(opacity,fade){
        if(Control.Overlay.notify('beforeShow') === false)
            return false;
        Control.Overlay.lastOpacity = opacity;
        Control.Overlay.positionIFrameShim();
        Control.Overlay.iFrameShim.show();
        if(fade){
            if(typeof(Effect) == "undefined")
                throw "Control.Window requires effects.js to be loaded."
            if(Control.Overlay.effects.fade)
                Control.Overlay.effects.fade.cancel();
            Control.Overlay.effects.appear = new Effect.Appear(Control.Overlay.container,{
                queue: {
                    position: 'end',
                    scope: 'Control.Overlay'
                },
                afterFinish: function(){
                    Control.Overlay.notify('afterShow');
                },
                from: 0,
                to: Control.Overlay.lastOpacity,
                duration: (fade === true ? 0.75 : fade) / 2
            });
        }else{
            Control.Overlay.container.setStyle({
                opacity: opacity || 1
            });
            Control.Overlay.container.show();
            Control.Overlay.notify('afterShow');
        }
        return true;
    },
    hide: function(fade){
        if(Control.Overlay.notify('beforeHide') === false)
            return false;
        if(Control.Overlay.effects.appear)
            Control.Overlay.effects.appear.cancel();
        Control.Overlay.iFrameShim.hide();
        if(fade){
            Control.Overlay.effects.fade = new Effect.Fade(Control.Overlay.container,{
                queue: {
                    position: 'front',
                    scope: 'Control.Overlay'
                },
                afterFinish: function(){
                    Control.Overlay.notify('afterHide');
                },
                from: Control.Overlay.lastOpacity,
                to: 0,
                duration: (fade === true ? 0.75 : fade) / 2
            });
        }else{
            Control.Overlay.container.hide();
            Control.Overlay.notify('afterHide');
        }
        return true;
    },
    positionIFrameShim: function(){
        if(Control.Overlay.container.visible())
            Control.Overlay.iFrameShim.positionUnder(Control.Overlay.container);
    },
    //IE only
    positionOverlay: function(){
        Control.Overlay.container.setStyle({
            width: document.body.clientWidth + 'px',
            height: document.body.clientHeight + 'px'
        });
    }
};
Object.Event.extend(Control.Overlay);

Control.ToolTip = Class.create(Control.Window,{
    initialize: function($super,container,tooltip,options){
        $super(tooltip,Object.extend(Object.extend(Object.clone(Control.ToolTip.defaultOptions),options || {}),{
            position: 'mouse',
            hover: container
        }));
    }
});
Object.extend(Control.ToolTip,{
    defaultOptions: {
        offsetLeft: 10
    }
});

Control.Modal = Class.create(Control.Window,{
    initialize: function($super,container,options){
        Control.Modal.InstanceMethods.beforeInitialize.bind(this)();
        $super(container,Object.extend(Object.clone(Control.Modal.defaultOptions),options || {}));
    }
});
Object.extend(Control.Modal,{
    defaultOptions: {
        overlayOpacity: 0.5,
        closeOnClick: 'overlay'
    },
    current: false,
    open: function(container,options){
        var modal = new Control.Modal(container,options);
        modal.open();
        return modal;
    },
    close: function(){
        if(Control.Modal.current)
            Control.Modal.current.close();
    },
    InstanceMethods: {
        beforeInitialize: function(){
            Control.Overlay.load();
            this.overlayFinishedOpening = false;
            this.observe('beforeOpen',Control.Modal.Observers.beforeOpen.bind(this));
            this.observe('afterOpen',Control.Modal.Observers.afterOpen.bind(this));
            this.observe('afterClose',Control.Modal.Observers.afterClose.bind(this));
        }
    },
    Observers: {
        beforeOpen: function(){
            if(!this.overlayFinishedOpening){
                Control.Overlay.observeOnce('afterShow',function(){
                    this.overlayFinishedOpening = true;
                    this.open();
                }.bind(this));
                Control.Overlay.show(this.options.overlayOpacity,this.options.fade ? this.options.fadeDuration : false);
                throw $break;
            }else
            Control.Window.windows.without(this).invoke('close');
        },
        afterOpen: function(){
            Control.Modal.current = this;
        },
        afterClose: function(){
            Control.Overlay.hide(this.options.fade ? this.options.fadeDuration : false);
            Control.Modal.current = false;
            this.overlayFinishedOpening = false;
        }
    }
});

Control.LightBox = Class.create(Control.Window,{
    initialize: function($super,container,options){
        this.allImagesLoaded = false;
        if(options.modal){
            var options = Object.extend(Object.clone(Control.LightBox.defaultOptions),options || {});
            options = Object.extend(Object.clone(Control.Modal.defaultOptions),options);
            options = Control.Modal.InstanceMethods.beforeInitialize.bind(this)(options);
            $super(container,options);
        }else
            $super(container,Object.extend(Object.clone(Control.LightBox.defaultOptions),options || {}));
        this.hasRemoteContent = this.href && !this.options.iframe;
        if(this.hasRemoteContent)
            this.observe('onRemoteContentLoaded',Control.LightBox.Observers.onRemoteContentLoaded.bind(this));
        else
            this.applyImageObservers();
        this.observe('beforeOpen',Control.LightBox.Observers.beforeOpen.bind(this));
    },
    applyImageObservers:function(){
        var images = this.getImages();
        this.numberImagesToLoad = images.length;
        this.numberofImagesLoaded = 0;
        images.each(function(image){
            image.observe('load',function(image){
                ++this.numberofImagesLoaded;
                if(this.numberImagesToLoad == this.numberofImagesLoaded){
                    this.allImagesLoaded = true;
                    this.onAllImagesLoaded();
                }
            }.bind(this,image));
            image.hide();
        }.bind(this));
    },
    onAllImagesLoaded: function(){
        this.getImages().each(function(image){
            this.showImage(image);
        }.bind(this));
        if(this.hasRemoteContent){
            if(this.options.indicator)
                this.hideIndicator();
            this.finishOpen();
        }else
            this.open();
    },
    getImages: function(){
        return this.container.select(Control.LightBox.imageSelector);
    },
    showImage: function(image){
        image.show();
    }
});
Object.extend(Control.LightBox,{
    imageSelector: 'img',
    defaultOptions: {},
    Observers: {
        beforeOpen: function(){
            if(!this.hasRemoteContent && !this.allImagesLoaded)
                throw $break;
        },
        onRemoteContentLoaded: function(){
            this.applyImageObservers();
            if(!this.allImagesLoaded)
                throw $break;
        }
    }
});
/**
 * @author Ryan Johnson <http://syntacticx.com/>
 * @copyright 2008 PersonalGrid Corporation <http://personalgrid.com/>
 * @package LivePipe UI
 * @license MIT
 * @url http://livepipe.net/core
 * @require prototype.js
 */

if(typeof(Control) == 'undefined')
    Control = {};
    
var $proc = function(proc){
    return typeof(proc) == 'function' ? proc : function(){return proc};
};

var $value = function(value){
    return typeof(value) == 'function' ? value() : value;
};

Object.Event = {
    extend: function(object){
        object._objectEventSetup = function(event_name){
            this._observers = this._observers || {};
            this._observers[event_name] = this._observers[event_name] || [];
        };
        object.observe = function(event_name,observer){
            if(typeof(event_name) == 'string' && typeof(observer) != 'undefined'){
                this._objectEventSetup(event_name);
                if(!this._observers[event_name].include(observer))
                    this._observers[event_name].push(observer);
            }else
                for(var e in event_name)
                    this.observe(e,event_name[e]);
        };
        object.stopObserving = function(event_name,observer){
            this._objectEventSetup(event_name);
            if(event_name && observer)
                this._observers[event_name] = this._observers[event_name].without(observer);
            else if(event_name)
                this._observers[event_name] = [];
            else
                this._observers = {};
        };
        object.observeOnce = function(event_name,outer_observer){
            var inner_observer = function(){
                outer_observer.apply(this,arguments);
                this.stopObserving(event_name,inner_observer);
            }.bind(this);
            this._objectEventSetup(event_name);
            this._observers[event_name].push(inner_observer);
        };
        object.notify = function(event_name){
            this._objectEventSetup(event_name);
            var collected_return_values = [];
            var args = $A(arguments).slice(1);
            try{
                for(var i = 0; i < this._observers[event_name].length; ++i)
                    collected_return_values.push(this._observers[event_name][i].apply(this._observers[event_name][i],args) || null);
            }catch(e){
                if(e == $break)
                    return false;
                else
                    throw e;
            }
            return collected_return_values;
        };
        if(object.prototype){
            object.prototype._objectEventSetup = object._objectEventSetup;
            object.prototype.observe = object.observe;
            object.prototype.stopObserving = object.stopObserving;
            object.prototype.observeOnce = object.observeOnce;
            object.prototype.notify = function(event_name){
                if(object.notify){
                    var args = $A(arguments).slice(1);
                    args.unshift(this);
                    args.unshift(event_name);
                    object.notify.apply(object,args);
                }
                this._objectEventSetup(event_name);
                var args = $A(arguments).slice(1);
                var collected_return_values = [];
                try{
                    if(this.options && this.options[event_name] && typeof(this.options[event_name]) == 'function')
                        collected_return_values.push(this.options[event_name].apply(this,args) || null);
                    for(var i = 0; i < this._observers[event_name].length; ++i)
                        collected_return_values.push(this._observers[event_name][i].apply(this._observers[event_name][i],args) || null);
                }catch(e){
                    if(e == $break)
                        return false;
                    else
                        throw e;
                }
                return collected_return_values;
            };
        }
    }
};

/* Begin Core Extensions */

//Element.observeOnce
Element.addMethods({
    observeOnce: function(element,event_name,outer_callback){
        var inner_callback = function(){
            outer_callback.apply(this,arguments);
            Element.stopObserving(element,event_name,inner_callback);
        };
        Element.observe(element,event_name,inner_callback);
    }
});

//mouse:wheel
(function(){
    function wheel(event){
        var delta, element, custom_event;
        // normalize the delta
        if (event.wheelDelta) { // IE & Opera
            delta = event.wheelDelta / 120;
        } else if (event.detail) { // W3C
            delta =- event.detail / 3;
        }
        if (!delta) { return; }
        element = Event.extend(event).target;
        element = Element.extend(element.nodeType === Node.TEXT_NODE ? element.parentNode : element);
        custom_event = element.fire('mouse:wheel',{ delta: delta });
        if (custom_event.stopped) {
            Event.stop(event);
            return false;
        }
    }
    document.observe('mousewheel',wheel);
    document.observe('DOMMouseScroll',wheel);
})();

/* End Core Extensions */

//from PrototypeUI
var IframeShim = Class.create({
    initialize: function() {
        this.element = new Element('iframe',{
            style: 'position:absolute;filter:progid:DXImageTransform.Microsoft.Alpha(opacity=0);display:none',
            src: 'javascript:void(0);',
            frameborder: 0 
        });
        $(document.body).insert(this.element);
    },
    hide: function() {
        this.element.hide();
        return this;
    },
    show: function() {
        this.element.show();
        return this;
    },
    positionUnder: function(element) {
        var element = $(element);
        var offset = element.cumulativeOffset();
        var dimensions = element.getDimensions();
        this.element.setStyle({
            left: offset[0] + 'px',
            top: offset[1] + 'px',
            width: dimensions.width + 'px',
            height: dimensions.height + 'px',
            zIndex: element.getStyle('zIndex') - 1
        }).show();
        return this;
    },
    setBounds: function(bounds) {
        for(prop in bounds)
            bounds[prop] += 'px';
        this.element.setStyle(bounds);
        return this;
    },
    destroy: function() {
        if(this.element)
            this.element.remove();
        return this;
    }
});/**
 * @author Ryan Johnson <http://syntacticx.com/>
 * @copyright 2008 PersonalGrid Corporation <http://personalgrid.com/>
 * @package LivePipe UI
 * @license MIT
 * @url http://livepipe.net/controls/hotkey/
 * @attribution http://www.quirksmode.org/js/cookies.html
 */

/*global document, Prototype, $A */

if(typeof(Prototype) == "undefined") {
  throw "Cookie requires Prototype to be loaded."; }
if(typeof(Object.Event) == "undefined") {
  throw "Cookie requires Object.Event to be loaded."; }

var Cookie = {
  build: function() {
    return $A(arguments).compact().join("; ");
  },
  secondsFromNow: function(seconds) {
    var d = new Date();
    d.setTime(d.getTime() + (seconds * 1000));
    return d.toGMTString();
  },
  set: function(name,value,seconds){
    Cookie.notify('set',name,value);
    var expiry = seconds ? 'expires=' + Cookie.secondsFromNow(seconds) : null;
    document.cookie = Cookie.build(name + "=" + value, expiry, "path=/");
  },
  get: function(name){
    Cookie.notify('get',name);
    var valueMatch = new RegExp(name + "=([^;]+)").exec(document.cookie);
    return valueMatch ? valueMatch[1] : null;
  },
  unset: function(name){
    Cookie.notify('unset',name);
    Cookie.set(name,'',-1);
  }
};
Object.Event.extend(Cookie);
/**
 * @author Ryan Johnson <http://syntacticx.com/>
 * @copyright 2008 PersonalGrid Corporation <http://personalgrid.com/>
 * @package LivePipe UI
 * @license MIT
 * @url http://livepipe.net/control/contextmenu
 * @require prototype.js, livepipe.js
 */

/*global window, document, Prototype, Class, Event, $, $A, $R, Control, $value */

if(typeof(Prototype) == "undefined") {
    throw "Control.ContextMenu requires Prototype to be loaded."; }
if(typeof(Object.Event) == "undefined") {
    throw "Control.ContextMenu requires Object.Event to be loaded."; }

Control.ContextMenu = Class.create({
    initialize: function(container,options){
        Control.ContextMenu.load();
        this.options = Object.extend({
            leftClick: false,
            disableOnShiftKey: true,
            disableOnAltKey: true,
            selectedClassName: 'selected',
            activatedClassName: 'activated',
            animation: true,
            animationCycles: 2,
            animationLength: 300,
            delayCallback: true
        },options || {});
        this.activated = false;
        this.items = this.options.items || [];
        this.container = $(container);
        this.container.observe(this.options.leftClick ? 'click' : (Prototype.Browser.Opera ? 'click' : 'contextmenu'),function(event){
            if(!Control.ContextMenu.enabled || Prototype.Browser.Opera && !event.ctrlKey) {
                return; }
            this.open(event);
        }.bindAsEventListener(this));
    },
    open: function(event){
        if(Control.ContextMenu.current && !Control.ContextMenu.current.close()) {
            return; }
        if(this.notify('beforeOpen',event) === false) {
            return false; }
        this.buildMenu();
        if(this.items.length === 0){
            this.close(event);
            return false;
        }
        this.clicked = Event.element(event);
        Control.ContextMenu.current = this;
        Control.ContextMenu.positionContainer(event);
        Control.ContextMenu.container.show();
        if(this.notify('afterOpen',event) === false) {
            return false; }
        event.stop();
        return true;
    },
    close: function(event){
        if(event) {
            event.stop(); }
        if(this.notify('beforeClose') === false) {
            return false; }
        Control.ContextMenu.current = false;
        this.activated = false;
        Control.ContextMenu.container.removeClassName(this.options.activatedClassName);
        Control.ContextMenu.container.select('li').invoke('stopObserving');
        Control.ContextMenu.container.hide();
        Control.ContextMenu.container.update('');
        if(this.notify('afterClose') === false) {
            return false; }
        return true;
    },
    buildMenu: function(){
        var list = document.createElement('ul');
        Control.ContextMenu.container.appendChild(list);
        this.items.each(function(item){
            if(!(!item.condition || item.condition && item.condition() !== false)) {
                return; }
            var item_container = $(document.createElement('li'));
            item_container.update($value(item.label));
            list.appendChild(item_container);
            item_container[$value(item.enabled) ? 'removeClassName' : 'addClassName']('disabled');
            item_container.observe('mousedown',function(event,item){
                if(!$value(item.enabled)) {
                    return event.stop(); }
                this.activated = $value(item.label);
            }.bindAsEventListener(this,item));
            item_container.observe('click',this.selectMenuItem.bindAsEventListener(this,item,item_container));
            item_container.observe('contextmenu',this.selectMenuItem.bindAsEventListener(this,item,item_container));
        }.bind(this));
    },
    addItem: function(params){
        if (!('enabled' in params)) { params.enabled = true; }
        this.items.push(params);
        return this;
    },
    destroy: function(){
        this.container.stopObserving(Prototype.Browser.Opera || this.options.leftClick ? 'click' : 'contextmenu');
        this.items = [];
    },
    selectMenuItem: function(event,item,item_container){
        if(!$value(item.enabled)) {
            return event.stop(); }
        if(!this.activated || this.activated == $value(item.label)){
            if(this.options.animation){
                Control.ContextMenu.container.addClassName(this.options.activatedClassName);
                $A($R(0,this.options.animationCycles * 2)).each(function(i){
                    window.setTimeout(function(){
                        item_container.toggleClassName(this.options.selectedClassName);
                    }.bind(this),i * parseInt(this.options.animationLength / (this.options.animationCycles * 2), 10));
                }.bind(this));
                window.setTimeout(function(){
                    if(this.close() && this.options.delayCallback) {
                        item.callback(this.clicked); }
                }.bind(this),this.options.animationLength);
                if(!this.options.delayCallback) {
                    item.callback(this.clicked); }
            }else if(this.close()) {
                item.callback(this.clicked); }
        }
        event.stop();
        return false;
    }
});
Object.extend(Control.ContextMenu,{
    loaded: false,
    capture_all: false,
    menus: [],
    current: false,
    enabled: false,
    offset: 4,
    load: function(capture_all){
        if(Control.ContextMenu.loaded) {
            return; }
        Control.ContextMenu.loaded = true;
        if(typeof(capture_all) == 'undefined') {
            capture_all = false; }
        Control.ContextMenu.capture_all = capture_all;
        Control.ContextMenu.container = $(document.createElement('div'));
        Control.ContextMenu.container.id = 'control_contextmenu';
        Control.ContextMenu.container.style.position = 'absolute';
        Control.ContextMenu.container.style.zIndex = 99999;
        Control.ContextMenu.container.hide();
        document.body.appendChild(Control.ContextMenu.container);
        Control.ContextMenu.enable();
    },
    enable: function(){
        Control.ContextMenu.enabled = true;
        Event.observe(document.body,'click',Control.ContextMenu.onClick);
        if(Control.ContextMenu.capture_all) {
            Event.observe(document.body,'contextmenu',Control.ContextMenu.onContextMenu); }
    },
    disable: function(){
        Event.stopObserving(document.body,'click',Control.ContextMenu.onClick);
        if(Control.ContextMenu.capture_all) {
            Event.stopObserving(document.body,'contextmenu',Control.ContextMenu.onContextMenu);    }
    },
    onContextMenu: function(event){
        event.stop();
        return false;
    },
    onClick: function(){
        if(Control.ContextMenu.current) {
            Control.ContextMenu.current.close(); }
    },
    positionContainer: function(event){
        var dimensions = Control.ContextMenu.container.getDimensions();
        var top = Event.pointerY(event);
        var left = Event.pointerX(event);
        var bottom = dimensions.height + top;
        var right = dimensions.width + left;
        var viewport_dimensions = document.viewport.getDimensions();
        var viewport_scroll_offsets = document.viewport.getScrollOffsets();
        if(bottom > viewport_dimensions.height + viewport_scroll_offsets.top) {
            top -= bottom - ((viewport_dimensions.height  + viewport_scroll_offsets.top) - Control.ContextMenu.offset); }
        if(right > viewport_dimensions.width + viewport_scroll_offsets.left) {
            left -= right - ((viewport_dimensions.width + viewport_scroll_offsets.left) - Control.ContextMenu.offset); }
        Control.ContextMenu.container.setStyle({
            top: top + 'px',
            left: left + 'px'
        });
    }
});
Object.Event.extend(Control.ContextMenu);/**
 * @author Ryan Johnson <http://syntacticx.com/>
 * @copyright 2008 PersonalGrid Corporation <http://personalgrid.com/>
 * @package LivePipe UI
 * @license MIT
 * @url http://livepipe.net/extra/event_behavior
 * @require prototype.js, livepipe.js
 * @attribution http://www.adamlogic.com/2007/03/20/3_metaprogramming-javascript-presentation
 */

/*global Prototype, Class, Event, Try, $, $A, $H */

if(typeof(Prototype) == "undefined") {
    throw "Event.Behavior requires Prototype to be loaded."; }
if(typeof(Object.Event) == "undefined") {
    throw "Event.Behavior requires Object.Event to be loaded."; }
    
Event.Behavior = {
    addVerbs: function(verbs){
        var v;
        for (var name in verbs) { if (verbs.hasOwnProperty(name)) {
            v = new Event.Behavior.Verb(verbs[name]);
            Event.Behavior.Verbs[name] = v;
            Event.Behavior[name.underscore()] = Event.Behavior[name] = v.getCallbackForStack.bind(v);
        }}
    },
    addEvents: function(events){
        $H(events).each(function(event_type){
            Event.Behavior.Adjective.prototype[event_type.key.underscore()] = Event.Behavior.Adjective.prototype[event_type.key] = function(){
                this.nextConditionType = 'and';
                this.events.push(event_type.value);
                this.attachObserver(false);
                return this;
            };
        });
    },
    invokeElementMethod: function(element,action,args){
        if(typeof(element) == 'function'){
            return $A(element()).each(function(e){
                if(typeof(args[0]) == 'function'){
                    return $A(args[0]).each(function(a){
                        return $(e)[action].apply($(e),(a ? [a] : []));
                    });
                }else {
                    return $(e)[action].apply($(e),args || []); }
            });
        }else {
            return $(element)[action].apply($(element),args || []); }
    }
};

Event.Behavior.Verbs = $H({});

Event.Behavior.Verb = Class.create();
Object.extend(Event.Behavior.Verb.prototype,{
    originalAction: false,
    execute: false,
    executeOpposite: false,
    target: false,
    initialize: function(action){
        this.originalAction = action;
        this.execute = function(action,target,argument){
            return (argument) ? action(target,argument) : action(target);
        }.bind(this,action);
    },
    setOpposite: function(opposite_verb){
        var opposite_action = opposite_verb.originalAction;
        this.executeOpposite = function(opposite_action,target,argument){
            return (argument) ? opposite_action(target,argument) : 
                opposite_action(target);
        }.bind(this,opposite_action);
    },
    getCallbackForStack: function(argument){
        return new Event.Behavior.Noun(this,argument);
    }
});

Event.Behavior.addVerbs({
    call: function(callback){
        callback();
    },
    show: function(element){
        return Event.Behavior.invokeElementMethod(element,'show');
    },
    hide: function(element){
        return Event.Behavior.invokeElementMethod(element,'hide');
    },
    remove: function(element){
        return Event.Behavior.invokeElementMethod(element,'remove');
    },
    setStyle: function(element,styles){
        return Event.Behavior.invokeElementMethod(element,'setStyle',[(typeof(styles) == 'function' ? styles() : styles)]);
    },
    addClassName: function(element,class_name){
        return Event.Behavior.invokeElementMethod(element,'addClassName',[(typeof(class_name) == 'function' ? class_name() : class_name)]);
    },
    removeClassName: function(element,class_name){
        return Event.Behavior.invokeElementMethod(element,'removeClassName',[(typeof(class_name) == 'function' ? class_name() : class_name)]);
    },
    setClassName: function(element,class_name){
        var c = (typeof(class_name) == 'function') ? class_name() : class_name;
        if(typeof(element) == 'function'){
            return $A(element()).each(function(e){
                $(e).className = c;
            });
        }else {
            c = $(element).className;
            return c;
        }
    },
    update: function(content,element){
        return Event.Behavior.invokeElementMethod(element,'update',[(typeof(content) == 'function' ? content() : content)]);
    },
    replace: function(content,element){
        return Event.Behavior.invokeElementMethod(element,'replace',[(typeof(content) == 'function' ? content() : content)]);
    }
});
Event.Behavior.Verbs.show.setOpposite(Event.Behavior.Verbs.hide);
Event.Behavior.Verbs.hide.setOpposite(Event.Behavior.Verbs.show);
Event.Behavior.Verbs.addClassName.setOpposite(Event.Behavior.Verbs.removeClassName);
Event.Behavior.Verbs.removeClassName.setOpposite(Event.Behavior.Verbs.addClassName);

Event.Behavior.Noun = Class.create();
Object.extend(Event.Behavior.Noun.prototype,{
    verbs: false,
    verb: false,
    argument: false,
    subject: false,
    target: false,
    initialize: function(verb,argument){
        //this.verbs = $A([]);
        this.verb = verb;
        this.argument = argument;
    },
    execute: function(){
        return (this.target) ? this.verb.execute(this.target,this.argument) : 
            this.verb.execute(this.argument);
    },
    executeOpposite: function(){
        return (this.target) ? 
            this.verb.executeOpposite(this.target,this.argument) : 
            this.verb.executeOpposite(this.argument);
    },
    when: function(subject){
        this.subject = subject;
        return new Event.Behavior.Adjective(this);
    },
    getValue: function(){
        return Try.these(
            function(){return $(this.subject).getValue();}.bind(this),
            function(){return $(this.subject).options[$(this.subject).options.selectedIndex].value;}.bind(this),
            function(){return $(this.subject).value;}.bind(this),
            function(){return $(this.subject).innerHTML;}.bind(this)
        );
    },
    containsValue: function(match){
        var value = this.getValue();
        if(typeof(match) == 'function'){
            return $A(match()).include(value);
        }else {
            return value.match(match); }
    },
    setTarget: function(target){
        this.target = target;
        return this;
    },
    and: function(){

    }
});
Event.Behavior.Noun.prototype._with = Event.Behavior.Noun.prototype.setTarget;
Event.Behavior.Noun.prototype.on = Event.Behavior.Noun.prototype.setTarget;
Event.Behavior.Noun.prototype.of = Event.Behavior.Noun.prototype.setTarget;
Event.Behavior.Noun.prototype.to = Event.Behavior.Noun.prototype.setTarget;
Event.Behavior.Noun.prototype.from = Event.Behavior.Noun.prototype.setTarget;

Event.Behavior.Adjective = Class.create();
Object.extend(Event.Behavior.Adjective.prototype,{
    noun: false,
    lastConditionName: '',
    nextConditionType: 'and',
    conditions: $A([]),
    events: $A([]),
    attached: false,
    initialize: function(noun){
        this.conditions = $A([]);
        this.events = $A([]);
        this.noun = noun;
    },
    attachObserver: function(execute_on_load){
        if(this.attached){
            //this may call things multiple times, but is the only way to gaurentee correct state on startup
            if(execute_on_load) {
                this.execute(); }
            return;
        }
        this.attached = true;
        if(typeof(this.noun.subject) == 'function'){
            $A(this.noun.subject()).each(function(subject){
                (this.events.length > 0 ? this.events : $A(['change'])).each(function(event_name){
                    (subject.observe ? subject : $(subject)).observe(event_name,function(){
                        this.execute();
                    }.bind(this));
                }.bind(this));
            }.bind(this));
        }else{
            (this.events.length > 0 ? this.events : $A(['change'])).each(function(event_name){
                $(this.noun.subject).observe(event_name,function(){
                    this.execute();
                }.bind(this));
            }.bind(this));
        }
        if(execute_on_load) { this.execute(); }
    },
    execute: function(){
        if(this.match()) { return this.noun.execute(); }
        else if(this.noun.verb.executeOpposite) { this.noun.executeOpposite(); }
    },
    attachCondition: function(callback){
        this.conditions.push([this.nextConditionType,callback.bind(this)]);
    },
    match: function(){
        if(this.conditions.length === 0) { return true; }
        else {
            return this.conditions.inject(false, function (bool,condition) {
                return (condition[0] === 'or') ? 
                       (bool && condition[1]()) : (bool || condition[1]());
            });
        }
    },
    //conditions
    is: function(item){
        this.lastConditionName = 'is';
        this.attachCondition(function(item){
            return (typeof(item) == 'function' ? item() : item) == this.noun.getValue();
        }.bind(this,item));
        this.attachObserver(true);
        return this;
    },
    isNot: function(item){
        this.lastConditionName = 'isNot';
        this.attachCondition(function(item){
            return (typeof(item) == 'function' ? item() : item) != this.noun.getValue();
        }.bind(this,item));
        this.attachObserver(true);
        return this;
    },
    contains: function(item){
        this.lastConditionName = 'contains';
        this.attachCondition(function(item){
            return this.noun.containsValue(item);
        }.bind(this,item));
        this.attachObserver(true);
        return this;
    },
    within: function(item){
        this.lastConditionName = 'within';
        this.attachCondition(function(item){
            
        }.bind(this,item));
        this.attachObserver(true);
        return this;
    },
    //events
    change: function(){
        this.nextConditionType = 'and';
        this.attachObserver(true);
        return this;
    },
    and: function(condition){
        this.attached = false;
        this.nextConditionType = 'and';
        if(condition) { this[this.lastConditionName](condition); }
        return this;
    },
    or: function(condition){
        this.attached = false;
        this.nextConditionType = 'or';
        if(condition) { this[this.lastConditionName](condition); }
        return this;
    }
});

Event.Behavior.addEvents({
    losesFocus: 'blur',
    gainsFocus: 'focus',
    isClicked: 'click',
    isDoubleClicked: 'dblclick',
    keyPressed: 'keypress'
});

Event.Behavior.Adjective.prototype.is_not = Event.Behavior.Adjective.prototype.isNot;
Event.Behavior.Adjective.prototype.include = Event.Behavior.Adjective.prototype.contains;
Event.Behavior.Adjective.prototype.includes = Event.Behavior.Adjective.prototype.contains;
Event.Behavior.Adjective.prototype.are = Event.Behavior.Adjective.prototype.is;
Event.Behavior.Adjective.prototype.areNot = Event.Behavior.Adjective.prototype.isNot;
Event.Behavior.Adjective.prototype.are_not = Event.Behavior.Adjective.prototype.isNot;
Event.Behavior.Adjective.prototype.changes = Event.Behavior.Adjective.prototype.change;
/**
 * @author Ryan Johnson <http://syntacticx.com/>
 * @copyright 2008 PersonalGrid Corporation <http://personalgrid.com/>
 * @package LivePipe UI
 * @license MIT
 * @url http://livepipe.net/extra/hotkey
 * @require prototype.js, livepipe.js
 */

/*global document, Prototype, Class, Event, $ */

if(typeof(Prototype) == "undefined") {
    throw "HotKey requires Prototype to be loaded."; }
if(typeof(Object.Event) == "undefined") {
    throw "HotKey requires Object.Event to be loaded."; }

var HotKey = Class.create({
    initialize: function(letter,callback,options){
        letter = letter.toUpperCase();
        HotKey.hotkeys.push(this);
        this.options = Object.extend({
            element: false,
            shiftKey: false,
            altKey: false,
            ctrlKey: true,
            bubbleEvent : true,
            fireOnce : false // Keep repeating event while key is pressed?
        },options || {});
        this.letter = letter;

        // All custom hotkey events should stop after their custom actions.
        this.callback = function (event) {
            if (!(this.options.fireOnce && this.fired) && Object.isFunction(callback)) { 
                callback(event); 
            }
            if (!this.options.bubbleEvent) { event.stop(); }
            this.fired = true;
        };

        this.element = $(this.options.element || document);
        this.handler = function(event){
            if(!event || (
                (Event['KEY_' + this.letter] || this.letter.charCodeAt(0)) == event.keyCode &&
                ((!this.options.shiftKey || (this.options.shiftKey && event.shiftKey)) &&
                    (!this.options.altKey || (this.options.altKey && event.altKey)) &&
                    (!this.options.ctrlKey || (this.options.ctrlKey && event.ctrlKey))
                )
            )){
                if(this.notify('beforeCallback',event) === false) {
                    return; }
                this.callback(event);
                this.notify('afterCallback',event);
            }
        }.bind(this);
        this.enable();
    },
    trigger: function(){
        this.handler();
    },
    enable: function(){
        this.element.observe('keydown',this.handler);
    },
    disable: function(){
        this.element.stopObserving('keydown',this.handler);
    },
    destroy: function(){
        this.disable();
        HotKey.hotkeys = HotKey.hotkeys.without(this);
    }
});
Object.extend(HotKey,{
    hotkeys: []
});
Object.Event.extend(HotKey);
/**
 * @author Ryan Johnson <http://syntacticx.com/>
 * @copyright 2008 PersonalGrid Corporation <http://personalgrid.com/>
 * @package LivePipe UI
 * @license MIT
 * @url http://livepipe.net/control/progressbar
 * @require prototype.js, livepipe.js
 */

/*global document, Prototype, Ajax, Class, PeriodicalExecuter, $, $A, Control */

if(typeof(Prototype) == "undefined") {
    throw "Control.ProgressBar requires Prototype to be loaded."; }
if(typeof(Object.Event) == "undefined") {
    throw "Control.ProgressBar requires Object.Event to be loaded."; }

Control.ProgressBar = Class.create({
    initialize: function(container,options){
        this.progress = 0;
        this.executer = false;
        this.active = false;
        this.poller = false;
        this.container = $(container);
        this.containerWidth = this.container.getDimensions().width - (parseInt(this.container.getStyle('border-right-width').replace(/px/,''), 10) + parseInt(this.container.getStyle('border-left-width').replace(/px/,''), 10));
        this.progressContainer = $(document.createElement('div'));
        this.progressContainer.setStyle({
            width: this.containerWidth + 'px',
            height: '100%',
            position: 'absolute',
            top: '0px',
            right: '0px'
        });
        this.container.appendChild(this.progressContainer);
        this.options = {
            afterChange: Prototype.emptyFunction,
            interval: 0.25,
            step: 1,
            classNames: {
                active: 'progress_bar_active',
                inactive: 'progress_bar_inactive'
            }
        };
        Object.extend(this.options,options || {});
        this.container.addClassName(this.options.classNames.inactive);
        this.active = false;
    },
    setProgress: function(value){
        this.progress = value;
        this.draw();
        if(this.progress >= 100) {
            this.stop(false); }
        this.notify('afterChange',this.progress,this.active);
    },
    poll: function (url, interval, ajaxOptions){
        // Extend the passed ajax options and success callback with our own.
        ajaxOptions = ajaxOptions || {};
        var success = ajaxOptions.onSuccess || Prototype.emptyFunction;
        ajaxOptions.onSuccess = success.wrap(function (callOriginal, request) {
            this.setProgress(parseInt(request.responseText, 10));
            if(!this.active) { this.poller.stop(); }
            callOriginal(request);
        }).bind(this);

        this.active = true;
        this.poller = new PeriodicalExecuter(function(){
            var a = new Ajax.Request(url, ajaxOptions);
        }.bind(this),interval || 3);
    },
    start: function(){
        this.active = true;
        this.container.removeClassName(this.options.classNames.inactive);
        this.container.addClassName(this.options.classNames.active);
        this.executer = new PeriodicalExecuter(this.step.bind(this,this.options.step),this.options.interval);
    },
    stop: function(reset){
        this.active = false;
        if(this.executer) {
            this.executer.stop(); }
        this.container.removeClassName(this.options.classNames.active);
        this.container.addClassName(this.options.classNames.inactive);
        if (typeof reset  === 'undefined' || reset === true) {
            this.reset(); }
    },
    step: function(amount){
        this.active = true;
        this.setProgress(Math.min(100,this.progress + amount));
    },
    reset: function(){
        this.active = false;
        this.setProgress(0);
    },
    draw: function(){
        this.progressContainer.setStyle({
            width: (this.containerWidth - Math.floor((parseInt(this.progress, 10) / 100) * this.containerWidth)) + 'px'
        });
    },
    notify: function(event_name){
        if(this.options[event_name]) {
            return [this.options[event_name].apply(this.options[event_name],$A(arguments).slice(1))]; }
    }
});
Object.Event.extend(Control.ProgressBar);
/**
 * @author Ryan Johnson <http://syntacticx.com/>
 * @copyright 2008 PersonalGrid Corporation <http://personalgrid.com/>
 * @package LivePipe UI
 * @license MIT
 * @url http://livepipe.net/control/rating
 * @require prototype.js, livepipe.js
 */

/*global document, Prototype, Ajax, Class, Event, $, $A, $F, $R, $break, Control */

if(typeof(Prototype) == "undefined") {
    throw "Control.Rating requires Prototype to be loaded."; }
if(typeof(Object.Event) == "undefined") {
    throw "Control.Rating requires Object.Event to be loaded."; }

Control.Rating = Class.create({
    initialize: function(container,options){
        Control.Rating.instances.push(this);
        this.value = false;
        this.links = [];
        this.container = $(container);
        this.container.update('');
        this.options = {
            min: 1,
            max: 5,
            rated: false,
            input: false,
            reverse: false,
            capture: true,
            multiple: false,
            classNames: {
                off: 'rating_off',
                half: 'rating_half',
                on: 'rating_on',
                selected: 'rating_selected'
            },
            updateUrl: false,
            updateParameterName: 'value',
            updateOptions : {},
            afterChange: Prototype.emptyFunction
        };
        Object.extend(this.options,options || {});
        if(this.options.value){
            this.value = this.options.value;
            delete this.options.value;
        }
        if(this.options.input){
            this.options.input = $(this.options.input);
            this.options.input.observe('change',function(input){
                this.setValueFromInput(input);
            }.bind(this,this.options.input));
            this.setValueFromInput(this.options.input,true);
        }
        var range = $R(this.options.min,this.options.max);
        (this.options.reverse ? $A(range).reverse() : range).each(function(i){
            var link = this.buildLink(i);
            this.container.appendChild(link);
            this.links.push(link);
        }.bind(this));
        this.setValue(this.value || this.options.min - 1,false,true);
    },
    buildLink: function(rating){
        var link = $(document.createElement('a'));
        link.value = rating;
        if(this.options.multiple || (!this.options.rated && !this.options.multiple)){
            link.href = '';
            link.onmouseover = this.mouseOver.bind(this,link);
            link.onmouseout = this.mouseOut.bind(this,link);
            link.onclick = this.click.bindAsEventListener(this,link);
        }else{
            link.style.cursor = 'default';
            link.observe('click',function(event){
                Event.stop(event);
                return false;
            }.bindAsEventListener(this));
        }
        link.addClassName(this.options.classNames.off);
        return link;
    },
    disable: function(){
        this.links.each(function(link){
            link.onmouseover = Prototype.emptyFunction;
            link.onmouseout = Prototype.emptyFunction;
            link.onclick = Prototype.emptyFunction;
            link.observe('click',function(event){
                Event.stop(event);
                return false;
            }.bindAsEventListener(this));
            link.style.cursor = 'default';
        }.bind(this));
    },
    setValueFromInput: function(input,prevent_callbacks){
        this.setValue($F(input),true,prevent_callbacks);
    },
    setValue: function(value,force_selected,prevent_callbacks){
        this.value = value;
        if(this.options.input){
            if(this.options.input.options){
                $A(this.options.input.options).each(function(option,i){
                    if(option.value == this.value){
                        this.options.input.options.selectedIndex = i;
                        throw $break;
                    }
                }.bind(this));
            }else {
                this.options.input.value = this.value; }
        }
        this.render(this.value,force_selected);
        if(!prevent_callbacks){
            if(this.options.updateUrl){
                var params = {}, a;
                params[this.options.updateParameterName] = this.value;
                a = new Ajax.Request(this.options.updateUrl, Object.extend(
                    this.options.updateOptions, { parameters : params }
                ));
            }
            this.notify('afterChange',this.value);
        }
    },
    render: function(rating,force_selected){
        (this.options.reverse ? this.links.reverse() : this.links).each(function(link,i){
            if(link.value <= Math.ceil(rating)){
                link.className = this.options.classNames[link.value <= rating ? 'on' : 'half'];
                if(this.options.rated || force_selected) {
                    link.addClassName(this.options.classNames.selected); }
            }else {
                link.className = this.options.classNames.off; }
        }.bind(this));
    },
    mouseOver: function(link){
        this.render(link.value,true);
    },
    mouseOut: function(link){
        this.render(this.value);
    },
    click: function(event,link){
        this.options.rated = true;
        this.setValue((link.value ? link.value : link),true);
        if(!this.options.multiple) {
            this.disable(); }
        if(this.options.capture){
            Event.stop(event);
            return false;
        }
    }
});
Object.extend(Control.Rating,{
    instances: [],
    findByElementId: function(id){
        return Control.Rating.instances.find(function(instance){
            return (instance.container.id && instance.container.id == id);
        });
    }
});
Object.Event.extend(Control.Rating);

// script.aculo.us Resizables.js

// Copyright(c) 2007 - Orr Siloni, Comet Information Systems http://www.comet.co.il/en/
//
// Resizable.js is freely distributable under the terms of an MIT-style license.
// For details, see the script.aculo.us web site: http://script.aculo.us/

var Resizables = {
	instances: [],
	observers: [],
	
	register: function(resizable) {
		if(this.instances.length == 0) {
			this.eventMouseUp   = this.endResize.bindAsEventListener(this);
			this.eventMouseMove = this.updateResize.bindAsEventListener(this);
			
			Event.observe(document, "mouseup", this.eventMouseUp);
			Event.observe(document, "mousemove", this.eventMouseMove);
		}
		this.instances.push(resizable);
	},
	
	unregister: function(resizable) {
		this.instances = this.instances.reject(function(d) { return d==resizable });
		if(this.instances.length == 0) {
			Event.stopObserving(document, "mouseup", this.eventMouseUp);
			Event.stopObserving(document, "mousemove", this.eventMouseMove);
		}
	},
	
	activate: function(resizable) {
		if(resizable.options.delay) { 
			this._timeout = setTimeout(function() {
				Resizables._timeout = null; 
				Resizables.activeResizable = resizable; 
			}.bind(this), resizable.options.delay); 
		} else {
			this.activeResizable = resizable;
		}
	},
	
	deactivate: function() {
		this.activeResizable = null;
	},
	
	updateResize: function(event) {
		if(!this.activeResizable) return;
		var pointer = [Event.pointerX(event), Event.pointerY(event)];
		// Mozilla-based browsers fire successive mousemove events with
		// the same coordinates, prevent needless redrawing (moz bug?)
		if(this._lastPointer && (this._lastPointer.inspect() == pointer.inspect())) return;
		this._lastPointer = pointer;
		
		this.activeResizable.updateResize(event, pointer);
	},
	
	endResize: function(event) {
		if(this._timeout) { 
		  clearTimeout(this._timeout); 
		  this._timeout = null; 
		}
		if(!this.activeResizable) return;
		this._lastPointer = null;
		this.activeResizable.endResize(event);
		this.activeResizable = null;
	},
	
	addObserver: function(observer) {
		this.observers.push(observer);
		this._cacheObserverCallbacks();
	},
  
	removeObserver: function(element) {  // element instead of observer fixes mem leaks
		this.observers = this.observers.reject( function(o) { return o.element==element });
		this._cacheObserverCallbacks();
	},
	
	notify: function(eventName, resizable, event) {  // 'onStart', 'onEnd', 'onResize'
		if(this[eventName+'Count'] > 0)
			this.observers.each( function(o) {
				if(o[eventName]) o[eventName](eventName, resizable, event);
			});
		if(resizable.options[eventName]) resizable.options[eventName](resizable, event);
	},
	
	_cacheObserverCallbacks: function() {
		['onStart','onEnd','onResize'].each( function(eventName) {
			Resizables[eventName+'Count'] = Resizables.observers.select(
				function(o) { return o[eventName]; }
			).length;
		});
	}
}

var Resizable = Class.create();
Resizable._resizing = {};

Resizable.prototype = {
	initialize: function(element){
		var defaults = {
			handle: false,
			snap: false,  // false, or xy or [x,y] or function(x,y){ return [x,y] }
			delay: 0,
			minHeight: false,
			minwidth: false,
			maxHeight: false,
			maxWidth: false
		}
		
		this.element = $(element);
		
		var options = Object.extend(defaults, arguments[1] || {});
		if(options.handle && typeof options.handle == 'string')
			this.handle = $(options.handle);
		else if(options.handle)
			this.handle = options.handle;
			
		if(!this.handle) this.handle = this.element;
		
		this.options  = options;
		this.dragging = false;
		
		this.eventMouseDown = this.initResize.bindAsEventListener(this);
		Event.observe(this.handle, "mousedown", this.eventMouseDown);
		
		Resizables.register(this);
	},
	
	destroy: function() {
		Event.stopObserving(this.handle, "mousedown", this.eventMouseDown);
	},
	
	currentDelta: function() {
		return([
			parseInt(Element.getStyle(this.element,'width') || '0'),
			parseInt(Element.getStyle(this.element,'height') || '0')]);
	},
	
	initResize: function(event) {
		if(typeof Resizable._resizing[this.element] != 'undefined' &&
			Resizable._resizing[this.element]) return;
		if(Event.isLeftClick(event)) {
			// abort on form elements, fixes a Firefox issue
			var src = Event.element(event);
			if((tag_name = src.tagName.toUpperCase()) && (
				tag_name=='INPUT' || tag_name=='SELECT' || tag_name=='OPTION' ||
				tag_name=='BUTTON' || tag_name=='TEXTAREA')) return;
			
			this.pointer = [Event.pointerX(event), Event.pointerY(event)];
			this.size = [parseInt(this.element.getStyle('width')) || 0, parseInt(this.element.getStyle('height')) || 0];
			
			Resizables.activate(this);
			Event.stop(event);
		}
	},
	
	startResize: function(event) {
		this.resizing = true;
		if(this.options.zindex) {
			this.originalZ = parseInt(Element.getStyle(this.element,'z-index') || 0);
			this.element.style.zIndex = this.options.zindex;
		}
		Resizables.notify('onStart', this, event);
		Resizable._resizing[this.element] = true;
	},
	
	updateResize: function(event, pointer) {
		if(!this.resizing) this.startResize(event);
		
		Resizables.notify('onResize', this, event);
		
		this.draw(pointer);
		if(this.options.change) this.options.change(this);
		
		// fix AppleWebKit rendering
		if(Prototype.Browser.WebKit) window.scrollBy(0,0);
		Event.stop(event);
	},
	
	finishResize: function(event, success) {
		this.resizing = false;
		Resizables.notify('onEnd', this, event);
		if(this.options.zindex) this.element.style.zIndex = this.originalZ;
		Resizable._resizing[this.element] = false;
		Resizables.deactivate(this);
	},
	
	endResize: function(event) {
		if(!this.resizing) return;
		this.finishResize(event, true);
		Event.stop(event);
	},
	
	draw: function(point) {
		var p = [0,1].map(function(i){ 
			return (this.size[i] + point[i] - this.pointer[i]);
		}.bind(this));
		
		if(this.options.snap) {
			if(typeof this.options.snap == 'function') {
				p = this.options.snap(p[0],p[1],this);
			} else {
				if(this.options.snap instanceof Array) {
				p = p.map( function(v, i) {
				return Math.round(v/this.options.snap[i])*this.options.snap[i] }.bind(this))
			} else {
				p = p.map( function(v) {
				return Math.round(v/this.options.snap)*this.options.snap }.bind(this))
			}
		}}
		
		var minWidth = (typeof(this.options.minWidth) == 'function') ? this.options.minWidth(this.element) : this.options.minWidth;
		var maxWidth = (typeof(this.options.maxWidth) == 'function') ? this.options.maxWidth(this.element) : this.options.maxWidth;
		var minHeight = (typeof(this.options.minHeight) == 'function') ? this.options.minHeight(this.element) : this.options.minHeight;
		var maxHeight = (typeof(this.options.maxHeight) == 'function') ? this.options.maxHeight(this.element) : this.options.maxHeight;

		if (minWidth && p[0] <= minWidth) p[0] = minWidth;
		if (maxWidth && p[0] >= maxWidth) p[0] = maxWidth;
		if (minHeight && p[1] <= minHeight) p[1] = minHeight;
		if (maxHeight && p[1] >= maxHeight) p[1] = maxHeight;
		
		var style = this.element.style;
		if((!this.options.constraint) || (this.options.constraint=='horizontal')){
			style.width = p[0] + "px";
		}
		if((!this.options.constraint) || (this.options.constraint=='vertical')){
			style.height = p[1] + "px";
		}
		
		if(style.visibility=="hidden") style.visibility = ""; // fix gecko rendering
	}
};
/**
 * @author Ryan Johnson <http://syntacticx.com/>
 * @copyright 2008 PersonalGrid Corporation <http://personalgrid.com/>
 * @package LivePipe UI
 * @license MIT
 * @url http://livepipe.net/control/scrollbar
 * @require prototype.js, slider.js, livepipe.js
 */

if(typeof(Prototype) == "undefined")
    throw "Control.ScrollBar requires Prototype to be loaded.";
if(typeof(Control.Slider) == "undefined")
    throw "Control.ScrollBar requires Control.Slider to be loaded.";
if(typeof(Object.Event) == "undefined")
    throw "Control.ScrollBar requires Object.Event to be loaded.";

Control.ScrollBar = Class.create({
    initialize: function(container,track,options){
        this.enabled = false;
        this.notificationTimeout = false;
        this.container = $(container);
        this.boundMouseWheelEvent = this.onMouseWheel.bindAsEventListener(this);
        this.boundResizeObserver = this.onWindowResize.bind(this);
        this.track = $(track);
        this.handle = this.track.firstDescendant();
        this.options = Object.extend({
            active_class_name: 'scrolling',
            apply_active_class_name_to: this.container,
            notification_timeout_length: 125,
            handle_minimum_height: 25,
            scroll_to_smoothing: 0.01,
            scroll_to_steps: 15,
            proportional: true,
            slider_options: {}
        },options || {});
        this.slider = new Control.Slider(this.handle,this.track,Object.extend({
            axis: 'vertical',
            onSlide: this.onChange.bind(this),
            onChange: this.onChange.bind(this)
        },this.options.slider_options));
        this.recalculateLayout();
        Event.observe(window,'resize',this.boundResizeObserver);
        this.handle.observe('mousedown',function(){
            if(this.auto_sliding_executer)
                this.auto_sliding_executer.stop();
        }.bind(this));
    },
    destroy: function(){
        Event.stopObserving(window,'resize',this.boundResizeObserver);
    },
    enable: function(){
        this.enabled = true;
        this.container.observe('mouse:wheel',this.boundMouseWheelEvent);
        this.slider.setEnabled();
        this.track.show();
        if(this.options.active_class_name)
            $(this.options.apply_active_class_name_to).addClassName(this.options.active_class_name);
        this.notify('enabled');
    },
    disable: function(){
        this.enabled = false;
        this.container.stopObserving('mouse:wheel',this.boundMouseWheelEvent);
        this.slider.setDisabled();
        this.track.hide();
        if(this.options.active_class_name)
            $(this.options.apply_active_class_name_to).removeClassName(this.options.active_class_name);
        this.notify('disabled');
        this.reset();
    },
    reset: function(){
        this.slider.setValue(0);
    },
    recalculateLayout: function(){
        if(this.container.scrollHeight <= this.container.offsetHeight)
            this.disable();
        else{
            this.enable();
            this.slider.trackLength = this.slider.maximumOffset() - this.slider.minimumOffset();
            if(this.options.proportional){
                this.handle.style.height = Math.max(this.container.offsetHeight * (this.container.offsetHeight / this.container.scrollHeight),this.options.handle_minimum_height) + 'px';
                this.slider.handleLength = this.handle.style.height.replace(/px/,'');
            }
        }
    },
    onWindowResize: function(){
        this.recalculateLayout();
        this.scrollBy(0);
    },
    onMouseWheel: function(event){
        if(this.auto_sliding_executer)
            this.auto_sliding_executer.stop();
        this.slider.setValueBy(-(event.memo.delta / 20)); //put in math to account for the window height
        event.stop();
        return false;
    },
    onChange: function(value){
        this.container.scrollTop = Math.round(value / this.slider.maximum * (this.container.scrollHeight - this.container.offsetHeight));
        if(this.notification_timeout)
            window.clearTimeout(this.notificationTimeout);
        this.notificationTimeout = window.setTimeout(function(){
            this.notify('change',value);
        }.bind(this),this.options.notification_timeout_length);
    },
    getCurrentMaximumDelta: function(){
        return this.slider.maximum * (this.container.scrollHeight - this.container.offsetHeight);
    },
    getDeltaToElement: function(element){
        return this.slider.maximum * ((element.positionedOffset().top + (element.getHeight() / 2)) - (this.container.getHeight() / 2));
    },
    scrollTo: function(y,animate){
        var current_maximum_delta = this.getCurrentMaximumDelta();
        if(y == 'top')
            y = 0;
        else if(y == 'bottom')
            y = current_maximum_delta;
        else if(typeof(y) != "number")
            y = this.getDeltaToElement($(y));
        if(this.enabled){
            y = Math.max(0,Math.min(y,current_maximum_delta));
            if(this.auto_sliding_executer)
                this.auto_sliding_executer.stop();
            var target_value = y / current_maximum_delta;
            var original_slider_value = this.slider.value;
            var delta = (target_value - original_slider_value) * current_maximum_delta;
            if(animate){
                this.auto_sliding_executer = new PeriodicalExecuter(function(){
                    if(Math.round(this.slider.value * 100) / 100 < Math.round(target_value * 100) / 100 || Math.round(this.slider.value * 100) / 100 > Math.round(target_value * 100) / 100){
                        this.scrollBy(delta / this.options.scroll_to_steps);
                    }else{
                        this.auto_sliding_executer.stop();
                        this.auto_sliding_executer = null;
                        if(typeof(animate) == "function")
                            animate();
                    }            
                }.bind(this),this.options.scroll_to_smoothing);
            }else
                this.scrollBy(delta);
        }else if(typeof(animate) == "function")
            animate();
    },
    scrollBy: function(y){
        if(!this.enabled)
            return false;
        this.slider.setValueBy(y / this.getCurrentMaximumDelta());
    }
});
Object.Event.extend(Control.ScrollBar);
/**
 * @author Ryan Johnson <http://syntacticx.com/>
 * @copyright 2008 PersonalGrid Corporation <http://personalgrid.com/>
 * @package LivePipe UI
 * @license MIT
 * @url http://livepipe.net/control/selection
 * @require prototype.js, effects.js, draggable.js, livepipe.js
 */

/*global window, document, Prototype, Element, Event, $, $$, $break, Control, Draggable */

if(typeof(Prototype) == "undefined") {
    throw "Control.Selection requires Prototype to be loaded."; }
if(typeof(Object.Event) == "undefined") {
    throw "Control.Selection requires Object.Event to be loaded."; }

Control.Selection = {
    options: {
        resize_layout_timeout: 125,
        selected: Prototype.emptyFunction,
        deselected: Prototype.emptyFunction,
        change: Prototype.emptyFunction,
        selection_id: 'control_selection',
        selection_style: {
            zIndex: 999,
            cursor: 'default',
            border: '1px dotted #000'
        },
        filter: function(element){
            return true;
        },
        drag_proxy: false,
        drag_proxy_threshold: 1,
        drag_proxy_options: {}
    },
    selectableElements: [],
    elements: [],
    selectableObjects: [],
    objects: [],
    active: false,
    container: false,
    resizeTimeout: false,
    load: function(options){
        Control.Selection.options = Object.extend(Control.Selection.options,options || {});
        Control.Selection.selection_div = $(document.createElement('div'));
        Control.Selection.selection_div.id = Control.Selection.options.selection_id;
        Control.Selection.selection_div.style.display = 'none';
        Control.Selection.selection_div.setStyle(Control.Selection.options.selection_style);
        Control.Selection.border_width = parseInt(Control.Selection.selection_div.getStyle('border-top-width'), 10) * 2;
        Control.Selection.container = Prototype.Browser.IE ? window.container : window;
        $(document.body).insert(Control.Selection.selection_div);
        Control.Selection.enable();
        if(Control.Selection.options.drag_proxy && typeof(Draggable) != 'undefined') {
            Control.Selection.DragProxy.load(); }
        Event.observe(window,'resize',function(){
            if(Control.Selection.resizeTimeout) {
                window.clearTimeout(Control.Selection.resizeTimeout); }
            Control.Selection.resizeTimeout = window.setTimeout(Control.Selection.recalculateLayout,Control.Selection.options.resize_layout_timeout);
        });
        if(Prototype.Browser.IE){
            var body = $$('body').first();
            body.observe('mouseleave',Control.Selection.stop);
            body.observe('mouseup',Control.Selection.stop);
        }
    },
    enable: function(){
        if(Prototype.Browser.IE){
            document.onselectstart = function(){
                return false;
            };
        }
        Event.observe(Control.Selection.container,'mousedown',Control.Selection.start);
        Event.observe(Control.Selection.container,'mouseup',Control.Selection.stop);
    },
    disable: function(){
        if(Prototype.Browser.IE){
            document.onselectstart = function(){
                return true;
            };
        }
        Event.stopObserving(Control.Selection.container,'mousedown',Control.Selection.start);
        Event.stopObserving(Control.Selection.container,'mouseup',Control.Selection.stop);
    },
    recalculateLayout: function(){
        Control.Selection.selectableElements.each(function(element){
            var dimensions = element.getDimensions();
            var offset = element.cumulativeOffset();
            var scroll_offset = element.cumulativeScrollOffset();
            if(!element._control_selection) {
                element._control_selection = {}; }
            element._control_selection.top = offset[1] - scroll_offset[1];
            element._control_selection.left = offset[0] - scroll_offset[0];
            element._control_selection.width = dimensions.width;
            element._control_selection.height = dimensions.height;
        });
    },
    addSelectable: function(element,object,activation_targets,activation_target_callback){
        element = $(element);
        if(activation_targets) {
            activation_targets = activation_targets.each ? activation_targets : [activation_targets]; }
        var dimensions = element.getDimensions();
        var offset = Element.cumulativeOffset(element);
        element._control_selection = {
            activation_targets: activation_targets,
            is_selected: false,
            top: offset[1],
            left: offset[0],
            width: dimensions.width,
            height: dimensions.height,
            activationTargetMouseMove: function(){
                Control.Selection.notify('activationTargetMouseMove',element);
                if(activation_targets){
                    activation_targets.each(function(activation_target){
                        activation_target.stopObserving('mousemove',element._control_selection.activationTargetMouseMove);
                    });
                }
                Control.Selection.DragProxy.container.stopObserving('mousemove',element._control_selection.activationTargetMouseMove);
            },
            activationTargetMouseDown: function(event){
                if(!Control.Selection.elements.include(element)) {
                    Control.Selection.select(element); }
                Control.Selection.DragProxy.start(event);
                Control.Selection.DragProxy.container.hide();
                if(activation_targets){
                    activation_targets.each(function(activation_target){
                        activation_target.observe('mousemove',element._control_selection.activationTargetMouseMove);
                    });
                }
                Control.Selection.DragProxy.container.observe('mousemove',element._control_selection.activationTargetMouseMove);
            },
            activationTargetClick: function(){
                Control.Selection.select(element);
                if(typeof(activation_target_callback) == "function") {
                    activation_target_callback(); }
                if(activation_targets){
                    activation_targets.each(function(activation_target){
                        activation_target.stopObserving('mousemove',element._control_selection.activationTargetMouseMove);
                    });
                }
                Control.Selection.DragProxy.container.stopObserving('mousemove',element._control_selection.activationTargetMouseMove);
            }
        };
        element.onselectstart = function(){
            return false;
        };
        element.unselectable = 'on';
        element.style.MozUserSelect = 'none';
        if(activation_targets){
            activation_targets.each(function(activation_target){
                activation_target.observe('mousedown',element._control_selection.activationTargetMouseDown);
                activation_target.observe('click',element._control_selection.activationTargetClick);
            });
        }
        Control.Selection.selectableElements.push(element);
        Control.Selection.selectableObjects.push(object);
    },
    removeSelectable: function(element){
        element = $(element);
        if(element._control_selection.activation_targets){
            element._control_selection.activation_targets.each(function(activation_target){
                activation_target.stopObserving('mousedown',element._control_selection.activationTargetMouseDown);
            });
            element._control_selection.activation_targets.each(function(activation_target){
                activation_target.stopObserving('click',element._control_selection.activationTargetClick);
            });
        }
        element._control_selection = null;
        element.onselectstart = function() {
            return true;
        };
        element.unselectable = 'off';
        element.style.MozUserSelect = '';
        var position = 0;
        Control.Selection.selectableElements.each(function(selectable_element,i){
            if(selectable_element == element){
                position = i;
                throw $break;
            }
        });
        Control.Selection.selectableElements = Control.Selection.selectableElements.without(element);
        Control.Selection.selectableObjects = Control.Selection.selectableObjects.slice(0,position).concat(Control.Selection.selectableObjects.slice(position + 1));
    },
    select: function(selected_elements){
        if(typeof(selected_elements) == "undefined" || !selected_elements) {
            selected_elements = []; }
        if(!selected_elements.each && !selected_elements._each) {
            selected_elements = [selected_elements]; }
        //comparing the arrays directly wouldn't equate to true in safari so we need to compare each item
        var selected_items_have_changed = !(Control.Selection.elements.length == selected_elements.length && Control.Selection.elements.all(function(item,i){
            return selected_elements[i] == item;
        }));
        if(!selected_items_have_changed) {
            return; }
        var selected_objects_indexed_by_element = {};
        var selected_objects = selected_elements.collect(function(selected_element){
            var selected_object = Control.Selection.selectableObjects[Control.Selection.selectableElements.indexOf(selected_element)];
            selected_objects_indexed_by_element[selected_element] = selected_object;
            return selected_object;
        });
        if(Control.Selection.elements.length === 0 && selected_elements.length !== 0){
            selected_elements.each(function(element){
                Control.Selection.notify('selected',element,selected_objects_indexed_by_element[element]);
            });
        }else{
            Control.Selection.elements.each(function(element){
                if(!selected_elements.include(element)){
                    Control.Selection.notify('deselected',element,selected_objects_indexed_by_element[element]);
                }
            });
            selected_elements.each(function(element){
                if(!Control.Selection.elements.include(element)){
                    Control.Selection.notify('selected',element,selected_objects_indexed_by_element[element]);
                }
            });
        }
        Control.Selection.elements = selected_elements;
        Control.Selection.objects = selected_objects;
        Control.Selection.notify('change',Control.Selection.elements,Control.Selection.objects);
    },
    deselect: function(){
        if(Control.Selection.notify('deselect') === false) {
            return false; }
        Control.Selection.elements.each(function(element){
            Control.Selection.notify('deselected',element,Control.Selection.selectableObjects[Control.Selection.selectableElements.indexOf(element)]);
        });
        Control.Selection.objects = [];
        Control.Selection.elements = [];
        Control.Selection.notify('change',Control.Selection.objects,Control.Selection.elements);
        return true;
    },
    //private
    start: function(event){
        if(!event.isLeftClick() || Control.Selection.notify('start',event) === false) {
            return false; }
        if(!event.shiftKey && !event.altKey) {
            Control.Selection.deselect(); }
        Event.observe(Control.Selection.container,'mousemove',Control.Selection.onMouseMove);
        Event.stop(event);
        return false;
    },
    stop: function(){
        Event.stopObserving(Control.Selection.container,'mousemove',Control.Selection.onMouseMove);
        Control.Selection.active = false;
        Control.Selection.selection_div.setStyle({
            display: 'none',
            top: null,
            left: null,
            width: null,
            height: null
        });
        Control.Selection.start_mouse_coordinates = {};
        Control.Selection.current_mouse_coordinates = {};
    },
    mouseCoordinatesFromEvent: function(event){
        return {
            x: Event.pointerX(event),
            y: Event.pointerY(event)
        };
    },
    onClick: function(event,element,source){
        var selection = [];
        if(event.shiftKey){
            selection = Control.Selection.elements.clone();
            if(!selection.include(element)) {
                selection.push(element); }
        }else if(event.altKey){
            selection = Control.Selection.elements.clone();
            if(selection.include(element)) {
                selection = selection.without(element); }
        }else{
            selection = [element];
        }
        Control.Selection.select(selection);
        if(source == 'click') {
            Event.stop(event); }
    },
    onMouseMove: function(event){
        if(!Control.Selection.active){
            Control.Selection.active = true;
            Control.Selection.start_mouse_coordinates = Control.Selection.mouseCoordinatesFromEvent(event);
        }else{
            Control.Selection.current_mouse_coordinates = Control.Selection.mouseCoordinatesFromEvent(event);
            Control.Selection.drawSelectionDiv();
            var current_selection = Control.Selection.selectableElements.findAll(function(element){
                return Control.Selection.options.filter(element) && Control.Selection.elementWithinSelection(element);
            });
            if(event.shiftKey && !event.altKey){
                Control.Selection.elements.each(function(element){
                    if(!current_selection.include(element)) {
                        current_selection.push(element); }
                });
            }else if(event.altKey && !event.shiftKey){
                current_selection = Control.Selection.elements.findAll(function(element){
                    return !current_selection.include(element);
                });
            }
            Control.Selection.select(current_selection);
        }
    },
    drawSelectionDiv: function(){
        if(Control.Selection.start_mouse_coordinates == Control.Selection.current_mouse_coordinates){
            Control.Selection.selection_div.style.display = 'none';
        }else{
            Control.Selection.viewport = document.viewport.getDimensions();
            Control.Selection.selection_div.style.position = 'absolute';
            Control.Selection.current_direction = (Control.Selection.start_mouse_coordinates.y > Control.Selection.current_mouse_coordinates.y ? 'N' : 'S') + (Control.Selection.start_mouse_coordinates.x < Control.Selection.current_mouse_coordinates.x ? 'E' : 'W');
            Control.Selection.selection_div.setStyle(Control.Selection['dimensionsFor' + Control.Selection.current_direction]());
            Control.Selection.selection_div.style.display = 'block';
        }
    },
    dimensionsForNW: function(){
        return {
            top: (Control.Selection.start_mouse_coordinates.y - (Control.Selection.start_mouse_coordinates.y - Control.Selection.current_mouse_coordinates.y)) + 'px',
            left: (Control.Selection.start_mouse_coordinates.x - (Control.Selection.start_mouse_coordinates.x - Control.Selection.current_mouse_coordinates.x)) + 'px',
            width: (Control.Selection.start_mouse_coordinates.x - Control.Selection.current_mouse_coordinates.x) + 'px',
            height: (Control.Selection.start_mouse_coordinates.y - Control.Selection.current_mouse_coordinates.y) + 'px'
        };
    },
    dimensionsForNE: function(){
        return {
            top: (Control.Selection.start_mouse_coordinates.y - (Control.Selection.start_mouse_coordinates.y - Control.Selection.current_mouse_coordinates.y)) + 'px',
            left: Control.Selection.start_mouse_coordinates.x + 'px',
            width: Math.min((Control.Selection.viewport.width - Control.Selection.start_mouse_coordinates.x) - Control.Selection.border_width,Control.Selection.current_mouse_coordinates.x - Control.Selection.start_mouse_coordinates.x) + 'px',
            height: (Control.Selection.start_mouse_coordinates.y - Control.Selection.current_mouse_coordinates.y) + 'px'
        };
    },
    dimensionsForSE: function(){
        return {
            top: Control.Selection.start_mouse_coordinates.y + 'px',
            left: Control.Selection.start_mouse_coordinates.x + 'px',
            width: Math.min((Control.Selection.viewport.width - Control.Selection.start_mouse_coordinates.x) - Control.Selection.border_width,Control.Selection.current_mouse_coordinates.x - Control.Selection.start_mouse_coordinates.x) + 'px',
            height: Math.min((Control.Selection.viewport.height - Control.Selection.start_mouse_coordinates.y) - Control.Selection.border_width,Control.Selection.current_mouse_coordinates.y - Control.Selection.start_mouse_coordinates.y) + 'px'
        };
    },
    dimensionsForSW: function(){
        return {
            top: Control.Selection.start_mouse_coordinates.y + 'px',
            left: (Control.Selection.start_mouse_coordinates.x - (Control.Selection.start_mouse_coordinates.x - Control.Selection.current_mouse_coordinates.x)) + 'px',
            width: (Control.Selection.start_mouse_coordinates.x - Control.Selection.current_mouse_coordinates.x) + 'px',
            height: Math.min((Control.Selection.viewport.height - Control.Selection.start_mouse_coordinates.y) - Control.Selection.border_width,Control.Selection.current_mouse_coordinates.y - Control.Selection.start_mouse_coordinates.y) + 'px'
        };
    },
    inBoundsForNW: function(element,selection){
        return (
            ((element.left > selection.left || element.right > selection.left) && selection.right > element.left) &&
            ((element.top > selection.top || element.bottom > selection.top) && selection.bottom > element.top)
        );
    },
    inBoundsForNE: function(element,selection){
        return (
            ((element.left < selection.right || element.left < selection.right) && selection.left < element.right) &&
            ((element.top > selection.top || element.bottom > selection.top) && selection.bottom > element.top)
        );
    },
    inBoundsForSE: function(element,selection){
        return (
            ((element.left < selection.right || element.left < selection.right) && selection.left < element.right) &&
            ((element.bottom < selection.bottom || element.top < selection.bottom) && selection.top < element.bottom)
        );
    },
    inBoundsForSW: function(element,selection){
        return (
            ((element.left > selection.left || element.right > selection.left) && selection.right > element.left) &&
            ((element.bottom < selection.bottom || element.top < selection.bottom) && selection.top < element.bottom)
        );
    },
    elementWithinSelection: function(element){
        if(Control.Selection['inBoundsFor' + Control.Selection.current_direction]({
            top: element._control_selection.top,
            left: element._control_selection.left,
            bottom: element._control_selection.top + element._control_selection.height,
            right: element._control_selection.left + element._control_selection.width
        },{
            top: parseInt(Control.Selection.selection_div.style.top, 10),
            left: parseInt(Control.Selection.selection_div.style.left, 10),
            bottom: parseInt(Control.Selection.selection_div.style.top, 10) + parseInt(Control.Selection.selection_div.style.height, 10),
            right: parseInt(Control.Selection.selection_div.style.left, 10) + parseInt(Control.Selection.selection_div.style.width, 10)
        })){
            element._control_selection.is_selected = true;
            return true;
        }else{
            element._control_selection.is_selected = false;
            return false;
        }
    },
    DragProxy: {
        active: false,
        xorigin: 0,
        yorigin: 0,
        load: function(){
            Control.Selection.DragProxy.container = $(document.createElement('div'));
            Control.Selection.DragProxy.container.id = 'control_selection_drag_proxy';
            Control.Selection.DragProxy.container.setStyle({
                position: 'absolute',
                top: '1px',
                left: '1px',
                zIndex: 99999
            });
            Control.Selection.DragProxy.container.hide();
            document.body.appendChild(Control.Selection.DragProxy.container);
            Control.Selection.observe('selected',Control.Selection.DragProxy.selected);
            Control.Selection.observe('deselected',Control.Selection.DragProxy.deselected);
        },
        start: function(event){            
            if(event.isRightClick()){
                Control.Selection.DragProxy.container.hide();
                return;
            }            
            if(Control.Selection.DragProxy.xorigin == Event.pointerX(event) && Control.Selection.DragProxy.yorigin == Event.pointerY(event)) {
                return; }
            Control.Selection.DragProxy.active = true;
            Control.Selection.DragProxy.container.setStyle({
                position: 'absolute',
                top: Event.pointerY(event) + 'px',
                left: Event.pointerX(event) + 'px'
            });            
            Control.Selection.DragProxy.container.observe('mouseup',Control.Selection.DragProxy.onMouseUp);            
            Control.Selection.DragProxy.container.show();
            Control.Selection.DragProxy.container._draggable = new Draggable(Control.Selection.DragProxy.container,Object.extend({
                onEnd: Control.Selection.DragProxy.stop
            },Control.Selection.options.drag_proxy_options));
            Control.Selection.DragProxy.container._draggable.eventMouseDown(event);            
            Control.Selection.DragProxy.notify('start',Control.Selection.DragProxy.container,Control.Selection.elements);
        },
        stop: function(){
            window.setTimeout(function(){
                Control.Selection.DragProxy.active = false;
                Control.Selection.DragProxy.container.hide();
                if(Control.Selection.DragProxy.container._draggable){
                    Control.Selection.DragProxy.container._draggable.destroy();
                    Control.Selection.DragProxy.container._draggable = null;
                }
                Control.Selection.DragProxy.notify('stop');
            },1);
        },
        onClick: function(event){
            Control.Selection.DragProxy.xorigin = Event.pointerX(event);
            Control.Selection.DragProxy.yorigin = Event.pointerY(event);
            if(event.isRightClick()) {
                Control.Selection.DragProxy.container.hide(); }
            if(Control.Selection.elements.length >= Control.Selection.options.drag_proxy_threshold && !(event.shiftKey || event.altKey) && (Control.Selection.DragProxy.xorigin != Event.pointerX(event) || Control.Selection.DragProxy.yorigin != Event.pointerY(event))){
                Control.Selection.DragProxy.start(event);
                Event.stop(event);
            }
        },
        onMouseUp: function(event){
            Control.Selection.DragProxy.stop();
            Control.Selection.DragProxy.container.stopObserving('mouseup',Control.Selection.DragProxy.onMouseUp);
        },
        selected: function(element){
            element.observe('mousedown',Control.Selection.DragProxy.onClick);
        },
        deselected: function(element){
            element.stopObserving('mousedown',Control.Selection.DragProxy.onClick);
        }
    }
};
Object.Event.extend(Control.Selection);
Object.Event.extend(Control.Selection.DragProxy);
/**
 * @author Ryan Johnson <http://syntacticx.com/>
 * @copyright 2008 PersonalGrid Corporation <http://personalgrid.com/>
 * @package LivePipe UI
 * @license MIT
 * @url http://livepipe.net/control/rating
 * @require prototype.js, livepipe.js
 */

/*global Prototype, Class, Option, $, $A, Control, $break,  */

if(typeof(Prototype) == "undefined") {
    throw "Control.SelectMultiple requires Prototype to be loaded."; }
if(typeof(Object.Event) == "undefined") {
    throw "Control.SelectMultiple requires Object.Event to be loaded."; }

Control.SelectMultiple = Class.create({
    select: false,
    container: false,
    numberOfCheckedBoxes: 0,
    checkboxes: [],
    hasExtraOption: false,
    initialize: function(select,container,options){
        this.options = {
            checkboxSelector: 'input[type=checkbox]',
            nameSelector: 'span.name',
            labelSeparator: ', ',
            valueSeparator: ',',
            afterChange: Prototype.emptyFunction,
            overflowString: function(str){
                return str.truncate();
            },
            overflowLength: 30
        };
        Object.extend(this.options,options || {});
        this.select = $(select);
        this.container =  $(container);
        this.checkboxes = (typeof(this.options.checkboxSelector) == 'function') ? 
            this.options.checkboxSelector.bind(this)() : 
            this.container.getElementsBySelector(this.options.checkboxSelector);
        var value_was_set = false;
        if(this.options.value){
            value_was_set = true;
            this.setValue(this.options.value);
            delete this.options.value;
        }
        this.hasExtraOption = false;
        this.checkboxes.each(function(checkbox){
         checkbox.observe('click',this.checkboxOnClick.bind(this,checkbox));
        }.bind(this));
        this.select.observe('change',this.selectOnChange.bind(this));
        this.countAndCheckCheckBoxes();
        if(!value_was_set) {
         this.scanCheckBoxes(); }
        this.notify('afterChange',this.select.options[this.select.options.selectedIndex].value);
    },
    countAndCheckCheckBoxes: function(){
        this.numberOfCheckedBoxes = this.checkboxes.inject(0,function(number,checkbox){
            checkbox.checked = (this.select.options[this.select.options.selectedIndex].value == checkbox.value);
            var value_string = this.select.options[this.select.options.selectedIndex].value;
            var value_collection = $A(value_string.split ? value_string.split(this.options.valueSeparator) : value_string);
            var should_check = value_collection.any(function(value) {
                if (!should_check && checkbox.value == value) {
                    return true; }
            }.bind(this));
            checkbox.checked = should_check;
            if(checkbox.checked) {
                ++number; }
            return number;
        }.bind(this));
    },
    setValue: function(value_string){
        this.numberOfCheckedBoxes = 0;
        var value_collection = $A(value_string.split ? value_string.split(this.options.valueSeparator) : value_string);
        this.checkboxes.each(function(checkbox){
            checkbox.checked = false;
            value_collection.each(function(value){
                if(checkbox.value == value){
                    ++this.numberOfCheckedBoxes;
                    checkbox.checked = true;
                }
            }.bind(this));
        }.bind(this));
        this.scanCheckBoxes();
    },
    selectOnChange: function(){
        this.removeExtraOption();
        this.countAndCheckCheckBoxes();
        this.notify('afterChange',this.select.options[this.select.options.selectedIndex].value);
    },
    checkboxOnClick: function(checkbox){
        this.numberOfCheckedBoxes = this.checkboxes.findAll(function (c) { 
            return c.checked; 
        }).length;
        this.scanCheckBoxes();
        this.notify('afterChange', this.numberOfCheckedBoxes === 0 ? "" :
            this.select.options[this.select.options.selectedIndex].value);
    },
    scanCheckBoxes: function(){
        switch(this.numberOfCheckedBoxes){
            case 1:
                this.checkboxes.each(function(checkbox){
                    if(checkbox.checked){
                        $A(this.select.options).each(function(option,i){
                            if(option.value == checkbox.value){
                                this.select.options.selectedIndex = i;
                                throw $break;
                            }
                        }.bind(this));
                        throw $break;
                    }
                }.bind(this));
                break;
            case 0:
                this.removeExtraOption();
                break;
            default:
                this.addExtraOption();
                break;
        }
    },
    getLabelForExtraOption: function(){
        var label = (typeof(this.options.nameSelector) == 'function' ? 
            this.options.nameSelector.bind(this)() : 
            this.container.getElementsBySelector(this.options.nameSelector).inject([],function(labels,name_element,i){
                if(this.checkboxes[i].checked) {
                    labels.push(name_element.innerHTML); }
                return labels;
            }.bind(this))
        ).join(this.options.labelSeparator);
        return (label.length >= this.options.overflowLength && this.options.overflowLength > 0) ? 
            (typeof(this.options.overflowString) == 'function' ? this.options.overflowString(label) : this.options.overflowString) : 
            label;
    },
    getValueForExtraOption: function(){
        return this.checkboxes.inject([],function(values,checkbox){
            if(checkbox.checked) {
                values.push(checkbox.value); }
            return values;
        }).join(this.options.valueSeparator);
    },
    addExtraOption: function(){
        this.removeExtraOption();
        this.hasExtraOption = true;
        this.select.options[this.select.options.length] = new Option(this.getLabelForExtraOption(),this.getValueForExtraOption());
        this.select.options.selectedIndex = this.select.options.length - 1;
    },
    removeExtraOption: function(){
        if(this.hasExtraOption){
            this.select.remove(this.select.options.length - 1);
            this.hasExtraOption = false;
        }
    }
});
Object.Event.extend(Control.SelectMultiple);
/**
 * @author Ryan Johnson <http://syntacticx.com/>
 * @copyright 2008 PersonalGrid Corporation <http://personalgrid.com/>
 * @package LivePipe UI
 * @license MIT
 * @url http://livepipe.net/control/tabs
 * @require prototype.js, livepipe.js
 */

/*global window, document, Prototype, $, $A, $H, $break, Class, Element, Event, Control */

if(typeof(Prototype) == "undefined") {
    throw "Control.Tabs requires Prototype to be loaded."; }
if(typeof(Object.Event) == "undefined") {
    throw "Control.Tabs requires Object.Event to be loaded."; }

Control.Tabs = Class.create({
    initialize: function(tab_list_container,options){
        if(!$(tab_list_container)) {
            throw "Control.Tabs could not find the element: " + tab_list_container; }
        this.activeContainer = false;
        this.activeLink = false;
        this.containers = $H({});
        this.links = [];
        Control.Tabs.instances.push(this);
        this.options = {
            beforeChange: Prototype.emptyFunction,
            afterChange: Prototype.emptyFunction,
            hover: false,
            linkSelector: 'li a',
            setClassOnContainer: false,
            activeClassName: 'active',
            defaultTab: 'first',
            autoLinkExternal: true,
            targetRegExp: /#(.+)$/,
            showFunction: Element.show,
            hideFunction: Element.hide
        };
        Object.extend(this.options,options || {});
        (typeof(this.options.linkSelector == 'string') ? 
            $(tab_list_container).select(this.options.linkSelector) : 
            this.options.linkSelector($(tab_list_container))
        ).findAll(function(link){
            return (/^#/).exec((Prototype.Browser.WebKit ? decodeURIComponent(link.href) : link.href).replace(window.location.href.split('#')[0],''));
        }).each(function(link){
            this.addTab(link);
        }.bind(this));
        this.containers.values().each(Element.hide);
        if(this.options.defaultTab == 'first') {
            this.setActiveTab(this.links.first());
        } else if(this.options.defaultTab == 'last') {
            this.setActiveTab(this.links.last());
        } else {
            this.setActiveTab(this.options.defaultTab); }
        var targets = this.options.targetRegExp.exec(window.location);
        if(targets && targets[1]){
            targets[1].split(',').each(function(target){
                this.setActiveTab(this.links.find(function(link){
                    return link.key == target;
                }));
            }.bind(this));
        }
        if(this.options.autoLinkExternal){
            $A(document.getElementsByTagName('a')).each(function(a){
                if(!this.links.include(a)){
                    var clean_href = a.href.replace(window.location.href.split('#')[0],'');
                    if(clean_href.substring(0,1) == '#'){
                        if(this.containers.keys().include(clean_href.substring(1))){
                            $(a).observe('click',function(event,clean_href){
                                this.setActiveTab(clean_href.substring(1));
                            }.bindAsEventListener(this,clean_href));
                        }
                    }
                }
            }.bind(this));
        }
    },
    addTab: function(link){
        this.links.push(link);
        link.key = link.getAttribute('href').replace(window.location.href.split('#')[0],'').split('#').last().replace(/#/,'');
        var container = $(link.key);
        if(!container) {
            throw "Control.Tabs: #" + link.key + " was not found on the page."; }
        this.containers.set(link.key,container);
        link[this.options.hover ? 'onmouseover' : 'onclick'] = function(link){
            if(window.event) {
                Event.stop(window.event); }
            this.setActiveTab(link);
            return false;
        }.bind(this,link);
    },
    setActiveTab: function(link){
        if(!link && typeof(link) == 'undefined') {
            return; }
        if(typeof(link) == 'string'){
            this.setActiveTab(this.links.find(function(_link){
                return _link.key == link;
            }));
        }else if(typeof(link) == 'number'){
            this.setActiveTab(this.links[link]);
        }else{
            if(this.notify('beforeChange',this.activeContainer,this.containers.get(link.key)) === false) {
                return; }
            if(this.activeContainer) {
                this.options.hideFunction(this.activeContainer); }
            this.links.each(function(item){
                (this.options.setClassOnContainer ? $(item.parentNode) : item).removeClassName(this.options.activeClassName);
            }.bind(this));
            (this.options.setClassOnContainer ? $(link.parentNode) : link).addClassName(this.options.activeClassName);
            this.activeContainer = this.containers.get(link.key);
            this.activeLink = link;
            this.options.showFunction(this.containers.get(link.key));
            this.notify('afterChange',this.containers.get(link.key));
        }
    },
    next: function(){
        this.links.each(function(link,i){
            if(this.activeLink == link && this.links[i + 1]){
                this.setActiveTab(this.links[i + 1]);
                throw $break;
            }
        }.bind(this));
    },
    previous: function(){
        this.links.each(function(link,i){
            if(this.activeLink == link && this.links[i - 1]){
                this.setActiveTab(this.links[i - 1]);
                throw $break;
            }
        }.bind(this));
    },
    first: function(){
        this.setActiveTab(this.links.first());
    },
    last: function(){
        this.setActiveTab(this.links.last());
    }
});
Object.extend(Control.Tabs,{
    instances: [],
    findByTabId: function(id){
        return Control.Tabs.instances.find(function(tab){
            return tab.links.find(function(link){
                return link.key == id;
            });
        });
    }
});
Object.Event.extend(Control.Tabs);
/**
 * @author Ryan Johnson <http://syntacticx.com/>
 * @copyright 2008 PersonalGrid Corporation <http://personalgrid.com/>
 * @package LivePipe UI
 * @license MIT
 * @url http://livepipe.net/control/textarea
 * @require prototype.js, livepipe.js
 */

/*global window, document, Prototype, Class, $, $A, Control */

if(typeof(Prototype) == "undefined") {
    throw "Control.TextArea requires Prototype to be loaded."; }
if(typeof(Object.Event) == "undefined") {
    throw "Control.TextArea requires Object.Event to be loaded."; }

Control.TextArea = Class.create({
    initialize: function(textarea){
        this.onChangeTimeout = false;
        this.element = $(textarea);
        $(this.element).observe('keyup',this.doOnChange.bindAsEventListener(this));
        $(this.element).observe('paste',this.doOnChange.bindAsEventListener(this));
        $(this.element).observe('input',this.doOnChange.bindAsEventListener(this));
        if(!!document.selection){
            $(this.element).observe('mouseup',this.saveRange.bindAsEventListener(this));  
            $(this.element).observe('keyup',this.saveRange.bindAsEventListener(this));
        }
    },
    doOnChange: function(event){
        if(this.onChangeTimeout) {
            window.clearTimeout(this.onChangeTimeout); }
        this.onChangeTimeout = window.setTimeout(function(){
            this.notify('change',this.getValue());
        }.bind(this),Control.TextArea.onChangeTimeoutLength);
    },
    saveRange: function(){
        this.range = document.selection.createRange();  
    },
    getValue: function(){
        return this.element.value;
    },
    getSelection: function(){
        if(!!document.selection) {
            return document.selection.createRange().text; }
        else if(!!this.element.setSelectionRange) {
            return this.element.value.substring(this.element.selectionStart,this.element.selectionEnd); }
        else {
            return false; }
    },
    replaceSelection: function(text){
        var scroll_top = this.element.scrollTop;
        if(!!document.selection){
            this.element.focus();
            var range = (this.range) ? this.range : document.selection.createRange();
            range.text = text;
            range.select();
        }else if(!!this.element.setSelectionRange){
            var selection_start = this.element.selectionStart;
            this.element.value = this.element.value.substring(0,selection_start) + text + this.element.value.substring(this.element.selectionEnd);
            this.element.setSelectionRange(selection_start + text.length,selection_start + text.length);
        }
        this.doOnChange();
        this.element.focus();
        this.element.scrollTop = scroll_top;
    },
    wrapSelection: function(before,after){
        var sel = this.getSelection();
        // Remove the wrapping if the selection has the same before/after
        if (sel.indexOf(before) === 0 && 
            sel.lastIndexOf(after) === (sel.length - after.length)) {
            this.replaceSelection(sel.substring(before.length, 
                sel.length - after.length));
        } else { this.replaceSelection(before + sel + after); }
    },
    insertBeforeSelection: function(text){
        this.replaceSelection(text + this.getSelection());
    },
    insertAfterSelection: function(text){
        this.replaceSelection(this.getSelection() + text);
    },
    collectFromEachSelectedLine: function(callback,before,after){
        this.replaceSelection((before || '') + $A(this.getSelection().split("\n")).collect(callback).join("\n") + (after || ''));
    },
    insertBeforeEachSelectedLine: function(text,before,after){
        this.collectFromEachSelectedLine(function(line){
        },before,after);
    }
});
Object.extend(Control.TextArea,{
    onChangeTimeoutLength: 500
});
Object.Event.extend(Control.TextArea);

Control.TextArea.ToolBar = Class.create(    {
    initialize: function(textarea,toolbar){
        this.textarea = textarea;
        if(toolbar) {
            this.container = $(toolbar); }
        else{
            this.container = $(document.createElement('ul'));
            this.textarea.element.parentNode.insertBefore(this.container,this.textarea.element);
        }
    },
    attachButton: function(node,callback){
        node.onclick = function(){return false;};
        $(node).observe('click',callback.bindAsEventListener(this.textarea));
    },
    addButton: function(link_text,callback,attrs){
        var li = document.createElement('li');
        var a = document.createElement('a');
        a.href = '#';
        this.attachButton(a,callback);
        li.appendChild(a);
        Object.extend(a,attrs || {});
        if(link_text){
            var span = document.createElement('span');
            span.innerHTML = link_text;
            a.appendChild(span);
        }
        this.container.appendChild(li);
    }
});
/**
 * @author Ryan Johnson <http://syntacticx.com/>
 * @copyright 2008 PersonalGrid Corporation <http://personalgrid.com/>
 * @package LivePipe UI
 * @license MIT
 * @url http://livepipe.net/control/window
 * @require prototype.js, effects.js, draggable.js, resizable.js, livepipe.js
 */

//adds onDraw and constrainToViewport option to draggable
if(typeof(Draggable) != 'undefined'){
    //allows the point to be modified with an onDraw callback
    Draggable.prototype.draw = function(point) {
        var pos = Position.cumulativeOffset(this.element);
        if(this.options.ghosting) {
            var r = Position.realOffset(this.element);
            pos[0] += r[0] - Position.deltaX; pos[1] += r[1] - Position.deltaY;
        }
        
        var d = this.currentDelta();
        pos[0] -= d[0]; pos[1] -= d[1];
        
        if(this.options.scroll && (this.options.scroll != window && this._isScrollChild)) {
            pos[0] -= this.options.scroll.scrollLeft-this.originalScrollLeft;
            pos[1] -= this.options.scroll.scrollTop-this.originalScrollTop;
        }
        
        var p = [0,1].map(function(i){ 
            return (point[i]-pos[i]-this.offset[i]) 
        }.bind(this));
        
        if(this.options.snap) {
            if(typeof this.options.snap == 'function') {
                p = this.options.snap(p[0],p[1],this);
            } else {
                if(this.options.snap instanceof Array) {
                    p = p.map( function(v, i) {return Math.round(v/this.options.snap[i])*this.options.snap[i] }.bind(this))
                } else {
                    p = p.map( function(v) {return Math.round(v/this.options.snap)*this.options.snap }.bind(this))
                  }
            }
        }
        
        if(this.options.onDraw)
            this.options.onDraw.bind(this)(p);
        else{
            var style = this.element.style;
            if(this.options.constrainToViewport){
                var viewport_dimensions = document.viewport.getDimensions();
                var container_dimensions = this.element.getDimensions();
                var margin_top = parseInt(this.element.getStyle('margin-top'));
                var margin_left = parseInt(this.element.getStyle('margin-left'));
                var boundary = [[
                    0 - margin_left,
                    0 - margin_top
                ],[
                    (viewport_dimensions.width - container_dimensions.width) - margin_left,
                    (viewport_dimensions.height - container_dimensions.height) - margin_top
                ]];
                if((!this.options.constraint) || (this.options.constraint=='horizontal')){ 
                    if((p[0] >= boundary[0][0]) && (p[0] <= boundary[1][0]))
                        this.element.style.left = p[0] + "px";
                    else
                        this.element.style.left = ((p[0] < boundary[0][0]) ? boundary[0][0] : boundary[1][0]) + "px";
                } 
                if((!this.options.constraint) || (this.options.constraint=='vertical')){ 
                    if((p[1] >= boundary[0][1] ) && (p[1] <= boundary[1][1]))
                        this.element.style.top = p[1] + "px";
                  else
                        this.element.style.top = ((p[1] <= boundary[0][1]) ? boundary[0][1] : boundary[1][1]) + "px";               
                }
            }else{
                if((!this.options.constraint) || (this.options.constraint=='horizontal'))
                  style.left = p[0] + "px";
                if((!this.options.constraint) || (this.options.constraint=='vertical'))
                  style.top     = p[1] + "px";
            }
            if(style.visibility=="hidden")
                style.visibility = ""; // fix gecko rendering
        }
    };
}

if(typeof(Prototype) == "undefined")
    throw "Control.Window requires Prototype to be loaded.";
if(typeof(IframeShim) == "undefined")
    throw "Control.Window requires IframeShim to be loaded.";
if(typeof(Object.Event) == "undefined")
    throw "Control.Window requires Object.Event to be loaded.";
/*
    known issues:
        - when iframe is clicked is does not gain focus
        - safari can't open multiple iframes properly
        - constrainToViewport: body must have no margin or padding for this to work properly
        - iframe will be mis positioned during fade in
        - document.viewport does not account for scrollbars (this will eventually be fixed in the prototype core)
    notes
        - setting constrainToViewport only works when the page is not scrollable
        - setting draggable: true will negate the effects of position: center
*/
Control.Window = Class.create({
    initialize: function(container,options){
        Control.Window.windows.push(this);
        
        //attribute initialization
        this.container = false;
        this.isOpen = false;
        this.href = false;
        this.sourceContainer = false; //this is optionally the container that will open the window
        this.ajaxRequest = false;
        this.remoteContentLoaded = false; //this is set when the code to load the remote content is run, onRemoteContentLoaded is fired when the connection is closed
        this.numberInSequence = Control.Window.windows.length + 1; //only useful for the effect scoping
        this.indicator = false;
        this.effects = {
            fade: false,
            appear: false
        };
        this.indicatorEffects = {
            fade: false,
            appear: false
        };
        
        //options
        this.options = Object.extend({
            //lifecycle
            beforeOpen: Prototype.emptyFunction,
            afterOpen: Prototype.emptyFunction,
            beforeClose: Prototype.emptyFunction,
            afterClose: Prototype.emptyFunction,
            //dimensions and modes
            height: null,
            width: null,
            className: false,
            position: 'center', //'center', 'relative', [x,y], [function(){return x;},function(){return y;}]
            offsetLeft: 0, //available only for anchors opening the window, or windows set to position: hover
            offsetTop: 0, //""
            iframe: false, //if the window has an href, this will display the href as an iframe instead of requesting the url as an an Ajax.Request
            hover: false, //element object to hover over, or if "true" only available for windows with sourceContainer (an anchor or any element already on the page with an href attribute)
            indicator: false, //element to show or hide when ajax requests, images and iframes are loading
            closeOnClick: false, //does not work with hover,can be: true (click anywhere), 'container' (will refer to this.container), or element (a specific element)
            iframeshim: true, //whether or not to position an iFrameShim underneath the window 
            //effects
            fade: false,
            fadeDuration: 0.75,
            //draggable
            draggable: false,
            onDrag: Prototype.emptyFunction,
            //resizable
            resizable: false,
            minHeight: false,
            minWidth: false,
            maxHeight: false,
            maxWidth: false,
            onResize: Prototype.emptyFunction,
            //draggable and resizable
            constrainToViewport: false,
            //ajax
            method: 'post',
            parameters: {},
            onComplete: Prototype.emptyFunction,
            onSuccess: Prototype.emptyFunction,
            onFailure: Prototype.emptyFunction,
            onException: Prototype.emptyFunction,
            //any element with an href (image,iframe,ajax) will call this after it is done loading
            onRemoteContentLoaded: Prototype.emptyFunction,
            insertRemoteContentAt: false //false will set this to this.container, can be string selector (first returned will be selected), or an Element that must be a child of this.container
        },options || {});
        
        //container setup
        this.indicator = this.options.indicator ? $(this.options.indicator) : false;
        if(container){
            if(typeof(container) == "string" && container.match(Control.Window.uriRegex))
                this.href = container;
            else{
                this.container = $(container);
                //need to create the container now for tooltips (or hover: element with no container already on the page)
                //second call made below will not create the container since the check is done inside createDefaultContainer()
                this.createDefaultContainer(container);
                //if an element with an href was passed in we use it to activate the window
                if(this.container && ((this.container.readAttribute('href') && this.container.readAttribute('href') != '') || (this.options.hover && this.options.hover !== true))){                        
                    if(this.options.hover && this.options.hover !== true)
                        this.sourceContainer = $(this.options.hover);
                    else{
                        this.sourceContainer = this.container;
                        this.href = this.container.readAttribute('href');
                        var rel = this.href.match(/^#(.+)$/);
                        if(rel && rel[1]){
                            this.container = $(rel[1]);
                            this.href = false;
                        }else
                            this.container = false;
                    }
                    //hover or click handling
                    this.sourceContainerOpenHandler = function(event){
                        this.open(event);
                        event.stop();
                        return false;
                    }.bindAsEventListener(this);
                    this.sourceContainerCloseHandler = function(event){
                        this.close(event);
                    }.bindAsEventListener(this);
                    this.sourceContainerMouseMoveHandler = function(event){
                        this.position(event);
                    }.bindAsEventListener(this);
                    if(this.options.hover){
                        this.sourceContainer.observe('mouseenter',this.sourceContainerOpenHandler);
                        this.sourceContainer.observe('mouseleave',this.sourceContainerCloseHandler);
                        if(this.options.position == 'mouse')
                            this.sourceContainer.observe('mousemove',this.sourceContainerMouseMoveHandler);
                    }else
                        this.sourceContainer.observe('click',this.sourceContainerOpenHandler);
                }
            }
        }
        this.createDefaultContainer(container);
        if(this.options.insertRemoteContentAt === false)
            this.options.insertRemoteContentAt = this.container;
        var styles = {
            margin: 0,
            position: 'absolute',
            zIndex: Control.Window.initialZIndexForWindow()
        };
        if(this.options.width)
            styles.width = $value(this.options.width) + 'px';
        if(this.options.height)
            styles.height = $value(this.options.height) + 'px';
        this.container.setStyle(styles);
        if(this.options.className)
            this.container.addClassName(this.options.className);
        this.positionHandler = this.position.bindAsEventListener(this);
        this.outOfBoundsPositionHandler = this.ensureInBounds.bindAsEventListener(this);
        this.bringToFrontHandler = this.bringToFront.bindAsEventListener(this);
        this.container.observe('mousedown',this.bringToFrontHandler);
        this.container.hide();
        this.closeHandler = this.close.bindAsEventListener(this);
        //iframeshim setup
        if(this.options.iframeshim){
            this.iFrameShim = new IframeShim();
            this.iFrameShim.hide();
        }
        //resizable support
        this.applyResizable();
        //draggable support
        this.applyDraggable();
        
        //makes sure the window can't go out of bounds
        Event.observe(window,'resize',this.outOfBoundsPositionHandler);
        
        this.notify('afterInitialize');
    },
    open: function(event){
        if(this.isOpen){
            this.bringToFront();
            return false;
        }
        if(this.notify('beforeOpen') === false)
            return false;
        //closeOnClick
        if(this.options.closeOnClick){
            if(this.options.closeOnClick === true)
                this.closeOnClickContainer = $(document.body);
            else if(this.options.closeOnClick == 'container')
                this.closeOnClickContainer = this.container;
            else if (this.options.closeOnClick == 'overlay'){
                Control.Overlay.load();
                this.closeOnClickContainer = Control.Overlay.container;
            }else
                this.closeOnClickContainer = $(this.options.closeOnClick);
            this.closeOnClickContainer.observe('click',this.closeHandler);
        }
        if(this.href && !this.options.iframe && !this.remoteContentLoaded){
            //link to image
            this.remoteContentLoaded = true;
            if(this.href.match(/\.(jpe?g|gif|png|tiff?)$/i)){
                var img = new Element('img');
                img.observe('load',function(img){
                    this.getRemoteContentInsertionTarget().insert(img);
                    this.position();
                    if(this.notify('onRemoteContentLoaded') !== false){
                        if(this.options.indicator)
                            this.hideIndicator();
                        this.finishOpen();
                    }
                }.bind(this,img));
                img.writeAttribute('src',this.href);
            }else{
                //if this is an ajax window it will only open if the request is successful
                if(!this.ajaxRequest){
                    if(this.options.indicator)
                        this.showIndicator();
                    this.ajaxRequest = new Ajax.Request(this.href,{
                        method: this.options.method,
                        parameters: this.options.parameters,
                        onComplete: function(request){
                            this.notify('onComplete',request);
                            this.ajaxRequest = false;
                        }.bind(this),
                        onSuccess: function(request){
                            this.getRemoteContentInsertionTarget().insert(request.responseText);
                            this.notify('onSuccess',request);
                            if(this.notify('onRemoteContentLoaded') !== false){
                                if(this.options.indicator)
                                    this.hideIndicator();
                                this.finishOpen();
                            }
                        }.bind(this),
                        onFailure: function(request){
                            this.notify('onFailure',request);
                            if(this.options.indicator)
                                this.hideIndicator();
                        }.bind(this),
                        onException: function(request,e){
                            this.notify('onException',request,e);
                            if(this.options.indicator)
                                this.hideIndicator();
                        }.bind(this)
                    });
                }
            }
            return true;
        }else if(this.options.iframe && !this.remoteContentLoaded){
            //iframe
            this.remoteContentLoaded = true;
            if(this.options.indicator)
                this.showIndicator();
            this.getRemoteContentInsertionTarget().insert(Control.Window.iframeTemplate.evaluate({
                href: this.href
            }));
            var iframe = this.container.down('iframe');
            iframe.onload = function(){
                this.notify('onRemoteContentLoaded');
                if(this.options.indicator)
                    this.hideIndicator();
                iframe.onload = null;
            }.bind(this);
        }
        this.finishOpen(event);
        return true
    },
    close: function(event){ //event may or may not be present
        if(!this.isOpen || this.notify('beforeClose',event) === false)
            return false;
        if(this.options.closeOnClick)
            this.closeOnClickContainer.stopObserving('click',this.closeHandler);
        if(this.options.fade){
            this.effects.fade = new Effect.Fade(this.container,{
                queue: {
                    position: 'front',
                    scope: 'Control.Window' + this.numberInSequence
                },
                from: 1,
                to: 0,
                duration: this.options.fadeDuration / 2,
                afterFinish: function(){
                    if(this.iFrameShim)
                        this.iFrameShim.hide();
                    this.isOpen = false;
                    this.notify('afterClose');
                }.bind(this)
            });
        }else{
            this.container.hide();
            if(this.iFrameShim)
                this.iFrameShim.hide();
        }
        if(this.ajaxRequest)
            this.ajaxRequest.transport.abort();
        if(!(this.options.draggable || this.options.resizable) && this.options.position == 'center')
            Event.stopObserving(window,'resize',this.positionHandler);
        if(!this.options.draggable && this.options.position == 'center')
            Event.stopObserving(window,'scroll',this.positionHandler);
        if(this.options.indicator)
            this.hideIndicator();
        if(!this.options.fade){
            this.isOpen = false;
            this.notify('afterClose');
        }
        return true;
    },
    position: function(event){
        //this is up top for performance reasons
        if(this.options.position == 'mouse'){
            var xy = [Event.pointerX(event),Event.pointerY(event)];
            this.container.setStyle({
                top: xy[1] + $value(this.options.offsetTop) + 'px',
                left: xy[0] + $value(this.options.offsetLeft) + 'px'
            });
            return;
        }
        var container_dimensions = this.container.getDimensions();
        var viewport_dimensions = document.viewport.getDimensions();
        Position.prepare();
        var offset_left = (Position.deltaX + Math.floor((viewport_dimensions.width - container_dimensions.width) / 2));
        var offset_top = (Position.deltaY + ((viewport_dimensions.height > container_dimensions.height) ? Math.floor((viewport_dimensions.height - container_dimensions.height) / 2) : 0));
        if(this.options.position == 'center'){
            this.container.setStyle({
                top: (container_dimensions.height <= viewport_dimensions.height) ? ((offset_top != null && offset_top > 0) ? offset_top : 0) + 'px' : 0,
                left: (container_dimensions.width <= viewport_dimensions.width) ? ((offset_left != null && offset_left > 0) ? offset_left : 0) + 'px' : 0
            });
        }else if(this.options.position == 'relative'){
            var xy = this.sourceContainer.cumulativeOffset();
            var top = xy[1] + $value(this.options.offsetTop);
            var left = xy[0] + $value(this.options.offsetLeft);
            this.container.setStyle({
                top: (container_dimensions.height <= viewport_dimensions.height) ? (this.options.constrainToViewport ? Math.max(0,Math.min(viewport_dimensions.height - (container_dimensions.height),top)) : top) + 'px' : 0,
                left: (container_dimensions.width <= viewport_dimensions.width) ? (this.options.constrainToViewport ? Math.max(0,Math.min(viewport_dimensions.width - (container_dimensions.width),left)) : left) + 'px' : 0
            });
        }else if(this.options.position.length){
            var top = $value(this.options.position[1]) + $value(this.options.offsetTop);
            var left = $value(this.options.position[0]) + $value(this.options.offsetLeft);
            this.container.setStyle({
                top: (container_dimensions.height <= viewport_dimensions.height) ? (this.options.constrainToViewport ? Math.max(0,Math.min(viewport_dimensions.height - (container_dimensions.height),top)) : top) + 'px' : 0,
                left: (container_dimensions.width <= viewport_dimensions.width) ? (this.options.constrainToViewport ? Math.max(0,Math.min(viewport_dimensions.width - (container_dimensions.width),left)) : left) + 'px' : 0
            });
        }
        if(this.iFrameShim)
            this.updateIFrameShimZIndex();
    },
    ensureInBounds: function(){
        if(!this.isOpen)
            return;
        var viewport_dimensions = document.viewport.getDimensions();
        var container_offset = this.container.cumulativeOffset();
        var container_dimensions = this.container.getDimensions();
        if(container_offset.left + container_dimensions.width > viewport_dimensions.width){
            this.container.setStyle({
                left: (Math.max(0,viewport_dimensions.width - container_dimensions.width)) + 'px'
            });
        }
        if(container_offset.top + container_dimensions.height > viewport_dimensions.height){
            this.container.setStyle({
                top: (Math.max(0,viewport_dimensions.height - container_dimensions.height)) + 'px'
            });
        }
    },
    bringToFront: function(){
        Control.Window.bringToFront(this);
        this.notify('bringToFront');
    },
    destroy: function(){
        this.container.stopObserving('mousedown',this.bringToFrontHandler);
        if(this.draggable){
            Draggables.removeObserver(this.container);
            this.draggable.handle.stopObserving('mousedown',this.bringToFrontHandler);
            this.draggable.destroy();
        }
        if(this.resizable){
            Resizables.removeObserver(this.container);
            this.resizable.handle.stopObserving('mousedown',this.bringToFrontHandler);
            this.resizable.destroy();
        }
        if(this.container && !this.sourceContainer)
            this.container.remove();
        if(this.sourceContainer){
            if(this.options.hover){
                this.sourceContainer.stopObserving('mouseenter',this.sourceContainerOpenHandler);
                this.sourceContainer.stopObserving('mouseleave',this.sourceContainerCloseHandler);
                if(this.options.position == 'mouse')
                    this.sourceContainer.stopObserving('mousemove',this.sourceContainerMouseMoveHandler);
            }else
                this.sourceContainer.stopObserving('click',this.sourceContainerOpenHandler);
        }
        if(this.iFrameShim)
            this.iFrameShim.destroy();
        Event.stopObserving(window,'resize',this.outOfBoundsPositionHandler);
        Control.Window.windows = Control.Window.windows.without(this);
        this.notify('afterDestroy');
    },
    //private
    applyResizable: function(){
        if(this.options.resizable){
            if(typeof(Resizable) == "undefined")
                throw "Control.Window requires resizable.js to be loaded.";
            var resizable_handle = null;
            if(this.options.resizable === true){
                resizable_handle = new Element('div',{
                    className: 'resizable_handle'
                });
                this.container.insert(resizable_handle);
            }else
                resizable_handle = $(this.options.resziable);
            this.resizable = new Resizable(this.container,{
                handle: resizable_handle,
                minHeight: this.options.minHeight,
                minWidth: this.options.minWidth,
                maxHeight: this.options.constrainToViewport ? function(element){
                    //viewport height - top - total border height
                    return (document.viewport.getDimensions().height - parseInt(element.style.top || 0)) - (element.getHeight() - parseInt(element.style.height || 0));
                } : this.options.maxHeight,
                maxWidth: this.options.constrainToViewport ? function(element){
                    //viewport width - left - total border width
                    return (document.viewport.getDimensions().width - parseInt(element.style.left || 0)) - (element.getWidth() - parseInt(element.style.width || 0));
                } : this.options.maxWidth
            });
            this.resizable.handle.observe('mousedown',this.bringToFrontHandler);
            Resizables.addObserver(new Control.Window.LayoutUpdateObserver(this,function(){
                if(this.iFrameShim)
                    this.updateIFrameShimZIndex();
                this.notify('onResize');
            }.bind(this)));
        }
    },
    applyDraggable: function(){
        if(this.options.draggable){
            if(typeof(Draggables) == "undefined")
                throw "Control.Window requires dragdrop.js to be loaded.";
            var draggable_handle = null;
            if(this.options.draggable === true){
                draggable_handle = new Element('div',{
                    className: 'draggable_handle'
                });
                this.container.insert(draggable_handle);
            }else
                draggable_handle = $(this.options.draggable);
            this.draggable = new Draggable(this.container,{
                handle: draggable_handle,
                constrainToViewport: this.options.constrainToViewport,
                zindex: this.container.getStyle('z-index'),
                starteffect: function(){
                    if(Prototype.Browser.IE){
                        this.old_onselectstart = document.onselectstart;
                        document.onselectstart = function(){
                            return false;
                        };
                    }
                }.bind(this),
                endeffect: function(){
                    document.onselectstart = this.old_onselectstart;
                }.bind(this)
            });
            this.draggable.handle.observe('mousedown',this.bringToFrontHandler);
            Draggables.addObserver(new Control.Window.LayoutUpdateObserver(this,function(){
                if(this.iFrameShim)
                    this.updateIFrameShimZIndex();
                this.notify('onDrag');
            }.bind(this)));
        }
    },
    createDefaultContainer: function(container){
        if(!this.container){
            //no container passed or found, create it
            this.container = new Element('div',{
                id: 'control_window_' + this.numberInSequence
            });
            $(document.body).insert(this.container);
            if(typeof(container) == "string" && $(container) == null && !container.match(/^#(.+)$/) && !container.match(Control.Window.uriRegex))
                this.container.update(container);
        }
    },
    finishOpen: function(event){
        this.bringToFront();
        if(this.options.fade){
            if(typeof(Effect) == "undefined")
                throw "Control.Window requires effects.js to be loaded."
            if(this.effects.fade)
                this.effects.fade.cancel();
            this.effects.appear = new Effect.Appear(this.container,{
                queue: {
                    position: 'end',
                    scope: 'Control.Window.' + this.numberInSequence
                },
                from: 0,
                to: 1,
                duration: this.options.fadeDuration / 2,
                afterFinish: function(){
                    if(this.iFrameShim)
                        this.updateIFrameShimZIndex();
                    this.isOpen = true;
                    this.notify('afterOpen');
                }.bind(this)
            });
        }else
            this.container.show();
        this.position(event);
        if(!(this.options.draggable || this.options.resizable) && this.options.position == 'center')
            Event.observe(window,'resize',this.positionHandler,false);
        if(!this.options.draggable && this.options.position == 'center')
            Event.observe(window,'scroll',this.positionHandler,false);
        if(!this.options.fade){
            this.isOpen = true;
            this.notify('afterOpen');
        }
        return true;
    },
    showIndicator: function(){
        this.showIndicatorTimeout = window.setTimeout(function(){
            if(this.options.fade){
                this.indicatorEffects.appear = new Effect.Appear(this.indicator,{
                    queue: {
                        position: 'front',
                        scope: 'Control.Window.indicator.' + this.numberInSequence
                    },
                    from: 0,
                    to: 1,
                    duration: this.options.fadeDuration / 2
                });
            }else
                this.indicator.show();
        }.bind(this),Control.Window.indicatorTimeout);
    },
    hideIndicator: function(){
        if(this.showIndicatorTimeout)
            window.clearTimeout(this.showIndicatorTimeout);
        this.indicator.hide();
    },
    getRemoteContentInsertionTarget: function(){
        return typeof(this.options.insertRemoteContentAt) == "string" ? this.container.down(this.options.insertRemoteContentAt) : $(this.options.insertRemoteContentAt);
    },
    updateIFrameShimZIndex: function(){
        if(this.iFrameShim)
            this.iFrameShim.positionUnder(this.container);
    }
});
//class methods
Object.extend(Control.Window,{
    windows: [],
    baseZIndex: 9999,
    indicatorTimeout: 250,
    iframeTemplate: new Template('<iframe src="#{href}" width="100%" height="100%" frameborder="0"></iframe>'),
    uriRegex: /^(\/|\#|https?\:\/\/|[\w]+\/)/,
    bringToFront: function(w){
        Control.Window.windows = Control.Window.windows.without(w);
        Control.Window.windows.push(w);
        Control.Window.windows.each(function(w,i){
            var z_index = Control.Window.baseZIndex + i;
            w.container.setStyle({
                zIndex: z_index
            });
            if(w.isOpen){
                if(w.iFrameShim)
                w.updateIFrameShimZIndex();
            }
            if(w.options.draggable)
                w.draggable.options.zindex = z_index;
        });
    },
    open: function(container,options){
        var w = new Control.Window(container,options);
        w.open();
        return w;
    },
    //protected
    initialZIndexForWindow: function(w){
        return Control.Window.baseZIndex + (Control.Window.windows.length - 1);
    }
});
Object.Event.extend(Control.Window);

//this is the observer for both Resizables and Draggables
Control.Window.LayoutUpdateObserver = Class.create({
    initialize: function(w,observer){
        this.w = w;
        this.element = $(w.container);
        this.observer = observer;
    },
    onStart: Prototype.emptyFunction,
    onEnd: function(event_name,instance){
        if(instance.element == this.element && this.iFrameShim)
            this.w.updateIFrameShimZIndex();
    },
    onResize: function(event_name,instance){
        if(instance.element == this.element)
            this.observer(this.element);
    },
    onDrag: function(event_name,instance){
        if(instance.element == this.element)
            this.observer(this.element);
    }
});

//overlay for Control.Modal
Control.Overlay = {
    id: 'control_overlay',
    loaded: false,
    container: false,
    lastOpacity: 0,
    styles: {
        position: 'fixed',
        top: 0,
        left: 0,
        width: '100%',
        height: '100%',
        zIndex: 9998
    },
    ieStyles: {
        position: 'absolute',
        top: 0,
        left: 0,
        zIndex: 9998
    },
    effects: {
        fade: false,
        appear: false
    },
    load: function(){
        if(Control.Overlay.loaded)
            return false;
        Control.Overlay.loaded = true;
        Control.Overlay.container = new Element('div',{
            id: Control.Overlay.id
        });
        $(document.body).insert(Control.Overlay.container);
        if(Prototype.Browser.IE){
            Control.Overlay.container.setStyle(Control.Overlay.ieStyles);
            Event.observe(window,'scroll',Control.Overlay.positionOverlay);
            Event.observe(window,'resize',Control.Overlay.positionOverlay);
            Control.Overlay.observe('beforeShow',Control.Overlay.positionOverlay);
        }else
            Control.Overlay.container.setStyle(Control.Overlay.styles);
        Control.Overlay.iFrameShim = new IframeShim();
        Control.Overlay.iFrameShim.hide();
        Event.observe(window,'resize',Control.Overlay.positionIFrameShim);
        Control.Overlay.container.hide();
        return true;
    },
    unload: function(){
        if(!Control.Overlay.loaded)
            return false;
        Event.stopObserving(window,'resize',Control.Overlay.positionOverlay);
        Control.Overlay.stopObserving('beforeShow',Control.Overlay.positionOverlay);
        Event.stopObserving(window,'resize',Control.Overlay.positionIFrameShim);
        Control.Overlay.iFrameShim.destroy();
        Control.Overlay.container.remove();
        Control.Overlay.loaded = false;
        return true;
    },
    show: function(opacity,fade){
        if(Control.Overlay.notify('beforeShow') === false)
            return false;
        Control.Overlay.lastOpacity = opacity;
        Control.Overlay.positionIFrameShim();
        Control.Overlay.iFrameShim.show();
        if(fade){
            if(typeof(Effect) == "undefined")
                throw "Control.Window requires effects.js to be loaded."
            if(Control.Overlay.effects.fade)
                Control.Overlay.effects.fade.cancel();
            Control.Overlay.effects.appear = new Effect.Appear(Control.Overlay.container,{
                queue: {
                    position: 'end',
                    scope: 'Control.Overlay'
                },
                afterFinish: function(){
                    Control.Overlay.notify('afterShow');
                },
                from: 0,
                to: Control.Overlay.lastOpacity,
                duration: (fade === true ? 0.75 : fade) / 2
            });
        }else{
            Control.Overlay.container.setStyle({
                opacity: opacity || 1
            });
            Control.Overlay.container.show();
            Control.Overlay.notify('afterShow');
        }
        return true;
    },
    hide: function(fade){
        if(Control.Overlay.notify('beforeHide') === false)
            return false;
        if(Control.Overlay.effects.appear)
            Control.Overlay.effects.appear.cancel();
        Control.Overlay.iFrameShim.hide();
        if(fade){
            Control.Overlay.effects.fade = new Effect.Fade(Control.Overlay.container,{
                queue: {
                    position: 'front',
                    scope: 'Control.Overlay'
                },
                afterFinish: function(){
                    Control.Overlay.notify('afterHide');
                },
                from: Control.Overlay.lastOpacity,
                to: 0,
                duration: (fade === true ? 0.75 : fade) / 2
            });
        }else{
            Control.Overlay.container.hide();
            Control.Overlay.notify('afterHide');
        }
        return true;
    },
    positionIFrameShim: function(){
        if(Control.Overlay.container.visible())
            Control.Overlay.iFrameShim.positionUnder(Control.Overlay.container);
    },
    //IE only
    positionOverlay: function(){
        Control.Overlay.container.setStyle({
            width: document.body.clientWidth + 'px',
            height: document.body.clientHeight + 'px'
        });
    }
};
Object.Event.extend(Control.Overlay);

Control.ToolTip = Class.create(Control.Window,{
    initialize: function($super,container,tooltip,options){
        $super(tooltip,Object.extend(Object.extend(Object.clone(Control.ToolTip.defaultOptions),options || {}),{
            position: 'mouse',
            hover: container
        }));
    }
});
Object.extend(Control.ToolTip,{
    defaultOptions: {
        offsetLeft: 10
    }
});

Control.Modal = Class.create(Control.Window,{
    initialize: function($super,container,options){
        Control.Modal.InstanceMethods.beforeInitialize.bind(this)();
        $super(container,Object.extend(Object.clone(Control.Modal.defaultOptions),options || {}));
    }
});
Object.extend(Control.Modal,{
    defaultOptions: {
        overlayOpacity: 0.5,
        closeOnClick: 'overlay'
    },
    current: false,
    open: function(container,options){
        var modal = new Control.Modal(container,options);
        modal.open();
        return modal;
    },
    close: function(){
        if(Control.Modal.current)
            Control.Modal.current.close();
    },
    InstanceMethods: {
        beforeInitialize: function(){
            Control.Overlay.load();
            this.overlayFinishedOpening = false;
            this.observe('beforeOpen',Control.Modal.Observers.beforeOpen.bind(this));
            this.observe('afterOpen',Control.Modal.Observers.afterOpen.bind(this));
            this.observe('afterClose',Control.Modal.Observers.afterClose.bind(this));
        }
    },
    Observers: {
        beforeOpen: function(){
            if(!this.overlayFinishedOpening){
                Control.Overlay.observeOnce('afterShow',function(){
                    this.overlayFinishedOpening = true;
                    this.open();
                }.bind(this));
                Control.Overlay.show(this.options.overlayOpacity,this.options.fade ? this.options.fadeDuration : false);
                throw $break;
            }else
            Control.Window.windows.without(this).invoke('close');
        },
        afterOpen: function(){
            Control.Modal.current = this;
        },
        afterClose: function(){
            Control.Overlay.hide(this.options.fade ? this.options.fadeDuration : false);
            Control.Modal.current = false;
            this.overlayFinishedOpening = false;
        }
    }
});

Control.LightBox = Class.create(Control.Window,{
    initialize: function($super,container,options){
        this.allImagesLoaded = false;
        if(options.modal){
            var options = Object.extend(Object.clone(Control.LightBox.defaultOptions),options || {});
            options = Object.extend(Object.clone(Control.Modal.defaultOptions),options);
            options = Control.Modal.InstanceMethods.beforeInitialize.bind(this)(options);
            $super(container,options);
        }else
            $super(container,Object.extend(Object.clone(Control.LightBox.defaultOptions),options || {}));
        this.hasRemoteContent = this.href && !this.options.iframe;
        if(this.hasRemoteContent)
            this.observe('onRemoteContentLoaded',Control.LightBox.Observers.onRemoteContentLoaded.bind(this));
        else
            this.applyImageObservers();
        this.observe('beforeOpen',Control.LightBox.Observers.beforeOpen.bind(this));
    },
    applyImageObservers:function(){
        var images = this.getImages();
        this.numberImagesToLoad = images.length;
        this.numberofImagesLoaded = 0;
        images.each(function(image){
            image.observe('load',function(image){
                ++this.numberofImagesLoaded;
                if(this.numberImagesToLoad == this.numberofImagesLoaded){
                    this.allImagesLoaded = true;
                    this.onAllImagesLoaded();
                }
            }.bind(this,image));
            image.hide();
        }.bind(this));
    },
    onAllImagesLoaded: function(){
        this.getImages().each(function(image){
            this.showImage(image);
        }.bind(this));
        if(this.hasRemoteContent){
            if(this.options.indicator)
                this.hideIndicator();
            this.finishOpen();
        }else
            this.open();
    },
    getImages: function(){
        return this.container.select(Control.LightBox.imageSelector);
    },
    showImage: function(image){
        image.show();
    }
});
Object.extend(Control.LightBox,{
    imageSelector: 'img',
    defaultOptions: {},
    Observers: {
        beforeOpen: function(){
            if(!this.hasRemoteContent && !this.allImagesLoaded)
                throw $break;
        },
        onRemoteContentLoaded: function(){
            this.applyImageObservers();
            if(!this.allImagesLoaded)
                throw $break;
        }
    }
});
