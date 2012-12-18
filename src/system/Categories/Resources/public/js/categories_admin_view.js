// Copyright Zikula Foundation 2009 - license GNU/LGPLv3 (or at your option, any later version).

Zikula.define('Categories');

Event.observe(window, 'load', function() {
    Zikula.TreeSortable.trees.categoriesTree.config.onSave = Zikula.Categories.Resequence;
    $('catExpand').observe('click',function(e){
        e.preventDefault();
        Zikula.TreeSortable.trees.categoriesTree.expandAll();
    });
    $('catCollapse').observe('click',function(e){
        e.preventDefault();
        Zikula.TreeSortable.trees.categoriesTree.collapseAll();
    });
    Zikula.UI.Tooltips($$('.tree a'));
    Zikula.Categories.AttachMenu();
});

Zikula.Categories.AttachMenu = function () {
    Zikula.Categories.ContextMenu = new Zikula.UI.ContextMenu(Zikula.TreeSortable.trees.categoriesTree.tree, {
        animation: false,
        beforeOpen: function(event) {
            Zikula.Categories.ContextMenu.lastClick = event;
            if(!event.findElement('a')) {
                throw $break;
            }
        }
    });
    Zikula.Categories.ContextMenu.addItem({
        label: Zikula.__('Edit'),
        callback: function(node){
            Zikula.Categories.MenuAction(node, 'edit');
        }
    });
    Zikula.Categories.ContextMenu.addItem({
        label: Zikula.__('Delete'),
        callback: function(node){
            Zikula.Categories.DeleteMenuAction(node);
        }
    });
    Zikula.Categories.ContextMenu.addItem({
        label: Zikula.__('Copy'),
        callback: function(node){
            Zikula.Categories.MenuAction(node, 'copy');
        }
    });
    Zikula.Categories.ContextMenu.addItem({
        label: Zikula.__('Activate'),
        condition: function() {
            return Zikula.Categories.ContextMenu.lastClick.findElement('a').hasClassName(Zikula.TreeSortable.trees.categoriesTree.config.nodeUnactive);
        },
        callback: function(node){
            Zikula.Categories.MenuAction(node, 'activate');
        }
    });
    Zikula.Categories.ContextMenu.addItem({
        label: Zikula.__('Deactivate'),
        condition: function() {
            return !Zikula.Categories.ContextMenu.lastClick.findElement('a').hasClassName(Zikula.TreeSortable.trees.categoriesTree.config.nodeUnactive);
        },
        callback: function(node){
            Zikula.Categories.MenuAction(node, 'deactivate');
        }
    });
    Zikula.Categories.ContextMenu.addItem({
        label: Zikula.__('Add category (after selected)'),
        callback: function(node){
            Zikula.Categories.MenuAction(node, 'addafter');
        }
    });
    Zikula.Categories.ContextMenu.addItem({
        label: Zikula.__('Add subcategory (into selected)'),
        condition: function() {
            return !Zikula.Categories.ContextMenu.lastClick.findElement('a').up('li').hasClassName('leaf');
        },
        callback: function(node){
            Zikula.Categories.MenuAction(node, 'addchild');
        }
    });
};

Zikula.Categories.Indicator = function() {
    return $('ajax_indicator') ? $('ajax_indicator') : new Element('img',{id: 'ajax_indicator', src: 'images/ajax/indicator_circle.gif'});
};

Zikula.Categories.DeleteMenuAction = function(node){
    var subCats = Zikula.Categories.ContextMenu.lastClick.findElement('a').up('li').down('ul'),
        msg = new Element('div',{id:'dialogContent'}).insert(
            new Element('p').update(Zikula.__('Do you really want to delete this category?'))
        ),
        buttons = [
            {name: 'Delete', value: 'Delete', label: Zikula.__('Delete'), 'class': 'z-btgreen'},
            {name: 'Cancel', value: 'Cancel', label: Zikula.__('Cancel'), 'class': 'z-btred'},
        ];
    subCats = subCats ? subCats.childElements().size() : 0;
    if (subCats > 0) {
        var info = Zikula.__f('It contains %s direct sub-categories.', subCats)
            + ' '
            + Zikula.__("Please also choose what to do with this category's sub-categories.");
        msg.insert(new Element('p',{'class':'z-informationmsg'}).update(info));
        buttons = [
            {name: 'Delete', value: 'Delete', label: Zikula.__('Delete all sub-categories'), 'class': 'z-btgreen'},
            {name: 'Delete', value: 'DeleteAndMoveSubs', label: Zikula.__('Move all sub-categories'), 'class': 'z-btgreen', close: false},
            {name: 'Cancel', value: 'Cancel', label: Zikula.__('Cancel'), 'class': 'z-btred'},
        ];
    }
    Zikula.Categories.DeleteDialog = new Zikula.UI.Dialog(
        msg,
        buttons,
        {title: Zikula.__('Confirmation prompt'), width: 500, callback: function(res){
             switch (res.value) {
                 case 'Delete':
                    Zikula.Categories.MenuAction(node, 'delete');
                    Zikula.Categories.DeleteDialog.destroy();
                    break;
                 case 'DeleteAndMoveSubs':
                    if (!$('subcat_move')) {
                        $('dialogContent').addClassName('z-form');
                        Zikula.Categories.DeleteDialog.window.indicator.appear({to: 0.7, duration: 0.2});
                        new Zikula.Ajax.Request('ajax.php?module=Categories&func=deletedialog', {
                            parameters: {cid: Zikula.TreeSortable.trees.categoriesTree.getNodeId(node.up('li'))},
                            onComplete: function(req) {
                                var subcat_move = req.getData().result;
                                $('dialogContent').insert(subcat_move);
                                Zikula.Categories.DeleteDialog.container.morph('height:250px');
                                Zikula.Categories.DeleteDialog.window.indicator.fade({duration: 0.2});
                            }
                        });
                    } else {
                        var parent = $F('category_parent_id_');
                        if (parent) {
                            Zikula.Categories.MenuAction(node, 'deleteandmovesubs', parent);
                            Zikula.Categories.DeleteDialog.destroy();
                        }
                    }
                    break;
                default:
                    Zikula.Categories.DeleteDialog.destroy();
             }
        }}
    );
    Zikula.Categories.DeleteDialog.open()
    Zikula.Categories.DeleteDialog.container.down('button[name=Cancel]').focus();
};

Zikula.Categories.MenuAction = function(node, action, extrainfo){
    if (!['edit', 'delete', 'deleteandmovesubs', 'copy', 'activate', 'deactivate', 'addafter', 'addchild'].include(action)) {
        return false;
    }
    node.insert({after: Zikula.Categories.Indicator()});
    var url = "ajax.php?module=Categories&func=",
        pars = {
            cid: Zikula.TreeSortable.trees.categoriesTree.getNodeId(node.up('li'))
        };
    switch(action) {
        case 'edit':
            pars.mode = 'edit';
            break;
        case 'deleteandmovesubs':
            pars.parent = extrainfo;
            break;
        case 'copy':
            pars.parent = Zikula.TreeSortable.trees.categoriesTree.getNodeId(node.up('li').up('li'));
            break;
        case 'addafter':
            pars.mode = 'new';
            pars.parent = Zikula.TreeSortable.trees.categoriesTree.getNodeId(node.up('li').up('li'));
            action = 'edit';
            break;
        case 'addchild':
            pars.mode = 'new';
            pars.parent = Zikula.TreeSortable.trees.categoriesTree.getNodeId(node.up('li'));
            action = 'edit';
            break;
    }
    url = url + action;

    new Zikula.Ajax.Request(
        url, {
            parameters: pars,
            onComplete: Zikula.Categories.MenuActionCallback
        });
    return true;
};

Zikula.Categories.MenuActionCallback = function(req) {
    if (!req.isSuccess()) {
        Zikula.showajaxerror(req.getMessage());
        return false;
    }
    var data = req.getData(),
        node = $(Zikula.TreeSortable.trees.categoriesTree.config.nodePrefix + data.cid);
    switch(data.action) {
        case 'delete':
            Droppables.remove(node);
            node.select('li').each(function(subnode) {
                Droppables.remove(subnode);
            });
            Effect.SwitchOff(node,{
                afterFinish: function() {node.remove();}
            });
            Zikula.TreeSortable.trees.categoriesTree.drawNodes();
            break;
        case 'deleteandmovesubs':
            Droppables.remove(node);
            node.select('li').each(function(subnode) {
                Droppables.remove(subnode);
            });
            Effect.SwitchOff(node,{
                afterFinish: function() {node.remove();}
            });
            var parent = Zikula.TreeSortable.trees.categoriesTree.config.nodePrefix + data.parent;
            $(parent).replace(data.node);
            Zikula.Categories.ReinitTreeNode($(parent), data);
            break;
        case 'activate':
            node.down('a').removeClassName(Zikula.TreeSortable.trees.categoriesTree.config.nodeUnactive);
            break;
        case 'deactivate':
            node.down('a').addClassName(Zikula.TreeSortable.trees.categoriesTree.config.nodeUnactive);
            break;
        case 'copy':
            var newNode = Zikula.TreeSortable.trees.categoriesTree.config.nodePrefix + data.copycid;
            $(newNode).replace(data.node);
            Zikula.Categories.ReinitTreeNode($(newNode), data);
            break;
        case 'edit':
            $(document.body).insert(data.result);
            Zikula.Categories.OpenForm(data, Zikula.Categories.EditNode);
            break;
        case 'add':
            $(document.body).insert(data.result);
            Zikula.Categories.OpenForm(data, Zikula.Categories.AddNode);
            break;
    }
    return true;
};

Zikula.Categories.OpenForm = function(data, callback) {
    if (Zikula.Categories.Form) {
        Zikula.Categories.Form.destroy();
    }
    Zikula.Categories.Form = new Zikula.UI.FormDialog($('categories_ajax_form_container'),callback, {
        title: $('categories_ajax_form_container').title, 
        width: 700, 
        afterOpen: Zikula.Categories.InitEditView,
        buttons: [
            {label: Zikula.__('Submit'), type: 'submit', name: 'submit', value: 'submit', 'class': 'z-btgreen', close: false},
            {label: Zikula.__('Cancel'), type: 'submit', name: 'cancel', value: false, 'class': 'z-btred', close: true}
        ]
    });
    return Zikula.Categories.Form.open();
};

Zikula.Categories.CloseForm = function() {
    Zikula.Categories.Form.destroy();
    Zikula.Categories.Form = null;
};

Zikula.Categories.UpdateForm = function(data) {
    $('categories_ajax_form_container').replace(data);
    Zikula.Categories.Form.window.indicator.fade({duration: 0.2});
    $('categories_ajax_form_container').show();
    Zikula.Categories.InitEditView();
};

Zikula.Categories.EditNode = function(res) {
    if (!res || (res.hasOwnProperty('cancel') && res.cancel === false)) {
        Zikula.Categories.CloseForm();
        return false;
    }
    Zikula.Categories.Form.window.indicator.appear({to: 0.7, duration: 0.2});
    var pars = Zikula.Categories.Form.serialize(true);
    pars.mode = 'edit';
    new Zikula.Ajax.Request('ajax.php?module=Categories&func=save', {
        parameters: pars,
        onComplete: function(req) {
            var data = req.getData();
            if (!req.isSuccess() || data.validationErrors) {
                Zikula.showajaxerror(req.getMessage());
                if (data && data.validationErrors) {
                    Zikula.Categories.UpdateForm(data.result);
                } else {
                    Zikula.Categories.CloseForm();
                }
            } else {
                var nodeId = Zikula.TreeSortable.trees.categoriesTree.config.nodePrefix + data.cid;
                $(nodeId).replace(data.node);
                Zikula.Categories.ReinitTreeNode($(nodeId), data);
                Zikula.Categories.CloseForm();
            }
        }
    });
    return true;
};

Zikula.Categories.AddNode = function(res) {
    if (!res || (res.hasOwnProperty('cancel') && res.cancel === false)) {
        Zikula.Categories.CloseForm();
        return false;
    }
    Zikula.Categories.Form.window.indicator.appear({to: 0.7, duration: 0.2});
    var pars = Zikula.Categories.Form.serialize(true);
    pars.mode = 'new';
    new Zikula.Ajax.Request('ajax.php?module=Categories&func=save', {
        parameters: pars,
        onComplete: function(req) {
            var data = req.getData();
            if (!req.isSuccess() || data.validationErrors) {
                Zikula.showajaxerror(req.getMessage());
                if (data && data.validationErrors) {
                    Zikula.Categories.UpdateForm(data.result);
                } else {
                    Zikula.Categories.CloseForm();
                }
            } else {
                var relNode = $(Zikula.TreeSortable.trees.categoriesTree.config.nodePrefix + data.parent),
                    newParent = relNode.down('ul');
                if (!newParent) {
                    newParent = new Element(('ul'),{'class': 'tree'});
                    relNode.insert(newParent);
                }
                newParent.insert(data.node);
                var node = $(Zikula.TreeSortable.trees.categoriesTree.config.nodePrefix + data.cid);
                Zikula.Categories.ReinitTreeNode(node, data);
                Zikula.Categories.CloseForm();
            }
        }
    });
    return true;
};

Zikula.Categories.ReinitTreeNode = function(node, data) {
    Zikula.TreeSortable.trees.categoriesTree.initNode(node);
    var subNodes = node.select('li');
    if (subNodes) {
        subNodes.each(Zikula.TreeSortable.trees.categoriesTree.initNode.bind(Zikula.TreeSortable.trees.categoriesTree));
    }
    Zikula.TreeSortable.trees.categoriesTree.drawNodes();

    if (data.leafstatus) {
        if (data.leafstatus.leaf) {
            Zikula.TreeSortable.trees.categoriesTree.config.disabledForDrop = Zikula.TreeSortable.trees.categoriesTree.config.disabledForDrop.concat(data.leafstatus.leaf);
        }
        if (data.leafstatus.noleaf) {
            Zikula.TreeSortable.trees.categoriesTree.config.disabledForDrop = [].without.apply(Zikula.TreeSortable.trees.categoriesTree.config.disabledForDrop, data.leafstatus.noleaf);
        }
    }
    Zikula.UI.Tooltips(node.select('a'));
};

Zikula.Categories.Resequence = function(node, params, data) {
    // do not allow inserts on root level
    if (node.up('li') === undefined) {
        return false;
    }
    var pars = {
            'data': data
        };
    node.insert({bottom: Zikula.Categories.Indicator()});

    var request = new Zikula.Ajax.Request(
        "ajax.php?module=Categories&func=resequence",
        {
            parameters: pars,
            onComplete: Zikula.Categories.ResequenceCallback
        });
    return request.success();
};

Zikula.Categories.ResequenceCallback = function(req)
{
    if (!req.isSuccess()) {
        Zikula.showajaxerror(req.getMessage());
        return Zikula.TreeSortable.categoriesTree.revertInsertion();
    }
    return true;
};
