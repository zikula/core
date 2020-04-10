// Copyright Zikula, licensed MIT.

jQuery(document).ready(function ($) {
    $('a.external, #footer a').attr('target', '_blank');
    $('label.col-form-label').removeClass('col-md-2');

    if ($('#startButton').length > 0) {
        $('#startButton').removeClass('d-none');
    }

    $('form:first *:input:text:first').focus();

    if ($('#databaseCredentials').length > 0) {
        $('#submitButton').closest('form').submit(function(event) {
            $('#dbCheck').removeClass('d-none');
        });
    } else if ($('#userMigration').length > 0) {
        $('#migrate').on('click', function() {
            $('#spinner').removeClass('d-none');
            $(this).addClass('disabled');
            $(this).bind('click', false);
            migrate();
        });
        function migrate() {
            $.ajax({
                data: {},
                url: $('#pathHolder').data('migrate-route'),
                success: function(data, textStatus, jqXHR) {
                    $('.progress-bar').css('width', data.data.percentcomplete + '%');
                    if (data.data.percentcomplete === 100) {
                        $('.progress-bar').removeClass('progress-bar-striped active');
                        var redirect = setTimeout(function() {
                            window.location = $('#pathHolder').data('redirect-route');
                        }, 800);
                    } else {
                        migrate();
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert(jqXHR.responseText);
                }
            });
        }
    }
});
