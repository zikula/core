// Copyright Zikula Foundation, licensed MIT.

( function($) {
    $(document).ready(function() {
        $('#zikulablocksmodule-block-view-modal').on('show.bs.modal', function (e) {
            var link = $(e.relatedTarget);
            $(this).find('.modal-body').load(link.attr('href'));
        });
    })
})(jQuery);
