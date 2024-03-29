// Copyright Zikula, licensed MIT.

(function($) {
    $(document).ready(function() {
        // config items
        var id_prefix = 'node_'; // must match NodeController::$domTreeNodePrefix
        var treeElem = $('#tree_container');
        treeElem.jstree({
            'core' : {
                'multiple': false,
                'check_callback': function(operation, node, node_parent, node_position, more) {
                    if (operation === 'move_node') {
                        return ($.inArray($(node).attr('id'), nodesDisabledForDrop) === -1);
                    }
                    return true; // allow all other operations
                }
            },
            'contextmenu': {
                'items': getContextMenuActions
            },
            'dnd': {
                'copy': false,
                'is_draggable': function(node) {
                    // disable drag and drop for root category
                    return ($(node).attr('id') !== 'node_1');
                },
                'inside_pos': 'last'
            },
            'state': {
                'key': 'categoryTree'
            },
            'plugins': [ 'contextmenu', 'dnd', 'search', 'state', 'types' ],
            'types' : {
                'default' : {
                    'icon': 'fas fa-folder'
                },
                'leaf' : {
                    'icon': false
                }
            }
        });

        // Tree interaction
        treeElem.on('move_node.jstree', moveTreeNode);
        $('#catExpand').on('click', function(event) {
            event.preventDefault();
            treeElem.jstree(true).open_all(null, 500);
        });
        $('#catCollapse').on('click', function(event) {
            event.preventDefault();
            treeElem.jstree(true).close_all(null, 500);
        });
        $('#newCategory').on('click', function (event) {
            event.preventDefault();
            performContextMenuAction(null, 'edit');
        });

        // Tooltips
        treeElem.on('hover_node.jstree', function(e, data) {
            $('.tooltip').remove();
            var anchor = $('#' + data.node.id + '_anchor');
            anchor.tooltip({
                placement: 'right',
                html: true
            });
            anchor.tooltip('show');
        });

        function getContextMenuActions(node) {
            var actions = {
                editItem: {
                    label: Translator.trans('Edit'),
                    action: function (obj) {
                        performContextMenuAction(node, 'edit');
                    },
                    icon: 'fas fa-edit'
                },
                deleteItem: {
                    label: Translator.trans('Delete'),
                    action: function (obj) {
                        getDeleteMenuAction(node);
                    },
                    icon: 'fas fa-times'
                },
                copyItem: {
                    label: Translator.trans('Copy'),
                    action: function (obj) {
                        performContextMenuAction(node, 'copy');
                    },
                    icon: 'fas fa-copy'
                },
                activateItem: {
                    label: Translator.trans('Activate'),
                    action: function (obj) {
                        performContextMenuAction(node, 'activate');
                    },
                    icon: 'fas fa-check-square'
                },
                deactivateItem: {
                    label: Translator.trans('Deactivate'),
                    action: function (obj) {
                        performContextMenuAction(node, 'deactivate');
                    },
                    icon: 'fas fa-square'
                },
                addItemAfter: {
                    label: Translator.trans('Add sibling item (after selected)'),
                    action: function (obj) {
                        performContextMenuAction(node, 'addafter');
                    },
                    icon: 'fas fa-level-up-alt fa-rotate-90'
                },
                addItemInto: {
                    label: Translator.trans('Add child item (into selected)'),
                    action: function (obj) {
                        performContextMenuAction(node, 'addchild');
                    },
                    icon: 'fas fa-long-arrow-alt-right'
                }
            };
            if (treeElem.jstree('is_disabled', node, true)) {
                actions.deactivateItem._disabled = true;
            } else {
                actions.activateItem._disabled = true;
            }
            if (typeof node.a_attr.class != 'undefined' && node.a_attr.class.indexOf('leaf')) {
                // jstree.is_leaf() returns true if the node has no children, not if the node is defined as a leaf
                actions.addItemInto._disabled = true;
            }
            return actions;
        }

        function performContextMenuAction(node, action, extrainfo) {
            var allowedActions = ['edit', 'delete', 'deleteandmovechildren', 'copy', 'activate', 'deactivate', 'addafter', 'addchild'];
            var parentId, entityId;
            if (-1 === $.inArray(action, allowedActions)) {
                return false;
            }
            if (node) {
                var nodeId = $(node).attr('id');
                entityId = nodeId.replace(id_prefix, '');
                // append spinner
                $('#' + nodeId).find('a').first().after('<i id="temp-spinner" class="fas fa-spinner fa-spin fa-lg text-primary"></i>');
            }

            var pars = {};
            switch (action) {
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
                    pars.after = entityId;
                    entityId = null;
                    pars.mode = 'add';
                    action = 'edit';
                    break;
                case 'addchild':
                    pars.parent = entityId;
                    entityId = null;
                    pars.mode = 'add';
                    action = 'edit';
                    break;
            }

            $.ajax({
                type: 'POST',
                url: Routing.generate('zikulacategoriesbundle_node_contextmenu', {action: action, id: entityId}),
                data: pars
            }).done(function (data) {
                performContextMenuActionCallback(data);
            }).fail(function (jqXHR, textStatus) {
                alert('Request failed: ' + textStatus);
            }).always(function () {
                $('#temp-spinner').remove();
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
                    treeElem.jstree('enable_node', originalNode);
                    break;
                case 'deactivate':
                    treeElem.jstree('disable_node', originalNode);
                    break;
                case 'copy':
                    var indexOfOriginalNode = originalNode.parent().children().index(originalNode[0]);
                    treeElem.jstree(true).create_node(parentNode, data.node, indexOfOriginalNode);
                    break;
                case 'edit':
                    updateEditForm(data.result);
                    openEditForm(data, function (event) {
                        event.preventDefault();
                        // var mode = data.action;
                        var buttonValue = $(this).val();
                        var entityId;

                        if (buttonValue === 'Cancel') {
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
                            url: Routing.generate('zikulacategoriesbundle_node_contextmenu', {action: data.action, id: entityId}),
                            data: pars
                        }).done(function (data) {
                            if (data.action) {
                                // validation failed
                                updateEditForm(data.result);
                            } else {
                                var nodeData = $.parseJSON(data.node);
                                var editedNode;
                                if ('edit' === data.mode) {
                                    // rename the existing node
                                    var newName = nodeData.displayName[Translator.locale];
                                    newName = newName ? newName : nodeData.name;
                                    editedNode = treeElem.jstree('get_node', nodeData.id);
                                    treeElem.jstree(true).rename_node(editedNode, newName);
                                } else {
                                    var selectedNode = treeElem.jstree('get_selected', true)[0], selectedNodeIndex = $('#' + selectedNode.id).index();
                                    var parentNode = treeElem.jstree('get_node', id_prefix + nodeData.parent);
                                    parentNode = (!parentNode) ? '#' : parentNode;
                                    var nodeId = treeElem.jstree(true).create_node(parentNode, nodeData, selectedNodeIndex + 1);
                                    editedNode = treeElem.jstree('get_node', nodeId);
                                }
                                var nodeType = nodeData.leaf ? 'leaf' : 'default';
                                treeElem.jstree(true).set_type(editedNode, nodeType);
                                closeEditForm();
                            }
                        }).fail(function (jqXHR, textStatus) {
                            alert('Request failed: ' + textStatus);
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
                var info = Translator.transChoice('{1}It contains one direct child.|]1,Inf]It contains %count% direct children.', childrenCount, {count: childrenCount})
                    + ' '
                    + Translator.transChoice("{1}Please choose what to do with this item's child.|]1,Inf]Please choose what to do with this item's children.", childrenCount, {count: childrenCount});
                $('#deleteWithChildrenInfo').addClass('alert alert-danger').text(info);
            } else {
                $('#deleteWithChildrenInfo').removeClass('alert alert-danger').text('');
            }
            var deleteModal = $('#deleteModal');
            // hide all buttons
            deleteModal.find('.modal-footer button').addClass('d-none');
            $('#node_cancel').removeClass('d-none');

            if (childrenCount > 0) {
                $('#node_delete_all').removeClass('d-none');
                $('#node_delete_move').removeClass('d-none');
            } else {
                $('#node_delete').removeClass('d-none');
                deleteModal.find('.modal-dialog').removeClass('modal-lg');
            }

            deleteModal.find('.modal-footer button').one('click', function(event) {
                event.preventDefault();
                var buttonValue = $(this).val();

                switch (buttonValue) {
                    case 'Delete':
                        performContextMenuAction(node, 'delete');
                        deleteModal.modal('hide');
                        break;
                    case 'DeleteAndMove':
                        $('#node_delete_all').addClass('d-none');
                        $('#categorySelector').addClass('show');
                        $('#node_delete_move_action').removeClass('d-none');
                        $(this).addClass('d-none');
                        break;
                    case 'DeleteAndMoveAction':
                        // utilize new parent to perform delete and move operation
                        var parent = $('#form_category').val();
                        if (parent) {
                            performContextMenuAction(node, 'deleteandmovechildren', parent);
                            deleteModal.modal('hide');
                        }
                        break;
                    default:
                        deleteModal.modal('hide');
                }
            });

            deleteModal.modal();
            deleteModal.on('hidden.bs.modal', function (e) {
                $('#categorySelector').removeClass('show');
                $(this).find('.modal-dialog').addClass('modal-lg');
                $('#button-spinner').remove();
            });
            deleteModal.find('.modal-footer button[value=Cancel]').focus();
        }

        function openEditForm(data, callback) {
            var editModal = $('#editModal');
            editModal.find('.modal-footer button').unbind('click').click(callback);

            editModal.modal();
            editModal.find('.modal-footer button[value=Cancel]').focus();
        }

        function updateEditForm(data) {
            $('#editModal').find('.modal-body').html(data);
            if ('function' === typeof initFontAwesomeIconPicker) {
                initFontAwesomeIconPicker();
            }
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
            //     .find('a.jstree-anchor.leaf > i.fa-folder').addClass('d-none').end()
            // // use folder-open icon for already open nodes
            //     .find('li.jstree-open > a.z-tree-fixedparent > i.fa-folder').removeClass('fa-folder').addClass('fa-folder-open');
        }

        function moveTreeNode(e, data) {
            $.ajax({
                type: 'POST',
                url: Routing.generate('zikulacategoriesbundle_node_move'),
                data: {
                    node: data.node,
                    old_parent: data.old_parent,
                    old_position: data.old_position,
                    parent: data.parent,
                    position: data.position
                }
            }).done(function (data) {
                //console.log(data);
            }).fail(function (jqXHR, textStatus) {
                alert('Request failed: ' + textStatus);
            });
        }
    });
})(jQuery);
