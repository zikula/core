// Copyright Zikula Foundation, licensed MIT.

(function($) {
    $(document).ready(function() {
        // Toggle developer notices
        $(document).on('click', '#z-developernotices strong', function(e) {
            var ul = $('#z-developernotices ul');
            var span = $('#z-developernotices span');

            if ($('#z-developernotices ul').is(':visible')) {
                ul.slideUp();
                ul.addClass('d-none');
                span.removeClass('fas fa-caret-down');
                span.addClass('fas fa-caret-right');
            } else {
                ul.hide();
                ul.removeClass('d-none');
                ul.slideDown();
                span.removeClass('fas fa-caret-right');
                span.addClass('fas fa-caret-down');
            } 
        });
    });
})(jQuery);
