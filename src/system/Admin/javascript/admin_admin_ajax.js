// Copyright Zikula Foundation 2009 - license GNU/LGPLv3 (or at your option, any later version).

/**
 * Zikula AdminPanel namespace
 *
 * @name Zikula.AdminPanel
 * @namespace Zikula AdminPanel namespace
 */
Zikula.define('AdminPanel');

Zikula.AdminPanel.labels = {
    clickToEdit: Zikula.__('Right-click down arrows to edit tab name'),
    edit: Zikula.__('Edit category'),
    remove: Zikula.__('Delete category'),
    makeDefault: Zikula.__('Make default category'),
    saving: Zikula.__('Saving')
};

Zikula.AdminPanel.setupNotices = function() {
    var options = {
            headerSelector: 'strong',
            headerClassName: 'z-systemnoticeheader z-panel-indicator',
            effectDuration: 0.5
        },
        ul_first = $$('#z-developernotices ul:first');
    if ($('z-securityanalyzer')) {
        options.active = [0];
    }
    Zikula.AdminPanel.noticesPanels = new Zikula.UI.Panels('admin-systemnotices', options);
    if (ul_first[0]) {
        ul_first[0].removeClassName('z-hide');
    }
};

Zikula.AdminPanel.Tab = Class.create(/** @lends Zikula.AdminPanel.Tab.prototype */{
    initialize: function(tab, create) {
        if (create) {
            this.tab = this.createTab(create);
        } else {
            this.tab = $(tab);
        }
        this.id = /admintab_(\d+)/.exec(this.tab.identify())[1];
        this.attachActionsMenu();
        this.attachModulesMenu();
        this.attachEditor();
        Droppables.add(this.tab.down('a'), {
            accept: 'draggable',
            hoverclass: 'ajaxhover',
            onDrop: function(module, tab) {
                Zikula.AdminPanel.Module.getModule(module).move(tab);
            }
        });
        this.tab.store('tab', this);
    },
    createTab: function(data) {
        var link = new Element('a', {
                href: data.url
            }).update(data.name),
            menu = new Element('span', {
                'class': 'z-admindrop'
            }).update('&nbsp;'),
            tab = new Element('li', {
                id: 'admintab_' + data.id,
                'class': 'admintab',
                style: 'z-index: 0'
            }).insert(link).insert(menu);

        $('addcat').insert({
            before: tab
        });
        return tab;
    },
    attachActionsMenu: function() {
        var menuContainer = this.tab.down('span'),
            menu = new Zikula.UI.ContextMenu(menuContainer, {animation: false}),
            tabKlass = this;
        menu.addItem({
            label: Zikula.AdminPanel.labels.edit,
            callback: function(item) {
                tabKlass.editor.enterEditMode();
            }
        });
        menu.addItem({
            label: Zikula.AdminPanel.labels.remove,
            callback: function(item) {
                tabKlass.deleteTab();
            }
        });
        menu.addItem({
            label: Zikula.AdminPanel.labels.makeDefault,
            callback: function(item) {
                tabKlass.setTabDefault();
            }
        });
        this.actionsMenu = menu;
    },
    attachModulesMenu: function() {
        var menuContainer = this.tab.down('span'),
            modules = Zikula.AdminPanel.Tab.menusData[this.id] ? Zikula.AdminPanel.Tab.menusData[this.id].items : [],
            menu = new Zikula.UI.ContextMenu(menuContainer, {
                leftClick: true,
                animation: false
            });
        modules.each(function(item) {
            menu.addItem({
                label: item.menutext,
                moduleId: item.id,
                callback: function() {window.location = item.menutexturl;}
            });
        });
        this.modulesMenu = menu;
    },
    attachEditor: function() {
        var link = this.tab.down('a'),
            tabKlass = this;
        this.editor = new Ajax.InPlaceEditor(link, 'index.php?module=Admin&type=ajax&func=editCategory', {
            clickToEditText: Zikula.AdminPanel.labels.clickToEdit,
            savingText: Zikula.AdminPanel.labels.saving,
            externalControl: 'admintabs-none',
            externalControlOnly: true,
            rows: 1,
            cols: link.innerHTML.length,
            submitOnBlur: true,
            okControl: false,
            cancelControl: false,
            ajaxOptions: Zikula.Ajax.Request.defaultOptions(),
            // in webkit browsers , when submitOnBlur is true
            // enter press causes form submission twice so catch this event, stop it and call blur on input
            onFormCustomization: function(editor, form) {
                $(form).observe('keypress', function(event) {
                    if (event.keyCode === Event.KEY_RETURN) {
                        event.stop();
                        event.element().blur();
                    }
                });
            },
            onEnterEditMode: function(form, value) {
                tabKlass.originalText = link.innerHTML;
            },
            callback: function(form, value) {
                return {
                    name: value,
                    cid: tabKlass.id
                };
            },
            onComplete: function(transport, element) {
                transport = Zikula.Ajax.Response.extend(transport);
                if (!transport.isSuccess()) {
                    link.update(tabKlass.originalText);
                    Zikula.showajaxerror(transport.getMessage());
                    return;
                }
                var data = transport.getData();
                link.update(data.response);
            }
        });
    },
    deleteTab: function() {
        var pars = {
            cid: this.id
        };
        new Zikula.Ajax.Request("index.php?module=Admin&type=ajax&func=deleteCategory", {
            parameters: pars,
            onComplete: this.deleteTabResponse.bind(this)
        });
    },
    deleteTabResponse: function(response) {
        if (!response.isSuccess()) {
            Zikula.showajaxerror(response.getMessage());
            return;
        }
        this.tab.remove();
        Zikula.AdminPanel.Tab.removeTab(this.id);
    },
    setTabDefault: function() {
        var pars = {
            cid: this.id
        };
        new Zikula.Ajax.Request("index.php?module=Admin&type=ajax&func=defaultCategory", {
            parameters: pars,
            onComplete: this.setTabDefaultResponse.bind(this)
        });
    },
    setTabDefaultResponse: function(response) {
        if (!response.isSuccess()) {
            Zikula.showajaxerror(response.getMessage());
        }
    }
});

Object.extend(Zikula.AdminPanel.Tab, /** @lends Zikula.AdminPanel.Tab */{
    tabs: {},
    menusData: {},
    getTab: function(element) {
        element = $(element);
        var tab;
        if (element.nodeName === 'LI' && element.hasClassName('admintab')) {
            tab = element;
        } else {
            tab = element.up('li.admintab');
        }
        return tab ? $(tab).retrieve('tab') : null;
    },
    removeTab: function(tabId) {
        delete this.tabs[tabId];
        this.setupSortable();
    },
    init: function() {
        this.menusData = $('admintabs-menuoptions').getValue().unescapeHTML().evalJSON();
        this.setupSortable();
        // prevent clicks on links during dragging tabs
        var preventDefault = function(event) {
            event.preventDefault();
            event.element().stopObserving('click', preventDefault);
        };
        Draggables.addObserver({
            onStart: function(name, draggable, event) {
                draggable.element.down('a').stopObserving('click', preventDefault);
                setTimeout(function() {
                    draggable.element.down('a').observe('click', preventDefault);
                }, 200);
            }
        });
        $('admintabs').select('li.admintab').each(function(tab) {
            var klass = new Zikula.AdminPanel.Tab(tab);
            this.tabs[klass.id] = klass;
        }.bind(this));
        this.setupForm();
    },
    setupSortable: function() {
        Sortable.destroy('admintabs');
        Sortable.create('admintabs', {
            tag: 'li',
            constraint: 'horizontal',
            onUpdate: function(sortable) {
                var pars = Sortable.serialize('admintabs');
                //send the new sort order to the ajax controller
                new Zikula.Ajax.Request(
                    'index.php?module=Admin&type=ajax&func=sortCategories', {
                        parameters: pars,
                        onComplete: function(response) {
                            if (!response.isSuccess()) {
                                Zikula.showajaxerror(response.getMessage());
                            }
                        }
                    }
                );
            },
            //prevents sorting of the "add new category" link
            only: ['admintab', 'active']
        });
    },
    setupForm: function() {
        this.addTabLink = $('addcatlink');
        this.addTabForm = $('ajaxNewCatHidden').removeClassName('z-hide').hide();
        this.addTabLink.observe('click', this.addTabShowForm.bindAsEventListener(this));
        this.addTabForm.down('form').observe('submit', this.addTabSave.bindAsEventListener(this));
        this.addTabForm.down('a.cancel').observe('click', this.addTabHideForm.bindAsEventListener(this));
        this.addTabForm.down('a.save').observe('click', this.addTabSave.bindAsEventListener(this));
        return this;
    },
    addTabShowForm: function(event) {
        if (event) {
            event.stop();
        }
        this.addTabLink.hide();
        this.addTabForm.show();
        return this;
    },
    addTabHideForm: function(event) {
        if (event) {
            event.stop();
        }
        this.addTabLink.show();
        this.addTabForm.hide().down('form').reset();
        return this;
    },
    addTabSave: function(event) {
        event.stop();
        var name = this.addTabForm.down('[name=name]').getValue();
        if (name === '') {
            Zikula.showajaxerror(Zikula.__('You must enter a name for the new category'));
            return this;
        }
        this.addTabHideForm();
        var pars = {
            name: name
        };
        new Zikula.Ajax.Request("index.php?module=Admin&type=ajax&func=addCategory", {
            parameters: pars,
            onComplete: this.addTabResponse.bind(this)
        });
        return this;
    },
    addTabResponse: function(response) {
        if (!response.isSuccess()) {
            Zikula.showajaxerror(response.getMessage());
            this.addTabHideForm();
            return this;
        }
        var data = response.getData(),
            tabKlass = new Zikula.AdminPanel.Tab(null, data);
        this.tabs[tabKlass.id] = tabKlass;
        Zikula.AdminPanel.Tab.setupSortable();

        return this;
    }
});

// modules
/*
 to review:
 - moving module to other tab
 */

Zikula.AdminPanel.Module = Class.create(/** @lends Zikula.AdminPanel.Module.prototype */{
    initialize: function(module) {
        this.module = $(module);
        this.id = (/module_(\d+)/).exec($(this.module).identify())[1];
        this.attachMenu();
        this.module.store('module', this);
    },
    attachMenu: function() {
        var modLinks = this.module.down('input.modlinks').getValue().unescapeHTML().evalJSON() || [],
            menu;
        if (modLinks.size() > 0) {
            menu = new Zikula.UI.ContextMenu(this.module.down('.module-context'), {
                leftClick: true,
                animation: false
            });
            modLinks.each(function(item) {
                menu.addItem({
                    label: item.text,
                    callback: function() {window.location = item.url;}
                });
            });
            this.menu = menu;
        }
    },
    move: function(tab) {
        var pars = {
            modid: this.id,
            cat: Zikula.AdminPanel.Tab.getTab(tab).id
        };
        new Zikula.Ajax.Request("index.php?module=Admin&type=ajax&func=changeModuleCategory", {
            parameters: pars,
            onComplete: this.moveResponse.bind(this)
        });
    },
    moveResponse: function(response) {
        if (!response.isSuccess()) {
            Zikula.showajaxerror(response.getMessage());
            return;
        }
        var data = response.getData();
        if (data.parentCategory == data.oldCategory) {
            return this;
        }
        // add module to new tab menu
        Zikula.AdminPanel.Tab.tabs[data.parentCategory].modulesMenu.addItem({
            label: data.name,
            moduleId: data.id,
            callback: function() {window.location = data.url;}
        });
        // remove from old tab
        var oldTabMenu = Zikula.AdminPanel.Tab.tabs[data.oldCategory].modulesMenu.items;
        oldTabMenu.each(function(item, index) {
            if (item.moduleId == data.id) {
                oldTabMenu.splice(index, 1);
            }
        });
        // remove from this panel
        this.module.remove();
        Zikula.AdminPanel.Module.removeModule(this.id);
    }

});
Object.extend(Zikula.AdminPanel.Module, /** @lends Zikula.AdminPanel.Module */{
    modules: {},
    getModule: function(module) {
        return $(module).retrieve('module');
    },
    removeModule: function(moduleId) {
        delete this.modules[moduleId];
        this.setupSortable();
    },
    init: function() {
        this.setupSortable();
        // Zikula.AdminPanel.Module.modules[this.id] = this;
        $$('.z-adminiconcontainer').each(function(module) {
            var klass = new Zikula.AdminPanel.Module(module);
            this.modules[klass.id] = klass;
        }.bind(this));
    },
    setupSortable: function() {
        if ($$('.z-adminiconcontainer').size() === 0) {
            return;
        }
        Sortable.destroy('modules');
        Sortable.create('modules', {
            tag: 'div',
            constraint: '',
            only: ['z-adminiconcontainer'],
            handle: 'z-dragicon',
            onUpdate: function(element) {
                var pars = Sortable.serialize('modules');
                //send the new order to the ajax controller
                new Zikula.Ajax.Request('index.php?module=Admin&type=ajax&func=sortModules', {
                    parameters: pars,
                    onComplete: function(response) {
                        if (!response.isSuccess()) {
                            Zikula.showajaxerror(response.getMessage());
                        }
                    }
                });
            }
        });
    }
});

Zikula.AdminPanel.init = function() {
    Zikula.AdminPanel.setupNotices();
    Zikula.AdminPanel.Tab.init();
    Zikula.AdminPanel.Module.init();
};
document.observe('dom:loaded', Zikula.AdminPanel.init);

