// Copyright Zikula, licensed MIT.

(function($) {
    $(document).ready(function() {
        $('#migrate').on('click', function() {
            $('#spinner').show();
            $(this).addClass('disabled');
            $(this).bind('click', false);
            migrate();
        });
        function migrate() {
            $.ajax({
                data: {},
                url: window.location.pathname,
                success: function(data, textStatus, jqXHR) {
                    $('.progress-bar').css('width', data.data.percentcomplete + '%');
                    if (data.data.percentcomplete === 100) {
                        $('.progress-bar').removeClass('progress-bar-striped active');
                        var redirect = setTimeout(function() {
                            window.location = $('#redirectUrlAfterSuccess').data('route');
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
    });
})(jQuery);
