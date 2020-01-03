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
                span.removeClass('fa fa-caret-down');
                span.addClass('fa fa-caret-right');
            } else {
                ul.hide();
                ul.removeClass('d-none');
                ul.slideDown();
                span.removeClass('fa fa-caret-right');
                span.addClass('fa fa-caret-down');
            } 
        });
    });
})(jQuery);
