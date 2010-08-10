Zikula.define('Menutree');

Zikula.Menutree.Tree = Class.create(Zikula.TreeSortable,{
    initialize: function($super, element, config) {
        config = this.decodeConfig(config);
        config = Object.extend({
            unactiveClass:      'unactive',
            dynamicClass:       'dynamic',
            dynamicPattern:     function(str) {return str ? str.startsWith('{ext:') : false;},
            langs:              ['en'],
            linkClasses:        [],
            stripBaseURL:       false,
            onSave:             this.save,
            saveContentTo:      'menutree_content'
            /* tmp cfg */
            ,langs: ['en']

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

        if(this.config.langs.length > 1) {
            this.multilingual = true;
            this.cLang = this.config.langs[0];
            this.defaultLang = this.config.langs[0];
        }
        if(this.config.linkClasses.size() > 0) {
            this.multiclass = true;
        }

        this.stripBaseURL();
        this.attachMenu();
        this.buildForm();
        this.unsaved = false;
        Event.observe(window, 'beforeunload', this.beforeUnloadHandler.bindAsEventListener(this));
    },
    initNode: function($super,node) {
        node.select('a[lang!='+'en'+']').invoke('hide');
        $super(node);
    },
    stripBaseURL: function() {
        if(this.config.stripbaseurl) {
            var baseurl = new RegExp('^'+Zikula.Config.baseURL);
            this.tree.select('a').each(function(n) {
                n.href = n.readAttribute('href').replace(baseurl, '');
            }.bind(this));
        }
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
        if($('menutree_content')) {
            this.tree.up('form').insert(new Element('input',{
                type:'hidden',
                'id':this.config.saveContentTo,
                name:this.config.saveContentTo
            }));
        }
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
        // todo
        console.log(arguments);
    },
    buildForm: function() {
        if(!this.form) {
            this.form = new Zikula.UI.FormDialog($('menutree_form_container'),this.submitForm,{title: $('menutree_form_container').title});
            if(this.multilingual){
                $('linklang').observe('change',this.changeFormLang.bindAsEventListener(this));
            }
        }
    },
    showForm: function(obj) {
        this.buildForm();
        this.form.open();
    },
    submitForm: function() {
        console.log(arguments);
    },
    changeFormLang: function(event) {
        console.log(arguments);
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

//http://www.prototypejs.org/2007/5/12/dom-builder#comment-15901
//new Element('p').appendText('test');
Element.addMethods({
    appendText: function(element, text) {
        element.appendChild(document.createTextNode(text));
        return $(element);
    }
});