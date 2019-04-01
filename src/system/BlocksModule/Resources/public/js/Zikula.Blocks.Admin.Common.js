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
            })
            .done(function(data) {
                if (a.hasClass('label-success')) {
                    a.removeClass('label-success')
                        .addClass('label-danger')
                        .text(Translator.__('Inactive'))
                        .attr('title', Translator.__('Click to activate block'))
                    ;
                } else {
                    a.removeClass('label-danger')
                        .addClass('label-success')
                        .text(Translator.__('Active'))
                        .attr('title', Translator.__('Click to deactivate block'))
                    ; 
                }
            })
            .fail(function(jqXHR, textStatus) {
                alert('Error: ' + textStatus);
            })
            .always(function() {
                $('#spin' + bid).remove();
            });
        });
    });
})(jQuery);
