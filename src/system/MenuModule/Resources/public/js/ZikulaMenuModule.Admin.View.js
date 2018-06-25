( function($) {
    $(document).ready(function() {
        // config items
        var id_prefix = 'node_'; // must match NodeController::$domTreeNodePrefix
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
                "default" : {
                    "icon" : "fa fa-check-circle"
                }
            },
            "plugins" : [
                "contextmenu", "dnd", "state", "types", "wholerow"
            ]
        });
        // end config

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
                // activateItem: {
                //     label: 'Activate',
                //     action: function (obj) {
                //         performContextMenuAction(node, 'activate');
                //     },
                //     icon: 'fa fa-check-square-o'
                // },
                // deactivateItem: {
                //     label: 'Deactivate',
                //     action: function (obj) {
                //         performContextMenuAction(node, 'deactivate');
                //     },
                //     icon: 'fa fa-square-o'
                // },
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
            var allowedActions = ['edit', 'delete', 'deleteandmovechildren', 'copy', 'activate', 'deactivate', 'addafter', 'addchild'];
            var parentId;
            if (!$.inArray(action, allowedActions) == -1) {
                return false;
            }
            var nodeId = $(node).attr('id');
            var entityId;
            // append spinner
            $('#' + nodeId).find('a').first().after('<i id="temp-spinner" class="fa fa-spinner fa-spin fa-lg text-primary"></i>');

            var pars = {};
            switch (action) {
                case 'edit':
                case 'delete':
                    entityId = nodeId.replace(id_prefix, '');
                    break;
                case 'deleteandmovechildren':
                    pars.parent = extrainfo;
                    break;
                case 'copy':
                    parentId = treeElem.jstree('get_parent', node);
                    pars.parent = parentId !== '#' ? $('#' + parentId).attr('id').replace(id_prefix, '') : null;
                    break;
                case 'addafter':
                    parentId = treeElem.jstree('get_parent', node);
                    pars.parent = parentId !== '#' ? $('#' + parentId).attr('id').replace(id_prefix, '') : null;
                    pars.after = nodeId.replace(id_prefix, '');
                    pars.mode = 'add';
                    action = 'edit';
                    break;
                case 'addchild':
                    pars.parent = nodeId.replace(id_prefix, '');
                    pars.mode = 'add';
                    action = 'edit';
                    break;
            }

            $.ajax({
                type: 'POST',
                url: Routing.generate('zikulamenumodule_node_contextmenu', {action: action, id: entityId}),
                data: pars
            }).done(function(result) {
                performContextMenuActionCallback(result.data);
            }).fail(function(result) {
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
            var originalNode = $('#' + id_prefix + data.id);
            var parentNode = $('#' + id_prefix + data.parent);
            var pars = {};
            switch (data.action) {
                case 'delete':
                    treeElem.jstree('delete_node', originalNode);
                    break;
                case 'deleteandmovechildren':
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
                // case 'add':
                    $('#editModal').find('.modal-body').html(data.result);
                    openEditForm(data, function (event) {
                        event.preventDefault();
                        // var mode = data.action;
                        var buttonValue = $(this).val();
                        var entityId;

                        if (buttonValue == 'Cancel') {
                            closeEditForm();
                            return false;
                        }

                        // fetch each input and hidden field and store the value to POST
                        $.each($(':input, :hidden').serializeArray(), function(i, field) {
                            pars[field.name] = field.value;
                        });
                        if ((typeof data.id !== 'undefined') && data.id) {
                            entityId = data.id;
                        }

                        $.ajax({
                            type: 'POST',
                            url: Routing.generate('zikulamenumodule_node_contextmenu', {action: data.action, id: entityId}),
                            data: pars
                        }).done(function(result) {
                            var data = result.data;

                            if (data.action) {
                                // validation failed
                                updateEditForm(data.result);
                            } else {
                                var nodeData = $.parseJSON(data.node);
                                if (data.mode == 'edit') {
                                    // rename the existing node
                                    var editedNode = treeElem.jstree('get_node', nodeData.id);
                                    treeElem.jstree(true).rename_node(editedNode, nodeData.title);
                                } else {
                                    var selectedNode = treeElem.jstree('get_selected', true)[0], selectedNodeIndex = $('#' + selectedNode.id).index();
                                    var parentNode = treeElem.jstree('get_node', id_prefix + nodeData.parent);
                                    parentNode = (!parentNode) ? "#" : parentNode;
                                    treeElem.jstree(true).create_node(parentNode, nodeData, selectedNodeIndex + 1);
                                }
                                closeEditForm();
                            }
                        }).fail(function(result) {
                            alert(result.status + ': ' + result.statusText);
                        });

                        return true;
                    });
                    break;
            }

            return true;
        }

        function getDeleteMenuAction(node) {
            var childrenCount = node.children.length;
            if (childrenCount > 0) {
                var info = 'It contains ' + childrenCount + ' direct children.'
                    + ' '
                    + "Please choose what to do with this item's children.";
                $('#deleteWithChildrenInfo').addClass('alert alert-warning').text(info);
            } else {
                $('#deleteWithChildrenInfo').removeClass('alert alert-warning').text('');
            }
            var deleteModal = $('#deleteModal');

            if (childrenCount > 0) {
                deleteModal.find('#node_delete').hide();
                deleteModal.find('#node_delete_all').show();
                deleteModal.find('#node_delete_move').show();
            }
            $('#children_move').remove();

            deleteModal.find('.modal-footer button').one('click', function(event) {
                event.preventDefault();
                var buttonValue = $(this).val();

                switch (buttonValue) {
                    case 'Delete':
                        performContextMenuAction(node, 'delete');
                        deleteModal.modal('hide');
                        break;
                    case 'DeleteAndMoveChildren':
                        if (!$('#children_move').length) {
                            // present dialog to determine new parent
                            $(this).prepend('<i id="button-spinner" class="fa fa-gear fa-spin fa-lg text-danger"></i> ');
                            $.ajax({
                                type: 'POST',
                                url: Routing.generate('zikulacategoriesmodule_ajax_deletedialog'),
                                data: {
                                    id: $(node).attr('id').replace(id_prefix, '')
                                }
                            }).done(function(result) {
                                var children_move = result.data.result;
                                deleteModal.find('.modal-body').append(children_move);
                                deleteModal.find('#node_delete_move').hide();
                                deleteModal.find('#node_delete_move_action').show();
                            }).fail(function(result) {
                                alert(result.status + ': ' + result.statusText);
                            }).always(function() {
                                $('#button-spinner').remove();
                            });
                        } else {
                            // utilize new parent to perform delete and move operation
                            var parent = $('#category_parent_id_').val();
                            if (parent) {
                                performContextMenuAction(node, 'deleteandmovechildren', parent);
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
                deleteModal.find('#node_delete').show();
                deleteModal.find('#node_delete_all').hide();
                deleteModal.find('#node_delete_move').hide();
                deleteModal.find('#node_delete_move_action').hide();
                $('#button-spinner').remove();
                $('#children_move').remove();
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
            // if (data.leafstatus) {
            //     if (data.leafstatus.leaf) {
            //         // add elements
            //         $.merge(nodesDisabledForDrop, data.leafstatus.leaf);
            //     }
            //     if (data.leafstatus.noleaf) {
            //         // remove elements
            //         nodesDisabledForDrop = $.grep(nodesDisabledForDrop, function(value) {
            //             return $.inArray(value, data.leafstatus.noleaf) < 0;
            //         });
            //     }
            // }
        }

        function redrawTree(treeElem) {
            // treeElem
            // // hide folder icons for leaf nodes
            //     .find('a.jstree-anchor.leaf > i.fa-folder').hide().end()
            // // use folder-open icon for already open nodes
            //     .find('li.jstree-open > a.z-tree-fixedparent > i.fa-folder').removeClass('fa-folder').addClass('fa-folder-open');
        }

        treeElem.on("move_node.jstree", moveTreeNode);

        function moveTreeNode(e, data) {
            $.ajax({
                type: 'POST',
                url: Routing.generate('zikulamenumodule_node_move'),
                data: {
                    node: data.node,
                    old_parent: data.old_parent,
                    old_position: data.old_position,
                    parent: data.parent,
                    position: data.position
                }
            }).done(function(result) {
                console.log(result);
            }).fail(function(result) {
                alert(result.status + ': ' + result.statusText);
            });
        }
    });
})(jQuery);
