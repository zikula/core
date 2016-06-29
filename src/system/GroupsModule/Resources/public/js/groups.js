// Copyright Zikula Foundation, licensed MIT.

( function($) {
    $(document).ready(function() {
        // Delete a group
        $('.fa-trash-o').click( function(e) {
            e.preventDefault();
            var deleteAnchor = $(this);
            if (!confirm(deleteAnchor.data('confirm'))) {
                return;
            }

            $.ajax({
                url: Routing.generate('zikulagroupsmodule_ajax_deletegroup'),
                data: {
                    gid: deleteAnchor.data('gid')
                },
                success: function(response) {
                    deleteAnchor.parent().parent().remove();
                },
                error: function (response) {
                    alert($.parseJSON(response.responseText).core.statusmsg);
                }
            });
        });    
    });
})(jQuery);
