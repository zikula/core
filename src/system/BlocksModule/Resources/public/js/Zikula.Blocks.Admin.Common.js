// Copyright Zikula Foundation 2013 - license GNU/LGPLv3 (or at your option, any later version).

/*******************************************************************************
 * Toggle block
 *******************************************************************************/
( function($) {
    $(document).ready(function() {
        $('.z-admin-content .label[data-bid]').click( function(e) {
            e.preventDefault();
            var a = $(this);
            var bid = a.data('bid');

            a.after('<i id="spin' + bid + '" class="fa fa-cog fa-spin"></i>');

            $.ajax({
                url: Routing.generate('zikulablocksmodule_block_toggleblock'),
                data: {
                    bid: bid
                }
            })
            .done(function(response) {
                // toggle label
                a.parent().find('a').toggleClass('hide');
            })
            .fail(function(jqXHR, textStatus) {
                alert( "error: " + textStatus );
            })
            .always(function() {
                $('#spin' + bid).remove();
            })
        });
    });
})(jQuery);
