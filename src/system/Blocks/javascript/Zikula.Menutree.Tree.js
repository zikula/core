Zikula.define('Menutree');

Zikula.Menutree.Tree = Class.create(Zikula.TreeSortable,{
    initialize: function($super, element, config) {
        config = this.decodeConfig(config);
        config = Object.extend({
            unactiveClass:           'unactive',
            langs: ['en'],
            onSave: this.save
        }, config || { });
        config.langLabels = Object.extend({
            delConfirm:         'Do you really want to delete this node and all of it child nodes?',
            linkname:           'Link name',
            linkhref:           'Link URL',
            linktitle:          'Link title',
            linkclass:          'Link class',
            linkclassblank:     'Select class',
            linklang:           'Language',
            linkstate:          'Active?',
            activate:           'Activate',
            deactivate:         'Deactivate',
            edit:               'Edit',
            remove:             'Delete',
            add:                'Add new...',
            before:             'before',
            after:              'after',
            bottom:             'as child',
            expand:             'Expand this node',
            collapse:           'Collapse this node',
            multitoggle:        'Status',
            multiactivate:      'Activate for all langs',
            multideactivate:    'Deactivate for all langs',
            usedefault:         'Use one for all langs',
            cancel:             'Cancel',
            submit:             'Save',
            required:           'Please fill required fields',
            forminfo:           'Marked fields are required',
            maxdepthreached:    'Maximum depth reached. Limit is: ',
            warnbeforeunload:   'You have unsaved changes!'
        },config.langLabels);
        config.images = Object.extend({
            edit:               'menu/folder_edit.png',
            remove:             'menu/folder_delete.png',
            add:                'menu/folder_add.png',
            before:             'menu/before.png',
            after:              'menu/after.png',
            bottom:             'menu/aschild.png',
            collapse:           'menu/collapse.png',
            expand:             'menu/expand.png',
            activate:           'menu/folder_on.png',
            deactivate:         'menu/folder_off.png',
            multitoggle:        'menu/all-onoff.png',
            multiactivate:      'menu/all-on.png',
            multideactivate:    'menu/all-off.png'
        },config.images);
        $super(element,config);
        this.tree.up('form').insert(new Element('input',{type:'hidden','id':'menutree_content',name:'menutree_content'}));
        this.attachMenu();
        this.unsaved = false;
        this.form = new Zikula.UI.FormDialog($('menuTreeNodeBuilder'),this.submitForm);
        Event.observe(window, 'beforeunload', this.beforeUnloadHandler.bindAsEventListener(this));
    },
    initNode: function($super,node) {
        node.select('a[lang!='+'en'+']').invoke('hide');
        $super(node);
    },
    serializeNode: function($super,node,index) {
        var link, nodeData = {};
        this.config.langs.each(function(lang) {
            link =  node.down('a[lang='+lang+']');
            nodeData[lang] = {
                id:         this.getNodeId(node),
                name:       link.innerHTML,
                title:      link.readAttribute('title'),
                className:  $w(link.className).without(this.config.unactiveClass).join(' '),
                state:      !link.hasClassName(this.config.unactiveClass),
                href:       link.readAttribute('href'),
                lang:       link.readAttribute('lang'),
                lineno:     index || null,
                parent:     node.up('#'+this.tree.id+' li') ? this.getNodeId(node.up('#'+this.tree.id+' li')) : 0
            };
        }.bind(this));
        return nodeData;
    },
    save: function(node,params,data) {
        $('menutree_content').setValue(Zikula.urlsafeJsonEncode(data, false));
        return true;
    },
    attachMenu: function() {
        this.config.menuConfig = Object.extend({
            objs:       '',
            trigger:    'click',
            dynamic:    true
        },this.config.menuConfig);

        this.config.menuConfig.objs = '#'+this.tree.id+' li a';

        this.menuItemsBind = this.menuItems.bind(this);
        this.config.menuConfig.items = this.menuItemsBind;

        this.menu = new ContextMenu(this.config.menuConfig);
    },
    menuItems: function(elementEvt) {
        var actionBind = this.menuAction.bind(this),
            element = elementEvt.element(),
            expandItem = {};
        if(element.up('li').down('ul') && element.up('li').down('ul').visible()) {
            expandItem = {name: 'collapse', displayname: this.config.langLabels.collapse, img: this.config.images.collapse, action: actionBind};
        } else if (element.up('li').down('ul') && !element.up('li').down('ul').visible()) {
            expandItem = {name: 'expand', displayname: this.config.langLabels.expand, img: this.config.images.expand, action: actionBind};
        } else {
            expandItem = {name: 'expand', displayname: this.config.langLabels.expand, disabled: true, img: this.config.images.expand, action: actionBind};
        }
        if(this.config.maxDepth > 0) {
            var addAsChildDisabled = (this.countLevels(element.up('li'),'up') + 2) > this.config.maxDepth;
        }
        var menuItems = {
            edit: {name: 'edit', displayname: this.config.langLabels.edit, img: this.config.images.edit,action: actionBind},
            remove: {name: 'remove', displayname: this.config.langLabels.remove, img: this.config.images.remove, confirm: this.config.langLabels.delConfirm, action: actionBind},
            add: {name: 'add', displayname: this.config.langLabels.add, img: this.config.images.add,
                action: {
                    before: {name: 'before', displayname: this.config.langLabels.before,img: this.config.images.before,action: actionBind},
                    after: {name: 'after', displayname: this.config.langLabels.after,img: this.config.images.after,action: actionBind},
                    bottom: {name: 'bottom', displayname: this.config.langLabels.bottom,img: this.config.images.bottom, action: actionBind, disabled: addAsChildDisabled}
                }
            },
            s1: true,
            expand: expandItem,
            toggle: {name: 'toggle', displayname: element.hasClassName(this.config.unactiveClass) ? this.config.langLabels.activate : this.config.langLabels.deactivate,img: element.hasClassName(this.config.unactiveClass) ? this.config.images.activate : this.config.images.deactivate , action: actionBind}
        };
        if(this.multilingual) {
            Object.extend(menuItems,{
                onoffs: {name: 'onoffs', displayname: this.config.langLabels.multitoggle, img: this.config.images.multitoggle,
                    action: {
                        on: {name: 'on', displayname: this.config.langLabels.multiactivate,img: this.config.images.multiactivate, action: actionBind},
                        off: {name: 'off', displayname: this.config.langLabels.multideactivate,img: this.config.images.multideactivate, action: actionBind}
                    }
                }
            });
        }
        return menuItems;
    },
    menuAction: function(elementEvt,actionEvt) {
        console.log(arguments);
        var action = actionEvt.element().tagName == 'LI' ? actionEvt.element()._name : actionEvt.element().up('li')._name,
            obj = elementEvt.element();
        switch (action) {
            case 'expand':
                this.expandAll(obj.up('li'));
                break;
            case 'collapse':
                this.collapseAll(obj.up('li'));
                break;
            case 'remove':
                this.deleteNode(obj);
                break;
            case 'toggle':
                this.switchNode(obj);
                break;
            case 'on':
                this.switchNode(obj, true, true);
                break;
            case 'off':
                this.switchNode(obj, true, false);
                break;
            case 'edit':
                this.readNode(obj);
                this.formaction = action;
                this.showForm(obj);
                break;
            case 'before':
            case 'after':
            case 'bottom':
                this.readNode();
                this.formaction = action;
                this.referer = obj.up('li');
                this.showForm(obj);
                break;
        }
    },
    deleteNode: function(obj) {
        var li = obj.up('li');
        Droppables.remove(li);
        li.select('li').each(function(node) {
            Droppables.remove(node);
        }.bind(this));
        li.remove();
        this.drawNodes();
        this.unsaved = true;
    },
    switchNode: function(obj,full,on) {
        if(full) {
            if(on) {
                obj.up('li').select('a').invoke('removeClassName',this.config.unactiveClass);
            } else {
                obj.up('li').select('a').invoke('addClassName',this.config.unactiveClass);
            }
        } else {
            obj.toggleClassName(this.config.unactiveClass);
        }
    },
    readNode: function(obj) {
        console.log(arguments);
    },
    showForm: function(obj) {
        this.form.open();
    },
    submitForm: function() {
        console.log(arguments)
    },
    beforeUnloadHandler: function (event) {
        if(this.unsaved && this.config.langLabels.warnbeforeunload) {
            return event.returnValue = this.config.langLabels.warnbeforeunload;
        }
    }
});
Object.extend(Zikula.Menutree.Tree,{
    trees: {},
    add: function(element,config) {
        if (!this.trees.hasOwnProperty(element)) {
            this.trees[element] = new Zikula.Menutree.Tree(element,config);
            Zikula.t = this.trees[element];
        }
    }
});