// Copyright Zikula Foundation 2009 - license GNU/LGPLv3 (or at your option, any later version).

var ZikulaCategories = {};

( function($) {

    var treeElem;

    $(document).ready(function() {
        treeElem = $('#categoryTreeContainer .treewraper');

        // Tree instantiation
        treeElem.jstree({
            'core': {
                'multiple': false,
                'check_callback': true
            },
            'dnd': {
                'copy': false,
                'is_draggable': function(node) {
                    // disable drag and drop for root category
                    var inst = node.inst;
                    var level = inst.get_path().length;

                    return (level > 1) ? true : false;
                }
            },
            'state': {
                'key': 'categoryTree'
            },
            'plugins': [ 'contextmenu', 'dnd', 'search', 'state', 'types' ],
            'types': {
                'default': {
                    'icon': 'fa fa-folder'
                },
                'leaf': {
                    'icon': 'fa fa-leaf'
                }
            },
        });

        // Types plugin
        treeElem.find('li.leaf').each(function (index) {
            $(this).set_type('leaf');
        });

        // Search plugin
        var searchStartDelay = false;
        $('#categoryTreeSearchTerm').keyup(function () {
            if (searchStartDelay) {
                clearTimeout(searchStartDelay);
            }
            searchStartDelay = setTimeout(function () {
                var v = $('#categoryTreeSearchTerm').val();
                treeElem.jstree(true).search(v);
            }, 250);
        });


        // Event handling

        // Clicking on the link will not direct the user to a new page,
        // to do that - intercept the changed.jstree event and act accordingly.
//         tree.on('move_node.jstree', function (e, data) {
//             var node = data.node;
//             var parentId = data.parent;
//             var parentNode = $tree.jstree('get_node', parentId, false);
//
//             Zikula.TreeSortable.trees.categoriesTree.config.onSave = ZikulaCategories.Resequence;
//             CategoryTreeSave(node, parentNode, 'bottom');
//         });


        // Tree interaction
        $('#catExpand').click(function(event) {
            event.preventDefault();
            treeElem.jstree(true).open_all(null, 500);
        });
        $('#catCollapse').click(function(event) {
            event.preventDefault();
            treeElem.jstree(true).close_all(null, 500);
        });

//         $('button').on('click', function () {
//             $('#jstree').jstree(true).select_node('child_node_1');
//             $('#jstree').jstree('select_node', 'child_node_1');
//         });


        treeElem.find('a').tooltip({
            container: 'body',
            position: 'auto right',
            html: true
        });

//         ZikulaCategories.AttachMenu();
    });

    ZikulaCategories.AttachMenu = function () {
        ZikulaCategories.ContextMenu = new Zikula.UI.ContextMenu(Zikula.TreeSortable.trees.categoriesTree.tree, {
            animation: false,
            beforeOpen: function(event) {
                ZikulaCategories.ContextMenu.lastClick = event;
                if (!event.target.closest('a').length) {
                    event.stop();
                }
            }
        });
        ZikulaCategories.ContextMenu.addItem({
            label: Zikula.__('Edit'),
            callback: function(node) {
                ZikulaCategories.MenuAction(node, 'edit');
            }
        });
        ZikulaCategories.ContextMenu.addItem({
            label: Zikula.__('Delete'),
            callback: function(node) {
                ZikulaCategories.DeleteMenuAction(node);
            }
        });
        ZikulaCategories.ContextMenu.addItem({
            label: Zikula.__('Copy'),
            callback: function(node) {
                ZikulaCategories.MenuAction(node, 'copy');
            }
        });
        ZikulaCategories.ContextMenu.addItem({
            label: Zikula.__('Activate'),
            condition: function() {
                return ZikulaCategories.ContextMenu.lastClick.closest('a').hasClass(Zikula.TreeSortable.trees.categoriesTree.config.nodeUnactive);
            },
            callback: function(node) {
                ZikulaCategories.MenuAction(node, 'activate');
            }
        });
        ZikulaCategories.ContextMenu.addItem({
            label: Zikula.__('Deactivate'),
            condition: function() {
                return !ZikulaCategories.ContextMenu.lastClick.closest('a').hasClass(Zikula.TreeSortable.trees.categoriesTree.config.nodeUnactive);
            },
            callback: function(node) {
                ZikulaCategories.MenuAction(node, 'deactivate');
            }
        });
        ZikulaCategories.ContextMenu.addItem({
            label: Zikula.__('Add category (after selected)'),
            callback: function(node) {
                ZikulaCategories.MenuAction(node, 'addafter');
            }
        });
        ZikulaCategories.ContextMenu.addItem({
            label: Zikula.__('Add subcategory (into selected)'),
            condition: function() {
                return !ZikulaCategories.ContextMenu.lastClick.closest('a').parent('li').hasClass('leaf');
            },
            callback: function(node){
                ZikulaCategories.MenuAction(node, 'addchild');
            }
        });
    };

    ZikulaCategories.Indicator = function() {
        return $('#ajax_indicator').length > 0 ? $('#ajax_indicator') : $('<img>').attr({ id: 'ajax_indicator', src: 'images/ajax/indicator_circle.gif' });
    };

    ZikulaCategories.DeleteMenuAction = function(node) {
        var subCats = ZikulaCategories.ContextMenu.lastClick.closest('a').parent('li').children('ul'),
            msg = $('<div>').attr({ id: 'dialogContent' }).append(
                $('p').text(Zikula.__('Do you really want to delete this category?'))
            ),
            buttons = [
                { name: 'Delete', value: 'Delete', label: Zikula.__('Delete'), 'class': 'btn btn-success' },
                { name: 'Cancel', value: 'Cancel', label: Zikula.__('Cancel'), 'class': 'btn btn-danger' },
            ];
        subCats = subCats ? subCats.childElements().size() : 0;
        if (subCats > 0) {
            var info = Zikula.__f('It contains %s direct sub-categories.', subCats)
                + ' '
                + Zikula.__("Please also choose what to do with this category's sub-categories.");
            msg.append($('p').attr({ class: 'alert alert-info' }).text(info));
            buttons = [
                { name: 'Delete', value: 'Delete', label: Zikula.__('Delete all sub-categories'), 'class': 'btn btn-success' },
                { name: 'Delete', value: 'DeleteAndMoveSubs', label: Zikula.__('Move all sub-categories'), 'class': 'btn btn-success', close: false },
                { name: 'Cancel', value: 'Cancel', label: Zikula.__('Cancel'), 'class': 'btn btn-danger' },
            ];
        }
        ZikulaCategories.DeleteDialog = new Zikula.UI.Dialog(
            msg,
            buttons,
            {title: Zikula.__('Confirmation prompt'), width: 500, callback: function(res) {
                switch (res.value) {
                    case 'Delete':
                        ZikulaCategories.MenuAction(node, 'delete');
                        ZikulaCategories.DeleteDialog.destroy();
                        break;
                    case 'DeleteAndMoveSubs':
                        if (!$('#subcat_move').length) {
                            $('#dialogContent').addClass('z-form');
                            ZikulaCategories.DeleteDialog.window.indicator.appear({ to: 0.7, duration: 0.2 });

                            $.ajax({
                                url: Routing.generate('zikulacategoriesmodule_ajax_deletedialog'),
                                data: {
                                    cid: Zikula.TreeSortable.trees.categoriesTree.getNodeId(node.parent('li'))
                                }
                            }).success(function(result) {
                                var data = result.data;

                                var subcat_move = data.result;
                                $('#dialogContent').append(subcat_move);
                                ZikulaCategories.DeleteDialog.container.morph('height: 250px');
                                ZikulaCategories.DeleteDialog.window.indicator.fade({ duration: 0.2 });
                            }).error(function(result) {
                                Zikula.showajaxerror(result.status + ': ' + result.statusText);
                            });
                        } else {
                            var parent = $('#category_parent_id_').val();
                            if (parent) {
                                ZikulaCategories.MenuAction(node, 'deleteandmovesubs', parent);
                                ZikulaCategories.DeleteDialog.destroy();
                            }
                        }
                        break;
                    default:
                        ZikulaCategories.DeleteDialog.destroy();
                }
            }}
        );
        ZikulaCategories.DeleteDialog.open()
        ZikulaCategories.DeleteDialog.container.children('button[name=Cancel]').focus();
    };

    ZikulaCategories.MenuAction = function(node, action, extrainfo) {
        if (!['edit', 'delete', 'deleteandmovesubs', 'copy', 'activate', 'deactivate', 'addafter', 'addchild'].include(action)) {
            return false;
        }
        node.append({ after: ZikulaCategories.Indicator() });
        var pars = {
            cid: Zikula.TreeSortable.trees.categoriesTree.getNodeId(node.parent('li'))
        };
        switch (action) {
            case 'edit':
                pars.mode = 'edit';
                break;
            case 'deleteandmovesubs':
                pars.parent = extrainfo;
                break;
            case 'copy':
                pars.parent = Zikula.TreeSortable.trees.categoriesTree.getNodeId(node.parent('li').parent('li'));
                break;
            case 'addafter':
                pars.mode = 'new';
                pars.parent = Zikula.TreeSortable.trees.categoriesTree.getNodeId(node.parent('li').parent('li'));
                action = 'edit';
                break;
            case 'addchild':
                pars.mode = 'new';
                pars.parent = Zikula.TreeSortable.trees.categoriesTree.getNodeId(node.parent('li'));
                action = 'edit';
                break;
        }

        $.ajax({
            url: Routing.generate('zikulacategoriesmodule_ajax_' + action),
            data: pars
        }).success(function(result) {
            var data = result.data;

            ZikulaCategories.MenuActionCallback(data);
        }).error(function(result) {
            Zikula.showajaxerror(result.status + ': ' + result.statusText);
        });

        return true;
    };

    ZikulaCategories.MenuActionCallback = function(data) {
        var node = $('#' + Zikula.TreeSortable.trees.categoriesTree.config.nodePrefix + data.cid);

        switch (data.action) {
            case 'delete':
                Droppables.remove(node);
                node.find('li').each(function(index) {
                    Droppables.remove($(this));
                });
                Effect.SwitchOff(node, {
                    afterFinish: function() {
                        node.remove();
                    }
                });
                treeElem.redraw();
                break;
            case 'deleteandmovesubs':
                Droppables.remove(node);
                node.find('li').each(function(index) {
                    Droppables.remove($(this));
                });
                Effect.SwitchOff(node,{
                    afterFinish: function() {
                        node.remove();
                    }
                });
                var parent = Zikula.TreeSortable.trees.categoriesTree.config.nodePrefix + data.parent;
                $('#' + parent).replaceWith(data.node);
                ZikulaCategories.ReinitTreeNode($(parent), data);
                // http://www.jstree.com/api/#/?q=%28&f=delete_node%28obj%29
                break;
            case 'activate':
                node.children('a').removeClass(Zikula.TreeSortable.trees.categoriesTree.config.nodeUnactive);
                // http://www.jstree.com/api/#/?q=%28&f=enable_node%28obj%29
                break;
            case 'deactivate':
                node.children('a').addClass(Zikula.TreeSortable.trees.categoriesTree.config.nodeUnactive);
                // http://www.jstree.com/api/#/?q=%28&f=disable_node%28obj%29
                break;
            case 'copy':
                var newNode = Zikula.TreeSortable.trees.categoriesTree.config.nodePrefix + data.copycid;
                $('#' + newNode).replaceWith(data.node);
                ZikulaCategories.ReinitTreeNode($(newNode), data);
                // http://www.jstree.com/api/#/?q=%28&f=copy%28obj%29
                // http://www.jstree.com/api/#/?q=%28&f=copy_node%28obj,%20par%20%5B,%20pos,%20callback,%20is_loaded%5D%29
                break;
            case 'edit':
                $(document.body).append(data.result);
                ZikulaCategories.OpenForm(data, ZikulaCategories.EditNode);
                break;
            case 'add':
                $(document.body).append(data.result);
                ZikulaCategories.OpenForm(data, ZikulaCategories.AddNode);
                // http://www.jstree.com/api/#/?q=%28&f=create_node%28%5Bobj,%20node,%20pos,%20callback,%20is_loaded%5D%29
                break;
        }

        return true;
    };

    ZikulaCategories.OpenForm = function(data, callback) {
        if (ZikulaCategories.Form) {
            ZikulaCategories.Form.destroy();
        }
        ZikulaCategories.Form = new Zikula.UI.FormDialog($('#categories_ajax_form_container'), callback, {
            title: $('#categories_ajax_form_container').title, 
            width: 700, 
            afterOpen: ZikulaCategories.InitEditView,
            buttons: [
                { label: Zikula.__('Submit'), type: 'submit', name: 'submit', value: 'submit', 'class': 'btn btn-success', close: false },
                { label: Zikula.__('Cancel'), type: 'submit', name: 'cancel', value: false, 'class': 'btn btn-danger', close: true }
            ]
        });

        return ZikulaCategories.Form.open();
    };

    ZikulaCategories.CloseForm = function() {
        ZikulaCategories.Form.destroy();
        ZikulaCategories.Form = null;
    };

    ZikulaCategories.UpdateForm = function(data) {
        $('#categories_ajax_form_container').replaceWith(data);
        ZikulaCategories.Form.window.indicator.fade({ duration: 0.2 });
        $('#categories_ajax_form_container').show();
        ZikulaCategories.InitEditView();
    };

    ZikulaCategories.EditNode = function(res) {
        if (!res || (res.hasOwnProperty('cancel') && res.cancel === false)) {
            ZikulaCategories.CloseForm();
            return false;
        }
        ZikulaCategories.Form.window.indicator.appear({ to: 0.7, duration: 0.2 });
        var pars = ZikulaCategories.Form.serialize(true);
        pars.mode = 'edit';
        new Zikula.Ajax.Request(Routing.generate('zikulacategoriesmodule_ajax_save'), {
            parameters: pars,
            onComplete: function(req) {
                var data = req.getData();
                if (!req.isSuccess() || data.validationErrors) {
                    Zikula.showajaxerror(req.getMessage());
                    if (data && data.validationErrors) {
                        ZikulaCategories.UpdateForm(data.result);
                    } else {
                        ZikulaCategories.CloseForm();
                    }
                } else {
                    var nodeId = Zikula.TreeSortable.trees.categoriesTree.config.nodePrefix + data.cid;
                    $('#' + nodeId).replaceWith(data.node);
                    ZikulaCategories.ReinitTreeNode($('#' + nodeId), data);
                    ZikulaCategories.CloseForm();
                }
            }
        });
        return true;
    };

    ZikulaCategories.AddNode = function(res) {
        if (!res || (res.hasOwnProperty('cancel') && res.cancel === false)) {
            ZikulaCategories.CloseForm();
            return false;
        }
        ZikulaCategories.Form.window.indicator.appear({ to: 0.7, duration: 0.2 });
        var pars = ZikulaCategories.Form.serialize(true);
        pars.mode = 'new';
        new Zikula.Ajax.Request(Routing.generate('zikulacategoriesmodule_ajax_save'), {
            parameters: pars,
            onComplete: function(req) {
                var data = req.getData();
                if (!req.isSuccess() || data.validationErrors) {
                    Zikula.showajaxerror(req.getMessage());
                    if (data && data.validationErrors) {
                        ZikulaCategories.UpdateForm(data.result);
                    } else {
                        ZikulaCategories.CloseForm();
                    }
                } else {
                    var relNode = $('#' + Zikula.TreeSortable.trees.categoriesTree.config.nodePrefix + data.parent),
                        newParent = relNode.children('ul');
                    if (!newParent) {
                        newParent = $('<ul>').attr({ class: 'tree' });
                        relNode.append(newParent);
                    }
                    newParent.append(data.node);
                    var node = $('#' + Zikula.TreeSortable.trees.categoriesTree.config.nodePrefix + data.cid);
                    ZikulaCategories.ReinitTreeNode(node, data);
                    ZikulaCategories.CloseForm();
                }
            }
        });

        return true;
    };

    ZikulaCategories.ReinitTreeNode = function(node, data) {
        Zikula.TreeSortable.trees.categoriesTree.initNode(node);
        var subNodes = node.find('li');
        if (subNodes.length > 0) {
            subNodes.each(Zikula.TreeSortable.trees.categoriesTree.initNode.bind(Zikula.TreeSortable.trees.categoriesTree));
        }
        treeElem.redraw();

        if (data.leafstatus) {
            if (data.leafstatus.leaf) {
                Zikula.TreeSortable.trees.categoriesTree.config.disabledForDrop = Zikula.TreeSortable.trees.categoriesTree.config.disabledForDrop.concat(data.leafstatus.leaf);
            }
            if (data.leafstatus.noleaf) {
                Zikula.TreeSortable.trees.categoriesTree.config.disabledForDrop = [].without.apply(Zikula.TreeSortable.trees.categoriesTree.config.disabledForDrop, data.leafstatus.noleaf);
            }
        }
        Zikula.UI.Tooltips(node.find('a'));
    };

    ZikulaCategories.Resequence = function(node, params, data) {
        // do not allow inserts on root level
        if (node.parent('li') === undefined) {
            return false;
        }
        var pars = {
            'data': data
        };
        node.append({ bottom: ZikulaCategories.Indicator() });

        $.ajax({
            url: Routing.generate('zikulacategoriesmodule_ajax_resequence'),
            data: pars
        }).success(function(result) {
            var data = result.data;
        }).error(function(result) {
            Zikula.showajaxerror(result.status + ': ' + result.statusText);

            /** TODO */
            return Zikula.TreeSortable.categoriesTree.revertInsertion();
        });

        return true;
    };
})(jQuery);
