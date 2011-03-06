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
    Zikula.Categories.ContextMenu = new Control.ContextMenu(Zikula.TreeSortable.trees.categoriesTree.tree, {
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
            Zikula.UI.Confirm(Zikula.__('Do you really want to delete this category?'), Zikula.__('Confirmation prompt'), function(res){
                if(res) {
                    Zikula.Categories.MenuAction(node, 'delete');
                }
            });
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
            return !Zikula.Categories.ContextMenu.lastClick.findElement('a').up('li').hasClassName(Zikula.TreeSortable.trees.categoriesTree.config.nodeLeaf);
        },
        callback: function(node){
            Zikula.Categories.MenuAction(node, 'addchild');
        }
    });
};

Zikula.Categories.Indicator = function() {
    return $('ajax_indicator') ? $('ajax_indicator') : new Element('img',{id: 'ajax_indicator', src: 'images/ajax/indicator_circle.gif'});
};

Zikula.Categories.MenuAction = function(node, action){
    if (!['edit', 'delete', 'copy', 'activate', 'deactivate', 'addafter', 'addchild'].include(action)) {
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

    var request = new Zikula.Ajax.Request(
        url, {
            method: 'post',
            parameters: pars,
            authid: 'categoriesauthid',
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
        method: 'post',
        parameters: pars,
        authid: 'categoriesauthid',
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
    var pars = Zikula.Categories.Form.serialize(true);
    pars.mode = 'new';
    new Zikula.Ajax.Request('ajax.php?module=Categories&func=save', {
        method: 'post',
        parameters: pars,
        authid: 'categoriesauthid',
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
            method: 'post',
            parameters: pars,
            authid: 'categoriesauthid',
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
