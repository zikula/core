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
            if (lastContextMenuClickEventTargetLink.closest('li').hasClass('leaf')/*
                || lastContextMenuClickEventTargetLink.closest('li').hasClass('jstree-leaf')*/) {
                delete actions.addItemInto;
            }
        }

        return actions;
    };

    function performCategoryContextMenuAction(node, action, extrainfo) {
        var allowedActions = ['edit', 'delete', 'deleteandmovesubs', 'copy', 'activate', 'deactivate', 'addafter', 'addchild'];
        if (!$.inArray(action, allowedActions) == -1) {
            return false;
        }

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
                var parentId = treeElem.jstree('get_parent', node);
                pars.parent = parentId.replace('node_', '');
                break;
            case 'addafter':
                pars.mode = 'new';
                var parentId = treeElem.jstree('get_parent', node);
                pars.parent = parentId.replace('node_', '');
                action = 'edit';
                break;
            case 'addchild':
                pars.mode = 'new';
                var parentId = treeElem.jstree('get_parent', node);
                pars.parent = parentId.replace('node_', '');
                action = 'edit';
                break;
        }

        $.ajax({
            type: "POST",
            url: Routing.generate('zikulacategoriesmodule_ajax_' + action),
            data: pars
        }).success(function(result) {
            performCategoryContextMenuActionCallback(result.data);
        }).error(function(result) {
            alert(result.status + ': ' + result.statusText);
        });

        return true;
    };

    function performCategoryContextMenuActionCallback(data) {
        var node = $('#node_' + data.cid);

        switch (data.action) {
            case 'delete':
                treeElem.jsTree(true).delete_node(node);
                //treeElem.jstree(true).redraw();
                break;
            case 'deleteandmovesubs':
                treeElem.jsTree(true).delete_node(node);

                var parentNodeId = 'node_' + data.parent;
                $('#' + parentNodeId).replaceWith(data.node);
                reinitTreeNode($(parentNodeId), data);
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
                reinitTreeNode($(newNode), data);
                break;
            case 'edit':
            case 'add':
                $('#categoryEditModal .modal-body').html(data.result);
                openCategoryEditForm(data, function (event) {
                    event.preventDefault();
                    var mode = data.action;
                    var buttonValue = $(this).val();

                    if (buttonValue == 'Cancel') {
                        closeCategoryEditForm();
                        return false;
                    }

                    var pars = {};
                    $.each($(":input, :hidden").serializeArray(), function(i, field) {
                        pars[field.name] = field.value;
                    });
                    pars.mode = (mode == 'edit') ? 'edit' : 'new';

                    $.ajax({
                        type: "POST",
                        url: Routing.generate('zikulacategoriesmodule_ajax_save'),
                        data: pars
                    }).success(function(result) {
                        var data = result.data;

                        if (data.validationErrors) {
                            if (data && data.validationErrors) {
                                updateCategoryEditForm(data.result);
                            } else {
                                closeCategoryEditForm();
                            }
                        } else {
                            if (mode == 'edit') {
                                var nodeId = 'node_' + data.cid;
                                $('#' + nodeId).replaceWith(data.node);
                                reinitTreeNode($('#' + nodeId), data);
                            } else if (mode == 'add') {
                                var relNode = $('#node_' + data.parent),
                                    newParent = relNode.children('ul');
                                if (!newParent) {
                                    newParent = $('<ul>').attr({ class: 'tree' });
                                    relNode.append(newParent);
                                }
                                newParent.append(data.node);
                                var node = $('#node_' + data.cid);
                                reinitTreeNode(node, data);
                            }
                            closeCategoryEditForm();
                        }
                    }).error(function(result) {
                        alert(result.status + ': ' + result.statusText);
                    });

                    return true;
                });
                break;
        }

        return true;
    };

    function getCategoryDeleteMenuAction(node) {
        var subCats, msg;

        subCats = $(node).find('> ul > li').length;
        if (subCats > 0) {
            var info = Zikula.__f('It contains %s direct sub-categories.', subCats)
                + ' '
                + /*Zikula.__(*/"Please also choose what to do with this category's sub-categories."/*)*/;
            $('#deleteWithSubCatInfo').addClass('alert alert-info').text(info);
        } else {
            $('#deleteWithSubCatInfo').removeClass('alert alert-info').text('');
        }

        if (subCats > 0) {
            $('#categoryDeleteModal .modal-footer .leaf-node').hide();
            $('#categoryDeleteModal .modal-footer .parent-node').show();
        } else {
            $('#categoryDeleteModal .modal-footer .leaf-node').show();
            $('#categoryDeleteModal .modal-footer .parent-node').hide();
        }

        $('#categoryDeleteModal .modal-footer button').click(function(event) {
            event.preventDefault();
            var buttonValue = $(this).val();

            switch (buttonValue) {
                case 'Delete':
                    performCategoryContextMenuAction(node, 'delete');
                    $('#categoryDeleteModal').modal('hide');
                    break;
                case 'DeleteAndMoveSubs':
                    if (!$('#subcat_move').length) {
                        $.ajax({
                            type: "POST",
                            url: Routing.generate('zikulacategoriesmodule_ajax_deletedialog'),
                            data: {
                                cid: $(node).attr('id').replace('node_', '')
                            }
                        }).success(function(result) {
                            var subcat_move = result.data.result;
                            $('#categoryDeleteModal .modal-body').append(subcat_move);
                        }).error(function(result) {
                            alert(result.status + ': ' + result.statusText);
                        });
                    } else {
                        var parent = $('#category_parent_id_').val();
                        if (parent) {
                            performCategoryContextMenuAction(node, 'deleteandmovesubs', parent);
                            $('#categoryDeleteModal').modal('hide');
                        }
                    }
                    break;
                default:
                    $('#categoryDeleteModal').modal('hide');
            }
        });

        $('#categoryDeleteModal').modal();
        $('#categoryDeleteModal .modal-footer button[value=Cancel]').focus();
    };

    function openCategoryEditForm(data, callback) {
        $('#categories_ajax_form_container').show();
        $('#categoryEditModal .modal-footer button').unbind('click').click(callback);

        $('#categoryEditModal').modal();
        $('#categoryEditModal .modal-footer button[value=Cancel]').focus();
    };

    function updateCategoryEditForm(data) {
        $('#categories_ajax_form_container').replaceWith(data);
        $('#categories_ajax_form_container').show();
    };

    function closeCategoryEditForm() {
        $('#categoryEditModal').modal('hide');
    };

    var nodesDisabledForDrop = [];

    function reinitTreeNode(node, data) {
        treeElem.jstree(true).redraw();

        if (data.leafstatus) {
            if (data.leafstatus.leaf) {
                // add elements
                $.merge(nodesDisabledForDrop, data.leafstatus.leaf);
            }
            if (data.leafstatus.noleaf) {
                // remove elements
                nodesDisabledForDrop = $.grep(nodesDisabledForDrop, function(value) {
                    return $.inArray(value, data.leafstatus.noleaf) < 0;
                });
            }
        }
    };

    function resequenceCategories(event, data) {
        var node = data.node;
        var parentId = data.parent;
        var parentNode = treeElem.jstree('get_node', parentId, false);

        // do not allow inserts on root level
        if ($(node).parent('li') === undefined) {
            return false;
        }
        // do not allow inserts on forbidden leaf nodes
        if ($.inArray($(node).attr('id'), nodesDisabledForDrop) > -1) {
            return false;
        }

        var elements = [];
        $('#categoryTreeContainer li').each(function(index) {
            var elem = $(this);
            var catId = elem.attr('id').replace('node_', '');

            if (catId != '') {
                elements[catId] = {
                    lineno: index,
                    parent: $(parentNode).attr('id').replace('node_', '')
                };
            }
        });

        $.ajax({
            type: "POST",
            url: Routing.generate('zikulacategoriesmodule_ajax_resequence'),
            data: {
                'data': elements
            }
        }).success(function(result) {
            var data = result.data;
        }).error(function(result) {
            alert(result.status + ': ' + result.statusText);
        });

        return true;
    };

    $(document).ready(function() {
        treeElem = $('#categoryTreeContainer .treewraper');

        // Tree instantiation
        treeElem.jstree({
            'core': {
                'multiple': false,
                'check_callback': function(operation, node, node_parent, node_position, more) {
                    if (operation === 'move_node') {
                        return ($.inArray($(node).attr('id'), nodesDisabledForDrop) == -1);
                    }
                    return true; // allow all other operations
                }
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
            }
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
        treeElem.on('move_node.jstree', resequenceCategories);

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
        treeElem.on('hover_node.jstree', function(e, data) {
            $('#' + data.node.id + '_anchor').tooltip({
                placement: 'right',
                html: true
            });
        })
    });
})(jQuery);
