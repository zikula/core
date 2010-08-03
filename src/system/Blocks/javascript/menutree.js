// Copyright Zikula Foundation 2009 - license GNU/LGPLv3 (or at your option, any later version).
/*
todo:

bugs:
- opera: dziwny wyglad listy (tymczasowy fix - rozwiniecie i zwiniececie calej listy)

*/

var myTree = Class.create();

myTree.prototype = {
    initialize: function() {
        this.config = Object.extend({
            treeElement:        '',//id ul, ktore ma byc przeksztalcone w drzewko
            imagesDir:          'img/',
            handler:            'handle',
            toggler:            'toggle',
            nodeNoChildren:     'noC',
            nodeLast:           'last',
            unactiveClass:      'unactive',
            draggableClass:     'draggable',
            onDragClass:        'onDragClass',
            dropOnClass:        'dropOnClass',
            dropAfterClass:     'dropAfterClass',
            dropBeforeClass:    'dropBeforeClass',
            dropAfterOverlap:   [0.3, 0.7],
            expandTimeout:      1500,
            maxDepth:           0,
            onLangChange:      Prototype.emptyFunction,
            langs:              [],//lista langow, obslugiwanych przez drzewko; puste - brak opcji wielojezycznych
            linkclasses:        [],//lista klas dla linkow, jesli puste - beda wprowadzane przez input
            formToObserve:      '',
            formElement:        'treeData',
            nodeIdPrefix:       'node_',
            nodeIdPattern:       /^[^_\-](?:[A-Za-z0-9\-\_]*)[_](.*)$/,
            stripbaseurl:       false,
            baseurl:            '',
            cookieName:         'menutree',
            dynamicClass:       'dynamic',
            dynamicPattern:     function(str) {return str ? str.startsWith('{ext:') : false;},
            langLabels: {},
            images: {},
            menuConfig: {}
        }, arguments[0] || { });

        //uzupelnij langi wartosciami domyslnymi
        this.config.langLabels = Object.extend({
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
        },this.config.langLabels);

        //uzupelnij langi wartosciami domyslnymi
        this.config.images = Object.extend({
            handler:            'folder.gif',
            toggle_minus:       'minus.gif',
            toggle_plus:        'plus.gif',
            edit:               'folder_edit.gif',
            remove:             'folder_delete.gif',
            add:                'folder_add.gif',
            before:             'before.gif',
            after:              'after.gif',
            bottom:             'aschild.gif',
            collapse:           'collapse.gif',
            expand:             'expand.gif',
            activate:           'folder_on.gif',
            deactivate:         'folder_off.gif',
            multitoggle:        'all-onoff.gif',
            multiactivate:      'all-on.gif',
            multideactivate:    'all-off.gif'
        },this.config.images);

        //dodaj do img sciezke
        for (var item in this.config.images) {
            if (this.config.images.hasOwnProperty(item)) {
                this.config.images[item] = this.config.imagesDir + this.config.images[item];
            }
        }

        //uzupelnij config menu wartosciami domyslnymi
        this.config.menuConfig = Object.extend({
            objs:       '',
            trigger:    'click',
            dynamic:    true
        },this.config.menuConfig);

        this.tree = $(this.config.treeElement);
        this.menuItemsBind = this.menuItems.bind(this);

        if(this.config.langs.length > 1) {
            this.multilingual = true;
            this.cLang = this.config.langs[0];
            this.defaultLang = this.config.langs[0];
        }
        if(this.config.linkclasses.size() > 0) {
            this.multiclass = true;
        }

        // load node status from cookie
//        this.cookie = new CookieJar({expires:'',path:'/'});
        this.cookieStatus = Zikula.Cookie.get(this.config.cookieName) ? $H($H(Zikula.Cookie.get(this.config.cookieName)).get('tree')) : new Hash();

        //przygotuj kazde li
        this.tree.select('li').each(this.initNode.bind(this));

        // stripbaseurl
        if(this.config.stripbaseurl) {
            var baseurl = new RegExp('^'+this.config.baseurl);
            this.tree.select('a').each(function(n) {
                n.href = n.readAttribute('href').replace(baseurl, '');
            }.bind(this));
        }


        //http://www.thefutureoftheweb.com/blog/detect-ie6-in-javascript 
        this.ie6 = false /*@cc_on || @_jscript_version < 5.7 @*/;

        this.attachMenu();
        this.observeForm();

        this.unsaved = false;
        Event.observe(window, 'unload', this.saveCookie.bindAsEventListener(this));
        Event.observe(window, 'beforeunload', this.beforeUnloadHandler.bindAsEventListener(this));
    },
    //WYSWIETLANIE
    initNode: function(node) {
        //ukryj na start dzieci
        if (node.down('ul') && !this.cookieStatus.get(node.id)) {
            node.down('ul').hide();
        }
        //ukryj nieaktywne wersje jezykowe
        if(this.multilingual) {
            node.select('a[lang!='+this.cLang+']').invoke('hide');
        }

        //wstaw element handler
        node.insert({
            top : new Element('img',{className:this.config.handler,src:this.config.images.handler})
        });
        //wstaw element toggler
        var toggler = new Element('img',{className:this.config.toggler,src:this.config.images.toggle_plus});
        node.insert({
            top : toggler
        });
        toggler.observe('click',this.toggleNode.bindAsEventListener(this));

        //rysowanie drzewka
        this.drawNode(node);

        //dodaj klase dla droppables
        node.addClassName(this.config.draggableClass);

        //init Draggable
        new Draggable(node,{
            handle:this.config.handler,
            onEnd: this.endDrag.bind(this),
            onStart : this.startDrag.bind(this),
            revert:true,
            //ghosting: true,
            starteffect:null,
            scroll: window
        });
        //init Droppables
        Droppables.add(node, {
            accept:this.config.draggableClass,
            hoverclass:this.config.dropOnClass,
            //overlap:'horizontal',
            overlap:'vertical',
            onDrop:this.dropNode.bind(this),
            onHover:this.hoverNode.bind(this)
        });

    },
    drawNode: function (node) {
        //czy jest to ostatnia galaz?
        if (node.next() == undefined) {
            node.addClassName(this.config.nodeLast);
        } else {
            node.removeClassName(this.config.nodeLast);
        }
        //czy ma dzieci?
        if (node.down('li') == undefined) {
            node.addClassName(this.config.nodeNoChildren);
            if (node.down('ul') != undefined) {
                node.down('ul').remove();
            }
        } else {
            node.removeClassName(this.config.nodeNoChildren);
            if(node.down('ul').visible()) {
                node.down('img.'+this.config.toggler).writeAttribute({src: this.config.images.toggle_minus});
            }
        }
    },
    drawNodes: function() {
        this.tree.select('li').each(this.drawNode.bind(this));
    },
    startDrag: function(draggable) {
        this.dropCache = {};
        draggable.element.addClassName(this.config.onDragClass);
    },
    endDrag: function(draggable) {
        if(this.dropCache.lastElement) {
            this.insertNode(draggable.element,this.dropCache.lastElement);
            this.drawNodes();
        }
        this.tree.select('.'+this.config.dropAfterClass)
            .invoke('removeClassName',this.config.dropAfterClass);
        this.tree.select('.'+this.config.dropBeforeClass)
            .invoke('removeClassName',this.config.dropBeforeClass);
        draggable.element.removeClassName(this.config.onDragClass);
    },
    hoverNode: function(node,dropOnNode,overlap) {
        window.clearTimeout(this.dropCache.timeout);
        this.tree.select('.'+this.config.dropAfterClass)
            .invoke('removeClassName',this.config.dropAfterClass);
        this.tree.select('.'+this.config.dropBeforeClass)
            .invoke('removeClassName',this.config.dropBeforeClass);
        if (overlap > this.config.dropAfterOverlap[1]) {
            dropOnNode.addClassName(this.config.dropBeforeClass);
            this.dropCache.lastElement = ['before',dropOnNode.id];
        } else if (overlap < this.config.dropAfterOverlap[0]) {
            dropOnNode.addClassName(this.config.dropAfterClass);
            this.dropCache.lastElement = ['after',dropOnNode.id];
        } else {
            this.dropCache.expand = this.expandOne.bindAsEventListener(this);
            this.dropCache.element = dropOnNode;
            this.dropCache.timeout = window.setTimeout(this.dropCache.expand, this.config.expandTimeout);
            dropOnNode.removeClassName(this.config.dropAfterClass);
            dropOnNode.removeClassName(this.config.dropBeforeClass);
        }
    },
    dropNode: function(node,dropOnNode,point) {
        var insertion = true,
            direction = 'bottom';
        if (dropOnNode.hasClassName(this.config.dropAfterClass)) {
            direction = 'after';
        } else if (dropOnNode.hasClassName(this.config.dropBeforeClass)) {
            direction = 'before';
        } else if(dropOnNode.hasClassName(this.config.dynamicClass)) {
            direction = 'after';
        }
        insertion = this.insertNode(node,[direction,dropOnNode]);
        if(!insertion) {
            return false;
        }
        //uporzadkuj drzewko
        this.dropCache = {};
        this.drawNodes();
    },
    insertNode: function(node,params) {
        var dropOnNode = $(params[1]),
            position = params[0],
            newlevel = position == 'bottom';
        if(this.config.maxDepth > 0) {
            var dropOnNodeLevel = this.countLevels(dropOnNode,'up'),
                nodeLevel = this.countLevels(node,'down'),
                treeDepth = dropOnNodeLevel + nodeLevel + Number(newlevel) + 1;
            if(treeDepth > this.config.maxDepth) {
                alert(this.config.langLabels.maxdepthreached + this.config.maxDepth);
                this.dropCache = {};
                return false;
            }
        }
        if(newlevel) {
            var ul = dropOnNode.down('ul',0);
            if (ul == undefined) {
                ul = new Element('ul');
                dropOnNode.insert(ul);
            }
            ul.show();
            dropOnNode = ul;
        }
        var obj = {}
        obj[position] = node;
        dropOnNode.insert(obj);
        this.unsaved = true;
        return true;
    },
    countLevels : function(node,mode,stop) {
        var levels = 0;
        if (mode == 'up') {
            stop = (stop == undefined) ? this.tree : stop;
            var ancestors = node.ancestors();
            levels = stop.select('ul')
                .select(function(item) {
                    return ancestors.include(item);
                }).size();
        } else if (mode == 'down') {
            levels = node.select('li').max(function(subnode) {
                return this.countLevels(subnode,"up",node);
            }.bind(this));
        }
        return isNaN(levels) ? 0 : levels;
    },
    toggleNode: function (event) {
        var target = event.element(),
            ul = target.up('li').down('ul');
        if (ul != undefined) {
            ul.toggle();
            target.src = (ul.visible()) ? (this.config.images.toggle_minus) : (this.config.images.toggle_plus);
        }
    },
    expandOne: function() {
        if(this.dropCache.element && this.dropCache.element.down('ul') != undefined) {
            this.dropCache.element.down('img.'+ this.config.toggler).writeAttribute({src: this.config.images.toggle_minus});
            this.dropCache.element.down('ul').show();
            this.dropCache.element = false;
        }
    },
    expandAll: function(node) {
        var base = node ? node : this.tree;
        base.select('li img.'+ this.config.toggler).invoke('writeAttribute',{src: this.config.images.toggle_minus});
        if(node) {
            node.down('img.'+ this.config.toggler).writeAttribute({src: this.config.images.toggle_minus});
        }
        base.select('ul').invoke('show');
    },
    collapseAll: function(node) {
        var base = node ? node : this.tree;
        base.select('li img.'+ this.config.toggler).invoke('writeAttribute',{src: this.config.images.toggle_plus});
        if(node) {
            node.down('img.'+ this.config.toggler).writeAttribute({src: this.config.images.toggle_plus});
        }
        base.select('ul').reverse(true).invoke('hide');
    },
    changeLang: function(lang){
        if(this.multilingual) {
            this.tree.select('li a[lang='+this.cLang+']').invoke('hide');
            this.cLang = lang;
            this.tree.select('li a[lang='+this.cLang+']').invoke('show');
            this.config.onLangChange(lang);
        }
    },
    //EDYCJA
    attachMenu: function() {
        if(!this.config.menuConfig.objs) {
            this.config.menuConfig.objs = '#'+this.tree.id+' li a';
        }
        this.config.menuConfig.items = this.menuItemsBind;
        this.menu = new ContextMenu(this.config.menuConfig);
    },
    //evt wywolujacy menu (gdzie byl klik)
    menuItems: function(elementEvt) {
        var actionBind = this.action.bind(this),
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
    //dwa args - evt wywolujacy menu (gdzie byl klik) oraz evt zamykajacy menu (jaka opcja z menu zostala wybrana)
    action: function(elementEvt,actionEvt) {
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
    newNode: function(data) {
        //data - trzeba obsluzyc predefininowane dane
        //np na potrzeby "add this url"
        //najpierw przeczysc data
        if(data) {
            for (var item in data) {
                data[item] = data[item].unescapeHTML();
            }
        }
        this.readNode(data);
        this.formaction = 'new';
        this.showForm(data);
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
    //link do deaktywacji, full - czy wszystkie wersje jezykowe?
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
/*
 */
    buildForm: function() {
        this.overlay = new Element('div',{id:'overlay'});
        this.formbox = new Element('div',{id:'formbox'});
        //href row
        var hrefRow = new Element('div',{className:'formrow z-clearfix'});
        if(this.multilingual){
            hrefRow
                .insert(new Element('label',{'for':'linkhref'}).appendText(this.config.langLabels.linkhref))
                .insert(new Element('div',{className:'formsubrow z-clearfix'})
                    .insert(new Element('input',{type:'text',name:'linkhref',id:'linkhref'}))
                    .insert(new Element('div',{className:'formrow z-clearfix'})
                        .insert(new Element('input',{type:'checkbox',className:'checkbox',name:'globallinkhref',id:'globallinkhref'}))
                        .insert(new Element('label',{'for':'globallinkhref'}).appendText(this.config.langLabels.usedefault))
                )
            );
        } else {
            hrefRow
                .insert(new Element('label',{'for':'linkhref'}).appendText(this.config.langLabels.linkhref))
                .insert(new Element('input',{type:'text',name:'linkhref',id:'linkhref'}));
        }
        //class row
        var classRow = new Element('div',{className:'formrow z-clearfix'});
        if(this.multiclass) {
            var classFormElement = new Element('select',{name:'linkclass',id:'linkclass'})
                .insert(new Element('option',{value: ''}).appendText(this.config.langLabels.linkclassblank));
            this.config.linkclasses.each(function(item){
                classFormElement.insert(new Element('option',{value: Object.keys(item)[0]}).appendText(Object.values(item)[0]));
            }.bind(this));
        } else {
            var classFormElement = new Element('input',{type:'text',name:'linkclass',id:'linkclass'});
        }
        if(this.multilingual){
            classRow
                .insert(new Element('label',{'for':'linkclass'}).appendText(this.config.langLabels.linkclass))
                .insert(new Element('div',{className:'formsubrow z-clearfix'})
                    .insert(classFormElement)
                    .insert(new Element('div',{className:'formrow z-clearfix'})
                        .insert(new Element('input',{type:'checkbox',className:'checkbox',name:'globallinkclass',id:'globallinkclass'}))
                        .insert(new Element('label',{'for':'globallinkclass'}).appendText(this.config.langLabels.usedefault))
                )
            );
        } else {
            classRow
                .insert(new Element('label',{'for':'linkclass'}).appendText(this.config.langLabels.linkclass))
                .insert(classFormElement)
        }
        this.form = new Element('form',{action:'#',id:'nodeBuilder'})
                .insert(new Element('div',{className:'z-clearfix'})
                    .insert(new Element('input',{type:'hidden',name:'clang',id:'clang'}))
                    .insert(new Element('div',{className:'formrow z-clearfix'})
                        .insert(new Element('label',{'for':'linkname'}).appendText(this.config.langLabels.linkname))
                        .insert(new Element('input',{type:'text',name:'linkname',id:'linkname',className:'required'}))
                    )
                    .insert(new Element('div',{className:'formrow z-clearfix'})
                        .insert(new Element('label',{'for':'linktitle'}).appendText(this.config.langLabels.linktitle))
                        .insert(new Element('input',{type:'text',name:'linktitle',id:'linktitle'}))
                    )
                    .insert(hrefRow)
                    .insert(classRow)
                    .insert(new Element('div',{className:'formrow z-clearfix'})
                        .insert(new Element('label',{'for':'linkstate'}).appendText(this.config.langLabels.linkstate))
                        .insert(new Element('input',{type:'checkbox',className:'checkbox',name:'linkstate',id:'linkstate'}))
                    )
                    .insert(new Element('div',{className:'formrow z-clearfix'})
                        .insert(new Element('input',{type:'submit',className:'submit',name: 'submit',value:this.config.langLabels.submit}))
                        .insert(new Element('input',{type:'button',className:'submit',name: 'cancel',id:'nodeBuilderCancel',value:this.config.langLabels.cancel}))
                    )
                );
        if(this.multilingual){
            var langSelect = new Element('select',{name:'linklang',id:'linklang',className:'required'});
            this.config.langs.each(function(lang){
                langSelect.insert(new Element('option',{value: lang}).appendText(lang));
            }.bind(this));
            this.form.down('div')
                .insert({top:(new Element('div',{className:'formrow z-clearfix'})
                    .insert(new Element('label',{'for':'linklang'}).appendText(this.config.langLabels.linklang))
                    .insert(langSelect)
                )});
        }
        if(this.config.langLabels.forminfo) {
            this.formbox.insert(new Element('p',{id:'forminfo'}).appendText(this.config.langLabels.forminfo));
        }

        //na poczatek ukryj
        this.formbox.hide();
        this.overlay.hide();

        document.body.appendChild(this.overlay);
        document.body.appendChild(this.formbox);
        this.formbox.appendChild(this.form);

        this.form.observe('submit',this.submitForm.bindAsEventListener(this));
        if(this.multilingual){
            $('linklang').observe('change',this.changeFormLang.bindAsEventListener(this));
        }
        $('nodeBuilderCancel').observe('click',this.removeForm.bindAsEventListener(this));
    },
    showForm: function(obj) {
        if(!this.form) {
            //create form
            this.buildForm();
        }
        //reset form, set initial values
        this.form.reset();
        $('clang').value = this.cLang;
        $('linkstate').checked = 'checked';
        if(this.multilingual){
            $('linklang').down('option[value='+this.cLang+']').selected = 'selected';
        }
        if($('requiredInfo')) {
            $('requiredInfo').hide();
        }

        this.editedNode = Object.isElement(obj) ? obj.up('li').id : this.genNextId();
        this.loadElementValues();

        if(this.ie6) {
            document.body.style.height = '100%';
            document.body.style.overflow = 'hidden';
            document.documentElement.style.height = '100%';
            document.documentElement.style.overflow = 'hidden';
            $$('select').invoke('addClassName','hideSelectsFromIE');
            window.scrollTo(0, 0);
        }
        this.overlay.show();
        this.formbox.show();
        $('linkname').focus();
    },
    readNode: function(obj) {
        this.tmp = {};
        if(obj && Object.isElement(obj)){
            if(this.multilingual){
                var node = obj.up('li');
                var urls = [];
                var classnames = [];
                this.config.langs.each(function(lang){
                    obj = node.down('a[lang='+lang+']');
                    this.tmp[lang] = {
                        linkname:   obj.innerHTML.unescapeHTML(),
                        linkhref:   obj.readAttribute('href'),
                        linktitle:  obj.title.unescapeHTML(),
                        linkclass:  $w(obj.className).without(this.config.unactiveClass).join(' '),
                        linkstate:  !obj.hasClassName(this.config.unactiveClass),
                        linklang:   lang
                    }
                    urls.push(this.tmp[lang].linkhref);
                    classnames.push(this.tmp[lang].linkclass);
                }.bind(this));
                this.tmp.global = {
                    linkhref:   urls.uniq().size() <= 1,
                    linkclass:  classnames.uniq().size() <= 1
                }
            } else {
                this.tmp = {
                    linkname:   obj.innerHTML.unescapeHTML(),
                    linkhref:   obj.readAttribute('href'),
                    linktitle:  obj.title.unescapeHTML(),
                    linkclass:  $w(obj.className).without(this.config.unactiveClass).join(' '),
                    linkstate:  !obj.hasClassName(this.config.unactiveClass)
                };
            }
        } else {
            if(this.multilingual){
                this.config.langs.each(function(lang){
                    this.tmp[lang] = Object.extend({
                        linkstate:  true,
                        linklang:   lang
                    }, obj || {});
                }.bind(this));
                this.tmp.global = {
                    linkhref:   true,
                    linkclass:  true
                }
            } else {
                this.tmp = {
                    linkstate:  true
                }
            }
        }
    },
    loadElementValues: function(lang,oldlang) {
        if(this.multilingual){
            lang = lang ? lang : this.cLang;
            var data = this.tmp[lang];
            $('globallinkhref').checked =  this.tmp.global.linkhref ? 'checked' : null;
            $('globallinkclass').checked =  this.tmp.global.linkclass ? 'checked' : null;
            if(oldlang && this.tmp.global.linkhref) {
                data.linkhref = this.tmp[oldlang].linkhref;
            }
            if(oldlang && this.tmp.global.linkclass) {
                data.linkclass = this.tmp[oldlang].linkclass;
            }
        } else {
            var data = this.tmp;
        }
        $('clang').value = data.linklang || lang;
        $('linkname').value = data.linkname || '';
        $('linkhref').value = data.linkhref || '';
        $('linktitle').value = data.linktitle || '';
        $('linkstate').checked = data.linkstate ? 'checked' : null;
        if(this.multiclass) {
            if(data.linkclass) {
                var clinkclass = $('linkclass').down('option[value='+data.linkclass+']');
                if(clinkclass) {
                    $('linkclass').down('option[value='+data.linkclass+']').selected = 'selected';
                } else {
                    $('linkclass').down('option[value=""]').selected = 'selected';
                }
            } else {
                $('linkclass').down('option[value=""]').selected = 'selected';
            }
        } else {
            $('linkclass').value = data.linkclass || '';
        }
    },
    changeFormLang: function(event) {
        var newlang = event.element().value,
            data = this.form.serialize(true);
        data.linklang = data.clang;
        this.tmp[data.clang] = data;
        this.tmp.global.linkhref = data.globallinkhref;
        this.tmp.global.linkclass = data.globallinkclass;
        this.loadElementValues(newlang,data.clang);
    },
    removeForm: function() {
        delete this.tmp;
        if(this.ie6) {
            document.body.style.height = 'auto';
            document.body.style.overflow = 'auto';
            document.documentElement.style.height = 'auto';
            document.documentElement.style.overflow = 'auto';
            $$('select').invoke('removeClassName','hideSelectsFromIE');
            this.tree.scrollTo();
        }
        this.formbox.hide();
        this.overlay.hide();
    },
    submitForm: function(event) {
        event.stop();
        var data = this.form.serialize(true);
        if(this.multilingual){
            this.tmp[data.clang] = data;
            this.tmp.global.linkhref = data.globallinkhref;
            this.tmp.global.linkclass = data.globallinkclass;
        } else {
            this.tmp = data;
        }
        if(!$('linkname').present()) {
            if(!$('requiredInfo')) {
                this.form.insert({before: new Element('p',{id:'requiredInfo'}).appendText(this.config.langLabels.required)});
            } else {
                $('requiredInfo').show();
            }
            $('linkname').focus();
            return;
        }
        if(this.tmp.global && (this.tmp.global.linkhref || this.tmp.global.linkclass)) {
            this.config.langs.each(function(lang){
                if(this.tmp.global.linkhref) {
                    this.tmp[lang].linkhref = data.linkhref;
                }
                if(this.tmp.global.linkclass) {
                    this.tmp[lang].linkclass = data.linkclass;
                }
            }.bind(this));
        }
        if(this.formaction == 'edit') {
            this.editNode();
        } else {
            this.addNode();
        }
        this.removeForm();
    },
    editNode: function() {
        this.tree.select('#'+this.editedNode+' > a').each(function(node) {
            if(this.multilingual) {
                this.saveNode(node,this.tmp[node.lang]);
            } else {
                this.saveNode(node,this.tmp);
            }
        }.bind(this));
    },
    addNode: function() {
        var node = new Element('li',{id:this.config.nodeIdPrefix+this.genNextId()});
        switch(this.formaction) {
            case 'new':
                this.tree.insert(node);
                break;
            case 'before':
                this.referer.insert({before: node});
                break;
            case 'after':
                this.referer.insert({after: node});
                break;
            case 'bottom':
                var subnode = this.referer.down('ul');
                if(subnode) {
                    subnode.insert({bottom: node});
                    subnode.show();
                } else {
                    this.referer.insert(new Element('ul').insert(node));
                }
                break;
        }
        if(this.multilingual){
            this.config.langs.each(function(language){
                var link = new Element('a',{lang:language});
                node.insert(link);
                if(this.tmp[language]) {
                    if(!this.tmp[language].linkname) {
                        //dla tego langa nie ma linku
                        //stworz pusty z nazwa glownego langa lub innego istniejacego
                        var validlang = this.config.langs.find(function(n) {
                           return this.tmp[n].linkname;
                        }.bind(this))
                        this.tmp[language].linkname = this.tmp[validlang].linkname;
                        this.tmp[language].linkstate = false;
                    }
                    this.saveNode(link,this.tmp[language]);
                }
                this.menu.add(link);
            }.bind(this));
        } else {
            var link = new Element('a');
            node.insert(link);
            this.saveNode(link,this.tmp);
            this.menu.add(link);
        }
        if(node.select('a').any(function(a) {
            return this.config.dynamicPattern(a.readAttribute('href'));
        }.bind(this))) {
            node.addClassName(this.config.dynamicClass);
        }
        this.initNode(node,true);
        this.drawNodes();
    },
    saveNode: function(obj,data) {
        obj.innerHTML = data.linkname.escapeHTML() || '';
        obj.writeAttribute('href',data.linkhref || null);
        obj.writeAttribute('title',data.linktitle ? data.linktitle.escapeHTML() : null);
        obj.writeAttribute('className',data.linkclass || null);
        if(!data.linkstate) {
            obj.addClassName(this.config.unactiveClass);
        }
        obj.writeAttribute('lang',data.linklang || null);
        this.unsaved = true;
    },
    genNextId: function() {
        var maxId = this.tree.select('li').max(function(node) {
            return Number(node.id.match(this.config.nodeIdPattern)[1]);
        }.bind(this));
        maxId = isNaN(maxId) ? 0 : maxId;
        return ++maxId;
    },
    //ZAPIS
    observeForm: function() {
        var treeForm = this.config.formToObserve ? $(this.config.formToObserve) : this.tree.up('form');
        if(treeForm) {
            treeForm.observe('submit', this.sendSaved.bindAsEventListener(this));
        }
    },
    sendSaved: function(event) {
        var form = event.element() ? Event.element(event) : $(event);
        this.save();
        this.unsaved = false;
        form.insert(new Element('input',{type:'hidden',name:this.config.formElement,id:this.config.formElement,value:this.saved}));
    },
    save: function() {
        this.saved = {};
        this.counter = 0;
        this.makeArray(this.tree.childElements(),0);
        delete this.counter;
        this.saved = this.serialize(this.saved);
    },
    makeArray: function(nodes,parent) {
        nodes.each(function(node,index){
            var node_id = Number(node.id.match(this.config.nodeIdPattern)[1]);
            if(this.multilingual){
                this.saved[this.counter] = {}
                this.config.langs.each(function(lang){
                    obj = node.down('a[lang='+lang+']');
                    this.saved[this.counter][lang] = {
                        id:         node_id,
                        name:       obj.innerHTML,
                        href:       obj.readAttribute('href'),
                        title:      obj.title,
                        className:  $w(obj.className).without(this.config.unactiveClass).join(' '),
                        state:      !obj.hasClassName(this.config.unactiveClass),
                        lang:       lang,
                        lineno:     index,
                        parent:     parent
                    };
                }.bind(this));
            } else {
                obj = node.down('a');
                var data = {
                    id:         node_id,
                    name:       obj.innerHTML,
                    href:       obj.readAttribute('href'),
                    title:      obj.title,
                    className:  $w(obj.className).without(this.config.unactiveClass).join(' '),
                    state:      !obj.hasClassName(this.config.unactiveClass),
                    lineno:     index,
                    parent:     parent
                };
                //jesli jest podany jeden jezyk - uwglednij to przy zapisie
                if(this.config.langs[0]) {
                    data.lang = this.config.langs[0];
                    this.saved[this.counter] = {}
                    this.saved[this.counter][this.config.langs[0]] = data;
                } else {
                    this.saved[this.counter] = data;
                }
            }

            this.counter++;
            if(node.down('ul')) {
                this.makeArray(node.down('ul').childElements(),node_id);
            }
        }.bind(this));
        return this.saved;
    },
    serialize: function (object) {
        // return PHPSerializer.serialize(object);
        return Object.toJSON(object);
    },
    saveCookie: function() {
        // get expanded nodes
        this.tree.select('ul').each(function(u) {
            if(u.visible()) {
                this.cookieStatus.set(u.up('li').identify(),true);
            }
        }.bind(this));
        var menutreeCookie = Zikula.Cookie.get(this.config.cookieName) ? $H($H(Zikula.Cookie.get(this.config.cookieName))) : new Hash();
        menutreeCookie.set('tree',this.cookieStatus);
        Zikula.Cookie.set(this.config.cookieName,menutreeCookie);
    },
    beforeUnloadHandler: function (event) {
        if(this.unsaved && this.config.langLabels.warnbeforeunload) {
            return event.returnValue = this.config.langLabels.warnbeforeunload;
        }
    }
}

//http://www.prototypejs.org/2007/5/12/dom-builder#comment-15901
//new Element('p').appendText('test');
Element.addMethods({
    appendText: function(element, text) {
        element.appendChild(document.createTextNode(text));
        return $(element);
    }
});