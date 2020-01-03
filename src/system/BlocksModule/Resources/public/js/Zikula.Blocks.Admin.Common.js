// Copyright Zikula Foundation, licensed MIT.

/*******************************************************************************
 * Toggle block
 *******************************************************************************/
(function($) {
    $(document).ready(function() {
        $('.block-state-switch').click(function (event) {
            event.preventDefault();
            var a = $(this);
            var bid = a.data('bid');

            a.after('<i id="spin' + bid + '" class="fa fa-cog fa-spin"></i>');

            $.ajax({
                url: Routing.generate('zikulablocksmodule_block_toggleblock'),
                data: {
                    bid: bid
                }
            }).done(function (data) {
                a.parent().find('a').toggleClass('d-none');
            }).fail(function (jqXHR, textStatus) {
                alert('Request failed: ' + textStatus);
            })
            .always(function () {
                $('#spin' + bid).remove();
            });
        });
    });
})(jQuery);
