// Copyright Zikula Foundation, licensed MIT.

/*
params:
    objs            string: css selector for elements to be tracked
    evt             string or array: event or events to track
    menuItems       object: menu items
    config          object: menu configuration

    parameters menuItems:
        name
        displayname
        title
        img
        confirm
        action
        disable
    or just true for separators
*/
var ContextMenu = Class.create({
    initialize: function() {
        this.config = Object.extend({
            objs:               '', // elements on which the menu will be performed
            trigger:            'click', // or click contextmenu (opera click + alt, ctrl, not because it does not work on img)
            menuId:             'ContextMenu',
            menuClassName:      'ContextMenu',
            subMenuClassName:   'more',
            showMenuClassName:  'show',
            disabledClassName:  'disabled',
            activeClassName:    'isfocused',
            menuOffset:         10,
            menuTopAlign:       -3,
            menuLeftAlign:      ['100%','-100%'],
            imagesDir:          '',
            actionBaseHref:      '',
            actionName:          'action',
            actionArgs:          '',
            dynamic:              false, // whether the menu is to be generated each time from scratch
            items:          {} // menu items - object or function
        }, arguments[0] || { });

        this.showMenuBind = this.showMenu.bindAsEventListener(this);
        this.hideMenuBind = this.hideMenu.bindAsEventListener(this);
        this.toggleNodeBind = this.toggleNode.bindAsEventListener(this);
        this.clickNodeBind = this.clickNode.bindAsEventListener(this);
        this.actionBind = this.action.bind(this);

        this.observe();
    },
    observe: function() {
        if (this.config.trigger == 'contextmenu') {
            $$(this.config.objs).invoke('observe', Prototype.Browser.Opera ? 'click' : 'contextmenu', this.showMenuBind);
        } else {
            $$(this.config.objs).invoke('observe', this.config.trigger, this.showMenuBind);
        }
    },
    add: function(obj) {
        if (this.config.trigger == 'contextmenu') {
            $(obj).observe(Prototype.Browser.Opera ? 'click' : 'contextmenu', this.showMenuBind);
        } else {
            $(obj).observe(this.config.trigger, this.showMenuBind);
        }
    },
    destroy: function() {
        if (this.config.trigger == 'contextmenu') {
            $$(this.config.objs).invoke('stopObserving', Prototype.Browser.Opera ? 'click' : 'contextmenu', this.showMenuBind);
        } else {
            $$(this.config.objs).invoke('stopObserving', this.config.trigger, this.showMenuBind);
        }
    },
    buildMenu: function(evt) {
        // if required remove the previous menu
        if (this.menu) {
            this.menu.remove();
        }
        this.menu = new Element('div', { id: this.config.menuId, className: this.config.menuClassName }).hide();
        var menuItems = this.config.items.constructor === Function
            ? this.config.items(evt) 
            : this.config.items;
        var list = new Element('ul');
        for (var item in menuItems) {
            list.insert(this.buildItem(menuItems[item]));
        }
        this.menu.insert(list);
        $(document.body).insert(this.menu);
    },
    buildItem: function(item) {
        var li;
        if (item === true) {
            li = new Element('li', { className: 'separator' });
        }
        else if (item.action && item.action.constructor === Object) {
            li = new Element('li', { className: item.disabled ? this.config.disabledClassName : this.config.subMenuClassName })
                .insert(new Element('span', { title:item.title ? item.title : null })
                    .appendText(item.displayname ? item.displayname : item.name)
                    .setStyle(item.img ? { backgroundImage: 'url("' + this.config.imagesDir + item.img + '")' } : {}))
                .observe('click', this.clickNodeBind);
            var ul = new Element('ul');
            for (var subitem in item.action) {
                ul.insert(this.buildItem(item.action[subitem]));
            }
            li.insert(ul);
        } else {
            li = Object.extend(
                    new Element('li', { className: item.disabled ? this.config.disabledClassName : null }),
                    {_action: item.action ? item.action : this.actionBind,
                     _confirm: item.confirm ? item.confirm : false,
                     _name: item.name})
                        .insert(new Element('span', { title: item.title ? item.title : null })
                            .setStyle(item.img ? { backgroundImage: 'url("' + this.config.imagesDir + item.img + '")' } : {})
                            .appendText(item.displayname ? item.displayname : item.name)
                            .observe('click', this.clickNodeBind))
                        .observe('click', this.clickNodeBind);
        }
        return li;
    },
    clickNode: function(evt) {
        evt.stop();
        evt.li = evt.element().tagName == 'LI' ? evt.element() : evt.element().up('li');
        if (!evt.li.hasClassName(this.config.disabledClassName)) {
            if (evt.li.hasClassName(this.config.subMenuClassName)) {
                this.toggleNode(evt);
            } else {
                this.hideMenu();
                if (!evt.li._confirm || confirm(evt.li._confirm)) {
                    evt.li._action(this.event, evt);
                }
            }
        }
    },
    toggleNode: function(evt) {
        evt.stop();
        evt.ul = evt.li.down('ul');
        if (evt.ul.hasClassName(this.config.showMenuClassName)) {
            evt.li.select('ul').invoke('removeClassName', this.config.showMenuClassName);
        } else {
            var ancestors = evt.li.ancestors();
            this.menu.select('ul')
                .select(function(item) {
                    return !ancestors.include(item);
                })
                .invoke('removeClassName', this.config.showMenuClassName);
            var menuSize = evt.ul.getDimensions(),
                evtpos = {
                    x: evt.li.cumulativeOffset()[0] + evt.li.getWidth(),
                    y: evt.li.cumulativeOffset()[1]
                },
                viewportSize = document.viewport.getDimensions(),
                viewportOffset = document.viewport.getScrollOffsets(),
                pos = {
                    left: ((evtpos.x + menuSize.width + this.config.menuOffset) > viewportSize.width) ? this.config.menuLeftAlign[1] : this.config.menuLeftAlign[0],
                    top: (((evtpos.y - viewportOffset.top + menuSize.height) > viewportSize.height) 
                        ? (((evtpos.y - viewportOffset.top + menuSize.height) - viewportSize.height + this.config.menuOffset) < evtpos.y - this.config.menuOffset
                            ? -((evtpos.y - viewportOffset.top + menuSize.height) - viewportSize.height + this.config.menuOffset)
                            : -(evtpos.y - this.config.menuOffset))
                        : this.config.menuTopAlign ) + 'px'
                };
            evt.ul.setStyle(pos).addClassName(this.config.showMenuClassName);
        }
    },
    showMenu: function(evt) {
        if (this.config.trigger == 'contextmenu' && Prototype.Browser.Opera && !evt.altKey) {
            return;
        }
        evt.stop();
        if (!this.menu || this.config.dynamic) {
            //if there are no menus or menuitems are dynamic, and the menu is not invoked again for the same item - build menu
            this.buildMenu(evt);
        } else {
            //otherwise use the existing one
            this.menu.select('ul').invoke('removeClassName', this.config.showMenuClassName);
        }
        var menuSize = this.menu.getDimensions(),
            viewportSize = document.viewport.getDimensions(),
            viewportOffset = document.viewport.getScrollOffsets(),
            pos = {
                left: (((evt.pageX + menuSize.width + this.config.menuOffset) > viewportSize.width) ? (viewportSize.width - menuSize.width - this.config.menuOffset) : evt.pageX) + 'px',
                top: (((evt.pageY - viewportOffset.top + menuSize.height) > viewportSize.height && (evt.pageY - viewportOffset.top) > menuSize.height) ? (evt.pageY - menuSize.height) : evt.pageY) + 'px'
            };
        $(this.menu).setStyle(pos).show();
        this.event = evt;
        // mark the item for which you accessed the menu as an active
        $$('.' + this.config.activeClassName).invoke('removeClassName', this.config.activeClassName);
        this.event.element().addClassName(this.config.activeClassName);
        Event.observe(document, 'click', this.hideMenuBind);
    },
    hideMenu: function() {
        this.menu.select('ul').invoke('removeClassName', this.config.showMenuClassName);
        this.menu.hide();
        this.event.element().removeClassName(this.config.activeClassName);
        Event.stopObserving(document, 'click', this.hideMenuBind);
    },
    action: function() {
        var args = $(arguments[0].target).match(this.config.actionArgs) 
                ? $(arguments[0].target).id.split('-') : $(arguments[0].target).up(this.config.actionArgs).id.split('-'),
            params = new Hash();
        params.set(this.config.actionName, $(arguments[1].li)._name);
        params.set(args[0], args[1]);
        this.config.actionBaseHref = this.config.actionBaseHref.unescapeHTML();
        if (this.config.actionBaseHref.include('?')) {
            var query = this.config.actionBaseHref.toQueryParams();
            params.update(query)
            var newlocation = this.config.actionBaseHref.replace($H(query).toQueryString(),'') + params.toQueryString();
        } else {
            var newlocation = this.config.actionBaseHref + '?' + params.toQueryString();
        }
        window.location = newlocation;
    }
});


//new Element('p').appendText('test');
Element.addMethods({
    appendText: function(element, text) {
        element.appendChild(document.createTextNode(text));
        return $(element);
    }
});
