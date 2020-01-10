// Copyright Zikula Foundation, licensed MIT.

(function($) {
    $(document).ready(function() {
        // config items
        var id_prefix = 'node_'; // must match NodeController::$domTreeNodePrefix
        var treeElem = $('#tree_container');
        treeElem.jstree({
            'core': {
                'animation': 0,
                'check_callback': true,
                'themes': { 'stripes': true }
            },
            'contextmenu': {
                'items': getContextMenuActions
            },
            'types': {
                'default': {
                    'icon': 'fa fa-check-circle'
                }
            },
            'plugins': [
                'contextmenu', 'dnd', 'state', 'types', 'wholerow'
            ]
        });

        function getContextMenuActions(node) {
            return {
                editItem: {
                    label: Translator.trans('Edit'),
                    action: function (obj) {
                        performContextMenuAction(node, 'edit');
                    },
                    icon: 'fa fa-edit'
                },
                deleteItem: {
                    label: Translator.trans('Delete'),
                    action: function (obj) {
                        getDeleteMenuAction(node);
                    },
                    icon: 'fa fa-times'
                },
                copyItem: {
                    label: Translator.trans('Copy'),
                    action: function (obj) {
                        performContextMenuAction(node, 'copy');
                    },
                    icon: 'fa fa-copy'
                },
                // activateItem: {
                //     label: Translator.trans('Activate'),
                //     action: function (obj) {
                //         performContextMenuAction(node, 'activate');
                //     },
                //     icon: 'fa fa-check-square'
                // },
                // deactivateItem: {
                //     label: Translator.trans('Deactivate'),
                //     action: function (obj) {
                //         performContextMenuAction(node, 'deactivate');
                //     },
                //     icon: 'fa fa-square'
                // },
                addItemAfter: {
                    label: Translator.trans('Add sibling item (after selected)'),
                    action: function (obj) {
                        performContextMenuAction(node, 'addafter');
                    },
                    icon: 'fa fa-level-up-alt fa-rotate-90'
                },
                addItemInto: {
                    label: Translator.trans('Add child item (into selected)'),
                    action: function (obj) {
                        performContextMenuAction(node, 'addchild');
                    },
                    icon: 'fa fa-long-arrow-alt-right'
                }
            };
        }

        function performContextMenuAction(node, action, extrainfo) {
            var allowedActions = ['edit', 'delete', 'deleteandmovechildren', 'copy', 'activate', 'deactivate', 'addafter', 'addchild'];
            var parentId;
            if (-1 === $.inArray(action, allowedActions)) {
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
            }).done(function (data) {
                performContextMenuActionCallback(data);
            }).fail(function (jqXHR, textStatus) {
                alert('Request failed: ' + textStatus);
            }).always(function () {
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

                        if ('Cancel' === buttonValue) {
                            closeEditForm();
                            return false;
                        }

                        // fetch each input and hidden field and store the value to POST
                        $.each($(':input, :hidden').serializeArray(), function(i, field) {
                            pars[field.name] = field.value;
                        });
                        if ('undefined' !== typeof data.id && data.id) {
                            entityId = data.id;
                        }

                        $.ajax({
                            type: 'POST',
                            url: Routing.generate('zikulamenumodule_node_contextmenu', {action: data.action, id: entityId}),
                            data: pars
                        }).done(function(data) {
                            if (data.action) {
                                // validation failed
                                updateEditForm(data.result);
                            } else {
                                var nodeData = $.parseJSON(data.node);
                                if ('edit' === data.mode) {
                                    // rename the existing node
                                    var editedNode = treeElem.jstree('get_node', nodeData.id);
                                    treeElem.jstree(true).rename_node(editedNode, nodeData.title);
                                } else {
                                    var selectedNode = treeElem.jstree('get_selected', true)[0], selectedNodeIndex = $('#' + selectedNode.id).index();
                                    var parentNode = treeElem.jstree('get_node', id_prefix + nodeData.parent);
                                    parentNode = !parentNode ? '#' : parentNode;
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
                var info = Translator._fn('It contains %count% direct child.', 'It contains %count% direct children.', childrenCount, {count: childrenCount})
                    + ' '
                    + Translator._n("Please choose what to do with this item's child.", "Please choose what to do with this item's children.", childrenCount);
                $('#deleteWithChildrenInfo').addClass('alert alert-warning').text(info);
            } else {
                $('#deleteWithChildrenInfo').removeClass('alert alert-warning').text('');
            }
            var deleteModal = $('#deleteModal');

            if (childrenCount > 0) {
                deleteModal.find('#node_delete').addClass('d-none');
                deleteModal.find('#node_delete_all').removeClass('d-none');
                deleteModal.find('#node_delete_move').removeClass('d-none');
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
                            $(this).prepend('<i id="button-spinner" class="fa fa-cog fa-spin fa-lg text-danger"></i> ');
                            $.ajax({
                                type: 'POST',
                                url: Routing.generate('zikulacategoriesmodule_ajax_deletedialog'),
                                data: {
                                    id: $(node).attr('id').replace(id_prefix, '')
                                }
                            }).done(function (data) {
                                var children_move = data.result;
                                deleteModal.find('.modal-body').append(children_move);
                                deleteModal.find('#node_delete_move').addClass('d-none');
                                deleteModal.find('#node_delete_move_action').removeClass('d-none');
                            }).fail(function (jqXHR, textStatus) {
                                alert('Request failed: ' + textStatus);
                            }).always(function () {
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
                deleteModal.find('#node_delete').removeClass('d-none');
                deleteModal.find('#node_delete_all').addClass('d-none');
                deleteModal.find('#node_delete_move').addClass('d-none');
                deleteModal.find('#node_delete_move_action').addClass('d-none');
                $('#button-spinner').remove();
                $('#children_move').remove();
            });
            deleteModal.find('.modal-footer button[value=Cancel]').focus();
        }

        function openEditForm(data, callback) {
            $('#form_container').removeClass('d-none');
            var editModal = $('#editModal');
            editModal.find('.modal-footer button').unbind('click').click(callback);

            editModal.modal();
            editModal.find('.modal-footer button[value=Cancel]').focus();
        }

        function updateEditForm(data) {
            $('#form_container').replaceWith(data).removeClass('d-none');
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
            }).done(function (data) {
                //console.log(data);
            }).fail(function (jqXHR, textStatus) {
                alert('Request failed: ' + textStatus);
            });
        }
    });
})(jQuery);
