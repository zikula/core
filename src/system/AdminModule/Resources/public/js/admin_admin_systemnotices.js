// Copyright Zikula Foundation, licensed MIT.

( function($) {
    $(document).ready(function() {
        // Toggle developer notices
        $(document).on('click', '#z-developernotices strong', function(e) {
            var ul = $('#z-developernotices ul');
            var span = $('#z-developernotices span');

            if ($('#z-developernotices ul').is(':visible')) {
                ul.slideUp();
                span.removeClass('fa fa-caret-down');
                span.addClass('fa fa-caret-right');
            } else {
                // We have to do some magic here, because the element has
                // 'display: none !important;' (Bootstrap's 'hide' class).
                // So first hide the element the jQuery way and then remove the css class.
                ul.hide();
                ul.removeClass('hide');

                ul.slideDown();
                span.removeClass('fa fa-caret-right');
                span.addClass('fa fa-caret-down');
            } 
        });
    });
})(jQuery);
