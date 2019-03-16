// Copyright Zikula Foundation, licensed MIT.

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
            .done(function(data) {
                // toggle label
                a.parent().find('a').toggleClass('hide');
            })
            .fail(function(jqXHR, textStatus) {
                alert('Error: ' + textStatus );
            })
            .always(function() {
                $('#spin' + bid).remove();
            })
        });
    });
})(jQuery);
