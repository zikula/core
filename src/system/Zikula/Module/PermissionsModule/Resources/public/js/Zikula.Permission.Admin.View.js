var currentDelete;
( function($) {
$(document).ready(function() {

/* --- init --------------------------------------------------------------------------------------------------------- */
/* hide no ajax fallback - show ajax */
$('.ajax').removeClass('hidden');
$('.no-script').addClass('hidden');

var $sortable = $('#permission-list > tbody');

// Return a helper with preserved width of cells
var fixHelper = function(e, ui) {
    ui.children().each(function() {
        jQuery(this).css({width: jQuery(this).width()});
    });
    return ui;
};
$sortable.sortable({
    helper: fixHelper,
    items: "tr:not(.warning)",
    update: function( event, ui ) {
        var parameters = [];

        $('#permission-list > tbody > tr').each(function() {
            parameters.push($(this).data('id'));

        });

        $.ajax({
            url: Routing.generate("zikulapermissionsmodule_ajax_changeorder"),
            dataType: "json",
            type: 'POST',
            data: {
                permorder: parameters
            },
            success: function(result) {
                console.log(result);
            }
        });

    }
});
$sortable.disableSelection();

/* --- test permission ---------------------------------------------------------------------------------------------- */
/* Copies the component, instance and level to the permission test form */
$(document).on('click', '.test-permission', function() {
    var pid = $(this).parent().parent().data('id');
    $('#test_user').val('');

    $('#test_component').val($('#permission-component-' + pid).text());
    $('#test_instance').val($('#permission-instance-' + pid).text());
    $('#permission-test-info').html('&nbsp;');
    $('html, body').animate({
        scrollTop: $("#testpermform").offset().top
    }, 500);
});

/* Test a permission for a user */
$('#test-permission').click( function(event) {
    event.preventDefault();
    var $permissionTestInfo = $('#permission-test-info');
    $permissionTestInfo.text($permissionTestInfo.data('testing'));
    var vars = {
        test_user: $('#test_user').val(),
        test_component: $('#test_component').val(),
        test_instance: $('#test_instance').val(),
        test_level: $('#test_level').val()
    };
    $.ajax({
        url: Routing.generate("zikulapermissionsmodule_ajax_test"),
        dataType: "json",
        type: 'POST',
        data: vars,
        success: function(result) {
            $permissionTestInfo.html(result.data.testresult);
        }
    });
});

/* --- edit permission ---------------------------------------------------------------------------------------------- */
/* Open modal to edit permission */
$(document).on('click', '.edit-permission', function(event) {
    event.preventDefault();
    var id = $(this).parent().parent().data('id');
    $('#permission-id').val(id);
    $('#permission-component').val($('#permission-component-'+id).text());
    $('#permission-instance').val($('#permission-instance-'+id).text());
    $('#permission-group').val($('#permission-group-'+id).data('id'));
    $('#permission-level').val($('#permission-level-'+id).data('id'));

    $('#save-permission-changes').show();
    $('#save-new-permission').hide();

    $('#editModal').modal()
});

/* Save permission changes */
$('#save-permission-changes').click( function() {
    var pid = $('#permission-id').val();
    if (pid==adminpermission && lockadmin==1) {
        return;
    }
    var vars = {
        pid: pid,
        gid: $('#permission-group').val(),
        comp: $('#permission-component').val(),
        inst: $('#permission-instance').val(),
        level: $('#permission-level').val()
    };

    $.ajax({
        url: Routing.generate("zikulapermissionsmodule_ajax_update"),
        dataType: "html",
        type: 'POST',
        data: vars,
        success: function() {
            $('#permission-component-'+pid).text(vars.comp);
            $('#permission-instance-'+pid).text(vars.inst);
            $('#permission-group-'+pid).data('id', vars.gid);
            $('#permission-group-'+pid).text($('#permission-group').find('option:selected').text());
            $('#permission-level-'+pid).data('id', vars.level);
            $('#permission-level-'+pid).text($('#permission-level').find('option:selected').text());
        }
    });

});

/* --- delete permission -------------------------------------------------------------------------------------------- */
/* Open modal  */
$(document).on('click', '.delete-permission', function(event) {
    event.preventDefault();
    currentDelete = $(this).parent().parent();
    $('#deleteModal').modal();
});

/* Delete a permission */
$('#confirm-delete-permission').click( function() {
    $.ajax({
        url: Routing.generate("zikulapermissionsmodule_ajax_delete"),
        type: 'POST',
        data: {pid: currentDelete.data('id')},
        success: function() {
            currentDelete.remove();
        }
    });
});

/* --- View instance info ------------------------------------------------------------------------------------------- */
/* Open modal */
$('#view-instance-info').click( function(event) {
    event.preventDefault();
    $('#instanceInfoModal').modal()
});

/* --- Add new permission ------------------------------------------------------------------------------------------- */
/* Open modal */
$('#new-permission').click( function(event) {
    event.preventDefault();
    $('#save-permission-changes').hide();
    $('#save-new-permission').show();

    $('#permission-component').val('.*');
    $('#permission-instance').val('.*');
    $('#permission-group').val(-1);
    $('#permission-level').val(0);

    $('#editModal').modal()
});

/* Save new permission */
$('#save-new-permission').click( function() {
    var vars = {
        group: $('#permission-group').val(),
        component: $('#permission-component').val(),
        level: $('#permission-level').val(),
        instance: $('#permission-instance').val()
    };

    $.ajax({
        url: Routing.generate("zikulapermissionsmodule_ajax_create"),
        dataType: "json",
        type: 'POST',
        data: vars,
        success: function(result) {

            var data = result.data;

            $('#permission-list').append(
                '<tr data-id="'+data.pid+'">'+
                '<td><i class="fa fa-arrows"></i></td>'+
                '<td>'+data.pid+'</td>'+
                '<td id="permission-group-'+data.pid+'" data-id="'+data.gid+'">'+data.groupname+'</td>'+
                '<td id="permission-component-'+data.pid+'">'+data.component+'</td>'+
                '<td id="permission-instance-'+data.pid+'">'+data.instance+'</td>'+
                '<td id="permission-level-'+data.pid+'" data-id="'+data.level+'">'+data.levelname+'</td>'+
                '<td class="actions">'+
                '<i class="fa fa-pencil  edit-permission   pointer" title="'+$('.edit-permission').first().attr('title')+'"></i> '+
                '<i class="fa fa-trash-o delete-permission pointer" title="'+$('.delete-permission').first().attr('title')+'"></i> '+
                '<i class="fa fa-key test-permission pointer" title="'+$('.test-permission').first().attr('title')+'"></i>'+
                '</td>'+
                '</tr>'
            );

            $('html, body').animate({
                scrollTop: $('#permission-group-'+data.pid).offset().top
            }, 500);
        }
    });
});

/* --- Filter permissions ------------------------------------------------------------------------------------------- */
$('#filter-group, #filter-component').change(function() {
    var group = $('#filter-group').val();
    var component = $('#filter-component').val();

    // toggle warnings
    if (group == -1) {
        $('#filter-warning-group').hide();
    } else {
        $('#filter-warning-group').show();
    }
    if (component == -1) {
        $('#filter-warning-component').hide();
    } else {
        $('#filter-warning-component').show();
    }

    $('#permission-list > tbody > tr').each(function() {
        var $this = $(this);
        var pid = $this.data('id');
        var show = true;
        if (group != -1 && group != $('#permission-group-'+pid).data('id')) {
            show = false;
        }
        if (component != -1 && component != $('#permission-component-'+pid).text().replace('::', '')) {
            show = false;
        }
        if (show) {
            $this.show();
        } else {
            $this.hide();
        }
    });
});


$('#reset-filter').click(function() {
    $('#filter-component').val(-1);
    $('#filter-group').val(-1).trigger("change");
})


});
})(jQuery);

