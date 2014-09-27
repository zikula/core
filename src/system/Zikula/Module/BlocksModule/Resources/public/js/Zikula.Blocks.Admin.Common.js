// Copyright Zikula Foundation 2013 - license GNU/LGPLv3 (or at your option, any later version).

/*******************************************************************************
 * Toggle block
 *******************************************************************************/
( function($) {
    $(document).ready(function() {
        $('.z-admin-content .label[data-bid]').click( function(e) {
            e.preventDefault();
            var a = $(this)
            $.ajax({
                url: Routing.generate('zikulablocksmodule_ajax_toggleblock'),
                data: {
                    bid: a.data('bid')
                },
                success: function(response) {
                    // toggle label
                    a.parent().find('a').toggleClass('hide');

                }
            });
        });
    });
})(jQuery);