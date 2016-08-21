// Copyright Zikula Foundation, licensed MIT.

( function($) {
    $(document).ready(function() {
        $('#zikulablocksmodule-block-view-modal')
            .on('show.bs.modal', function (e) {
                var link = $(e.relatedTarget);
                $(this).find('.modal-body').load(link.attr('href'));
            })
            .on('hidden.bs.modal', function () {
                $(this).find('.modal-body').html('<div class="text-center"><i class="fa fa-spin fa-3x fa-spinner"></i></div>"');
            })
        ;
    })
})(jQuery);
