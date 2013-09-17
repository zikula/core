// Copyright Zikula Foundation 2013 - license GNU/LGPLv3 (or at your option, any later version).

( function($) {$(document).ready(function() {

    $('.label').click( function(e) {
        e.preventDefault();
        var a = $(this)
        $.ajax({
            url: 'index.php?module=Blocks&type=ajax&func=toggleblock',
            data: {
                bid: a.data('bid')
            },
            success: function(response) {
                // toggle label
                a.parent().find('a').toggleClass('hide');
                
            }
        });
    });

});})(jQuery);