// Copyright Zikula Foundation, licensed MIT.

var currentDelete;
(function ($) {

    function initRowHandlers() {
        $('.edit-permission').unbind('click').on('click', {action: 'edit'}, editPermissionHandler);
        $('.create-new-permission').unbind('click').on('click', {action: 'new'}, editPermissionHandler);
        $('.delete-permission').unbind('click').click(startDeletePermission);
        $('.test-permission').unbind('click').click(startTestPermission);
        $('[data-toggle="tooltip"]').tooltip();
    }

    /* --- edit or create permission ---------------------------------------------------------------------------------------------- */
    /* Open modal to edit permission */
    function editPermissionHandler(event) {
        event.preventDefault();
        $(this).find('.fa').addClass('fa-spin');
        var pars = {};
        var id = 'edit' === event.data.action ? $(this).parents('tr').data('id') : 'undefined';
        if ('new' === event.data.action) {
            pars.sequence = $(this).hasClass('insertBefore') ? $(this).parents('tr').data('id') : -1;
        }
        $.ajax({
            type: 'POST',
            url: Routing.generate('zikulapermissionsmodule_permission_edit', {pid: id}),
            data: pars
        }).done(function (data) {
            var modal = $('#editModal');
            modal.find('.modal-body').html(data.view);
            modal.modal();
        }).fail(function (jqXHR, textStatus) {
            alert('Request failed: ' + textStatus);
        }).always(function () {
        });
    }

    function savePermission() {
        var pid = $('#zikulapermissionsmodule_permission_pid').val();
        if ('' === pid) {
            pid = '-1';
        } else if (pid === $('#adminPermissionParameters').data('adminid') && '1' == $('#adminPermissionParameters').data('locked')) {
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
        }).done(function (data) {
            if (data.view) {
                // validation failed
                $('#edit-form-container').replaceWith(data.view).show();
            } else {
                if (pid !== '-1') {
                    // update existing row
                    $('#permission-row-' + pid).removeClass().addClass('table-' + (data.permission.colour ? data.permission.colour : 'default'));
                    $('#permission-row-' + pid)
                        .attr('title', data.permission.comment)
                        .attr('data-original-title', data.permission.comment)
                        .tooltip('update')
                        .tooltip('show')
                    ;
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
        }).fail(function (jqXHR, textStatus) {
            alert('Request failed: ' + textStatus);
        }).always(function () {
            $('#editModal').modal('hide');
        });
    }

    /* --- delete permission -------------------------------------------------------------------------------------------- */
    /* Open modal  */
    function startDeletePermission(event) {
        event.preventDefault();
        $(this).find('.fa').addClass('fa-spin');
        currentDelete = $(this).parents('tr');
        $('#deleteModal').modal();
    }

    function deletePermission() {
        $.ajax({
            url: Routing.generate('zikulapermissionsmodule_permission_delete', {pid: currentDelete.data('id')}),
            type: 'POST'
        }).done(function () {
            currentDelete.remove();
        });
    }

    /* --- test permission ---------------------------------------------------------------------------------------------- */
    /* Copies the component, instance and level to the permission test form */
    function startTestPermission(event) {
        event.preventDefault();
        var pid = $(this).parents('tr').data('id');
        $('#zikulapermissionsmodule_permissioncheck_user').val('');
        $('#zikulapermissionsmodule_permissioncheck_component').val($('#permission-component-' + pid).text());
        $('#zikulapermissionsmodule_permissioncheck_instance').val($('#permission-instance-' + pid).text());
        $('#permission-test-info').html('&nbsp;');
        $('html, body').animate({
            scrollTop: $('#testpermform').offset().top
        }, 500);
    }

    $(document).ready(function () {
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
            items: 'tr:not(.locked)',
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
                    }
                }).done(function (data) {
                    //console.log(data);
                });
            }
        });
        $sortable.disableSelection();

        /* Save permission changes */
        $('#save-permission').click(savePermission);

        /* Delete a permission */
        $('#confirm-delete-permission').click(deletePermission);

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
                data: pars
            }).done(function (data) {
                $permissionTestInfo.html(data.testresult);
            });
        });
        $('#zikulapermissionsmodule_permissioncheck_reset').click(function (event) {
            event.preventDefault();
            $('#zikulapermissionsmodule_permissioncheck_user').val('');
            $('#zikulapermissionsmodule_permissioncheck_component').val('');
            $('#zikulapermissionsmodule_permissioncheck_instance').val('');
        });

        initRowHandlers();

        /* --- View instance info ------------------------------------------------------------------------------------------- */
        $('.view-instance-info').click(function (event) {
            event.preventDefault();
            $('#instanceInfoModal').modal();
        });

        /* --- Filter permissions ------------------------------------------------------------------------------------------- */
        $('#zikulapermissionsmodule_filterlist_filterGroup, #zikulapermissionsmodule_filterlist_filterComponent, #zikulapermissionsmodule_filterlist_filterColour').change(function () {
            var group = $('#zikulapermissionsmodule_filterlist_filterGroup').val();
            var component = $('#zikulapermissionsmodule_filterlist_filterComponent').val();
            var colour = $('#zikulapermissionsmodule_filterlist_filterColour').val();

            // toggle warnings
            $('#filter-warning-group').toggleClass('d-none', group == '-1');
            $('#filter-warning-component').toggleClass('d-none', component == '-1');

            $('#permission-list > tbody > tr').each(function () {
                var $this = $(this);
                var pid = $this.data('id');
                var show = true;
                if (group != '-1' && group != $('#permission-group-' + pid).data('id')) {
                    show = false;
                }
                if (component != '-1' && $('#permission-component-' + pid).text().indexOf(component) == -1) {
                    show = false;
                }
                if (colour != '-1' && !$('#permission-row-' + pid).hasClass('table-' + colour)) {
                    show = false;
                }
                $this.toggleClass('d-none', !show);
            });
        });

        $('#zikulapermissionsmodule_filterlist_reset').click(function () {
            $('#zikulapermissionsmodule_filterlist_filterComponent').val(-1);
            $('#zikulapermissionsmodule_filterlist_filterGroup').val(-1).trigger('change');
            $('#zikulapermissionsmodule_filterlist_filterColour').val(-1);
        });

        // on modal close, stop all spinning icons
        $('.modal').on('hidden.bs.modal', function (event) {
            $('.fa').removeClass('fa-spin');
        });
    });
})(jQuery);
