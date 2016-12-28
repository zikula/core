// Copyright Zikula Foundation, licensed MIT.

( function($) {

    var treeElem;

    function getCategoryContextMenuActions(node) {
        if (node.id == 'node_1') {
            return {};
        }
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
                icon: 'fa fa-check-square-o'
            },
            deactivateItem: {
                label: /*Zikula.__(*/'Deactivate'/*)*/,
                action: function (obj) {
                    performCategoryContextMenuAction(node, 'deactivate');
                },
                icon: 'fa fa-square-o'
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
                icon: 'fa fa-long-arrow-right'
            }
        };

        var currentNode = treeElem.jstree('get_node', node, true);
        // disable unwanted context menu items
        if (currentNode.closest('li').hasClass('z-tree-unactive') || currentNode.hasClass('z-tree-unactive')) {
            actions.deactivateItem._disabled = true;
        } else {
            actions.activateItem._disabled = true;
        }

        return actions;
    }

    function performCategoryContextMenuAction(node, action, extrainfo) {
        var allowedActions = ['edit', 'delete', 'deleteandmovesubs', 'copy', 'activate', 'deactivate', 'addafter', 'addchild'];
        var parentId;
        if (!$.inArray(action, allowedActions) == -1) {
            return false;
        }

        var nodeId = $(node).attr('id');
        // append spinner
        $('#' + nodeId).find('a').first().after('<i id="temp-spinner" class="fa fa-spinner fa-spin fa-lg text-primary"></i>');

        var pars = {
            cid: nodeId.replace('node_', '')
        };
        if (nodeId == 'node_1') {
            // do not allow editing of root category
            $('#temp-spinner').remove();
            return false;
        }
        switch (action) {
            case 'edit':
                pars.mode = 'edit';
                break;
            case 'deleteandmovesubs':
                pars.parent = extrainfo;
                break;
            case 'copy':
                parentId = treeElem.jstree('get_parent', node);
                pars.parent = parentId.replace('node_', '');
                break;
            case 'addafter':
                pars.mode = 'new';
                parentId = treeElem.jstree('get_parent', node);
                pars.parent = parentId.replace('node_', '');
                action = 'edit';
                break;
            case 'addchild':
                pars.mode = 'new';
                pars.parent = pars.cid;
                action = 'edit';
                break;
        }

        $.ajax({
            type: 'POST',
            url: Routing.generate('zikulacategoriesmodule_ajax_' + action),
            data: pars
        }).success(function(result) {
            performCategoryContextMenuActionCallback(result.data);
        }).error(function(result) {
            alert(result.status + ': ' + result.statusText);
        }).always(function() {
            $('#temp-spinner').remove();
            redrawTree(treeElem);
        });

        return true;
    }

    function performCategoryContextMenuActionCallback(data) {
        if (null == data) {
            return;
        }
        var originalNode = $('#node_' + data.cid);
        var parentNode = $('#node_' + data.parent);

        switch (data.action) {
            case 'delete':
                treeElem.jstree('delete_node', originalNode);
                break;
            case 'deleteandmovesubs':
                var workingNode = treeElem.jstree('get_node', originalNode.attr('id'));
                treeElem.jstree('copy_node', workingNode.children, parentNode, 'last'); // use copy here to avoid move_node event
                treeElem.jstree('delete_node', originalNode);
                reinitTreeNode(parentNode, data);
                break;
            case 'activate':
                originalNode.removeClass('z-tree-unactive');
                break;
            case 'deactivate':
                originalNode.addClass('z-tree-unactive');
                break;
            case 'copy':
                var indexOfOriginalNode = originalNode.parent().children().index(originalNode[0]);
                treeElem.jstree(true).create_node(parentNode, data.node, indexOfOriginalNode);
                break;
            case 'edit':
            case 'add':
                $('#categoryEditModal').find('.modal-body').html(data.result);
                openCategoryEditForm(data, function (event) {
                    event.preventDefault();
                    var mode = data.action;
                    var buttonValue = $(this).val();

                    if (buttonValue == 'Cancel') {
                        closeCategoryEditForm();
                        return false;
                    }

                    // fetch each input and hidden field and store the value to POST
                    var pars = {};
                    $.each($(':input, :hidden').serializeArray(), function(i, field) {
                        pars[field.name] = field.value;
                    });
                    pars.mode = (mode == 'edit') ? 'edit' : 'new';
                    pars.attribute_name = [];
                    pars.attribute_value = [];
                    // special handling of potential array values
                    $('input[name="attribute_name[]"]').each(function() {
                        if ($(this).val() == '') {
                            return true;
                        }
                        pars.attribute_name.push($(this).val());
                    });
                    $('input[name="attribute_value[]"]').each(function() {
                        if ($(this).val() == "") {
                            return true;
                        }
                        pars.attribute_value.push($(this).val());
                    });

                    $.ajax({
                        type: 'POST',
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
                                // delete the existing node and replace with edited version
                                var editedNode = treeElem.jstree('get_node', 'node_' + data.cid);
                                treeElem.jstree('delete_node', editedNode);
                            }
                            var parentLi = $('#node_' + data.parent),
                                parentUl = parentLi.children('ul');
                            if (!parentUl) {
                                parentUl = $('<ul>').attr({ 'class': 'tree' });
                                parentLi.append(parentUl);
                            }
                            var newNode = treeElem.jstree(true).create_node(parentUl, data.node[0]);
                            var node = $('#' + newNode);
                            reinitTreeNode(node, data);
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
    }

    function getCategoryDeleteMenuAction(node) {
        var subCats = node.children.length;
        if (subCats > 0) {
            //var info = Zikula.__f('It contains %s direct sub-categories.', subCats)
            var info = 'It contains ' + subCats + ' direct sub-categories.'
                + ' '
                + /*Zikula.__(*/"Please choose what to do with this category's sub-categories."/*)*/;
            $('#deleteWithSubCatInfo').addClass('alert alert-warning').text(info);
        } else {
            $('#deleteWithSubCatInfo').removeClass('alert alert-warning').text('');
        }
        var deleteModal = $('#categoryDeleteModal');

        if (subCats > 0) {
            deleteModal.find('#cat_delete').addClass('hidden');
            deleteModal.find('#cat_delete_all').removeClass('hidden');
            deleteModal.find('#cat_delete_move').removeClass('hidden');
        }
        $('#subcat_move').remove();

        deleteModal.find('.modal-footer button').one('click', function(event) {
            event.preventDefault();
            var buttonValue = $(this).val();

            switch (buttonValue) {
                case 'Delete':
                    performCategoryContextMenuAction(node, 'delete');
                    deleteModal.modal('hide');
                    break;
                case 'DeleteAndMoveSubs':
                    if (!$('#subcat_move').length) {
                        // present dialog to determine new parent
                        $(this).prepend('<i id="button-spinner" class="fa fa-gear fa-spin fa-lg text-danger"></i> ');
                        $.ajax({
                            type: 'POST',
                            url: Routing.generate('zikulacategoriesmodule_ajax_deletedialog'),
                            data: {
                                cid: $(node).attr('id').replace('node_', '')
                            }
                        }).success(function(result) {
                            var subcat_move = result.data.result;
                            deleteModal.find('.modal-body').append(subcat_move);
                            deleteModal.find('#cat_delete_move').addClass('hidden');
                            deleteModal.find('#cat_delete_move_action').removeClass('hidden');
                        }).error(function(result) {
                            alert(result.status + ': ' + result.statusText);
                        }).always(function() {
                            $('#button-spinner').remove();
                        });
                    } else {
                        // utilize new parent to perform delete and move operation
                        var parent = $('#category_parent_id_').val();
                        if (parent) {
                            performCategoryContextMenuAction(node, 'deleteandmovesubs', parent);
                            deleteModal.modal('hide');
                        }
                    }
                    break;
                default:
                    deleteModal.modal('hide');
            }
        });

        deleteModal.modal();
        deleteModal.on('hidden.bs.modal', function (e) {
            // reset modal to initial state
            deleteModal.find('#cat_delete').removeClass('hidden');
            deleteModal.find('#cat_delete_all').addClass('hidden');
            deleteModal.find('#cat_delete_move').addClass('hidden');
            deleteModal.find('#cat_delete_move_action').addClass('hidden');
            $('#button-spinner').remove();
            $('#subcat_move').remove();
        });
        deleteModal.find('.modal-footer button[value=Cancel]').focus();
    }

    function openCategoryEditForm(data, callback) {
        $('#categories_ajax_form_container').show();
        ZikulaCategories.init();
        var editModal = $('#categoryEditModal');
        editModal.find('.modal-footer button').unbind('click').click(callback);

        editModal.modal();
        editModal.find('.modal-footer button[value=Cancel]').focus();
    }

    function updateCategoryEditForm(data) {
        $('#categories_ajax_form_container').replaceWith(data).show();
    }

    function closeCategoryEditForm() {
        $('#categoryEditModal').modal('hide');
    }

    var nodesDisabledForDrop = [];

    function reinitTreeNode(node, data) {
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
    }

    function redrawTree(treeElem) {
        treeElem
            // hide folder icons for leaf nodes
            .find('a.jstree-anchor.leaf > i.fa-folder').hide().end()
            // use folder-open icon for already open nodes
            .find('li.jstree-open > a.z-tree-fixedparent > i.fa-folder').removeClass('fa-folder').addClass('fa-folder-open');
    }

    function resequenceCategories(event, data) {
        var node = data.node;

        // do not allow inserts on forbidden leaf nodes
        if ($.inArray($(node).attr('id'), nodesDisabledForDrop) > -1) {
            return false;
        }

        var elements = [];
        // iterate all the nodes and prepare for POST
        $.each(treeElem.jstree('get_json', '#', {'flat':true}), function (index, node) {
            elements.push({
                id: node.id.replace('node_', ''),
                name: node.text, // present for debugging purposes
                lineno: index,
                parent: node.parent.replace('node_', '') != '#' ? node.parent.replace('node_', '') : null
            });
        });

        $.ajax({
            type: 'POST',
            url: Routing.generate('zikulacategoriesmodule_ajax_resequence'),
            data: {
                tree: elements
            }
        }).success(function(result) {
            var data = result.data;
        }).error(function(result) {
            alert(result.status + ': ' + result.statusText);
        });

        return true;
    }

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
                    return ($(node).attr('id') != 'node_1');
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

        treeElem.on('ready.jstree redraw.jstree create_node.jstree changed.jstree delete_node.jstree', function(e) {
            redrawTree(treeElem);
        });
        treeElem.on('open_node.jstree', function(e, data) {
            if (data.instance.is_leaf(data.node)) {
                return;
            }
            $('#' + data.node.id)
                // hide the folder icons
                .find('a.jstree-anchor.leaf > i.fa-folder').hide().end()
                // replace folder with folder-open
                .find('i.jstree-icon.jstree-themeicon').first()
                    .removeClass('fa-folder').addClass('fa-folder-open');
        });
        treeElem.on('close_node.jstree', function(e, data) {
            if (data.instance.is_leaf(data.node)) {
                return;
            }
            $('#' + data.node.id).find('i.jstree-icon.jstree-themeicon').first()
                .removeClass('fa-folder-open').addClass('fa-folder');
        });

        // allow redirecting if a link has been clicked
        treeElem.find('ul').on('click', 'li.jstree-node a', function(e) {
            treeElem.jstree('save_state');
            if ($(this).attr('id') == 'node_1_anchor') {
                return true;
            }
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
            var anchor = $('#' + data.node.id + '_anchor');
            anchor.tooltip({
                placement: 'right',
                html: true
            });
            anchor.tooltip('show');
        });
    });
})(jQuery);
