// Copyright Zikula Foundation 2009 - license GNU/LGPLv3 (or at your option, any later version).

var ZikulaCategories = {};

( function($) {

    var treeElem;
    var lastContextMenuClickEventTargetLink;

    function getCategoryContextMenuActions(node) {
        var actions = {
            editItem: {
                label: /*Zikula.__(*/'Edit'/*)*/,
                action: function (obj) {
                    performCategoryContextMenuAction(node, 'edit');
                },
                icon: 'fa fa-edit'
            },
            deleteItem: {
                label: /*Zikula.__(*/'Delete'/*)*/,
                action: function (obj) {
                    //this.remove(obj);
                    getCategoryDeleteMenuAction(node);
                },
                icon: 'fa fa-remove'
            },
            copyItem: {
                label: /*Zikula.__(*/'Copy'/*)*/,
                action: function (obj) {
                    performCategoryContextMenuAction(node, 'copy');
                },
                icon: 'fa fa-copy'
            },
            activateItem: {
                label: /*Zikula.__(*/'Activate'/*)*/,
                action: function (obj) {
                    performCategoryContextMenuAction(node, 'activate');
                },
                icon: 'fa fa-check-circle'
            },
            deactivateItem: {
                label: /*Zikula.__(*/'Deactivate'/*)*/,
                action: function (obj) {
                    performCategoryContextMenuAction(node, 'deactivate');
                },
                icon: 'fa fa-times-circle'
            },
            addItemAfter: {
                label: /*Zikula.__(*/'Add category (after selected)'/*)*/,
                action: function (obj) {
                    performCategoryContextMenuAction(node, 'addafter');
                },
                icon: 'fa fa-level-up fa-rotate-90'
            },
            addItemInto: {
                label: /*Zikula.__(*/'Add subcategory (into selected)'/*)*/,
                action: function (obj) {
                    performCategoryContextMenuAction(node, 'addchild');
                },
                icon: 'fa fa-level-up fa-rotate-90'
            }
        };

        // remove unwanted actions dynamically
        if (lastContextMenuClickEventTargetLink !== null && typeof lastContextMenuClickEventTargetLink != 'undefined') {
            if (lastContextMenuClickEventTargetLink.hasClass('z-tree-unactive')) {
                delete actions.deactivateItem;
            } else {
                delete actions.activateItem;
            }
            if (lastContextMenuClickEventTargetLink.parent('li').hasClass('leaf')/*
                || lastContextMenuClickEventTargetLink.parent('li').hasClass('jstree-leaf')*/) {
                delete actions.addItemInto;
            }
        }

        return actions;
    };

    function categoriesAjaxIndicator() {
        return '<img id="ajax_indicator" src="images/ajax/indicator_circle.gif" alt="ajax" />';
    };

    function performCategoryContextMenuAction(node, action, extrainfo) {
        var allowedActions = ['edit', 'delete', 'deleteandmovesubs', 'copy', 'activate', 'deactivate', 'addafter', 'addchild'];
        if (!$.inArray(action, allowedActions) == -1) {
            return false;
        }
        //$(node).append(categoriesAjaxIndicator()); // TODO

        var pars = {
            cid: $(node).attr('id').replace('node_', '')
        };
        switch (action) {
            case 'edit':
                pars.mode = 'edit';
                break;
            case 'deleteandmovesubs':
                pars.parent = extrainfo;
                break;
            case 'copy':
                pars.parent = $(node).parent('li').attr('id').replace('node_', '');
                break;
            case 'addafter':
                pars.mode = 'new';
                pars.parent = $(node).parent('li').attr('id').replace('node_', '');
                action = 'edit';
                break;
            case 'addchild':
                pars.mode = 'new';
                pars.parent = $(node).attr('id').replace('node_', '');
                action = 'edit';
                break;
        }

        $.ajax({
            url: Routing.generate('zikulacategoriesmodule_ajax_' + action),
            data: pars
        }).success(function(result) {
            performCategoryContextMenuActionCallback(result.data);
        }).error(function(result) {
            Zikula.showajaxerror(result.status + ': ' + result.statusText);
        });

        return true;
    };

    function getCategoryDeleteMenuAction(node) {
        var subCats = lastContextMenuClickEventTargetLink.parent('li').children('ul'),
            msg = $('<div>').attr({ id: 'dialogContent' }).append(
                $('p').text(/*Zikula.__(*/'Do you really want to delete this category?'/*)*/)
            ),
            buttons = [
                { name: 'Delete', value: 'Delete', label: /*Zikula.__(*/'Delete'/*)*/, 'class': 'btn btn-success' },
                { name: 'Cancel', value: 'Cancel', label: /*Zikula.__(*/'Cancel'/*)*/, 'class': 'btn btn-danger' },
            ];
        subCats = subCats ? subCats.children().length : 0;
        if (subCats > 0) {
            var info = Zikula.__f('It contains %s direct sub-categories.', subCats)
                + ' '
                + /*Zikula.__(*/"Please also choose what to do with this category's sub-categories."/*)*/;
            msg.append($('p').attr({ class: 'alert alert-info' }).text(info));
            buttons = [
                { name: 'Delete', value: 'Delete', label: /*Zikula.__(*/'Delete all sub-categories'/*)*/, 'class': 'btn btn-success' },
                { name: 'Delete', value: 'DeleteAndMoveSubs', label: /*Zikula.__(*/'Move all sub-categories'/*)*/, 'class': 'btn btn-success', close: false },
                { name: 'Cancel', value: 'Cancel', label: /*Zikula.__(*/'Cancel'/*)*/, 'class': 'btn btn-danger' },
            ];
        }
        ZikulaCategories.DeleteDialog = new Zikula.UI.Dialog(
            msg,
            buttons,
            {title: /*Zikula.__(*/'Confirmation prompt'/*)*/, width: 500, callback: function(res) {
                switch (res.value) {
                    case 'Delete':
                        performCategoryContextMenuAction(node, 'delete');
                        ZikulaCategories.DeleteDialog.destroy();
                        break;
                    case 'DeleteAndMoveSubs':
                        if (!$('#subcat_move').length) {
                            $('#dialogContent').addClass('z-form');
                            ZikulaCategories.DeleteDialog.window.indicator.appear({ to: 0.7, duration: 0.2 });

                            $.ajax({
                                url: Routing.generate('zikulacategoriesmodule_ajax_deletedialog'),
                                data: {
                                    cid: $(node).attr('id').replace('node_', '')
                                }
                            }).success(function(result) {
                                var subcat_move = result.data.result;
                                $('#dialogContent').append(subcat_move);
                                ZikulaCategories.DeleteDialog.container.morph('height: 250px');
                                ZikulaCategories.DeleteDialog.window.indicator.fade({ duration: 0.2 });
                            }).error(function(result) {
                                Zikula.showajaxerror(result.status + ': ' + result.statusText);
                            });
                        } else {
                            var parent = $('#category_parent_id_').val();
                            if (parent) {
                                performCategoryContextMenuAction(node, 'deleteandmovesubs', parent);
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

    $(document).ready(function() {
        treeElem = $('#categoryTreeContainer .treewraper');

        // Tree instantiation
        treeElem.jstree({
            'core': {
                'multiple': false,
                'check_callback': true
            },
            'contextmenu': {
                'items': getCategoryContextMenuActions
            },
            'dnd': {
                'copy': false,
                'is_draggable': function(node) {
                    // disable drag and drop for root category
                    return $(node).attr('id') != 'node_1' ? true : false;
                },
                'inside_pos': 'last'
            },
            'state': {
                'key': 'categoryTree'
            },
            'plugins': [ 'contextmenu', 'dnd', 'search', 'state', 'types' ],
            'types': {
                '#': {
                    // prevent unwanted drops on root
                    'max_children': 1
                },
                'default': {
                    'icon': 'fa fa-folder'
                }
            },
        });

        treeElem.find('li.leaf i.jstree-icon.jstree-themeicon')
                .removeClass('fa-folder').addClass('fa-leaf')
                .hide();
        treeElem.on('open_node.jstree', function(e, data) {
            if (data.instance.is_leaf(data.node)) {
                return;
            }
            $('#' + data.node.id).find('i.jstree-icon.jstree-themeicon').first()
                .removeClass('fa-folder').addClass('fa-folder-open');
        });
        treeElem.on('close_node.jstree', function(e, data) {
            if (data.instance.is_leaf(data.node)) {
                return;
            }
            $('#' + data.node.id).find('i.jstree-icon.jstree-themeicon').first()
                .removeClass('fa-folder-open').addClass('fa-folder');
        });

        // allow redirecting if a linked has been clicked
        treeElem.find('ul').on('click', 'li.jstree-node a', function(e) {
            treeElem.jstree('save_state');
            document.location.href = $(this).attr('href');
        });

        // Search plugin
        var searchStartDelay = false;
        $('#categoryTreeSearchTerm').keyup(function() {
            if (searchStartDelay) {
                clearTimeout(searchStartDelay);
            }
            searchStartDelay = setTimeout(function() {
                var v = $('#categoryTreeSearchTerm').val();
                treeElem.jstree(true).search(v);
            }, 250);
        });

        // Context menu
        treeElem.on('show_contextmenu.jstree', function(event, node, x, y) {
            lastContextMenuClickEventTargetLink = $(event.target).closest('a');
            if (!lastContextMenuClickEventTargetLink.length) {
                event.stopPropagation();
            }
        });

        // Drag & drop
        /**
         * TODO
         *   - prevent drop on leafs marked as subcat
         *          http://www.jstree.com/api/#/?q=%28&f=is_leaf%28obj%29
         *   - handle events (ajax)
         */
//         tree.on('move_node.jstree', function (event, data) {
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

        // Tooltips
        treeElem.on('hover_node.jstree', function (e, data) 
        {
            $('#' + data.node.id + '_anchor').tooltip({
                placement: 'right',
                html: true
            });
        })
    });

    /** TODO */
    performCategoryContextMenuActionCallback = function(data) {
        var node = $('#node_' + data.cid);

        switch (data.action) {
            case 'delete':
                treeElem.jsTree(true).delete_node(node);
                //treeElem.redraw();
                break;
            case 'deleteandmovesubs':
                treeElem.jsTree(true).delete_node(node);

                var parentNodeId = 'node_' + data.parent;
                $('#' + parentNodeId).replaceWith(data.node);
                ZikulaCategories.ReinitTreeNode($(parentNodeId), data);
                break;
            case 'activate':
                node.children('a').removeClass('z-tree-unactive');
                treeElem.jsTree(true).enable_node(node);
                break;
            case 'deactivate':
                node.children('a').addClass('z-tree-unactive');
                treeElem.jsTree(true).disable_node(node);
                break;
            case 'copy':
                var newNode = 'node_' + data.copycid;
                $('#' + newNode).replaceWith(data.node);
                ZikulaCategories.ReinitTreeNode($(newNode), data);
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

    /** TODO */
    ZikulaCategories.OpenForm = function(data, callback) {
        if (ZikulaCategories.Form) {
            ZikulaCategories.Form.destroy();
        }
        ZikulaCategories.Form = new Zikula.UI.FormDialog($('#categories_ajax_form_container'), callback, {
            title: $('#categories_ajax_form_container').title, 
            width: 700, 
            afterOpen: ZikulaCategories.InitEditView,
            buttons: [
                { label: /*Zikula.__(*/'Submit'/*)*/, type: 'submit', name: 'submit', value: 'submit', 'class': 'btn btn-success', close: false },
                { label: /*Zikula.__(*/'Cancel'/*)*/, type: 'submit', name: 'cancel', value: false, 'class': 'btn btn-danger', close: true }
            ]
        });

        return ZikulaCategories.Form.open();
    };

    /** TODO */
    ZikulaCategories.CloseForm = function() {
        ZikulaCategories.Form.destroy();
        ZikulaCategories.Form = null;
    };

    /** TODO */
    ZikulaCategories.UpdateForm = function(data) {
        $('#categories_ajax_form_container').replaceWith(data);
        ZikulaCategories.Form.window.indicator.fade({ duration: 0.2 });
        $('#categories_ajax_form_container').show();
        ZikulaCategories.InitEditView();
    };

    /** TODO */
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
                    var nodeId = 'node_' + data.cid;
                    $('#' + nodeId).replaceWith(data.node);
                    ZikulaCategories.ReinitTreeNode($('#' + nodeId), data);
                    ZikulaCategories.CloseForm();
                }
            }
        });
        return true;
    };

    /** TODO */
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
                    var relNode = $('#node_' + data.parent),
                        newParent = relNode.children('ul');
                    if (!newParent) {
                        newParent = $('<ul>').attr({ class: 'tree' });
                        relNode.append(newParent);
                    }
                    newParent.append(data.node);
                    var node = $('#node_' + data.cid);
                    ZikulaCategories.ReinitTreeNode(node, data);
                    ZikulaCategories.CloseForm();
                }
            }
        });

        return true;
    };

    /** TODO */
    ZikulaCategories.ReinitTreeNode = function(node, data) {
        Zikula.TreeSortable.trees.categoriesTree.initNode(node);
        var subNodes = node.find('li');
        if (subNodes.length > 0) {
            subNodes.each(Zikula.TreeSortable.trees.categoriesTree.initNode.bind(Zikula.TreeSortable.trees.categoriesTree));
        }
        treeElem.redraw();
        // http://www.jstree.com/api/#/?q=%28&f=rename_node%28obj,%20val%29 (needed?)

        if (data.leafstatus) {
            if (data.leafstatus.leaf) {
                Zikula.TreeSortable.trees.categoriesTree.config.disabledForDrop = Zikula.TreeSortable.trees.categoriesTree.config.disabledForDrop.concat(data.leafstatus.leaf);
            }
            if (data.leafstatus.noleaf) {
                Zikula.TreeSortable.trees.categoriesTree.config.disabledForDrop = [].without.apply(Zikula.TreeSortable.trees.categoriesTree.config.disabledForDrop, data.leafstatus.noleaf);
            }
        }
        Zikula.UI.Tooltips($(node).find('a'));
    };

    /** TODO */
    ZikulaCategories.Resequence = function(node, params, data) {
        // do not allow inserts on root level
        if ($(node).parent('li') === undefined) {
            return false;
        }
        var pars = {
            'data': data
        };
        //$(node).append(categoriesAjaxIndicator()); // TODO

        $.ajax({
            url: Routing.generate('zikulacategoriesmodule_ajax_resequence'),
            data: pars
        }).success(function(result) {
            var data = result.data;
        }).error(function(result) {
            Zikula.showajaxerror(result.status + ': ' + result.statusText);
        });

        return true;
    };
})(jQuery);
