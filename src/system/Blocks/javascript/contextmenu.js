// Copyright Zikula Foundation 2009 - license GNU/LGPLv3 (or at your option, any later version).

/*
params:
    objs            string: selektor css elementow, ktore maja byc sledzone
    evt             string lub tablica: zdarzenie lub zdarzenia do sledzenia
    menuItems       obiekt: elementy menu
    config          obiekt: konfig dla menu

    parametry menuItems:
        name
        displayname
        title
        img
        confirm
        action
        disable
    lub tylko true dla separatorow

*/
var ContextMenu = Class.create({
    initialize: function() {
        this.config = Object.extend({
            objs:               '', //elementy, na ktorych bedzie menu wykonywane
            trigger:            'click',//click lub contextmenu (opera click + alt, ctrl nie, bo nie dziala na img)
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
            dynamic:              false, //czy menu ma byc za kazdym razem generowane od zera
            items:          {} // menu items - obiekt lub funkcja
        }, arguments[0] || { });

        //http://www.thefutureoftheweb.com/blog/detect-ie6-in-javascript 
        this.ie6 = false /*@cc_on || @_jscript_version < 5.7 @*/;

        this.showMenuBind = this.showMenu.bindAsEventListener(this);
        this.hideMenuBind = this.hideMenu.bindAsEventListener(this);
        this.toggleNodeBind = this.toggleNode.bindAsEventListener(this);
        this.clickNodeBind = this.clickNode.bindAsEventListener(this);
        this.actionBind = this.action.bind(this);

        this.observe();
    },
    observe: function() {
        if(this.config.trigger == 'contextmenu') {
            $$(this.config.objs).invoke('observe',Prototype.Browser.Opera ? 'click' : 'contextmenu',this.showMenuBind);
        } else {
            $$(this.config.objs).invoke('observe',this.config.trigger,this.showMenuBind);
        }
    },
    add: function(obj) {
        if(this.config.trigger == 'contextmenu') {
            $(obj).observe(Prototype.Browser.Opera ? 'click' : 'contextmenu',this.showMenuBind);
        } else {
            $(obj).observe(this.config.trigger,this.showMenuBind);
        }
    },
    destroy: function() {
        if(this.config.trigger == 'contextmenu') {
            $$(this.config.objs).invoke('stopObserving',Prototype.Browser.Opera ? 'click' : 'contextmenu',this.showMenuBind);
        } else {
            $$(this.config.objs).invoke('stopObserving',this.config.trigger,this.showMenuBind);
        }
    },
    buildMenu: function(evt) {
        //jesli dynamicznie- trzeba usuwac poprzednie menu
        if(this.menu) {
            this.menu.remove();
        }
        this.menu = new Element('div',{id:this.config.menuId, className:this.config.menuClassName}).hide();
        //http://yura.thinkweb2.com/scripting/contextMenu/
        if(this.ie6) {
            this.iframe = new Element('iframe', {
                style: 'position:absolute;filter:progid:DXImageTransform.Microsoft.Alpha(opacity=0);',
                src: 'javascript:false;',
                frameborder: 0
            });
            this.menu.insert(this.iframe);
        }
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
            li = new Element('li', {className:'separator'});
        }
        else if (item.action && item.action.constructor === Object) {
            li = new Element('li',{className: item.disabled ? this.config.disabledClassName : this.config.subMenuClassName})
                .insert(new Element('span',{title:item.title ? item.title : null})
                    .appendText(item.displayname ? item.displayname : item.name)
                    .setStyle(item.img ? {backgroundImage: 'url("'+this.config.imagesDir+item.img+'")'} : {}))
                .observe('click',this.clickNodeBind);
            var ul = new Element('ul');
            if(this.ie6) {
                ul.insert($(this.iframe.cloneNode()));
            }
            for (var subitem in item.action) {
                ul.insert(this.buildItem(item.action[subitem]));
            }
            li.insert(ul);
        } else {
            li = Object.extend(
                    new Element('li',{className: item.disabled ? this.config.disabledClassName : null}),
                    {_action: item.action ? item.action : this.actionBind,
                     _confirm: item.confirm ? item.confirm : false,
                     _name: item.name})
                        .insert(new Element('span',{title:item.title ? item.title : null})
                            .setStyle(item.img ? {backgroundImage: 'url("'+this.config.imagesDir+item.img+'")'} : {})
                            .appendText(item.displayname ? item.displayname : item.name)
                            .observe('click',this.clickNodeBind))
                        .observe('click',this.clickNodeBind);
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
                if(!evt.li._confirm || confirm(evt.li._confirm)) {
                    evt.li._action(this.event, evt);
                }
            }
        }
    },
    toggleNode: function(evt) {
        evt.stop();
        evt.ul = evt.li.down('ul');
        if(evt.ul.hasClassName(this.config.showMenuClassName)) {
            evt.li.select('ul').invoke('removeClassName',this.config.showMenuClassName);
        } else {
            var ancestors = evt.li.ancestors();
            this.menu.select('ul')
                .select(function(item) {
                    return !ancestors.include(item);
                })
                .invoke('removeClassName',this.config.showMenuClassName);
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
            if(this.ie6) {
                evt.ul.down('iframe').setStyle(menuSize);
            }
            evt.ul.setStyle(pos).addClassName(this.config.showMenuClassName);
        }
    },
    showMenu: function(evt) {
        if (this.config.trigger == 'contextmenu' && Prototype.Browser.Opera && !evt.altKey) {
            return;
        }
        evt.stop();
        //jesli nie ma menu lub menuItems sa dynamiczne oraz menu nie jest wywolywane ponownie dla tego samego elementu - zbuduj menu
        if(!this.menu || this.config.dynamic) {
            this.buildMenu(evt);
        //w przeciwnym wypadku uzyj istniejacego
        } else {
            this.menu.select('ul').invoke('removeClassName',this.config.showMenuClassName);
        }
        var menuSize = this.menu.getDimensions(),
            viewportSize = document.viewport.getDimensions(),
            viewportOffset = document.viewport.getScrollOffsets(),
            pos = {
                left: (((evt.pageX + menuSize.width + this.config.menuOffset) > viewportSize.width) ? (viewportSize.width - menuSize.width - this.config.menuOffset) : evt.pageX) + 'px',
                top: (((evt.pageY - viewportOffset.top + menuSize.height) > viewportSize.height && (evt.pageY - viewportOffset.top) > menuSize.height) ? (evt.pageY - menuSize.height) : evt.pageY) + 'px'
            };
        if(this.ie6) {
            this.menu.down('iframe').setStyle(menuSize);
        }
        $(this.menu).setStyle(pos).show();
        this.event = evt;
        //oznacz element, dla ktorego wywolano menu jako aktywny
        $$('.'+this.config.activeClassName).invoke('removeClassName',this.config.activeClassName);
        this.event.element().addClassName(this.config.activeClassName);
        Event.observe(document, 'click', this.hideMenuBind);
    },
    hideMenu: function() {
        this.menu.select('ul').invoke('removeClassName',this.config.showMenuClassName);
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
        if(this.config.actionBaseHref.include('?')) {
            var query = this.config.actionBaseHref.toQueryParams();
            params.update(query)
            var newlocation = this.config.actionBaseHref.replace($H(query).toQueryString(),'') + params.toQueryString();
        } else {
            var newlocation = this.config.actionBaseHref + '?' + params.toQueryString();
        }
        window.location = newlocation;
    }
});


//http://www.prototypejs.org/2007/5/12/dom-builder#comment-15901
//new Element('p').appendText('test');
Element.addMethods({
    appendText: function(element, text) {
        element.appendChild(document.createTextNode(text));
        return $(element);
    }
});