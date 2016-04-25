// Copyright Zikula Foundation, licensed MIT.

/**
 * Zikula UI namespace
 *
 * @name Zikula.UI
 * @namespace Zikula UI namespace
 *
 */
Zikula.define('UI');

Zikula.UI.Key = Class.create(HotKey,/** @lends Zikula.UI.Key.prototype */{
    /**
     * Custom extension for Livepipe HotKey
     * Inherit all of the methods, options and events from
     * <a href="http://livepipe.net/extra/hotkey">Livepipe HotKey</a>.
     * Overwrites default ctrlKey option value and set is to false.
     *
     * @example
     * // note - $super param is omited
     * var key = new Zikula.UI.Key('esc',callback,{ctrlKey: true});
     *
     * @class Zikula.UI.Key
     * @constructs
     *
     * @param {HotKey} $super Reference to super class, this is private param, do not use it.
     * @param {String} letter Letter or prototype key code
     * @param {Function} callback Callback function
     * @param {Object} [options] Config object
     * @param {Boolean} [options.ctrlKey=false] Should crtl key be pressed to fire event
     *
     * @return {Zikula.UI.Key} New Zikula.UI.Key instance
     */
    initialize: function($super,letter,callback,options) {
        options = Object.extend({
            ctrlKey: false
        }, options || { });
        $super(letter,callback,options);
    }
});

/**
 * Zikula.UI.Tooltips
 * Shorthand for group of tooltips, calls {Zikula.UI.Tooltip} for each element
 *
 * @example
 * Zikula.UI.Tooltips($$('.hasTooltip'));
 *
 * @param {HTMLElement[]} elements Array of elements to bind tooltips
 * @param {Object} options Object with options for tooltips
 *
 * @return void
 */
Zikula.UI.Tooltips = function(elements,options)
{
    $A(elements).each(function(e){
        new Zikula.UI.Tooltip($(e),null,options)
    })
}

Zikula.UI.Tooltip = Class.create(Control.ToolTip,/** @lends Zikula.UI.Tooltip.prototype */{
    /**
     * Custom extension for Livepipe Control.ToolTip
     * Inherit all of the methods, options and events from
     * <a href="http://livepipe.net/control/window">Livepipe Control.ToolTip</a>.
     * Overwrites listed below options.
     *
     * @example
     * // note - $super param is omited
     * var tooltip = new Zikula.UI.Tooltip($('someElement'),null,{offsetTop:10});
     *
     * @class Zikula.UI.Tooltip
     * @constructs
     *
     * @param {Control.ToolTip} $super Reference to super class, this is private param, do not use it.
     * @param {HTMLElement} container Element to bind tooltip
     * @param {HTMLElement|String} [tooltip] Element or string for tooltip content, if none title attribute is used
     * @param {Object} [options] Config object
     * @param {String} [options.className='z-tooltip'] Base class for tooltips
     * @param {Number} [options.offsetTop=0] Top offset between tooltip and cursor
     * @param {Number} [options.offsetLeft=15] Left offset between tooltip and cursor
     * @param {Boolean} [options.iframeshim=Zikula.Browser.IE] Should iframeshim be used; by defaut it's hown only for IE
     *
     * @return {Zikula.UI.Tooltip} New Zikula.UI.Tooltip instance
     */
    initialize: function($super, container, tooltip, options) {
        options = Object.extend({
            className: 'z-tooltip',
            offsetTop: 20,
            offsetLeft: 15,
            iframeshim: Zikula.Browser.IE
        }, options || { });
        if(!tooltip) {
            if (container.hasAttribute('title')) {
                tooltip = container.readAttribute('title');
                container.store('title',tooltip);
                container.writeAttribute('title','');
                if(tooltip.startsWith('#')) {
                    this.tooltipContent = $(tooltip.replace("#", ""));
                    document.body.insert(this.tooltipContent);
                    tooltip = this.tooltipContent;
                }
            }
        }
        $super(container, tooltip, options);
    },
    /**
     * Takes care about tooltips position.
     * This overwrites default Control.Toolip position behaviour which is quite buggy.
     *
     * @private
     * @param {Function} $super Reference to overridden method, private.
     * @param {Event} event Mause hoover event
     *
     * @return void
     */
    position: function($super,event) {
        var dim = this.container.getDimensions(),
            vSize = document.viewport.getDimensions(),
            vtOffset = document.viewport.getScrollOffsets(),
            offset = {v: $value(this.options.offsetTop), h: $value(this.options.offsetLeft)},
            x= event ? Event.pointerX(event): this.sourceContainer.getLayout().get('left'),
            y = event ? Event.pointerY(event): this.sourceContainer.getLayout().get('top'),
            pos = {left:'auto',right:'auto',top:'auto',bottom:'auto'};
        if (x + dim.width + (offset.h * 2) < vSize.width || dim.width + offset.h > vSize.width) {
            pos.left = (x + offset.h + vtOffset.left).toUnits();
        } else {
            pos.right = (vSize.width - x + offset.h).toUnits();
        }
        if (y + dim.height + offset.v < vtOffset.top + vSize.height || dim.height + offset.v > vSize.height) {
            pos.top = (y + offset.v).toUnits();
        } else {
            pos.top = (vSize.height + vtOffset.top - dim.height - offset.v).toUnits();
        }
        this.container.setStyle(pos);
    },
    /**
     * Removes tooltip and cleans up
     *
     * @param {Function} $super Reference to overridden method, private.
     *
     * @return void
     */
    destroy: function($super) {
        if(this.sourceContainer) {
            this.sourceContainer.writeAttribute('title',this.sourceContainer.retrieve('title'));
        }
        if(this.tooltipContent) {
            this.sourceContainer.insert(this.tooltipContent);
        }
        $super();
    }
});
/**
 * Global Zikula.UI template
 *
 * @param {Object} options Zikula.UI.Options
 *
 * @return {HTMLElement[]}
 */
Zikula.UI.WindowTemplate = function(options) {
    return {
        container:  new Element('div',{className: 'z-window-container'}),
        header:     new Element('div',{className: 'z-window-header'}),
        title:      new Element('div',{className: 'z-window-title'}).update('&nbsp;'),
        close:      new Element('div',{className: 'z-window-close z-window-control'}),
        minimize:   new Element('div',{className: 'z-window-minimize z-window-control'}).hide(),
        maximize:   new Element('div',{className: 'z-window-maximize z-window-control'}).hide(),
        body:       new Element('div',{className: 'z-window-body'}),
        indicator:  new Element('div',{className: 'z-window-indicator'}),
        footer:     new Element('div',{className: 'z-window-footer'}).update('&nbsp;')
    };
}

Zikula.UI.Window = Class.create(Control.Window,/** @lends Zikula.UI.Window.prototype */{
    /**
     * Custom extension for Livepipe Control.Window
     * Inherit all of the methods, options and events from
     * <a href="http://livepipe.net/control/window">Livepipe Control.Window</a>.
     * Overwrites listed below options and methods.
     *
     * @example
     * // note - $super param is omited
     * var myWindow = new Zikula.UI.Window($('someElement'),{minmax: false});
     *
     * @class Zikula.UI.Window
     * @constructs
     *
     * @param {Control.Window} $super Reference to super class, this is private param, do not use it.
     * @param {HTMLElement|String} container HTMLElement, element id or direct text for window
     * @param {Object} [options] Config object
     * @param {String} [options.className='z-window'] Base css class for windows
     * @param {Boolean} [options.minmax=true] Turn on/off window minimization
     * @param {Number} [options.width=400] Default window width
     * @param {Number} [options.initMaxHeight=400] Default window height for ajax windows
     * @param {Number[]} [options.offset=[50,50]] Minimal left and top offsets from browsers edge
     * @param {HTMLElement} [options.indicator] Element used as loading indicator for ajax windows
     * @param {Number} [options.overlayOpacity=0.3] Overlay opacity for modal windows
     * @param {String} [options.method='get'] Method for ajax calls
     * @param {Boolean} [options.modal=false] Should widnow be modal
     * @param {Boolean} [options.destroyOnClose=false] Should window be destroyed on close
     * @param {Boolean} [options.iframeshim=Zikula.Browser.IE] Should iframeshim be used; by defaut it's hown only for IE
     * @param {HTMLElement} [options.closeOnClick] Element which handles close action
     * @param {Boolean|HTMLElement} [options.draggable] Element which handles dragging or false to disable
     * @param {HTMLElement} [options.insertRemoteContentAt]
     * @param {Number} [options.autoClose=0] Time in second after which window will be automatically closed, 0 to disable
     *
     * @return {Zikula.UI.Window} New Zikula.UI.Window instance
     */
    initialize: function($super, container, options) {
        this.setWindowType(container);
        this.window = Zikula.UI.WindowTemplate(options);
        container = this.initContainer(container,options);
        options = Object.extend({
            className: 'z-window',
            minmax: true,
            width: 400,
            initMaxHeight: 400,
            offset: [0,0],//left, top
            indicator: this.window.indicator,
            overlayOpacity: 0.3,
            method: 'get',
            modal: false,
            destroyOnClose: false,
            iframeshim: Zikula.Browser.IE,
            closeOnClick: this.window.close,
            draggable: this.window.header,
            insertRemoteContentAt: this.window.body,
            autoClose: 0
        }, options || { });
        if(options.modal) {
            options.minmax = false;
            Control.Modal.InstanceMethods.beforeInitialize.bind(this)();
        }
        $super(container, options);
        if(this.draggable) {
            this.draggable.options.scroll = window;
        }
        this.finishContainer();
        this.setWindowMaxSize();
        this.setWindowMaxSizeHandler = this.setWindowMaxSize.bindAsEventListener(this);
        Event.observe(window,'resize',this.setWindowMaxSizeHandler,false);
        this.openHandler = this.open.bindAsEventListener(this);
        this.closeHandler = this.close.bindAsEventListener(this);
        this.key = new Zikula.UI.Key('esc',this.closeHandler,{
            element: this.container
        });
    },
    /**
     * Brings window to front and marks it as 'active'
     *
     * @private
     * @param {Function} $super Reference to overridden method, private.
     *
     * @return void
     */
    bringToFront: function($super) {
        $super();
        if (!this.container.hasClassName('active')) {
            $$('.z-window.active').invoke('removeClassName','active');
            this.container.addClassName('active');
            this.focusWindow();
        }
    },
    /**
     * Sets window max size depending on browser viewport dimensions
     * @private
     * @return void
     */
    setWindowMaxSize: function() {
        var dim = document.viewport.getDimensions()
        this.container.setStyle({
            maxWidth: (dim.width - this.container.getOutlineSize('h') - this.options.offset[0]).toUnits(),
            maxHeight: (dim.height - this.container.getOutlineSize() - this.options.offset[1]).toUnits()
        })
    },
    /**
     * Calculates window content "topOffset" which referes to window header height and window border plus margins
     * @private
     * @return {Number}
     */
    getTopOffset: function() {
        return this.window.header.getHeight() + this.window.header.getOutlineSize();
    },
    /**
     * Calculates window content "bottomOffset" which referes to window footer height and window border plus margins
     * @private
     * @return {Number}
     */
    getBottomOffset: function() {
        return this.window.footer.getHeight() + this.window.footer.getOutlineSize();
    },
    /**
     * Calculates proper window height
     * For ajax windows takes this.options.initMaxHeight.
     * For other window types tries to adjust it to window content
     * @private
     * @return {Number}
     */
    getWindowHeight: function() {
        if (this.calculated) {
            return this.container.getHeight();
        }
        var height = this.container.getHeight(),
            header = this.getTopOffset(),
            body = this.window.body.getHeight(),
            footer = this.getBottomOffset();
        if(this.windowType == 'ajax' && this.options.initMaxHeight) {
            height = this.options.initMaxHeight;
        } else if(height < header + body + footer) {
            height = height + header + footer;
        }
        this.calculated = true;
        return height;
    },
    /**
     * Open the window
     * @private
     * @param {Event} event Event which fired window opening
     * @return {Boolean}
     */
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
                    this.ajaxRequest = new Zikula.Ajax.Request(this.href,{
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
        if (Object.isNumber(this.options.autoClose) && this.options.autoClose > 0) {
            this.autoClose = this.close.bind(this).delay(this.options.autoClose);
        }
        this.finishOpen(event);
        return true
    },
    /**
     * Finishing window opening process and take care about window size
     * @private
     * @param {Function} $super Reference to overridden method, private.
     * @param {Event} event Event which fired window opening
     * @return {Boolean}
     */
    finishOpen: function($super, event){
        $super(event);
        this.container.setHeight(this.getWindowHeight());
        var bodyStyle = {
            position: 'absolute',
            maxHeight: 'none',
            top: this.getTopOffset().toUnits(),
            bottom: this.getBottomOffset().toUnits()
        };
        if(this.indicator) {
            this.window.indicator.setStyle(bodyStyle);
            if(Zikula.Browser.IE) {
                var container = this.container,
                    indicator = this.indicator;
                new PeriodicalExecuter(function(pe) {
                    if(container.down('iframe')) {
                        var iframe = container.down('iframe');
                        if (iframe.document.readyState == 'complete') {
                            pe.stop();
                            indicator.hide();
                        }
                    } else {
                        pe.stop();
                        indicator.hide();
                    }
                }, 1);
            }
        }

        if(this.window.body.down('iframe')) {
            this.window.body.addClassName('iframe');
        }
        this.window.body.setStyle(bodyStyle);
        this.initialWidth = this.container.getWidth();
        this.ensureInBounds();
        this.position();
        this.focusWindow();
        return true;
    },
    /**
     * Checks if window does not exceed browser viewport.
     * Overwrites Control.Window.ensureInBounds method
     * @private
     * @return void
     */
    ensureInBounds: function(){
        if(!this.isOpen)
            return;
        var viewport_dimensions = document.viewport.getDimensions(),
            viewport_offset = document.viewport.getScrollOffsets(),
            container_offset = this.container.cumulativeOffset(),
            container_dimensions = this.container.getDimensions(),
            pos = {};
        if (container_offset[1] < viewport_offset[1]) {
            pos.top = viewport_offset[1].toUnits();
        } else if (container_offset[1]+container_dimensions.height > viewport_offset[1]+viewport_dimensions.height) {
            pos.top = (viewport_offset[1]+viewport_dimensions.height-container_dimensions.height).toUnits();
        }
        if (container_offset[0] < viewport_offset[0]) {
            pos.left = viewport_offset[0].toUnits();
        } else if (container_offset[0]+container_dimensions.width > viewport_offset[0]+viewport_dimensions.width) {
            pos.left = (viewport_offset[0]+viewport_dimensions.width-container_dimensions.width).toUnits();
        }
        if(pos.top || pos.left) {
            this.container.setStyle(pos);
        }
    },
    /**
     * Close window.
     * If there are other opened windows - tries to bring to front and set focus on most top window
     *
     * @param {Function} $super Reference to overridden method, private.
     * @param {Event} [event] Event which fired window closing
     * @return {Boolean}
     */
    close: function($super, event) {
        if (Object.isNumber(this.autoClose)) {
            window.clearTimeout(this.autoClose);
        }
        this.restore(event);
        this.pos = {};
        if(this.initialWidth) {
            this.container.setStyle({
                width:this.initialWidth.toUnits()
            });
        }
        $super.defer(event);
        if(this.options.destroyOnClose) {
            this.destroy();
        }
        var windows = Control.Window.windows.clone(),x;
        while(x = windows.pop()) {
            if(x.isOpen) {
                x.bringToFront();
                break;
            }
        }
        return true;
    },
    /**
     * Maximize window or restores it to normal position
     *
     * @param {Event} [event]
     * @return void
     */
    toggleMax: function(event) {
        if(this.container.hasClassName('z-maximized')) {
            this.restore(event);
            this.restorePosition(event);
        } else {
            this.maximize(event);
        }
    },
    /**
     * Minimize window or restores it to normal position
     *
     * @param {Event} [event]
     * @return void
     */
    toggleMin: function(event) {
        if(this.container.hasClassName('z-minimized')) {
            this.restore(event);
            this.restorePosition(event);
        } else {
            this.minimize(event);
        }
    },
    /**
     * Maximize window
     * @param {Event} [event]
     * @return void
     */
    maximize: function(event) {
        this.savePosition();
        this.restore(event);
        this.container.addClassName('z-maximized');
        $(document.body).setStyle({overflow: 'hidden'});
        if(this.draggable) {
            Draggable._dragging[this.container] = true;
        }
    },
    /**
     * Minimize window
     * @param {Event} [event]
     * @return void
     */
    minimize: function(event) {
        this.savePosition();
        this.restore(event);
        this.container.addClassName('z-minimized');
        if(this.draggable) {
            this.draggable.options.constraint = 'horizontal';
        }
    },
    /**
     * Restore window to normal position
     * @param {Event} [event]
     * @return void
     */
    restore: function(event) {
        this.container.removeClassName('z-minimized');
        this.container.removeClassName('z-maximized');
        $(document.body).setStyle({overflow: 'visible'});
        if(this.draggable) {
            this.draggable.options.constraint = false;
            Draggable._dragging[this.container] = false;
        }
    },
    /**
     * Saves window position before minimizing or maximizing
     * @private
     * @return void
     */
    savePosition: function() {
        if(!this.container.hasClassName('z-minimized') && !this.container.hasClassName('z-maximized')) {
            var dim = this.container.getDimensions(),
                offset = this.container.viewportOffset()
            this.pos = {
                top: offset[1],
                left: offset[0],
                width: dim.width.toUnits(),
                height: dim.height.toUnits()
            }
        }
    },
    /**
     * Restores window position after minimizing or maximizing
     * @private
     * @return void
     */
    restorePosition: function() {
        if(this.pos) {
            var viewport_offset = document.viewport.getScrollOffsets();
            this.pos.top = (viewport_offset[1] + this.pos.top).toUnits();
            this.pos.left = (viewport_offset[0] + this.pos.left).toUnits();
            this.container.setStyle(this.pos);
        }
    },
    /**
     * Set window type according to container type
     * @private
     * @param {HTMLElement|String} container Window container
     * @return void
     */
    setWindowType: function(container) {
        this.windowType = 'string';
        if(Object.isElement(container)) {
            this.windowType = 'element';
            if(container.hasAttribute('href')) {
                var href = container.readAttribute('href');
                if(href.startsWith('#')) {
                    this.windowType = 'relelement';
                } else {
                    this.windowType = 'ajax';
                }
            }
        }
    },
    /**
     * Sets focus on window element
     * @return void
     */
    focusWindow: function() {
        try {
            this.container.focus()
        } catch(e) {}
    },
    /**
     * Adds for draggable windows with iframe overlay to aviod focus lost while dragging
     * @private
     * @param {Function} $super Reference to overridden method, private.
     * @return void
     */
    applyDraggable: function($super) {
        $super();
        if(this.options.iframe) {
            this.window.body.insert({
                top: new Element('div',{'class':'iframe-overlay'}).hide()
            })
            this.draggable.options.onStart = function(draggable) {
                draggable.element.down('.iframe-overlay').show();
            };
            this.draggable.options.onEnd = function(draggable) {
                 draggable.element.down('.iframe-overlay').hide();
            };
        }
    },
    /**
     * Adds for resizable windows with iframe overlay to aviod focus lost while resizing
     * @private
     * @param {Function} $super Reference to overridden method, private.
     * @return void
     */
    applyResizable: function($super) {
        $super();
        var resizable_handle = this.container.down('.resizable_handle'),
            disableSelection = function (e) {e.stop()};
        if(resizable_handle) {
            Resizables.addObserver({
                onStart: function(){
                    $(document.body).observe('selectstart',disableSelection);
                    $(document.body).observe('mousedown',disableSelection);
                    resizable_handle.addClassName('onresize');
                },
                onEnd: function(){
                    $(document.body).stopObserving('selectstart',disableSelection);
                    $(document.body).stopObserving('mousedown',disableSelection);
                    resizable_handle.removeClassName('onresize');
                }
            });
        }
    },
    /**
     * Initializing window container
     * @private
     * @return void
     */
    initContainer: function(container) {
        if(this.windowType == 'relelement') {
            this.insertContainer();
            var href = container.readAttribute('href');
            var rel = href.match(/^#(.+)$/);
            if(rel && rel[1]){
                this.window.body.insert($(rel[1]).show());
                this.window.container.id = 'Zikula_UI_Window_'+rel[1]
                container.writeAttribute('href','#'+this.window.container.id);
            }
        }
        else if (this.windowType == 'element') {
            this.insertContainer();
            this.window.body.insert(container.show());
            this.window.container.id = 'Zikula_UI_Window_'+container.identify();
            container = this.window.container;
        }
        return container;
    },
    /**
     * Inserts window container elements to document
     * @private
     * @return void
     */
    insertContainer: function() {
        if(!this.container){
            $(document.body).insert(this.window.container);
        }
        this.window.container.insert(this.window.header);
        this.window.header.insert(this.window.title);
        this.window.header.insert(this.window.minimize);
        this.window.header.insert(this.window.maximize);
        this.window.header.insert(this.window.close);
        this.window.container.insert(this.window.body);
        this.window.container.insert(this.window.footer);
        this.window.container.insert(this.window.indicator.hide());
        this.inserted = true;
    },
    /**
     * Finishing window container build process
     * @private
     * @return void
     */
    finishContainer: function() {
        this.window.container.writeAttribute('tabindex','-1');
        if(this.options.title) {
            this.window.title.update(this.options.title);
        } else if(this.sourceContainer && this.sourceContainer.hasAttribute('title')) {
            this.window.title.update( this.sourceContainer.readAttribute('title'));
        }
        if(this.options.draggable) {
            this.window.container.addClassName('z-draggable');
        }
        if(this.options.modal) {
            this.window.container.addClassName('z-modal');
        }
        if(this.options.resizable) {
            this.window.container.addClassName('z-resizable');
        }
        if(this.options.minmax) {
            this.window.minimize.show().observe('click',this.toggleMin.bindAsEventListener(this));
            this.window.maximize.show().observe('click',this.toggleMax.bindAsEventListener(this));
        }
    },
    /**
     * Overwrites Control.Window.createDefaultContainer method to allow custom window template
     * @private
     * @return void
     */
    createDefaultContainer: function(container){
        if(!this.container){
            this.window.container.id = 'Zikula_UI_Window_' + this.numberInSequence;
            this.container = this.window.container;
            $(document.body).insert(this.container);
            this.insertContainer()
            if(typeof(container) == "string" && $(container) == null && !container.match(/^#(.+)$/) && !container.match(Control.Window.uriRegex))
                this.window.body.update(container);
        }
    }
});

Zikula.UI.Dialog = Class.create(Zikula.UI.Window,/** @lends Zikula.UI.Dialog.prototype */{
    /**
     * Extension for {Zikula.UI.Window}, customized for dialog windows
     *
     * @example
     * // note - $super param is omited
     * var myDialog = new Zikula.UI.Dialog($('someElement'),[
     *     {label: 'Button label, action: doSomething, close: true}],
     *     {minmax: true}
     * );
     *
     * @class Zikula.UI.Dialog
     * @extends Zikula.UI.Window
     * @constructs
     *
     * @param {Zikula.UI.Window} $super Reference to super class, this is private param, do not use it.
     * @param {HTMLElement|string} container @param {HTMLElement|String} container HTMLElement, element id or direct text for dialog
     * @param {Object[]} [buttons] Array of button objects, each should define at least:<br />
     * - label - {String} label for button<br />
     * - action - {Function} action to perform after click<br />
     * - close - {Boolean} should dialog be close after button click
     * and any other atribute valid for button element
     * @param {Object} [options] Config object
     * @param {String} [options.className='z-window z-dialog'] Base class names for dialog window
     * @param {Function} [options.callback='Prototype.emptyFunction'] Callback fuction called when button without specified action was clicked. As argument clicked button object is passed.
     *
     * @return {Zikula.UI.Dialog} New window object
     */
    initialize: function($super, container, buttons, options) {
        options = Object.extend({
            className: 'z-window z-dialog',
            callback: Prototype.emptyFunction
        }, options || { });
        options.afterClose = this.notifyCallback.curry(false);
        $super(container, options);
        this.window.footer.addClassName('z-buttons');
        this.buttons = {};
        this.insertButtons(buttons);
    },
    /**
     * Open dialog
     *
     * @param {Function} $super Reference to overridden method, private.
     * @param {Event} [event] Event which fired window closing
     * @return {Boolean}
     */
    open: function($super, event) {
        this.isNotified = false;
        $super(event);
    },
    /**
     * Sets focus on dialog window
     * @return void
     */
    focusWindow: function() {
        try {
            this.buttons[Object.keys(this.buttons)[0]].focus();
        } catch(e) {}
    },
    /**
     * Calls callback function after button click
     * @private
     * @param {Object} button Clicked button
     * @return void
     */
    notifyCallback: function(button) {
        if(!this.isNotified) {
            this.options.callback(button);
            this.isNotified = typeof button.close !== 'undefined' ? button.close : true;
        }
    },
    /**
     * Inserts to dialog window buttons
     * @private
     * @param {Object[]} buttons Array with buttons to insert
     * @return void
     */
    insertButtons: function(buttons) {
        $A(buttons).each(function(button){
            this.button(button);
        }.bind(this));
    },
    /**
     * Insert button to dialog window
     * Converts button object to button element, decodes button action
     * @private
     * @param {Object} button Button object
     * @return void
     */
    button: function(button) {
        // 'id class lang dir title style disabled accesskey tabindex name value type'
        var action = button.action || this.notifyCallback.bindAsEventListener(this),
            btnAttr = {},
            btnAttrKeys = Object.keys(button).intersect($w('id class lang dir title style disabled accesskey tabindex name value type'));
        btnAttrKeys.each(function(a){
            btnAttr[a] = button[a]
        })
        button.close = typeof button.close !== 'undefined' ? button.close : true;
        var buttonElement =  new Element('button',btnAttr).update(button.label);
        this.window.footer.insert(buttonElement);
        this.buttons[buttonElement.identify()] = buttonElement;
        if(Object.isFunction(action)) {
            buttonElement.observe('click',action.curry(button))
        }
        if(button.close) {
            buttonElement.observe('click',this.close.bindAsEventListener(this));
        }
    }
});

/**
 * Static shorthand for Zikula.UI.AlertDialog
 *
 * @example
 * Zikula.UI.Alert('This is alter dialog message!','Alert dialog title');
 *
 * @param {String} text Alert message
 * @param {String} [title] Title for alert window
 * @param {Object} [options] Config object
 *
 * @return {Zikula.UI.AlertDialog} New Zikula.UI.AlertDialog instance
 */
Zikula.UI.Alert = function(text, title, options){
    options = Object.extend({
        destroyOnClose: true,
        title: title
    },options);
    var dialog = new Zikula.UI.AlertDialog(text, options);
    dialog.open();
    return dialog;
};


Zikula.UI.AlertDialog = Class.create(Zikula.UI.Dialog,/** @lends Zikula.UI.AlertDialog.prototype */{
    /**
     * Preconfigured {Zikula.UI.Dialog} extension imitating Alert dialogs
     *
     * @example
     * // note - $super param is omited
     * var myAlert = new Zikula.UI.AlertDialog($('someElement'));
     *
     * @class Zikula.UI.AlertDialog
     * @extends Zikula.UI.Dialog
     * @constructs
     *
     * @param {Zikula.UI.Dialog} $super Reference to super class, this is private param, do not use it.
     * @param {HTMLElement|String} container HTMLElement, element id or direct text for dialog
     * @param {Object} [options] Config object
     * @param {String} [options.className='z-window z-dialog z-alert'] Base class names for dialog window
     * @param {Boolean} [options.minmax=false]
     *
     * @return {Zikula.UI.Dialog} New window object
     */
    initialize: function($super, container, options) {
        options = Object.extend({
            className: 'z-window z-dialog z-alert',
            minmax: false
        }, options || { });
        $super(container, this.defaultButtons(this.notifyCallback.bind(this)), options);
    },
    /**
     * Returns default buttons for this dialog.
     * Alert dialog has only "Ok" button
     * @private
     * @param {Function} callback This.notifyCallback
     * @return {Object[]} Array of objects with buttons for dialog
     */
    defaultButtons: function(callback) {
        return [
            {label: Zikula.__('Ok'), 'class': 'z-btgreen'}
        ]
    }
});

/**
 * Callable shorthand which allows to use Zikula.UI.ConfirmDialog as callback function.
 *
 * @example
 * // after user click "someElement" Zikula.UI.ConfirmDialog is show
 * // when user confirm - deleteAction action is performed
 * $('someElement').observe('click',
 *     Zikula.UI.IfConfirmed('Do you want to remove test element?','Confirmation prompt',deleteAction)
 * );
 *
 * @param {String} text Confirm message
 * @param {String} title Title for confirm window
 * @param {Object} callback Callback called when user confirm
 * @param {Object} [options] Config object
 *
 * @return {mixed} Exectutes callback function and return it result
 */
Zikula.UI.IfConfirmed = function(text, title, callback, options){
    return Zikula.UI.Confirm.curry(text, title, callback, options);
};

/**
 * Static shorthand for Zikula.UI.ConfirmDialog
 *
 * @param {String} text Confirm message
 * @param {String} title Title for confirm window
 * @param {Object} callback Callback called when user confirm
 * @param {Object} [options] Config object
 *
 * @return {Zikula.UI.ConfirmDialog} New Zikula.UI.ConfirmDialog instance
 */
Zikula.UI.Confirm = function(text, title, callback, options){
    options = Object.extend({
        destroyOnClose: true,
        title: title,
        callback: callback
    },options);
    var dialog = new Zikula.UI.ConfirmDialog(text, options);
    dialog.open();
    return dialog;
};

Zikula.UI.ConfirmDialog = Class.create(Zikula.UI.Dialog,/** @lends Zikula.UI.ConfirmDialog.prototype */{
    /**
     * Preconfigured {Zikula.UI.Dialog} extension imitating Confirm dialogs
     *
     * @example
     * // note - $super param is omited
     * var myConfirm = new Zikula.UI.ConfirmDialog($('someElement'));
     *
     * @class Zikula.UI.ConfirmDialog
     * @extends Zikula.UI.Dialog
     * @constructs
     *
     * @param {Zikula.UI.Dialog} $super Reference to super class, this is private param, do not use it.
     * @param {HTMLElement|String} container HTMLElement, element id or direct text for dialog
     * @param {Object} [options] Config object
     * @param {String} [options.className='z-window z-dialog z-confirm'] Base class names for dialog window
     * @param {Boolean} [options.minmax=false]
     *
     * @return {Zikula.UI.Dialog} New window object
     */
    initialize: function($super, container, options) {
        options = Object.extend({
            className: 'z-window z-dialog z-confirm',
            minmax: false
        }, options || { });
        $super(container, this.defaultButtons(this.notifyCallback.bind(this)), options);
    },
    /**
     * Returns default buttons for this dialog.
     * Confirm dialog has "Ok" and "Cancel" buttons
     * @private
     * @param {Function} callback This.notifyCallback
     * @return {Object[]} Array of objects with buttons for dialog
     */
    defaultButtons: function(callback) {
        return [
            {label: Zikula.__('Ok'), action: callback.curry(true), 'class': 'z-btgreen'},
            {label: Zikula.__('Cancel'), action: callback.curry(false), 'class': 'z-btred'}
        ]
    }
});
Zikula.UI.FormDialog = Class.create(Zikula.UI.Dialog,/** @lends Zikula.UI.FormDialog.prototype */{
    /**
     * Preconfigured {Zikula.UI.Dialog} extension which allows for easy creating dialogs with forms
     *
     * @example
     * // note - $super param is omited
     * // someElement should contain form element without submit or button elements
     * var myForm = new Zikula.UI.FormDialog($('someElement'));
     *
     * @class Zikula.UI.FormDialog
     * @extends Zikula.UI.Dialog
     * @constructs
     *
     * @param {Zikula.UI.Dialog} $super Reference to super class, this is private param, do not use it.
     * @param {HTMLElement|String} container HTMLElement (or element id) containing form.
     * @param {Function} callback Callback function executed when form is submited and form has no action attribute
     * @param {Object} [options] Config object
     * @param {String} [options.className='z-window z-dialog z-form'] Base class names for dialog window
     * @param {Number} [options.width=500]
     * @param {Boolean} [options.ajaxRequest=false] Whether ajax request should be made after form submit, if set to true form action attribute will be used
     * @param {Boolean} [options.resizable=true]
     * @param {Function} [options.callback=callback]
     * @param {Array} [options.buttons] Array of button objects to use instead of default submit and cancel buttons
     *
     * @return {Zikula.UI.Dialog} New window object
     */
    initialize: function($super, container, callback, options) {
        options = Object.extend({
            className: 'z-window z-dialog z-form',
            width: 500,
            ajaxRequest: false,
            resizable: true,
            callback: callback
        }, options || { });
        $super(container, options.buttons || this.defaultButtons(this.notifyCallback.bind(this)), options);
    },
    /**
     * Sets focus on first form element in dialog window
     * @return void
     */
    focusWindow: function() {
        try {
            this.container.down('form').focusFirstElement();
        } catch(e) {}
    },
    /**
     * Serialize dialog form using prototypes Form.serialize method
     * 
     * @param {Boolean} [object] If true method returns object, otherwise string
     * @return {mixed} Object or string with form values
     */
    serialize: function(object) {
        try {
            return this.container.down('form').serialize(object);
        } catch(e) {
            return object ? {} : '';
        }
    },
    /**
     * Calls callback function after button click.
     * If form has define action attribute and options.ajaxRequest is set to true
     *  - form is exectuded via ajax request and callback function is called on requests complete.
     * Otherwise callback is called just after form is submitted
     *
     * @private
     * @param {Object} button Clicked button
     * @return void
     */
    notifyCallback: function(button) {
        if(!this.isNotified) {
            var form =  this.container.down('form'),
                buttonData = {},
                result;
            if(button && button.name) {
                buttonData[button.name] = button.value;
            }
            if(form.action && form.readAttribute('action') != '#' && button) {
                this.isNotified = true;
                if(this.options.ajaxRequest) {
                    form.request({
                        parameters: buttonData,
                        onComplete: this.options.callback.bind(this)
                    });
                } else {
                    if(button && button.name) {
                        var type = button.name == 'submit' ? 'submit' : 'hidden';
                        form.insert(new Element('input',{type: type, name: button.name, value: button.value}));
                    }
                    try {
                        form.submit();
                    } catch(e) {
                        form.submit.click();
                    }
                }
            } else {
                if(button) {
                    result = Object.extend(form.serialize(true),buttonData);
                } else {
                    result = false;
                }
                this.options.callback(result);
                this.isNotified = typeof button.close !== 'undefined' ? button.close : true;
            }
        }
    },
    /**
     * Returns default buttons for this dialog.
     * Form dialog has "Submit" and "Cancel" buttons
     * @private
     * @param {Function} callback This.notifyCallback
     * @return {Object[]} Array of objects with buttons for dialog
     */
    defaultButtons: function(callback) {
        return [
            {label: Zikula.__('Submit'), type: 'submit', name: 'submit', value: 'submit', 'class': 'z-btgreen'},
            {label: Zikula.__('Cancel'), action: callback.curry(false), 'class': 'z-btred'}
        ]
    }
});


Zikula.UI.SelectMultiple = Class.create(Control.SelectMultiple,/** @lends Zikula.UI.SelectMultiple.prototype */{
    /**
     * Custom extension for Livepipe Control.SelectMultiple
     * Inherit all of the methods, options and events from
     * <a href="http://livepipe.net/control/selectmultiple">Livepipe Control.SelectMultiple</a>.
     * Overwrites listed below options and methods.
     *
     * @example
     * // note - $super param is omited
     * var select_multiple = new Zikula.UI.SelectMultiple('select_multiple');
     *
     * @class Zikula.UI.SelectMultiple
     * @constructs
     *
     * @param {Control.SelectMultiple} $super Reference to super class, this is private param, do not use it.
     * @param {HTMLElement|String} select HTMLElement or id of select element which should be converted to multiple select
     * @param {Object} [options] Config object
     * @param {String} [options.nameSelector='label'] Selector for options values
     * @param {String} [options.valueSeparator=','] Separator used for multiple values. When user will choose two or more select values - they will be joined by this separator
     * @param {Array} [options.excludeValues=[]] Allows to exclude from multiple select options with given values (such as "Select something form this list" with value="null")
     * @param {Number} [options.opener=null] Element, which will open dialog with multiple options, when null - opener is build automatically
     * @param {String} [options.title='Select multiple'] Name for automatically build opener
     * @param {String} [options.windowTitle=null] Title for multiple select dialog window when opener is build automatically. If null - option.title is used
     * @param {String} [options.okLabel='Ok'] Custom label for button inside multiple select dialog window 
     *
     * @return {Zikula.UI.SelectMultiple} New Zikula.UI.SelectMultiple instance
     */
    initialize: function($super, select, options) {
        if(options && options.afterChange) {
            this.origAfterChange = options.afterChange;
        }
        options = Object.extend({
            nameSelector: 'label',
            valueSeparator: ',',
            excludeValues: [],
            opener: null,
            title: Zikula.__('Select multiple'),
            windowTitle: null,
            okLabel: Zikula.__('Ok'),
            afterChange: this.afterChange.bind(this)
        }, options || { });
        select = $(select);
        if(!options.value) {
            var opts = select.select('option[selected]');
            options.value = opts.pluck('value').join(options.valueSeparator);
            opts.invoke('writeAttribute','selected',false);
        }
        var container = this.buildContainer(select,options);
        $super(select, container, options);
    },
    /**
     * Creates Zikula.UI.Dialog containing multiple choises for multiple select
     * Also when option.opener is null - creates element for opening such dialog
     *
     * @private
     * @param {HTMLElement} select Select element
     * @param {Object} options Config object
     *
     * @returns {HTMLElement} Form element with multiple choises
     */
    buildContainer: function(select,options) {
        var opener = options.opener || null,
            selectId = select.identify(),
            openerId = selectId+'_opener',
            containerId = selectId+'_options';
        if(!opener) {
            opener = new Element('a',{
                id:openerId,
                href:'#'+containerId,
                title: options.windowTitle || options.title
            }).update(options.title);
            select.insert({after:opener});
        } else {
            opener = $(opener);
            opener.writeAttribute('href', '#'+containerId);
        }
        var container = new Element('div',{id:containerId,'class':'z-select-multiple z-form'});
        $(opener).insert({after:container});
        options.excludeValues = $A(Object.isArray(options.excludeValues) ? options.excludeValues : [options.excludeValues]) || [];
        select.select('option').each(function(option) {
            if(!options.excludeValues.include(option.value)) {
                container.insert(
                    new Element('div',{'class':'z-formrow'})
                        .insert(new Element('label',{'for':option.identify()+'m'})
                            .update(option.text)
                        )
                        .insert(new Element('input',{id:option.identify()+'m',name:select.name+'[]',type:'checkbox',value:option.value,checked:option.selected}))
                )
            }
        });
        this.dialog = new Zikula.UI.Dialog(opener,[{label: options.okLabel}],{position:'relative'});
        return container;
    },
    /**
     * Method called to mark selected options
     * @private
     * @param {HTMLElement[]} elements Checkboxes
     * @return void
     */
    afterChange: function(elements) {
        this.checkboxes.each(function(checkbox){
            if(checkbox.checked) {
                checkbox.up('div.z-formrow').addClassName('selected');
            } else {
                checkbox.up('div.z-formrow').removeClassName('selected');
            }

        });
        if(this.origAfterChange) {
            this.origAfterChange(elements);
        }
    }
});

Zikula.UI.Tabs = Class.create(Control.Tabs,/** @lends Zikula.UI.Tabs.prototype */{
    /**
     * Custom extension for Livepipe Control.Tabs
     * Inherit all of the methods, options and events from
     * <a href="http://livepipe.net/control/tabs">Livepipe Control.Tabs</a>.
     * Overwrites listed below options and methods.
     *
     * @example
     * // note - $super param is omited
     * var myTabs =  new Zikula.UI.Tabs('tabs_example_eq',{equal: true});
     *
     * @class Zikula.UI.Tabs
     * @constructs
     *
     * @param {Control.Tabs} $super Reference to super class, this is private param, do not use it.
     * @param {HTMLElement|String} tab_list_container HTMLElement or id element containing unordered list of links, which href attributes points to tabs elements
     * @param {Object} [options] Config object
     * @param {Boolean} [options.equal=false] Should all tabs have equal height
     * @param {String} [options.containerClass='z-tabs'] Class to add for tabs
     * @param {Boolean} [options.setClassOnContainer=true]
     * @param {String} [options.activeClassName='active'] Class to mark active tab
     *
     * @return {Zikula.UI.Tabs} New Zikula.UI.Tabs instance
     */
    initialize: function($super, tab_list_container, options) {
        options = Object.extend({
            equal: false,
            containerClass: 'z-tabs',
            setClassOnContainer: true,
            activeClassName: 'active'
        }, options || { });
        $(tab_list_container).addClassName(options.containerClass);
        $super(tab_list_container, options);
        this.containers.values().invoke('addClassName', options.containerClass+'-content');
        if (this.options.equal) {
            this.alignTabs();
        }
    },
    /**
     * Activate given tab
     *
     * @param {Function} $super
     * @param {HTMLElement|String} link Tab element
     * @return void
     */
    setActiveTab: function($super,link) {
        $super(link);
        if (this.options.equal) {
            this.alignTabs();
        }
    },
    /**
     * Align tabs height
     *
     * @return void
     */
    alignTabs: function() {
        this.containers.values().invoke('show');
        this.maxHeight = this.containers.values().invoke('getContentHeight').max();
        this.containers.values().invoke('hide');
        this.activeContainer.show();
        this.containers.values().invoke('setStyle', {minHeight: this.maxHeight.toUnits()});
    }
});

Zikula.UI.Accordion = Class.create(/** @lends Zikula.UI.Accordion.prototype */{
    /**
     * Accordion script.
     * Markup for accordion container needs pairs of headers and content panels.
     * Each pair is identyfied by panel header.
     *
     * @example
     * var accordion =  new Zikula.UI.Accordion('accordion_container',{equal: true});
     *
     * @class Zikula.UI.Accordion
     * @constructs
     *
     * @param {HTMLElement|String} element HTMLElement or id element containing accordion content
     * @param {Object} [options] Config object
     * @param {Boolean} [options.equal=false] Should all accordion panels have equal height
     * @param {Number} [options.height=null] Height for all panels
     * @param {String} [options.headerSelector='.z-acc-header'] Selector to match panel headers
     * @param {String} [options.containerClass='z-accordion'] Class to add for accordion container
     * @param {String} [options.activeClassName='z-acc-active'] Class to mark active panel header and content
     * @param {String} [options.contentClassName=null] Class to mark the panel contents
     * @param {String|Number} [options.active=null] Id of index of panel to open; when id is given it should point to panel header, not content
     * @param {Boolean} [options.activateOnHash=false] If true - script will try to open panel pointed via url hash (it may be panel index or id)
     * @param {Boolean} [options.saveToCookie=false] If true - panel status will be saved to cookie and loaded on page refresh
     *
     * @return {Zikula.UI.Accordion} New Zikula.UI.Accordion instance
     */
    initialize: function(element, options) {
        this.options = Object.extend({
            equal: false,
            height: null,
            headerSelector: '.z-acc-header',
            containerClass: 'z-accordion',
            activeClassName: 'z-acc-active',
            contentClassName: null,
            active: null,
            activateOnHash: false,
            saveToCookie: false
        }, options || { });
        this.accordion = $(element);
        if(this.options.saveToCookie) {
            this.cookie = 'z-accordion:'+this.accordion.identify()
        }
        this.accordion.addClassName(this.options.containerClass);
        this.initPanels();
    },
    /**
     * Prepares accordion for display
     * 
     * @private
     * @return void
     */
    initPanels: function() {
        this.headers = this.accordion.select(this.options.headerSelector);
        if (!this.headers || this.headers.length === 0) return;
        this.contents = this.headers.map(function(h) {
            return h.next();
        });
        if (!this.options.active && this.options.saveToCookie) {
            this.options.active = Zikula.Cookie.get(this.cookie);
        }
        if (this.options.activateOnHash && window.location.hash) {
            var hash = window.location.hash.replace('#','');
            if (this.headers.include($(hash))) {
                this.options.active = this.headers.indexOf($(hash));
            } else if(this.headers[hash]) {
                this.options.active = hash;
            }
        }
        if (this.options.equal || this.options.height) {
            this.alignPanels();
        }
        this.headers.each(function(h,i){
            this.contents[i].hide();
            if (this.options.height) {
                this.contents[i].setStyle({height: this.options.height.toUnits(),overflow: 'auto'});
            }
            if (this.options.contentClassName) {
                this.contents[i].addClassName(this.options.contentClassName);
            }
            h.observe('click',this.click.bindAsEventListener(this));
        }.bind(this));
        if(this.options.active) {
            if(this.headers.include($(this.options.active))) {
                this.options.active = this.headers.indexOf($(this.options.active));
            } else if (Object.isElement(this.headers[this.options.active])) {
                this.options.active = this.options.active;
            } else {
                this.options.active = null;
            }
        }
        this.setActivePanel(this.options.active || this.headers.first(), true);
    },
    /**
     * Handler for click event on panel headers.
     *
     * @private
     * @param {Event} event Click event
     * @return void
     */
    click: function(event) {
        var header = event.findElement(this.options.headerSelector);
        if (!header || !this.headers.include(header)) return;
        this.setActivePanel(header);
    },
    /**
     * Activate given panel
     *
     * @param {String|Number} panel Panel to activate, it may be panel header id or index
     * @param {Boolean} [skipAnimation=false] Should activation be made without animation
     *
     * @return void
     */
    setActivePanel: function(panel, skipAnimation) {
        if (this.animating == true) return;
        var panelIndex;
        if (Object.isElement(panel) && this.headers.include(panel)) {
            panelIndex = this.headers.indexOf(panel);
        } else if (Object.isElement(this.headers[panel])) {
            panelIndex = panel;
            panel = this.headers[panel];
        } else {
            return;
        }
        if (panelIndex == this.activePanel) return;
        if (skipAnimation || Object.isUndefined(this.activePanel)) {
            [panel, panel.next().show()].invoke('addClassName',this.options.activeClassName);
            this.activePanel = panelIndex;
            if(this.options.saveToCookie) {
                Zikula.Cookie.set(this.cookie,this.activePanel);
            }
            return;
        }
        this.animate(panelIndex, this.activePanel);
    },
    /**
     * Internal animation method
     *
     * @private
     * @param {Number} show Index of panel to show
     * @param {Number} hide Index of panel to hide
     * @return void
     */
    animate: function(show,hide) {
        this.effects = [];
        var options = $H({
            sync: true,
            scaleContent: false,
            transition: Effect.Transitions.sinoidal
        });
        new Effect.Parallel([
            new Effect.BlindUp(this.contents[hide], options),
            new Effect.BlindDown(this.contents[show], options)
        ], {
            duration: 0.3,
            queue: {
                position: 'end',
                scope: 'accordionAnimation'
            },
            beforeStart: function() {
                this.animating = true;
                [this.headers[hide],this.contents[hide]].invoke('removeClassName',this.options.activeClassName);
            }.bind(this),
            afterFinish: function() {
                this.animating = false;
                [this.headers[show],this.contents[show]].invoke('addClassName',this.options.activeClassName);
                this.activePanel = show;
                if(this.options.saveToCookie) {
                    Zikula.Cookie.set(this.cookie,this.activePanel);
                }
                if(this.options.height) {
                    this.contents[show].setStyle({height: this.options.height.toUnits(),overflow: 'auto'});
                }
            }.bind(this)
        });
    },
    /**
     * Activate next panel.
     *
     * @return void
     */
    next: function(){
        this.setActivePanel(this.headers[this.activePanel+1] || this.headers.first());
    },
    /**
     * Activate previous panel.
     *
     * @return void
     */
    previous: function(){
        this.setActivePanel(this.headers[this.activePanel-1] || this.headers.last());
    },
    /**
     * Activate first panel.
     *
     * @return void
     */
    first: function(){
        this.setActivePanel(this.headers.first());
    },
    /**
     * Activate last panel.
     *
     * @return void
     */
    last: function(){
        this.setActivePanel(this.headers.last());
    },
    /**
     * Align panels hight.
     *
     * @return void
     */
    alignPanels: function() {
        if(!this.options.height) {
            this.options.height = this.contents.invoke('getHeight').max();
        }
        $A(this.contents).invoke('setStyle',{
            height: this.options.height.toUnits(),
            overflow: 'auto'
        });
    }
});

Zikula.UI.Panels = Class.create(/** @lends Zikula.UI.Panels.prototype */{
    /**
     * Panels script.
     * Panels are similar concept to Accordion with the exception that you can
     * open/close them - not just switch active one.
     * Markup for panels container needs pairs of headers and content panels.
     * Each pair is identyfied by panel header.
     *
     * @example
     * var panels =  new Zikula.UI.Panels('panels_container',{equal: true});
     *
     * @class Zikula.UI.Panels
     * @constructs
     *
     * @param {HTMLElement|String} element HTMLElement or id element containing panel
     * @param {Object} [options] Config object
     * @param {Boolean} [options.equal=false] Should all panels headers have equal height
     * @param {Number} [options.height=null] Default height for all panels contents
     * @param {Number} [options.minheight=null] Minimum height for all panels contents, which means they will be always visible
     * @param {String} [options.headerSelector='.z-panels-header'] Selector to match panel headers
     * @param {String} [options.containerClass='z-panels'] Class to add for panels container
     * @param {String} [options.activeClassName='z-panels-active'] Class to mark active panel header and content
     * @param {String} [options.headerClassName='z-panels-header'] Class to mark the panel headers
     * @param {String} [options.contentClassName=null] Class to mark the panel contents
     * @param {String|Number} [options.active=null] Id of index of panel to open; when id is given it should point to panel header, not content
     * @param {Boolean} [options.saveToCookie=false] If true - panel status will be saved to cookie and loaded on page refresh
     * @param {Float} [options.effectDuration=1.0] Duration of the content toggle effect
     *
     * @return {Zikula.UI.Panels} New Zikula.UI.Accordion instance
     */
    initialize: function(element, options) {
        this.options = Object.extend({
            equal: false,
            height: null,
            minheight: null,
            headerSelector: '.z-panel-header',
            containerClass: 'z-panels',
            activeClassName: 'z-panel-active',
            headerClassName: 'z-panel-header',
            contentClassName: null,
            active: [],
            saveToCookie: false,
            effectDuration: 1.0
        }, options || { });
        this.panels = $(element);
        if (this.options.saveToCookie) {
            this.cookie = 'z-panels:'+this.panels.identify()
        }
        if (!Object.isArray(this.options.active)) {
            this.options.active = [this.options.active].flatten();
        }
        this.panels.addClassName(this.options.containerClass);
        this.activePanels = [];
        this.animating = [];
        this.initPanels();
    },
    /**
     * Prepares accordion for display
     * 
     * @private
     * @return void
     */
    initPanels: function() {
        this.headers = this.panels.select(this.options.headerSelector);
        if (!this.headers || this.headers.length === 0) return;
        this.contents = this.headers.map(function(h) {
            return h.next();
        });
        if (!this.options.active.size() && this.options.saveToCookie) {
            this.options.active = Zikula.Cookie.get(this.cookie);
        }
        if (this.options.equal || this.options.height) {
            this.alignPanels();
        }
        this.headers.each(function(h,i){
            if (this.options.minheight) {
                var originalheight = this.contents[i].getContentHeight();
                this.contents[i].setStyle({height: this.options.minheight.toUnits()});
                if (originalheight <= this.options.minheight) {
                    // do not add it as a valid panel if the content equal or smaller than the minheight
                    return;
                }
                this.contents[i].store('fullheight', originalheight);
            } else {
                this.contents[i].hide();
            }
            if (this.options.contentClassName) {
                this.contents[i].addClassName(this.options.contentClassName);
            }
            h.addClassName(this.options.headerClassName);
            h.addClassName('z-pointer');
            h.observe('click', this.click.bindAsEventListener(this));
        }.bind(this));
        if (this.options.active.size()) {
            this.options.active.each(function(panel){
                this.expand(panel, true);
            }.bind(this));
        }
    },
    /**
     * Retruns panel index.
     * 
     * @private
     * @return void
     */
    getPanelIndex: function(panel) {
        var panelIndex;
        if (Object.isElement($(panel)) && this.headers.include($(panel))) {
            panelIndex = this.headers.indexOf($(panel));
        } else if (Object.isElement(this.headers[panel])) {
            panelIndex = panel;
        } else {
            panelIndex = null;
        }
        return panelIndex;
    },
    /**
     * Handler for click event on panel headers.
     *
     * @private
     * @param {Event} event Click event
     * @return void
     */
    click: function(event) {
        var header = event.findElement(this.options.headerSelector);
        if (!header || !this.headers.include(header)) return;
        this.toggle(header);
    },
    /**
     * Toggle given panel state.
     *
     * @param {String|Number} panel Panel to activate, it may be panel header id or index
     * @param {Boolean} [skipAnimation=false] Should activation be made without animation
     *
     * @return void
     */
    toggle: function(panel, skipAnimation) {
        panel = this.getPanelIndex(panel)
        if (this.activePanels.include(panel)) {
            this.collapse(panel, skipAnimation);
        } else {
            this.expand(panel, skipAnimation);
        }
    },
    /**
     * Expand given panel
     *
     * @param {String|Number} panel Panel to activate, it may be panel header id or index
     * @param {Boolean} [skipAnimation=false] Should activation be made without animation
     *
     * @return void
     */
    expand: function(panel, skipAnimation) {
        var panelIndex = this.getPanelIndex(panel);
        if (this.animating[panelIndex] == true) return;
        panel = this.headers[panelIndex];
        if (this.activePanels.include(panelIndex)) return;
        this.activePanels.push(panelIndex);
        if (this.options.saveToCookie) {
            Zikula.Cookie.set(this.cookie,this.activePanels);
        }
        if (skipAnimation) {
            if (this.options.minheight) {
                panel.next().setStyle({height: 'auto'});
            }
            [panel, panel.next().show()].invoke('addClassName', this.options.activeClassName);
            return;
        }
        this.animate(panelIndex, false);
    },
    /**
     * Collapse given panel
     *
     * @param {String|Number} panel Panel to activate, it may be panel header id or index
     * @param {Boolean} [skipAnimation=false] Should activation be made without animation
     *
     * @return void
     */
    collapse: function(panel, skipAnimation) {
        var panelIndex = this.getPanelIndex(panel);
        if (this.animating[panelIndex] == true) return;
        panel = this.headers[panelIndex];
        if (!this.activePanels.include(panelIndex)) return;
        this.activePanels = this.activePanels.without(panelIndex);
        if (this.options.saveToCookie) {
            Zikula.Cookie.set(this.cookie,this.activePanels);
        }
        if (skipAnimation) {
            if (this.options.minheight) {
                panel.next().setStyle({height: this.options.minheight.toUnits()});
            }
            [panel, panel.next().hide()].invoke('removeClassName',this.options.activeClassName);
            return;
        }
        this.animate(panelIndex, true);
    },
    /**
     * Internal animation method
     *
     * @private
     * @param {Number} show Index of panel to show
     * @param {Boolean} True to open, false to close
     * @return void
     */
    animate: function(element,hide) {
        this.effects = [];
        var options = {
            duration: this.options.effectDuration,
            scaleContent: false,
            beforeStart: function() {
                this.animating[element] = true;
                if (!hide) {
                    [this.headers[element],this.contents[element]].invoke('addClassName', this.options.activeClassName);
                    if (this.options.height) {
                        this.contents[element].setStyle({height: this.options.height.toUnits(), overflow: 'auto'});
                    }
                }
            }.bind(this),
            afterFinish: function() {
                this.animating[element] = false;
                if (hide) {
                    [this.headers[element],this.contents[element]].invoke('removeClassName', this.options.activeClassName);
                    if (this.options.minheight) {
                        this.contents[element].setStyle({height: this.options.minheight.toUnits()}).show();
                    }
                }
            }.bind(this)
        };
        if (this.options.minheight) {
            var panelcontent = this.contents[element];
            if (hide) {
                options['scaleFrom'] = 100;
                options['scaleTo']   = Math.round(this.options.minheight*100/panelcontent.retrieve('fullheight'));
            } else {
                options['scaleFrom'] = Math.round(panelcontent.getContentHeight()*100/panelcontent.retrieve('fullheight'));
                options['scaleTo']   = 100;
            }
            panelcontent.setStyle({height: panelcontent.retrieve('fullheight').toUnits()});
            // avoid lapsus between height changes
            if (!hide) {
                panelcontent.hide();
            }
        }
        //var fullheight =
        if (hide) {
            new Effect.BlindUp(this.contents[element], options);
        } else {
            new Effect.BlindDown(this.contents[element], options);
        }
    },
    /**
     * Expand all panels.
     *
     * @return void
     */
    expandAll: function(){
        this.headers.each(function(panel) {
            this.expand(panel);
        }.bind(this))
    },
    /**
     * Collapse all panels.
     *
     * @return void
     */
    collapseAll: function(){
        this.headers.each(function(panel) {
            this.collapse(panel);
        }.bind(this))
    },
    /**
     * Align panels height.
     *
     * Enabled when configured to be equal or a height is specified.
     *
     * @return void
     */
    alignPanels: function() {
        if (!this.options.height) {
            this.options.height = this.contents.invoke('getHeight').max();
        }
        $A(this.contents).invoke('setStyle',{
            height: this.options.height.toUnits(),
            overflow: 'auto'
        });
    }
});
Zikula.UI.ContextMenu = Class.create(Control.ContextMenu,/** @lends Zikula.UI.ContextMenu.prototype */{
    /**
     * Custom extension for Livepipe ContextMenu
     * Inherit all of the methods, options and events from
     * <a href="http://livepipe.net/control/contextmenu">Livepipe ContextMenu</a>.
     * Overwrites parents constructor to fix issue with Opera. Usage stay as it was with Control.ContextMenu.
     *
     * @class Zikula.UI.ContextMenu
     * @constructs
     *
     * @param {ContextMenu} $super Reference to super class, this is private param, do not use it.
     * @param {HTMLElement} container Element that the ContextMenu is attached to
     * @param {Object} [options] Config object
     *
     * @return {Zikula.UI.ContextMenu} New Zikula.UI.ContextMenu instance
     */
    initialize: function($super, container, options){
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
        var eventName = this.options.leftClick ? 'click' : (Prototype.Browser.Opera ? 'click' : 'contextmenu');
        this.container.observe(eventName, function(event){
            if(!Control.ContextMenu.enabled || (!this.options.leftClick && Prototype.Browser.Opera && !event.ctrlKey)) {
                return;
            }
            this.open(event);
        }.bindAsEventListener(this));
    }
});