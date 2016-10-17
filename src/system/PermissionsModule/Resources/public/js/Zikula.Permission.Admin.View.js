// Copyright Zikula Foundation, licensed MIT.

var currentDelete, currentInsertBefore;
(function ($) {
    $(document).ready(function () {

        /* --- init --------------------------------------------------------------------------------------------------------- */
        var $sortable = $('#permission-list > tbody');

        // Return a helper with preserved width of cells
        var fixHelper = function (e, ui) {
            ui.children().each(function () {
                jQuery(this).css({width: jQuery(this).width()});
            });
            return ui;
        };
        $sortable.sortable({
            helper: fixHelper,
            items: 'tr:not(.warning)',
            update: function (event, ui) {
                var parameters = [];

                $('#permission-list > tbody > tr').each(function () {
                    parameters.push($(this).data('id'));

                });

                $.ajax({
                    url: Routing.generate('zikulapermissionsmodule_ajax_changeorder'),
                    dataType: 'json',
                    type: 'POST',
                    data: {
                        permorder: parameters
                    },
                    success: function (result) {
                        console.log(result);
                    }
                });

            }
        });
        $sortable.disableSelection();

        /* --- test permission ---------------------------------------------------------------------------------------------- */
        /* Copies the component, instance and level to the permission test form */
        $(document).on('click', '.test-permission', function () {
            var pid = $(this).parents("tr").data('id');
            $('#zikulapermissionsmodule_permissioncheck_test_user').val('');

            $('#zikulapermissionsmodule_permissioncheck_test_component').val($('#permission-component-' + pid).text());
            $('#zikulapermissionsmodule_permissioncheck_test_instance').val($('#permission-instance-' + pid).text());
            $('#permission-test-info').html('&nbsp;');
            $('html, body').animate({
                scrollTop: $('#testpermform').offset().top
            }, 500);
        });

        /* Test a permission for a user */
        $('#zikulapermissionsmodule_permissioncheck_test_permission').click(function (event) {
            event.preventDefault();
            var $permissionTestInfo = $('#permission-test-info');
            $permissionTestInfo.text($permissionTestInfo.data('testing'));
            var vars = {
                test_user: $('#zikulapermissionsmodule_permissioncheck_test_user').val(),
                test_component: $('#zikulapermissionsmodule_permissioncheck_test_component').val(),
                test_instance: $('#zikulapermissionsmodule_permissioncheck_test_instance').val(),
                test_level: $('#zikulapermissionsmodule_permissioncheck_test_level').val()
            };
            $.ajax({
                url: Routing.generate('zikulapermissionsmodule_ajax_test'),
                dataType: 'json',
                type: 'POST',
                data: vars,
                success: function (result) {
                    $permissionTestInfo.html(result.data.testresult);
                }
            });
        });
        $('#zikulapermissionsmodule_permissioncheck_reset').click(function (event) {
            event.preventDefault();
            $('#zikulapermissionsmodule_permissioncheck_test_user').val('');
            $('#zikulapermissionsmodule_permissioncheck_test_component').val('');
            $('#zikulapermissionsmodule_permissioncheck_test_instance').val('');
        });

        /* --- edit permission ---------------------------------------------------------------------------------------------- */
        /* Open modal to edit permission */
        $(document).on('click', '.edit-permission', function (event) {
            event.preventDefault();
            $(this).find('.fa').addClass('fa-spin');
            var id = $(this).parents("tr").data('id');
            $.ajax({
                type: 'POST',
                url: Routing.generate('zikulapermissionsmodule_permission_edit', {pid: id})
            }).done(function(result) {
                openEditForm(result.data);
            }).fail(function(result) {
                alert(result.status + ': ' + result.statusText);
            }).always(function() {
            });
        });

        function openEditForm(data) {
            var modal = $('#editModal');
            modal.find('.modal-body').html(data.view);
            $('#save-permission-changes').show();
            $('#save-new-permission').hide();

            modal.modal();
        }

        function updateEditForm(view) {
            $('#edit-form-container').replaceWith(view).show();
        }

        function closeEditForm() {
            $('#editModal').modal('hide');
        }

        /* Save permission changes */
        $('#save-permission-changes').click(function () {
            var pid = $('#zikulapermissionsmodule_permission_pid').val();
            if (pid == adminpermission && lockadmin == 1) {
                return;
            }

            // fetch each input and hidden field and store the value to POST
            var pars = {};
            $.each($(':input, :hidden').serializeArray(), function(i, field) {
                pars[field.name] = field.value;
            });
            // if ((typeof data.id !== 'undefined') && data.id) {
            //     entityId = data.id;
            // }

            $.ajax({
                type: 'POST',
                url: Routing.generate('zikulapermissionsmodule_permission_edit', {pid: pid}),
                data: pars
            }).done(function(result) {
                var data = result.data;
                if (data.view) {
                    // validation failed
                    updateEditForm(data.view);
                } else {
                    $('#permission-component-' + pid).text(data.permission.component);
                    $('#permission-instance-' + pid).text(data.permission.instance);
                    $('#permission-group-' + pid).data('id', data.permission.gid);
                    $('#permission-group-' + pid).text($('#permission-group').find('option:selected').text());
                    $('#permission-level-' + pid).data('id', data.permission.level);
                    $('#permission-level-' + pid).text($('#permission-level').find('option:selected').text());
                    closeEditForm();
                }
            }).fail(function(result) {
                alert(result.status + ': ' + result.statusText);
            }).always(function() {
            });
        });

        /* --- delete permission -------------------------------------------------------------------------------------------- */
        /* Open modal  */
        $(document).on('click', '.delete-permission', function (event) {
            event.preventDefault();
            $(this).find('.fa').addClass('fa-spin');
            currentDelete = $(this).parents("tr");
            $('#deleteModal').modal();
        });

        /* Delete a permission */
        $('#confirm-delete-permission').click(function () {
            $.ajax({
                url: Routing.generate('zikulapermissionsmodule_ajax_delete'),
                type: 'POST',
                data: {
                    pid: currentDelete.data('id')
                },
                success: function () {
                    currentDelete.remove();
                }
            });
        });

        /* --- View instance info ------------------------------------------------------------------------------------------- */
        /* Open modal */
        $('#view-instance-info').click(function (event) {
            event.preventDefault();
            $('#instanceInfoModal').modal();
        });

        /* --- Add new permission ------------------------------------------------------------------------------------------- */
        /* Open modal */
        $('.create-new-permission').click(function (event) {
            event.preventDefault();
            $(this).find('.fa').addClass('fa-spin');
            currentInsertBefore = $(this).hasClass('insertBefore') ? $(this).parents("tr") : null;
            $('#save-permission-changes').hide();
            $('#save-new-permission').show();

            $('#permission-component').val('.*');
            $('#permission-instance').val('.*');
            $('#permission-group').val(-1);
            $('#permission-level').val(0);

            $('#editModal').modal();
        });

        /* Save new permission */
        $('#save-new-permission').click(function () {
            var vars = {
                group: $('#permission-group').val(),
                component: $('#permission-component').val(),
                level: $('#permission-level').val(),
                instance: $('#permission-instance').val(),
                insseq: (currentInsertBefore) ? currentInsertBefore.data('id') : -1
            };

            $.ajax({
                url: Routing.generate('zikulapermissionsmodule_ajax_create'),
                dataType: 'json',
                type: 'POST',
                data: vars,
                success: function (result) {
                    var data = result.data;
                    var row = '<tr data-id="' + data.pid + '">' +
                        '<td><i class="fa fa-arrows"></i></td>' +
                        '<td>' + data.pid + '</td>' +
                        '<td id="permission-group-' + data.pid + '" data-id="' + data.gid + '">' + data.groupname + '</td>' +
                        '<td id="permission-component-' + data.pid + '">' + data.component + '</td>' +
                        '<td id="permission-instance-' + data.pid + '">' + data.instance + '</td>' +
                        '<td id="permission-level-' + data.pid + '" data-id="' + data.level + '">' + data.levelname + '</td>' +
                        '<td class="actions">' +
                        '<a class="fa fa-plus pointer insertBefore create-new-permission tooltips" href="#" title="' + $('.create-new-permission').first().attr('title') + '"></a> ' +
                        '<a class="fa fa-pencil pointer edit-permission tooltips" href="#" title="' + $('.edit-permission').first().attr('title') + '"></a> ' +
                        '<a class="fa fa-trash-o delete-permission tooltips" href="#" title="' + $('.delete-permission').first().attr('title') + '"></a> ' +
                        '<i class="fa fa-key test-permission pointer tooltips" title="' + $('.test-permission').first().attr('title') + '"></i>' +
                        '</td>' +
                        '</tr>';

                    // insert the new row either before selected row or at the end of the list
                    if (currentInsertBefore) {
                        currentInsertBefore.before(row);
                        currentInsertBefore = null;
                    } else {
                        $('#permission-list').append(row);
                        $('html, body').animate({
                            scrollTop: $('#permission-group-' + data.pid).offset().top
                        }, 500);
                    }

                }
            });
        });

        /* --- Filter permissions ------------------------------------------------------------------------------------------- */
        $('#zikulapermissionsmodule_filterlist_filterGroup, #zikulapermissionsmodule_filterlist_filterComponent').change(function () {
            var group = $('#zikulapermissionsmodule_filterlist_filterGroup').val();
            var component = $('#zikulapermissionsmodule_filterlist_filterComponent').val();

            // toggle warnings
            if (group == -1) {
                $('#filter-warning-group').hide();
            } else {
                $('#filter-warning-group').show();
            }
            if (component == "-1") {
                $('#filter-warning-component').hide();
            } else {
                $('#filter-warning-component').show();
            }

            $('#permission-list > tbody > tr').each(function () {
                var $this = $(this);
                var pid = $this.data('id');
                var show = true;
                if (group != -1 && group != $('#permission-group-' + pid).data('id')) {
                    show = false;
                }
                if (component != "-1" && $('#permission-component-' + pid).text().indexOf(component) == -1) {
                    show = false;
                }
                if (show) {
                    $this.show();
                } else {
                    $this.hide();
                }
            });
        });

        $('#zikulapermissionsmodule_filterlist_reset').click(function () {
            $('#zikulapermissionsmodule_filterlist_filterComponent').val(-1);
            $('#zikulapermissionsmodule_filterlist_filterGroup').val(-1).trigger('change');
        });

        // on modal close, stop all spinning icons
        $('.modal').on('hidden.bs.modal', function (e) {
            $('.fa').removeClass('fa-spin');
        });
    });
})(jQuery);
