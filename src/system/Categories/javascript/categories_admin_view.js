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
        label: Zikula.__('Add'),
        callback: function(node){
            Zikula.Categories.MenuAction(node, 'add');
        }
    });
};

Zikula.Categories.Indicator = function() {
    return $('ajax_indicator') ? $('ajax_indicator') : new Element('img',{id: 'ajax_indicator', src: 'images/ajax/indicator_circle.gif'});
};

Zikula.Categories.MenuAction = function(node, action){
    if (!['edit', 'delete', 'copy', 'activate', 'deactivate', 'add'].include(action)) {
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
        case 'add':
            pars.mode = 'new';
            pars.parent = Zikula.TreeSortable.trees.categoriesTree.getNodeId(node.up('li').up('li'));
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
    if (!Object.isUndefined(Zikula.Categories.Form) && Zikula.Categories.Form.isOpen) {
        Zikula.Categories.Form.destroy();
    }
    Zikula.Categories.Form = new Zikula.UI.FormDialog($('categories_ajax_form_container'),callback, {title: $('categories_ajax_form_container').title, width: 700, afterOpen: Zikula.Categories.InitEditView});
    return Zikula.Categories.Form.open();
};

Zikula.Categories.EditNode = function(res) {
    if (!res) {
        Zikula.Categories.Form.destroy();
        return false;
    }
    var pars = $('categories_ajax_form').serialize(true);
    pars.mode = 'edit';
    new Zikula.Ajax.Request('ajax.php?module=Categories&func=save', {
        method: 'post',
        parameters: pars,
        authid: 'categoriesauthid',
        onComplete: function(req) {
            if (!req.isSuccess()) {
                Zikula.showajaxerror(req.getMessage());
            } else {
                var data = req.getData(),
                    nodeId = Zikula.TreeSortable.trees.categoriesTree.config.nodePrefix + data.cid;
                $(nodeId).replace(data.node);
                Zikula.Categories.ReinitTreeNode($(nodeId), data);
            }
            Zikula.Categories.Form.destroy();
        }
    });
    return true;
};

Zikula.Categories.AddNode = function(res) {
    if (!res) {
        Zikula.Categories.Form.destroy();
        return false;
    }
    var pars = $('categories_ajax_form').serialize(true);
    pars.mode = 'new';
    new Zikula.Ajax.Request('ajax.php?module=Categories&func=save', {
        method: 'post',
        parameters: pars,
        authid: 'categoriesauthid',
        onComplete: function(req) {
            if (!req.isSuccess()) {
                Zikula.showajaxerror(req.getMessage());
            } else {
                var data = req.getData(),
                    newParent = $(Zikula.TreeSortable.trees.categoriesTree.config.nodePrefix + data.parent).down('ul');
                newParent.insert(data.node);
                var node = $(Zikula.TreeSortable.trees.categoriesTree.config.nodePrefix + data.cid);
                Zikula.Categories.ReinitTreeNode(node, data);
            }
            Zikula.Categories.Form.destroy();
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
