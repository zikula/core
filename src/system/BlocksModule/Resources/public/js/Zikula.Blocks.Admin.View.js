// Copyright Zikula Foundation, licensed MIT.

(function($) {
    $(document).ready(function() {
        $('#zikulablocksmodule-block-view-modal')
            .on('show.bs.modal', function (event) {
                var link = $(event.relatedTarget);
                $(this).find('.modal-body').load(link.attr('href'));
            })
            .on('hidden.bs.modal', function () {
                $(this).find('.modal-body').html('<div class="text-center"><i class="fas fa-spin fa-3x fa-spinner"></i></div>"');
            })
        ;
    });
})(jQuery);
