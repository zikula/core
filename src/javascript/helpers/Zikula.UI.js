// Copyright Zikula Foundation 2009 - license GNU/LGPLv2.1 (or at your option, any later version).

Zikula.define('UI');

Zikula.UI.Key = Class.create(HotKey, {
    initialize: function($super,letter,callback,options) {
        options = Object.extend({
            ctrlKey: false
        }, options || { });
        $super(letter,callback,options);
    }
});


/**
 * Zikula.UI.Tooltips
 * Shorthand for group of tooltips, calls Zikula.UI.Tooltip for each element
 *
 * @param  elements   array of elelents to bind tooltips
 * @param  options    object with options for tooltip (see Control.ToolTip options)
 * @return void
 */
Zikula.UI.Tooltips = function(elements,options)
{
    $A(elements).each(function(e){
        new Zikula.UI.Tooltip($(e),null,options)
    })
}

/**
 * Zikula.UI.Tooltip
 * Custom extension for Control.ToolTip
 *
 * @param  container  element to bind tooltip
 * @param  tooltip    element or string for tooltip content, if none title attribute is used
 * @param  options    object with options for tooltip (see Control.ToolTip options)
 * @return void
 */
Zikula.UI.Tooltip = Class.create(Control.ToolTip, {
    initialize: function($super, container, tooltip, options) {
        options = Object.extend({
            className: 'z-tooltip',
            iframeshim: Zikula.Browser.IE
        }, options || { });
        if(!tooltip) {
            if (container.hasAttribute('title')) {
                tooltip = container.readAttribute('title');
                container.store('title',tooltip);
                container.writeAttribute('title','');
                if(tooltip.startsWith('#')) {
                    tooltip = $(tooltip.replace("#", ""));
                }
            }
        }
        $super(container, tooltip, options);
    },
    destroy: function($super) {
        if(this.sourceContainer) {
            this.sourceContainer.writeAttribute('title',this.sourceContainer.retrieve('title'));
        }
        $super();
    }
});
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
/**
 * Zikula.UI.Window
 * Custom extension for Control.Window
 *
 * @param  todo document this :)
 * @param  todo cleanup on window.destroy()
 * @param  todo methods to change window content after window initialization
 * @return void
 */
Zikula.UI.Window = Class.create(Control.Window, {
    initialize: function($super, container, options) {
        this.setWindowType(container);
        this.window = Zikula.UI.WindowTemplate(options);
        this.initContainer(container,options);
        options = Object.extend({
            className: 'z-window',
            minmax: true,
            width: 400,
            initMaxHeight: 400,
            offset: [50,50],//left, top
            indicator: this.window.indicator,
            overlayOpacity: 0.5,
            method: 'get',
            modal: false,
            destroyOnClose: false,
            iframeshim: Zikula.Browser.IE,
            closeOnClick: this.window.close,
            draggable: this.window.header,
            insertRemoteContentAt: this.window.body
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
    open: function($super, event) {
        return $super(event);
    },
    bringToFront: function($super) {
        $super();
        $$('.z-window.active').invoke('removeClassName','active');
        this.container.addClassName('active');
        this.focusWindow();
    },
    setWindowMaxSize: function() {
        var dim = document.viewport.getDimensions()
        this.container.setStyle({
            maxWidth: (dim.width - this.container.getOutlineSize('h') - this.options.offset[0]).toUnits(),
            maxHeight: (dim.height - this.container.getOutlineSize() - this.options.offset[1]).toUnits()
        })
    },
    getTopOffset: function() {
        return this.window.header.getHeight() + this.window.header.getOutlineSize();
    },
    getBottomOffset: function() {
        return this.window.footer.getHeight() + this.window.footer.getOutlineSize();
    },
    getWindowHeight: function() {
        var height = this.container.getHeight(),
            header = this.getTopOffset(),
            body = this.window.body.getHeight(),
            footer = this.getBottomOffset();
        if(this.windowType == 'ajax' && this.options.initMaxHeight) {
            height = this.options.initMaxHeight;
        } else if(height < header + body + footer) {
            height = height + header + footer;
        }
        return height;
    },
    finishOpen: function($super, event){
        if(this.options.initMaxHeight) {
            this.container.setStyle({maxHeight: (this.options.initMaxHeight-this.getTopOffset()-this.getBottomOffset()).toUnits()});
        }
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
        }
        if(this.window.body.down('iframe')) {
            this.window.body.addClassName('iframe');
        }
        this.window.body.setStyle(bodyStyle);
        this.initialWidth = this.container.getWidth();
        this.ensureInBounds();
        this.focusWindow();
        return true;
    },
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
    close: function($super, event) {
        this.restore(event);
        this.pos = {};
        if(this.initialWidth) {
            this.container.setStyle({
                width:this.initialWidth.toUnits()
            });
        }
        $super(event);
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
    toggleMax: function(event) {
        if(this.container.hasClassName('z-maximized')) {
            this.restore(event);
            this.restorePosition(event);
        } else {
            this.maximize(event);
        }
    },
    toggleMin: function(event) {
        if(this.container.hasClassName('z-minimized')) {
            this.restore(event);
            this.restorePosition(event);
        } else {
            this.minimize(event);
        }
    },
    maximize: function(event) {
        this.savePosition();
        this.restore(event);
        this.container.addClassName('z-maximized');
        $(document.body).setStyle({overflow: 'hidden'});
        if(this.draggable) {
            Draggable._dragging[this.container] = true;
        }
    },
    minimize: function(event) {
        this.savePosition();
        this.restore(event);
        this.container.addClassName('z-minimized');
        if(this.draggable) {
            this.draggable.options.constraint = 'horizontal';
        }
    },
    restore: function(event) {
        this.container.removeClassName('z-minimized');
        this.container.removeClassName('z-maximized');
        $(document.body).setStyle({overflow: 'visible'});
        if(this.draggable) {
            this.draggable.options.constraint = false;
            Draggable._dragging[this.container] = false;
        }
    },
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
    restorePosition: function() {
        if(this.pos) {
            var viewport_offset = document.viewport.getScrollOffsets();
            this.pos.top = (viewport_offset[1] + this.pos.top).toUnits();
            this.pos.left = (viewport_offset[0] + this.pos.left).toUnits();
            this.container.setStyle(this.pos);
        }
    },
    setWindowType: function(container) {
        this.windowType = 'string';
        if(Object.isElement(container)) {
            if(container.hasAttribute('href')) {
                var href = container.readAttribute('href');
                if(href.startsWith('#')) {
                    this.windowType = 'element';
                } else {
                    this.windowType = 'ajax';
                }
            }
        }
//        else if(Object.isString(container) && container.isJSON()) {
//            this.windowType = 'json';
//        }
    },
    focusWindow: function() {
        this.container.focus()
    },
    initContainer: function(container) {
        if(this.windowType == 'element') {
            this.insertContainer();
            var href = container.readAttribute('href');
            var rel = href.match(/^#(.+)$/);
            if(rel && rel[1]){
                this.window.body.insert($(rel[1]).show());
                this.window.container.id = 'Zikula_UI_Window_'+rel[1]
                container.writeAttribute('href','#'+this.window.container.id);
            }
        }
    },
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
/**
 * button = {label, action, close [more...]}
 **/
Zikula.UI.Dialog = Class.create(Zikula.UI.Window, {
    initialize: function($super, container, buttons, options) {
        options = Object.extend({
            className: 'z-window z-dialog',
            callback: Prototype.emptyFunction
        }, options || { });
        $super(container, options);
        this.window.footer.addClassName('z-buttons');
        this.buttons = {};
        this.insertButtons(buttons);
    },
    open: function($super, event) {
        this.isNotified = false;
        $super(event);
    },
    focusWindow: function() {
        this.buttons[Object.keys(this.buttons)[0]].focus();
    },
    notifyCallback: function(button) {
        if(!this.isNotified) {
            this.options.callback(button);
            this.isNotified = typeof button.close !== 'undefined' ? button.close : true;
        }
    },
    insertButtons: function(buttons) {
        $A(buttons).each(function(button){
            this.button(button);
        }.bind(this));
    },
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
Zikula.UI.Alert = function(text, title, options){
    options = Object.extend({
        destroyOnClose: true,
        title: title
    },options);
    var dialog = new Zikula.UI.AlertDialog(text, options);
    dialog.open();
    return dialog;
};
Zikula.UI.AlertDialog = Class.create(Zikula.UI.Dialog, {
    initialize: function($super, container, options) {
        options = Object.extend({
            className: 'z-window z-dialog z-alert',
            minmax: false
        }, options || { });
        $super(container, this.defaultButtons(this.notifyCallback.bind(this)), options);
    },
    defaultButtons: function(callback) {
        return [
            {label: 'Ok'}
        ]
    }
});
Zikula.UI.IfConfirmed = function(text, title, callback, options){
    return Zikula.UI.Confirm.curry(text, title, callback, options);
};
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
Zikula.UI.ConfirmDialog = Class.create(Zikula.UI.Dialog, {
    initialize: function($super, container, options) {
        options = Object.extend({
            className: 'z-window z-dialog z-confirm'
        }, options || { });
        options.afterClose = this.notifyCallback.curry(false);
        $super(container, this.defaultButtons(this.notifyCallback.bind(this)), options);
    },
    defaultButtons: function(callback) {
        return [
            {label: 'Ok',action: callback.curry(true)},
            {label: 'Cancel',action: callback.curry(false)}
        ]
    }
});
Zikula.UI.FormDialog = Class.create(Zikula.UI.Dialog, {
    initialize: function($super, container, callback, options) {
        options = Object.extend({
            className: 'z-window z-dialog z-form',
            width: 500,
            ajaxRequest: false,
            resizable: true,
            callback: callback
        }, options || { });
        options.afterClose = this.notifyCallback.curry(false);
        $super(container, this.defaultButtons(this.notifyCallback.bind(this)), options);
    },
    focusWindow: function() {
        this.container.down('form').focusFirstElement();
    },
    notifyCallback: function(button) {
        if(!this.isNotified) {
            var form =  this.container.down('form'),
                buttonData = {},
                result;
            if(button && button.name) {
                buttonData[button.name] = button.value;
            }
            if(form.action && button) {
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
    defaultButtons: function(callback) {
        return [
            {label: 'Submit', type: 'submit', name: 'submit', value: 'submit'},
            {label: 'Cancel', action: callback.curry(false)}
        ]
    }
});
Zikula.UI.SelectMultiple = Class.create(Control.SelectMultiple, {
    initialize: function($super, select, options) {
        if(options && options.afterChange) {
            this.origAfterChange = options.afterChange;
        }
        options = Object.extend({
            nameSelector: 'label',
            valueSeparator: ',',
            opener: null,
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
    buildContainer: function(select,options) {
        var opener = options.opener || null,
            selectId = select.identify(),
            openerId = selectId+'_opener',
            containerId = selectId+'_options';
        if(!opener) {
            opener = new Element('a',{id:openerId,href:'#'+containerId,title:'Select multiple'}).update('Select multiple');
            select.insert({after:opener});
        } else {
            opener = $(opener);
            opener.writeAttribute('href', '#'+containerId);
        }
        var container = new Element('div',{id:containerId,'class':'z-select-multiple z-form'});
        $(opener).insert({after:container});
        select.select('option').each(function(option) {
            container.insert(
                new Element('div',{'class':'z-formrow'})
                    .insert(new Element('label',{'for':option.identify()+'m'})
                        .update(option.text)
                    )
                    .insert(new Element('input',{id:option.identify()+'m',name:select.name+'[]',type:'checkbox',value:option.value,checked:option.selected}))
            )
        });
        this.dialog = new Zikula.UI.Dialog(opener,[{label: 'Ok'}],{position:'relative'});
        return container;
    },
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

Zikula.UI.Tabs = Class.create(Control.Tabs, {
    initialize: function($super, tab_list_container, options) {
        options = Object.extend({
            equal: false,
            containerClass: 'z-tabs',
            setClassOnContainer: true,
            activeClassName: 'active'
        }, options || { });
        $(tab_list_container).addClassName(options.containerClass);
        $super(tab_list_container,options);
        if(this.options.equal) {
            this.alignTabs();
        }
    },
    setActiveTab: function($super,link) {
        $super(link);
        if(this.options.equal) {
            this.alignTabs();
        }
    },
    alignTabs: function() {
        this.maxHeight = this.containers.values().invoke('getHeight').max();
        this.containers.values().invoke('setStyle',{minHeight: this.maxHeight.toUnits()});
    }
});

/* Builder replecement
 *
 **/
//Builder = {node: function(e,a,t) {return new Element(e,a||{}).update(t||'')}};

