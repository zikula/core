// Copyright Zikula Foundation, licensed MIT.

( function($) {
    $(document).ready(function() {
        // Delete a group
        $('.fa-trash-o').click( function(e) {
            e.preventDefault();
            var a = $(this);
            if (confirm(a.data('confirm'))) {
                $.ajax({
                    url: Routing.generate('zikulagroupsmodule_ajax_deletegroup'),
                    data: {
                        gid: a.data('gid')
                    },
                    success: function(response) {
                        a.parent().parent().remove();
                    },
                    error: function (response) {
                        alert($.parseJSON(response.responseText).core.statusmsg);
                    }
                });
            }
        });    
    });
})(jQuery);
