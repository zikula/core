( function($) {
    $(document).ready(function() {
        var treeElem = $('#tree_container');
        treeElem.jstree({
            "core" : {
                "animation" : 0,
                "check_callback" : true,
                "themes" : { "stripes" : true }
            },
            'contextmenu': {
                'items': getContextMenuActions
            },
            "types" : {
                "#" : {
                    "max_children" : 1,
                    "max_depth" : 4,
                    "valid_children" : ["root"]
                },
                "default" : {
                    "icon" : "fa fa-check-circle"
                }
            },
            "plugins" : [
                "contextmenu", "dnd", "state", "types", "wholerow"
            ]
        });

        function getContextMenuActions(node) {
            return {
                editItem: {
                    label: 'Edit',
                    action: function (obj) {
                        performContextMenuAction(node, 'edit');
                    },
                    icon: 'fa fa-edit'
                },
                deleteItem: {
                    label: 'Delete',
                    action: function (obj) {
                        getDeleteMenuAction(node);
                    },
                    icon: 'fa fa-remove'
                },
                copyItem: {
                    label: 'Copy',
                    action: function (obj) {
                        performContextMenuAction(node, 'copy');
                    },
                    icon: 'fa fa-copy'
                },
                activateItem: {
                    label: 'Activate',
                    action: function (obj) {
                        performContextMenuAction(node, 'activate');
                    },
                    icon: 'fa fa-check-square-o'
                },
                deactivateItem: {
                    label: 'Deactivate',
                    action: function (obj) {
                        performContextMenuAction(node, 'deactivate');
                    },
                    icon: 'fa fa-square-o'
                },
                addItemAfter: {
                    label: 'Add sibling item (after selected)',
                    action: function (obj) {
                        performContextMenuAction(node, 'addafter');
                    },
                    icon: 'fa fa-level-up fa-rotate-90'
                },
                addItemInto: {
                    label: 'Add child item (into selected)',
                    action: function (obj) {
                        performContextMenuAction(node, 'addchild');
                    },
                    icon: 'fa fa-long-arrow-right'
                }
            };
        }

        function performContextMenuAction(node, action, extrainfo) {
            var allowedActions = ['edit', 'delete', 'deleteandmovesubs', 'copy', 'activate', 'deactivate', 'addafter', 'addchild'];
            var parentId;
            if (!$.inArray(action, allowedActions) == -1) {
                return false;
            }
            var nodeId = $(node).attr('id');
            // append spinner
            $('#' + nodeId).find('a').first().after('<i id="temp-spinner" class="fa fa-spinner fa-spin fa-lg text-primary"></i>');

            var pars = {
                entityId: node.data.entityId
            };
            // if (nodeId == 'node_1') {
            //     // do not allow editing of root
            //     $('#temp-spinner').remove();
            //     return false;
            // }
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
                url: Routing.generate('zikulamenumodule_menu_contextmenu', {action: action}),
                data: pars
            }).success(function(result) {
                performContextMenuActionCallback(result.data);
            }).error(function(result) {
                alert(result.status + ': ' + result.statusText);
            }).always(function() {
                $('#temp-spinner').remove();
                redrawTree(treeElem);
            });

            return true;
        }

        function performContextMenuActionCallback(data) {
            if (null == data) {
                return;
            }
            var originalNode = $('#node_' + data.id);
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
                    $('#editModal').find('.modal-body').html(data.result);
                    openEditForm(data, function (event) {
                        event.preventDefault();
                        var mode = data.action;
                        var buttonValue = $(this).val();

                        if (buttonValue == 'Cancel') {
                            closeEditForm();
                            return false;
                        }

                        // fetch each input and hidden field and store the value to POST
                        var pars = {entityId: data.id};
                        $.each($(':input, :hidden').serializeArray(), function(i, field) {
                            pars[field.name] = field.value;
                        });

                        $.ajax({
                            type: 'POST',
                            url: Routing.generate('zikulamenumodule_menu_contextmenu', {action: data.action}),
                            data: pars
                        }).success(function(result) {
                            var data = result.data;

                            if (data.validationErrors) {
                                if (data && data.validationErrors) {
                                    updateEditForm(data.result);
                                } else {
                                    closeEditForm();
                                }
                            } else {
                                if (mode == 'edit') {
                                    var nodeData = $.parseJSON(data.node);
                                    // delete the existing node and replace with edited version
                                    var editedNode = treeElem.jstree('get_node', 'node_' + data.id);
                                    treeElem.jstree(true).rename_node(editedNode, nodeData.title);
                                }
                                // var parentLi = $('#node_' + data.parent),
                                //     parentUl = parentLi.children('ul');
                                // if (!parentUl) {
                                //     parentUl = $('<ul>').attr({ 'class': 'tree' });
                                //     parentLi.append(parentUl);
                                // }
                                // var newNode = treeElem.jstree(true).create_node(parentUl, nodeData);
                                // var node = $('#' + newNode);
                                // reinitTreeNode(node, data);
                                closeEditForm();
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

        function getDeleteMenuAction(node) {
            var subCats = node.children.length;
            if (subCats > 0) {
                var info = 'It contains ' + subCats + ' direct children.'
                    + ' '
                    + "Please choose what to do with this item's children.";
                $('#deleteWithSubCatInfo').addClass('alert alert-warning').text(info);
            } else {
                $('#deleteWithSubCatInfo').removeClass('alert alert-warning').text('');
            }
            var deleteModal = $('#deleteModal');

            if (subCats > 0) {
                deleteModal.find('#cat_delete').hide();
                deleteModal.find('#cat_delete_all').show();
                deleteModal.find('#cat_delete_move').show();
            }
            $('#subcat_move').remove();

            deleteModal.find('.modal-footer button').one('click', function(event) {
                event.preventDefault();
                var buttonValue = $(this).val();

                switch (buttonValue) {
                    case 'Delete':
                        performContextMenuAction(node, 'delete');
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
                                deleteModal.find('#cat_delete_move').hide();
                                deleteModal.find('#cat_delete_move_action').show();
                            }).error(function(result) {
                                alert(result.status + ': ' + result.statusText);
                            }).always(function() {
                                $('#button-spinner').remove();
                            });
                        } else {
                            // utilize new parent to perform delete and move operation
                            var parent = $('#category_parent_id_').val();
                            if (parent) {
                                performContextMenuAction(node, 'deleteandmovesubs', parent);
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
                deleteModal.find('#cat_delete').show();
                deleteModal.find('#cat_delete_all').hide();
                deleteModal.find('#cat_delete_move').hide();
                deleteModal.find('#cat_delete_move_action').hide();
                $('#button-spinner').remove();
                $('#subcat_move').remove();
            });
            deleteModal.find('.modal-footer button[value=Cancel]').focus();
        }

        function openEditForm(data, callback) {
            $('#form_container').show();
            var editModal = $('#editModal');
            editModal.find('.modal-footer button').unbind('click').click(callback);

            editModal.modal();
            editModal.find('.modal-footer button[value=Cancel]').focus();
        }

        function updateEditForm(data) {
            $('#form_container').replaceWith(data).show();
        }

        function closeEditForm() {
            $('#editModal').modal('hide');
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
            // treeElem
            // // hide folder icons for leaf nodes
            //     .find('a.jstree-anchor.leaf > i.fa-folder').hide().end()
            // // use folder-open icon for already open nodes
            //     .find('li.jstree-open > a.z-tree-fixedparent > i.fa-folder').removeClass('fa-folder').addClass('fa-folder-open');
        }
    });
})(jQuery);
