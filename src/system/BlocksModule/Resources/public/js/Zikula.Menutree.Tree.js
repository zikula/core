// Copyright Zikula Foundation, licensed MIT.

Zikula.define('Menutree');

Zikula.Menutree.Tree = Class.create(Zikula.TreeSortable,{
    initialize: function($super, element, config) {
        config = this.decodeConfig(config);
        config = Object.extend({
            unactiveClass:      'z-tree-unactive',
            dynamicClass:       'z-tree-dynamic',
            nodeIdPrefix:       'node_',
            dynamicPattern:     function(str) {
                return str ? str.startsWith('{ext:') : false;
            },
            langs:              ['en'],
            stripBaseURL:       false,
            onSave:             this.save.bind(this),
            saveContentTo:      'menutree_content'
            /* tmp cfg */
//            ,langs: ['en']

        }, config || { });
        config.langLabels = Object.extend({
            delConfirm:         Zikula.__('Do you really want to delete this node and all of it child nodes?'),
            linkname:           Zikula.__('Link name'),
            linkhref:           Zikula.__('Link URL'),
            linktitle:          Zikula.__('Link title'),
            linkclass:          Zikula.__('Link class'),
            linkclassblank:     Zikula.__('Select class'),
            linklang:           Zikula.__('Language'),
            linkstate:          Zikula.__('Active?'),
            activate:           Zikula.__('Activate'),
            deactivate:         Zikula.__('Deactivate'),
            edit:               Zikula.__('Edit'),
            remove:             Zikula.__('Delete'),
            add:                Zikula.__('Add new...'),
            before:             Zikula.__('before'),
            after:              Zikula.__('after'),
            bottom:             Zikula.__('as child'),
            expand:             Zikula.__('Expand this node'),
            collapse:           Zikula.__('Collapse this node'),
            multitoggle:        Zikula.__('Status'),
            multiactivate:      Zikula.__('Activate for all langs'),
            multideactivate:    Zikula.__('Deactivate for all langs'),
            usedefault:         Zikula.__('Use one for all langs'),
            cancel:             Zikula.__('Cancel'),
            submit:             Zikula.__('Save'),
            required:           Zikula.__('Please fill required fields'),
            forminfo:           Zikula.__('Marked fields are required'),
            maxdepthreached:    Zikula.__('Maximum depth reached. Limit is: '),
            warnbeforeunload:   Zikula.__('You have unsaved changes!')
        }, config.langLabels);
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

        this.multilingual = config.langs.length > 1;
        this.cLang = config.langs[0];
        this.defaultLang = config.langs[0];

        $super(element, config);

        this.stripBaseURL();
        this.attachMenu();
        this.buildForm();
        this.observeForm();
        this.unsaved = false;
        Event.observe(window, 'beforeunload', this.beforeUnloadHandler.bindAsEventListener(this));
    },
    initNode: function($super, node) {
        node.select('a[lang!=' + this.cLang + ']').invoke('hide');
        if (!node.down('.' + this.config.icon)) {
            node.insert({ top: new Element('img', { className: this.config.icon }) });
        }
        if ((!node.down('.' + this.config.toggler))) {
            node.insert({ top: new Element('img', { className: this.config.toggler,src: this.config.images.plus }) });
        }
        $super(node);
    },
    insertNode: function($super, node, params, revert) {
        var result = $super(node, params, revert);
        if (result) {
            this.unsaved = true;
        }
        return result;
    },
    stripBaseURL: function() {
        if (this.config.stripbaseurl) {
            var baseurl = new RegExp('^' + Zikula.Config.baseURL);
            this.tree.select('a').each(function(n) {
                n.href = n.readAttribute('href').replace(baseurl, '');
            }.bind(this));
        }
    },
    serializeNode: function($super, node, index) {
        return this.getNodeData(node, index, true);
    },
    observeForm: function() {
        this.tree.up('form').observe('submit', this.sendSaved.bindAsEventListener(this));
    },
    sendSaved: function() {
        this.save();
        this.unsaved = false;
    },
    save: function(node, params, data) {
        if (node && params && Object.isElement(params[1]) && params[1].hasClassName(this.config.dynamicClass) && params[0] == 'bottom') {
            return false;
        }
        data = data || this.serialize();
        if (!$('menutree_content')) {
            this.tree.up('form').insert(new Element('input', {
                type: 'hidden',
                'id': this.config.saveContentTo,
                name: this.config.saveContentTo
            }));
        }
        $('menutree_content').setValue(Zikula.urlsafeJsonEncode(data, false));

        return true;
    },
    changeLang: function(lang) {
        this.tree.select('li a[lang=' + this.cLang + ']').invoke('hide');
        this.cLang = lang;
        this.tree.select('li a[lang=' + this.cLang + ']').invoke('show');
    },
    attachMenu: function() {
        this.config.menuConfig = Object.extend({
            objs:       '',
            trigger:    'click',
            dynamic:    true
        }, this.config.menuConfig);

        this.config.menuConfig.objs = '#' + this.tree.id + ' li a';

        this.menuItemsBind = this.menuItems.bind(this);
        this.config.menuConfig.items = this.menuItemsBind;

        this.menu = new ContextMenu(this.config.menuConfig);
    },
    menuItems: function(elementEvt) {
        var actionBind = this.menuAction.bind(this),
            element = elementEvt.element(),
            expandItem = {};
        if (element.up('li').down('ul') && element.up('li').down('ul').visible()) {
            expandItem = { name: 'collapse', displayname: this.config.langLabels.collapse, img: this.config.images.collapse, action: actionBind };
        } else if (element.up('li').down('ul') && !element.up('li').down('ul').visible()) {
            expandItem = { name: 'expand', displayname: this.config.langLabels.expand, img: this.config.images.expand, action: actionBind };
        } else {
            expandItem = { name: 'expand', displayname: this.config.langLabels.expand, disabled: true, img: this.config.images.expand, action: actionBind };
        }
        if (this.config.maxDepth > 0) {
            var addAsChildDisabled = (this.countLevels(element.up('li'),'up') + 2) > this.config.maxDepth;
        }
        var menuItems = {
            edit: { name: 'edit', displayname: this.config.langLabels.edit, img: this.config.images.edit,action: actionBind },
            remove: { name: 'remove', displayname: this.config.langLabels.remove, img: this.config.images.remove, confirm: this.config.langLabels.delConfirm, action: actionBind },
            add: { name: 'add', displayname: this.config.langLabels.add, img: this.config.images.add,
                action: {
                    before: { name: 'before', displayname: this.config.langLabels.before,img: this.config.images.before,action: actionBind },
                    after: { name: 'after', displayname: this.config.langLabels.after,img: this.config.images.after,action: actionBind },
                    bottom: { name: 'bottom', displayname: this.config.langLabels.bottom,img: this.config.images.bottom, action: actionBind, disabled: addAsChildDisabled }
                }
            },
            s1: true,
            expand: expandItem,
            toggle: { name: 'toggle', displayname: element.hasClassName(this.config.unactiveClass) ? this.config.langLabels.activate : this.config.langLabels.deactivate,img: element.hasClassName(this.config.unactiveClass) ? this.config.images.activate : this.config.images.deactivate , action: actionBind }
        };
        if (this.multilingual) {
            Object.extend(menuItems,{
                onoffs: { name: 'onoffs', displayname: this.config.langLabels.multitoggle, img: this.config.images.multitoggle,
                    action: {
                        on: { name: 'on', displayname: this.config.langLabels.multiactivate,img: this.config.images.multiactivate, action: actionBind },
                        off: { name: 'off', displayname: this.config.langLabels.multideactivate,img: this.config.images.multideactivate, action: actionBind }
                    }
                }
            });
        }

        return menuItems;
    },
    menuAction: function(elementEvt, actionEvt) {
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
    switchNode: function(obj, full, on) {
        if (full) {
            if (on) {
                obj.up('li').select('a').invoke('removeClassName', this.config.unactiveClass);
            } else {
                obj.up('li').select('a').invoke('addClassName', this.config.unactiveClass);
            }
        } else {
            obj.toggleClassName(this.config.unactiveClass);
        }
        this.unsaved = true;
    },
    getNodeData: function(node, index, forSerialize) {
        var link, nodeData = {}, prefix = forSerialize ? '' : 'link_';
        this.config.langs.each(function(lang) {
            link = node.down('a[lang=' + lang + ']');
            nodeData[lang] = {}
            nodeData[lang][prefix+'id'] = this.getNodeId(node);
            nodeData[lang][prefix+'name'] = link.innerHTML;
            nodeData[lang][prefix+'title'] = link.readAttribute('title');
            nodeData[lang][prefix+'className'] = $w(link.className).without(this.config.unactiveClass).join(' ');
            nodeData[lang][prefix+'state'] = !link.hasClassName(this.config.unactiveClass);
            nodeData[lang][prefix+'href'] = link.readAttribute('href');
            nodeData[lang][prefix+'lang'] = link.readAttribute('lang');
            nodeData[lang][prefix+'lineno'] = index || null;
            nodeData[lang][prefix+'parent'] = node.up('#'+this.tree.id+' li') ? this.getNodeId(node.up('#'+this.tree.id+' li')) : 0;
        }.bind(this));

        return nodeData;
    },
    setNodeData: function(node, data) {
        var link;
        this.config.langs.each(function(lang) {
            if (data[lang]) {
                link =  node.down('a[lang=' + lang + ']');
                link.update(data[lang].link_name.escapeHTML() || '');
                link.writeAttribute('href', data[lang].link_href || null);
                link.writeAttribute('title', data[lang].link_title ? data[lang].link_title.escapeHTML() : null);
                link.writeAttribute('className', data[lang].link_className || null);
                if (!data[lang].link_state) {
                    link.addClassName(this.config.unactiveClass);
                }
                link.writeAttribute('lang', data[lang].link_lang || this.defaultLang);
                this.unsaved = true;
            }
        }.bind(this));
        if (node.select('a').any(function(a) {
            return this.config.dynamicPattern(a.readAttribute('href'));
        }.bind(this))) {
            node.addClassName(this.config.dynamicClass);
        }
        this.save();
        this.tree.fire('tree:item:save', { node: node });
    },
    addNode: function() {
        var node = new Element('li', { id: this.config.nodeIdPrefix + this.genNextId() });
        switch(this.formaction) {
            case 'new':
                this.tree.insert(node);
                break;
            case 'before':
                this.referer.insert({ before: node });
                break;
            case 'after':
                this.referer.insert({ after: node });
                break;
            case 'bottom':
                var subnode = this.referer.down('ul');
                if (subnode) {
                    subnode.insert({ bottom: node });
                    subnode.show();
                } else {
                    this.referer.insert(new Element('ul').insert(node));
                }
                break;
        }
        this.config.langs.each(function(lang) {
            var link = new Element('a', { lang: lang });
            node.insert(link);
            if (!this.tmp[lang] || !this.tmp[lang].link_name) {
                var validlang = this.config.langs.find(function(n) {
                   return this.tmp[n].link_name;
                }.bind(this))
                this.tmp[lang].link_name = this.tmp[validlang].link_name;
                this.tmp[lang].link_state = false;
            }
        }.bind(this));
        this.setNodeData(node,this.tmp);
        node.select('a').each(this.menu.add.bind(this.menu));
        this.initNode(node);
        this.drawNodes();
    },
    readNode: function(obj) {
        this.tmp = {};
        if (obj && Object.isElement(obj)) {
            obj = obj.tagName == 'LI' ? obj : obj.up('li');
            this.tmp = this.getNodeData(obj);
            var urls = [];
            var classnames = [];
            this.config.langs.each(function(lang) {
                urls.push(this.tmp[lang].link_href);
                classnames.push(this.tmp[lang].link_className);
            }.bind(this));
            this.tmp.global = {
                link_href:   urls.uniq().size() <= 1,
                link_className:  classnames.uniq().size() <= 1
            }
        } else {
            this.config.langs.each(function(lang) {
                this.tmp[lang] = Object.extend({
                    link_state:  true,
                    link_lang:   lang
                }, obj || {});
            }.bind(this));
            this.tmp.global = {
                link_href:   true,
                link_className:  true
            }
        }
    },
    newNode: function(data) {
        if (data) {
            for (var item in data) {
                data[item] = data[item].unescapeHTML();
            }
        }
        this.readNode(data);
        this.formaction = 'new';
        this.showForm();
    },
    buildForm: function() {
        if (!this.formDialog) {
            var options = {
                title: $('menutree_form_container').title,
                height: 400
            };
            this.formDialog = new Zikula.UI.FormDialog($('menutree_form_container'), this.submitForm.bind(this), options);
            this.form = this.formDialog.window.container.down('form');
//            this.form = this.formDialog.form;
            if ($('link_lang')) {
                $('link_lang').observe('change', this.changeFormLang.bindAsEventListener(this));
            }
        }
    },
    loadFormValues: function(lang, oldlang) {
        lang = lang ? lang : this.cLang;
        this.formLang = lang;
        var data = this.tmp[lang],
            global = this.tmp.global;
        if (oldlang && this.tmp.global.link_href) {
            data.href = this.tmp[oldlang].link_href;
        }
        if (oldlang && this.tmp.global.link_className) {
            data.link_className = this.tmp[oldlang].link_className;
        }
        this.form.getElements().each(function(element) {
            if (element.id.startsWith('global_')) {
                element.setValue(global[element.id.replace('global_','')]);
            } else {
                element.setValue(data[element.id]);
            }
        })
    },
    showForm: function(obj) {
        this.buildForm();
        this.form.reset();
        this.formLang = this.cLang;
        if ($('requiredInfo')) {
            $('requiredInfo').hide();
        }
        this.editedNode = Object.isElement(obj) ? obj.up('li').id : this.genNextId();
        this.loadFormValues();
        this.formDialog.open();
    },
    submitForm: function(data) {
        if (!data) {
            delete this.tmp;
            return;
        }
        delete data.submit;
        this.tmp[this.formLang] = data;
        if (this.tmp.global && (this.tmp.global.link_href || this.tmp.global.link_className)) {
            this.config.langs.each(function(lang) {
                if (this.tmp.global.link_href) {
                    this.tmp[lang].link_href = data.link_href;
                }
                if (this.tmp.global.link_className) {
                    this.tmp[lang].link_className = data.link_className;
                }
            }.bind(this));
        }
        if (this.formaction == 'edit') {
            this.setNodeData($(this.editedNode),this.tmp);
        } else {
            this.addNode();
        }
    },
    changeFormLang: function(event) {
        var newlang = event.element().value,
            data = this.form.serialize(true);
        data.link_lang = this.formLang;
        this.tmp[this.formLang] = data;
        this.tmp.global.link_href = data.global_link_href;
        this.tmp.global.link_className = data.global_link_className;
        this.loadFormValues(newlang,this.formLang);
    },
    genNextId: function() {
        var maxId = this.tree.select('li').max(function(node) {
            return parseInt(this.getNodeId(node));
        }.bind(this));

        maxId = isNaN(maxId) ? 0 : maxId;

        return ++maxId;
    },
    beforeUnloadHandler: function (event) {
        if (this.unsaved && this.config.langLabels.warnbeforeunload) {
            return event.returnValue = this.config.langLabels.warnbeforeunload;
        }
        return false;
    }
});
Object.extend(Zikula.Menutree.Tree,{
    add: function(element, config) {
        if (!this.inst) {
            // avaiable outside as Zikula.Menutree.Tree.inst
            this.inst = new Zikula.Menutree.Tree(element,config);
        }
    }
});

//new Element('p').appendText('test');
Element.addMethods({
    appendText: function(element, text) {
        element.appendChild(document.createTextNode(text));
        return $(element);
    }
});
