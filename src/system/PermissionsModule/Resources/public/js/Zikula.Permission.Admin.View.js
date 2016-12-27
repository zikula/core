// Copyright Zikula Foundation, licensed MIT.

var currentDelete;
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
                    url: Routing.generate('zikulapermissionsmodule_permission_changeorder'),
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

        /* --- edit or create permission ---------------------------------------------------------------------------------------------- */
        /* Open modal to edit permission */
        function editPermissionHandler(event) {
            event.preventDefault();
            $(this).find('.fa').addClass('fa-spin');
            var pars = {};
            var id = event.data.action == 'edit' ? $(this).parents("tr").data('id') : 'undefined';
            if (event.data.action == 'new') {
                pars.sequence = $(this).hasClass('insertBefore') ? $(this).parents("tr").data("id") : -1;
            }
            $.ajax({
                type: 'POST',
                url: Routing.generate('zikulapermissionsmodule_permission_edit', {pid: id}),
                data: pars
            }).done(function(result) {
                var modal = $('#editModal');
                modal.find('.modal-body').html(result.data.view);
                modal.modal();
            }).fail(function(result) {
                alert(result.status + ': ' + result.statusText);
            }).always(function() {
            });
        }

        function updateEditForm(view) {
            $('#edit-form-container').replaceWith(view).show();
        }

        /* Save permission changes */
        $('#save-permission').click(function () {
            var pid = $('#zikulapermissionsmodule_permission_pid').val();
            if (pid == '') {
                pid = '-1';
            } else if (pid == adminpermission && lockadmin == 1) {
                return;
            }
            // fetch each input and hidden field and store the value to POST
            var pars = {};
            $.each($(':input, :hidden').serializeArray(), function(i, field) {
                pars[field.name] = field.value;
            });
            $.ajax({
                type: 'POST',
                url: Routing.generate('zikulapermissionsmodule_permission_edit', {
                    pid: pid
                }),
                data: pars
            }).done(function(result) {
                var data = result.data;
                if (data.view) {
                    // validation failed
                    updateEditForm(data.view);
                } else {
                    if (pid !== '-1') {
                        // update existing row
                        $('#permission-component-' + pid).text(data.permission.component);
                        $('#permission-instance-' + pid).text(data.permission.instance);
                        $('#permission-group-' + pid).data('id', data.permission.gid);
                        $('#permission-group-' + pid).text($('#zikulapermissionsmodule_permission_gid').find('option:selected').text());
                        $('#permission-level-' + pid).data('id', data.permission.level);
                        $('#permission-level-' + pid).text($('#zikulapermissionsmodule_permission_level').find('option:selected').text());
                    } else {
                        var existingIndexRow = $('#permission-list tr').eq(data.permission.sequence);
                        if (existingIndexRow.length !== 0) {
                            // insert new row above it
                            existingIndexRow.before(data.row);
                        } else {
                            // append new row
                            $('#permission-list').append(data.row);
                        }
                        initRowHandlers();
                    }
                }
            }).fail(function(result) {
                alert(result.status + ': ' + result.statusText);
            }).always(function() {
                $('#editModal').modal('hide');
            });
        });

        /* --- delete permission -------------------------------------------------------------------------------------------- */
        /* Open modal  */
        function startDeletePermission(event) {
            event.preventDefault();
            $(this).find('.fa').addClass('fa-spin');
            currentDelete = $(this).parents('tr');
            $('#deleteModal').modal();
        }

        /* Delete a permission */
        $('#confirm-delete-permission').click(function () {
            $.ajax({
                url: Routing.generate('zikulapermissionsmodule_permission_delete', {pid: currentDelete.data('id')}),
                type: 'POST',
                success: function () {
                    currentDelete.remove();
                }
            });
        });

        /* --- test permission ---------------------------------------------------------------------------------------------- */
        /* Copies the component, instance and level to the permission test form */
        function startTestPermission() {
            var pid = $(this).parents('tr').data('id');
            $('#zikulapermissionsmodule_permissioncheck_user').val('');
            $('#zikulapermissionsmodule_permissioncheck_component').val($('#permission-component-' + pid).text());
            $('#zikulapermissionsmodule_permissioncheck_instance').val($('#permission-instance-' + pid).text());
            $('#permission-test-info').html('&nbsp;');
            $('html, body').animate({
                scrollTop: $('#testpermform').offset().top
            }, 500);
        }

        /* Test a permission for a user */
        $('#zikulapermissionsmodule_permissioncheck_check').click(function (event) {
            event.preventDefault();
            var $permissionTestInfo = $('#permission-test-info');
            $permissionTestInfo.text($permissionTestInfo.data('testing'));
            // fetch each input and hidden field and store the value to POST
            var pars = {};
            $.each($(':input, :hidden').serializeArray(), function(i, field) {
                pars[field.name] = field.value;
            });
            $.ajax({
                url: Routing.generate('zikulapermissionsmodule_permission_test'),
                dataType: 'json',
                type: 'POST',
                data: pars,
                success: function (result) {
                    $permissionTestInfo.html(result.data.testresult);
                }
            });
        });
        $('#zikulapermissionsmodule_permissioncheck_reset').click(function (event) {
            event.preventDefault();
            $('#zikulapermissionsmodule_permissioncheck_user').val('');
            $('#zikulapermissionsmodule_permissioncheck_component').val('');
            $('#zikulapermissionsmodule_permissioncheck_instance').val('');
        });

        function initRowHandlers() {
            $('.edit-permission').unbind('click').on('click', {action: 'edit'}, editPermissionHandler);
            $('.create-new-permission').unbind('click').on('click', {action: 'new'}, editPermissionHandler);
            $('.delete-permission').unbind('click').click(startDeletePermission);
            $('.test-permission').unbind('click').click(startTestPermission);
        }

        initRowHandlers();

        /* --- View instance info ------------------------------------------------------------------------------------------- */
        /* Open modal */
        $('#view-instance-info').click(function (event) {
            event.preventDefault();
            $('#instanceInfoModal').modal();
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
        $('.modal').on('hidden.bs.modal', function (event) {
            $('.fa').removeClass('fa-spin');
        });
    });
})(jQuery);
